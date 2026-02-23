<?php

use App\Http\Controllers\LegacyFileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZohoOauthController;
use Filament\Facades\Filament;
use Illuminate\Notifications\Notification as BaseNotif;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductionRequestFile;

Route::get('/', function () {
    return view('welcome');
});

// Redirect default login to Filament login
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');



// Secure file access routes - require authentication and authorization
Route::middleware(['auth'])->group(function () {
    Route::get('/files/{file}', [App\Http\Controllers\ProductionRequestFileController::class, 'show'])
        ->name('files.show');
    Route::get('/files/{file}/download', [App\Http\Controllers\ProductionRequestFileController::class, 'download'])
        ->name('files.download');
    
    // Print Quotation
    Route::get('/quotations/{quotation}/print', [App\Http\Controllers\QuotationController::class, 'print'])
        ->name('quotations.print');
});

// Admin-only utility routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/perm-cache-reset', function () {
        // Only allow admins to reset permission cache
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'super-admin']), 403);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return 'Permission cache reset.';
    })->name('admin.perm-cache-reset');
});

// Development/testing routes - protect with environment check
if (app()->environment(['local', 'testing'])) {
    Route::middleware(['auth'])->group(function () {
        Route::get('/test-notif', function () {
            $user = Filament::auth()->user() ?? auth()->user();
            abort_unless($user, 401);

            Notification::send($user, new class extends BaseNotif {
                public function via($n): array
                { return ['database']; }
                public function toDatabase($n): array
                { return ['title'=>'تنبيه تجريبي','url'=>url('/')]; }
            });

            return 'OK';
        })->name('test-notif');
    });
}

Route::middleware(['auth'])->group(function () {
    Route::get('/files/legacy/{file}', [LegacyFileController::class, 'show'])
        ->name('legacy-files.show');

    Route::get('/files/legacy/{file}/download', [LegacyFileController::class, 'download'])
        ->name('legacy-files.download');
});

// Zoho integration routes - only available in non-production environments
if (!app()->environment('production')) {
    Route::middleware(['auth'])->group(function () {
        Route::get('/dev/zoho-perm-test', function () {
            // Only allow admins to test Zoho permissions
            abort_unless(auth()->user()?->hasAnyRole(['admin', 'super-admin']), 403);
            
            $accounts = rtrim(env('ZOHO_ACCOUNTS_BASE', 'https://accounts.zoho.com'), '/');
            $api      = rtrim(env('ZOHO_API_BASE',      'https://www.zohoapis.com'), '/');

            // جدّد access_token
            $tok = Http::asForm()->post($accounts.'/oauth/v2/token', [
                'grant_type'    => 'refresh_token',
                'client_id'     => env('ZOHO_CLIENT_ID'),
                'client_secret' => env('ZOHO_CLIENT_SECRET'),
                'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
            ])->throw()->json();

            $access = $tok['access_token'] ?? null;
            $H = ['Authorization' => 'Zoho-oauthtoken '.$access, 'Accept'=>'application/json'];

            $check = function($path) use ($api, $H) {
                try {
                    $r = Http::withHeaders($H)->get($api.'/crm/v3/'.$path, ['per_page'=>1]);
                    return ['ok' => $r->successful(), 'status' => $r->status(), 'body' => $r->json()];
                } catch (\Throwable $e) {
                    return ['ok' => false, 'error' => $e->getMessage()];
                }
            };

            return [
                'Quotes'   => $check('Quotes'),
                'Accounts' => $check('Accounts'),
                'Contacts' => $check('Contacts'),
                'Products' => $check('Products'),
            ];
        })->name('dev.zoho-perm-test');
    });
}

// Zoho OAuth callback - should be handled by a proper controller
Route::get('/oauth/zoho/callback', [ZohoOauthController::class, 'callback'])
    ->name('oauth.zoho.callback');

// Zoho test route - only in non-production
if (!app()->environment('production')) {
    Route::middleware(['auth'])->group(function () {
        Route::get('/dev/zoho-test', function () {
            // Only allow admins
            abort_unless(auth()->user()?->hasAnyRole(['admin', 'super-admin']), 403);
            
            $accounts = rtrim(env('ZOHO_ACCOUNTS_BASE', 'https://accounts.zoho.com'), '/');
            $api      = rtrim(env('ZOHO_API_BASE',      'https://www.zohoapis.com'), '/');
            $verify   = storage_path('certs/cacert.pem');

            // 1) refresh token
            $tok = Http::withOptions(['verify' => $verify])   // ← مهم
            ->asForm()
                ->post($accounts . '/oauth/v2/token', [
                    'grant_type'    => 'refresh_token',
                    'client_id'     => env('ZOHO_CLIENT_ID'),
                    'client_secret' => env('ZOHO_CLIENT_SECRET'),
                    'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
                ])->throw()->json();

            $access = $tok['access_token'] ?? null;
            if (!$access) return response()->json(['error'=>'no access_token','raw'=>$tok], 500);

            // 2) call API
            $resp = Http::withOptions(['verify' => $verify])  // ← مهم
            ->withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $access,
                'Accept'        => 'application/json',
            ])->get($api . '/crm/v3/Quotes', ['per_page' => 1])
                ->throw()->json();

            return ['ok'=>true, 'sample'=>$resp];
        })->name('dev.zoho-test');
    });
}
