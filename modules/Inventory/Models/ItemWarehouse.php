<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;

class ItemWarehouse extends ModelTenant
{ 
    protected $table = 'item_warehouse';

    protected $fillable = [
        'item_id',
        'warehouse_id', 
        'stock', 
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}