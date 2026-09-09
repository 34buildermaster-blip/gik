<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class LineMessaging
{
    public function isConfigured(): bool
    {
        return (bool) config('project_notifications.line')
            && filled(config('project_notifications.line_channel_access_token'))
            && filled(config('project_notifications.line_channel_secret'));
    }

    public function canStartAccountLink(): bool
    {
        return $this->isConfigured()
            && filled(config('project_notifications.line_add_friend_url'));
    }

    public function verifySignature(string $payload, ?string $signature): bool
    {
        $secret = (string) config('project_notifications.line_channel_secret');

        if ($secret === '' || blank($signature)) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($expected, $signature);
    }

    public function sendAccountLinkInvitation(string $lineUserId, string $replyToken): void
    {
        $endpoint = sprintf(
            (string) config('project_notifications.line_link_token_url'),
            rawurlencode($lineUserId),
        );
        $linkToken = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(8)
            ->post($endpoint)
            ->throw()
            ->json('linkToken');

        if (! is_string($linkToken) || $linkToken === '') {
            throw new RuntimeException('LINE did not return an account link token.');
        }

        $url = route('line.account.connect', ['linkToken' => $linkToken]);
        $this->replyText(
            $replyToken,
            "เชื่อมบัญชี LINE กับ 34 Build Master\nเปิดลิงก์และเข้าสู่ระบบด้วยบัญชีลูกค้าของคุณภายใน 10 นาที\n{$url}",
        );
    }

    public function replyText(?string $replyToken, string $message): void
    {
        if (blank($replyToken)) {
            return;
        }

        Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(8)
            ->post((string) config('project_notifications.line_reply_url'), [
                'replyToken' => $replyToken,
                'messages' => [[
                    'type' => 'text',
                    'text' => $message,
                ]],
            ])
            ->throw();
    }

    private function accessToken(): string
    {
        $token = (string) config('project_notifications.line_channel_access_token');

        if ($token === '') {
            throw new RuntimeException('LINE channel access token is not configured.');
        }

        return $token;
    }
}
