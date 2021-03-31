<?php

namespace App\Models\Tenant;

class Configuration extends ModelTenant
{
    protected $fillable = [
        'send_auto',
        'formats',
        'cron',
        'stock',
        'locked_emission',
        'locked_users',
        'limit_documents',
        'sunat_alternate_server',
        'plan',
        'visual',
        'enable_whatsapp',
        'phone_whatsapp',
        'limit_users',
        'quantity_documents',
        'date_time_start',
        'locked_tenant',
        'compact_sidebar',
        'decimal_quantity',
        'amount_plastic_bag_taxes',
        'colums_grid_item',
        'options_pos',
        'edit_name_product',
        'restrict_receipt_date',
        'affectation_igv_type_id',
        'terms_condition',
        'cotizaction_finance',
        'quotation_allow_seller_generate_sale',
        'include_igv',
        'product_only_location',
        'header_image',
        'legend_footer',
        'default_document_type_03',
        'destination_sale',
        'terms_condition_sale',
        'login',
        'finances'
    ];

    protected $casts = [
      'quotation_allow_seller_generate_sale' => 'boolean'
    ];

    public function setPlanAttribute($value)
    {
        $this->attributes['plan'] = (is_null($value))?null:json_encode($value);
    }

    public function getPlanAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setVisualAttribute($value)
    {
        $this->attributes['visual'] = (is_null($value))?null:json_encode($value);
    }

    public function getVisualAttribute($value)
    {
        return (is_null($value))?null:(object) json_decode($value);
    }

    public function setLoginAttribute($value)
    {
        $this->attributes['login'] = is_null($value) ? null : json_encode($value);
    }

    public function getLoginAttribute($value)
    {
        return is_null($value) ? null : (object) json_decode($value);
    }

    public function setFinancesAttribute($value)
    {
        $this->attributes['finances'] = (is_null($value))?null:json_encode($value);
    }

    public function getFinancesAttribute($value)
    {
        return is_null($value) ? ['apply_arrears' => false, 'arrears_amount' => 0] : (object) json_decode($value);
    }
}
