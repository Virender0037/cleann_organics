<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use App\Services\Admin\SpreadsheetImportReader;
use App\Services\CsvExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TagController extends Controller
{
    public function index(Request $request): View
    {
        $tags = Tag::withCount('products')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.catalog.tags.index', compact('tags'));
    }

    public function export(Request $request, CsvExporter $exporter): StreamedResponse
    {
        $tags = Tag::withCount('products')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderBy('name')
            ->lazy(200);

        $headers = ['id', 'name', 'slug', 'status', 'products_count'];

        $rows = $tags->map(fn (Tag $tag) => [
            $tag->id,
            $tag->name,
            $tag->slug,
            $tag->status,
            $tag->products_count,
        ]);

        return $exporter->stream('tags.csv', $headers, $rows);
    }

    public function importForm(): View
    {
        return view('admin.catalog.tags.import');
    }

    public function downloadTemplate(CsvExporter $exporter): StreamedResponse
    {
        $headers = ['name', 'slug', 'status'];

        $exampleRows = [
            ['Organic', 'organic', 'active'],
            ['Bestseller', '', 'active'],
        ];

        return $exporter->stream('tags-import-template.csv', $headers, $exampleRows);
    }

    public function import(Request $request, SpreadsheetImportReader $reader): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:2048'],
        ]);

        try {
            $parsed = $reader->parse($request->file('file'));
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Unable to read the uploaded file: '.$e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Tag import: failed to parse uploaded file.', ['exception' => $e]);

            return back()->with('error', 'Unable to read the uploaded file. Please check the file and try again.');
        }

        $header = $parsed['header'];
        $rows = $parsed['rows'];

        $missingColumns = array_diff(['name', 'status'], $header);

        if (! empty($missingColumns)) {
            return back()->with('error', 'Invalid file: missing required column(s): '.implode(', ', $missingColumns));
        }

        $success = 0;
        $skipped = [];
        $errors = [];
        $claimedSlugs = [];

        foreach ($rows as $entry) {
            $rowNum = $entry['row'];
            $data = $entry['data'];

            $name = $data['name'] ?? null;
            $status = $data['status'] ?? null;

            if (blank($name)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Missing required field: name'];

                continue;
            }

            if (! in_array($status, ['active', 'inactive'], true)) {
                $errors[] = ['row' => $rowNum, 'message' => "Invalid status '{$status}' (must be active or inactive)"];

                continue;
            }

            $slug = blank($data['slug'] ?? null) ? Str::slug($name) : Str::slug($data['slug']);

            if (blank($slug)) {
                $errors[] = ['row' => $rowNum, 'message' => 'Could not generate a valid slug from name'];

                continue;
            }

            if (isset($claimedSlugs[$slug]) || Tag::where('slug', $slug)->exists()) {
                $skipped[] = ['row' => $rowNum, 'slug' => $slug, 'message' => 'Duplicate slug'];

                continue;
            }

            try {
                Tag::create([
                    'name' => $name,
                    'slug' => $slug,
                    'status' => $status,
                ]);
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowNum, 'message' => 'Failed to create tag: '.$e->getMessage()];

                continue;
            }

            $claimedSlugs[$slug] = true;
            $success++;
        }

        return redirect()->route('admin.catalog.tags.import')
            ->with('import_results', [
                'success' => $success,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
    }

    public function create(): View
    {
        return view('admin.catalog.tags.create');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);

        Tag::create($data);

        return redirect()->route('admin.catalog.tags.index')->with('success', 'Tag created.');
    }

    public function edit(Tag $tag): View
    {
        return view('admin.catalog.tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);

        $tag->update($data);

        return redirect()->route('admin.catalog.tags.index')->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Tag deleted.');
    }
}
