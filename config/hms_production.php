<?php
return [
    'health' => ['snapshot_minutes' => (int) env('HMS_HEALTH_SNAPSHOT_MINUTES', 15)],
    'backups' => ['retention_days' => (int) env('HMS_BACKUP_RETENTION_DAYS', 30)],
    'api' => ['rate_limit_per_minute' => (int) env('HMS_API_RATE_LIMIT', 120)],
    'firebase' => ['project_id' => env('FIREBASE_PROJECT_ID'), 'access_token' => env('FIREBASE_ACCESS_TOKEN')],
];
