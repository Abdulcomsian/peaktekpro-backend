<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CrewInformation;
use App\Http\Resources\CrewInformationResource;
use App\Models\CompanyJob;
use Exception;

class CrewInformationController extends Controller
{
    public function addCrewInformation(Request $request,$jobId)
    {
        // dd($jobId);
        try{
            $request->validate([
                'build_date'=> 'nullable|string',
                'status' => 'nullable|string',
                'crew_name' => 'nullable|string',
                // 'data' => 'nullable|string'
            ]);

            $companyjob = CompanyJob::find($jobId);
            if(!$companyjob){
                return response()->json([
                    'status'=> true,
                    'message' => 'company job not found'
                ]);
            }
    
            // $crewInformation = CrewInformation::updateOrCreate([
            //     'company_job_id' => $jobId,
            // ],
            // [
            //     'company_job_id' => $jobId,
            //     'build_date' => $request->build_date,
            //     'status' => $request->status,
            //     'crew_name' => $request->crew_name,
            //     // 'data' => $request->data,
            // ]);
            $crewInformation = CrewInformation::updateOrCreate([
                'company_job_id' => $jobId,
                'build_date' => $request->build_date,
                'status' => $request->status,
                'crew_name' => $request->crew_name,
                // 'data' => $request->data,
            ]);
    
            return new CrewInformationResource($crewInformation);

        }catch(Exception $e){
            return response()->json([
                'status'=> false,
                'message' => $e->getMessage()
            ]);
        }

    }

    public function getCrewInformation($jobId)
    {
        try{
            
            $companyjob = CompanyJob::find($jobId);
            if(!$companyjob){
                return response()->json([
                    'status'=> true,
                    'message' => 'company job not found'
                ]);
            }

            $crewInformation = CrewInformation::where('company_job_id', $jobId)->first();
            if($crewInformation){
                return response()->json([
                    'status'=> true,
                    'message' => 'data fetched successfully',
                    'data' => $crewInformation
                ]);
            }

            return response()->json([
                'status'=> true,
                'message' => 'crew information not found',
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status'=> false,
                'message' => $e->getMessage()
            ]);
        }
       

    }

    public function deleteCrewInformation($jobId)
    {
        try{
            
            $companyjob = CompanyJob::find($jobId);
            if(!$companyjob){
                return response()->json([
                    'status'=> false,
                    'message' => 'company job not found'
                ]);
            }
            $crewInformation = CrewInformation::where('company_job_id',$jobId)->first();
            if(!$crewInformation){
                return response()->json([
                    'status'=> true,
                    'message' => 'crew information not found',
                ]);
            }

            $crewInformation->delete();
            return response()->json([
                'status'=> true,
                'message' => 'crew information deleted successfully',
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status'=> false,
                'message' => $e->getMessage()
            ]);
        }
       

    }
}
