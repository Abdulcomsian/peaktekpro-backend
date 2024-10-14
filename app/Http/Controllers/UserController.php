<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
class UserController extends Controller
{
    public function addUser(Request $request)
    {
        dd($request->all());
        $this->validate($request, [
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'email' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            'permission_level_id' => 'nullable:roles,id',
            'status' => 'nullable|in:active,inactive',
        ]);

        try{
            $user = new User;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->company_id = $request->company_id;
            $user->role_id = $request->permission_level_id;
            $user->status = $request->status;
            $user->save();

            return response()->json([
                'status_code'=> 200,
                'status' => true,
                'message' => 'User Created Succcessfully',
                // 'data' => []
            ]);
        
        }catch(Exception $e){
                return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
        
    }
}
