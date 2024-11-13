<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Page};

class TemplateController extends Controller
{
    public function index()
    {
        return view('templates.index');

    }
    public function create()
    {

        $pages = Page::all();

        return view('templates.create', compact('pages'));

    }

    public function updatePageOrdering(Request $request)
    {
        $order = $request->input('order');

        dd($order);

        // // Save the order to the database (assuming you have a `Tab` model and `order` column)
        // foreach ($order as $position => $id) {
        //     \App\Models\Tab::where('id', $id)->update(['order' => $position]);
        // }

        return response()->json(['status' => 'success']);
    }

}
