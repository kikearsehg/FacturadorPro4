<?php

namespace Modules\Item\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\Brand;
use Modules\Item\Http\Resources\BrandCollection;
use Modules\Item\Http\Resources\BrandResource;
use Modules\Item\Http\Requests\BrandRequest;

class BrandController extends Controller
{

    public function index()
    {
        return view('item::brands.index');
    }


    public function columns()
    {
        return [
            'name' => 'Nombre',
        ];
    }

    public function records(Request $request)
    {
        $records = Brand::where($request->column, 'like', "%{$request->value}%")
                            ->latest();

        return new BrandCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function record($id)
    {
        $record = Brand::findOrFail($id);

        return $record;
    }

    public function store(BrandRequest $request)
    {
        $id = $request->input('id');
        $brand = Brand::firstOrNew(['id' => $id]);
        $brand->fill($request->all());
        $brand->save();


        return [
            'success' => true,
            'message' => ($id)?'Marca editada con éxito':'Marca registrada con éxito',
            'data' => $brand
        ];

    }

    public function destroy($id)
    {
        try {

            $brand = Brand::findOrFail($id);
            $brand->delete();

            return [
                'success' => true,
                'message' => 'Marca eliminada con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => "La Marca esta siendo usada por otros registros, no puede eliminar"] : ['success' => false,'message' => "Error inesperado, no se pudo eliminar la Marca"];

        }

    }




}
