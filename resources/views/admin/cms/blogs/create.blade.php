<x-admin-layout title="Add Blog">

    <main class="pc-container-edit">

        <x-admin.page-header title="Add Blog" subtitle="Create a new blog article">
            <x-slot:actions>
                <a href="{{ route('admin.cms.blogs.index') }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[
            ['label' => 'CMS'],
            ['label' => 'Blogs', 'url' => route('admin.cms.blogs.index')],
            ['label' => 'Add Blog'],
        ]" />

        @include('admin.partials.alerts')

        <form action="{{ route('admin.cms.blogs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Blog Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Blog Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Benefits of Organic Food">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="benefits-of-organic-food">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="blog_category_id" class="form-select @error('blog_category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('blog_category_id') === $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="published" @selected(old('status') === 'published')>Published</option>
                                <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                                <option value="archived" @selected(old('status') === 'archived')>Archived</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" accept="image/*">
                            @error('featured_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Published Date</label>
                            <input type="date" name="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at') }}">
                            @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" @checked(old('is_featured'))>
                                <label class="form-check-label" for="is_featured">Featured Blog</label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Tags</label>
                            <div>
                                @foreach ($tags as $tag)
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="form-check-input" id="tag-{{ $tag->id }}" @checked(collect(old('tags', []))->contains($tag->id))>
                                        <label class="form-check-label" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('tags') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="3" placeholder="Short blog summary">{{ old('short_description') }}</textarea>
                            @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="8" placeholder="Write blog content here...">{{ old('content') }}</textarea>
                            @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>SEO Information</h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title') }}" placeholder="Meta title">
                            @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Canonical URL</label>
                            <input type="text" name="canonical_url" class="form-control @error('canonical_url') is-invalid @enderror" value="{{ old('canonical_url') }}" placeholder="https://example.com/blog/benefits-of-organic-food">
                            @error('canonical_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3" placeholder="Meta description">{{ old('meta_description') }}</textarea>
                            @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="{{ route('admin.cms.blogs.index') }}" class="btn btn-light">Cancel</a>

                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Create Blog
                </button>
            </div>

        </form>

    </main>

</x-admin-layout>