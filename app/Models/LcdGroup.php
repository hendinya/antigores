<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LcdGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'note'];

    public function productMasters(): BelongsToMany
    {
        return $this->belongsToMany(ProductMaster::class, 'lcd_group_product_master')
            ->withTimestamps();
    }
}
