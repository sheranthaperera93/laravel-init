<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Permission;
use App\Role;
use Log;

class PermissionsController extends Controller
{
    public function index()
    {
        return Permission::all();
    }

    public function getPermissionById(Request $request, $permId)
    {
        $permission = Permission::find($permId);
        if (count($permission) > 0) {
            return response()->json($permission, 200);
        } else {
            return response()->json("No Permission Information Available", 404);
        }
    }

    public function createPermission (Request $request)
    {
        try{
            $permission = new Permission();
            $permission->name = $request->input("permissionName");
            $insertCount = $permission->save();
            if($insertCount == 1){
                Log::channel('daily')->info("Permission create failed");
                return response()->json("Successfully Created", 201);
            }else{
                Log::channel('daily')->error("Permission create failed");
                return response()->json("Permission Creation Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("Permission create failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }

    public function updatePermission (Request $request, $permId)
    {
        try{
            $permission = Permission::find($permId);
            $permission->name = $request->input("permissionName");
            $updateCount = $permission->save();
            if($updateCount == 1){
                Log::channel('daily')->info("Permission update success", ['id' => $id]);
                return response()->json("Successfully Updated", 201);
            }else{
                Log::channel('daily')->error("Permission update failed", ['id' => $id]);
                return response()->json("Permission Update Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("Permission update failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }

    public function deletePermission (Request $request, $permId)
    {
        try{
            $roles = Role::all();
            foreach ($roles as $role) {
                $permList = explode(",",$role->permission_ids);
                if(in_array((string)$permId, $permList, true)){
                    Log::channel('daily')->error("Permission already assigned to user role", ['permission_id' => $permId, 'role_id' => $role->id]);
                    return response()->json("Permission Already Assigned. Cannot Be Deleted", 200);
                }
            }
            $permission = Permission::find($permId);
            $response = $permission->delete();
            if($response > 0 ) {
                Log::channel('daily')->info("Permission delete success", ['id' => $permId]);
                return response()->json("Successfully Deleted", 200);
            }else{
                Log::channel('daily')->error("Permission delete failed", ['id' => $permId]);
                return response()->json("Permission Delete Failed", 500);
            }
        } catch (\Exception $e) {
            Log::critical("Permission delete failed",[$e->getMessage()]);
            return response()->json("Request Failed", 500);
        }
    }
}
