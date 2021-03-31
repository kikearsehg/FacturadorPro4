<?php

namespace App\Models\System;

use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use UsesSystemConnection;

    protected $with = ['hostname','plan'];
    protected $fillable = [
        'hostname_id',
        'number',
        'name',
        'email',
        'token',
        'locked',
        'locked_emission',
        'locked_tenant',
        'locked_users',
        'plan_id',
        'start_billing_cycle'
    ];

    protected $casts = [
        'start_billing_cycle' => 'date',
    ];

    public function hostname()
    {
        return $this->belongsTo(Hostname::class)->with(['website']);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(ClientPayment::class);
    }

}
