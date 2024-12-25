<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\{ReactAuthController, TemplateController, ReportLayoutController};


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('auth',ReactAuthController::class)->middleware('check.react.auth');
// templates
// Route::middleware(['check.react.auth','check.user.role'])->group(function () {

    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/create', [TemplateController::class, 'create'])->name('templates.create');
    Route::post('/templates/store', [TemplateController::class, 'store'])->name('templates.store');
    Route::delete('/templates/{id}', [TemplateController::class, 'destroy'])->name('templates.destroy');
    Route::get('/templates/edit/{id}', [TemplateController::class, 'edit'])->name('templates.edit');
    Route::put('/templates/update-title/{id}', [TemplateController::class, 'updateTitle'])->name('templates.update.title');

// });


Route::post('/update-page-ordering/{id}', [TemplateController::class, 'updateTemplatePagesOrdering'])->name('templates.page-ordering.update');
Route::post('/templates/create-page/{id}', [TemplateController::class, 'createPage'])->name('templates.create-page');
Route::patch('/templates/update-page-status/{pageId}', [TemplateController::class, 'updatePageStatus'])->name('templates.update-page.status');
Route::put('/templates/page-title/{id}', [TemplateController::class, 'updatePageTitle'])->name('templates.update.page-title');
Route::post('/templates/page/save-data', [TemplateController::class, 'savePageData'])->name('templates.page.save-data');
Route::post('/templates/page/save-file', [TemplateController::class, 'savePageFile'])->name('templates.page.save-file');
Route::delete('/templates/page/delete-file', [TemplateController::class, 'deletePageFile'])->name('templates.page.delete-file');
Route::delete('/templates/page/repairability/delete-file', [TemplateController::class, 'deleteRepairabilityPageFile'])->name('templates.page.repairability.delete-file');
Route::post('/templates/page/save-files', [TemplateController::class, 'savePageMultipleFiles'])->name('templates.page.save-multiple-files');

// repariablity-combatibility
Route::post('/templates/page/repariablity-combatibility/save', [TemplateController::class, 'saveRepairibility'])->name('templates.repariablity-combatibility.update');
Route::delete('/templates/page/repariablity/destroy', [TemplateController::class, 'removeRepairibilitySection'])->name('template.repariablity.remove-section');
Route::post('/templates/page/repariablity-combatibility-items/ordering', [TemplateController::class, 'updateRepairibilityItemsSectionsOrdering'])->name('templates.page.repariablity-combatibility-items-ordering.update');
Route::post('/templates/page/repariablity-combatibility/ordering', [TemplateController::class, 'updateRepairibilitySectionsOrdering'])->name('templates.page.repariablity-combatibility-ordering.update');

// quote
Route::post('/templates/page/quote-section/save', [TemplateController::class, 'saveQuoteSectionDetails'])->name('templates.quote-section.update');
Route::delete('/templates/page/quote-section/destroy', [TemplateController::class, 'removeQuoteSection'])->name('template.quote.remove-section');
Route::post('/templates/page/quote-section/ordering', [TemplateController::class, 'updateQuoteSectionsOrdering'])->name('templates.page.quote-sections-ordering.update');
// authorization
Route::post('/templates/page/authorization-section/save', [TemplateController::class, 'saveAuthorizationSectionDetails'])->name('templates.authorization-section.update');
Route::delete('/templates/page/authorization-section/destroy', [TemplateController::class, 'removeAuthorizationSection'])->name('template.authorization.remove-section');
Route::post('/templates/page/authorization-section/ordering', [TemplateController::class, 'updateAuthorizationSectionsOrdering'])->name('templates.page.authorization-sections-ordering.update');




// repairibility assessment
Route::post('/templates/repairibility-assessment', function () {
    return response()->json(['url' => asset('assets/pdf_header.png')], 200);
})->name('templates-repairibility-image');

// reports
Route::get('/reports', [ReportLayoutController::class, 'index'])->name('reports.index');
Route::post('/reports/store', [ReportLayoutController::class, 'store'])->name('reports.store');
Route::get('/reports/edit/{id}', [ReportLayoutController::class, 'edit'])->name('reports.edit');



Route::get('/test', function(){
    $job = \App\Models\CompanyJob::find(4);
    $quotes = \App\Models\ProjectDesignQuote::where('company_job_id', $job->id)->with('sections','sections.items')->first();
   return view('pdf.design-meeting', ['job' => $job, 'quotes' => $quotes]);
});
