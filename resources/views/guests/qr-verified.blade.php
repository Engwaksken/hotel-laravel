<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Guest Verification</title></head>
<body style="font-family:Arial,sans-serif;background:#f5f7f8;padding:24px">
<div style="max-width:520px;margin:auto;background:#fff;padding:24px;border-radius:18px">
<h2>Guest Verified</h2>
<p><strong>Name:</strong> {{ $record->full_name ?? $record->name ?? 'Guest' }}</p>
<p><strong>Email:</strong> {{ $record->email ?? '—' }}</p>
<p><strong>Phone:</strong> {{ $record->phone ?? '—' }}</p>
<p><strong>Status:</strong> Verified hotel guest record</p>
</div>
</body>
</html>
