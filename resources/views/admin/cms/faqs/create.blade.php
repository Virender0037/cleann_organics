<x-admin-layout title="Add FAQ">
    <main class="pc-container-edit">

        <x-admin.page-header title="Add FAQ" subtitle="Create a new frequently asked question">
            <x-slot:actions>
                <a href="{{ route('admin.cms.faqs.index') }}" class="btn btn-light">
                    <i class="ph ph-arrow-left me-1"></i>
                    Back
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[
            ['label' => 'CMS'],
            ['label' => 'FAQs', 'url' => route('admin.cms.faqs.index')],
            ['label' => 'Add FAQ'],
        ]" />

        @include('admin.partials.alerts')

        <x-admin.form-card title="FAQ Information" action="{{ route('admin.cms.faqs.store') }}">
            <div class="row">

                <div class="col-md-12 mb-3">
                    <label class="form-label">Question <span class="text-danger">*</span></label>
                    <input type="text" name="question" class="form-control @error('question') is-invalid @enderror" value="{{ old('question') }}" placeholder="How can I track my order?">
                    @error('question') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', 0) }}">
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Answer <span class="text-danger">*</span></label>
                    <textarea name="answer" class="form-control @error('answer') is-invalid @enderror" rows="6" placeholder="Write answer here...">{{ old('answer') }}</textarea>
                    @error('answer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

            </div>

            <x-slot:actions>
                <a href="{{ route('admin.cms.faqs.index') }}" class="btn btn-light">Cancel</a>

                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Create FAQ
                </button>
            </x-slot:actions>
        </x-admin.form-card>

    </main>
</x-admin-layout>
