<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class ReactAuthController extends Controller
{
    public function __invoke(Request $request)
    {
        dd($request->all());
    }
}
