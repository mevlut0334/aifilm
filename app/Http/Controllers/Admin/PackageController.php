<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\PackageRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        private PackageRepository $packageRepository
    ) {}

    public function index(): View
    {
        $packages = $this->packageRepository->getAll();

        return view('admin.packages.index', [
            'packages' => $packages,
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token_amount' => 'required|integer|min:1',
            'title_en' => 'required|string|max:255',
            'title_tr' => 'nullable|string|max:255',
            'description_en' => 'required|string',
            'description_tr' => 'nullable|string',
            'paddle_price_id' => 'required|string|max:255',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $data = [
            'token_amount' => $validated['token_amount'],
            'title' => [
                'en' => $validated['title_en'],
                'tr' => $validated['title_tr'] ?? $validated['title_en'],
            ],
            'description' => [
                'en' => $validated['description_en'],
                'tr' => $validated['description_tr'] ?? $validated['description_en'],
            ],
            'paddle_price_id' => $validated['paddle_price_id'],
            'is_active' => $request->boolean('is_active', true),
            'order' => $validated['order'] ?? 0,
        ];

        $this->packageRepository->create($data);

        return redirect()->route('admin.packages.index')
            ->with('success', __('admin.packages.created_successfully'));
    }

    public function edit(int $id): View
    {
        $package = $this->packageRepository->findById($id);
        abort_if(! $package, 404);

        return view('admin.packages.edit', [
            'package' => $package,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $package = $this->packageRepository->findById($id);
        abort_if(! $package, 404);

        $validated = $request->validate([
            'token_amount' => 'required|integer|min:1',
            'title_en' => 'required|string|max:255',
            'title_tr' => 'nullable|string|max:255',
            'description_en' => 'required|string',
            'description_tr' => 'nullable|string',
            'paddle_price_id' => 'required|string|max:255',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $data = [
            'token_amount' => $validated['token_amount'],
            'title' => [
                'en' => $validated['title_en'],
                'tr' => $validated['title_tr'] ?? $validated['title_en'],
            ],
            'description' => [
                'en' => $validated['description_en'],
                'tr' => $validated['description_tr'] ?? $validated['description_en'],
            ],
            'paddle_price_id' => $validated['paddle_price_id'],
            'is_active' => $request->boolean('is_active'),
            'order' => $validated['order'] ?? $package->order,
        ];

        $this->packageRepository->update($package, $data);

        return redirect()->route('admin.packages.index')
            ->with('success', __('admin.packages.updated_successfully'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $package = $this->packageRepository->findById($id);
        abort_if(! $package, 404);

        $this->packageRepository->delete($package);

        return redirect()->route('admin.packages.index')
            ->with('success', __('admin.packages.deleted_successfully'));
    }
}
