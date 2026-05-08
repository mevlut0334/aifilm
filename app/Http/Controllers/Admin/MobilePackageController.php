<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\MobilePackageRepository;
use Illuminate\Http\Request;

class MobilePackageController extends Controller
{
    public function __construct(
        private MobilePackageRepository $mobilePackageRepository
    ) {}

    public function index()
    {
        $packages = $this->mobilePackageRepository->getAll();

        return view('admin.mobile-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.mobile-packages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_en'           => 'required|string|max:255',
            'title_tr'           => 'nullable|string|max:255',
            'description_en'     => 'nullable|string',
            'description_tr'     => 'nullable|string',
            'token_amount'       => 'required|integer|min:1',
            'ios_product_id'     => 'required_without:android_product_id|nullable|string|max:255|unique:mobile_packages,ios_product_id',
            'android_product_id' => 'required_without:ios_product_id|nullable|string|max:255|unique:mobile_packages,android_product_id',
            'order'              => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $this->mobilePackageRepository->create([
            'title' => [
                'en' => $request->input('title_en'),
                'tr' => $request->input('title_tr') ?? $request->input('title_en'),
            ],
            'description' => [
                'en' => $request->input('description_en'),
                'tr' => $request->input('description_tr'),
            ],
            'token_amount'       => $request->input('token_amount'),
            'ios_product_id'     => $request->input('ios_product_id') ?: null,
            'android_product_id' => $request->input('android_product_id') ?: null,
            'order'              => $request->input('order', 0),
            'is_active'          => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.mobile-packages.index')
            ->with('success', 'Mobile package created successfully.');
    }

    public function edit(int $id)
    {
        $package = $this->mobilePackageRepository->findById($id);

        if (! $package) {
            abort(404);
        }

        return view('admin.mobile-packages.edit', compact('package'));
    }

    public function update(Request $request, int $id)
    {
        $package = $this->mobilePackageRepository->findById($id);

        if (! $package) {
            abort(404);
        }

        $request->validate([
            'title_en'           => 'required|string|max:255',
            'title_tr'           => 'nullable|string|max:255',
            'description_en'     => 'nullable|string',
            'description_tr'     => 'nullable|string',
            'token_amount'       => 'required|integer|min:1',
            'ios_product_id'     => 'required_without:android_product_id|nullable|string|max:255|unique:mobile_packages,ios_product_id,' . $id,
            'android_product_id' => 'required_without:ios_product_id|nullable|string|max:255|unique:mobile_packages,android_product_id,' . $id,
            'order'              => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $this->mobilePackageRepository->update($package, [
            'title' => [
                'en' => $request->input('title_en'),
                'tr' => $request->input('title_tr') ?? $request->input('title_en'),
            ],
            'description' => [
                'en' => $request->input('description_en'),
                'tr' => $request->input('description_tr'),
            ],
            'token_amount'       => $request->input('token_amount'),
            'ios_product_id'     => $request->input('ios_product_id') ?: null,
            'android_product_id' => $request->input('android_product_id') ?: null,
            'order'              => $request->input('order', 0),
            'is_active'          => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.mobile-packages.index')
            ->with('success', 'Mobile package updated successfully.');
    }

    public function destroy(int $id)
    {
        $package = $this->mobilePackageRepository->findById($id);

        if (! $package) {
            abort(404);
        }

        $this->mobilePackageRepository->delete($package);

        return redirect()
            ->route('admin.mobile-packages.index')
            ->with('success', 'Mobile package deleted successfully.');
    }
}
