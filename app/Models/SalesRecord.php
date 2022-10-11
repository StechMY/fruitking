<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesRecord extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    protected $fillable = [
        'user_id', 'products', 'total_sales', 'total_commission', 'type', 'remarks'
    ];

    protected $casts = [
        'products' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sales_records()
    {
        return $this->belongsTo(SalesRecord::class, 'id', 'id');
    }
}
