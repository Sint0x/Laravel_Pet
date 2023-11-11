<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;
use App\Http\Controllers\Utils\JWTGenerator;

class TestController extends Controller
{
    public function tester(Request $request)
    {
        return $request->user;
    }
}