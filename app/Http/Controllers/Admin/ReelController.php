<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveReelRequest;
use App\Models\Product;
use App\Models\Reel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReelController extends Controller
{
    public function index(): View
    {
        return view('admin.cms.reels.index', [
            'reels' => Reel::query()->with(['product:id,name', 'variant:id,sku,variant_name'])->ordered()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.cms.reels.create', $this->formOptions());
    }

    public function store(SaveReelRequest $request): RedirectResponse
    {
        Reel::create($this->payload($request));

        return redirect()->route('admin.cms.reels.index')->with('success', 'Reel created.');
    }

    public function edit(Reel $reel): View
    {
        return view('admin.cms.reels.edit', ['reel' => $reel] + $this->formOptions());
    }

    public function update(SaveReelRequest $request, Reel $reel): RedirectResponse
    {
        $reel->update($this->payload($request, $reel));

        return redirect()->route('admin.cms.reels.index')->with('success', 'Reel updated.');
    }

    public function destroy(Reel $reel): RedirectResponse
    {
        foreach ([$reel->thumbnail, $reel->video_path] as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        $reel->delete();

        return redirect()->route('admin.cms.reels.index')->with('success', 'Reel deleted.');
    }

    /** @return array<string, mixed> */
    private function payload(SaveReelRequest $request, ?Reel $existing = null): array
    {
        $data = $request->safe()->except(['thumbnail', 'video']);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('thumbnail')) {
            if ($existing?->thumbnail) {
                Storage::disk('public')->delete($existing->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')->store('reels/thumbnails', 'public');
        }

        if ($request->hasFile('video')) {
            if ($existing?->video_path) {
                Storage::disk('public')->delete($existing->video_path);
            }

            $data['video_path'] = $request->file('video')->store('reels/videos', 'public');
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'products' => Product::query()->with(['variants' => fn ($q) => $q->orderByDesc('is_default')->orderBy('sort_order')])->orderBy('name')->get(['id', 'name']),
        ];
    }
}
