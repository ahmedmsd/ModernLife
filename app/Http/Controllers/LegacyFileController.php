<?php

namespace App\Http\Controllers;

use App\Models\LegacyClientProjectFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LegacyFileController extends Controller
{
    protected string $disk = 'public';

    public function show(LegacyClientProjectFile $file, Request $request)
    {
        $path = (string) $file->file_path;
        abort_unless($path && Storage::disk($this->disk)->exists($path), 404);

        $fullPath = Storage::disk($this->disk)->path($path);

        // MIME موثوق
        $mime = $this->resolveMime($file->mime_type, $path);

        // كاش قوي + ETag/Last-Modified
        $lastMod  = Storage::disk($this->disk)->lastModified($path);
        $size     = Storage::disk($this->disk)->size($path);
        $etag     = \sprintf('W/"%s-%s"', $lastMod, $size);

        // If-None-Match / If-Modified-Since => 304
        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=31536000, immutable')
                ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastMod).' GMT');
        }

        if ($ims = $request->headers->get('If-Modified-Since')) {
            $imsTime = strtotime($ims);
            if ($imsTime !== false && $imsTime >= $lastMod) {
                return response('', 304)
                    ->header('ETag', $etag)
                    ->header('Cache-Control', 'public, max-age=31536000, immutable')
                    ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastMod).' GMT');
            }
        }

        // اسم ملف آمن (يدعم العربية)
        $name = basename($fullPath);
        $disp = 'inline; filename="' . $this->asciiFallback($name) . '"; filename*=UTF-8\'\'' . rawurlencode($name);

        return response()->file($fullPath, [
            'Content-Type'        => $mime,
            'Content-Disposition' => $disp,
        ])
            ->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=31536000, immutable')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastMod).' GMT')
            ->header('X-Content-Type-Options', 'nosniff'); // مفيد خصوصًا للـ SVG
    }

    public function download(LegacyClientProjectFile $file, Request $request)
    {
        $path = (string) $file->file_path;
        abort_unless($path && Storage::disk($this->disk)->exists($path), 404);

        $name = basename($path);
        return Storage::disk($this->disk)->download($path, $name);
    }

    /** MIME موثوق مع fallback بالامتداد */
    protected function resolveMime(?string $storedMime, string $path): string
    {
        $mime = trim((string) $storedMime);
        if ($mime === '') {
            try {
                $mime = (string) (Storage::disk($this->disk)->mimeType($path) ?? '');
            } catch (\Throwable $e) {
                $mime = '';
            }
        }
        if ($mime === '') {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $map = [
                'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif',
                'webp'=>'image/webp','bmp'=>'image/bmp','svg'=>'image/svg+xml','svgz'=>'image/svg+xml',
                'avif'=>'image/avif','heic'=>'image/heic','heif'=>'image/heif',
                'pdf'=>'application/pdf',
                'doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];
            $mime = $map[$ext] ?? 'application/octet-stream';
        }
        return $mime;
    }

    /** اسم ASCII بديل لـ Content-Disposition */
    protected function asciiFallback(string $name): string
    {
        $fallback = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        return $fallback !== false && $fallback !== '' ? $fallback : 'file';
    }
}
