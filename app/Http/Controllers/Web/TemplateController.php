<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Template\{StoreRequest, UpdateRequest};
use Illuminate\Http\Request;
use App\Models\{Page, Role, Template, TemplatePage, TemplatePageData};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        try {
            $templates = Template::paginate(5);
            return view('templates.index', compact('templates'));
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
            $template = Template::with('templatePages.pageData')->findOrFail($templateId);
            // dd($template->templatePages->toArray());
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

    public function savePageData(Request $request)
    {

        try {

            $pageId = $request->input('page_id');
            $jsonData = $request->except('page_id');

            // Find if the report exists by page_id
            $template = TemplatePageData::where('template_page_id', $pageId)->first();

            if ($template) {

                $existingJsonData = $template->json_data;

                // Merge existing data with the new data from the request
                $updatedData = array_merge($existingJsonData, $jsonData);

                // Save the updated json_data
                $template->json_data = json_encode($updatedData);
                $template->save();

                return response()->json(['status' => true, 'message' => 'Data updated successfully']);
            } else {
                // If the $template does not exist, create a new one with the json_data
                $newTemplate = new TemplatePageData();
                $newTemplate->template_page_id = $pageId;
                $newTemplate->json_data = json_encode($jsonData);
                $newTemplate->save();

                return response()->json(['status' => true, 'message' => 'Data saved successfully']);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'An error occurred while updating the page data'], 500);
        }


    }

    public function savePageFile(Request $request)
    {
        try {

            if ($request->hasFile('file')) {

                // Retrieve additional data from the request
                $pageId = $request->input('page_id');
                $type = $request->input('type');
                $folder = $request->input('folder');

                // Find if the report exists by page_id
                $template = TemplatePageData::where('template_page_id', $pageId)->first();

                $file = $request->file('file');
                // Generate unique filename
                $filename = time() . '_' . $file->getClientOriginalName();

                // Store the file in 'storage/app/public/uploads'
                $path = $file->storeAs('template-files/'.$folder, $filename, 'public');

                // Get the file size
                $fileSize = $file->getSize();  // Size in bytes

                // store file data
                $jsonData[$type] = [
                    'file_name' => $filename,
                    'path' => $path,
                    'size' => $fileSize
                ];

                if ($template) {

                    $existingJsonData = $template->json_data;

                    // Merge existing data with the new data from the request
                    $updatedData = array_merge($existingJsonData, $jsonData);

                    // Save the updated json_data
                    $template->json_data = json_encode($updatedData);
                    $template->save();

                } else {
                    // If the $template does not exist, create a new one with the json_data
                    $newTemplate = new TemplatePageData();
                    $newTemplate->template_page_id = $pageId;
                    $newTemplate->json_data = json_encode($jsonData);
                    $newTemplate->save();
                }

                return response()->json([
                    'status' => true,
                    'message' => 'File uploaded successfully',
                    'file_name' => $filename,
                    'file_size' => $fileSize,
                    'file_url' => asset('storage'),
                    'file_path' => $path
                ]);

            }

            return response()->json(['status' => false, 'message' => 'No file uploaded'], 400);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while uploading file'], 400);
        }
    }

    public function deletePageFile(Request $request)
    {
        try {

            $pageId = $request->input('page_id');
            $fileKey = $request->input('file_key');
            $fileId = $request->input('file_id',null);

            // Find if the report exists by page_id
            $template = TemplatePageData::where('template_page_id', $pageId)->firstOrFail();

            if ($template) {
                $existingJsonData = $template->json_data;
                $fileData = '';
                if($fileId !== null) {

                    // Check if the file key exists in the data and is an array
                    if (!array_key_exists($fileKey, $existingJsonData) || !is_array($existingJsonData[$fileKey])) {
                        return response()->json(['status' => false, 'message' => 'File key not found or invalid'], 404);
                    }

                    // Find the file index by searching for the file_id
                    $fileIndex = array_search($fileId, array_column($existingJsonData[$fileKey], 'file_id'));

                    // dd($fileDataArray, $fileIndex);
                    // Check if the file was found
                    if ($fileIndex === false) {
                        return response()->json(['status' => false, 'message' => 'File not found'], 404);
                    }

                    // Store file data for deletion from storage
                    $fileData = $existingJsonData[$fileKey][$fileIndex];

                    // Remove the file from the array
                    unset($existingJsonData[$fileKey][$fileIndex]);

                    // // Re-index the array to maintain sequential keys
                    $existingJsonData[$fileKey] = array_values($existingJsonData[$fileKey]);

                }
                else
                {
                    // Check if the key exists and remove it
                    if (array_key_exists($fileKey, $existingJsonData)) {
                        $fileData = $existingJsonData[$fileKey];
                        unset($existingJsonData[$fileKey]); // Remove the key from the array
                    }


                }

                // dd($existingJsonData);
                // Re-encode the updated data to JSON
                $template->json_data = json_encode($existingJsonData);

                // Save the updated template data back to the database
                $template->save();


                // Delete the file from storage
                if($fileData !== '')
                {
                    Storage::disk('public')->delete($fileData['path']);
                }

                return response()->json(['status' => true, 'message' => 'File removed successfully']);
            }

            return response()->json(['status' => false, 'message' => 'File not found'], 404);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while deleting file not found'], 400);
        }

    }

    public function savePageMultipleFiles(Request $request)
    {

        try {
            if ($request->hasFile('file')) {
                // Retrieve additional data from the request
                $pageId = $request->input('page_id');
                $folder = $request->input('folder');
                $type = $request->input('type');

                // Find or create the template record by page_id
                $template = TemplatePageData::firstOrCreate(
                    ['template_page_id' => $pageId],
                    ['json_data' => json_encode([])]
                );

                $existingJsonData = $template->json_data;

                // Loop through each file in the files array
                $files = $request->file('file');
                $fileData = [];

                foreach ($files as $file) {
                    // Generate unique filename and store the file
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('template-files/' . $folder, $filename, 'public');

                    // Get the file size
                    $fileSize = $file->getSize();  // Size in bytes

                    // Store file details in the array
                    $fileData[] = [
                        'file_name' => $filename,
                        'path' => $path,
                        'size' => $fileSize,
                        'file_id' => (string) Str::uuid()
                    ];
                }

                // Update json_data with the new files
                $existingJsonData[$type] = array_merge($existingJsonData[$type] ?? [], $fileData);
                $template->json_data = json_encode($existingJsonData);
                $template->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Files uploaded successfully',
                    'file_details' => $fileData
                ]);
            }

            return response()->json(['status' => false, 'message' => 'No files uploaded'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while uploading files',
            ], 400);
        }


    }


}
