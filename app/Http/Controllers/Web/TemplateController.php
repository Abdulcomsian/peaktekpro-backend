<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Template\{StoreRequest, UpdateRequest};
use Illuminate\Http\Request;
use App\Models\{Page, Role, Template, TemplatePage};

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        try {
            $accessToken = $request->_accessToken;
            $user = $request->user;
            $templates = Template::paginate(5);
            dd($request->all(),$accessToken);
            return view('templates.index', compact('accessToken','user','templates'));
        } catch (\Throwable $th) {
            abort(500,'An error occurred while fetching templates.');
        }
    }
    public function create()
    {

        dd('create');
        $pages = Page::all();

        return view('templates.create', compact('pages'));
    }

    public function store(StoreRequest $request)
    {

        try {

            $template = Template::create([
                'title' => $request->title
            ]);
            // sync pages with template pages
            $pages = Page::all();
            $pages->each(function ($page, $index) use ($template) {
                $template->templatePages()->create([
                    'name' => $page->name,
                    'slug' => $page->slug,
                    'order_no' => $index
                ]);
            });

            return response()->json([
                'status' => true,
                'message' => 'Template created successfully',
                'redirect_to' => route('templates.edit', $template->id)
            ], 200);
        } catch (\Throwable $th) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $th->getMessage()
                ]
            );
        }
    }

    public function edit($templateId)
    {
        try {
            $template = Template::with('templatePages')->findOrFail($templateId);
            return view('templates.edit', compact('template'));
        } catch (\Throwable $e) {
            return redirect()->route('templates.index')->with('error', 'Template not found');
        }
    }

    public function updateTitle(UpdateRequest $request, $templateId)
    {

        try {

            $template = Template::findOrFail($templateId);
            $template->title = $request->title;

            $template->save();

            return response()->json([
                'status' => true,
                'message' => 'Title updated successfully',
            ], 200);
        } catch (\Throwable $th) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $th->getMessage()
                ]
            );
        }
    }

    public function destroy($templateId)
    {
        try {

            $template = Template::findOrFail($templateId);
            $template->delete();

            return response()->json([
                'status' => true,
                'message' => 'Template deleted successfully',
            ], 200);
        } catch (\Throwable $th) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $th->getMessage()
                ]
            );
        }
    }

    public function updateTemplatePagesOrdering(Request $request, $templateId)
    {
        try {

            $order = $request->input('order');

            if (empty($order)) {

                return response()->json([
                    'status' => false,
                    'message' => 'Pages ordering not updated successfully',
                ], 500);
            }

            // update page ordering
            foreach ($order as $position => $id) {

                TemplatePage::where('id', $id)->update(['order_no' => $position]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Pages ordering updated successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $th->getMessage()
                ]
            );
        }
    }

    public function createPage(Request $request, $templateId)
    {

        try {

            $lastTemplatePage = TemplatePage::where('template_id', $templateId)->orderBy('order_no', 'desc')->first();

            $templatePage = TemplatePage::create([
                'template_id' => $templateId,
                'name' => $request->title,
                'order_no' => $lastTemplatePage->order_no + 1
            ]);

            $templatePage = TemplatePage::findOrFail($templatePage->id);

            return response()->json([
                'status' => true,
                'message' => 'Page created successfully',
                'page' => $templatePage
            ], 200);
        } catch (\Throwable $th) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $th->getMessage()
                ]
            );
        }
    }

    // Method to update page status
    public function updatePageStatus(Request $request, $pageId)
    {

        try {

            $page = TemplatePage::findOrFail($pageId);
            $page->is_active = $request->status;
            $page->save();

            return response()->json(['status' => true, 'message' => 'Page status updated successfully'], 200);
        } catch (\Throwable $th) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $th->getMessage()
                ]
            );
        }
    }

    public function updatePageTitle(Request $request,$pageId)
    {

        try {

            $page = TemplatePage::findOrFail($pageId);
            $page->name = $request->name;
            $page->save();

            return response()->json(['status' => true, 'message' => 'Page name updated successfully'], 200);
        } catch (\Throwable $th) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $th->getMessage()
                ]
            );
        }

    }

}
