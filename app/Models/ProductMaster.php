<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductMaster extends Model
{
    use HasFactory;

    public const PRECISION_STATUS_PRESISI = 'presisi';
    public const PRECISION_STATUS_BELUM_PRESISI = 'belum_presisi';
    public const PRECISION_STATUS_BELUM_DITES = 'belum_dites';

    protected $fillable = ['name', 'brand_id', 'product_note', 'is_visible_for_affiliator', 'precision_status'];

    protected $casts = [
        'is_visible_for_affiliator' => 'boolean',
    ];

    public static function precisionStatusOptions(): array
    {
        return [
            self::PRECISION_STATUS_PRESISI => 'Presisi',
            self::PRECISION_STATUS_BELUM_PRESISI => 'Belum Presisi',
            self::PRECISION_STATUS_BELUM_DITES => 'Belum di tes',
        ];
    }

    public static function normalizePrecisionStatus(?string $value): string
    {
        $normalized = mb_strtolower(trim((string) $value));

        return match ($normalized) {
            self::PRECISION_STATUS_PRESISI => self::PRECISION_STATUS_PRESISI,
            self::PRECISION_STATUS_BELUM_PRESISI, 'belum presisi', 'belum-presisi' => self::PRECISION_STATUS_BELUM_PRESISI,
            self::PRECISION_STATUS_BELUM_DITES, 'belum di tes', 'belum dites', 'belum_di_tes', 'belum di-test' => self::PRECISION_STATUS_BELUM_DITES,
            default => self::PRECISION_STATUS_BELUM_DITES,
        };
    }

    public static function precisionStatusLabel(?string $value): string
    {
        $status = self::normalizePrecisionStatus($value);

        return self::precisionStatusOptions()[$status];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Product::class, 'product_master_id');
    }
}
