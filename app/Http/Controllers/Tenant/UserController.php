<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\Module;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Establishment;
use App\Http\Requests\Tenant\UserRequest;
use App\Http\Resources\Tenant\UserResource;
use Modules\LevelAccess\Models\ModuleLevel;
use App\Http\Resources\Tenant\UserCollection;

class UserController extends Controller
{
    public function index()
    {
        return view('tenant.users.index');
    }

    public function record($id)
    {
        $record = new UserResource(User::findOrFail($id));

        return $record;
    }

    public function tables()
    {
        $modulesTenant = DB::connection('tenant')
            ->table('module_user')
            ->where('user_id', 1)
            ->select('module_id')
            ->get()
            ->pluck('module_id')
            ->all();

        $levelsTenant = DB::connection('tenant')
            ->table('module_level_user')
            ->where('user_id', 1)
            ->get()
            ->pluck('module_level_id')
            ->toArray();

        $modules = Module::with(['levels' => function ($query) use ($levelsTenant) {
            $query->whereIn('id', $levelsTenant);
        }])
            ->orderBy('order_menu')
            ->whereIn('id', $modulesTenant)
            ->get();
        $datasource = [];
        $children = array();

        for ($i = 0; $i < count($modules); $i++) {
            $hasChild = false;
            $expanded = false;
            $isChecked = true;
            if (count($modules[$i]->levels) > 0) :
                for ($j = 0; $j < count($modules[$i]->levels); $j++) {
                    array_push($datasource, ['id' => $modules[$i]->id . '-' . $modules[$i]->levels[$j]->id, 'pid' => $modules[$i]->id, 'name' => $modules[$i]->levels[$j]->description, 'isChecked' => $isChecked]);
                }
            endif;
            if (count($modules[$i]->levels) > 0) :
                $hasChild = true;
                $expanded = true;
                $isChecked = false;
            endif;
            array_push($datasource,  ['id' => $modules[$i]->id, 'name' => $modules[$i]->description, 'hasChild' => $hasChild, 'expanded' => $expanded, 'isChecked' => $isChecked]);
        }

        $establishments = Establishment::orderBy('description')->get();
        $types = [['type' => 'admin', 'description' => 'Administrador'], ['type' => 'seller', 'description' => 'Vendedor']];

        return compact('modules', 'establishments', 'types', 'datasource');
    }

    public function store(UserRequest $request)
    {
        $id = $request->input('id');

        if (!$id)  //VALIDAR EMAIL DISPONIBLE
        {
            $verify = User::where('email', $request->input('email'))->first();
            if ($verify) {
                return [
                    'success' => false,
                    'message' => 'Email no disponible. Ingrese otro Email'
                ];
            }
        }

        $user = User::firstOrNew(['id' => $id]);
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->establishment_id = $request->input('establishment_id');
        $user->type = $request->input('type');
        if (!$id) {
            $user->api_token = str_random(50);
            $user->password = bcrypt($request->input('password'));
        } elseif ($request->has('password')) {
            if (config('tenant.password_change')) {
                $user->password = bcrypt($request->input('password'));
            }
        }
        $user->save();

        $first_user = User::select('id')->first();

        if ($first_user->id != $id) {

            $modules = collect($request->input('modules'))->where('checked', true)->pluck('id')->toArray();

            $user->modules()->sync($modules);


            $levels = collect($request->input('levels'))->where('checked', true)->pluck('id')->toArray();
            $user->levels()->sync($levels);


        }

        // dd($user->getModules()->transform(function($row, $key) {
        //     return [
        //         'id' => $row->id,
        //         'privot_id' => $row->pivot,
        //         'privot_user' => $row->pivot->user_id,
        //         'privot_module' => $row->pivot->module_id,

        //     ];
        // }));

        return [
            'success' => true,
            'message' => ($id) ? 'Usuario actualizado' : 'Usuario registrado'
        ];
    }

    public function records()
    {
        $records = User::all();

        return new UserCollection($records);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return [
            'success' => true,
            'message' => 'Usuario eliminado con éxito'
        ];
    }
}
