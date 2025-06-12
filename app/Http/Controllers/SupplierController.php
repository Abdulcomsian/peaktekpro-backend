<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\UserRole;
use App\Models\CompanyJob;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\SupplierResource;

class SupplierController extends Controller
{
    public function storeSupplierold(Request $request, $jobId) //it was old method where handle the supplier on the base of job_id
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        try {

            //Check Job
            $job = CompanyJob::find($jobId);
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Job not found'
                ], 422);
            }

            // Create a new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            //Assign Role
            $user_role = new UserRole;
            $user_role->user_id = $user->id;
            $user_role->company_id = $jobId;
            $user_role->save();

            return response()->json([
                'status' => 201,
                'message' => 'Supplier Added Successfully',
                'user' => $user,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function storeSupplier(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            // 'password' => 'required|string|min:8',
            'location'=> 'nullable|string', 
            'status' => 'nullable|string',
            //////new fiels
            'subject' => 'nullable|string',
            'content' => 'nullable|string'
        ]);

        try {   

            $user = Auth::user();
            $created_by = $user->company_id; //here we save the company id
            $name = explode(' ', $request->name, 2);
            $firstName = $name[0];
            $lastName = isset($name[1]) ? $name[1] : '';
            // Create a new user
            $user = User::create([
                'role_id' => 4,
                'name' => $request->name,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $request->email,
                // 'password' => Hash::make($request->password),
                'company_id'=> $user->company_id,
                'created_by'=> $created_by,
                'location' => $request->location,
                'status' => $request->status
            ]);

            //save supplier subj and content here
            $emailTemplate = EmailTemplate::updateOrCreate(
                [
                    'supplier_id' => $user->id,
                    'company_id' => $user->company_id,
                ],
                [
                    'title' => $request->title ?? $user->name,
                    'content' => $request->content,
                    'subject' => $request->subject,

                ]
            );
            //Assign Role
            $user_role = new UserRole;
            $user_role->user_id = $user->id;
            $user_role->company_id = $user->company_id;
            $user_role->save();

            return response()->json([
                'status' => 201,
                'message' => 'Supplier Added Successfully',
                // 'user' => $user,
                // 'emailTemplate' => $emailTemplate,
                'user' => new SupplierResource($user, $emailTemplate)

            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getSuppliers($Id)
    {
        try {
            //Check Job
            $job = CompanyJob::find($Id);
            // $jobSummary = CompanyJob::with('summary')->where('id',$Id)->first();
            if(!$job) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company job not found',
                    'data' => []
                ], 422);
            }

            $summary=$job->summary;
            if(!$summary)
            {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Job Location not found',
                    'data' => []
                ]);
            }

            $location = $summary->market;  //this is job location
            // dd($location);
            $company_id = $job->created_by; //this is job company id
            // dd($location);Nashville
            $suppliers = User::where('role_id', 4)
            ->with('emailTemplate')
            ->whereHas('userRoles', function($query) use ($company_id){
                $query->where('company_id', $company_id); //if you want to get all supplier whos company_id is same as my job company id, check user_roles table because each role have company id here in this table also
            })
            ->get();   

            if($suppliers->isEmpty())
            {
                return response()->json([
                    'status' => 200,
                    'message' => 'Suppliers not Found',
                    'data' =>[],
                ], 404);
            }


            return response()->json([
                'status' => 201,
                'message' => 'Suppliers Found Successfully',
                'data' => $suppliers,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateSupplier(Request $request, $id)
    {
         //Validate Request
         $this->validate($request, [
            'name' => 'nullable|string',
            // 'email' => 'nullable|email|unique:users,email',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($id),
            ],
            // 'password' => 'nullable|string|min:8',
            'location'=> 'nullable|string',
            'status' => 'nullable|string',
                //////new fiels
            'subject' => 'nullable|string',
            'content' => 'nullable|string'
        ]);

        try {   

            $user = Auth::user();
            $created_by = $user->company_id; //here we save the company id
            $name = explode(' ', $request->name, 2);
            $firstName = $name[0];
            // dd($firstName);
            $lastName = isset($name[1]) ? $name[1] : '';    
            // Create a new user
            $user = User::where('id',$id)->first();
            $user->role_id = 4;
            $user->name = $firstName.' '. $lastName;
            $user->first_name = $firstName ?? $user->first_name;
            $user->last_name = $lastName ?? $user->last_name;
            $user->email = $request->email ?? $user->email;
            // $user->password = $request->password ?? $user->password;
            $user->company_id = $user->company_id;
            $user->created_by = $created_by;
            $user->location = $request->location ?? $user->location;
            $user->status = $request->status ?? $user->status;
            $user->save();

                //save supplier subj and content here
            $emailTemplate = EmailTemplate::updateOrCreate(
                [
                    'supplier_id' => $user->id,
                    'company_id' => $user->company_id,
                ],
                [
                    'title' => $request->title ?? $user->name,
                    'content' => $request->content,
                    'subject' => $request->subject,

                ]
            );

            //Assign Role
            $user_role = new UserRole;
            $user_role->user_id = $user->id;
            $user_role->company_id = $user->company_id;
            $user_role->save();

            return response()->json([
                'status' => 201,
                'message' => 'Supplier Updated Successfully',
                // 'user' => $user,
                 'user' => new SupplierResource($user, $emailTemplate)

            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getAllSuppliers()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $users = User::with('emailTemplate')
        ->where('role_id',4)->where('company_id',$companyId)->get();
        if($users)
        {
            return response()->json([
                'status' => 200,
                'message' => 'Supplier Updated Successfully',
                'user' => $users,
            ], 200);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Supplier Not Found',
            'user' => $users,
        ], 200);

    }

    public function deleteSupplier($id)
    {
        $user = User::where('id',$id)->first();
        if(!$user)
        {
            return response()->json([
                'status' => 200,
                'message' => 'Supplier Not Found',
                'user' => [],
            ], 200);
        }
        $user->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Supplier Deleted',
            'user' => [],
        ], 200);

    }
}
