<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Status;
use App\Models\Payment;
use App\Models\Location;
use App\Models\CompanyJob;
use App\Models\ClaimDetail;
use Illuminate\Support\Str;
use App\Models\CompanyNotes;
use Illuminate\Http\Request;
use App\Models\CompanyJobUser;
use App\Models\ClaimDetailMedia;
use App\Models\ClaimInformation;
use App\Models\CompanyJobContent;
use App\Models\CompanyJobSummary;
use App\Models\CustomerAgreement;
use App\Models\ProjectDesignPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\JobStatusUpdateEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\CompanyJobContentMedia;
use App\Models\ProjectDesignPageStatus;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ClaimDetailRequest;
use Illuminate\Support\Facades\DB as FacadesDB;

class CompanyJobController extends Controller
{
    public function createJob(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'address' => 'required',
            'address.city'=>'nullable',
            'address.postalCode' => 'nullable',
            'address.state' => 'nullable',
            'address.street' => 'nullable',
            // 'latitude' => 'required',
            // 'longitude' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',

            'market' => 'nullable',
            'job_type' => 'nullable|in:Retail,Insurance',
            'lead_source' => 'nullable|in:Door Knocking,Customer Referral,Call In,Facebook,Family Member,Home Advisor,Website,Social Encounter',
            'lead_status' => 'nullable|in:New,Contacted,Follow-up Needed',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'invoice_number' => 'nullable',
            'profile_path'=> 'nullable|image'
        ]);

        try {

            if (!is_array($request->address)) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Invalid address, Please Select from Google places.'
                ], 422);
            
            }
            $user = Auth::user();
            $company= $user->company_id;
            $created_by = $user->created_by == 0 ? 1 : $user->created_by ;
        
            $fileFinalName = null; // Set a default value

            if (isset($request->profile_path)) {
                $file = $request->profile_path;
                $fileName = $file->getClientOriginalName();
                $fileExtension = $file->getClientOriginalExtension();

                $fileFinalName = rand(0000,9999).'_'. time().'.'.$fileExtension;
                $fileDestination = "storage/profile_photos";
                $file->move($fileDestination,$fileFinalName);

            }
            //Create Job
            $job = new CompanyJob;
            $job->status_id = 1;
            $job->user_id = Auth::id();
            $job->created_by = $created_by;
            $job->name = $request->name;
            $job->address = json_encode($request->address); // Save raw address JSON in the job table
            // $job->address = $request->address;
            $job->latitude = 12.3; //dummay add
            $job->longitude = 12.4; //dummy temp add
            $job->email = $request->email;
            $job->phone = $request->phone;
            $job->date = now()->format('Y-m-d');

            $job->customer_id = $user->id; //it will be job customer
            $job->profile_path =  isset($fileFinalName) ? 'profile_photos/'.$fileFinalName : Null;

            $job->save();

            //save job new fields data here
            $job_summary = CompanyJobSummary::updateOrCreate([
                'company_job_id' => $job->id,
            ],[
                'company_job_id' => $job->id,
                'market' => $request->market,
                'lead_source' => $request->lead_source,
                'job_type' => $request->job_type,
                'lead_status' => $request->lead_status,
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->name,



            ]);

            
            // Assign Job To Users
            if(isset($request->user_ids) && count($request->user_ids) > 0) {
                $job->users()->sync($request->user_ids);
            }
           
            $job_summary->user_ids = $job->users()->pluck('user_id')->toArray();
            //here I will save the address but this will save in CustomerAgreement table here we will save the adress that get from google map api
            $address = new CustomerAgreement();
            $address->company_job_id = $job->id;
            $address->street = $request->address['street'] ?? null;
            $address->city = $request->address['city'] ?? null;
            $address->state = $request->address['state'] ?? null;
            $address->zip_code = $request->address['postalCode'] ?? null;
            $address->address = $request->address['formatedAddress'] ?? null;

            $address->save();

            //Update Project Design Status Table
            $pages = ProjectDesignPage::all();
            foreach($pages as $page)
            {
                $status = ProjectDesignPageStatus::updateOrCreate([
                    'project_design_page_id' => $page->id,
                    'company_job_id' => $job->id,
                ],[
                    'project_design_page_id' => $page->id,
                    'company_job_id' => $job->id,
                ]);
            }

            //Fire an Event
            event(new JobStatusUpdateEvent('Refresh Pgae'));
            
            //Assign Job
            $job->users()->attach($user->id);
            //  i have comment this line because it auto add the value in company_job_user table and it will show the user in ready to close sales represntative

            return response()->json([
                'status' => 201,
                'message' => 'Job Created Successfully',
                'job' => [
                    'id' => $job->id,
                    'name' => $job->name,
                    'email' => $job->email,
                    'phone' => $job->phone,
                    'address' => json_decode($job->address, true)['formatedAddress'] ?? null, 
                    // 'address' => $job->address['formatedAddress'] ?? null, 
                    'market' => $job_summary->market,
                    'lead_source' => $job_summary->lead_source,
                    'job_type' => $job_summary->job_type,
                    'lead_status' => $job_summary->lead_status,
                    'sales_representatives' =>$job_summary->user_ids,
                    'profile_path' => asset('storage/' . $job->profile_path),
                    'created_at' => $job->created_at,
                    'updated_at' => $job->updated_at
                ]
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
 
    public function customerProfile($jobId)
    {
        $customer_profile = CompanyJob::with('companyJobSummaries')->select('id','name','address','email','phone','user_id')->where('id',$jobId)->first();

        $jobTotal = $customer_profile->companyJobSummaries->first()->job_total ?? null;

        $response=[
            'id'=>$customer_profile->id,
            'name'=>$customer_profile->name,
            'email'=>$customer_profile->email,
            'phone'=>$customer_profile->phone,
            'job_total' => $jobTotal ?? null, // Safely access job_total
            'address'=>json_decode($customer_profile->address),
            // 'job_total' => $customer_profile->companyJobSummaries->sum('job_total') ?? null, // Safely access job_total

        ];
        
        if($customer_profile)
        {
            return response()->json([
                'status'=>200,
                'message' => 'Details Fetched Successfully',
                'data' => $response
            ]);
        }

        return response()->json([
            'status'=>404,
            'message' => 'Not Found',
            'data' =>[]
        ]);

    }

    public function getAllJobs()
    {
        try {
            $user = Auth::user();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;

            // Retrieve assigned jobs
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();

            // Fetch jobs based on user role
            $jobs = ($user->role_id == 1 || $user->role_id == 2) ?
                CompanyJob::with(['summary' => function ($query) {
                        $query->select('company_job_id', 'job_total', 'claim_number', 'job_type');
                    }])
                    ->select('id', 'name', 'address', 'created_at', 'updated_at', 'status_id')
                    ->where('created_by', $created_by)
                    ->orderBy('status_id', 'asc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($job) {
                        $decodedAddress = json_decode($job->address, true);
                        $job->address = $decodedAddress['formatedAddress'] ?? null;
                        return $job;
                    })
                :
                CompanyJob::with(['summary' => function ($query) {
                        $query->select('company_job_id', 'job_total', 'claim_number', 'job_type');
                    }])
                    ->select('id', 'name', 'address', 'created_at', 'updated_at', 'status_id')
                    ->where(function ($query) use ($user, $assigned_jobs) {
                        $query->orWhere('user_id', $user->id);
                        $query->orWhereIn('id', $assigned_jobs);
                    })
                    ->orderBy('status_id', 'asc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($job) {
                        $decodedAddress = json_decode($job->address, true);
                        $job->address = $decodedAddress['formatedAddress'] ?? null;
                        return $job;
                    });

            // Add progress calculations based on status_id
            $jobsWithProgress = $jobs->map(function ($job) {
                $statusId = $job->status_id;
                $completedPercentage = 0;

                // Calculate completed percentage based on status_id
                switch ($statusId) {
                    case 1: // New Leads
                        $completedPercentage = 10;
                        break;
                    case 2: // Customer Agreement
                        $completedPercentage = 20;
                        break;
                    case 4: // Adjuster Scheduled
                        $completedPercentage = 30;
                        break;
                    case 5: // Insurance Under Review
                        $completedPercentage = 40;
                        break;
                    case 6: // Overturn
                        $completedPercentage = 50;
                        break;
                    case 8: // Approved
                        $completedPercentage = 50;
                        break;
                    case 11: // In Progress
                        $completedPercentage = 60;
                        break;
                    case 13: // COC Required
                        $completedPercentage = 70;
                        break;
                    case 14: // Final Payment Due
                        $completedPercentage = 80;
                        break;
                    case 15: // Ready to Close
                        $completedPercentage = 90;
                        break;
                    case 20: // Completed
                        $completedPercentage = 100;
                        break;
                    default:
                        $completedPercentage = 0; // Default to 0 if status is unknown
                }

                // Add completed_percentage to job data
                $job->completed_percentage = $completedPercentage;

                return $job;
            });

            // Group jobs by status name
            $groupedJobs = $jobsWithProgress->map(function ($job) {
                $job->job_summaries = $job->jobSummaries ? $job->jobSummaries->map(function ($summary) {
                    return [
                        'job_total' => $summary->job_total ?? 0,
                        'claim_number' => $summary->claim_number,
                    ];
                }) : collect([]);

                return $job;
            })->groupBy(function ($job) {
                return $job->status->name;
            });

            // Define statuses
            $statuses = Status::whereIn('name', [
                'New Leads',
                'Inspection',
                'Signed Deal',
                // 'Estimate Prepared',
                'Adjuster Scheduled',
                'Approved',
                'Denied',
                'Partial',
           
                'Ready To Build',
                'Build Scheduled',
                'In Progress',
                'Build Complete',
                'COC Required',
                'Final Payment Due',
                'Won and Closed',
                
            ])->get();

            // Map statuses with job totals and tasks
            $response = $statuses->map(function ($status) use ($groupedJobs, $user, $created_by) {
                $jobTotalSumQuery = CompanyJobSummary::whereHas('job', function ($query) use ($status) {
                    $query->where('status_id', $status->id);
                });

                // Apply `created_by` filter for role_id 1 and 2
                if ($user->role_id == 1 || $user->role_id == 2) {
                    $jobTotalSumQuery->whereHas('job', function ($query) use ($created_by) {
                        $query->where('created_by', $created_by);
                    });
                }

                $jobTotalSum = $jobTotalSumQuery->sum('job_total');

                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'job_total' => $jobTotalSum,
                    'tasks' => $groupedJobs->get($status->name, collect()),
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Jobs Found Successfully',
                'data' => $response
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()
            ], 500);
        }
    }


    public function getAllJobs0()
    {
        try {
            $user = Auth::user();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;

            // Retrieve assigned jobs
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();

            // Tables with stages
            $tables = [
                'customer_agreements',
                'estimate_prepareds',
                'adjustor_meetings',
                'ready_to_builds',
                'build_details',
                'inprogresses',
                'cocs',
                'final_payment_dues',
                'ready_to_closes',
            ];

            // Fetch jobs based on user role
            $jobs = ($user->role_id == 1 || $user->role_id == 2) ?
                CompanyJob::with(['summary' => function ($query) {
                        $query->select('company_job_id', 'job_total', 'claim_number','job_type');
                    }])
                    ->select('id', 'name', 'address', 'created_at', 'updated_at', 'status_id')
                    ->where('created_by', $created_by)
                    ->orderBy('status_id', 'asc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($job) {
                        $decodedAddress = json_decode($job->address, true);
                        $job->address = $decodedAddress['formatedAddress'] ?? null;
                        return $job;
                    })
                :
                CompanyJob::with(['summary' => function ($query) {
                        $query->select('company_job_id', 'job_total', 'claim_number','job_type');
                    }])
                    ->select('id', 'name', 'address', 'created_at', 'updated_at', 'status_id')
                    ->where(function ($query) use ($user, $assigned_jobs) {
                        $query->orWhere('user_id', $user->id);
                        $query->orWhereIn('id', $assigned_jobs);
                    })
                    ->orderBy('status_id', 'asc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($job) {
                        $decodedAddress = json_decode($job->address, true);
                        $job->address = $decodedAddress['formatedAddress'] ?? null;
                        return $job;
                    });

            // Add progress calculations to each job
            $jobsWithProgress = $jobs->map(function ($job) use ($tables) {
                $completedSteps = 0;
                $totalSteps = count($tables) + 1; 
                // dd($totalSteps);

                $customerAgreement = DB::table('customer_agreements')
                    ->where('company_job_id', $job->id)
                    ->select('current_stage')
                    ->first();
                    if ($customerAgreement && $customerAgreement->current_stage === 'yes') {
                        $completedSteps += 1; // Count both 'new_leads' and 'customer_agreements'
                    }elseif ($customerAgreement && $customerAgreement->current_stage === 'no') {
                        $completedSteps += 1; // when we revert from status 4 to status 2 it means at this time job is in status 2 so no 1 step is complete thats why set 10 percent
                    }

                foreach ($tables as $table) {
                    $currentStage = DB::table($table)
                        ->where('company_job_id', $job->id)
                        ->select('current_stage')
                        ->first();

                        // dd($currentStage);

                    if ($currentStage && $currentStage->current_stage === 'yes') {
                        $completedSteps++;
                    }
                }

                $completedPercentage = ($completedSteps / $totalSteps) * 100;

                $job->completed_percentage = round($completedPercentage, 2);
                return $job;
            });

            // Group jobs by status name
            $groupedJobs = $jobsWithProgress->map(function ($job) {
                $job->job_summaries = $job->jobSummaries ? $job->jobSummaries->map(function ($summary) {
                    return [
                        'job_total' => $summary->job_total ?? 0,
                        'claim_number' => $summary->claim_number,
                    ];
                }) : collect([]);

                return $job;
            })->groupBy(function ($job) {
                return $job->status->name;
            });

            // Define statuses
            $statuses = Status::whereIn('name', [
                'New Leads',
                'Customer Agreement',
                // 'Estimate Prepared', //excluded
                'Adjuster Scheduled',
                'Ins Under Review', 
                'Overturn',
                'Appraisal',  
                'Approved',
                'Ready To Build',
                'Build Scheduled',
                'In Progress',
                'Build Complete',
                'COC Required',
                'Final Payment Due',
                'Ready to Close',
                // 'Supplement Submitted',
                'Won and Closed',
                'Lost',
                'Unqualified', 
            ])->get();

            // return response($statuses);

            // Map statuses with job totals and tasks
            $response = $statuses->map(function ($status) use ($groupedJobs, $user, $created_by) {
                $jobTotalSumQuery = CompanyJobSummary::whereHas('job', function ($query) use ($status) {
                    $query->where('status_id', $status->id);
                });

                // Apply `created_by` filter for role_id 1 and 2
                if ($user->role_id == 1 || $user->role_id == 2) {
                    $jobTotalSumQuery->whereHas('job', function ($query) use ($created_by) {
                        $query->where('created_by', $created_by);
                    });
                }

                $jobTotalSum = $jobTotalSumQuery->sum('job_total');

                // $completedJobs = $jobsWithProgress->filter(function ($job) use ($status) {
                //     return $job->status_id === $status->id;
                // })->map(function ($job) {
                //     return [
                //         'id' => $job->id,
                //         'name' => $job->name,
                //         'completed_percentage' => $job->completed_percentage,
                //     ];
                // });

                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'job_total' => $jobTotalSum,
                    'tasks' => $groupedJobs->get($status->name, collect()),
                    // 'completed' => $completedJobs,
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Jobs Found Successfully',
                'data' => $response
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()
            ], 500);
        }
    }

    public function filterJobsByInsurance(Request $request)
    {
        try {
            $user = Auth::user();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;

            // $jobTypeFilter = $request->input('job_type');
            $jobTypeFilter = "insurance";


            // Retrieve assigned jobs
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();

            // Tables with stages
            $tables = [
                'customer_agreements',
                'estimate_prepareds',
                'adjustor_meetings',
                'ready_to_builds',
                'build_details',
                'inprogresses',
                'cocs',
                'final_payment_dues',
                'ready_to_closes',
            ];

            // Fetch jobs based on user role
            $jobsQuery = CompanyJob::with(['summary' => function ($query) {
                    $query->select('company_job_id', 'job_total', 'claim_number', 'job_type');
                }])
                ->select('id', 'name', 'address', 'created_at', 'updated_at', 'status_id');

            if ($user->role_id == 1 || $user->role_id == 2) {
                $jobsQuery->where('created_by', $created_by);
            } else {
                $jobsQuery->where(function ($query) use ($user, $assigned_jobs) {
                    $query->orWhere('user_id', $user->id)
                        ->orWhereIn('id', $assigned_jobs);
                });
            }

            // Apply job_type filter if provided
            if ($jobTypeFilter) {
                $jobsQuery->whereHas('summary', function ($query) use ($jobTypeFilter) {
                    $query->where('job_type', $jobTypeFilter);
                });
            }

            $jobs = $jobsQuery->orderBy('status_id', 'asc')
                            ->orderBy('id', 'desc')
                            ->get()
                            ->map(function ($job) {
                                $decodedAddress = json_decode($job->address, true);
                                $job->address = $decodedAddress['formatedAddress'] ?? null;
                                return $job;
                            });

            // Add progress calculations to each job
            $jobsWithProgress = $jobs->map(function ($job) use ($tables) {
                $completedSteps = 0;
                $totalSteps = count($tables) + 1;

                $customerAgreement = DB::table('customer_agreements')
                    ->where('company_job_id', $job->id)
                    ->select('current_stage')
                    ->first();
                if ($customerAgreement && $customerAgreement->current_stage === 'yes') {
                    $completedSteps += 1; // Count both 'new_leads' and 'customer_agreements'
                }

                foreach ($tables as $table) {
                    $currentStage = DB::table($table)
                        ->where('company_job_id', $job->id)
                        ->select('current_stage')
                        ->first();
                    if ($currentStage && $currentStage->current_stage === 'yes') {
                        $completedSteps++;
                    }
                }

                $completedPercentage = ($completedSteps / $totalSteps) * 100;

                $job->completed_percentage = round($completedPercentage, 2);
                return $job;
            });

            // Group jobs by status name
            $groupedJobs = $jobsWithProgress->map(function ($job) {
                $job->job_summaries = $job->jobSummaries ? $job->jobSummaries->map(function ($summary) {
                    return [
                        'job_total' => $summary->job_total ?? 0,
                        'claim_number' => $summary->claim_number,
                    ];
                }) : collect([]);

                return $job;
            })->groupBy(function ($job) {
                return $job->status->name;
            });

            // Define statuses
            $statuses = Status::whereIn('name', [
               'New Leads',
                'Signed Deals',
                // 'Estimate Prepared', //excluded
                'Adjuster',
                'Ins Under Review', 
                'Overturn',
                'Appraisal',  
                'Approved',
                'Ready To Build',
                'Build Scheduled',
                'In Progress',
                'Build Complete',
                'COC Required',
                'Final Payment Due',
                'Ready to Close',
                'Supplement Submitted',
                'Won and Closed',
                'Lost',
                'Unqualified', 
            ])->get();

            // Map statuses with job totals and tasks
            $response = $statuses->map(function ($status) use ($groupedJobs, $user, $created_by, $jobsWithProgress) {
                $jobTotalSumQuery = CompanyJobSummary::whereHas('job', function ($query) use ($status) {
                    $query->where('status_id', $status->id);
                });

                // Apply `created_by` filter for role_id 1 and 2
                if ($user->role_id == 1 || $user->role_id == 2) {
                    $jobTotalSumQuery->whereHas('job', function ($query) use ($created_by) {
                        $query->where('created_by', $created_by);
                    });
                }

                $jobTotalSum = $jobTotalSumQuery->sum('job_total');

                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'job_total' => $jobTotalSum,
                    // 'tasks' => $groupedJobs->get($status->name, collect()),
                    'tasks' => $jobsWithProgress,
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Jobs Found Successfully',
                'data' => $response
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()
            ], 500);
        }
    }

    public function getSingleJob($id)
    {
        try {
            //Check Job
            $job = CompanyJob::whereId($id)->with('summary')->first();
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Found Successfully',
                'job' => $job
            ], 200); 
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateJobSummary(Request $request, $id)
    {
        // dd("sds");
        //Validate Request
        $this->validate($request, [
            'job_total' => 'nullable',
            'first_payment' => 'nullable',
            'first_payment_cheque_number' => 'nullable',
            'deductable' => 'nullable',
            'deductable_cheque_number' => 'nullable',
            'final_payment' => 'nullable',
            'final_payment_cheque_number' => 'nullable',
            'upgrades' => 'nullable',
            'upgrades_cheque_number' => 'nullable',
            'balance' => 'nullable',
            // 'invoice_number' => 'nullable',
            // 'market' => 'nullable|in:Nashville,Chattanooga',
            // 'lead_source' => 'nullable|in:Door Knocking,Customer Referral,Call In,Facebook,Family Member,Home Advisor,Website,Social Encounter',
            // 'insurance' => 'nullable',
            // 'policy_number' => 'nullable',
            // 'email' => 'nullable|email',
            // 'insurance_representative' => 'nullable',
            // 'user_ids' => 'nullable|array',
            // 'user_ids.*' => 'integer|exists:users,id'
        ]);

        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            
            if ($request->has('job_total')) { //when working with job total value adding api just pass the job_total value no pass other else check is not working
                $jobtotal = $request->job_total;
                $totalAmountpaid = Payment::where('company_job_id', $id)->sum('payment_amount');
                
               if($jobtotal < $totalAmountpaid)
                {
                    // dd("you have paid more amount then job total value");
                    return response()->json([
                        'status_code' => 200,
                        'msg' => 'Paid Amount is greater then job total value' //increase the value or delete the amount
                    ]);
                }
                // $amount = $jobtotal - $totalAmount;
                // dd($amount);
            }
            
            
            

            //Update Job Summary
            $job_summary = CompanyJobSummary::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'job_total' => $request->job_total,
                'first_payment' => $request->first_payment,
                'first_payment_cheque_number' => $request->first_payment_cheque_number,
                'deductable' => $request->deductable,
                'deductable_cheque_number' => $request->deductable_cheque_number,
                'upgrades' => $request->upgrades,
                'upgrades_cheque_number' => $request->upgrades_cheque_number,
                'final_payment' => $request->final_payment,
                'final_payment_cheque_number' => $request->final_payment_cheque_number,
                'balance' => $request->balance,
                'is_fully_paid' => 'no',
                'full_payment_date' => null
                // 'invoice_number' => $request->invoice_number,
                // 'market' => $request->market,
                // 'lead_source' => $request->lead_source,
                // 'insurance' => $request->insurance,
                // 'policy_number' => $request->policy_number,
                // 'email' => $request->email,
                // 'insurance_representative' => $request->insurance_representative
            ]);

            // $job_payment = Payment::updateOrCreate([
            //     'company_job_id' => $id
            // ],
            // [
            //     'company_job_id' => $id,
            //     'payment_amount' 
            // ])

            // Assign Job To Users
            // if(isset($request->user_ids) && count($request->user_ids) > 0) {
            //     $job->users()->sync($request->user_ids);
            // }

            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Updated Successfully',
                'job' => $job_summary
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getJobSummary($id)
    {
        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $job_summary = CompanyJobSummary::where('company_job_id', $job->id)->first();
            if(!$job_summary) {
                
                //Create Object
                $object = new \StdClass;
                $object->address = $job->address;
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Job Summary Not Yet Created',
                    'job' => $object
                ], 200);
            }
            
            $job_summary->user_ids = $job->users()->pluck('user_id')->toArray();
            $job_summary->address = $job->address;

            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Found Successfully',
                'job' => $job_summary
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function updateJobSummaryInsuranceInformation(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'insurance' => 'nullable',
            'policy_number' => 'nullable',
            'email' => 'nullable|email',
            'insurance_representative' => 'nullable',
            'claim_number' => 'nullable',
        ]);
        
        try {
            
            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }
            
            //Update Job Summary
            $job_summary = CompanyJobSummary::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'insurance' => $request->insurance,
                'policy_number' => $request->policy_number,
                'email' => $request->email,
                'insurance_representative' => $request->insurance_representative,
                'claim_number' => $request->claim_number
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Updated Successfully',
                'job' => $job_summary
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getJobSummaryInsuranceInformation($id)
    {
        try {
            
            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $job_summary = CompanyJobSummary::select('id','insurance','policy_number','email','insurance_representative','claim_number')
            ->where('company_job_id', $job->id)->first();
            if(!$job_summary) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Job Summary Not Yet Created',
                    'job' => $job_summary
                ], 200);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Found Successfully',
                'job' => $job_summary
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function updateJobSummaryInitialInformation(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'invoice_number' => 'nullable',
            'market' => 'nullable',
            'job_type' => 'nullable|in:Retail,Insurance',
            'lead_source' => 'nullable|in:Door Knocking,Customer Referral,Call In,Facebook,Family Member,Home Advisor,Website,Social Encounter',
            'lead_status' => 'nullable|in:New,Contacted,Follow-up Needed',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',

            'name' => 'nullable|string',
            'profile_path' => 'nullable|file',
        ]);
        
        try {
            
            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            
            $fileFinalName = $job->profile_path; // Set a default value

            if (isset($request->profile_path)) {
                $file = $request->profile_path;
                $fileName = $file->getClientOriginalName();
                $fileExtension = $file->getClientOriginalExtension();

                $fileFinalName = rand(0000,9999).'_'. time().'.'.$fileExtension;
                $fileDestination = "storage/profile_photos";
                $file->move($fileDestination,$fileFinalName);

                $job->profile_path = 'profile_photos/'.$fileFinalName;

            }
            
            //Update Job Summary
            $job_summary = CompanyJobSummary::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'invoice_number' => $request->invoice_number,
                'market' => $request->market,
                'lead_source' => $request->lead_source,
                'job_type' => $request->job_type,
                'lead_status' => $request->lead_status,
                'customer_name' => $request->name,


            ]);

            $job->name = $request->name;
            // $job->profile_path = isset($fileFinalName) ? 'profile_photos/'.$fileFinalName : Null;
            $job->save();


            
            // Assign Job To Users
            if(isset($request->user_ids) && count($request->user_ids) > 0) {
                $job->users()->sync($request->user_ids);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Updated Successfully',
                'job' => [
                    'id'=> $job_summary->id,
                    'email' => $job->email,
                    'phone' => $job->phone,

                    'address' => json_decode($job->address, true)['formatedAddress'] ?? null, 
                    'company_job_id'=> $job_summary->company_job_id,
                    'invoice_number' => $job_summary->invoice_number,
                    'market' => $job_summary->market,
                    'lead_source'=> $job_summary->lead_source,
                    'insurance' => $job_summary->insurance,
                    'policy_number' => $job_summary->policy_number,
                    // 'email' => $job_summary->email,
                    'insurance_representative'=> $job_summary->insurance_representative,
                    'claim_number' => $job_summary->claim_number,
                    'job_type'=> $job_summary->job_type,
                    'lead_status' => $job_summary->lead_status,
                    
                ],
                'profile_path' => asset('storage/' . $job->profile_path),
                // 'profile_path' => $job->profile_path,
                'name' => $job_summary->customer_name,

            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getJobSummaryInitialInformation($id)
    {
        try {
            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $job_summary = CompanyJobSummary::select('id','invoice_number','market','lead_source','job_type','market','job_type','lead_status','customer_name')
            ->where('company_job_id', $job->id)->first();

            $location= Location::select('id','name')->get();
            // dd($location);
            // $location_ids = Location::pluck('id')->toArray(); // Retrieve location IDs as an array

            if(!$job_summary) {
                
                //Create Object
                $object = new \StdClass;
                $object->address = json_decode($job->address, true)['formatedAddress'] ?? null;
                $object->email = $job->email;
                $object->phone = $job->phone;
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Job Summary Not Yet Created',
                    'job' => $object,

                    'locations' => $location,
                ], 200);
            }
            
            $job_summary->user_ids = $job->users()->pluck('user_id')->toArray();
            $job_summary->address = json_decode($job->address, true)['formatedAddress'] ?? null;
            $job_summary->email = $job->email;
            $job_summary->phone = $job->phone;

            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Found Successfully',
                'job' => $job_summary,
                'name' => $job_summary->customer_name,
                'profile_path'=> asset('storage/' . $job->profile_path),

                'locations' => $location,

            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateJobContentold(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'notes' => 'nullable',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:png,jpg,jpeg,gif'
        ]);

        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update Job Content
            $content = CompanyJobContent::updateOrCreate([
                'company_job_id' => $id,
            ],[
                'company_job_id' => $id,
                'notes' => $request->notes,
            ]);
            

            if(isset($request->images)) {
                //Store New Images
                foreach($request->file('images') as $file)
                {
                    $fileName = $file->getClientOriginalName();
                    $fileName = time() . '_' . $fileName;
                    $path = $file->storeAs('public/job_content_media', $fileName);

                    //Update Job Content
                    $media = new CompanyJobContentMedia;
                    $media->content_id = $content->id;
                    $media->media_url = Storage::url($path);
                    $media->save();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Content Updated Successfully',
                'job' => $job
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateJobContent(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'notes' => 'nullable',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:png,jpg,jpeg,gif'
        ]);

        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            //Update Job Content

            $content = CompanyJobContent::where('company_job_id',$id)->first();
            if($content)
            {
                $content->notes = $request->notes;
                $content->save();
            }else{
                $content = new CompanyJobContent();
                $content->company_job_id = $id;
                $content->notes = $request->notes;
                $content->save();
            }
          

            if(isset($request->images)) {
                //Store New Images
                foreach($request->file('images') as $file)
                {
                    $fileName = $file->getClientOriginalName();
                    $fileName = time() . '_' . $fileName;
                    $path = $file->storeAs('public/job_content_media', $fileName);

                    //Update Job Content
                    $media = new CompanyJobContentMedia;
                    $media->content_id = $content->id;
                    $media->media_url = Storage::url($path);
                    $media->save();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Job Content Updated Successfully',
                'job' => $job
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getJobContent($id)
    {
        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $job_content = CompanyJobContent::where('company_job_id', $job->id)->with('images')->first();
            if(!$job_content) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Content Not Found'
                ], 422);
            }

            return response()->json([ 
                'status' => 200,
                'message' => 'Job Content Found Successfully',
                'job' => $job_content
            ], 200); 

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateJobContentFileName(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'file_name' => 'required|string'
        ]);

        try {

            //Check Company Job Content
            $check_media = CompanyJobContentMedia::find($id);
            if(!$check_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Content Not Found'
                ], 422);
            }

            //Update File Name
            $check_media->file_name = $request->file_name;
            $check_media->save();

            return response()->json([
                'status' => 200,
                'message' => 'File Name Updated Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function deleteJobContentMedia(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'image_url' => 'required|string'
        ]);

        try {

            //Check Company Job Content
            $check_media = CompanyJobContentMedia::find($id);
            if(!$check_media) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Content Not Found'
                ], 422);
            }

            //Delete Media
            $oldFilePath = str_replace('/storage', 'public', $check_media->media_url);
            Storage::delete($oldFilePath);
            $check_media->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Media Deleted Successfully',
                'data' => []
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateJobStatus(Request $request, $id)
    {
        //Validate Request
        $this->validate($request, [
            'status_id' => 'required|integer'
        ]);

        try {

            //Check Job
            $job = CompanyJob::find($id);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }

            $job->status_id = $request->status_id;
            $job->save();

            return response()->json([
                'status' => 200,
                'message' => 'Job Status Updated Successfully',
                'job' => $job
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getTaskWithJobCount1()
    {
        try {
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by; 
            $specificStatuses = ['New Leads', 'Signed Deals', 'Estimate Prepared', 'Adjustor', 'Ready To Build', 'Build Scheduled', 'In Progress', 'Build Complete', 'Final Payment Due', 'Ready to Close', 'Won and Closed'];

            if (in_array($user->role_id, [1, 2, 7])) {
                $tasks = Status::whereIn('name', $specificStatuses)
                    ->withCount([
                        'tasks as jobs_count' => function ($query) use ($created_by) {
                            $query->where('created_by', $created_by);
                        },
                        'jobSummaries as job_total' => function ($query) use ($created_by) {
                            $query->whereHas('job', function ($q) use ($created_by) {
                                $q->where('created_by', $created_by);
                            });
                        }
                    ])
                    ->get();
            } else {
                $tasks = Status::whereIn('name', $specificStatuses)
                    ->withCount([
                        'tasks as jobs_count' => function ($query) use ($user, $assigned_jobs) {
                            $query->where('user_id', $user->id)
                                ->orWhereIn('id', $assigned_jobs);
                        },
                        'jobSummaries as job_total' => function ($query) use ($user, $assigned_jobs) {
                            $query->whereHas('job', function ($q) use ($user, $assigned_jobs) {
                                $q->where('user_id', $user->id)
                                ->orWhereIn('id', $assigned_jobs);
                            });
                        }
                    ])
                    ->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Tasks Found Successfully',
                'data' => $tasks
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getTaskWithJobCount()
    {
        try {
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by; 
            // $specificStatuses = ['New Leads', 'Signed Deals', 'Estimate Prepared', 'Adjustor', 'Ready To Build', 'Build Scheduled', 'In Progress', 'Build Complete', 'Final Payment Due', 'Ready to Close', 'Won and Closed'];
            $specificStatuses=[
                            'New Leads',
                            'Customer Agreement',
                            // 'Estimate Prepared', //excluded show only when job_type is insurance
                            'Adjuster Scheduled',
                            'Ins Under Review', 
                            'Overturn',
                            'Appraisal',  
                            'Approved',
                            'Ready To Build',
                            'Build Scheduled',
                            'In Progress',
                            'Build Complete',
                            'COC Required',
                            'Final Payment Due',
                            'Ready to Close',
                            // 'Supplement Submitted',
                            'Won and Closed',
                            'Lost',
                            'Unqualified',
            ];
            if (in_array($user->role_id, [1, 2, 7])) {
                $tasks = Status::whereIn('name', $specificStatuses)
                    ->withCount([
                        'tasks as jobs_count' => function ($query) use ($created_by) {
                            $query->where('created_by', $created_by);
                        }
                    ])
                    ->withSum([
                        'jobSummaries as job_total' => function ($query) use ($created_by) {
                            $query->whereHas('job', function ($q) use ($created_by) {
                                $q->where('created_by', $created_by);
                            });
                        }
                    ], 'job_total')
                    ->get();
            } else {
                $tasks = Status::whereIn('name', $specificStatuses)
                    ->withCount([
                        'tasks as jobs_count' => function ($query) use ($user, $assigned_jobs) {
                            $query->where('user_id', $user->id)
                                ->orWhereIn('id', $assigned_jobs);
                        }
                    ])
                    ->withSum([
                        'jobSummaries as job_total' => function ($query) use ($user, $assigned_jobs) {
                            $query->whereHas('job', function ($q) use ($user, $assigned_jobs) {
                                $q->where('user_id', $user->id)
                                ->orWhereIn('id', $assigned_jobs);
                            });
                        }
                    ], 'job_total')
                    ->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Tasks Found Successfully',
                'data' => $tasks
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    public function getV1TaskWithJobCount(Request $request)
    {
        $request->validate([
            'job_type' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;

            $statuses = Status::all();

            // Initialize an array to store the data for each status
            $data = $statuses->map(function ($status) use ($user, $assigned_jobs, $created_by, $request) {
                // Base query for jobs associated with this status
                if ($user->role_id == 1 || $user->role_id == 2) {
                    $jobsQuery = CompanyJob::where('created_by', $created_by)
                        ->where('status_id', $status->id);
                } else {
                    $jobsQuery = CompanyJob::whereIn('id', $assigned_jobs)
                        ->where('status_id', $status->id);
                }

                if ($request->job_type) {
                    $jobsQuery->whereHas('summary', function ($query) use ($request) {
                        $query->where('job_type', $request->job_type);
                    });
                }
                if ($request->location) {
                    $jobsQuery->whereHas('summary', function ($query) use ($request) {
                        $query->where('market', $request->location);
                    });
                }

                // Count the jobs for this status
                $jobCount = $jobsQuery->count();

                // Fetch job details with the filtered query
                $jobs = $jobsQuery
                    ->with('summary:company_job_id,balance,job_type,market') // Load only necessary fields from the summary
                    ->orderBy('status_id', 'asc')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($job) {
                        $job->amount = $job->summary->balance ?? 0;
                        return $job;
                    });

                return [
                    'status' => $status->name,
                    'job_count' => $jobCount,
                    'jobs' => $jobs,
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Jobs Found Successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }


    public function getJobWithStatus($statusId, Request $request)
{
    $request->validate([
        'job_type' => 'nullable',
        'market' => 'nullable',
    ]);

    try {
        // Check Status
        $task = Status::find($statusId);
        if (!$task) {
            return response()->json([
                'status' => 422,
                'message' => 'Task Not Found',
            ], 422);
        }

        $user = Auth::user();
        $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
        $created_by = $user->created_by == 0 ? 1 : $user->created_by;
        $market = $request->input('market'); // Get the market filter from the request
        $jobType = $request->input('job_type'); // Get the job_type filter from the request

        if ($user->role_id == 1 || $user->role_id == 2) {
            $jobs = CompanyJob::where('created_by', $created_by)
                ->where('status_id', $statusId)
                ->when($market && $market !== 'all', function ($query) use ($market) {
                    // Apply market filter only if it's not "all"
                    $query->whereHas('summary', function ($q) use ($market) {
                        $q->where('market', $market);
                    });
                })
                ->when($jobType && $jobType !== 'all', function ($query) use ($jobType) {
                    // Apply job_type filter only if it's not "all"
                    $query->whereHas('summary', function ($q) use ($jobType) {
                        $q->where('job_type', $jobType);
                    });
                })
                ->with('summary') // Load only necessary fields from the summary
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($job) {
                    // Assign amount based on the summary balance or set it to 0 if summary is null
                    $job->amount = $job->summary->balance ?? 0;
                    return $job;
                });
        } else {
            $jobs = CompanyJob::whereIn('id', $assigned_jobs)
                ->where('status_id', $statusId)
                ->when($market && $market !== 'all', function ($query) use ($market) {
                    // Apply market filter only if it's not "all"
                    $query->whereHas('summary', function ($q) use ($market) {
                        $q->where('market', $market);
                    });
                })
                ->when($jobType && $jobType !== 'all', function ($query) use ($jobType) {
                    // Apply job_type filter only if it's not "all"
                    $query->where('job_type', $jobType);
                })
                ->with('summary') // Load only necessary fields from the summary
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($job) {
                    $job->amount = $job->summary->balance ?? 0;
                    return $job;
                });
        }

        $task->jobs = $jobs;

        return response()->json([
            'status' => 200,
            'message' => 'Jobs Found Successfully',
            'data' => $task,
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
    }
}

    
    public function getJobWithStatus22($statusId, Request $request)
    {
        $request->validate([
            'job_type'=>'nullable',
            'market' => 'nullable'
        ]);

        try {
            // Check Status
            $task = Status::find($statusId);
            if(!$task) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Task Not Found'
                ], 422);
            }
            
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;
            $market = $request->input('market'); // Get the market filter from the request
            $jobType = $request->input('job_type'); // Get the job_type filter from the request

            if($user->role_id == 1 || $user->role_id == 2) {

                $jobs = CompanyJob::where('created_by', $created_by)
                ->where('status_id', $statusId)
                ->when($market, function ($query) use ($market) {
                    // Apply market filter if provided
                    $query->whereHas('summary', function ($q) use ($market) {
                        $q->where('market', $market);
                    });
                })
                ->when($jobType, function ($query) use ($jobType) {
                    $query->whereHas('summary', function ($q) use ($jobType) {
                        $q->where('job_type', $jobType);
                    });     
                           })
                ->with('summary') // Load only necessary fields from the summary
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($job) {
                    // Assign amount based on the summary balance or set it to 0 if summary is null
                    $job->amount = $job->summary->balance ?? 0;
                    return $job;
                });
                
                
            } else {
                $jobs = CompanyJob::whereIn('id', $assigned_jobs)
                ->where('status_id', $statusId)
                ->when($market, function ($query) use ($market) {
                    // Apply market filter if provided
                    $query->whereHas('summary', function ($q) use ($market) {
                        $q->where('market', $market);
                    });
                })
                ->when($jobType, function ($query) use ($jobType) {
                    $query->where('job_type', $jobType);
                })
                ->with('summary') // Load only necessary fields from the summary
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($job) {
                    $job->amount = $job->summary->balance ?? 0;
                    return $job;
                });
                
            }
            
            $task->jobs = $jobs;
            
            return response()->json([
                'status' => 200,
                'message' => 'Jobs Found Successfully',
                'data' => $task
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function dashboardStats()
    {
        try { 
            
            $data = [];
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            // dd($assigned_jobs);
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;
            
            // Define start and end of the current week (Sunday to Saturday)
            $weekStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $weekEnd = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            
            // Define start and end of the current month
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            
            // Get the start and end of the current year
            $yearStart = Carbon::now()->startOfYear();
            $yearEnd = Carbon::now()->endOfYear();
            
            // Get the date 6 days ago
            $sixDaysAgo = Carbon::now()->subDays(6);
            
            if($user->role_id == 1 || $user->role_id == 2) {
                //Weekly Main Query
                $weekly_tasks = CompanyJob::select('id','name','address','status_id')->where('created_by', $created_by)
                ->whereBetween('created_at', [$weekStart, $weekEnd]);
                // return response()->json([$weekly_tasks->get()]);

                //Monthly Main Query
                $monthly_tasks = CompanyJob::select('id','name','address','status_id')->where('created_by', $created_by)
                ->whereBetween('created_at', [$monthStart, $monthEnd]);

                // return response()->json([$monthly_tasks->get()]);
                
                // Yearly Query
                $yearly_tasks = CompanyJob::select('id', 'name', 'address', 'status_id')->where('created_by', $created_by)
                ->whereBetween('created_at', [$yearStart, $yearEnd]);
                
                
                //New Leads Count
                $weekly_new_leads = $weekly_tasks->count();
                $monthly_new_leads = $monthly_tasks->count();
                
                //Won & Closed Count
                $weekly_won_closed = $weekly_tasks->whereHas('wonAndClosed')->count();
                $monthly_won_closed = $monthly_tasks->whereHas('wonAndClosed')->count();
                $yearly_won_closed = $yearly_tasks->whereHas('wonAndClosed')->count();
                
                //Won & Closed Values
                //Weekly
                $weekly_won_closed_values = $weekly_tasks->whereHas('wonAndClosed')
                    ->whereHas('summary')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                ->get();
                
                $weekly_balances = $weekly_won_closed_values->pluck('summary.balance');
                $weekly_total_balance = $weekly_balances->sum();
                
                //Monthly
                $monthly_won_closed_values = $monthly_tasks->whereHas('wonAndClosed')
                    ->whereHas('summary')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                ->get();
                
                $monthly_balances = $monthly_won_closed_values->pluck('summary.balance');
                $monthly_total_balance = $monthly_balances->sum();
                
                //Current Year
                $yearly_won_closed_values = $yearly_tasks
                    ->whereHas('wonAndClosed')
                    ->whereHas('summary')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get();
                
                $yearly_balances = $yearly_won_closed_values->pluck('summary.balance');
                $yearly_total_balance = $yearly_balances->sum();
                
                //Customers
                $jobs = CompanyJob::select('id','name','address','status_id')->where('created_by', $created_by);
                
                //Deals Won & Closed
                $deals = $jobs->whereHas('wonAndClosed')
                    ->whereHas('summary')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                ->get();
                
                $balances = $deals->pluck('summary.balance');
                $total_balance = $balances->sum();
                
                //New Leads
                $new_leads = CompanyJob::where('created_by', $created_by)->where('status_id', 1)->count();
                // return response()->json([$new_leads]);

                //InProgress
                $in_progress = CompanyJob::where('created_by', $created_by)->where('status_id', 10)->count();
                
                //Needs Attention
                $stale_jobs = CompanyJob::with('status')->where('created_by', $created_by)
                // ->where('date', '<', $sixDaysAgo)
                ->get();
                
                // Process each job to assign a status
                foreach ($stale_jobs as $job) {
                    $daysSinceCreated = now()->diffInDays($job->date);

                    if ($daysSinceCreated <= 6) {
                        $job->statusCircle = 'Green'; // 0-6 days
                    } elseif ($daysSinceCreated <= 14) {
                        $job->statusCircle = 'Yellow'; // 7-14 days
                    } else {
                        $job->statusCircle = 'Red'; // 17+ days
                    }
                }
                // Prepare response data
                $data = [
                    'weekly' => [
                        'new_leads' => $weekly_new_leads,
                        'won_closed' => $weekly_won_closed,
                        'won_closed_values' => $weekly_total_balance,
                    ],
                    'monthly' => [
                        'new_leads' => $monthly_new_leads,
                        'won_closed' => $monthly_won_closed,
                        'won_closed_values' => $monthly_total_balance,
                    ],
                    'current_jobs' => [
                        'new_leads' => $new_leads,
                        'in_progress' => $in_progress
                    ],
                    'current_year' => [
                      'total_revenue_generated' => $yearly_total_balance,
                      'won_closed' => $yearly_won_closed
                    ],
                    'customers' => $jobs->count(),
                    'deals_won_closed' => $total_balance,
                    'needs_attention' => $stale_jobs
                ];
                
            } else {
                //Weekly Main Query
                $weekly_tasks = CompanyJob::select('id','name','address','status_id')->where(function($query) use ($user,$assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                })
                ->whereBetween('created_at', [$weekStart, $weekEnd]);
                // return response()->json(['weekly'=>$weekly_tasks->get()]);
                // dd($weekly_tasks);
                
                //Monthly Main Query
                $monthly_tasks = CompanyJob::select('id','name','address','status_id')->where(function($query) use ($user,$assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                })
                ->whereBetween('created_at', [$monthStart, $monthEnd]);
                // return response()->json(['monthly'=>$monthly_tasks->get()]);

                // Yearly Query
                $yearly_tasks = CompanyJob::select('id', 'name', 'address', 'status_id')->where(function ($query) use ($user, $assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                })
                ->whereBetween('created_at', [$yearStart, $yearEnd]);
                
                
                //New Leads Count
                $weekly_new_leads = $weekly_tasks->count();
                // return response()->json(['weekly'=>$weekly_new_leads]);
                $monthly_new_leads = $monthly_tasks->count();
                // return response()->json(['monthly'=>$monthly_new_leads]);

                //Won & Closed Count  wonAndClosed
                // $weekly_won_closed = $weekly_tasks->whereHas('adjustorMeeting')->count();
                $weekly_won_closed = $weekly_tasks->whereHas('wonAndClosed')->count();
                $monthly_won_closed = $monthly_tasks->whereHas('wonAndClosed')->count();
                $yearly_won_closed = $yearly_tasks->whereHas('wonAndClosed')->count();
                
                //Won & Closed Values
                //Weekly
                $weekly_won_closed_values = $weekly_tasks->whereHas('wonAndClosed')
                    ->whereHas('summary')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                ->get();

                //  return response()->json(['values'=>$weekly_won_closed_values]);
                
                $weekly_balances = $weekly_won_closed_values->pluck('summary.balance');
                // return response()->json(['values'=>$weekly_balances]);

                $weekly_total_balance = $weekly_balances->sum();
                // return response()->json(['values'=>$weekly_total_balance]);

                //Monthly
                $monthly_won_closed_values = $monthly_tasks->whereHas('wonAndClosed')
                    ->whereHas('summary')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                ->get();
                
                $monthly_balances = $monthly_won_closed_values->pluck('summary.balance');
                $monthly_total_balance = $monthly_balances->sum();
                
                //Current Year
                $yearly_won_closed_values = $yearly_tasks
                    ->whereHas('wonAndClosed')
                    ->whereHas('summary')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get();
                
                $yearly_balances = $yearly_won_closed_values->pluck('summary.balance');
                $yearly_total_balance = $yearly_balances->sum();
                
                //Customers
                $jobs = CompanyJob::select('id','name','address','status_id')->where(function($query) use ($user,$assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                });
                
                //Deals Won & Closed
                $deals = $jobs->whereHas('wonAndClosed')
                    ->whereHas('summary')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                ->get();
                
                $balances = $deals->pluck('summary.balance');
                $total_balance = $balances->sum();
                
                //New Leads
                $new_leads = CompanyJob::where(function($query) use ($user,$assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                })->where('status_id', 1)->count();

                // return response()->json($new_leads);
                
                //InProgress
                $in_progress = CompanyJob::where(function($query) use ($user,$assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                })->where('status_id', 10)->count();
                
                //Needs Attention
                $stale_jobs = CompanyJob::with('status')->where(function($query) use ($user, $assigned_jobs) {
                $query->orWhere('user_id', $user->id)
                    ->orWhereIn('id', $assigned_jobs);
            })
            // ->where('date', '<', $sixDaysAgo)
            ->get();

                // Process each job to assign a status
                foreach ($stale_jobs as $job) {
                    $daysSinceCreated = now()->diffInDays($job->date);

                    if ($daysSinceCreated <= 6) {
                        $job->statusCircle = 'Green'; // 0-6 days
                    } elseif ($daysSinceCreated <= 14) {
                        $job->statusCircle = 'Yellow'; // 7-14 days
                    } else {
                        $job->statusCircle = 'Red'; // 17+ days
                    }
                }

        // Now you can use $stale_jobs with their statuses assigned

                
                // Prepare response data
                $data = [
                    'weekly' => [
                        'new_leads' => $weekly_new_leads,
                        'won_closed' => $weekly_won_closed,
                        'won_closed_values' => $weekly_total_balance,
                    ],
                    'monthly' => [
                        'new_leads' => $monthly_new_leads,
                        'won_closed' => $monthly_won_closed,
                        'won_closed_values' => $monthly_total_balance,
                    ],
                    'current_jobs' => [
                        'new_leads' => $new_leads,
                        'in_progress' => $in_progress
                    ],
                    'current_year' => [
                      'total_revenue_generated' => $yearly_total_balance,
                      'won_closed' => $yearly_won_closed
                    ],
                    'customers' => $jobs->count(),
                    'deals_won_closed' => $total_balance,
                    'needs_attention' => $stale_jobs
                ];
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Weekly Stats',
                'data' => $data
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function dashboardStatsDetail(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'type' => 'required|in:weekly,monthly',
            'box' => 'required|in:new_leads,won_closed,won_closed_values'
        ]);
        
        try {
            
            $data = [];
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;
            
            // Define start and end of the current week (Sunday to Saturday)
            $weekStart = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $weekEnd = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            
            // Define start and end of the current month
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            
            if($user->role_id == 1 || $user->role_id == 2) {
                //Weekly Main Query
                $weekly_tasks = CompanyJob::where('created_by', $created_by)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc');
                
                //Monthly Main Query
                $monthly_tasks = CompanyJob::where('created_by', $created_by)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc');
                
                if($request->type == 'weekly' && $request->box == 'new_leads') {
                    //New Leads
                    $data = $weekly_tasks
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get()
                    ->map(function ($job) {
                        // If there's no summary, set it to an empty array
                        $job->summary = $job->summary ?? []; 
                        return $job;
                    });

                } else if($request->type == 'weekly' && $request->box == 'won_closed') {
                    //Won & Closed
                    $data = $weekly_tasks->whereHas('wonAndClosed')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get()
                    ->map(function ($job) {
                        // If there's no summary, set it to an empty array
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });
                } else if($request->type == 'weekly' && $request->box == 'won_closed_values') {
                    //Won & Closed Values
                    $weekly_won_closed_values = $weekly_tasks->whereHas('wonAndClosed')
                        ->with(['summary' => function ($query) {
                            $query->select('company_job_id', 'balance');
                        }])
                    ->get()
                    ->map(function ($job) {
                        // If there's no summary, set it to an empty array
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });
                    
                    $weekly_balances = $weekly_won_closed_values->pluck('summary.balance');
                    $weekly_total_balance = $weekly_balances->sum();
                    $data = $weekly_won_closed_values;
                } else if($request->type == 'monthly' && $request->box == 'new_leads') {
                    //New Leads
                    // $data = $monthly_tasks->get();
                    $data = $monthly_tasks
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });

                } else if($request->type == 'monthly' && $request->box == 'won_closed') {
                    //Won & Closed
                    // $data = $monthly_tasks->whereHas('wonAndClosed')->get();

                    $data = $monthly_tasks->whereHas('wonAndClosed')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; 
                        return $job;
                    });

                } else if($request->type == 'monthly' && $request->box == 'won_closed_values') {
                    //Won & Closed Values
                    $monthly_won_closed_values = $monthly_tasks->whereHas('wonAndClosed')
                        ->with(['summary' => function ($query) {
                            $query->select('company_job_id', 'balance');
                        }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });
                    
                    $monthly_balances = $monthly_won_closed_values->pluck('summary.balance');
                    $monthly_total_balance = $monthly_balances->sum();
                    $data = $monthly_won_closed_values;
                }

            } else {
                //Weekly Main Query
                $weekly_tasks = CompanyJob::where(function($query) use ($user,$assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                })
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc');

                // return response()->json($weekly_tasks->get());

                
                //Monthly Main Query
                $monthly_tasks = CompanyJob::where(function($query) use ($user,$assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                })
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc');
                
                if($request->type == 'weekly' && $request->box == 'new_leads') {
                    //New Leads
                    $data = $weekly_tasks
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });

                } else if($request->type == 'weekly' && $request->box == 'won_closed') {
                    //Won & Closed
                    $data = $weekly_tasks->whereHas('wonAndClosed')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });
                } else if($request->type == 'weekly' && $request->box == 'won_closed_values') {
                    //Won & Closed Values
                    $weekly_won_closed_values = $weekly_tasks->whereHas('wonAndClosed')
                        ->with(['summary' => function ($query) {
                            $query->select('company_job_id', 'balance');
                        }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });
                    
                    $weekly_balances = $weekly_won_closed_values->pluck('summary.balance');
                    $weekly_total_balance = $weekly_balances->sum();
                    $data = $weekly_won_closed_values;
                } else if($request->type == 'monthly' && $request->box == 'new_leads') {
                    //New Leads
                    // $data = $monthly_tasks->get();
                    $data = $monthly_tasks
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });
                } else if($request->type == 'monthly' && $request->box == 'won_closed') {
                    //Won & Closed
                    // $data = $monthly_tasks->whereHas('wonAndClosed')->get();
                    $data = $monthly_tasks->whereHas('wonAndClosed')
                    ->with(['summary' => function ($query) {
                        $query->select('company_job_id', 'balance');
                    }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; 
                        return $job;
                    });
                } else if($request->type == 'monthly' && $request->box == 'won_closed_values') {
                    //Won & Closed Values
                    $monthly_won_closed_values = $monthly_tasks->whereHas('wonAndClosed')
                        ->with(['summary' => function ($query) {
                            $query->select('company_job_id', 'balance');
                        }])
                    ->get()
                    ->map(function ($job) {
                        $job->summary = $job->summary ?? []; // Use null coalescing operator
                        return $job;
                    });
                    
                    $monthly_balances = $monthly_won_closed_values->pluck('summary.balance');
                    $monthly_total_balance = $monthly_balances->sum();
                    $data = $monthly_won_closed_values;
                }
            }
            
            return response()->json([
                'status' => 200,
                'message' => $request->type . ' detail for ' . $request->box . ' found successfully',
                'data' => $data
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function filterJobs(Request $request) //for grid view
    {
        $request->validate([
            'job_type' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by; 
            // dd($created_by);

            $specificStatuses = ['New Leads', 'Customer Agreement', 'Adjuster Scheduled','Ins Under Review','Overturn','Appraisal','Approved', 'Ready To Build', 'Build Scheduled', 'In Progress', 'Build Complete','COC Required', 'Final Payment Due', 'Ready to Close','Won and Closed','Lost','Unqualified'];

            if ($user->role_id == 1 || $user->role_id == 2) {
                $tasks = Status::whereIn('name', $specificStatuses)
                    ->withCount([
                        'tasks as jobs_count' => function ($query) use ($created_by, $request) {
                            $query->where('created_by', $created_by);
                            
                            if ($request->job_type) {
                                $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                    $q->where('job_type', $request->job_type);
                                });
                            }
                            
                            if ($request->location) {
                                $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                    $q->where('market', $request->location);
                                });
                            }
                        }
                    ])->get();
            } else {
                $tasks = Status::whereIn('name', $specificStatuses)
                    ->withCount([
                        'tasks as jobs_count' => function ($query) use ($user, $assigned_jobs, $request) {
                            $query->where('user_id', $user->id);
                            $query->orWhereIn('id', $assigned_jobs);
                            
                            if ($request->job_type) {
                                $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                    $q->where('job_type', $request->job_type);
                                });
                            }

                            if ($request->location) {
                                $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                    $q->where('market', $request->location);
                                });
                            }
                        }
                    ])->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Data Fetched Successfully',
                'data' => $tasks
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function filterJobskanban1(Request $request) // for kanban
    {
        $request->validate([
            'job_type' => 'nullable|array',
            'job_type.*'=>'nullable|string',
            'location' => 'nullable|string',
            'stages' => 'nullable|array',
            'stages.*' => 'nullable|string',
            'sort_by' => 'nullable|string|in:last_updated_newest,last_updated_oldest,created_date_newest,created_date_oldest,name,value_high,value_low,time_in_stage_newest,time_in_stage_oldest',

        ]);
    
        try {
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;
    
            $sortField = 'updated_at';
            $sortOrder = 'desc';

        // Determine sorting based on request
            if (!empty($request->sort_by)) {
                switch ($request->sort_by) {
                    case 'last_updated_newest':
                        $sortField = 'updated_at';
                        $sortOrder = 'desc';
                        break;
                    case 'last_updated_oldest':
                        $sortField = 'updated_at';
                        $sortOrder = 'asc';
                        break;
                    case 'created_date_newest':
                        $sortField = 'created_at';
                        $sortOrder = 'desc';
                        break;
                    case 'created_date_oldest':
                        $sortField = 'created_at';
                        $sortOrder = 'asc';
                        break;
                    case 'name':
                        $sortField = 'name';
                        $sortOrder = 'asc';
                        break;
                    case 'value_high':
                        $sortField = 'company_job_summaries.job_total'; // Sort by job_total (higher)
                        $sortOrder = 'desc';
                        break;
                    case 'value_low':
                        $sortField = 'job_total'; // Sort by job_total (lower)
                        $sortOrder = 'asc';
                        break;
                    case 'time_in_stage_newest':
                        $sortField = 'company_jobs.updated_at'; // You can use `updated_at` or `created_at` to determine time in stage
                        $sortOrder = 'desc';
                        break;
                    case 'time_in_stage_oldest':
                        $sortField = 'company_jobs.updated_at'; // You can use `updated_at` or `created_at` to determine time in stage
                        $sortOrder = 'asc';
                        break;
                }
            }
            $specificStatuses = ['New Leads', 'Signed Deals', 'Estimate Prepared', 'Adjustor', 'Ready To Build', 'Build Scheduled', 'In Progress', 'Build Complete', 'Final Payment Due', 'Ready to Close', 'Won and Closed'];
    
            $tasks = Status::select('id', 'name')
                // ->whereIn('name', $specificStatuses)
                ->when(!empty($request->stages), function ($query) use ($request) {
                    $query->whereIn('name', $request->stages); 
                })
                ->withCount([
                    'tasks' => function ($query) use ($created_by, $request) {
                        $query->where('created_by', $created_by);
    
                        if ($request->job_type) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->whereIn('job_type', $request->job_type);
                            });
                        }
    
                        if ($request->location) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->where('market', $request->location);
                            });
                        }

                    }
                ])
                ->with([
                    'tasks' => function ($query) use ($created_by, $request, $sortField,$sortOrder) {
                        $query->with(['summary' => function ($q) {
                            $q->select('company_job_id', 'job_total', 'claim_number','job_type','job_total');
                        }, 'status'])
                        ->select('id', 'name', 'address', 'created_at', 'updated_at', 'status_id')
                        ->where('created_by', $created_by);
    
                        if ($request->job_type) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->whereIn('job_type', $request->job_type);
                            });
                        }
    
                        if ($request->location) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->where('market', $request->location);
                            });
                        }

                        // Handle sorting for summary->job_total
                        // if (in_array($sortField, ['job_total'])) {
                        //     $query->leftJoin('company_job_summaries', 'company_jobs.id', '=', 'company_job_summaries.company_job_id')
                        //         ->orderBy('company_job_summaries.job_total', $sortOrder);
                        // } else {
                        //     $query->orderBy($sortField, $sortOrder);
                        // }

                          // Handle sorting for job_total (from company_job_summaries)
                        if ($sortField === 'company_job_summaries.job_total') {
                            $query->orderBy('company_job_summaries.job_total', $sortOrder);
                        } else {
                            $query->orderBy($sortField, $sortOrder);
                        }
                    }
                ])
                ->get();
    
            // Transform tasks data to include 'job_total' and 'claim_number' in 'summary' and sum up 'job_total' for each status
            $tasks->each(function ($status) {
                $status->job_total = $status->tasks->sum(function ($job) {
                    return optional($job->summary)->job_total ?? 0;
                });
    
                $status->tasks->transform(function ($job) {
                    return [
                        'id' => $job->id,
                        'name' => $job->name,
                        'address' => json_decode($job->address, true)['formatedAddress'] ?? null,
                        'status_id' => $job->status_id,
                        'created_at' => $job->created_at,
                        'updated_at' => $job->updated_at,
                        // 'days_since_creation' => $job->created_at->diffInDays($job->updated_at),
                        'summary' => [
                            'company_job_id' => $job->summary->company_job_id ?? null,
                            'job_total' => $job->summary->job_total ?? 0,
                            'claim_number' => $job->summary->claim_number ?? null,
                            'job_type' => $job->summary->job_type ?? null,

                        ],
                        'status' => [
                            'id' => $job->status->id ?? null,
                            'name' => $job->status->name ?? null,
                            'created_at' => $job->status->created_at ?? null,
                            'updated_at' => $job->status->updated_at ?? null,
                        ]
                    ];
                });
            });
    
            return response()->json([
                'status' => 200,
                'message' => 'Data Fetched Successfully',
                'data' => $tasks
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }
    

    public function filterJobskanban(Request $request)
    {
        $request->validate([
            'job_type' => 'nullable|array',
            'job_type.*'=>'nullable|string',
            'location' => 'nullable|array',
            'location.*' => 'nullable|string',
            'stages' => 'nullable|array',
            'stages.*' => 'nullable|string',
            'sort_by' => 'nullable|string|in:last_updated_newest,last_updated_oldest,created_date_newest,created_date_oldest,address,value_high,value_low,time_in_stage_newest,time_in_stage_oldest',
            'time_period' => 'nullable|string|in:last_7_days,last_4_weeks,last_3_months,last_6_months,last_12_months,month_to_date,quarter_to_date,year_to_date',
            'lead_source' => 'nullable|array',
            'lead_source.*' => 'nullable|string|in:Door Knocking,Customer Referral,Call In,Facebook,Family Member,Home Advisor,Website,Social Encounter',
            'sales_representatives' => 'nullable|array',
            'sales_representatives.*' => 'nullable|integer|exists:users,id',
            'search_term' => 'nullable|string', // Add search

        ]);

        try {
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;

            $sortField = 'updated_at';
            $sortOrder = 'desc';

            // Determine sorting based on request
            if (!empty($request->sort_by)) {
                switch ($request->sort_by) {
                    case 'last_updated_newest':
                        $sortField = 'updated_at';
                        $sortOrder = 'desc';
                        break;
                    case 'last_updated_oldest':
                        $sortField = 'updated_at';
                        $sortOrder = 'asc';
                        break;
                    case 'created_date_newest':
                        $sortField = 'created_at';
                        $sortOrder = 'desc';
                        break;
                    case 'created_date_oldest':
                        $sortField = 'created_at';
                        $sortOrder = 'asc';
                        break;
                    case 'address':
                        $sortField = 'address';
                        $sortOrder = 'asc';
                        break;
                    case 'value_high':
                        $sortField = 'company_job_summaries.job_total'; // Sort by job_total (higher)
                        $sortOrder = 'desc';
                        break;
                    case 'value_low':   
                        $sortField = 'company_job_summaries.job_total'; // Sort by job_total (lower)
                        $sortOrder = 'asc';
                        break;
                    case 'time_in_stage_newest':
                        $sortField = 'company_jobs.updated_at'; // You can use `updated_at` or `created_at` to determine time in stage
                        $sortOrder = 'desc';
                        break;
                    case 'time_in_stage_oldest':
                        $sortField = 'company_jobs.updated_at'; // You can use `updated_at` or `created_at` to determine time in stage
                        $sortOrder = 'asc';
                        break;
                }
            }

            $specificStatuses =
            ['New Leads',
            'Inspection',
            'Signed Deal',
            // 'Estimate Prepared',
            'Adjuster Scheduled',
            'Approved',
            'Denied',
            'Partial',
       
            'Ready To Build',
            'Build Scheduled',
            'In Progress',
            'Build Complete',
            'COC Required',
            'Final Payment Due',
            'Won and Closed',];

                // dd($specificStatuses);
            $tasks = Status::select('id', 'name')
                ->whereIn('name', $specificStatuses)
                ->when(!empty($request->stages), function ($query) use ($request) {
                    $query->whereIn('name', $request->stages);
                })
                ->withCount([
                    'tasks' => function ($query) use ($created_by, $request) {
                        $query->where('created_by', $created_by);

                        if ($request->job_type) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->whereIn('job_type', $request->job_type);
                            });
                        }

                        if ($request->location) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->whereIn('market', $request->location);
                            });
                        }

                        if ($request->lead_source) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->whereIn('lead_source', $request->lead_source);
                            });
                        }

                         // Filter by sales representative
                         if (!empty($request->sales_representatives)) {
                            $query->whereHas('companyJobUsers', function ($q) use ($request) {
                                $q->whereIn('user_id', $request->sales_representatives);
                            });
                        }
                        

                        // Add search filter
                        if ($request->search_term) {
                            $query->where('name', 'like', '%' . $request->search_term . '%');
                        }

                    }
                ])
                ->with([
                    'tasks' => function ($query) use ($created_by, $request, $sortField, $sortOrder) {
                        $query->with(['summary' => function ($q) {
                            $q->select('company_job_id', 'job_total', 'claim_number','job_type','lead_source');
                        }, 'status'])
                        ->select('company_jobs.id', 'company_jobs.name', 'company_jobs.address', 'company_jobs.created_at', 'company_jobs.updated_at', 'company_jobs.status_id','company_jobs.user_id')
                        ->leftJoin('company_job_summaries', 'company_jobs.id', '=', 'company_job_summaries.company_job_id')
                        ->where('created_by', $created_by);

                        // Add search filter
                        if ($request->search_term) {
                            $query->where('name', 'like', '%' . $request->search_term . '%');
                        }
                        
                        //filter for job type
                        if ($request->job_type) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->whereIn('job_type', $request->job_type);
                            });
                        }

                        //filter fir location
                        if ($request->location) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->whereIn('market', $request->location);
                            });
                        }

                        // Handle sorting for job_total (from company_job_summaries) using the summary relation
                        if ($sortField === 'company_job_summaries.job_total') {
                            $query->orderBy('company_job_summaries.job_total', $sortOrder);
                        } elseif($sortField==='address'){
                            $query->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(company_jobs.address, '$.formatedAddress')) " . strtoupper($sortOrder));
                        }else {
                            $query->orderBy($sortField, $sortOrder);
                        }

                        //here apply filter for time period on Updated Date
                        if ($request->has('time_period')) {
                            $now = now();
                            switch ($request->time_period) {
                                case 'last_7_days':
                                    $startDate = $now->copy()->subDays(7);
                                    $endDate = $now;
                                    Log::info(['start_date' => $startDate->toDateTimeString(), 'end_date' => $endDate->toDateTimeString()]);
                                    $query->whereBetween('company_jobs.updated_at', [$startDate, $endDate]);
                                    // Log::info(["jobs" => $jobs->toArray()]);
                                    break;
                                case 'last_4_weeks':
                                    $startDate = $now->copy()->subWeeks(4);
                                    $endDate = $now;
                                    $query->whereBetween('company_jobs.updated_at', [$startDate, $endDate]);
                                    break;
                                case 'last_3_months':
                                    $startDate = $now->copy()->subMonths(3);
                                    $endDate = $now;
                                    $query->whereBetween('company_jobs.updated_at', [$startDate, $endDate]);
                                    break;
                                case 'last_6_months':
                                    $startDate = $now->copy()->subMonths(6);
                                    $endDate =now();
                                    $query->whereBetween('company_jobs.updated_at', [$startDate, $endDate]);
                                    break;
                                case 'last_12_months':
                                    $startDate = $now->copy()->subMonths(6);
                                    $endDate =now();
                                    $query->whereBetween('company_jobs.updated_at', [$startDate, $endDate]);
                                    break;
                                case 'month_to_date':
                                    $query->whereMonth('company_jobs.updated_at', $now->month)
                                          ->whereYear('company_jobs.updated_at', $now->year);
                                    break;
                                case 'quarter_to_date':
                                    $startDate = $now->copy()->startOfQuarter();
                                    $endDate =now();
                                    Log::info(['start_date' => $startDate->toDateTimeString(), 'end_date' => $endDate->toDateTimeString()]);
                                    $jobs = $query->whereBetween('company_jobs.updated_at', [$startDate, $endDate])->get();
                                    Log::info(["jobs" => $jobs->toArray()]);

                                    break;
                                case 'year_to_date':
                                    $query->whereYear('company_jobs.updated_at', $now->year);
                                    break;
                            }
                        }

                          //filter for lead source
                        if ($request->lead_source) {
                            $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                                $q->whereIn('lead_source', $request->lead_source);
                            });
                        }

                         // Filter by sales representative
                         if (!empty($request->sales_representatives)) {
                            $query->whereHas('companyJobUsers', function ($q) use ($request) {
                                $q->whereIn('user_id', $request->sales_representatives);
                            });
                        }
                        

                    }
                ])
                ->get();

            // Transform tasks data to include 'job_total' and 'claim_number' in 'summary' and sum up 'job_total' for each status
            $tasks->each(function ($status) {
                $status->job_total = $status->tasks->sum(function ($job) {
                    return optional($job->summary)->job_total ?? 0;
                });

                $status->tasks->transform(function ($job) {
                    $completed_percentage = 0; // Default value

                        if ($job->status_id == 1) {  
                            $completed_percentage = 10;
                        } elseif ($job->status_id == 2) {  
                            $completed_percentage = 20;
                        } elseif ($job->status_id == 4) {  
                            $completed_percentage = 30;
                        } elseif ($job->status_id == 5) {  
                            $completed_percentage = 40;
                        } elseif ($job->status_id == 6 || $job->status_id == 8) {  
                            $completed_percentage = 50;
                        } elseif ($job->status_id == 11) {  
                            $completed_percentage = 60;
                        } elseif ($job->status_id == 13) {  
                            $completed_percentage = 70;
                        } elseif ($job->status_id == 14) {  
                            $completed_percentage = 80;
                        } elseif ($job->status_id == 15) {  
                            $completed_percentage = 90;
                        } elseif ($job->status_id == 20) {  
                            $completed_percentage = 100;
                        } 
                    return [
                        'id' => $job->id,
                        'name' => $job->name,
                        'address' => json_decode($job->address, true)['formatedAddress'] ?? null,
                        'status_id' => $job->status_id,
                        'user_id' => $job->user_id,
                        'completed_percentage' => $completed_percentage,
                        'created_at' => $job->created_at,
                        'updated_at' => $job->updated_at,
                        'summary' => [
                            'company_job_id' => $job->summary->company_job_id ?? null,
                            'job_total' => $job->summary->job_total ?? 0,
                            'claim_number' => $job->summary->claim_number ?? null,
                            'job_type' => $job->summary->job_type ?? null,
                            'lead_source' => $job->summary->lead_source ?? null,

                        ],
                        'status' => [
                            'id' => $job->status->id ?? null,
                            'name' => $job->status->name ?? null,
                            'created_at' => $job->status->created_at ?? null,
                            'updated_at' => $job->status->updated_at ?? null,
                        ]
                    ];
                });
            });

            return response()->json([
                'status' => 200,
                'message' => 'Data Fetched Successfully',
                'data' => $tasks
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function getJobsFilterSection(Request $request)
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;
            // dd($companyId);

            $statuses = Status::select('id','name')->whereIn('name',['New Leads','Customer Agreement','Adjuster Scheduled','Ins Under Review', 'Overturn','Appraisal','Approved','Ready To Build','Build Scheduled', 'In Progress','Build Complete','COC Required','Final Payment Due','Ready to Close','Won and Closed','Lost','Unqualified'])
                 ->get();
            if (in_array($user->role_id, [1, 2,5,8,9])) {
                // Role 1 or 2: Fetch users with the same company_id as the logged-in user
                $representatives = User::
                    // whereHas('companyJobUsers')
                    where('created_by', $companyId)
                    ->select('id', 'name', 'first_name', 'last_name', 'email','company_id', 'role_id', 'phone','created_by', 'created_at', 'updated_at')
                    ->get();
                $location = Location::whereIn('created_by',[$companyId,0])->get();

            } else {
                $representatives = User::whereHas('companyJobUsers')
                    ->select('id', 'name', 'first_name', 'last_name', 'email', 'role_id', 'phone', 'created_by','created_at', 'updated_at')
                    ->get();
                $location = Location::whereIn('created_by',[0])->get();

            }

            return response()->json([
                'status' => 200,
                'message' => 'Filters Retrieved Successfully',
                'data' =>[ 
                    'assignees'=>$representatives,
                     'locations'=>$location,
                     'stages' => $statuses
                    ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage() . ' on line ' . $e->getLine(),
            ], 500);
        }
    }


    public function getCurrentJobStage($jobId)
    {
        $companyJob = CompanyJob::with('status:id,name')->where('id',$jobId)->first();
        if($companyJob)
        {
            $response = $companyJob->toArray();
            $response['activeStatus'] = $response['status']; // Rename 
            unset($response['status']); // Remove the original key


            return response()->json([
                'message'=> 'Status Fetched Successfully',
                'status' =>200,
                'data' => $response
            ]);
        }
        return response()->json(['status' => 404, 'message' => 'Job Not Found'], 404);

    }

    public function getCustomerSummary($jobId)
    {
        $job= CompanyJob::with('summary:id,company_job_id,job_total,insurance,policy_number,insurance_representative,claim_number')->where('id',$jobId)->first();
        return response(["data"=>$job]);
    }

    public function FilterJobWithStatus(Request $request, $statusId) //notused
    {
        $request->validate([
            'job_type' => 'nullable|string',
            'location' => 'nullable|string',
        ]);
        try {
            // Check Status
            $task = Status::find($statusId);
            if (!$task) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Task Not Found'
                ], 422);
            }

            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;

            // Define the base query for jobs
            if ($user->role_id == 1 || $user->role_id == 2) {
                $jobsQuery = CompanyJob::where('created_by', $created_by)
                    ->where('status_id', $statusId);
            } else {
                $jobsQuery = CompanyJob::whereIn('id', $assigned_jobs)
                    ->where('status_id', $statusId);
            }

            // Apply job_type and location filters if provided
            if ($request->job_type) {
                $jobsQuery->whereHas('summary', function ($query) use ($request) {
                    $query->where('job_type', $request->job_type);
                });
            }
            if ($request->location) {
                $jobsQuery->whereHas('summary', function ($query) use ($request) {
                    $query->where('market', $request->location);
                });
            }

            // Fetch and map jobs with the filtered query
            $jobs = $jobsQuery
                ->with('summary:company_job_id,balance,job_type,market') // Load only necessary fields from the summary
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($job) {
                    $job->amount = $job->summary->balance ?? 0;
                    return $job;
                });

            $task->jobs = $jobs;

            return response()->json([
                'status' => 200,
                'message' => 'Jobs Found Successfully',
                'data' => $task
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }


    public function claimDetails($jobId, ClaimDetailRequest $request)
    {
        try{
            // dd($jobId);
            $job = CompanyJob::where('id',$jobId)->first();
            if(!$job)
            {
                return response()->json([
                    'status'=>200,
                    'message'=> 'Company not Found',
                    'data'=>[]
                ]);
            }
            //  $request->validated();
            // $ClaimDetail = ClaimDetail::where('company_job_id',$jobId)->first();

            // if($ClaimDetail){
            //     $ClaimDetail->claim_number = $request->claim_number;
            //     $ClaimDetail->status = $request->status;
            //     $ClaimDetail->supplement_amount = $request->supplement_amount;
            //     $ClaimDetail->notes = $request->notes;
            //     $ClaimDetail->last_update_date = $request->last_update_date;
            //     $ClaimDetail->save();
            //     $message = "Claim Details Updated";
            // }else{
                $ClaimDetail = new ClaimDetail();
                $ClaimDetail->insurance_company = $request->insurance_company;
                $ClaimDetail->desk_adjustor =$request->desk_adjustor;
                $ClaimDetail->email = $request->email;
                $ClaimDetail->company_job_id = $jobId;
                $ClaimDetail->claim_number = $request->claim_number;
                $ClaimDetail->status = $request->status;
                $ClaimDetail->supplement_amount = $request->supplement_amount;
                $ClaimDetail->notes = $request->notes ?? "";
                $ClaimDetail->last_update_date = $request->last_update_date;
                $ClaimDetail->save();
                $message = "Claim Details Created";

            // }

            return response()->json([
                'status_code' =>200,
                'message' => $message,
                'data' => $ClaimDetail
            ]);
    
        }catch(\Exception $e){
            return response()->json([
                'status_code' =>500,
                'message' => 'Issue Occured While Claim Details Added',
                'error' => $e->getMessage(),
            ]);
        }
       
    }

    public function getclaimDetails($jobId)
    {
        $ClaimDetail = ClaimDetail::where('company_job_id',$jobId)->get();
        if($ClaimDetail)
        {
            return response()->json([
                'status_code' =>200,
                'message' => 'Claim Details Fetched Successfully',
                'data' => $ClaimDetail
            ]);
        }
        return response()->json([
            'status_code' =>404,
            'message' => 'Claim Details Not Found',
            'data' => []
        ]);

        
    }


    public function claimDetailsDocuments200($jobId,Request $request)
    {
        // dd($jobId);
        $companyJob = CompanyJob::find($jobId);
        if(!$companyJob)
        {
            return response()->json([
                'status_code' =>404,
                'message' => 'Company Job Not Found',
                'data' => []
            ]);
        }

        $request->validate([
            'document' => 'nullable|array', 
            'document.*' => 'nullable|file', 
            'file_name' => 'nullable|array',         
            'file_name.*' => 'nullable|string',      
        ]);



        $existingPhotos = ClaimDetailMedia::where('company_job_id', $jobId)->get();
        // dd($existingPhotos);
        foreach ($existingPhotos as $photo) {
            // Delete file from storage
            $filePath = str_replace('/storage/', 'public/', $photo->document); // Convert storage path to public disk path
            Storage::delete($filePath);
            $photo->delete(); // Delete the record from the database
        }

        $savedPhotos = []; // To store successfully saved photos
        $squarePhotos = $request->document ?? [];
        foreach ($squarePhotos as $index => $document) {
            $document_fileName = time() . '_' . $document->getClientOriginalName();
            $document_filePath = $document->storeAs('ClaimDetailsDocument', $document_fileName, 'public');

            // Save new photo in database
            $media = new ClaimDetailMedia();
            $media->company_job_id = $jobId;
            $media->file_name = $request->file_name[$index] ?? null;
            $media->pdf_path = Storage::url($document_filePath);
            $media->save();

               // Collect saved photo details
               $savedPhotos[] = [
                'id' => $media->id,
                'company_job_id' => $media->company_job_id,
                'file_name' => $media->file_name,
                'pdf_path' => $media->pdf_path,
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];

        }

        return response()->json([
            'status' => 200,
            'message' => 'Document Updated Successfully',
            'data' => $savedPhotos,
        ]);


    }

    public function claimDetailsDocuments($jobId, Request $request)
    {
        $companyJob = CompanyJob::find($jobId);
        if (!$companyJob) {
            return response()->json([
                'status_code' => 404,
                'message' => 'Company Job Not Found',
                'data' => []
            ]);
        }

        $request->validate([
            'document' => 'nullable|array',
            'document.*' => 'nullable|file',
            'file_name' => 'nullable|array',
            'file_name.*' => 'nullable|string',
        ]);

        $savedPhotos = [];
        $squarePhotos = $request->document ?? [];

        foreach ($squarePhotos as $index => $document) {
            $document_fileName = time() . '_' . $document->getClientOriginalName();
            $document_filePath = $document->storeAs('ClaimDetailsDocument', $document_fileName, 'public');

            $media = new ClaimDetailMedia();
            $media->company_job_id = $jobId;
            $media->file_name = $request->file_name[$index] ?? null;
            $media->pdf_path = Storage::url($document_filePath);
            $media->save();

            $savedPhotos[] = [
                'id' => $media->id,
                'company_job_id' => $media->company_job_id,
                'file_name' => $media->file_name,
                'pdf_path' => $media->pdf_path,
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];
        }

        return response()->json([
            'status' => 200,
            'message' => 'Document(s) Added Successfully',
            'data' => $savedPhotos,
        ]);
    }


    public function getClaimDetailsDocuments($jobId)
    {
        $ClaimDetail = CompanyJob::find($jobId);
        if(!$ClaimDetail)
        {
            return response()->json([
                'status_code' =>404,
                'message' => 'Company Job Not Found',
                'data' => []
            ]);
        }

        $document = ClaimDetailMedia::where('company_job_id',$jobId)->get();
        return response()->json([
            'status' => 200,
            'message' => 'Document Fetched Successfully',
            'data' => $document,
        ]);
    }

    public function summaryMetrics()
    {
        $user = Auth::user();
        $created_by = $user->created_by;
        if($user->role_id == 7)
        {
            $jobs = CompanyJob::get();
        }else{
            $jobs = CompanyJob::where('created_by',$created_by)->get();

        }
        //jobs needing attenstion
        $current_date = now();
        $threash_hold = 17;
        $response =$jobs->map(function($job)use($current_date){
            $job_date = \Carbon\Carbon::parse($job->date);
            $days_difference = $current_date->diffInDays($job_date);
    
            return [
                'id' => $job->id,
                'status_id' => $job->status_id,
                'user_id' =>$job->user_id,
                'created_by' =>$job->created_by ,
                'user_id' =>$job->user_id,
                'name' =>$job->name,
                'address' =>json_decode($job->address),
                'email' =>$job->email,
                'phone' =>$job->phone,
                'date' =>$job->date,
                'days_from_now' => $days_difference,
                'created_at' =>$job->created_at,
                'updated_at' =>$job->updated_at,

            ];

        });

        $jobs_needing_attention = $response->filter(function($job)use($threash_hold){
            return $job['days_from_now'] >$threash_hold;
        });

        $total_job_needing_attention = $jobs_needing_attention->count();

        $response = $response->toArray();
        
        return response()->json([
            'status' => 200,
            'total_job_needing_attention' => $total_job_needing_attention,
            'total_jobs' => $response,
            
        ]);
    }

    public function summaryFilter(Request $request)
    {
        $request->validate([
            'location'=>'nullable|string',
            'job_status' => 'nullable|string|in:total_jobs,completed_jobs,in_progress'
        ]);

        try{
            $user = Auth::user();
            $created_by = $user->company_id;

            $jobs = CompanyJob::with('companyJobSummaries')
                ->when($user->role_id !== 7, function ($query) use ($created_by) {
                       // Apply the created_by filter only if the user's role_id is not 7
                    $query->where('created_by', $created_by);
                })
                ->when($request->has('location'), function ($query) use ($request) {
                    $query->whereHas('companyJobSummaries', function ($q) use ($request) {
                        $q->where('market', $request->location);
                    });
                })
                ->when($request->has('job_status'),function ($query) use ($request) {
                    //apply filter
                    switch($request->job_status){
                        case 'total_jobs':
                        break;
                        case 'completed_jobs':
                        $query->where('status_id',15);
                        break;
                        case 'in_progress':
                        $query->where('status_id', '<>', 15);
                        break;
                    }
                })
                ->get();

                // Add the "need_attention" field dynamically
                $jobs->each(function ($job) {
                    $dateField = $job->date; 
                    $job->attention_status = Carbon::parse($dateField)->diffInDays(Carbon::now()) > 17 ? 'required' : 'ok';
                });

                    // if need to apply the attention on the base of level uncomment this code and comment above
                    // $jobs->each(function ($job) {
                    //     $dateField = $job->date; 
                    //     $daysDifference = Carbon::parse($dateField)->diffInDays(Carbon::now());
    
                    //     if ($daysDifference <= 6) {
                    //         $job->attention_status = 'yellow';
                    //     } elseif ($daysDifference > 6 && $daysDifference < 17) {
                    //         $job->attention_status = 'normal';
                    //     } else {
                    //         $job->attention_status = 'urgenet attention';
                    //     }
                    // });

            return response()->json([
                'status' => 200,
                'message' => 'Jobs Filtered and Feteched Successfully',
                'data' => $jobs
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => 500,
                'error' => $e->getMessage(),
                'message' => 'Issue occured',
                'data' => []
            ]);
        }
       
    }

    public function progressLineold($jobId) //used for both grid and kanban
    {
        $tables = [
            'customer_agreements',
            'estimate_prepareds',
            'adjustor_meetings',
            'ready_to_builds',
            'build_details',
            'inprogresses',
            'cocs',
            'final_payment_dues',
            'ready_to_closes',
        ];

        $stages = [];
        $completedSteps = 0; 
        $totalSteps = count($tables) + 1; 

        $customerAgreement = DB::table('customer_agreements')
        ->where('company_job_id',$jobId)
        ->where('current_stage','yes')
        ->exists(); // Check if any customer agreement exists
        $newLeadsStatus = $customerAgreement ? 'yes' : 'no';

        $stages[] = [
            'step' => 1, 
            'table' => 'new_leads', 
            'current_stage' => $newLeadsStatus, 
        ];

        if ($newLeadsStatus === 'yes') {
            $completedSteps++; // Increment completed steps
        }

        foreach ($tables as $index => $table) {
            $currentStage = DB::table($table)
            ->where('company_job_id',$jobId)
                ->select('current_stage')
                ->first();

            $currentStageValue = $currentStage->current_stage ?? 'no';

            $stages[] = [
                'step' => $index + 2, 
                'table' => $table,
                'current_stage' => $currentStageValue, 
            ];
            if ($currentStageValue === 'yes') {
                $completedSteps++; 
            }
        }

        $completedPercentage = ($completedSteps / $totalSteps) * 100;
        $remainingPercentage = 100 - $completedPercentage;

        return response()->json([
            'status' => 200,
            'completed' => round($completedPercentage, 2),
            'remaining' => round($remainingPercentage, 2),
            'stages' => $stages, 
            
        ]);
    }

    public function progressLine($jobId) 
    {
        $statusCompletionMap = [
            1 => 10,  // New Leads
            2 => 20,  // Customer Agreement
            4 => 30,  // Adjustor Meeting
            9 => 40,  // Ready to Build
            10 => 50,  // Build Schedule
            11 => 60,  // In Progress
            13 => 70,  // COC
            14 => 80, // Final Payment Due
            15 => 90, //ready to close
            20 => 100 //completed
        ];

        // Get the job's current status_id
        $job = DB::table('company_jobs')
            ->where('id', $jobId)
            ->select('status_id')
            ->first();

        if (!$job) {
            return response()->json([
                'status' => 404,
                'message' => 'Job not found',
            ], 404);
        }

        $currentStatusId = $job->status_id;

        // Calculate completion and remaining percentages
        $completedPercentage = $statusCompletionMap[$currentStatusId] ?? 0;
        $remainingPercentage = 100 - $completedPercentage;

        // Prepare stages with their completion status
        $stages = [];
        foreach ($statusCompletionMap as $statusId => $percentage) {
            $stages[] = [
                'status_id' => $statusId,
                'step' => $statusId,
                'completion_percentage' => $percentage,
                'current_stage' => $statusId === $currentStatusId ? 'yes' : 'no',
            ];
        }

        return response()->json([
            'status' => 200,
            'completed' => round($completedPercentage, 2),
            'remaining' => round($remainingPercentage, 2),
            'stages' => $stages,
        ]);
    }


    public function progressLineNotused($jobId) //currently not used
    {
        $tables = [
            'customer_agreements',
            'estimate_prepareds',
            'adjustor_meetings',
            'ready_to_builds',
            'build_details',
            'inprogresses',
            'cocs',
            'final_payment_dues',
            'ready_to_closes',
        ];

        $stages = [];
        $customerAgreement = DB::table('customer_agreements')
        ->where('company_job_id',$jobId)
        ->where('current_stage','yes')
        ->exists(); // Check if any customer agreement exists
        $stages[] = [
            'step' => 1, 
            'table' => 'new_leads', 
            'current_stage' => $customerAgreement ? 'yes' : 'no', 
        ];

        foreach ($tables as $index => $table) {
            $currentStage = DB::table($table)
            ->where('company_job_id',$jobId)
                ->select('current_stage')
                ->first();

            $stages[] = [
                'step' => $index + 2, // Adjust step number to account for 'new_leads'
                'table' => $table, // Table name for reference
                'current_stage' => $currentStage->current_stage ?? 'no', // Default to 'no' if null
            ];
        }

        return response()->json([
            'status' => 200,
            'stages' => $stages, 
        ]);
    }

    public function notesAdd($jobId, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        try{
            $notes = new CompanyNotes();
            $notes->company_job_id = $jobId;
            $notes->notes = $request->notes;
            $notes->save();

            return response()->json([
                'status' =>200,
                'message' => 'notes added successfully',
                'data' => $notes
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' =>200,
                'message' => 'issue occure while adding notes',
                'data' => []
            ]);
        }

    }

    public function getNotes($jobId)
    {

            $notes = CompanyNotes::where('company_job_id',$jobId)->get();
            if($notes)
            {
                return response()->json([
                    'status' =>200,
                    'message' => 'notes fetched successfully',
                    'data' => $notes
                ]);
            }
            return response()->json([
                'status' =>200,
                'message' => 'notes not exist',
                'data' => []
            ]);
    }

    public function claimInformationSummary($jobId, Request $request)
    {
        $request->validate([
            'claim_number' => 'nullable|string',
            'date_of_loss' => 'nullable|date',
            'insurance_provider' => 'nullable|string',
            'adjustor_name' => 'nullable|string',
            'contact_information' => 'nullable|string',
            'status' => 'nullable|in:approved,review,denied'
        ]);

        try{
            $claim = new ClaimInformation();
            $claim->company_job_id = $jobId;
            $claim->claim_number = $request->claim_number;
            $claim->date_of_loss = $request->date_of_loss;
            $claim->insurance_provider = $request->insurance_provider;
            $claim->adjustor_name = $request->adjustor_name;
            $claim->contact_information = $request->contact_information;
            $claim->status = $request->status;
            $claim->save();

            return response()->json([
                'status' =>200,
                'message' => 'saved successfully',
                'data' => $claim
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'Issue Occcured',
                'data' => []
            ]);
        }
    }

    public function getClaimInformationSummary($jobId)
    {
        $claim_summary = ClaimInformation::where('company_job_id',$jobId)->first();
        if($claim_summary)
        {
            return response()->json([
                'status' => 200,
                'message' => 'claim summary fetched successfully',
                'data' => $claim_summary
            ]);
        }
        return response()->json([
            'status' => 401,
            'message' => 'Not found',
            'data' => []

        ]);
    }

}
