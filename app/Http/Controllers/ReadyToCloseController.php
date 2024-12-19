<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\CompanyJob;
use App\Models\ReadyToClose;
use Illuminate\Http\Request;
use App\Models\ReadyToCloseMedia;
use App\Models\OverheadPercentage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;
class ReadyToCloseController extends Controller
{
    public function updateReadyToClose(Request $request, $jobId)
    {
        //Validate Request
        $request->validate([
            'deal_value' => 'nullable', 
            'labor_costs' => 'nullable', 
            'material_costs' => 'nullable', 
            'costs_of_goods' => 'nullable', 
            'market' => 'nullable', 
            'additional_costs'=> 'nullable',
            'sales_rep1_commission_percentage' => 'nullable', 
            'sales_rep2_commission_percentage' => 'nullable',
            'status' => 'nullable',
            'sales_rep1' => 'nullable|integer|exists:users,id',
            'sales_rep2' => 'nullable|integer|exists:users,id',
            'attachements.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,txt',
            'notes' => 'nullable',

        ]);
        
        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }
            
           

            $ready_to_close = ReadyToClose::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'sales_rep1_commission_percentage' => $request->sales_rep1_commission_percentage,
                'sales_rep2_commission_percentage' => $request->sales_rep2_commission_percentage,
                'deal_value' => $request->deal_value,
                'labor_costs' => $request->labor_costs,
                'material_costs' => $request->material_costs,
                'costs_of_goods' => $request->costs_of_goods,
                'market' => $request->market, //
                'additional_costs' => $request->additional_costs,
                'status' => (isset($request->status)) ? $request->status : false,
                'notes' => $request->notes,
            ]);
            
            $user_ids = [];

               //store attachements here
               if(isset($request->attachements) && count($request->attachements) > 0) {
                foreach($request->attachements as $documents)
                {
                    $fileName = time() . '_' . $documents->getClientOriginalName();
                    $filePath = $documents->storeAs('public/ready_to_close', $fileName);
                    // Store Path
                    $media = new ReadyToCloseMedia();
                    $media->ready_close_id = $ready_to_close->id;
                    $media->image_url = Storage::url($filePath);
                    $media->file_name = $request->file_name;
                    $media->save();
                }
            }
            
            //auto calculation for net revence and profit calculation
            $overhead_percentage = OverheadPercentage::select('overhead_percentage')->first();
            $job_total = $ready_to_close->deal_value;
            $job_total = (float) preg_replace('/[^0-9.]/', '', $job_total);
            $overhead_percentage = (float) $overhead_percentage->overhead_percentage;
            $value_overhead_deduction = ($job_total * $overhead_percentage) / 100;
            $net_revenue = $job_total - $value_overhead_deduction;

            $ready_to_close->net_revenue = $net_revenue;
            $ready_to_close->save();

            //profit calculation
            $material_cost =  (float) preg_replace('/[^0-9.]/','',$ready_to_close->material_costs);
            $labor_cost =  (float) preg_replace('/[^0-9.]/','',$ready_to_close->labor_costs);
            $additional_costs =  (float) preg_replace('/[^0-9.]/','',$ready_to_close->additional_costs);
            $costs_of_goods =  (float) preg_replace('/[^0-9.]/','',$ready_to_close->costs_of_goods);
            $total_expense = $material_cost + $labor_cost + $additional_costs + $costs_of_goods + $value_overhead_deduction;

            $profit = $job_total-$total_expense;

            $ready_to_close->net_profit = $profit;
            $ready_to_close->save();

            // return response()->json([
            //     'status' => 200,
            //     'message' => 'Ready To Close Updated Successfully',
            //     'material_cost' => $material_cost,
            //     'labor_costs' => $labor_cost,
            //     'overhead_value' => $value_overhead_deduction,
            //     'additional_cost' => $additional_costs,
            //     'costs_of_goods' => $costs_of_goods,
            //     'total_expense' => $total_expense,
            //     'profit' => $profit
            // ], 200); 

            if (isset($request->sales_rep1)) {
                $user_ids[] = $request->sales_rep1;
            }
            
            if (isset($request->sales_rep2)) {
                $user_ids[] = $request->sales_rep2;
            }
            
            //Assign Job
            $job->users()->sync($user_ids);
            
            //Update Job Status
            if(isset($request->status) && $request->status == true)
            {
                $job->status_id = 15;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Close Updated Successfully',
                'data' => $ready_to_close->load('media')
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateReadyToCloseStatus(Request $request, $jobId)
    {
        // dd($request->all());
        //Validate Request
        $request->validate([
            'status' => 'nullable',
        ]);
        
        try {
            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }
            
            //add aditional Documents
            $documents = 
            $ready_to_close = ReadyToClose::updateOrCreate([
                'company_job_id' => $jobId
            ],[
                'company_job_id' => $jobId,
                'status' =>  $request->status,
            ]);
            
            //Update Job Status
            if(isset($request->status) && $request->status == true)
            {
                $job->status_id = 17;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                 //current stage save
                 $ready_to_close->current_stage="yes";
                 $ready_to_close->save();

            }elseif(isset($request->status) && $request->status == false)
            {
                $job->status_id = 15;
                $job->date = Carbon::now()->format('Y-m-d');
                $job->save();

                $ready_to_close->current_stage="no";
                $ready_to_close->save();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Close Status Updated Successfully',
                'data' => $ready_to_close,
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getReadyToClose($jobId)
    {
        try {
            
            //Check Job
            $job = CompanyJob::whereId($jobId)->with('materialOrder')->first();
            // dd($job);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job Not Found'
                ], 422);
            }
            
            $userIds = $job->users()->pluck('user_id')->toArray();
            
            $ready_to_close = ReadyToClose::where('company_job_id', $jobId)->first();
            if(is_null($ready_to_close)) {
                $object = new \StdClass();
                $object->sales_rep1 = isset($userIds[0]) ? $userIds[0] : null;
                $object->sales_rep2 = isset($userIds[1]) ? $userIds[1] : null;
                $object->square_count = !is_null($job->materialOrder) ? $job->materialOrder->square_count : null;
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Ready To Close Not Yet Created',
                    'data' => $object
                ], 200);
            }
            
            $ready_to_close->sales_rep1 = isset($userIds[0]) ? $userIds[0] : null;
            $ready_to_close->sales_rep2 = isset($userIds[1]) ? $userIds[1] : null;
            $ready_to_close->square_count = !is_null($job->materialOrder) ? $job->materialOrder->square_count : null;
            
            return response()->json([
                'status' => 200,
                'message' => 'Ready To Close Found Successfully',
                'data' => $ready_to_close->load('media')
            ], 200); 
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function jobSearch(Request $request)
    {
        $this->validate($request, [
            'search_term' => 'required|string|max:255',
        ]);

        try {
            $searchTerm = $request->input('search_term');
            $user = Auth::user();
            
            $jobs = CompanyJob::where('status_id',17)
            ->where(function($query) use ($searchTerm) {
                $query->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('address', 'LIKE', "%{$searchTerm}%");
            })->get();
            

            // if (in_array($user->role_id, [1, 2,8,9])) {
            //     $users = $jobs->get();
            // } elseif ($user->role_id == 7) {
            //     $users = $jobs->whereNotIn('role_id', [7])->get();
            // } else {
            //     $users = collect(); // Return an empty collection if no roles match
            // }
            
            return response()->json([
                'status_code' => 200,
                'status' => true,
                'data' => $jobs,
            ]);
            
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'status' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
