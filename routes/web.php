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
Route::post('/update-page-ordering', [TemplateController::class, 'updatePageOrdering'])->name('templates.page-ordering.update');

Route::get('/test', function(){
    $job = \App\Models\CompanyJob::find(4);
    $quotes = \App\Models\ProjectDesignQuote::where('company_job_id', $job->id)->with('sections','sections.items')->first();
   return view('pdf.design-meeting', ['job' => $job, 'quotes' => $quotes]);
});
