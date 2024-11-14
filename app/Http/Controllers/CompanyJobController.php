<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\CompanyJobContent;
use App\Models\CompanyJobSummary;
use App\Models\CustomerAgreement;
use App\Models\ProjectDesignPage;
use Illuminate\Support\Collection;
use App\Events\JobStatusUpdateEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyJobContentMedia;
use App\Models\ProjectDesignPageStatus;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanyJobUser;
use Carbon\Carbon;

class CompanyJobController extends Controller
{
    public function createJob(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required'
        ]);

        try {

            $user = Auth::user();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by ;

            //Create Job
            $job = new CompanyJob;
            $job->status_id = 1;
            $job->user_id = Auth::id();
            $job->created_by = $created_by;
            $job->name = $request->name;
            $job->address = $request->address;
            $job->latitude = $request->latitude;
            $job->longitude = $request->longitude;
            $job->email = $request->email;
            $job->phone = $request->phone;
            $job->save();

            //here I will save the address but this will save in CustomerAgreement table here we will save the adress that get from google map api
            // Get address details from Google Maps API response
            $googleAddress = $request->address;
            $parsedAddress = $this->parseAddress($googleAddress);
            $address = new CustomerAgreement();
            $address->company_job_id = $job->id;
            $address->street = $parsedAddress['street'];
            $address->city = $parsedAddress['city'];
            $address->state = $parsedAddress['state'];
            $address->zip_code = $parsedAddress['zip_code'];
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
            // $job->users()->attach($user->id);
            //  i have comment this line because it auto add the value in company_job_user table and it will show the user in ready to close sales represntative

            return response()->json([
                'status' => 201,
                'message' => 'Job Created Successfully',
                'job' => $job
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    protected function parseAddress($googleAddress)
    {
        return [
            'street' => $googleAddress->route ?? '',
            // 'street' => $googleAddress->street_address ?? '',
            'city' => $googleAddress->locality ?? '',
            'state' => $googleAddress->administrative_area_level_1 ?? '',
            'zip_code' => $googleAddress->postal_code ?? '',
        ];
    }


    public function getAllJobs()
    {
        try {
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            
            $created_by = $user->created_by == 0 ? 1 : $user->created_by ;
            
            if($user->role_id == 1 || $user->role_id == 2) {
                $jobs = CompanyJob::select('id','name','address','status_id')->where('created_by', $created_by)
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get();
            } else {
                $jobs = CompanyJob::select('id','name','address','status_id')->where(function($query) use ($user,$assigned_jobs) {
                    $query->orWhere('user_id', $user->id);
                    $query->orWhereIn('id', $assigned_jobs);
                })
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get();   
            }

            // Group jobs by status name
            $groupedJobs = $jobs->groupBy(function ($job) {
                return $job->status->name;
            });

            $statuses = Status::whereIn('name', [
                'New Leads', 
                'Signed Deals', 
                'Estimate Prepared', 
                'Adjustor',
                'Ready To Build',
                'Build Scheduled',
                'In Progress',
                'Build Complete',
                'Final Payment Due',
                'Ready to Close',
                'Won and Closed'
            ])->get(); 
            
            // Structure the response
            $response = $statuses->map(function ($status) use ($groupedJobs) {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'tasks' => $groupedJobs->get($status->name, new Collection()),
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Jobs Found Successfully',
                'data' => $response
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
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
                // 'invoice_number' => $request->invoice_number,
                // 'market' => $request->market,
                // 'lead_source' => $request->lead_source,
                // 'insurance' => $request->insurance,
                // 'policy_number' => $request->policy_number,
                // 'email' => $request->email,
                // 'insurance_representative' => $request->insurance_representative
            ]);

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
                    'job' => $object
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
            'market' => 'nullable|in:Nashville,Chattanooga,Knoxville',
            'job_type' => 'nullable|in:Retail,Insurance',
            'lead_source' => 'nullable|in:Door Knocking,Customer Referral,Call In,Facebook,Family Member,Home Advisor,Website,Social Encounter',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id'
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
                'invoice_number' => $request->invoice_number,
                'market' => $request->market,
                'lead_source' => $request->lead_source,
                'job_type' => $request->job_type,

            ]);
            
            // Assign Job To Users
            if(isset($request->user_ids) && count($request->user_ids) > 0) {
                $job->users()->sync($request->user_ids);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Updated Successfully',
                'job' => $job_summary
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

            $job_summary = CompanyJobSummary::select('id','invoice_number','market','lead_source','job_type')
            ->where('company_job_id', $job->id)->first();
            if(!$job_summary) {
                
                //Create Object
                $object = new \StdClass;
                $object->address = $job->address;
                $object->email = $job->email;
                $object->phone = $job->phone;
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Job Summary Not Yet Created',
                    'job' => $object
                ], 200);
            }
            
            $job_summary->user_ids = $job->users()->pluck('user_id')->toArray();
            $job_summary->address = $job->address;
            $job_summary->email = $job->email;
            $job_summary->phone = $job->phone;

            return response()->json([
                'status' => 200,
                'message' => 'Job Summary Found Successfully',
                'job' => $job_summary
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
    
    public function getTaskWithJobCount()
    {
        try {
            
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            // dd($assigned_jobs);
            $created_by = $user->created_by == 0 ? 1 : $user->created_by; 
            // dd($created_by);
            if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 7) {
                $specificStatuses = ['New Leads', 'Signed Deals', 'Estimate Prepared', 'Adjustor','Ready To Build','Build Scheduled','In Progress','Build Complete','Final Payment Due','Ready to Close','Won and Closed'];

                $tasks = Status::whereIn('name', $specificStatuses)
                ->withCount([
                    'tasks as jobs_count' => function ($query) use ($created_by) {
                        $query->where('created_by', $created_by);
                    }
                ])->get();

            } else {
                $specificStatuses = ['New Leads', 'Signed Deals', 'Estimate Prepared', 'Adjustor','Ready To Build','Build Scheduled','In Progress','Build Complete','Final Payment Due','Ready to Close','Won and Closed'];
                $tasks = Status::whereIn('name',$specificStatuses)
                ->withCount([
                    'tasks as jobs_count' => function ($query) use ($user,$assigned_jobs) {
                        $query->where('user_id', $user->id);
                        $query->orWhereIn('id', $assigned_jobs);
                    }
                ])->get();
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


    
    public function getJobWithStatus($statusId)
    {
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
            
            if($user->role_id == 1 || $user->role_id == 2) {
                $jobs = CompanyJob::where('created_by', $created_by)
                ->where('status_id', $statusId)
                ->with('summary:company_job_id,balance') // Load only necessary fields from the summary
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($job) {
                    // Assign amount based on the summary balance or set it to 0 if summary is null
                    $job->amount = $job->summary->balance ?? 0;
                    return $job;
                });
                
                
            } else {
                $jobs = CompanyJob::whereIn('id', $assigned_jobs)->where('status_id', $statusId)
                ->with('summary:company_job_id,balance') // Load only necessary fields from the summary
                ->orderBy('status_id', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($job) {
                    // Assign amount based on the summary balance or set it to 0 if summary is null
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

            $specificStatuses = ['New Leads', 'Signed Deals', 'Estimate Prepared', 'Adjustor', 'Ready To Build', 'Build Scheduled', 'In Progress', 'Build Complete', 'Final Payment Due', 'Ready to Close','Won and Closed'];

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
                                $query->whereHas('summaries', function ($q) use ($request) {
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

    public function filterJobskanban(Request $request) // for kanban
    {
        $request->validate([
            'job_type' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $assigned_jobs = \App\Models\CompanyJobUser::where('user_id', $user->id)->pluck('company_job_id')->toArray();
            $created_by = $user->created_by == 0 ? 1 : $user->created_by;

            $specificStatuses = ['New Leads', 'Signed Deals', 'Estimate Prepared', 'Adjustor', 'Ready To Build', 'Build Scheduled', 'In Progress', 'Build Complete', 'Final Payment Due', 'Ready to Close','Won and Closed'];

            // Get statuses and related tasks
            $tasks = Status::select('id', 'name')
                ->whereIn('name', $specificStatuses)
                ->withCount([
                    'tasks' => function ($query) use ($created_by, $request) {
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
                ])
                ->with([
                    'tasks' => function ($query) use ($created_by, $request) {
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
                ])
                ->get();

            // Modify the tasks data to include detailed information
            $tasks->each(function ($task) {
                $task->tasks->transform(function ($job) {
                    // Add 'status' and 'address' to the job
                    return [
                        'id' => $job->id,
                        'name' => $job->name,
                        'address' => $job->address,
                        'status_id' => $job->status_id,
                        'status' => $job->status,
                    ];
                });
            });

            return response()->json([
                'status' => 200,
                'message' => 'Data Fetched Successfully',
                'data' => $tasks
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
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


    public function filterJobsdone(Request $request) //not used
    {
        $request->validate([
            'location' => 'nullable|string',
            'job_type' => 'nullable|string',
        ]);

        $jobs = CompanyJob::with(['companyJobSummaries' => function ($query) use ($request) {
            if ($request->location) {
                $query->where('market', $request->location);
            }
            if ($request->job_type) {
                $query->where('job_type', $request->job_type);
            }
        }])->whereHas('companyJobSummaries', function ($query) use ($request) {
            if ($request->location) {
                $query->where('market', $request->location);
            }
            if ($request->job_type) {
                $query->where('job_type', $request->job_type);
            }
        })->get();

        return response()->json([
            'message' => 'Data Fetched Successfully',
            'status_code' => 200,
            'status' => true,
            'data' => $jobs,
        ]);
    }

    public function filterJobByLocation(Request $request) //not used
    {
        $request->validate([
            'location' => 'nullable|string',
        ]);

        $jobs = CompanyJob::with(['companyJobSummaries' => function ($query) use ($request) {
            if ($request->location) {
                $query->where('market', $request->location);
            }
        }])->whereHas('companyJobSummaries', function ($query) use ($request) {
            if ($request->location) {
                $query->where('market', $request->location);
            }
        })->get();

        return response()->json([
            'message' => 'Data Fetched Successfully',
            'status_code' => 200,
            'status' => true,
            'data' => $jobs,
        ]);
    }

    public function filterJobsByJobType(Request $request)//not used
    {
        $request->validate([
            'job_type' => 'nullable|string',
        ]);

        $jobs = CompanyJob::with(['companyJobSummaries' => function ($query) use ($request) {
            if ($request->job_type) {
                $query->where('job_type', $request->job_type);
            }
        }])->whereHas('companyJobSummaries', function ($query) use ($request) {
            if ($request->job_type) {
                $query->where('job_type', $request->job_type);
            }
        })->get();

        return response()->json([
            'message' => 'Data Fetched Successfully',
            'status_code' => 200,
            'status' => true,
            'data' => $jobs,
        ]);
    }



   


   


}
