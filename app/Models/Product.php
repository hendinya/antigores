<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['sku', 'product_master_id', 'name', 'category_id', 'brand_id', 'phone_type_id', 'product_note', 'is_visible_for_affiliator', 'precision_status'];

    protected $casts = [
        'is_visible_for_affiliator' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (trim((string) $product->sku) !== '') {
                return;
            }

            $product->sku = self::generateUniqueSku();
        });
    }

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

    public static function generateUniqueSku(): string
    {
        while (true) {
            $candidate = (string) now()->timestamp.random_int(100, 999);
            $existsInProducts = self::query()->where('sku', $candidate)->exists();
            $existsInPhoneTypes = PhoneType::query()->where('sku', $candidate)->exists();
            if (! $existsInProducts && ! $existsInPhoneTypes) {
                return $candidate;
            }

            usleep(20000);
        }
    }
}
