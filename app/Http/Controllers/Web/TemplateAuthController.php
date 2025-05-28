<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class TemplateAuthController extends Controller
{
    public function __invoke(Request $request)
    {
        return redirect()->route('templates.index');
    }
}
