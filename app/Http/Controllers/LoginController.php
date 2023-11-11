<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Utils\JWTGenerator;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $random_str = bin2hex(openssl_random_pseudo_bytes(32));

        $data = $request->json()->all();
        $username = $data['username'];
        $password = $data['password'];


        // ищем по логину в бд
        $user = DB::select('SELECT password, id FROM users WHERE login = ?', [$username]);

        if (!empty($user) && password_verify($password, $user[0]->password)) {

            // генерируем токен и секретный ключ
            $JWT = JWTGenerator::generate($user[0]->id);

            // обовляем секретный ключ
            DB::update('UPDATE users SET secret_key = ?, refresh_token = ? WHERE login = ? ', [$JWT['key'], $random_str ,$username]);

            return response()->json(['message' => 'Created!'], 201)->withCookie(cookie('access_token', $JWT['JWT'], 1))->withCookie(cookie('refresh_token', $random_str, 43200));
        } else {
            return response()->json(['error' => 'Bad Request!'], 400);
        }

    }
}