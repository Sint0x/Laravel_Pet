<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->json()->all();
        $username = $data['username'];
        $password = $data['password'];

        // ищем по логину
        $user = DB::select('SELECT * FROM users WHERE login = ?', [$username]);

        if (empty($user)) {
            // хешируем пароль
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // создаем новую запись
            DB::insert('INSERT INTO users (login, password) VALUES (?, ?)', [$username, $hashedPassword]);

            return response()->json(['success' => 'Registration successful!'], 201);
        } else {
            return response()->json(['error' => 'Username already exists!'], 409);
        }
    }
}