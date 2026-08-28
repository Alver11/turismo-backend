<?php

use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EventCategoryController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\OpenAIController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\TouristPlaceController;
use App\Http\Controllers\Api\V1\TypeServiceController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

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
    'prefix' => 'v1',
], function () {

    Route::get('chart-panel', [CategoryController::class, 'chartDashboard']);
    Route::get('getEvents', [EventController::class, 'getEvents']);
    Route::get('getCategories', [EventController::class, 'getCategories']);
    Route::get('getTourist', [EventController::class, 'getTourist']);
    Route::get('getServices', [ServiceController::class, 'getServices']);

    Route::post('login', [AuthController::class, 'login']);
    Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::get('/google-auth/redirect', [AuthController::class, 'googleRedirect'])
        ->middleware('throttle:10,1');
    Route::get('/google-auth/callback', [AuthController::class, 'googleCallback'])
        ->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('ask', [OpenAIController::class, 'askQuestion'])
            ->middleware(['permission:ai ask', 'throttle:ai']);
        Route::middleware('permission:program roles')->group(function () {
            Route::get('getRoles', [RoleController::class, 'getRoles']);
            Route::get('permission', [RoleController::class, 'indexPermission']);
            Route::apiResource('roles', RoleController::class);
        });

        Route::apiResource('users', UserController::class)
            ->middleware('permission:program users')
            ->middlewareFor('store', 'permission:users create')
            ->middlewareFor('update', 'permission:users edit')
            ->middlewareFor('destroy', 'permission:users delete');
        Route::apiResource('categories', CategoryController::class)
            ->middleware('permission:program categories')
            ->middlewareFor('store', 'permission:categories create')
            ->middlewareFor('update', 'permission:categories edit')
            ->middlewareFor('destroy', 'permission:categories delete');
        Route::apiResource('attributes', AttributeController::class)
            ->middleware('permission:program attributes')
            ->middlewareFor('store', 'permission:attributes create')
            ->middlewareFor('update', 'permission:attributes edit')
            ->middlewareFor('destroy', 'permission:attributes delete');
        Route::apiResource('typeServices', TypeServiceController::class)
            ->middleware('permission:program services')
            ->middlewareFor(['store', 'update', 'destroy'], 'permission:services manage');
        Route::apiResource('services', ServiceController::class)
            ->middleware('permission:program services')
            ->middlewareFor(['store', 'update', 'destroy'], 'permission:services manage');
        Route::apiResource('tourists', TouristPlaceController::class)
            ->middleware('permission:program tourists')
            ->middlewareFor('store', 'permission:tourists create')
            ->middlewareFor('update', 'permission:tourists edit')
            ->middlewareFor('destroy', 'permission:tourists delete');
        Route::apiResource('events', EventController::class)
            ->middleware('permission:program events')
            ->middlewareFor('store', 'permission:create events')
            ->middlewareFor('update', 'permission:edit events')
            ->middlewareFor('destroy', 'permission:delete events');
        Route::apiResource('event-categories', EventCategoryController::class)
            ->middleware('permission:program events')
            ->middlewareFor(['store', 'update', 'destroy'], 'permission:event categories manage');

        Route::middleware('permission:module setting')->group(function () {
            Route::get('get_departments', [DepartmentController::class, 'getDepartments']);
            Route::get('districts', [DepartmentController::class, 'getDistricts']);
            Route::get('get_categories', [CategoryController::class, 'getCategory']);
            Route::get('get_attribute', [AttributeController::class, 'getAttribute']);
        });

    });

    Route::get('get_places', [TouristPlaceController::class, 'getPlace']);
});
