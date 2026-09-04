<x-admin-layout title="My Profile">
<main class="pc-container-edit">

    <x-admin.page-header title="My Profile" subtitle="Manage your own account details" />

    <x-admin.breadcrumb :items="[['label' => 'My Profile']]" />

    @include('admin.partials.alerts')

    <div class="card mb-4">
        <div class="card-header"><h5>Account Overview</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" value="{{ ucfirst($user->status) }}" disabled readonly>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header"><h5>Profile Information</h5></div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                        @error('avatar') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        @if ($user->avatar)
                            <div class="mt-2" style="position:relative; width:80px;">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($user->avatar) }}" width="80" height="80" class="rounded border" style="object-fit: cover;" alt="{{ $user->name }}">
                                <button
                                    type="submit"
                                    form="delete-admin-avatar"
                                    class="vmm-card-remove"
                                    title="Delete image"
                                    aria-label="Delete image"
                                    onclick="return confirm('Are you sure you want to delete your profile image?');"
                                >
                                    <i class="ph ph-x"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-floppy-disk me-1"></i>
                    Save Profile
                </button>
            </div>
        </div>
    </form>

    @if ($user->avatar)
        <form id="delete-admin-avatar" action="{{ route('admin.profile.avatar.destroy') }}" method="POST">
            @csrf
            @method('DELETE')
        </form>
    @endif

    <form action="{{ route('admin.profile.password') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header"><h5>Change Password</h5></div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6"></div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="ph ph-key me-1"></i>
                    Update Password
                </button>
            </div>
        </div>
    </form>

</main>
</x-admin-layout>
