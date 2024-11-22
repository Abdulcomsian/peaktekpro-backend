<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class ReactAuthController extends Controller
{
    public function __invoke(Request $request)
    {

        return redirect()->route('templates.index',$request->query('_accessToken',$request->_accessToken));

    }
}
