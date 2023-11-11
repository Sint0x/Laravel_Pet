<?php

namespace App\Http\Middleware;
use App\Http\Controllers\Utils\JWTGenerator;
use Tech7ie\JwtLib\JWT;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;


class CheckUserToken
{
    public function handle($request, Closure $next)
    {
        //из головы запроса получаем jwt токен
        $access_token = $request->cookie('access_token');
        $refresh_token = $request->cookie('refresh_token');
        echo 'это куки:  ' . $refresh_token;
        if ($access_token == null) {
            if ($refresh_token == null) {
                return response()->json(['error' => 'Unauthorized!'], 401);
            }

            $user = DB::select('SELECT * FROM users WHERE refresh_token = ?', [$refresh_token]);



            // генерируем токен и секретный ключ
            $JWT = JWTGenerator::generate($user[0]->id);

            $random_str = bin2hex(openssl_random_pseudo_bytes(32));
            $response = $next($request);
            $access_token = $JWT['key'];
            $refresh_token = $random_str;
            Cookie::queue('access_token', $access_token, 1);
            Cookie::queue('refresh_token', $refresh_token, 43200);
            // обновляем секретный ключ
            DB::update('UPDATE users SET secret_key = ?, refresh_token = ? WHERE id = ? ', [$JWT['key'], $random_str, $user[0]->id]);


        }


        $payload_data = JWT::getpayload($access_token);
        try {
            $user = DB::select('SELECT * FROM users WHERE id = ?', [$payload_data->sub]);
        } catch(\Exception) {
            return response()->json(['error' => 'Bad Request!'], 400);
        }

        if (empty($user)) {
            return response()->json(['error' => 'Bad Request!'], 400);
        }

        // верифицируем
        if (!JWT::verify($access_token,$user[0]->secret_key)) {
            return response()->json(['error' => 'Unauthorized!'], 401);
        }
        

        $request->merge(['user' => (array)$user[0]]);
        return $response;
        // return $next($request);
    }
}
