<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ปฏิทินโครงการ {{ $month->format('m/Y') }}</title>
    <style>body{font-family:Tahoma,sans-serif;color:#17251f;margin:32px}header{display:flex;justify-content:space-between;align-items:end;border-bottom:2px solid #08754f;padding-bottom:14px;margin-bottom:20px}h1{margin:0;font-size:24px}p{margin:5px 0;color:#66736d}table{width:100%;border-collapse:collapse;font-size:13px}th,td{border:1px solid #d9dfdc;padding:9px;text-align:left;vertical-align:top}th{background:#edf5f1}button{padding:8px 14px}@media print{button{display:none}body{margin:12mm}}</style>
</head>
<body>
<header><div><h1>ปฏิทินโครงการ</h1><p>{{ $month->locale('th')->translatedFormat('F Y') }} · 34 Build Master</p></div><button type="button" onclick="window.print()">พิมพ์ / บันทึก PDF</button></header>
<table><thead><tr><th>วันเวลา</th><th>โครงการ</th><th>นัดหมาย</th><th>สถานที่</th><th>ผู้รับผิดชอบ</th><th>การตอบรับ</th></tr></thead><tbody>
@forelse($events as $event)<tr><td>{{ $event->starts_at->format('d/m/Y H:i') }}@if($event->ends_at)<br>ถึง {{ $event->ends_at->format('d/m/Y H:i') }}@endif</td><td>{{ $event->project->code }}<br>{{ $event->project->name }}</td><td>{{ $event->title }}<br><small>{{ \App\Models\ProjectEvent::TYPE_LABELS[$event->type] }}</small></td><td>{{ $event->location ?: '-' }}</td><td>{{ $event->assignee?->name ?: '-' }}</td><td>{{ \App\Models\ProjectEvent::CUSTOMER_RESPONSE_LABELS[$event->customer_response] ?? 'รอตอบรับ' }}</td></tr>@empty<tr><td colspan="6">ไม่มีนัดหมายในเดือนนี้</td></tr>@endforelse
</tbody></table>
</body></html>
