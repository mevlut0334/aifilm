<?php

namespace App\Repositories;

use App\Models\MobilePackage;
use Illuminate\Database\Eloquent\Collection;

class MobilePackageRepository
{
    public function findById(int $id): ?MobilePackage
    {
        return MobilePackage::find($id);
    }

    public function getAll(bool $activeOnly = false): Collection
    {
        $query = MobilePackage::ordered();

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    public function create(array $data): MobilePackage
    {
        return MobilePackage::create($data);
    }

    public function update(MobilePackage $package, array $data): bool
    {
        return $package->update($data);
    }

    public function delete(MobilePackage $package): bool
    {
        return $package->delete();
    }

    // iOS product ID ile bul
    public function findByIosProductId(string $productId): ?MobilePackage
    {
        return MobilePackage::where('ios_product_id', $productId)->first();
    }

    // Android product ID ile bul
    public function findByAndroidProductId(string $productId): ?MobilePackage
    {
        return MobilePackage::where('android_product_id', $productId)->first();
    }

    // Platforma göre aktif paketleri getir
    public function getActiveForIos(): Collection
    {
        return MobilePackage::active()->ordered()->forIos()->get();
    }

    public function getActiveForAndroid(): Collection
    {
        return MobilePackage::active()->ordered()->forAndroid()->get();
    }
}
