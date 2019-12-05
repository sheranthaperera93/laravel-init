<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use Illuminate\Support\Facades\DB;
use Log;

class RolesController extends Controller
{
    public function index()
    {
        return Role::all();
    }

    public function getPermissionByRoleId(Request $request, $id)
    {
        $role = Role::find($id);
        if (count($role) > 0) {
            Log::channel('daily')->info("Permission found for ID", ['id' => $id]);
            return response()->json($role, 200);
        } else {
            Log::channel('daily')->info("Permission not found for ID", ['id' => $id]);
            return response()->json("No Role Information Available", 404);
        }
    }

    public function getRolesPaged(Request $request, $offset, $length, $sortingCol, $sortingDir, $roleName)
    {
        Log::channel('daily')->info("Fetching roles", ['role_name' => $roleName]);
        $dataCount = DB::table('roles')
        ->where('id', '!=', 1)
        ->when($roleName != 'null', function ($query) use ($roleName) {
            return $query->where('roles.name', 'LIKE', $roleName.'%');
        })
        ->count();
        //Data fetch query builder
        $data = DB::table('roles')
        ->where('id', '!=', 1)
        ->when($roleName != 'null', function ($query) use ($roleName) {
            return $query->where('roles.name', 'LIKE', $roleName.'%');
        })
        ->orderBy($sortingCol, $sortingDir)
        ->offset($offset)
        ->limit($length)
        ->get();
        if ($dataCount > 0) {
            Log::channel('daily')->info("Roles found", ['total_count' => $dataCount]);
            return response()->json(["data" => $data, "totalCount" => $dataCount], 200);
        } else {
            Log::channel('daily')->info("Roles not found");
            return response()->json("No Data Available", 200);
        }
    }

    public function createRole(Request $request)
    {
        try{
            $role = new Role();
            $role->name = $request->input("roleName");
            $role->permission_ids = $request->input("permissionIds");
            $insertCount = $role->save();
            if($insertCount == 1){
                Log::channel('daily')->info("Role created", ['role_name' => $request->input("roleName"), 'permission_ids' => $request->input("permissionIds")]);
                return response()->json("Successfully Created", 201);
            }else{
                Log::channel('daily')->error("Role create failed", ['role_name' => $request->input("roleName"), 'permission_ids' => $request->input("permissionIds")]);
                return response()->json("Role Creation Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("Role create failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }

    public function updateRole(Request $request, $id)
    {
        try{
            $role = Role::find($id);
            $role->name = $request->input("roleName");
            $role->permission_ids = $request->input("permissionIds");
            $updateCount = $role->save();
            if($updateCount == 1){
                Log::channel('daily')->info("Role update", ['role_id' => $id, 'role_name' => $request->input("roleName"), 'permission_ids' => $request->input("permissionIds")]);
                return response()->json("Successfully Updated", 201);
            }else{
                Log::channel('daily')->error("Role update failed", ['role_id' => $id, 'role_name' => $request->input("roleName"), 'permission_ids' => $request->input("permissionIds")]);
                return response()->json("Role Update Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("Role update failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }

    public function deleteRole(Request $request, $id)
    {
        try{
            $role = Role::find($id);
            $response = $role->delete();
            if($response > 0 ) {
                Log::channel('daily')->info("Role deleted", ['role_id' => $id]);
                return response()->json("Successfully Deleted", 200);
            }else{
                Log::channel('daily')->error("Role delete failed", ['role_id' => $id]);
                return response()->json("Role Delete Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("Role delete failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }
}
