<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['product_master_id', 'name', 'category_id', 'brand_id', 'phone_type_id', 'product_note', 'is_visible_for_affiliator'];

    protected $casts = [
        'is_visible_for_affiliator' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(ProductMaster::class, 'product_master_id');
    }

    public function phoneType(): BelongsTo
    {
        return $this->belongsTo(PhoneType::class);
    }
}
