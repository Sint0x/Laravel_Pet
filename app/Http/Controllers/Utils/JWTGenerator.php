<?php

namespace App\Http\Controllers\Utils;

use Tech7ie\JwtLib\JWT;

class JWTGenerator
{
    public static function generate($id)
    {
        $key = bin2hex(openssl_random_pseudo_bytes(32));

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        
        $payload = [
            'exp' => time()+28800,
            'sub' => $id
        ];
        
        $jwt = JWT::JWTgenerator($header, $payload, $key);

        return ['JWT' => $jwt,'key' => $key];
    }
}