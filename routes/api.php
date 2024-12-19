<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CocController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobLogController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ProfileController;
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
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CompanyLocationController;
use App\Http\Controllers\PaymentController;

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
    Route::post('createUser', [UserController::class, 'addUser']);
    Route::get('getUser', [UserController::class, 'getUser']);
    Route::post('updateUser/{id}', [UserController::class, 'updateUser']);
    Route::get('users/filter', [UserController::class, 'filterUsersByPermission']);
    Route::get('users/search', [UserController::class, 'searchUsers']);

    //setting apis
    Route::post('updateProfile/{id}', [ProfileController::class, 'updateProfile']);
    Route::post('changePassword', [ProfileController::class, 'changePassword']);
    Route::post('add-overhead/percentage', [ProfileController::class, 'addOverheadPercentage']);
    Route::post('add-overhead/percentage', [ProfileController::class, 'addOverheadPercentage']);

    //Company Api's
    Route::post('create/company', [CompanyController::class, 'createCompany']);
    Route::get('get/company/{id}', [CompanyController::class, 'getCompany']);
    Route::get('getCompanies', [CompanyController::class, 'getCompanies']);
    Route::get('company/filter', [CompanyController::class, 'filterCompanyByStatus']);
    Route::get('company/search', [CompanyController::class, 'searchCompany']);
    Route::get('company/view/{id}', [CompanyController::class, 'viewCompany']);
    Route::get('company/edit/{id}', [CompanyController::class, 'editCompany']);
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
    Route::get('customer/profile/{jobId}', [CompanyJobController::class, 'customerProfile']); //get customer details

    //Job Summary Api's
    Route::post('update/job-status/{id}', [CompanyJobController::class, 'updateJobStatus']);
    Route::post('update/job-summary/{id}', [CompanyJobController::class, 'updateJobSummary']);
    Route::post('update/job-summary/insurance-information/{id}', [CompanyJobController::class, 'updateJobSummaryInsuranceInformation']);
    Route::get('get/job-summary/insurance-information/{id}', [CompanyJobController::class, 'getJobSummaryInsuranceInformation']);
    Route::post('update/job-summary/initial-information/{id}', [CompanyJobController::class, 'updateJobSummaryInitialInformation']);
    Route::get('get/job-summary/initial-information/{id}', [CompanyJobController::class, 'getJobSummaryInitialInformation']);
    Route::get('get/job-summary/{id}', [CompanyJobController::class, 'getJobSummary']);

    ///////new Filter APIS for job count and job details
    Route::post('filter/jobs', [CompanyJobController::class, 'filterJobs']);
    Route::post('filter/jobs/insurance', [CompanyJobController::class, 'filterJobsByInsurance']);
    Route::post('filter/jobs/retail', [CompanyJobController::class, 'filterJobsByRetail']);
    Route::post('filter/jobs/kanban', [CompanyJobController::class, 'filterJobskanban']);
    Route::get('get/jobs/filter/sections', [CompanyJobController::class, 'getJobsFilterSection']); //get sales resprenstative as assigneees for the filters
    Route::get('filter/jobs-by-status/{statusId}', [CompanyJobController::class, 'FilterJobWithStatus']);

    ///find customer job status
    Route::get('get/current/job/stage/{jobId}', [CompanyJobController::class, 'getCurrentJobStage']);
    Route::get('get/customer/summary/{jobId}', [CompanyJobController::class, 'getCustomerSummary']);


    Route::get('filter/job/location', [CompanyJobController::class, 'filterJobByLocation']);//not used
    Route::get('filter/job/jobType/{statusId}', [CompanyJobController::class, 'filterJobsByJobType']); //not used

    //Job Content Api's
    Route::post('update/job-content/{id}', [CompanyJobController::class, 'updateJobContent']);
    Route::get('get/job-content/{id}', [CompanyJobController::class, 'getJobContent']);
    Route::post('change/job-content/file-name/{id}', [CompanyJobController::class, 'updateJobContentFileName']);
    Route::post('delete/job-content/media/{id}', [CompanyJobController::class, 'deleteJobContentMedia']);
    Route::get('get/task-with-jobs-count', [CompanyJobController::class, 'getTaskWithJobCount']);
    ///count for grid
    Route::get('get/v1/task-with-jobs-count', [CompanyJobController::class, 'getV1TaskWithJobCount']);
    Route::get('get/jobs-by-task/{statusId}', [CompanyJobController::class, 'getJobWithStatus']);

    //claims details section new section
    Route::post('add/claim-details/{jobId}', [CompanyJobController::class, 'claimDetails']);
    Route::get('get/claim-details/{jobId}', [CompanyJobController::class, 'getclaimDetails']);

    ///Summary Metrics Section
    Route::get('summary-metrics', [CompanyJobController::class, 'summaryMetrics']); //Summary Metrics and Alerts Section Api
    Route::get('summary-filter', [CompanyJobController::class, 'summaryFilter']);  //Summary and Key Metrics api

    //progress Line
    Route::get('progress-line/{jobId}', [CompanyJobController::class, 'progressLine']); 

    //new Notes section api
    Route::post('job-notes-add/{jobId}', [CompanyJobController::class, 'notesAdd']); 
    Route::get('job-notes/{jobId}', [CompanyJobController::class, 'getNotes']); 

    //claim Information summary
    Route::post('claim/information-summary/{jobId}', [CompanyJobController::class, 'claimInformationSummary']); 
    Route::get('get/claim/information-summary/{jobId}', [CompanyJobController::class, 'getClaimInformationSummary']); 

    ////Pyment History Section
    Route::post('add/payment-history/{jobId}', [PaymentController::class, 'addPaymentHistory']);
    Route::get('get/payment-history/{jobId}', [PaymentController::class, 'getPaymentHistory']);


    //company Location 
    Route::post('add/company_location', [CompanyLocationController::class, 'addCompanyLocation']);
    Route::get('get/company_location', [CompanyLocationController::class, 'getCompanyLocation']);
    Route::get('edit/company_location/{id}', [CompanyLocationController::class, 'editCompanyLocation']);
    Route::post('update/company_location/{id}', [CompanyLocationController::class, 'updateCompanyLocation']);
    Route::get('delete/company_location/{id}', [CompanyLocationController::class, 'deleteCompanyLocation']);


    //Inprogress Api's 
    Route::post('update/in-progress/{jobId}', [InprogressController::class, 'updateInprogress']);
    Route::post('add/in-progress/photos/{jobId}', [InprogressController::class, 'addInprogressPhotos']);
    Route::get('get/in-progress/photos/{jobId}', [InprogressController::class, 'getInprogressPhotos']);
    Route::post('update/in-progress-status/{jobId}', [InprogressController::class, 'updateInprogressStatus']);
    Route::get('get/in-progress-status/{jobId}', [InprogressController::class, 'getInprogressStatus']);
    Route::get('get/in-progress/{jobId}', [InprogressController::class, 'getInprogress']);

    //Build Packet template Sumo quote
    Route::post('build-packet/sidebar/{jobId}', [InprogressController::class, 'buildPacketSidebar']);
    Route::get('get/build-packet/sidebar/{jobId}', [InprogressController::class, 'getBuildPacketSidebar']);
    Route::get('mark/build-packet-complete/{jobId}', [InprogressController::class, 'markBuildPacketComplete']);
    Route::get('get/project-status/{jobId}', [InprogressController::class, 'getProjectStatus']);
    Route::post('sign/build-packet/{jobId}', [InprogressController::class, 'signBuildPacket']);

    //Adjustor Meeting Api
    Route::post('create/adjustor-meeting/{jobId}', [MeetingController::class, 'createAdjustorMeeting']);
    Route::post('update/adjustor-meeting-media/{jobId}', [MeetingController::class, 'updateAdjustorMeetingMedia']);
    Route::post('update-status/adjustor-meeting/{id}', [MeetingController::class, 'updateAdjustorMeetingStatus']);
    Route::post('add/adjustor-meeting-status/{id}', [MeetingController::class, 'AdjustorMeetingStatus']);
    Route::get('get/adjustor-meeting/{jobId}', [MeetingController::class, 'getAdjustorMeeting']);
    Route::post('change/adjustor-meeting/file-name/{id}', [MeetingController::class, 'changeAdjustorMeetingFileName']);
    Route::post('delete/adjustor-meeting/media/{id}', [MeetingController::class, 'deleteAdjustorMeetingMedia']);

    //Adjustor meeting Photo section
    Route::post('add/exterior/photo-section/{Id}', [MeetingController::class, 'AddExteriorPhotoSection']);
    Route::get('get/exterior/photo-section/{Id}', [MeetingController::class, 'getExteriorPhotoSection']);
    Route::post('add/adjustor-meeting/square-photos/{Id}', [MeetingController::class, 'AdjustorMeetingSquarePhotos']);
    Route::get('get/adjustor-meeting/square-photos/{Id}', [MeetingController::class, 'getAdjustorMeetingSquarePhotos']);
    Route::get('mark/complete/adjustor-meeting-photos/{Id}', [MeetingController::class, 'CompleteAdjustorMeetingSquarePhotos']);
    Route::get('get/mark/complete/adjustor-meeting-photos/{Id}', [MeetingController::class, 'getCompleteAdjustorMeetingSquarePhotos']);

    //Overturn Meeting Api
    Route::post('create/overturn-meeting/{jobId}', [MeetingController::class, 'createOverturnMeeting']);
    Route::post('update/overturn-meeting-media/{jobId}', [MeetingController::class, 'updateOverturnMeetingMedia']);
    Route::post('change/overturn-meeting/file-name/{id}', [MeetingController::class, 'updateOverturnMeetingFileName']);
    Route::post('delete/overturn-meeting/media/{id}', [MeetingController::class, 'deleteOverturnMeetingMedia']);
    Route::get('get/overturn-meeting/{jobId}', [MeetingController::class, 'getOverturnMeeting']);
    Route::post('update-status/overturn-meeting/{id}', [MeetingController::class, 'updateOverturnMeetingStatus']);
    //Customer Agreements Api's
    Route::post('customer-agreement/{jobId}', [CustomerAgreementController::class, 'customerAgreement']);
    Route::post('customer-agreement-status/{jobId}', [CustomerAgreementController::class, 'customerAgreementStatus']);

    Route::get('get/customer-agreement/{id}', [CustomerAgreementController::class, 'getCustomerAgreement']);
    Route::post('update/customer-agreement/{id}', [CustomerAgreementController::class, 'updateCustomerAgreement']);
    Route::post('sign-by-email/{id}', [CustomerAgreementController::class, 'signCustomerAgreementByEmail']);
    Route::get('check/customer-agreement/{jobId}', [CustomerAgreementController::class, 'checkCustomerAgreement']);
    //Material Order Api's
    Route::post('material-order/{jobId}', [MaterialOrderController::class, 'materialOrder']);
    Route::get('material-list', [MaterialOrderController::class, 'materialList']);
    Route::post('material-selection/{id}', [MaterialOrderController::class, 'materialSelection']);
    Route::get('get/material-selection/{id}', [MaterialOrderController::class, 'getMaterialSelection']);
    Route::post('generate-pdf/{jobId}', [MaterialOrderController::class, 'generatePdf']);
    Route::get('view-pdf', [MaterialOrderController::class, 'viewPdf']);
    Route::get('delete/material-order/material/{id}', [MaterialOrderController::class, 'deleteMaterialOrderMaterial']);
    Route::get('get/material-order/{id}', [MaterialOrderController::class, 'getMaterialOrder']);
    Route::post('update/material-order/{id}', [MaterialOrderController::class, 'updateMaterialOrder']);
    Route::get('check/material-order/{jobId}', [MaterialOrderController::class, 'checkMaterialOrder']);
    Route::post('material-order/email/{jobId}', [MaterialOrderController::class, 'MaterialOrderEmail']);
    Route::get('send/email/{jobId}', [MaterialOrderController::class, 'EmailToSupplier']);
    Route::post('update/build-detail/{jobId}', [MaterialOrderController::class, 'updateBuildDetail']);
    Route::post('update/build-detail-status/{jobId}', [MaterialOrderController::class, 'updateBuildDetailStatus']);
    Route::get('get/build-detail/{jobId}', [MaterialOrderController::class, 'getBuildDetail']);
    Route::post('confirmation-email/{jobId}', [MaterialOrderController::class, 'confirmationEmail']);
    Route::post('confirmation-email-status/{jobId}', [MaterialOrderController::class, 'confirmationEmailStatus']);
    Route::get('get-confirmation-email-status/{jobId}', [MaterialOrderController::class, 'getConfirmationEmailStatus']);
    Route::post('material-order/confirmation-email/{jobId}', [MaterialOrderController::class, 'materialOrderconfirmationEmail']);
    Route::post('material-order/confirmation-email-status/{jobId}', [MaterialOrderController::class, 'materialOrderconfirmationEmailStatus']);
    Route::get('get-material-order/confirmation-email-status/{jobId}', [MaterialOrderController::class, 'getMaterialOrderconfirmationEmailStatus']);
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
    Route::post('update/coc/status/{jobId}', [CocController::class, 'updateStatusCoc']);
    Route::get('get/coc/{jobId}', [CocController::class, 'getCoc']);
    Route::post('coc/insurance-email/{id}', [CocController::class, 'CocInsuranceEmail']);
    Route::post('coc/insurance-email/status/{id}', [CocController::class, 'CocInsuranceEmailStatus']);
    Route::get('coc/insurance-email/status/{id}', [CocController::class, 'getCocInsuranceEmailStatus']);
    Route::get('get/coc/insurance-email/{id}', [CocController::class, 'getCocInsuranceEmail']);


    //Ready To Build Api's
    Route::post('store/ready-to-build/{jobId}', [ReadyToBuildController::class, 'storeReadyToBuild']);
    Route::post('store/ready-to-build-status/{jobId}', [ReadyToBuildController::class, 'storeReadyToBuildStatus']);
    Route::post('change/ready-to-build/file-name/{id}', [ReadyToBuildController::class, 'changeReadyToBuildFileName']);
    Route::post('delete/ready-to-build/media/{id}', [ReadyToBuildController::class, 'deleteReadyToBuildMedia']);
    Route::get('get/ready-to-build/{jobId}', [ReadyToBuildController::class, 'getReadyToBuild']);
    Route::get('send/email/supplier/{jobId}', [ReadyToBuildController::class, 'EmailToSupplier']); //send mail to supplier

    //Supplier Api's
    Route::post('store/supplier', [SupplierController::class, 'storeSupplier']);
    Route::get('get/suppliers/{Id}', [SupplierController::class, 'getSuppliers']);
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
    Route::post('estimate-prepared-status/{jobId}', [EstimatePreparedController::class, 'EstimatePreparedStatus']);
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
    Route::post('update/final-payment-due-status/{jobId}', [FinalPaymentController::class, 'updateFinalPaymentDueStatus']);
    Route::get('get/final-payment-due/{jobId}', [FinalPaymentController::class, 'getFinalPaymentDue']);
    //Won Closed Api's
    Route::post('update/won-closed/{jobId}', [WonClosedController::class, 'updateWonClosed']);
    Route::get('get/won-closed/{jobId}', [WonClosedController::class, 'getWonClosed']);
    //Ready To Close Api's
    Route::post('update/ready-to-close/{jobId}', [ReadyToCloseController::class, 'updateReadyToClose']);
    Route::post('update/ready-to-close-status/{jobId}', [ReadyToCloseController::class, 'updateReadyToCloseStatus']);
    Route::get('get/ready-to-close/{jobId}', [ReadyToCloseController::class, 'getReadyToClose']);
    Route::get('jobSearch', [ReadyToCloseController::class, 'jobSearch']);


    //Report Apis
    Route::post('user-reports', [ReportController::class, 'userReports']); //performance api
    Route::get('get-pipeline-data', [ReportController::class, 'getPipelineData']);  //pipeline api
    Route::get('get-own-pipeline-data', [ReportController::class, 'getOwnPipelineData']);  //when click on my job pipeline api

});
