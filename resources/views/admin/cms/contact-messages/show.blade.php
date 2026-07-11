<x-admin-layout title="Contact Message Details">
    <main class="pc-container-edit">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Contact Message Details</h4>
                <p class="text-muted mb-0">Review customer enquiry and update status</p>
            </div>

            <a href="{{ route('admin.cms.contact-messages.index') }}" class="btn btn-light">
                <i class="ph ph-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="mx-2">›</span>
            <span>CMS</span>
            <span class="mx-2">›</span>
            <a href="{{ route('admin.cms.contact-messages.index') }}">Contact Messages</a>
            <span class="mx-2">›</span>
            <span>Message Details</span>
        </div>

        <div class="row">

            <div class="col-lg-8">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Message</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Subject:</strong> {{ $message->subject ?? '—' }}</p>

                        <hr>

                        <p class="mb-0" style="white-space: pre-line;">{{ $message->message }}</p>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Sender Information</h5>
                    </div>

                    <div class="card-body">
                        <p class="mb-2"><strong>Name:</strong> {{ $message->name }}</p>
                        <p class="mb-2"><strong>Email:</strong> {{ $message->email }}</p>
                        <p class="mb-2"><strong>Phone:</strong> {{ $message->phone ?? '—' }}</p>
                        <p class="mb-0"><strong>Received:</strong> {{ $message->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Status</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.cms.contact-messages.update-status', $message) }}">
                            @csrf
                            @method('PUT')

                            <select name="status" class="form-select mb-3">
                                @foreach (['unread' => 'Unread', 'read' => 'Read', 'replied' => 'Replied'] as $value => $label)
                                    <option value="{{ $value }}" @selected($message->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ph ph-floppy-disk me-1"></i>
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>

                    <div class="card-body d-grid gap-2">
                        <form action="{{ route('admin.cms.contact-messages.destroy', $message) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this message?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light-danger w-100">
                                <i class="ph ph-trash me-1"></i>
                                Delete Message
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>

    </main>
</x-admin-layout>
