<?php

namespace Modules\LevelAccess\Models;

use App\Models\Tenant\User;
use App\Models\Tenant\Module;
use App\Models\Tenant\Person;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\CurrencyType;
use App\Models\Tenant\ModelTenant;

class ModuleLevel extends ModelTenant
{

    protected $fillable = [
        'value',
        'description',
        'module_id', 
    ];
 
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    public function module() {
        return $this->belongsTo(Module::class, 'module_id');
    }
 

}
