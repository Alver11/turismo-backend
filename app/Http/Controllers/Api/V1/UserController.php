<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use App\Models\Image;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use function PHPUnit\Framework\isEmpty;

class UserController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $queryUser =  User::query();
        if ($user->hasRole('Super-Admin') || $user->roles()->whereHas('permissions', function ($query) {
                $query->where('name', 'user list all');
            })->exists()) {
            return DataTables::eloquent($queryUser)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && trim($request->input('search.value')) !== '') {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($query) use ($searchValue) {
                            $query->where('name', 'ilike', "%{$searchValue}%")
                                ->orWhere('email', 'ilike', "%{$searchValue}%")
                                ->orWhere('phone', 'ilike', "%{$searchValue}%");
                        });
                        $query->orWhereHas('roles', function ($query) use ($searchValue) {
                            $query->where('name', 'ilike', "%{$searchValue}%");
                        });
                    }
                })
                ->make();
        } else {
            return DataTables::of([])->toJson();
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->input('data'), true);
        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required|string|email|max:50|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        DB::transaction(function () use ($request, $data,) {
            $imagePath = "";
            if ($request->hasFile('image') && $request->file('image') !== 'null') {
                $imagePath = $request->file('image')->store('images/profile');
            }
            $user = User::create([
                "profile_path" => $imagePath,
                "name" => $data['name'],
                "email" => $data['email'],
                "phone" => $data['phone'],
                "password" => Hash::make($data['password']),
            ]);

            $roles = Role::whereIn('id', $data['roles'])->get();

            foreach ($roles as $role) {
                $user->assignRole($role);
            }
        });
        return response()->json(['message' => 'Usuario creado con éxito'], 201);
    }

    public function show(User $user): User
    {
        return $user;
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = json_decode($request->input('data'), true);
        $userOld = User::findOrFail($id);
        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => [
                'required',
                'string',
                'email',
                'max:50',
                Rule::unique('users')->ignore($userOld->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        DB::transaction(function () use ($request, $data, $userOld) {
            $imagePath = $data['profile_path'];
            if($imagePath != $userOld->profile_path){
                if($imagePath == '' || $imagePath == null || !isEmpty($userOld->profile_path)){
                    Storage::delete($userOld->profile_path);
                }else{
                    if($userOld->profile_path){
                        Storage::delete($userOld->profile_path);
                    }
                    if ($request->hasFile('image') && $request->file('image') !== 'null') {
                        $imagePath = $request->file('image')->store('images/profile');
                    }
                }
            }

            $userOld->profile_path = $imagePath;
            $userOld->name = $data['name'];
            if($data['email'] != $userOld->email){
                $userOld->email = $data['email'];
            }
            $userOld->phone = $data['phone'];
            if(isset($data['password'])){
                $userOld->password = Hash::make($data['password']);
            }
            $userOld->save();

            if (empty($data['roles'])) {
                // Si el array roles está vacío, eliminar todas las asignaciones de roles
                $userOld->roles()->detach();
            } else {
                // Si hay roles, sincronizar con los nuevos roles
                $roles = Role::whereIn('id', $data['roles'])->pluck('id');
                $userOld->roles()->sync($roles);
            }
        });
        return response()->json(['message' => 'Usuario actualizado con éxito'], 201);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === 1) {
            return response()->json(['error' => 'No se puede eliminar el superadministrador'], 403);
        }
        DB::transaction(function () use ($user) {
            $user->roles()->detach();
            if($user->profile_path != null || $user->profile_path != ''){
                Storage::delete($user->profile_path);
            }
            $user->delete();
        });

        return response()->json(['message' => 'Usuario eliminado con éxito'], 200);
    }

}
