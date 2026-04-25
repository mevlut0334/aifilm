<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SliderController extends Controller
{
    public function index(): View
    {
        $sliders = Slider::orderBy('order', 'asc')->paginate(10);
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create(): View
    {
        return view('admin.sliders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title_en' => 'required|string|max:255',
            'title_tr' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_tr' => 'required|string',
            'button_text_en' => 'nullable|string|max:255',
            'button_text_tr' => 'nullable|string|max:255',
            'button_link' => 'nullable|url|max:500',
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'order' => 'nullable|integer|min:0',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('sliders', 'public');
            $imageUrl = Storage::url($imagePath);
        }

        Slider::create([
            'image_url' => $imageUrl,
            'title' => [
                'en' => $validated['title_en'],
                'tr' => $validated['title_tr'],
            ],
            'description' => [
                'en' => $validated['description_en'],
                'tr' => $validated['description_tr'],
            ],
            'button_text' => [
                'en' => $validated['button_text_en'] ?? null,
                'tr' => $validated['button_text_tr'] ?? null,
            ],
            'button_link' => $validated['button_link'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider başarıyla oluşturuldu.');
    }

    public function edit(Slider $slider): View
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider): RedirectResponse
    {
        $validated = $request->validate([
            'title_en' => 'required|string|max:255',
            'title_tr' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_tr' => 'required|string',
            'button_text_en' => 'nullable|string|max:255',
            'button_text_tr' => 'nullable|string|max:255',
            'button_link' => 'nullable|url|max:500',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'order' => 'nullable|integer|min:0',
        ]);

        $imageUrl = $slider->image_url;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            // Delete old image if exists
            if ($slider->image_url && strpos($slider->image_url, '/storage/') !== false) {
                $oldPath = str_replace('/storage/', '', $slider->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            
            $newImagePath = $request->file('image')->store('sliders', 'public');
            $imageUrl = Storage::url($newImagePath);
        }

        $slider->update([
            'image_url' => $imageUrl,
            'title' => [
                'en' => $validated['title_en'],
                'tr' => $validated['title_tr'],
            ],
            'description' => [
                'en' => $validated['description_en'],
                'tr' => $validated['description_tr'],
            ],
            'button_text' => [
                'en' => $validated['button_text_en'] ?? null,
                'tr' => $validated['button_text_tr'] ?? null,
            ],
            'button_link' => $validated['button_link'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider başarıyla güncellendi.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        // Delete image if exists
        if ($slider->image_url && strpos($slider->image_url, '/storage/') !== false) {
            $imagePath = str_replace('/storage/', '', $slider->image_url);
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('success', 'Slider başarıyla silindi.');
    }

    public function toggleActive(Slider $slider): RedirectResponse
    {
        $slider->update(['is_active' => !$slider->is_active]);
        
        return redirect()->route('admin.sliders.index')->with('success', 'Slider durumu güncellendi.');
    }
}
