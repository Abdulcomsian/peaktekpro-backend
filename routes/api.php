<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CocController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobLogController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CompanyJobController;
use App\Http\Controllers\CarrierScopeController;
use App\Http\Controllers\QcInspectionController;
use App\Http\Controllers\ReadyToBuildController;
use App\Http\Controllers\MaterialOrderController;
use App\Http\Controllers\ProjectDesignController;
use App\Http\Controllers\RoofComponentController;
use App\Http\Controllers\SubContractorController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\XactimateReportController;
use App\Http\Controllers\CustomerAgreementController;
use App\Http\Controllers\ProjectDesignQuoteController;
use App\Http\Controllers\ProjectDesignAuthorizationController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
//Forgot Password Api
Route::post('send/otp', [ForgotPasswordController::class, 'sendOTP']);
Route::post('verify/otp', [ForgotPasswordController::class, 'verifyOTP']);
Route::post('change/password', [ForgotPasswordController::class, 'changePassword']);

Route::middleware(['auth:sanctum', 'token.expiration'])->group(function(){
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('check/old-password', [AuthController::class, 'checkOldPassword']);
    Route::post('update/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    //Jobs Api's
    Route::post('create-job', [CompanyJobController::class, 'createJob']);
    Route::get('get/jobs', [CompanyJobController::class, 'getAllJobs']);
    Route::get('get-single/job/{id}', [CompanyJobController::class, 'getSingleJob']);
    Route::post('update/job-summary/{id}', [CompanyJobController::class, 'updateJobSummary']);
    Route::get('get/job-summary/{id}', [CompanyJobController::class, 'getJobSummary']);
    Route::post('update/job-content/{id}', [CompanyJobController::class, 'updateJobContent']);
    Route::get('get/job-content/{id}', [CompanyJobController::class, 'getJobContent']);
    //Adjustor Meeting Api
    Route::post('create/adjustor-meeting/{jobId}', [MeetingController::class, 'createAdjustorMeeting']);
    Route::post('update/adjustor-meeting-media/{jobId}', [MeetingController::class, 'updateAdjustorMeetingMedia']);
    Route::post('update-status/adjustor-meeting/{id}', [MeetingController::class, 'updateAdjustorMeetingStatus']);
    Route::get('get/adjustor-meeting/{jobId}', [MeetingController::class, 'getAdjustorMeeting']);
    //Overturn Meeting Api
    Route::post('create/overturn-meeting/{jobId}', [MeetingController::class, 'createOverturnMeeting']);
    Route::post('update/overturn-meeting-media/{jobId}', [MeetingController::class, 'updateOverturnMeetingMedia']);
    Route::get('get/overturn-meeting/{jobId}', [MeetingController::class, 'getOverturnMeeting']);
    Route::post('update-status/overturn-meeting/{id}', [MeetingController::class, 'updateOverturnMeetingStatus']);
    //Customer Agreements Api's
    Route::post('customer-agreement/{jobId}', [CustomerAgreementController::class, 'customerAgreement']);
    Route::get('get/customer-agreement/{id}', [CustomerAgreementController::class, 'getCustomerAgreement']);
    Route::post('update/customer-agreement/{id}', [CustomerAgreementController::class, 'updateCustomerAgreement']);
    Route::post('sign-by-email/{id}', [CustomerAgreementController::class, 'signCustomerAgreementByEmail']);
    Route::get('check/customer-agreement/{jobId}', [CustomerAgreementController::class, 'checkCustomerAgreement']);
    //Material Order Api's
    Route::post('material-order/{jobId}', [MaterialOrderController::class, 'materialOrder']);
    Route::get('get/material-order/{id}', [MaterialOrderController::class, 'getMaterialOrder']);
    Route::post('update/material-order/{id}', [MaterialOrderController::class, 'updateMaterialOrder']);
    Route::get('check/material-order/{jobId}', [MaterialOrderController::class, 'checkMaterialOrder']);
    Route::post('material-order/email/{jobId}', [MaterialOrderController::class, 'MaterialOrderEmail']);
    //Project Design Api's
    Route::post('update/project-design-page-status/{jobId}', [ProjectDesignController::class, 'updateProjectDesignPageStatus']);
    //Project Design Title
    Route::post('store/project-design/title/{jobId}', [ProjectDesignController::class, 'storeProjectDesignTitle']);
    Route::get('get/project-design/title/{jobId}', [ProjectDesignController::class, 'getProjectDesignTitle']);
    //Project Design Introduction
    Route::post('store/project-design/introduction/{jobId}', [ProjectDesignController::class, 'storeProjectDesignIntroduction']);
    Route::get('get/project-design/introduction/{jobId}', [ProjectDesignController::class, 'getProjectDesignIntroduction']);
    //Project Design Inspection
    Route::post('store/project-design/inspection/{jobId}', [ProjectDesignController::class, 'storeProjectDesignInspection']);
    Route::get('get/project-design/inspection/{jobId}', [ProjectDesignController::class, 'getProjectDesignInspection']);
    Route::get('delete/project-design/inspection/{id}', [ProjectDesignController::class, 'deleteProjectDesignInspection']);
    //Project Design Quote
    Route::post('store/project-design/quote/{jobId}', [ProjectDesignQuoteController::class, 'storeProjectDesignQuote']);
    Route::get('get/project-design/quote/{jobId}', [ProjectDesignQuoteController::class, 'getProjectDesignQuote']);
    Route::post('section/update-status/{jobId}', [ProjectDesignQuoteController::class, 'updateSectionStatus']);
    //Project Design Authorization
    Route::post('store/project-design/authorization/{jobId}', [ProjectDesignAuthorizationController::class, 'storeProjectDesignAuthorization']);
    Route::get('get/project-design/authorization/{jobId}', [ProjectDesignAuthorizationController::class, 'getProjectDesignAuthorization']);
    //Project Design Payment Schedule
    Route::post('store/payment-schedule/{jobId}', [PaymentScheduleController::class, 'storePaymentSchedule']);
    Route::get('get/payment-schedule/{jobId}', [PaymentScheduleController::class, 'getPaymentSchedule']);
    //Roof Component Generic
    Route::post('store/roof-component/{jobId}', [RoofComponentController::class, 'storeRoofComponent']);
    Route::get('get/roof-component/{jobId}', [RoofComponentController::class, 'getRoofComponent']);
    //Xactimate Report
    Route::post('store/xactimate-report/{jobId}', [XactimateReportController::class, 'storeXactimateReport']);
    Route::get('get/xactimate-report/{jobId}', [XactimateReportController::class, 'getXactimateReport']);
    //Job Log Api's
    Route::post('store/job-log/{jobId}', [JobLogController::class, 'storeJobLog']);
    Route::get('get/job-log/{jobId}', [JobLogController::class, 'getJobLog']);
    //QC Inspection Api's
    Route::post('store/qc-inspection/{jobId}', [QcInspectionController::class, 'storeQcInspection']);
    Route::get('get/qc-inspection/{jobId}', [QcInspectionController::class, 'getQcInspection']);
    //Certificate Of Completion Api's
    Route::post('store/coc/{jobId}', [CocController::class, 'storeCoc']);
    Route::get('get/coc/{jobId}', [CocController::class, 'getCoc']);
    //Ready To Build Api's
    Route::post('store/ready-to-build/{jobId}', [ReadyToBuildController::class, 'storeReadyToBuild']);
    Route::get('get/ready-to-build/{jobId}', [ReadyToBuildController::class, 'getReadyToBuild']);
    //Supplier Api's
    Route::post('store/supplier/{jobId}', [SupplierController::class, 'storeSupplier']);
    Route::get('get/suppliers/{jobId}', [SupplierController::class, 'getSuppliers']);
    //Sub Contractor Api's
    Route::post('store/sub-contractor/{jobId}', [SubContractorController::class, 'storeSubContractor']);
    Route::get('get/sub-contractors/{jobId}', [SubContractorController::class, 'getSubContractors']);
    //Carrier Scope Api's
    Route::post('store/carrier-scope/{jobId}', [CarrierScopeController::class, 'storeCarrierScope']);
    Route::get('get/carrier-scope/{jobId}', [CarrierScopeController::class, 'getCarrierScope']);
});
