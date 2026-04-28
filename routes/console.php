<?php

use Illuminate\Foundation\Inspiring;
use App\Models\ProductMaster;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:cleanup-unused-masters {--dry-run : Hanya tampilkan ringkasan tanpa menghapus data}', function () {
    $unusedMasterIds = ProductMaster::query()
        ->doesntHave('variants')
        ->pluck('id');
    $unusedCount = $unusedMasterIds->count();

    if ($unusedCount === 0) {
        $this->info('Tidak ada product master orphan. Database sudah bersih.');

        return;
    }

    $sampleNames = ProductMaster::query()
        ->whereIn('id', $unusedMasterIds)
        ->orderBy('name')
        ->limit(15)
        ->pluck('name')
        ->map(fn ($name) => trim((string) $name))
        ->all();

    $this->warn("Ditemukan {$unusedCount} product master orphan (tidak punya data di tabel products).");
    $this->line('Contoh data: '.implode(', ', $sampleNames));

    if ((bool) $this->option('dry-run')) {
        $this->info('Mode dry-run aktif. Tidak ada data yang dihapus.');

        return;
    }

    $deletedPivot = 0;
    $deletedMasters = 0;
    DB::transaction(function () use ($unusedMasterIds, &$deletedPivot, &$deletedMasters) {
        $deletedPivot = DB::table('lcd_group_product_master')
            ->whereIn('product_master_id', $unusedMasterIds)
            ->delete();

        $deletedMasters = ProductMaster::query()
            ->whereIn('id', $unusedMasterIds)
            ->delete();
    });

    $this->info("Selesai. Pivot terhapus: {$deletedPivot}, product_masters terhapus: {$deletedMasters}.");
})->purpose('Hapus product master orphan yang tidak punya data products');
