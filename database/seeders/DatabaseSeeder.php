<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PhoneType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminAccount = User::query()->where('role', 'admin')->first();
        if ($adminAccount) {
            $adminAccount->forceFill([
                'name' => 'Administrator',
                'email' => 'hendijadul@gmail.com',
                'password' => '$2y$10$idNHePU70QGq8edjqm9JNuUE0z4ZT8n02xgMtMR7rBAPpFnTQb.qi',
                'role' => 'admin',
                'email_verified_at' => now(),
            ])->save();
        } else {
            User::query()->create([
                'name' => 'Administrator',
                'email' => 'hendijadul@gmail.com',
                'password' => '$2y$10$idNHePU70QGq8edjqm9JNuUE0z4ZT8n02xgMtMR7rBAPpFnTQb.qi',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }

        User::query()->updateOrCreate(['email' => 'member@antigores.test'], [
            'name' => 'Member',
            'email' => 'member@antigores.test',
            'password' => Hash::make('password123'),
            'role' => 'member',
            'email_verified_at' => now(),
        ]);

        Category::query()->insertOrIgnore([
            ['name' => 'Kaca Bening', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Privacy', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $clearCategory = Category::query()->where('name', 'Kaca Bening')->first();
        $privacyCategory = Category::query()->where('name', 'Privacy')->first();

        if ($clearCategory && $privacyCategory) {
            Brand::query()->updateOrCreate(
                ['name' => 'Samsung', 'category_id' => $clearCategory->id],
                ['name' => 'Samsung', 'category_id' => $clearCategory->id]
            );
            Brand::query()->updateOrCreate(
                ['name' => 'Xiaomi', 'category_id' => $clearCategory->id],
                ['name' => 'Xiaomi', 'category_id' => $clearCategory->id]
            );
            Brand::query()->updateOrCreate(
                ['name' => 'Samsung', 'category_id' => $privacyCategory->id],
                ['name' => 'Samsung', 'category_id' => $privacyCategory->id]
            );
            Brand::query()->updateOrCreate(
                ['name' => 'Xiaomi', 'category_id' => $privacyCategory->id],
                ['name' => 'Xiaomi', 'category_id' => $privacyCategory->id]
            );
        }

        PhoneType::query()->insertOrIgnore([
            ['name' => 'Etalase Depan', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Etalase Tengah', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $category = Category::query()->first();
        $brand = $category
            ? Brand::query()->where('category_id', $category->id)->first()
            : null;
        $phoneType = PhoneType::query()->first();

        if ($category && $brand && $phoneType) {
            Product::query()->updateOrCreate(['name' => 'Antigores Premium', 'phone_type_id' => $phoneType->id], [
                'name' => 'Antigores Premium',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'phone_type_id' => $phoneType->id,
            ]);
        }
    }
}
