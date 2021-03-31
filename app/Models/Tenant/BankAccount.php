<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;

use App\Models\Tenant\Catalogs\CurrencyType;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Modules\Finance\Models\GlobalPayment;

class BankAccount extends ModelTenant
{
    use UsesTenantConnection;

    public $timestamps = false;

    protected $fillable = [
        'bank_id',
        'description',
        'number',
        'currency_type_id',
        'cci',
        'status',
        'initial_balance',
        'show_in_documents'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

    public function global_destination()
    {
        return $this->morphMany(GlobalPayment::class, 'destination')->with(['payment']);
    }

    public function getShowInDocumentsAttribute($value)
    {
        return $value ? true : false;
    }
}
