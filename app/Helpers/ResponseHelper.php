<?php

namespace App\Helpers;

class ResponseHelper
{
    public static function response($message, $code = 200)
    {
        return response()->json(['message' => $message], $code);
    }
}
