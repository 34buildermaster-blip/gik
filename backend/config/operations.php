<?php

return [
    'backup_retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
    'backup_max_age_hours' => (int) env('BACKUP_MAX_AGE_HOURS', 36),
];
