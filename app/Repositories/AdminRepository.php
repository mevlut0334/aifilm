<?php

namespace App\Repositories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Collection;

class AdminRepository
{
    public function findByEmail(string $email): ?Admin
    {
        return Admin::where('email', $email)->first();
    }

    public function create(array $data): Admin
    {
        return Admin::create($data);
    }

    public function findById(int $id): ?Admin
    {
        return Admin::find($id);
    }

    public function all(): Collection
    {
        return Admin::all();
    }

    public function delete(int $id): bool
    {
        return Admin::destroy($id) > 0;
    }
}
