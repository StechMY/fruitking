<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentStockRecord extends Model
{
    use HasFactory, DefaultDatetimeFormat;

    protected $fillable = [
        'agentstock_id',
        'stock_before',
        'quantity',
        'stock_after',
        'remarks', 'type', 'user_id'
    ];

    public function agentstock()
    {
        return $this->belongsTo(AgentStock::class, 'agentstock_id');
    }

    public function from()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
