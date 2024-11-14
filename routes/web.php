<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\TemplateController;

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

Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
Route::get('/templates/create', [TemplateController::class, 'create'])->name('templates.create');
Route::post('/templates/store', [TemplateController::class, 'store'])->name('templates.store');
Route::get('/templates/edit/{id}', [TemplateController::class, 'edit'])->name('templates.edit');
Route::put('/templates/update-title/{id}', [TemplateController::class, 'updateTitle'])->name('templates.update.title');
Route::post('/update-page-ordering/{id}', [TemplateController::class, 'updateTemplatePagesOrdering'])->name('templates.page-ordering.update');

Route::post('/templates/create-page/{id}', [TemplateController::class, 'createPage'])->name('templates.create-page');
Route::patch('/templates/update-page-status/{pageId}', [TemplateController::class, 'updatePageStatus'])->name('templates.update-page.status');

Route::get('/test', function(){
    $job = \App\Models\CompanyJob::find(4);
    $quotes = \App\Models\ProjectDesignQuote::where('company_job_id', $job->id)->with('sections','sections.items')->first();
   return view('pdf.design-meeting', ['job' => $job, 'quotes' => $quotes]);
});
