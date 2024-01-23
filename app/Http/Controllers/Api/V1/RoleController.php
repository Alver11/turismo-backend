<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index()
    {
        return Role::skip(1)->get();
    }

    /**
     * @throws Exception
     */
    public function getRoles(Request $request): JsonResponse
    {
        $queryRole = Role::query();
        $queryRole->with('permissions')
        ->where('guard_name', 'web');

        //return DataTables::of($query)->toJson();
        return DataTables::eloquent($queryRole)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && trim($request->input('search.value')) !== '') {
                    $searchValue = $request->input('search.value');
                    $query->where(function ($query) use ($searchValue) {
                        $query->where('name', 'ilike', "%{$searchValue}%");
                    });
                    $query->orWhereHas('permissions', function ($query) use ($searchValue) {
                        $query->where('description', 'ilike', "%{$searchValue}%")
                            ->where('guard_name', 'web');
                    });
                }
            })
            ->make();

    }
    public function indexPermission()
    {
        return Permission::get();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:roles|max:255',
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,id' // Asegura que cada ID de permiso exista
        ]);
        $role = Role::create(['name' => $validatedData['name'], 'guard_name' =>'web']);
        $role->syncPermissions($validatedData['permission']);
        return response()->json(['message' => 'Rol creado exitosamente', 'role' => $role]);
    }

    public function show($id)
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function update(Request $request, $roleId)
    {
        //$role = Role::findById($roleId);
        $role = Role::where('id', $roleId)->where('guard_name', 'web')->first();
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|unique:roles,name,' . $roleId . '|max:255',
            'permission' => 'required|array',
            'permission.*' => 'exists:permissions,id'
        ]);
        $role->name = $validatedData['name'];
        $role->save();
        $role->syncPermissions($validatedData['permission']);

        return response()->json(['message' => 'Rol actualizado exitosamente', 'role' => $role]);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();
            return response()->json(['message' => 'Eliminado con éxito']);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al Eliminar el Rol']);
        }
    }
}
