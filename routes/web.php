<?php

use App\Http\Controllers\LegacyFileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ZohoOauthController;
use Filament\Facades\Filament;
use Illuminate\Notifications\Notification as BaseNotif;

Route::get('/', function () {
    return view('welcome');
});

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
})->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/files/legacy/{file}', [LegacyFileController::class, 'show'])
        ->name('legacy-files.show');

    Route::get('/files/legacy/{file}/download', [LegacyFileController::class, 'download'])
        ->name('legacy-files.download');
});

Route::get('/dev/zoho-perm-test', function () {
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
});

Route::get('/oauth/zoho/callback', function (Request $r) {
    $code = $r->query('code');
    if (!$code) return response('Missing ?code', 400);

    $accounts = rtrim($r->query('accounts-server', env('ZOHO_ACCOUNTS_BASE', 'https://accounts.zoho.com')), '/');
    $verify   = storage_path('certs/cacert.pem');

    $res = Http::withOptions(['verify' => $verify])   // ← مهم
    ->asForm()
        ->post($accounts . '/oauth/v2/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('ZOHO_CLIENT_ID'),
            'client_secret' => env('ZOHO_CLIENT_SECRET'),
            'redirect_uri'  => 'http://localhost:8000/oauth/zoho/callback',
            'code'          => $code,
        ])->throw()->json();

    return response()->json($res);
});

Route::get('/dev/zoho-test', function () {
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
});
