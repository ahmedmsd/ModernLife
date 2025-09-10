<?php

namespace App\Http\Controllers;

use App\Models\LegacyClientProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegacyFileController extends Controller
{
    public function show(LegacyClientProjectFile $file, Request $request)
    {
        $disk = 'public';
        $path = $file->file_path;
        abort_unless($path && Storage::disk($disk)->exists($path), 404);

        $mime = $file->mime_type ?: Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        $full = Storage::disk($disk)->path($path);

        return response()->file($full, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($full).'"',
        ]);
    }

    public function download(LegacyClientProjectFile $file, Request $request)
    {
        $disk = 'public';
        $path = $file->file_path;
        abort_unless($path && Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->download($path, basename($path));
    }
}
