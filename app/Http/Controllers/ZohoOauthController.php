<?php
// app/Http/Controllers/ZohoOauthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZohoOauthController extends Controller
{
    public function callback(Request $request)
    {
        // 1) استلم الـcode
        $code = $request->query('code');
        if (!$code) {
            return response('Missing ?code', 400);
        }

        // 2) حدّد حساب الـaccounts (من بارامتر Zoho أو افتراضي .com)
        $accountsBase = $request->query('accounts-server', 'https://accounts.zoho.com');

        // 3) استخدم نفس redirect_uri المُسجَّل في الـConsole تمامًا
        $redirectUri = 'http://localhost:8000/oauth/zoho/callback';

        // 4) بدّل code ← access_token + refresh_token
        $res = Http::asForm()->post($accountsBase . '/oauth/v2/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('ZOHO_CLIENT_ID'),
            'client_secret' => env('ZOHO_CLIENT_SECRET'),
            'redirect_uri'  => $redirectUri,
            'code'          => $code,
        ])->throw()->json();

        // 5) اعرض النتيجة ببساطة (انسخ الـrefresh_token وضعه في .env)
        $access  = $res['access_token']   ?? null;
        $refresh = $res['refresh_token']  ?? null;
        $expires = $res['expires_in']     ?? null;

        // تقدر هنا تحفظ الـrefresh_token في DB/Settings بدلاً من العرض
        // مثال سريع (اختياري): \Log::info('ZOHO TOKENS', $res);

        return response()->json([
            'ok'            => true,
            'access_token'  => $access,
            'refresh_token' => $refresh,   // انسخه لـ .env
            'expires_in'    => $expires,
            'note'          => 'انسخ refresh_token إلى ملف .env ثم استخدمه مستقبلاً لتجديد الوصول',
        ]);
    }
}
