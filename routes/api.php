<?php

use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\TouristPlaceController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([
    'prefix' => 'v1'
], function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::get('/google-auth/redirect', function () {
        return Socialite::driver('google')->redirect();
    });

    Route::get('/google-auth/callback', function () {
        $user = Socialite::driver('google')->user();

        // $user->token
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('getRoles', [RoleController::class, 'getRoles']);
        Route::get('permission', [RoleController::class, 'indexPermission']);

        Route::apiResources([
            'roles' => RoleController::class,
            'users' => UserController::class,
            'categories' => CategoryController::class,
            'attributes' => AttributeController::class,
            'tourists' => TouristPlaceController::class,
        ]);

        Route::get('get_departments', [DepartmentController::class, 'getDepartments']);
        Route::get('districts', [DepartmentController::class, 'getDistricts']);
        Route::get('get_categories', [CategoryController::class, 'getCategory']);
        Route::get('get_attribute', [AttributeController::class, 'getAttribute']);

    });

    Route::get('get_places', [TouristPlaceController::class, 'getPlace']);
});
