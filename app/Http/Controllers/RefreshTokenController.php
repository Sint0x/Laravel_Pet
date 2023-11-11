<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Utils\JWTGenerator;

class RefreshTokenController extends Controller
{
    public function refresh(Request $request)
    {
        $random_str = bin2hex(openssl_random_pseudo_bytes(32));

        $header = $request->header('Authorization');
        list(,$token) = explode(' ', $header);

        if (!$token) {
            return response()->json(['error' => 'Forbidden'],403);
        }
        
        // ищем юзера по токену
        $user = DB::select('SELECT id FROM users WHERE refresh_token = ?', [$token]);

        if (!empty($user)) {

            // генерируем токен и секретный ключ
            $JWT = JWTGenerator::generate($user[0]->id);

            // обновляем секретный ключ
            DB::update('UPDATE users SET secret_key = ?, refresh_token = ? WHERE id = ? ', [$JWT['key'], $random_str, $user[0]->id]);

            return response()->json(['access_token' => $JWT['JWT'], 'refresh_token' => $random_str]);
        } else {
            return response()->json(['error' => 'Forbidden'], 403);
        }

    }
}