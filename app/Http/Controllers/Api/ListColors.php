<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Color as ColorResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListColors extends Controller
{

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return ColorResource::collection(DB::table('colors')->get());
    }
}
