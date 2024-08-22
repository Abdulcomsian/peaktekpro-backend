<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
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
                ->where('role_id', 5)->with('role');

            if (isset($request->results) && isset($request->page)) {
                $users = $usersQuery->skip($skipped)->take($results)->get();
            } else {
                $users = $usersQuery->get();
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
                'data' => $user
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
}
