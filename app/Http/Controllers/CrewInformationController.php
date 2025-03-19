<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\CrewInformation;
use App\Mail\CrewInformationEmail;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\CrewInformationResource;

class CrewInformationController extends Controller
{
    public function addCrewInformation(Request $request,$jobId)
    {
        try{
            $request->validate([
                'build_date'=> 'nullable|string',
                'status' => 'nullable|string',
                'crew_name' => 'nullable|string',
                'content' => 'nullable|string',
                'email'=> 'nullable|string',
            ]);

            $companyjob = CompanyJob::find($jobId);
            if(!$companyjob){
                return response()->json([
                    'status'=> true,
                    'message' => 'company job not found'
                ]);
            }
    
            $crewInformation = CrewInformation::create([
                'company_job_id' => $jobId,
                'build_date' => $request->build_date,
                'status' => $request->status,
                'crew_name' => $request->crew_name,
                'content' => $request->content,
                'email'=> $request->email,
            ]);

            Mail::to($request->email)->send(new CrewInformationEmail($crewInformation));

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

            $crewInformation = CrewInformation::where('company_job_id', $jobId)->get();
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
