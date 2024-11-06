<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use App\Models\Company;
use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Enums\PermissionLevel;
use App\Mail\UserPasswordMail;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CompanyController extends Controller
{
    public function createCompany(Request $request)
    {
        // Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'website' => 'required|string',
            'site_admin_name' => 'required|string',
            'site_admin_email' => 'required|email|unique:users,email',
            'password' => 'required|string', 
            'confirm_password' => 'required|string|same:password',
            'permission_level' => 'nullable|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
            'status' => 'required|string|in:active,inactive'
        ]);
        
        try {
            $user = Auth::user();
            if ($user->role_id == 7) {
                // Create Company
                $company = new Company;
                $company->name = $request->name;
                $company->website = $request->website;
                $company->status = $request->status;
                $company->save();

                // Generate password from request data
                $password = $request->password;

                // Split the full name from the request
                $fullName = $request->site_admin_name;
                $nameParts = explode(' ', $fullName);

                // Assign the first and last names
                $firstName = $nameParts[0];
                $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';

                $create_user = User::create([
                    'company_id' => $company->id,
                    'name' => $request->site_admin_name,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $request->site_admin_email,
                    'password' => Hash::make($password),
                    'role_id' => $request->permission_level,
                    'created_by' => $company->id,
                    'status' => $request->status,
                ]);

                // Optionally, send an email with the password
                // Mail::to($request->site_admin_email)->send(new UserPasswordMail($request->site_admin_email, $password));

                // Assign Role
                $user_role = UserRole::updateOrCreate([
                    'user_id' => $create_user->id,
                    'company_id' => $company->id
                ], [
                    'user_id' => $create_user->id,
                    'company_id' => $company->id
                ]);

                return response()->json([
                    'status' => 201,
                    'message' => 'Company Created Successfully',
                    'data' => $company,
                ], 201);
            }

            return response()->json([
                'status' => 422,
                'message' => 'Permission Denied!',
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function createCompany1(Request $request)
    {
        //Validate Request
        $this->validate($request, [
            'name' => 'required|string',
            'website' => 'required|string',
            'site_admin_name' => 'required|string',
            'site_admin_email' => 'required|email|unique:users,email',
            'permission_level' => 'nullable|integer|in:' . implode(',', array_column(PermissionLevel::cases(), 'value')),
            'status' => 'required|string|in:active,inactive'
        ]);
        
        try {

            $user = Auth::user();
            if($user->role_id == 7 )
            {
                // Create Company
                $company = new Company;
                $company->name = $request->name;
                $company->website = $request->website;
                $company->status  = $request->status;

                $company->save();
        
                $password= "12345678";
                // Create a new user
                // Split the full name from the request
                $fullName = $request->site_admin_name;
                $nameParts = explode(' ', $fullName);

                // Assign the first and last names
                $firstName = $nameParts[0];
                $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';

                $create_user = User::create([
                    // 'role_id' => 1,
                    'company_id' => $company->id,
                    'name' => $request->site_admin_name,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $request->site_admin_email,
                    'password' => Hash::make('12345678'),
                    'role_id'=>$request->permission_level,
                    'created_by' => $company->id,
                    'status' => $request->status,

                ]);
    
                   // Send the password email
                // Mail::to($request->site_admin_email)->send(new UserPasswordMail($request->site_admin_email, $password));
            
                //Assign Role
                $user_role = UserRole::updateOrCreate([
                    'user_id' => $create_user->id,
                    'company_id' => $company->id
                ],[
                    'user_id' => $create_user->id,
                    'company_id' => $company->id
                ]);
                
                return response()->json([
                    'status' => 201,
                    'message' => 'Company Created Successfully',
                    'data' => $company,
                ], 201);
            }
            
            return response()->json([
                'status' => 422,
                'message' => 'Permission Denied!',
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
    
    public function getCompany($id)
    {
        try {
            
            $user = Auth::user();
            if($user->role_id == 7)
            {
                $company = Company::find($id);
                if(!$company) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Company Not Found',
                    ], 422);
                }
                
                return response()->json([
                    'status' => 201,
                    'message' => 'Company Found Successfully',
                    'data' => $company,
                ], 201);
            }
            
            return response()->json([
                'status' => 422,
                'message' => 'Permission Denied!',
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanies()
    {
        try {
            $user = Auth::user();
            $companies = [];

            if ($user->role_id == 7) { // Super Admin
                $companies = Company::with('companyAdmin')
                    ->withCount('users')
                    ->get();

                if ($companies->isEmpty()) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'No Company Found Yet',
                    ], 422);
                }

                // Format the response to include company details inside 'company' object
                $formattedCompanies = $companies->map(function ($company) {
                    return [
                        'company' => [
                            'id' => $company->id,
                            'name' => $company->name,
                            'website' => $company->website,
                            'status' => $company->status,
                            'created_at' => $company->created_at,
                            'updated_at' => $company->updated_at,
                            'users_count' => $company->users_count,
                            'admin_name' => $company->companyAdmin->name ?? null,
                            'admin_email' => $company->companyAdmin->email ?? null,
                            'admin_role_id' => $company->companyAdmin->role_id ?? null,
                        ],
                    ];
                });

                return response()->json([
                    'status' => 200,
                    'message' => 'Companies Found Successfully',
                    'data' => $formattedCompanies,
                ], 200);

            } elseif ($user->role_id == 2 || $user->role_id == 1) { // Site Admin or Company Admin
                $companies = Company::with('companyAdmin')
                    ->withCount('users')
                    ->where('id', $user->company_id)
                    ->get();

                // Format the response similarly for Site Admin or Company Admin
                $formattedCompanies = $companies->map(function ($company) {
                    return [
                        'company' => [
                            'id' => $company->id,
                            'name' => $company->name,
                            'website' => $company->website,
                            'status' => $company->status,
                            'created_at' => $company->created_at,
                            'updated_at' => $company->updated_at,
                            'users_count' => $company->users_count,
                            'admin_name' => $company->companyAdmin->name ?? null,
                            'admin_email' => $company->companyAdmin->email ?? null,
                            'admin_role_id' => $company->companyAdmin->role_id ?? null,
                        ],
                    ];
                });

                return response()->json([
                    'status' => 200,
                    'message'=> 'Companies Found Successfully',
                    'data' => $formattedCompanies,
                ], 200);
            } else {
                return response()->json([
                    'status' => 422,
                    'message' => 'Permission Denied!',
                ], 422);
            }
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }


    public function getCompanies12()
    {
        try {
            $user = Auth::user();
            $companies = [];

            if ($user->role_id == 7) { // Super Admin
                // Fetch companies with site admins and user counts
                $companies = Company::with('siteAdmins') // Eager load site admins
                    ->withCount('users') // Count users for each company
                    ->get();

                if ($companies->isEmpty()) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'No Company Found Yet',
                    ], 422);
                }

                // Format the response to include the full company object
                $formattedCompanies = $companies->map(function ($company) {
                    $company->users_count = $company->users_count;
                    return [
                        'company' => $company,
                        //  $company, // Full company object
                        // 'site_admin' => $company->siteAdmin, // Site admin details
                        // 'users_count' => $company->users_count, // Count of users
                    ];
                });
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Companies Found Successfully',
                    'data' => $formattedCompanies,
                ], 200);
            } elseif ($user->role_id == 2 || $user->role_id == 1) { // Site Admin or Company Admin
                // Fetch specific company for the logged-in user
                $companies = Company::with('siteAdmins') // Eager load site admin
                    ->withCount('users') // Count users for this specific company
                    ->where('id', $user->company_id)
                    ->get();

                // Format the response similarly
                $formattedCompanies = $companies->map(function ($company) {
                    $company->users_count = $company->users_count;
                    return [
                        'company' => $company, // Full company object
                    ];
                });

                return response()->json([
                    'status' => 201,
                    'message'=> 'Companies Found Successfully',
                    'data' => $formattedCompanies,
                ], 200);
            } else {
                return response()->json([
                    'status' => 422,
                    'message' => 'Permission Denied!',
                ], 422);
            }
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()], 500);
        }
    }

    public function filterCompanyByStatus(Request $request)
    {
        $this->validate($request, [
            'status' => 'nullable'
        ]);

        try {
            $user= Auth::user();
            if($user->role_id == 7)
            {
                $status = $request->input('status');
                // Filter users by the active
                $companies = Company::with('siteAdmin')
                ->withCount('users')
                ->where('status', $status)
                ->get();

                // Format the response similarly
                $formattedCompanies = $companies->map(function ($company) {
                    $company->users_count = $company->users_count;
                    return [
                        'company' => $company, // Full company object
                    ];
                });
    
                return response()->json([
                    'status_code' => 200,
                    'status' => true,
                    'data' => $formattedCompanies,
                ]);
            }elseif($user->role_id == 1 || $user->role_id == 2)
            {
                $status = $request->input('status');
                // Filter users by the specified permission level
                $companies = Company::with('siteAdmin')
                ->withCount('users')
                ->where('status', $status)->where('id',$user->company_id)
                ->get();

                 // Format the response similarly
                 $formattedCompanies = $companies->map(function ($company) {
                    $company->users_count = $company->users_count;
                    return [
                        'company' => $company, // Full company object
                    ];
                });
    
    
                return response()->json([
                    'status_code' => 200,
                    'status' => true,
                    'data' => $formattedCompanies,
                ]);
            }
            else{
                return response()->json([
                    'status_code' => 422,
                    'status' => true,
                    'message' => 'Not allowed',
                ]);
            }
           
        } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);

        }
    }

    
    public function updateCompany1(Request $request, $id)
    {
        try {
            
            $user = Auth::user();
            if($user->role_id == 7 || $user->company_id == $id)
            {
                // Check Company
                $company = Company::find($id);
                if(!$company) {
                    return response()->json([
                        'status' => 422,
                        'message' => 'Company Not Found',
                    ], 422);
                }
                
                $update_user = User::where('company_id', $id)->first();
                
                $this->validate($request, [
                    'name' => 'required|string',
                    'website' => 'required|string',
                    'site_admin_name' => 'required|string',
                    'site_admin_email' => 'required|email|unique:users,email,' . $update_user->id,
                    'status' => 'required|string|in:active,inactive'
                ]);
                
                // Update Company
                $company->name = $request->name;
                $company->website = $request->website;
                $company->status = $request->status;

                $company->save();
        
                // Update user
                if($update_user) {
                    $update_user->name = $request->site_admin_name;
                    $update_user->email = $request->site_admin_email;
                    $update_user->status = $request->status;
                    $update_user->save();
                }
                
                return response()->json([
                    'status' => 200,
                    'message' => 'Company Updated Successfully',
                    'data' => $company,
                ], 200);
            }
            
            return response()->json([
                'status' => 422,
                'message' => 'Permission Denied!',
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function updateCompany(Request $request, $id)
    {
        try {
            $user = Auth::user();
            // dd($user);
            // Check user permissions
            // if ($user->role_id == 7 || $user->role_id == 1) {
            if($user->role_id == 7 || ($user->role_id == 1 && $user->company_id == $id)){
            // Check Company
            $company = Company::find($id);
            if (!$company) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Company Not Found',
                ], 422);
            }

            $update_user = User::where('company_id', $id)->first();

            // Validate incoming request
            $this->validate($request, [
                'name' => 'nullable|string',
                'website' => 'nullable|string',
                'site_admin_name' => 'nullable|string',
                'site_admin_email' => 'nullable|email|unique:users,email,' . ($update_user ? $update_user->id : ''),
                'status' => 'nullable|string|in:active,inactive',
            ]);

            // Update Company
            $company->name = $request->input('name', $company->name);
            $company->website = $request->input('website', $company->website);
            $company->status = $request->input('status', $company->status);
            $company->save();

            // Update User
            if ($update_user) {
                $update_user->name = $request->input('site_admin_name', $update_user->name);
                $update_user->email = $request->input('site_admin_email', $update_user->email);
                $update_user->status = $request->input('status', $update_user->status);
                $update_user->role_id = 1;
                $update_user->save();
            }

            // $data=$company->with('siteAdmin');

            return response()->json([
                'status' => 200,
                'message' => 'Company Updated Successfully',
                'data' => ['company'=>$company, 'user'=>$update_user],
            ], 200);
        }else{
            return response()->json([
                'status_code' =>false,
                'status' => 422,
                'message' => 'Permission Denied!',
            ], 422);
        }
           
        
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile()
            ], 500);
        }
    }

    
    public function getCompanyUsers(Request $request)
    {
        try {

            $user = Auth::user();
            // Determine the company ID
            $companyId = ($user->created_by == 0) ? 1 : $user->created_by;

            // Get pagination parameters
            $results = $request->input('results', 15);
            $page = $request->input('page', 1);

            // Calculate the offset for skipping
            $skipped = ($page - 1) * $results;

            $usersQuery = User::where('created_by', $companyId)
                ->whereIn('role_id', [2,8,9])->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
                // dd($users);
            } else {
                $users = $usersQuery->get();
                // dd($users);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Users Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanySubContractors(Request $request)
    {
        try {
            $data = [];

            $user = Auth::user();
            // Determine the company ID
            $companyId = ($user->created_by == 0) ? 1 : $user->created_by;

            // Get pagination parameters
            $results = $request->input('results', 15);
            $page = $request->input('page', 1);

            // Calculate the offset for skipping
            $skipped = ($page - 1) * $results;

            $usersQuery = User::where('created_by', $companyId)
                ->where('role_id', 3)->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
            } else {
                $users = $usersQuery->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Sub Contractors Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanySuppliers(Request $request)
    {
        try {

            $user = Auth::user();
            // Determine the company ID
            $companyId = ($user->created_by == 0) ? 1 : $user->created_by;

            // Get pagination parameters
            $results = $request->input('results', 15);
            $page = $request->input('page', 1);

            // Calculate the offset for skipping
            $skipped = ($page - 1) * $results;

            $usersQuery = User::where('created_by', $companyId)
                ->where('role_id', 4)->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
            } else {
                $users = $usersQuery->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Suppliers Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanyAdjustors(Request $request)
    {
        try {

            $user = Auth::user();
            // Determine the company ID
            $companyId = ($user->created_by == 0) ? 1 : $user->created_by;

            // Get pagination parameters
            $results = $request->input('results', 15);
            $page = $request->input('page', 1);

            // Calculate the offset for skipping
            $skipped = ($page - 1) * $results;

            $usersQuery = User::where('created_by', $companyId)
                ->where('role_id', 6)->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
            } else {
                $users = $usersQuery->get();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Adjustors Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

   

    public function searchCompany(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        try {
            $searchTerm = $request->input('name');

                $company = Company::with('siteAdmin')
                ->withCount('users')
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%{$searchTerm}%");
                })->get();

            return response()->json([
                'status_code' => 200,
                'status' => true,
                'data' => $company,
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);

        }
    }

    public function viewCompany($id)
    {
        try{
            $user =  Auth::user();
            // if($user->role_id == 7)
            $company = Company::with('siteAdmin')->find($id);
            if(!$company)
            {
                return response()->json([
                    'status_code' => 404,
                    'status' => true,
                    'message' => 'Company Not Exist',
                ]);
            }
            return response()->json([
                'status_code' => 200,
                'status' => true,
                'data' => $company,
            ]);

        }catch(\Exception $e){
            return response()->json(['error'=> $e->getMessage(). 'on Line' . $e->getLine(). 'in file'. $e->getFile()]);
        }
    }

    public function editCompany($id)
    {
        try{
            $company = Company::with('companyAdmin')->findOrFail($id);
            // return response()->json([
            //     'status_code' => 200,
            //     'status' => true,
            //     'data'=> $company,
            // ]);
            // Check if company admin exists and retrieve the name and email
            $companyAdmin = $company->companyAdmin;
            $companyAdminName = $companyAdmin ? $companyAdmin->name : null;
            $companyAdminEmail = $companyAdmin ? $companyAdmin->email : null;

            return response()->json([
                'status_code' => 200,
                'status' => true,
                'data' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'website' => $company->website,
                    'status' => $company->status,
                    'created_at' => $company->created_at,
                    'updated_at' => $company->updated_at,
                    'admin_name' => $companyAdminName,
                    'admin_email' => $companyAdminEmail,
                ],
            ]);

        }catch(ModelNotFoundException $e){
            return response()->json([
                'status_code' => 404,
                'status' => false,
                'message' => 'Company not found',
            ], 404);

        }catch(Exception $e){
            return response()->json([
                'status_code' => 500,
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

    }
}
