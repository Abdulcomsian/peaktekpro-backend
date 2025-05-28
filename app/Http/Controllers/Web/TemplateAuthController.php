<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class TemplateAuthController extends Controller
{
    public function __invoke(Request $request)
    {
        // return Redirect::to("templates?t=$request->t");
        return redirect()->route('template.index');


    }
}
