<?php

namespace App\Http\Controllers;

use App\Models\ProductionRequestFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ProductionRequestFileController extends Controller
{
    protected string $disk = 'public';

    /**
     * Display the file (inline view)
     */
    public function show(ProductionRequestFile $file, Request $request)
    {
        // Check if user can view the production request
        $productionRequest = $file->productionRequest;
        
        if (!$productionRequest) {
            abort(404);
        }

        // Use the policy to check access
        if (!Gate::allows('view', $productionRequest)) {
            abort(403, 'Unauthorized access to this file.');
        }

        $path = ltrim($file->file_path, '/');
        abort_unless($path && Storage::disk($this->disk)->exists($path), 404);

        $fullPath = Storage::disk($this->disk)->path($path);

        // MIME type resolution
        $mime = $this->resolveMime(null, $path);

        // Cache headers
        $lastMod = Storage::disk($this->disk)->lastModified($path);
        $size = Storage::disk($this->disk)->size($path);
        $etag = sprintf('W/"%s-%s"', $lastMod, $size);

        // If-None-Match / If-Modified-Since => 304
        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'private, max-age=3600')
                ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastMod) . ' GMT');
        }

        if ($ims = $request->headers->get('If-Modified-Since')) {
            $imsTime = strtotime($ims);
            if ($imsTime !== false && $imsTime >= $lastMod) {
                return response('', 304)
                    ->header('ETag', $etag)
                    ->header('Cache-Control', 'private, max-age=3600')
                    ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastMod) . ' GMT');
            }
        }

        // Safe filename handling (supports Arabic)
        $name = basename($fullPath);
        $disp = 'inline; filename="' . $this->asciiFallback($name) . '"; filename*=UTF-8\'\'' . rawurlencode($name);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disp,
        ])
            ->header('ETag', $etag)
            ->header('Cache-Control', 'private, max-age=3600')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s', $lastMod) . ' GMT')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Download the file
     */
    public function download(ProductionRequestFile $file, Request $request)
    {
        // Check if user can view the production request
        $productionRequest = $file->productionRequest;
        
        if (!$productionRequest) {
            abort(404);
        }

        // Use the policy to check access
        if (!Gate::allows('view', $productionRequest)) {
            abort(403, 'Unauthorized access to this file.');
        }

        $path = ltrim($file->file_path, '/');
        abort_unless($path && Storage::disk($this->disk)->exists($path), 404);

        $name = basename($path);
        return Storage::disk($this->disk)->download($path, $name);
    }

    /**
     * Resolve MIME type with fallback by extension
     */
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
                'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
                'webp' => 'image/webp', 'bmp' => 'image/bmp', 'svg' => 'image/svg+xml', 'svgz' => 'image/svg+xml',
                'avif' => 'image/avif', 'heic' => 'image/heic', 'heif' => 'image/heif',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];
            $mime = $map[$ext] ?? 'application/octet-stream';
        }
        return $mime;
    }

    /**
     * ASCII fallback for Content-Disposition
     */
    protected function asciiFallback(string $name): string
    {
        $fallback = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        return $fallback !== false && $fallback !== '' ? $fallback : 'file';
    }
}

