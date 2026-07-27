<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Serve uploaded files from storage/app/public/.
 *
 * Uses /uploads/{path} instead of /storage/{path} because PHP's built-in
 * server (php artisan serve) intercepts /storage/* URLs and returns 403
 * on Windows when it encounters the public/storage symlink. Even with the
 * symlink removed, the server may still return 403 for paths containing
 * "storage" (Windows path handling quirk).
 *
 * Security: realpath() + str_starts_with() prevents path traversal.
 * Explicit MIME type mapping ensures correct Content-Type headers.
 */
Route::get('uploads/{path}', function (string $path) {
    $fullPath = realpath(storage_path('app/public/' . $path));
    $allowedPath = realpath(storage_path('app/public'));

    abort_if($fullPath === false || !str_starts_with($fullPath, $allowedPath), 404);

    $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt' => 'text/plain',
    ];
    $contentType = $mimeTypes[strtolower($extension)] ?? null;

    return response()->file($fullPath, $contentType ? ['Content-Type' => $contentType] : []);
})->where('path', '.*');
