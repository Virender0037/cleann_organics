<x-admin-layout title="Contact Messages">
    <main class="pc-container-edit">

        <x-admin.page-header title="Contact Messages" subtitle="View customer enquiries submitted from the website">
            <x-slot:actions>
                <button class="btn btn-light-secondary">
                    <i class="ph ph-download-simple me-1"></i>
                    Export
                </button>
            </x-slot:actions>
        </x-admin.page-header>

        <x-admin.breadcrumb :items="[['label' => 'CMS'], ['label' => 'Contact Messages']]" />

        @include('admin.partials.alerts')

        <x-admin.table-card title="Message List">
            <x-slot:toolbar>
                <x-admin.filter-toolbar action="{{ route('admin.cms.contact-messages.index') }}">
                    <div class="col-md-4">
                        <input type="text"
                               name="search"
                               class="form-control"
                               value="{{ request('search') }}"
                               placeholder="Search name, email or subject">
                    </div>

                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach (['unread' => 'Unread', 'read' => 'Read', 'replied' => 'Replied'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-slot:submit>
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </x-slot:submit>
                </x-admin.filter-toolbar>
            </x-slot:toolbar>

            <x-slot:head>
                <th>#</th>
                <th>Sender</th>
                <th>Phone</th>
                <th>Subject</th>
                <th>Received On</th>
                <th>Status</th>
                <th width="130">Action</th>
            </x-slot:head>

            @forelse ($messages as $message)
                <tr>
                    <td>{{ $message->id }}</td>

                    <td>
                        <strong>{{ $message->name }}</strong>
                        <br>
                        <small class="text-muted">{{ $message->email }}</small>
                    </td>

                    <td>{{ $message->phone ?? '—' }}</td>

                    <td>{{ $message->subject ?? '—' }}</td>

                    <td>
                        {{ $message->created_at->format('d M Y') }}
                        <br>
                        <small class="text-muted">{{ $message->created_at->format('h:i A') }}</small>
                    </td>

                    <td>
                        <x-admin.status-badge :status="$message->status" />
                    </td>

                    <td>
                        <a href="{{ route('admin.cms.contact-messages.show', $message) }}"
                           class="btn btn-sm btn-info"
                           title="View Message">
                            <i class="ph ph-eye"></i>
                        </a>

                        <form action="{{ route('admin.cms.contact-messages.destroy', $message) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Delete this message?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Message">
                                <i class="ph ph-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-admin.empty-state>No contact messages found.</x-admin.empty-state>
                    </td>
                </tr>
            @endforelse

            <x-slot:pagination>
                {{ $messages->links() }}
            </x-slot:pagination>
        </x-admin.table-card>

    </main>
</x-admin-layout>
