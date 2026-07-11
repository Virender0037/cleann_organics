<x-admin-layout title="Contact Messages">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Contact Messages</h4>
                <p class="text-muted mb-0">View customer enquiries submitted from the website</p>
            </div>

            <button class="btn btn-light-secondary">
                <i class="ph ph-download-simple me-1"></i>
                Export
            </button>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <span>Contact Messages</span>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Message List</h5>
            </div>

            <div class="card-body">

                <form method="GET" action="{{ route('admin.cms.contact-messages.index') }}">
                    <div class="row mb-4">
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

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Sender</th>
                                <th>Phone</th>
                                <th>Subject</th>
                                <th>Received On</th>
                                <th>Status</th>
                                <th width="130">Action</th>
                            </tr>
                        </thead>

                        <tbody>
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
                                        @php
                                            $statusBadge = match ($message->status) {
                                                'replied' => 'bg-success',
                                                'read' => 'bg-info',
                                                default => 'bg-warning text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusBadge }}">{{ ucfirst($message->status) }}</span>
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
                                    <td colspan="7" class="text-center text-muted">No contact messages found.</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    {{ $messages->links() }}
                </div>

            </div>
        </div>

    </main>
</x-admin-layout>
