<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EventCategoryController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $query = EventCategory::query();
        return DataTables::of($query)->toJson();
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:event_categories,name'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $category = EventCategory::create($request->only('name'));
        return response()->json($category, 201);
    }

    public function show($id): JsonResponse
    {
        $category = EventCategory::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $category = EventCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:event_categories,name,' . $category->id
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $category->update($request->only('name'));
        return response()->json($category);
    }

    public function destroy($id): JsonResponse
    {
        $category = EventCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Categoría eliminada con éxito']);
    }
}
