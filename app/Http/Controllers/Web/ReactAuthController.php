<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
class ReactAuthController extends Controller
{
    public function __invoke(Request $request)
    {
        // return Redirect::to("templates?t=$request->t");
        return redirect()->route('reports.index');
        return redirect()->route('template.index');


    }
}
