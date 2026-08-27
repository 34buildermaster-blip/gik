<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactLead;
use App\Models\User;
use App\Notifications\ContactLeadSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ContactLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'min:8', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'service_type' => ['nullable', 'string', 'max:80'],
            'message' => ['nullable', 'string', 'max:5000'],
            'source_url' => ['nullable', 'url', 'max:1000'],
            'website' => ['nullable', 'string', 'max:0'],
        ], [
            'name.required' => 'กรุณาระบุชื่อผู้ติดต่อ',
            'name.min' => 'ชื่อต้องมีอย่างน้อย 2 ตัวอักษร',
            'phone.required' => 'กรุณาระบุเบอร์โทรศัพท์',
            'phone.min' => 'กรุณาตรวจสอบเบอร์โทรศัพท์',
            'phone.regex' => 'รูปแบบเบอร์โทรศัพท์ไม่ถูกต้อง',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
        ]);

        $lead = ContactLead::create([
            'name' => trim($validated['name']),
            'phone' => trim($validated['phone']),
            'email' => isset($validated['email']) ? mb_strtolower(trim($validated['email'])) : null,
            'service_type' => isset($validated['service_type']) ? trim($validated['service_type']) : null,
            'message' => isset($validated['message']) ? trim($validated['message']) : null,
            'source_url' => $validated['source_url'] ?? null,
            'status' => ContactLead::STATUS_NEW,
            'ip_hash' => $request->ip()
                ? hash_hmac('sha256', $request->ip(), (string) config('app.key'))
                : null,
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        Notification::send(
            User::query()->where('role', 'admin')->get(),
            new ContactLeadSubmitted($lead),
        );

        return response()->json([
            'message' => 'ส่งข้อมูลเรียบร้อยแล้ว ทีมงานจะติดต่อกลับโดยเร็วที่สุด',
            'data' => ['reference' => 'LEAD-'.str_pad((string) $lead->id, 6, '0', STR_PAD_LEFT)],
        ], 202);
    }
}
