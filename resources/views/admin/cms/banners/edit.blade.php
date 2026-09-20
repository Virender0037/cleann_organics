@php $label = \App\Models\HomeBanner::SECTION_LABELS[$section]; @endphp
<x-admin-layout :title="'Edit — '.$label">
    <main class="pc-container-edit">
        <x-admin.page-header :title="'Edit — '.$label" subtitle="Changes appear on the homepage immediately" />
        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => $label, 'url' => route('admin.cms.banners.index', ['section' => $section])], ['label' => 'Edit']]" />
        @include('admin.partials.alerts')

        <form action="{{ route('admin.cms.banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.cms.banners._form')
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.cms.banners.index', ['section' => $section]) }}" class="btn btn-light">Cancel</a>
        </form>
    </main>
</x-admin-layout>
