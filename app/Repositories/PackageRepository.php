<?php

namespace App\Repositories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;

class PackageRepository
{
    public function findById(int $id): ?Package
    {
        return Package::find($id);
    }

    public function getAll(bool $activeOnly = false): Collection
    {
        $query = Package::ordered();

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    public function create(array $data): Package
    {
        return Package::create($data);
    }

    public function update(Package $package, array $data): bool
    {
        return $package->update($data);
    }

    public function delete(Package $package): bool
    {
        return $package->delete();
    }

    public function findByPaddlePriceId(string $paddlePriceId): ?Package
    {
        return Package::where('paddle_price_id', $paddlePriceId)->first();
    }

    public function findByProductId(string $productId): ?Package
    {
        return Package::where('product_id', $productId)->first();
    }
}
