<?php

namespace App\Repositories;

use App\Models\Template;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TemplateRepository
{
    public function findByUuid(string $uuid): ?Template
    {
        return Template::where('uuid', $uuid)->first();
    }

    public function getAll(): Collection
    {
        return Template::ordered()->get();
    }

    public function getActive(): Collection
    {
        return Template::active()->ordered()->get();
    }

    public function getActiveByOrientation(string $orientation): Collection
    {
        return Template::active()
            ->ordered()
            ->get()
            ->filter(fn ($template) => $template->hasVideoForOrientation($orientation));
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Template::ordered()->paginate($perPage);
    }

    public function paginateActive(int $perPage = 15): LengthAwarePaginator
    {
        return Template::active()->ordered()->paginate($perPage);
    }

    public function create(array $data): Template
    {
        return Template::create($data);
    }

    public function update(Template $template, array $data): bool
    {
        return $template->update($data);
    }

    public function delete(Template $template): bool
    {
        return $template->delete();
    }

    public function getMaxOrder(): int
    {
        return Template::max('order') ?? 0;
    }
}
