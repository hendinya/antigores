<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhoneType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'antigores_size', 'camera_shape', 'shopping_link', 'masteran'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
