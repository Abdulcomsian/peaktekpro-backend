<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use Auth;
class CompanyLocationController extends Controller
{
    public function addCompanyLocation(Request $request)
    {
        $request->validate([
            'name'=>'nullable|string'
        ]);
        try{
            $user = Auth::user();
            $created_by = $user->company_id;
            $location = new Location();
            $location->name = $request->name;
            $location->created_by = $created_by;
            $location->save();
    
            return response()->json([
                'message' => 'Location Added Successfully',
                'status' => true,
                'status_code' => 200
            ]);

        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'error occured while Location Adding',
                'status' => false,
                'status_code' => 500
            ]);
        }
       
    }

    public function getCompanyLocation()
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        $location = Location::whereIn('created_by',[$company_id,0])->get();

        return response()->json([
            'message' => 'Location Fetched Successfully',
            'status' => true,
            'status_code' => 200,
            'data' => $location
        ]);

    }

    public function updateCompanyLocation(Request $request,$id)
    {
        $request->validate([
            'name'=>'nullable|string'
        ]);
        try{
            $user = Auth::user();
            $created_by = $user->company_id;
            $location = Location::find($id);
            $location->name = $request->name;
            $location->created_by = $created_by;
            $location->save();
    
            return response()->json([
                'message' => 'Location Updated Successfully',
                'status' => true,
                'status_code' => 200
            ]);

        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'error occured while Location Updating',
                'status' => false,
                'status_code' => 500
            ]);
        }
       
    }

    public function deleteCompanyLocation($id)
    {
        $location = Location::find($id);
        if($location){
            $location->delete();
            return response()->json([
                'message' => 'Location Deleted Successfully',
                'status' => true,
                'status_code' => 200
            ]);
        }
        return response()->json([
            'message' => 'Location Not Exist',
            'status' => true,
            'status_code' => 500
        ]);

    }

}
