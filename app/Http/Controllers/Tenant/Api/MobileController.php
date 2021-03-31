<?php

namespace App\Http\Controllers\Tenant\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Tenant\Item;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Mail\Tenant\DocumentEmail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Tenant\PersonRequest;
use Modules\Item\Http\Requests\ItemRequest;
use Modules\Dashboard\Helpers\DashboardData;
use Modules\Item\Http\Requests\ItemUpdateRequest;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Warehouse;
use Modules\Inventory\Models\ItemWarehouse;

class MobileController extends Controller
{

    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'No Autorizado'
            ];
        }

        $company = Company::active();

        $user = $request->user();
        return [
            'success' => true,
            'name' => $user->name,
            'email' => $user->email,
            'token' => $user->api_token,
            'ruc' => $company->number,
            'logo' => $company->logo
        ];

    }

    public function customers()
    {
        $customers = Person::whereType('customers')->orderBy('name')->take(20)->get()->transform(function($row) {
            return [
                'id' => $row->id,
                'description' => $row->number.' - '.$row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code,
                'address' => $row->address,
                'telephone' => $row->telephone,
                'country_id' => $row->country_id,
                'district_id' => $row->district_id,
                'email' => $row->email,
                'selected' => false
            ];
        });

        return [
            'success' => true,
            'data' => array('customers' => $customers)
        ];

    }

    public function tables()
    {
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        /*$customers = Person::whereType('customers')->orderBy('name')->take(20)->get()->transform(function($row) {
            return [
                'id' => $row->id,
                'description' => $row->number.' - '.$row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code
            ];
        });*/
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();

        $items = Item::with(['brand', 'category'])
                    ->whereWarehouse()
                    ->whereHasInternalId()
                    ->whereNotIsSet()
                    ->whereIsActive()
                    ->orderBy('description')
                    ->take(20)
                    ->get()
                    ->transform(function($row) use($warehouse){
                        $full_description = ($row->internal_id)?$row->internal_id.' - '.$row->description:$row->description;

                        return [
                            'id' => $row->id,
                            'item_id' => $row->id,
                            'name' => $row->name,
                            'full_description' => $full_description,
                            'description' => $row->description,
                            'currency_type_id' => $row->currency_type_id,
                            'internal_id' => $row->internal_id,
                            'item_code' => $row->item_code,
                            'currency_type_symbol' => $row->currency_type->symbol,
                            'sale_unit_price' => number_format( $row->sale_unit_price, 2),
                            'purchase_unit_price' => $row->purchase_unit_price,
                            'unit_type_id' => $row->unit_type_id,
                            'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                            'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                            'calculate_quantity' => (bool) $row->calculate_quantity,
                            'has_igv' => (bool) $row->has_igv,
                            'is_set' => (bool) $row->is_set,
                            'aux_quantity' => 1,
                    'brand' => $row->brand->name,
                    'category' => $row->brand->name,
                    'stock' => ItemWarehouse::where([['item_id', $row->id],['warehouse_id', $warehouse->id]])->first()->stock,
                    'image' => $row->image != "imagen-no-disponible.jpg" ? url("/storage/uploads/items/" . $row->image) : url("/logo/" . $row->image),

                        ];
                    });


        return [
            'success' => true,
            'data' => array('items' => $items, 'affectation_types' => $affectation_igv_types)
        ];

    }


    public function getSeries(){

        return Series::where('establishment_id', auth()->user()->establishment_id)
                    ->whereIn('document_type_id', ['01', '03'])
                    ->get()
                    ->transform(function($row) {
                        return [
                            'id' => $row->id,
                            'document_type_id' => $row->document_type_id,
                            'number' => $row->number
                        ];
                    });

    }

    public function document_email(Request $request)
    {
        $company = Company::active();
        $document = Document::find($request->id);
        $customer_email = $request->email;

        Mail::to($customer_email)->send(new DocumentEmail($company, $document));

        return [
            'success' => true,
            'message'=> 'Email enviado correctamente.'
        ];
    }


    public function item(ItemRequest $request)
    {
        $row = new Item();
        $row->item_type_id = '01';
        $row->amount_plastic_bag_taxes = Configuration::firstOrFail()->amount_plastic_bag_taxes;
        $row->fill($request->all());
        $row->image = 'imagen-no-disponible.jpg';
        $row->save();

        $full_description = ($row->internal_id)?$row->internal_id.' - '.$row->description:$row->description;

        return [
            'success' => true,
            'msg' => 'Producto registrado con éxito',
            'data' => (object)[
                'id' => $row->id,
                'item_id' => $row->id,
                'name' => $row->name,
                'full_description' => $full_description,
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'internal_id' => $row->internal_id,
                'item_code' => $row->item_code,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format( $row->sale_unit_price, 2),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'calculate_quantity' => (bool) $row->calculate_quantity,
                'has_igv' => (bool) $row->has_igv,
                'is_set' => (bool) $row->is_set,
                'aux_quantity' => 1,
            ],
        ];

    }

    public function person(PersonRequest $request)
    {
        $row = new Person();
		if ($request->department_id === '-') {
			$request->merge([
				'department_id' => null,
				'province_id'   => null,
				'district_id'   => null
			]);
		}
        $row->fill($request->all());
        $row->save();

        return [
            'success' => true,
            'msg' => ($request->type == 'customers') ? 'Cliente registrado con éxito' : 'Proveedor registrado con éxito',
            'data' => (object)[
                'id' => $row->id,
                'description' => $row->number.' - '.$row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code,
                'address' => $row->address,
                'email' => $row->email,
                'telephone' => $row->telephone,
                'country_id' => $row->country_id,
                'district_id' => $row->district_id,
                'selected' => false
            ]
        ];
    }

    public function searchItems(Request $request)
    {
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();

        $items = Item::where('description', 'like', "%{$request->input}%" )
                    ->orWhere('internal_id', 'like', "%{$request->input}%")
                    ->whereHasInternalId()
                    ->whereWarehouse()
                    ->whereNotIsSet()
                    ->whereIsActive()
                    ->orderBy('description')
                    ->get()
                    ->transform(function($row) use($warehouse){

                        $full_description = ($row->internal_id)?$row->internal_id.' - '.$row->description:$row->description;

                        return [
                            'id' => $row->id,
                            'item_id' => $row->id,
                            'name' => $row->name,
                            'full_description' => $full_description,
                            'description' => $row->description,
                            'currency_type_id' => $row->currency_type_id,
                            'internal_id' => $row->internal_id,
                            'item_code' => $row->item_code,
                            'currency_type_symbol' => $row->currency_type->symbol,
                            'sale_unit_price' => number_format( $row->sale_unit_price, 2),
                            'purchase_unit_price' => $row->purchase_unit_price,
                            'unit_type_id' => $row->unit_type_id,
                            'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                            'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                            'calculate_quantity' => (bool) $row->calculate_quantity,
                            'has_igv' => (bool) $row->has_igv,
                            'is_set' => (bool) $row->is_set,
                            'aux_quantity' => 1,
                    'brand' => $row->brand->name,
                    'category' => $row->brand->name,
                    'stock' => ItemWarehouse::where([['item_id', $row->id],['warehouse_id', $warehouse->id]])->first()->stock,
                    'image' => $row->image != "imagen-no-disponible.jpg" ? url("/storage/uploads/items/" . $row->image) : url("/logo/" . $row->image),
                            'warehouses' => collect($row->warehouses)->transform(function($row) {
                                return [
                                    'warehouse_description' => $row->warehouse->description,
                                    'stock' => $row->stock,
                                    'warehouse_id' => $row->warehouse_id,
                                ];
                            }),
                        ];
                    });

        return [
            'success' => true,
            'data' => array('items' => $items)
        ];
    }

    public function searchCustomers(Request $request)
    {

        $identity_document_type_id = $this->getIdentityDocumentTypeId($request->document_type_id);

        $customers = Person::where('name', 'like', "%{$request->input}%" )
                            ->orWhere('number','like', "%{$request->input}%")
                            ->whereType('customers')
                            ->whereIn('identity_document_type_id', $identity_document_type_id)
                            ->orderBy('name')
                            ->get()
                            ->transform(function($row) {
                                return [
                                    'id' => $row->id,
                                    'description' => $row->number.' - '.$row->name,
                                    'name' => $row->name,
                                    'number' => $row->number,
                                    'identity_document_type_id' => $row->identity_document_type_id,
                                    'identity_document_type_code' => $row->identity_document_type->code,
                                    'address' => $row->address,
                                    'telephone' => $row->telephone,
                                    'email' => $row->email,
                                    'country_id' => $row->country_id,
                                    'district_id' => $row->district_id,
                                    'selected' => false
                                ];
                            });

        return [
            'success' => true,
            'data' => array('customers' => $customers)
        ];
    }


    public function getIdentityDocumentTypeId($document_type_id){

        return ($document_type_id == '01') ? [6] : [1,4,6,7,0];

    }

    public function report()
    {
        $request = [
            'customer_id' => null,
            'date_end' => date('Y-m-d'),
            'date_start' => date('Y-m-d'),
            'enabled_expense' => null,
            'enabled_move_item' => false,
            'enabled_transaction_customer' => false,
            'establishment_id' => 1,
            'item_id' => null,
            'month_end' => date('Y-m'),
            'month_start' => date('Y-m'),
            'period' => 'month',
        ];

        return [
            'data' => (new DashboardData())->data_mobile($request)
        ];
    }

    public function updateItem(ItemUpdateRequest $request, $itemId)
    {
        $row = Item::findOrFail($itemId);

        $row->fill($request->only('internal_id', 'barcode', 'model', 'has_igv', 'description', 'sale_unit_price', 'stock_min', 'item_code'));
        $row->save();

        $full_description = ($row->internal_id)?$row->internal_id.' - '.$row->description:$row->description;

        return [
            'success' => true,
            'msg' => 'Producto editado con éxito',
            'data' => (object)[
                'id' => $row->id,
                'item_id' => $row->id,
                'name' => $row->name,
                'full_description' => $full_description,
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'internal_id' => $row->internal_id,
                'item_code' => $row->item_code,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format( $row->sale_unit_price, 2),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'calculate_quantity' => (bool) $row->calculate_quantity,
                'has_igv' => (bool) $row->has_igv,
                'is_set' => (bool) $row->is_set,
                'aux_quantity' => 1,
            ],
        ];
    }
}

