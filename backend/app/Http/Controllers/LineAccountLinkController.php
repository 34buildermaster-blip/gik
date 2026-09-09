<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LineAccountLink;
use App\Services\LineMessaging;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LineAccountLinkController extends Controller
{
    public function connect(Request $request, LineMessaging $line): RedirectResponse
    {
        abort_unless($line->isConfigured(), 503, 'ยังไม่ได้ตั้งค่าการเชื่อมต่อ LINE');

        $data = $request->validate([
            'linkToken' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);
        $nonce = Str::random(64);

        DB::transaction(function () use ($request, $nonce): void {
            LineAccountLink::query()
                ->where('user_id', $request->user()->id)
                ->whereNull('consumed_at')
                ->delete();

            LineAccountLink::create([
                'user_id' => $request->user()->id,
                'nonce_hash' => hash('sha256', $nonce),
                'expires_at' => now()->addMinutes(10),
            ]);
        });

        $url = (string) config('project_notifications.line_account_link_url');
        $query = http_build_query([
            'linkToken' => $data['linkToken'],
            'nonce' => $nonce,
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away($url.'?'.$query);
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (filled($user->line_recipient_id)) {
            $user->update(['line_recipient_id' => null]);
            LineAccountLink::query()->where('user_id', $user->id)->delete();
            AuditLog::record($user, 'line.account_disconnected', $user, 'ยกเลิกการเชื่อมต่อบัญชี LINE');
        }

        return back()->with('success', 'ยกเลิกการเชื่อมต่อ LINE เรียบร้อยแล้ว');
    }
}
