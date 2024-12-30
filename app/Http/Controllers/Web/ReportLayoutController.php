<?php

namespace App\Http\Controllers\Web;

use App\Models\Template;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Storage;
use App\Models\{Page, Report, ReportPageData, ReportPage};
use App\Http\Requests\Report\{StoreRequest, UpdateRequest};

class ReportLayoutController extends Controller
{
    public function index(Request $request)
    {
        try {
            $reports = Report::paginate(5);
            return view('reports_layout.index', compact('reports'));
        } catch (\Exception $e) {
            abort(500, 'An error occurred while fetching reports.');
        }
    }

    public function create()
    {
        dd('create');
        $pages = Page::all();

        return view('reports_layout.create', compact('pages'));
    }

    public function store(StoreRequest $request)
    {
        try {
            $report = Report::create([
                'title' => $request->title
            ]);
            // sync pages with report pages
            $pages = Page::all();
            $pages->each(function ($page, $index) use ($report) {
                $report->reportPages()->create([
                    'name' => $page->name,
                    'slug' => $page->slug,
                    'order_no' => $index
                ]);
            });

            return response()->json([
                'status' => true,
                'message' => 'Report created successfully',
                'redirect_to' => route('reports.edit', $report->id)
            ], 200);
        } catch (\Exception $e) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $e->getMessage()
                ]
            );
        }
    }

    public function edit($reportId)
    {
        try {
            $report = Report::with('reportPages.pageData')->findOrFail($reportId);
            $templates = Template::latest()->get();
            return view('reports_layout.edit', compact('report','templates'));
        } catch (\Exception $e) {
            return redirect()->route('reports.index')->with('error', 'Report not found');
        }
    }

    public function updateTitle(UpdateRequest $request, $reportId)
    {
        try {
            $report = Report::findOrFail($reportId);
            $report->title = $request->title;

            $report->save();

            return response()->json([
                'status' => true,
                'message' => 'Title updated successfully',
            ], 200);
        } catch (\Exception $e) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $e->getMessage()
                ]
            );
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $report = Report::findOrFail($id);
            $newStatus = $request->input('status', 'draft');
            $report->status = $newStatus;
            $report->save();

            return response()->json([
                'status' => true,
                'message' => 'Status updated successfully',
                'response' => $report
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function downloadPdf($id)
    {
        $report = Report::findOrFail($id)->getAllReportData();

        if (!$report) {
            return response()->json(['status' => false, 'message' => 'Report not found'], 404);
        }

        // Generate the PDF
        $pdf = SnappyPdf::loadView('pdf.report-pdf', ['report' => $report]);
        // Return the PDF for download
        return $pdf->download("report-{$id}.pdf");
    }

    public function copyTemplate(Request $request)
    {
        // Validate the input
         $request->validate([
            'template_id' => 'required|exists:templates,id',
            'report_id' => 'required|exists:reports,id',
        ]);

        $templateId = $request->template_id;
        $reportId = $request->report_id;
    
        // Fetch the template and report
        $template = Template::with('templatePages.pageData')->findOrFail($templateId);
        $report = Report::findOrFail($reportId);
    
        // Begin database transaction
        DB::beginTransaction();
    
        try {
            // Remove existing report pages and their data
            $report->reportPages()->each(function ($reportPage) {
                $reportPage->pageData()->delete(); // Delete associated page data
                $reportPage->delete(); // Delete the report page
            });
    
            // Copy template pages to report pages
            foreach ($template->templatePages as $templatePage) {
                // Create a new ReportPage
                $reportPage = $report->reportPages()->create([
                    'name' => $templatePage->name,
                    'slug' => $templatePage->slug,
                    'is_active' => $templatePage->is_active,
                    'order_no' => $templatePage->order_no,
                ]);
    
                // Copy the template page data to the report page data
                if ($templatePage->pageData) {
                    $reportPage->pageData()->create([
                        'json_data' => json_encode($templatePage->pageData->json_data),
                    ]);
                }
            }
    
            // Commit the transaction
            DB::commit();
    
            return response()->json([
                'message' => 'Template copied successfully!',
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction if there is an error
            DB::rollBack();
    
            return response()->json([
                'message' => 'An error occurred while copying the template.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }    


    public function destroy($reportId)
    {
        try {

            $report = Report::findOrFail($reportId);
            $report->delete();

            return response()->json([
                'status' => true,
                'message' => 'Report deleted successfully',
            ], 200);
        } catch (\Exception $e) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $e->getMessage()
                ]
            );
        }
    }

    public function updateReportPagesOrdering(Request $request, $reportId)
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

                ReportPage::where('id', $id)->update(['order_no' => $position]);
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
                    'errors' => $e->getMessage()
                ]
            );
        }
    }

    public function createPage(Request $request, $reportId)
    {
        try {

            $lastReportPage = ReportPage::where('report_id', $reportId)->orderBy('order_no', 'desc')->first();

            $reportPage = ReportPage::create([
                'report_id' => $reportId,
                'name' => $request->title,
                'order_no' => $lastReportPage->order_no + 1
            ]);

            $reportPage = ReportPage::findOrFail($reportPage->id);

            return response()->json([
                'status' => true,
                'message' => 'Page created successfully',
                'page' => $reportPage
            ], 200);
        } catch (\Exception $e) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $e->getMessage()
                ]
            );
        }
    }

    // Method to update page status
    public function updatePageStatus(Request $request, $pageId)
    {

        try {

            $page = ReportPage::findOrFail($pageId);
            $page->is_active = $request->status;
            $page->save();

            return response()->json(['status' => true, 'message' => 'Page status updated successfully'], 200);
        } catch (\Exception $e) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $e->getMessage()
                ]
            );
        }
    }

    public function updatePageTitle(Request $request, $pageId)
    {

        try {

            $page = ReportPage::findOrFail($pageId);
            $page->name = $request->name;
            $page->save();

            return response()->json(['status' => true, 'message' => 'Page name updated successfully'], 200);
        } catch (\Exception $e) {

            return response()->json(
                [
                    'status' => false,
                    'message' => 'Something went wrong',
                    'errors' => $e->getMessage()
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
            $report = ReportPageData::where('report_page_id', $pageId)->first();

            if ($report) {

                $existingJsonData = $report->json_data;

                // Merge existing data with the new data from the request
                $updatedData = array_merge($existingJsonData, $jsonData);

                // Save the updated json_data
                $report->json_data = json_encode($updatedData);
                $report->save();

                return response()->json(['status' => true, 'message' => 'Data updated successfully']);
            } else {
                // If the $report does not exist, create a new one with the json_data
                $newReport = new ReportPageData();
                $newReport->report_page_id = $pageId;
                $newReport->json_data = json_encode($jsonData);
                $newReport->save();

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
                $report = ReportPageData::where('report_page_id', $pageId)->first();

                $file = $request->file('file');
                // Generate unique filename
                $filename = time() . '_' . $file->getClientOriginalName();

                // Store the file in 'storage/app/public/uploads'
                $path = $file->storeAs('report-files/' . $folder, $filename, 'public');

                // Get the file size
                $fileSize = $file->getSize();  // Size in bytes

                // store file data
                $jsonData[$type] = [
                    'file_name' => $filename,
                    'path' => $path,
                    'size' => $fileSize
                ];

                if ($report) {

                    $existingJsonData = $report->json_data;

                    // Merge existing data with the new data from the request
                    $updatedData = array_merge($existingJsonData, $jsonData);

                    // Save the updated json_data
                    $report->json_data = json_encode($updatedData);
                    $report->save();
                } else {
                    // If the $report does not exist, create a new one with the json_data
                    $newReport = new ReportPageData();
                    $newReport->report_page_id = $pageId;
                    $newReport->json_data = json_encode($jsonData);
                    $newReport->save();
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
            $report = ReportPageData::where('report_page_id', $pageId)->firstOrFail();

            if ($report) {
                $existingJsonData = $report->json_data;
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
                $report->json_data = json_encode($existingJsonData);

                // Save the updated report data back to the database
                $report->save();


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

                // Find or create the report record by page_id
                $report = ReportPageData::firstOrCreate(
                    ['report_page_id' => $pageId],
                    ['json_data' => json_encode([])]
                );

                $existingJsonData = $report->json_data;

                // Loop through each file in the files array
                $files = $request->file('file');
                $fileData = [];

                foreach ($files as $file) {
                    // Generate unique filename and store the file
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('report-files/' . $folder, $filename, 'public');

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
                $report->json_data = json_encode($existingJsonData);
                $report->save();

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
            $quote = ReportPageData::where('report_page_id', $pageId)->first();

            if (!$quote) {
                // Create a new quote with default structure
                $quote = ReportPageData::create([
                    'report_page_id' => $pageId,
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
            dd($e);
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

            $quote = ReportPageData::where('report_page_id', $pageId)->first();
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

            $quote = ReportPageData::where('report_page_id', $pageId)->first();
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
            $authorization = ReportPageData::where('report_page_id', $pageId)->first();

            if (!$authorization) {
                // Create a new authorization with default structure
                $authorization = ReportPageData::create([
                    'report_page_id' => $pageId,
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

            $authorization = ReportPageData::where('report_page_id', $pageId)->first();
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

            $authorization = ReportPageData::where('report_page_id', $pageId)->first();
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
            // Get input data from request
            $pageId = $request->input('page_id');
            $repairabilityCompatibilitySection = $request->input('repairabilityCompatibilitySection');
            $items = $request->input('items', []);

            // Validate inputs
            if (!$pageId || !$repairabilityCompatibilitySection || !is_array($items)) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            // Get or create repairability data
            $repairibility = ReportPageData::where('report_page_id', $pageId)->first();

            if (!$repairibility) {
                $repairibility = ReportPageData::create([
                    'report_page_id' => $pageId,
                    'json_data' => json_encode(['comparision_sections' => []], true),
                ]);
            }

            // Decode existing JSON data
            $repairibilityDetails = $repairibility->json_data
                ? (is_array($repairibility->json_data) ? $repairibility->json_data : json_decode($repairibility->json_data, true))
                : ['comparision_sections' => []];

            // Search for the section
            $sectionIndex = collect($repairibilityDetails['comparision_sections'])->search(function ($section) use ($repairabilityCompatibilitySection) {
                return $section['id'] === $repairabilityCompatibilitySection['id'];
            });

            $processedItems = array_map(function ($item) use ($repairibilityDetails, $request) {
                $imageData = null;

                // Find the existing item in the database by ID
                $existingItem = collect($repairibilityDetails['comparision_sections'])
                    ->flatMap(function ($section) {
                        return $section['items'];
                    })
                    ->firstWhere('id', $item['id']);

                // Check if the item already has an image, and whether the new image should be updated
                if (isset($item['image']) && strpos($item['image'], 'data:image') === 0) {
                    // Only update image if the existing image is null or does not exist
                    if (empty($existingItem['image'])) {
                        // Extract and decode base64 image string
                        $imageData = explode(',', $item['image'])[1];
                        $decodedImage = base64_decode($imageData);

                        // Generate a unique filename for the image
                        $filename = time() . '_' . uniqid() . '.png';
                        $path = 'repairability-photos/' . $filename;

                        // Save the image to storage
                        Storage::disk('public')->put($path, $decodedImage);

                        // Prepare image data (file name, path, and size)
                        $imageData = [
                            'file_name' => $filename,
                            'path' => asset('storage/' . $path),
                            'size' => Storage::disk('public')->size($path),
                        ];
                    } else {
                        // Keep the existing image if it's already set
                        $imageData = $existingItem['image'];
                    }
                }

                // Return processed item with image data (if available)
                return [
                    'id' => $item['id'],
                    'order' => $item['order'],
                    'content' => strip_tags($item['content'], '<p><b><i><u><br>'),
                    'image' => $imageData,  // Save the image object if it's new or changed
                ];
            }, $items);

            // Update or add new section
            if ($sectionIndex !== false) {
                // Update items in the section, preserving image data for non-matching items
                $updatedItems = collect($repairibilityDetails['comparision_sections'][$sectionIndex]['items'])->map(function ($existingItem) use ($processedItems) {
                    $matchingItem = collect($processedItems)->firstWhere('id', $existingItem['id']);

                    if ($matchingItem) {
                        // If the item has a new image, update it; otherwise, keep the existing image
                        $existingItem['image'] = $matchingItem['image'] ?: $existingItem['image'];
                        $existingItem['content'] = $matchingItem['content'] ?: $existingItem['content'];
                        $existingItem['order'] = $matchingItem['order'] ?: $existingItem['order'];
                    }

                    return $existingItem;
                })->toArray();

                // Add any new items that don't exist in the section
                $newItems = collect($processedItems)->filter(function ($processedItem) use ($repairibilityDetails, $sectionIndex) {
                    return !collect($repairibilityDetails['comparision_sections'][$sectionIndex]['items'])->contains('id', $processedItem['id']);
                })->toArray();

                // Merge updated and new items
                $updatedItems = array_merge($updatedItems, $newItems);

                // Update section with new or unchanged items
                $repairibilityDetails['comparision_sections'][$sectionIndex] = [
                    'id' => $repairabilityCompatibilitySection['id'],
                    'title' => $repairabilityCompatibilitySection['title'],
                    'order' => $repairabilityCompatibilitySection['sectionOrder'],
                    'items' => $updatedItems ?: null,
                ];
            } else {
                // Add new section with processed items
                $repairibilityDetails['comparision_sections'][] = [
                    'id' => $repairabilityCompatibilitySection['id'],
                    'title' => $repairabilityCompatibilitySection['title'],
                    'order' => $repairabilityCompatibilitySection['sectionOrder'],
                    'items' => $processedItems ?: null,
                ];
            }

            // Save updated data to the database
            $repairibility->update(['json_data' => json_encode($repairibilityDetails, true)]);

            return response()->json(['status' => true, 'message' => 'Data saved successfully']);
        } catch (\Exception $e) {
            // Return error message in case of exception
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
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

            $repairibility = ReportPageData::where('report_page_id', $pageId)->first();
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

    public function updateRepairibilityItemsSectionsOrdering(Request $request)
    {
        try {
            $pageId = $request->input('page_id');
            $items = $request->input('items', []);

            // Validate page_id and items
            if (!$pageId || !is_array($items)) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            $repairibility = ReportPageData::where('report_page_id', $pageId)->first();

            if (!$repairibility) {
                return response()->json(['status' => false, 'message' => 'Page data not found.'], 404);
            }

            $repairibilityDetails = $repairibility->json_data
                ? (is_array($repairibility->json_data) ? $repairibility->json_data : json_decode($repairibility->json_data, true))
                : ['comparision_sections' => []];

            foreach ($repairibilityDetails['comparision_sections'] as &$section) {
                if (!empty($section['items'])) {
                    foreach ($section['items'] as &$item) {
                        $incomingItem = collect($items)->firstWhere('id', $item['id']);
                        if ($incomingItem) {
                            $item['order'] = $incomingItem['order'];
                        }
                    }
                    // Sort items by order
                    $section['items'] = collect($section['items'])->sortBy('order')->values()->toArray();
                }
            }

            $repairibility->update(['json_data' => json_encode($repairibilityDetails, true)]);

            return response()->json(['status' => true, 'message' => 'Item orders updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function removeRepairibilitySection(Request $request)
    {
        try {
            $pageId = $request->input('page_id');
            $sectionId = $request->input('section_id');
            $itemId = $request->input('item_id');

            // Validate inputs
            if (!$pageId || (!$sectionId && !$itemId)) {
                return response()->json(['status' => false, 'message' => 'Invalid inputs provided.'], 400);
            }

            // Retrieve the report page data
            $repairibility = ReportPageData::where('report_page_id', $pageId)->first();

            if (!$repairibility) {
                return response()->json(['status' => false, 'message' => 'Page not found.'], 404);
            }

            // Decode the JSON data
            $jsonData = $repairibility->json_data;

            if (is_string($jsonData)) {
                $jsonData = json_decode($jsonData, true); // Decode if it's a string
            }

            // Check if the 'comparision_sections' exist in the JSON data
            if ($sectionId) {
                if (isset($jsonData['comparision_sections']) && is_array($jsonData['comparision_sections'])) {
                    // Filter out the section with the given section_id
                    $jsonData['comparision_sections'] = array_filter($jsonData['comparision_sections'], function ($section) use ($sectionId) {
                        return $section['id'] !== $sectionId;
                    });

                    // Re-index the array to ensure keys are reset after removal
                    $jsonData['comparision_sections'] = array_values($jsonData['comparision_sections']);

                    // Update the report page data with the modified JSON
                    $repairibility->update(['json_data' => json_encode($jsonData, JSON_PRETTY_PRINT)]);

                    return response()->json(['status' => true, 'message' => 'Section removed successfully']);
                }
            } elseif ($itemId) {
                // If an itemId is provided, remove the item from the sections
                if (isset($jsonData['comparision_sections']) && is_array($jsonData['comparision_sections'])) {
                    // Iterate through each section to find the item by item_id
                    foreach ($jsonData['comparision_sections'] as &$section) {
                        if (isset($section['items']) && is_array($section['items'])) {
                            // Filter out the item with the given item_id
                            $section['items'] = array_filter($section['items'], function ($item) use ($itemId) {
                                return $item['id'] !== $itemId;
                            });

                            // Re-index the array to ensure keys are reset after removal
                            $section['items'] = array_values($section['items']);
                        }
                    }

                    // Update the report page data with the modified JSON
                    $repairibility->update(['json_data' => json_encode($jsonData, JSON_PRETTY_PRINT)]);

                    return response()->json(['status' => true, 'message' => 'Item removed successfully']);
                }
            }

            return response()->json(['status' => false, 'message' => 'Section ID not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'An error occurred while removing section: ' . $e->getMessage()], 400);
        }
    }

    public function deleteRepairabilityPageFile(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'page_id' => 'required|integer|exists:report_page_data,report_page_id', // Ensure `report_page_id` exists in the database
            'item_id' => 'required|string', // Ensure item_id is present
        ]);

        $pageId = $validated['page_id'];
        $itemId = $validated['item_id'];

        // Fetch the report page data
        $repairibility = ReportPageData::where('report_page_id', $pageId)->firstOrFail();

        // Decode JSON data
        $repairibilityDetails = $repairibility->json_data
            ? (is_array($repairibility->json_data) ? $repairibility->json_data : json_decode($repairibility->json_data, true))
            : ['comparision_sections' => []];

        // Initialize a flag to track if the item was deleted
        $itemDeleted = false;

        // Iterate over comparison sections to find and delete the item
        foreach ($repairibilityDetails['comparision_sections'] as &$section) {
            if (!empty($section['items'])) {
                foreach ($section['items'] as &$item) {
                    if ($item['id'] === $itemId) {
                        // Delete file from storage
                        if (isset($item['image']['path'])) {
                            // Extract the relative file path
                            $filePath = parse_url($item['image']['path'], PHP_URL_PATH); // Get the path part
                            $filePath = str_replace('/storage', '', $filePath); // Remove '/storage' prefix

                            // Delete file if it exists in the storage
                            if (Storage::disk('public')->exists($filePath)) {
                                Storage::disk('public')->delete($filePath);
                            }
                        }

                        // Remove the image field from the item
                        $item['image'] = null;

                        $itemDeleted = true;
                        break;
                    }
                }

                // Sort remaining items by 'order' and reindex
                $section['items'] = collect($section['items'])->sortBy('order')->values()->toArray();
            }

            // Stop processing further sections if the item has been deleted
            if ($itemDeleted) {
                break;
            }
        }

        // Return error if no item was found
        if (!$itemDeleted) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Save the updated JSON data back to the database
        $repairibility->update(['json_data' => json_encode($repairibilityDetails)]);

        return response()->json(['success' => 'File deleted successfully']);
    }
}
