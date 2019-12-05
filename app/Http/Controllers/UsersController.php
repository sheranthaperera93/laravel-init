<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Log;
use File;
use Response;

class UsersController extends Controller
{
    public function index()
    {
        return User::where('role_code', '!=', 1)->get();
    }

    public function getUsers(Request $request, $offset, $length, $sortingCol, $sortingDir, $name, $email, $roleCode)
    {
        // Count fetch query builder
        $dataCount = User::
        where('role_code', '!=', 1)
        ->when($name != 'null', function ($query) use ($name) {
            return $query->where('name', 'LIKE', $name.'%');
        })
        ->when($email != 'null', function ($query) use ($email) {
            return $query->where('email', 'LIKE', $email.'%');
        })
        ->when($roleCode != 'null', function ($query) use ($roleCode) {
            return $query->whereIn("role_code", explode(",", $roleCode));
        })
        ->count();

        //Data fetch query builder
        $data = User::
        where('role_code', '!=', 1)
        ->when($name != 'null', function ($query) use ($name) {
            return $query->where('name', 'LIKE', $name.'%');
        })
        ->when($email != 'null', function ($query) use ($email) {
            return $query->where('email', 'LIKE', $email.'%');
        })
        ->when($roleCode != 'null', function ($query) use ($roleCode) {
            return $query->whereIn("role_code", explode(",", $roleCode));
        })
        ->orderBy($sortingCol, $sortingDir)
        ->offset($offset)
        ->limit($length)
        ->get();
        
        if ($dataCount > 0) {
            Log::channel('daily')->info("Fetched user details", ['name' => $name, 'email' => $email, 'role_code' => $roleCode]);
            return response()->json(["data" => $data, "totalCount" => $dataCount], 200);
        } else {
            Log::channel('daily')->info("Fetched user details not found", ['name' => $name, 'email' => $email, 'role_code' => $roleCode]);
            return response()->json(["data" => [], "totalCount" => 0], 200);
        }
    }

    public function createNewUser(Request $request)
    {
        try{
            $user = new User;
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $user->role_code = $request->input('roleCode');

            $insertCount = $user->save();
            if($insertCount == 1){
                Log::channel('daily')->info("User created");
                return response()->json("Successfully Created", 201);
            }else{
                Log::channel('daily')->error("User create failed");
                return response()->json("User Creation Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("User delete failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }

    public function updateUser(Request $request)
    {
        try{
            $user = User::find($request->input('id'));
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            if(!$request->input('password') == "bypassPassword"){
                $user->password = bcrypt($request->input('password'));
            }
            $user->role_code = $request->input('roleCode');

            $count = $user->save();
            if($count == 1){
                Log::channel('daily')->info("User updated", ['id' => $id]);
                return response()->json("Successfully Updated", 200);
            }else{
                Log::channel('daily')->error("User update failed", ['id' => $id]);
                return response()->json("User Update Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("User update failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }

    public function deleteUser(Request $request, $id)
    {
        try{
            $user = User::find($id);
            $count = $user->delete();
            if($count == 1){
                Log::channel('daily')->info("User deleted", ['id' => $id]);
                return response()->json("Successfully Deleted", 200);
            }else{
                Log::channel('daily')->error("User delete failed", ['id' => $id]);
                return response()->json("User Delete Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("User delete failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }

    public function testFunction(Request $request)
    {
        return response()->json("Test Route Success", 200);
    }

}
