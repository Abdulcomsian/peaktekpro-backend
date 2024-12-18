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
        } catch (\Exception $e) {
            abort(500, 'An error occurred while fetching templates.');
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
        } catch (\Exception $e) {

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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {

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
        } catch (\Exception $e) {

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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {

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
        } catch (\Exception $e) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $th->getMessage()
                ]
            );
        }
    }

    public function updatePageTitle(Request $request, $pageId)
    {

        try {

            $page = TemplatePage::findOrFail($pageId);
            $page->name = $request->name;
            $page->save();

            return response()->json(['status' => true, 'message' => 'Page name updated successfully'], 200);
        } catch (\Exception $e) {

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
        } catch (\Exception $e) {
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
                $path = $file->storeAs('template-files/' . $folder, $filename, 'public');

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
            $fileId = $request->input('file_id', null);

            // Find if the report exists by page_id
            $template = TemplatePageData::where('template_page_id', $pageId)->firstOrFail();

            if ($template) {
                $existingJsonData = $template->json_data;
                $fileData = '';
                if ($fileId !== null) {

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
                } else {
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
                if ($fileData !== '') {
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


    public function saveQuoteSectionDetails(Request $request)
    {
        try {

            $pageId = $request->input('page_id');
            $quoteSectionData = $request->input('quoteSection');
            $grandTotal = $request->input('grandTotal');

            // Find if the page data exists by page_id
            $quote = TemplatePageData::where('template_page_id', $pageId)->first();

            if (!$quote) {
                // Create a new quote with default structure
                $quote = TemplatePageData::create([
                    'template_page_id' => $pageId,
                    'json_data' => json_encode([
                        'grand_total' => $grandTotal,
                        'sections' => []
                    ], true),
                ]);
            }


            // Retrieve the current JSON data
            $currentData = $quote->json_data ?? ['grand_total' => 0, 'sections' => []];
            $sections = collect($currentData['sections']);

            // Check if the section already exists
            $existingSectionIndex = $sections->search(fn($s) => $s['id'] === $quoteSectionData['id']);

            if ($existingSectionIndex !== false) {
                // Update the existing section
                $sections[$existingSectionIndex] = $quoteSectionData;
            } else {
                // Add the new section
                $sections->push($quoteSectionData);
            }

            // Update grand total
            $currentData['grand_total'] = $grandTotal;
            $currentData['sections'] = $sections->values()->all();

            // Save the updated JSON back to the database
            $quote->update(['json_data' => json_encode($currentData, true)]);

            return response()->json(['status' => true, 'message' => 'Data saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while updating the page data'], 500);
        }
    }

    public function removeQuoteSection(Request $request)
    {

        try {

            $pageId = $request->input('page_id');
            $sectionId = $request->input('section_id');

            // Validate inputs
            if (!$pageId || !$sectionId) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            $quote = TemplatePageData::where('template_page_id', $pageId)->first();
            if ($quote) {
                // Get the current quote details
                $quoteDetails = $quote->json_data ?? ['grand_total' => 0, 'sections' => []];

                // Filter out the section to be removed
                $updatedSections = collect($quoteDetails['sections'])
                    ->reject(fn($section) => $section['id'] === $sectionId)
                    ->values()
                    ->all();

                // Update grand total
                $quoteDetails['sections'] = $updatedSections;
                $quoteDetails['grand_total'] = collect($updatedSections)->sum(fn($s) => $s['sectionTotal']);

                // Save updated JSON to the database
                $quote->update(['json_data' => json_encode($quoteDetails, true)]);
            }

            return response()->json(['status' => true, 'message' => 'Data removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while removing section'], 400);
        }
    }

    public function updateQuoteSectionsOrdering(Request $request)
    {
        try {

            $pageId = $request->input('page_id');
            $sectionsOrder = $request->input('sections_order');

            // Validate inputs
            if (!$pageId || !$sectionsOrder || !is_array($sectionsOrder)) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            $quote = TemplatePageData::where('template_page_id', $pageId)->first();
            if ($quote) {

                // Get the current quote details
                $quoteDetails = $quote->json_data ?? ['grand_total' => 0, 'sections' => []];
                // Update the order of sections based on the input
                $updatedSections = collect($quoteDetails['sections'])->map(function ($section) use ($sectionsOrder) {
                    $sectionId = $section['id'];
                    $newOrder = array_search($sectionId, $sectionsOrder); // Get the new order index
                    if ($newOrder !== false) {
                        $section['order'] = $newOrder; // Update the order
                    }
                    return $section;
                })->sortBy('order')->values()->all();

                // Save the updated sections back to quote details
                $quoteDetails['sections'] = $updatedSections;

                // Save updated JSON to the database
                $quote->update(['json_data' => json_encode($quoteDetails, true)]);
            }

            return response()->json(['status' => true, 'message' => 'Ordering saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while update ordering'], 400);
        }
    }

    public function saveAuthorizationSectionDetails(Request $request)
    {

        try {

            $pageId = $request->input('page_id');
            $authorizationSectionData = $request->input('authorizationSection');
            $grandTotal = $request->input('grandTotal');

            // Find if the page data exists by page_id
            $authorization = TemplatePageData::where('template_page_id', $pageId)->first();

            if (!$authorization) {
                // Create a new authorization with default structure
                $authorization = TemplatePageData::create([
                    'template_page_id' => $pageId,
                    'json_data' => json_encode([
                        'authorization_sections_grand_total' => $grandTotal,
                        'sections' => []
                    ], true),
                ]);
            }


            // Retrieve the current JSON data
            $currentData = $authorization->json_data ?? [];

            // Ensure default structure for grand_total and sections
            $currentData['authorization_sections_grand_total'] = $grandTotal;
            $currentData['sections'] = $currentData['sections'] ?? [];

            $sections = collect($currentData['sections']);

            // Check if the section already exists
            $existingSectionIndex = $sections->search(fn($s) => $s['id'] === $authorizationSectionData['id']);

            if ($existingSectionIndex !== false) {
                // Update the existing section
                $sections[$existingSectionIndex] = $authorizationSectionData;
            } else {
                // Add the new section
                $sections->push($authorizationSectionData);
            }

            // Update grand total
            $currentData['authorization_sections_grand_total'] = $grandTotal;
            $currentData['sections'] = $sections->values()->all();

            // Save the updated JSON back to the database
            $authorization->update(['json_data' => json_encode($currentData, true)]);

            return response()->json(['status' => true, 'message' => 'Data saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function removeAuthorizationSection(Request $request)
    {

        try {

            $pageId = $request->input('page_id');
            $sectionId = $request->input('section_id');

            // Validate inputs
            if (!$pageId || !$sectionId) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            $authorization = TemplatePageData::where('template_page_id', $pageId)->first();
            if ($authorization) {
                // Get the current authorization details
                $authorizationDetails = $authorization->json_data ?? ['grand_total' => 0, 'sections' => []];

                // Filter out the section to be removed
                $updatedSections = collect($authorizationDetails['sections'])
                    ->reject(fn($section) => $section['id'] === $sectionId)
                    ->values()
                    ->all();

                // Update grand total
                $authorizationDetails['sections'] = $updatedSections;
                $authorizationDetails['grand_total'] = collect($updatedSections)->sum(fn($s) => $s['sectionTotal']);

                // Save updated JSON to the database
                $authorization->update(['json_data' => json_encode($authorizationDetails, true)]);
            }

            return response()->json(['status' => true, 'message' => 'Data removed successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while removing section'], 400);
        }
    }

    public function updateAuthorizationSectionsOrdering(Request $request)
    {
        try {

            $pageId = $request->input('page_id');
            $sectionsOrder = $request->input('sections_order');

            // Validate inputs
            if (!$pageId || !$sectionsOrder || !is_array($sectionsOrder)) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            $authorization = TemplatePageData::where('template_page_id', $pageId)->first();
            if ($authorization) {

                // Get the current authorization details
                $authorizationDetails = $authorization->json_data ?? ['grand_total' => 0, 'sections' => []];
                // Update the order of sections based on the input
                $updatedSections = collect($authorizationDetails['sections'])->map(function ($section) use ($sectionsOrder) {
                    $sectionId = $section['id'];
                    $newOrder = array_search($sectionId, $sectionsOrder); // Get the new order index
                    if ($newOrder !== false) {
                        $section['order'] = $newOrder; // Update the order
                    }
                    return $section;
                })->sortBy('order')->values()->all();

                // Save the updated sections back to authorization details
                $authorizationDetails['sections'] = $updatedSections;

                // Save updated JSON to the database
                $authorization->update(['json_data' => json_encode($authorizationDetails, true)]);
            }

            return response()->json(['status' => true, 'message' => 'Ordering saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while update ordering'], 400);
        }
    }

    public function saveRepairibility(Request $request)
    {
        try {
            $pageId = $request->input('page_id');
            $repairabilityCompatibilitySection = $request->input('repairabilityCompatibilitySection');
            $items = $request->input('items', []);

            if (!$pageId || !$repairabilityCompatibilitySection || !is_array($items)) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            $processedItems = !empty($items) ? array_map(function ($item) {
                return [
                    'id' => str_replace('item_', '', $item['id']),
                    'order' => $item['order'],
                    'content' => strip_tags($item['content'], '<p><b><i><u><br>'),
                    'image' => $item['image'] ?? null,
                ];
            }, $items) : [];

            $quote = TemplatePageData::where('template_page_id', $pageId)->first();

            if (!$quote) {
                $quote = TemplatePageData::create([
                    'template_page_id' => $pageId,
                    'json_data' => json_encode(['comparision_sections' => []], true),
                ]);
            }

            $quoteDetails = $quote->json_data
                ? (is_array($quote->json_data) ? $quote->json_data : json_decode($quote->json_data, true))
                : ['comparision_sections' => []];

            $sectionIndex = collect($quoteDetails['comparision_sections'])->search(function ($section) use ($repairabilityCompatibilitySection) {
                return $section['id'] === $repairabilityCompatibilitySection['id'];
            });

            if ($sectionIndex !== false) {
                // Update existing section
                $quoteDetails['comparision_sections'][$sectionIndex] = [
                    'id' => $repairabilityCompatibilitySection['id'],
                    'title' => $repairabilityCompatibilitySection['title'],
                    'order' => $repairabilityCompatibilitySection['sectionOrder'],
                    'items' => $processedItems ?: null
                ];
            } else {
                // Add new section
                $quoteDetails['comparision_sections'][] = [
                    'id' => $repairabilityCompatibilitySection['id'],
                    'title' => $repairabilityCompatibilitySection['title'],
                    'order' => $repairabilityCompatibilitySection['sectionOrder'],
                    'items' => $processedItems ?: null
                ];
            }

            $quote->update(['json_data' => json_encode($quoteDetails, true)]);

            return response()->json(['status' => true, 'message' => 'Data saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getmessage()], 500);
        }
    }

    public function updateRepairibilitySectionsOrdering(Request $request)
    {
        try {
            $pageId = $request->input('page_id');
            $sectionsOrder = $request->input('sections_order');

            if (!$pageId || !$sectionsOrder || !is_array($sectionsOrder)) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            $repairibility = TemplatePageData::where('template_page_id', $pageId)->first();
            if ($repairibility) {
                $repairibilityDetails = is_array($repairibility->json_data) ? $repairibility->json_data : json_decode($repairibility->json_data, true);

                if (isset($repairibilityDetails['comparision_sections'])) {
                    $updatedSections = collect($repairibilityDetails['comparision_sections'])->map(function ($section) use ($sectionsOrder) {
                        $sectionId = $section['id'];

                        $newOrder = array_search($sectionId, $sectionsOrder);
                        if ($newOrder !== false) {
                            $section['order'] = (string)$newOrder;
                        }
                        return $section;
                    });

                    $updatedSections = $updatedSections->sortBy(function ($section) {
                        return (string)$section['order'];
                    })->values()->all();

                    $repairibilityDetails['comparision_sections'] = $updatedSections;

                    $repairibility->json_data = json_encode($repairibilityDetails, JSON_PRETTY_PRINT);
                    $repairibility->save();
                }
            }

            return response()->json(['status' => true, 'message' => 'Ordering saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while updating ordering', 'error' => $e->getMessage()], 400);
        }
    }
}
