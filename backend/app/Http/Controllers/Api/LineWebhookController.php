<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LineAccountLink;
use App\Models\User;
use App\Services\LineMessaging;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class LineWebhookController extends Controller
{
    public function __invoke(Request $request, LineMessaging $line): JsonResponse
    {
        abort_unless($line->isConfigured(), 503);

        $payload = $request->getContent();
        abort_unless(
            $line->verifySignature($payload, $request->header('X-Line-Signature')),
            401,
        );

        foreach ((array) $request->input('events', []) as $event) {
            try {
                $this->handleEvent($event, $line);
            } catch (Throwable $exception) {
                Log::warning('LINE webhook event could not be processed.', [
                    'event_type' => data_get($event, 'type'),
                    'webhook_event_id' => data_get($event, 'webhookEventId'),
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    private function handleEvent(array $event, LineMessaging $line): void
    {
        $type = data_get($event, 'type');
        $lineUserId = data_get($event, 'source.type') === 'user'
            ? data_get($event, 'source.userId')
            : null;
        $replyToken = data_get($event, 'replyToken');

        if (! is_string($lineUserId) || ! str_starts_with($lineUserId, 'U')) {
            return;
        }

        if ($type === 'follow') {
            $line->sendAccountLinkInvitation($lineUserId, (string) $replyToken);

            return;
        }

        if ($type === 'message' && data_get($event, 'message.type') === 'text') {
            $command = mb_strtolower(trim((string) data_get($event, 'message.text')));

            if (in_array($command, ['เชื่อมบัญชี', 'เชื่อมไลน์', 'link', 'connect'], true)) {
                $line->sendAccountLinkInvitation($lineUserId, (string) $replyToken);
            }

            return;
        }

        if ($type === 'accountLink') {
            $this->completeAccountLink($event, $lineUserId, $replyToken, $line);
        }
    }

    private function completeAccountLink(
        array $event,
        string $lineUserId,
        ?string $replyToken,
        LineMessaging $line,
    ): void {
        $nonce = data_get($event, 'link.nonce');

        if (data_get($event, 'link.result') !== 'ok' || ! is_string($nonce)) {
            $line->replyText($replyToken, 'เชื่อมบัญชีไม่สำเร็จ กรุณาพิมพ์ “เชื่อมบัญชี” แล้วลองใหม่อีกครั้ง');

            return;
        }

        $link = LineAccountLink::query()
            ->where('nonce_hash', hash('sha256', $nonce))
            ->with('user')
            ->first();

        if (! $link?->isUsable()) {
            $line->replyText($replyToken, 'ลิงก์หมดอายุหรือถูกใช้งานแล้ว กรุณาพิมพ์ “เชื่อมบัญชี” เพื่อขอลิงก์ใหม่');

            return;
        }

        $alreadyLinked = User::query()
            ->where('line_recipient_id', $lineUserId)
            ->whereKeyNot($link->user_id)
            ->exists();

        if ($alreadyLinked) {
            $link->update(['consumed_at' => now()]);
            $line->replyText($replyToken, 'LINE บัญชีนี้เชื่อมกับผู้ใช้อื่นแล้ว กรุณาติดต่อผู้ดูแลระบบ');

            return;
        }

        try {
            DB::transaction(function () use ($link, $lineUserId): void {
                $lockedLink = LineAccountLink::query()->lockForUpdate()->findOrFail($link->id);
                abort_unless($lockedLink->isUsable(), 409);
                $lockedLink->user()->update(['line_recipient_id' => $lineUserId]);
                $lockedLink->update(['consumed_at' => now()]);
            });
        } catch (QueryException) {
            $line->replyText($replyToken, 'LINE บัญชีนี้เชื่อมกับผู้ใช้อื่นแล้ว กรุณาติดต่อผู้ดูแลระบบ');

            return;
        }

        AuditLog::record(
            $link->user,
            'line.account_connected',
            $link->user,
            'เชื่อมต่อบัญชี LINE สำหรับรับการแจ้งเตือน',
            ['webhook_event_id' => data_get($event, 'webhookEventId')],
        );
        $line->replyText($replyToken, 'เชื่อมบัญชี LINE กับ 34 Build Master สำเร็จแล้ว คุณจะได้รับแจ้งเตือนหลัง Admin อนุมัติอัปเดตงาน');
    }
}
