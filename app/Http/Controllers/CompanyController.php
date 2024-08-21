<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function getCompanyUsers()
    {
        try {

            $user = Auth::user();
            if($user->created_by == 0) {
                $companyId = 1;
            } else {
                $companyId = $user->created_by;
            }

            $users = User::where('created_by', $companyId)->where('role_id', 5)->with('role')->get();

            return response()->json([
                'status' => 200,
                'message' => 'Users Found Successfully',
                'data' => $users
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanySubContractors()
    {
        try {

            $user = Auth::user();
            if($user->created_by == 0) {
                $companyId = 1;
            } else {
                $companyId = $user->created_by;
            }

            $contractots = User::where('created_by', $companyId)->where('role_id', 3)->with('role')->get();

            return response()->json([
                'status' => 200,
                'message' => 'Sub Contractors Found Successfully',
                'data' => $contractots
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanySuppliers()
    {
        try {

            $user = Auth::user();
            if($user->created_by == 0) {
                $companyId = 1;
            } else {
                $companyId = $user->created_by;
            }

            $suppliers = User::where('created_by', $companyId)->where('role_id', 4)->with('role')->get();

            return response()->json([
                'status' => 200,
                'message' => 'Suppliers Found Successfully',
                'data' => $suppliers
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }

    public function getCompanyAdjustors()
    {
        try {

            $user = Auth::user();
            if($user->created_by == 0) {
                $companyId = 1;
            } else {
                $companyId = $user->created_by;
            }

            $adjustors = User::where('created_by', $companyId)->where('role_id', 6)->with('role')->get();

            return response()->json([
                'status' => 200,
                'message' => 'Adjustors Found Successfully',
                'data' => $adjustors
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage().' on line '.$e->getLine().' in file '.$e->getFile()], 500);
        }
    }
}
