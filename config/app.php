<?php

return [
    'app_name' => 'RUANG UJIAN',
    'app_version' => '1.0.0',
    'timezone' => 'Asia/Jakarta',
    'debug' => true,
    'session_lifetime' => 7200, // 2 hours
    'upload_max_filesize' => 25 * 1024 * 1024, // 25 MB
    'allowed_doc_types' => ['application/pdf', 'image/jpeg', 'image/png', 'video/mp4'],
    'base_dir' => dirname(__DIR__),
    'storage_dir' => dirname(__DIR__) . '/storage',
    'upload_dir' => dirname(__DIR__) . '/storage/uploads',
    'backup_dir' => dirname(__DIR__) . '/storage/backups',
];
