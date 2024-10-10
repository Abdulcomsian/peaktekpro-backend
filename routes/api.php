<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CocController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobLogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WonClosedController;
use App\Http\Controllers\CompanyJobController;
use App\Http\Controllers\InprogressController;
use App\Http\Controllers\CarrierScopeController;
use App\Http\Controllers\FinalPaymentController;
use App\Http\Controllers\QcInspectionController;
use App\Http\Controllers\ReadyToBuildController;
use App\Http\Controllers\ReadyToCloseController;
use App\Http\Controllers\BuildCompleteController;
use App\Http\Controllers\MaterialOrderController;
use App\Http\Controllers\ProjectDesignController;
use App\Http\Controllers\RoofComponentController;
use App\Http\Controllers\SubContractorController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\PaymentScheduleController;
use App\Http\Controllers\XactimateReportController;
use App\Http\Controllers\EstimatePreparedController;
use App\Http\Controllers\TermAndConditionController;
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
//Customer Signature Api
Route::get('get/sign/customer-agreement/{jobId}', [CustomerAgreementController::class, 'getSignCustomerAgreement']);
Route::post('sign/customer/{id}', [CustomerAgreementController::class, 'signCustomerByEmail']);

Route::middleware(['auth:sanctum', 'token.expiration'])->group(function(){

    //Dashbaord Api's
    Route::get('dashboard-stats', [CompanyJobController::class, 'dashboardStats']);
    Route::post('dashboard-stats/detail', [CompanyJobController::class, 'dashboardStatsDetail']);
    //Api for creating different users
    Route::post('create/user', [AuthController::class, 'createUser']);

    //Api for creating Users rr
    Route::post('add/user', [UserController::class, 'addUser']);

    //Company Api's
    Route::post('create/company', [CompanyController::class, 'createCompany']);
    Route::get('get/company/{id}', [CompanyController::class, 'getCompany']);
    Route::post('update/company/{id}', [CompanyController::class, 'updateCompany']);
    Route::get('get/company-users', [CompanyController::class, 'getCompanyUsers']);
    Route::get('get/company-sub-contractors', [CompanyController::class, 'getCompanySubContractors']);
    Route::get('get/company-suppliers', [CompanyController::class, 'getCompanySuppliers']);
    Route::get('get/company-adjustors', [CompanyController::class, 'getCompanyAdjustors']);
    
    //User Management Api's
    Route::post('add/user', [UserManagementController::class, 'addUser']);
    Route::get('get/user/{id}', [UserManagementController::class, 'getUser']);
    Route::post('update/user/{id}', [UserManagementController::class, 'updateUser']);
    Route::get('delete/user/{id}', [UserManagementController::class, 'deleteUser']);
    Route::get('get/roles', [UserManagementController::class, 'getRoles']);
    Route::get('get/companies', [UserManagementController::class, 'getCompanies']);

    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('check/old-password', [AuthController::class, 'checkOldPassword']);
    Route::post('update/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    //Jobs Api's
    Route::post('create-job', [CompanyJobController::class, 'createJob']);
    Route::get('get/jobs', [CompanyJobController::class, 'getAllJobs']);
    Route::get('get-single/job/{id}', [CompanyJobController::class, 'getSingleJob']);
    //Job Summary Api's
    Route::post('update/job-status/{id}', [CompanyJobController::class, 'updateJobStatus']);
    Route::post('update/job-summary/{id}', [CompanyJobController::class, 'updateJobSummary']);
    Route::post('update/job-summary/insurance-information/{id}', [CompanyJobController::class, 'updateJobSummaryInsuranceInformation']);
    Route::get('get/job-summary/insurance-information/{id}', [CompanyJobController::class, 'getJobSummaryInsuranceInformation']);
    Route::post('update/job-summary/initial-information/{id}', [CompanyJobController::class, 'updateJobSummaryInitialInformation']);
    Route::get('get/job-summary/initial-information/{id}', [CompanyJobController::class, 'getJobSummaryInitialInformation']);
    Route::get('get/job-summary/{id}', [CompanyJobController::class, 'getJobSummary']);
    //Job Content Api's
    Route::post('update/job-content/{id}', [CompanyJobController::class, 'updateJobContent']);
    Route::get('get/job-content/{id}', [CompanyJobController::class, 'getJobContent']);
    Route::post('change/job-content/file-name/{id}', [CompanyJobController::class, 'updateJobContentFileName']);
    Route::post('delete/job-content/media/{id}', [CompanyJobController::class, 'deleteJobContentMedia']);
    Route::get('get/task-with-jobs-count', [CompanyJobController::class, 'getTaskWithJobCount']);
    Route::get('get/jobs-by-task/{statusId}', [CompanyJobController::class, 'getJobWithStatus']);
    //Inprogress Api's
    Route::post('update/in-progress/{jobId}', [InprogressController::class, 'updateInprogress']);
    Route::get('get/in-progress/{jobId}', [InprogressController::class, 'getInprogress']);
    //Adjustor Meeting Api
    Route::post('create/adjustor-meeting/{jobId}', [MeetingController::class, 'createAdjustorMeeting']);
    Route::post('update/adjustor-meeting-media/{jobId}', [MeetingController::class, 'updateAdjustorMeetingMedia']);
    Route::post('update-status/adjustor-meeting/{id}', [MeetingController::class, 'updateAdjustorMeetingStatus']);
    Route::get('get/adjustor-meeting/{jobId}', [MeetingController::class, 'getAdjustorMeeting']);
    Route::post('change/adjustor-meeting/file-name/{id}', [MeetingController::class, 'changeAdjustorMeetingFileName']);
    Route::post('delete/adjustor-meeting/media/{id}', [MeetingController::class, 'deleteAdjustorMeetingMedia']);
    //Overturn Meeting Api
    Route::post('create/overturn-meeting/{jobId}', [MeetingController::class, 'createOverturnMeeting']);
    Route::post('update/overturn-meeting-media/{jobId}', [MeetingController::class, 'updateOverturnMeetingMedia']);
    Route::post('change/overturn-meeting/file-name/{id}', [MeetingController::class, 'updateOverturnMeetingFileName']);
    Route::post('delete/overturn-meeting/media/{id}', [MeetingController::class, 'deleteOverturnMeetingMedia']);
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
    Route::post('generate-pdf/{jobId}', [MaterialOrderController::class, 'generatePdf']);
    Route::get('view-pdf', [MaterialOrderController::class, 'viewPdf']);
    Route::get('get/material-order/{id}', [MaterialOrderController::class, 'getMaterialOrder']);
    Route::post('update/material-order/{id}', [MaterialOrderController::class, 'updateMaterialOrder']);
    Route::get('check/material-order/{jobId}', [MaterialOrderController::class, 'checkMaterialOrder']);
    Route::post('material-order/email/{jobId}', [MaterialOrderController::class, 'MaterialOrderEmail']);
    Route::get('send/email/{jobId}', [MaterialOrderController::class, 'EmailToSupplier']);
    Route::post('update/build-detail/{jobId}', [MaterialOrderController::class, 'updateBuildDetail']);
    Route::get('get/build-detail/{jobId}', [MaterialOrderController::class, 'getBuildDetail']);
    Route::post('confirmation-email/{jobId}', [MaterialOrderController::class, 'confirmationEmail']);
    Route::post('confirmation-email-status/{jobId}', [MaterialOrderController::class, 'confirmationEmailStatus']);
    Route::post('material-order/confirmation-email/{jobId}', [MaterialOrderController::class, 'materialOrderconfirmationEmail']);
    Route::post('material-order/confirmation-email-status/{jobId}', [MaterialOrderController::class, 'materialOrderconfirmationEmailStatus']);
    //Project Design Api's
    Route::post('update/project-design-page-status/{jobId}', [ProjectDesignController::class, 'updateProjectDesignPageStatus']);
    //Project Design Title
    Route::post('store/project-design/title/{jobId}', [ProjectDesignController::class, 'storeProjectDesignTitle']);
    Route::get('get/project-design/title/{jobId}', [ProjectDesignController::class, 'getProjectDesignTitle']);
    Route::post('change/project-design-title/file-name/{id}', [ProjectDesignController::class, 'changeProjectDesignTitleFileName']);
    Route::post('delete/project-design-title/media/{id}', [ProjectDesignController::class, 'deleteProjectDesignTitleMedia']);
    //Project Design Introduction
    Route::post('store/project-design/introduction/{jobId}', [ProjectDesignController::class, 'storeProjectDesignIntroduction']);
    Route::get('get/project-design/introduction/{jobId}', [ProjectDesignController::class, 'getProjectDesignIntroduction']);
    //Project Design Inspection
    Route::post('store/project-design/inspection/{jobId}', [ProjectDesignController::class, 'storeProjectDesignInspection']);
    Route::get('get/project-design/inspection/{jobId}', [ProjectDesignController::class, 'getProjectDesignInspection']);
    Route::post('delete/project-design/inspection/{jobId}', [ProjectDesignController::class, 'deleteProjectDesignInspection']);
    Route::post('change/project-design-inspection/file-name/{id}', [ProjectDesignController::class, 'changeProjectDesignInspectionFileName']);
    Route::post('delete/project-design-inspection/media/{id}', [ProjectDesignController::class, 'deleteProjectDesignInspectionMedia']);
    //Project Design Quote
    Route::post('store/project-design/quote/{jobId}', [ProjectDesignQuoteController::class, 'storeProjectDesignQuote']);
    Route::get('get/project-design/quote/{jobId}', [ProjectDesignQuoteController::class, 'getProjectDesignQuote']);
    Route::post('section/update-status/{jobId}', [ProjectDesignQuoteController::class, 'updateSectionStatus']);
    Route::post('delete/section/{jobId}', [ProjectDesignQuoteController::class, 'deleteSection']);
    Route::post('delete/item/{jobId}', [ProjectDesignQuoteController::class, 'deleteItem']);
    //Project Design Authorization
    Route::post('store/project-design/authorization/{jobId}', [ProjectDesignAuthorizationController::class, 'storeProjectDesignAuthorization']);
    Route::get('get/project-design/authorization/{jobId}', [ProjectDesignAuthorizationController::class, 'getProjectDesignAuthorization']);
    Route::post('delete/authorization-section/{jobId}', [ProjectDesignAuthorizationController::class, 'deleteAuthorizationSection']);
    Route::post('delete/authorization-item/{jobId}', [ProjectDesignAuthorizationController::class, 'deleteAuthorizationItem']);
    //Project Design Payment Schedule
    Route::post('store/payment-schedule/{jobId}', [PaymentScheduleController::class, 'storePaymentSchedule']);
    Route::get('get/payment-schedule/{jobId}', [PaymentScheduleController::class, 'getPaymentSchedule']);
    Route::post('change/payment-schedule/file-name/{id}', [PaymentScheduleController::class, 'changePaymentScheduleFileName']);
    Route::post('delete/payment-schedule/media/{id}', [PaymentScheduleController::class, 'deletePaymentScheduleMedia']);
    //Roof Component Generic
    Route::post('store/roof-component/{jobId}', [RoofComponentController::class, 'storeRoofComponent']);
    Route::get('get/roof-component/{jobId}', [RoofComponentController::class, 'getRoofComponent']);
    Route::post('change/roof-component/file-name/{id}', [RoofComponentController::class, 'changeRoofComponentFileName']);
    Route::post('delete/roof-component/media/{id}', [RoofComponentController::class, 'deleteRoofComponentMedia']);
    //Xactimate Report
    Route::post('store/xactimate-report/{jobId}', [XactimateReportController::class, 'storeXactimateReport']);
    Route::get('get/xactimate-report/{jobId}', [XactimateReportController::class, 'getXactimateReport']);
    Route::post('change/xactimate-report/file-name/{id}', [XactimateReportController::class, 'changeXactimateReportFileName']);
    Route::post('delete/xactimate-report/media/{id}', [XactimateReportController::class, 'deleteXactimateReportMedia']);
    //Project Design PDF
    Route::get('generate/design-meeting-pdf/{jobId}', [ProjectDesignController::class, 'generatePDF']);
    //QC Inspection Api's
    Route::post('store/qc-inspection/{jobId}', [QcInspectionController::class, 'storeQcInspection']);
    Route::post('store/qc-inspection/media/{jobId}', [QcInspectionController::class, 'storeQcInspectionMedia']);
    Route::get('get/qc-inspection/{jobId}', [QcInspectionController::class, 'getQcInspection']);
    Route::post('change/qc-inspection/file-name/{id}', [QcInspectionController::class, 'changeQcInspectionFileName']);
    Route::post('delete/qc-inspection/media/{id}', [QcInspectionController::class, 'deleteQcInspectionMedia']);
    //Certificate Of Completion Api's
    Route::post('store/coc/{jobId}', [CocController::class, 'storeCoc']);
    Route::get('get/coc/{jobId}', [CocController::class, 'getCoc']);
    Route::post('coc/insurance-email/{id}', [CocController::class, 'CocInsuranceEmail']);
    //Ready To Build Api's
    Route::post('store/ready-to-build/{jobId}', [ReadyToBuildController::class, 'storeReadyToBuild']);
    Route::post('store/ready-to-build-status/{jobId}', [ReadyToBuildController::class, 'storeReadyToBuildStatus']);
    Route::post('change/ready-to-build/file-name/{id}', [ReadyToBuildController::class, 'changeReadyToBuildFileName']);
    Route::post('delete/ready-to-build/media/{id}', [ReadyToBuildController::class, 'deleteReadyToBuildMedia']);
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
    Route::post('change/carrier-scope/file-name/{id}', [CarrierScopeController::class, 'changeCarrierScopeFileName']);
    Route::post('delete/carrier-scope/media/{id}', [CarrierScopeController::class, 'deleteCarrierScopeMedia']);
    //Term & Conditions Api's
    Route::post('store/term-and-condition/{jobId}', [TermAndConditionController::class, 'storeTermAndConditions']);
    Route::get('get/term-and-condition/{jobId}', [TermAndConditionController::class, 'getTermAndConditions']);
    //Estimate Prepared Api's
    Route::post('store/estimate-prepared/{jobId}', [EstimatePreparedController::class, 'storeEstimatePrepared']);
    Route::get('get/estimate-prepared/{jobId}', [EstimatePreparedController::class, 'getEstimatePrepared']);
    Route::post('change/estimate-prepared/file-name/{id}', [EstimatePreparedController::class, 'changeEstimatePreparedFileName']);
    Route::post('delete/estimate-prepared/media/{id}', [EstimatePreparedController::class, 'deleteEstimatePreparedMedia']);
    //Build Complete Api's
    Route::post('update/build-complete/{jobId}', [BuildCompleteController::class, 'updateBuildComplete']);
    Route::get('get/build-complete/{jobId}', [BuildCompleteController::class, 'getBuildComplete']);
    //Subpay Sheet Api's
    Route::post('update/subpay-sheet/{buildCompleteId}', [BuildCompleteController::class, 'updateSubpaySheet']);
    Route::get('get/subpay-sheet/{buildCompleteId}', [BuildCompleteController::class, 'getSubpaySheet']);
    //Job Log Api's
    Route::post('update/job-log/{buildCompleteId}', [BuildCompleteController::class, 'updateJobLog']);
    Route::get('get/job-log/{buildCompleteId}', [BuildCompleteController::class, 'getJobLog']);
    //Final Payment Due Api's
    Route::post('update/final-payment-due/{jobId}', [FinalPaymentController::class, 'updateFinalPaymentDue']);
    Route::get('get/final-payment-due/{jobId}', [FinalPaymentController::class, 'getFinalPaymentDue']);
    //Won Closed Api's
    Route::post('update/won-closed/{jobId}', [WonClosedController::class, 'updateWonClosed']);
    Route::get('get/won-closed/{jobId}', [WonClosedController::class, 'getWonClosed']);
    //Ready To Close Api's
    Route::post('update/ready-to-close/{jobId}', [ReadyToCloseController::class, 'updateReadyToClose']);
    Route::get('get/ready-to-close/{jobId}', [ReadyToCloseController::class, 'getReadyToClose']);
});
