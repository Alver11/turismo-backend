<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use App\Models\Image;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    /**
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $category =  Category::query();
        $query = $category->with('images');
        return DataTables::of($query)->toJson();
    }

    public function store(Request $request): JsonResponse
    {
        // Decodificar el JSON de los datos
        $data = json_decode($request->input('data'), true);

        // Validar los datos
        $validator = Validator::make($data, [
            'name' => 'required|unique:categories,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Guardar la categoría
        $category = new Category();
        $category->name = $data['name'];
        $category->save();

        // Manejar la imagen si se ha enviado
        if ($request->hasFile('image') && $request->file('image') !== 'null') {
            $imageFile = $request->file('image');
            $randomName = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = $request->file('image')->store('images/categories');
            $image = new Image([
                'name' => $randomName,
                'file_path' => $imagePath
            ]);
            $category->images()->save($image);
        }

        return response()->json(['message' => 'Categoría creada con éxito', 'category' => $category], 201);
    }

    public function show($id): Model|Collection|Builder|array|null
    {
        return Category::with('images')->findOrFail($id);
    }
    public function update(CategoryUpdateRequest $request, $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);
            $data = json_decode($request->input('data'), true);
            $category->name = $data['name'];
            $category->save();
            $existingImage = $category->images()->first();

            if($existingImage){
                if($existingImage->name != $data['imageName']) {
                    // Manejar la actualización o eliminación de la imagen
                    if ($request->hasFile('image') && $request->file('image') !== 'null') {
                        // Eliminar la imagen anterior si existe
                        $existingImage = $category->images()->first();
                        if ($existingImage) {
                            Storage::delete($existingImage->file_path);
                            $existingImage->delete();
                        }
                        // Guardar la nueva imagen y asociarla con la categoría
                        $imagePath = $request->file('image')->store('images/categories' );
                        $image = new Image(['file_path' => $imagePath]);
                        $category->images()->save($image);
                    }
                    elseif ($request->input('image') === 'null') {
                        // Si se proporciona 'null', eliminar la imagen asociada
                        $existingImage = $category->images()->first();
                        if ($existingImage) {
                            Storage::delete($existingImage->file_path);
                            $existingImage->delete();
                        }
                    }
                }
            }else{
                if ($request->hasFile('image') && $request->file('image') !== 'null') {
                    // Guardar la nueva imagen y asociarla con la categoría
                    $imagePath = $request->file('image')->store('images/categories' );
                    $image = new Image(['file_path' => $imagePath]);
                    $category->images()->save($image);
                }
            }

            return response()->json(['message' => 'Categoría actualizada con éxito', 'category' => $category], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocurrió un error al actualizar la categoría'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $producer = Category::findOrFail($id);
            $producer->delete();
            return response()->json(['message' => 'Eliminado con éxito']);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al Eliminar la Categoria']);
        }
    }

    public function getCategory(): Collection|array
    {
        return Category::get();
    }
}
