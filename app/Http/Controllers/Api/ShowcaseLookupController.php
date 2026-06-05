<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhoneType;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ShowcaseLookupController extends Controller
{
    public function show(string $sku): JsonResponse
    {
        $showcase = PhoneType::query()
            ->where('sku', trim($sku))
            ->first(['id', 'sku', 'name']);

        if (! $showcase) {
            return response()->json([
                'success' => false,
                'message' => 'Etalase tidak ditemukan.',
                'etalase' => null,
                'items' => [],
            ], 404);
        }

        $items = Product::query()
            ->where('phone_type_id', $showcase->id)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['sku', 'name'])
            ->map(fn (Product $product): array => [
                'child_sku' => (string) $product->sku,
                'item_name' => (string) $product->name,
            ])
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'etalase' => [
                'sku' => (string) $showcase->sku,
                'name' => (string) $showcase->name,
            ],
            'items' => $items,
        ]);
    }
}

