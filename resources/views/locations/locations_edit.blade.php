@extends('inc.base')

@section('content')
    <div class="page-wrapper">
        <div class="profile-header-container bg-white shadow-sm">

                <div class="container-fluid">
                    <div class="header-content d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="header-text">
                            <h3 class="page-title d-flex align-items-center gap-2 mb-2" style="font-size: 1.4rem;">
                                <i class="fas fa-user-edit text-primary"></i> Business Locations
                            </h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a
                                            href="{{ route('location.accounts.index') }}">Locations</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Edit Locations</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="header-actions d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-icon" data-bs-toggle="tooltip" title="Refresh"
                                style="font-size: 0.8rem;">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            @auth
                                @if (auth()->user()->hasPermission('users_create'))
                                    <button class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal"
                                        data-bs-target="#add_companies">
                                        <i class="fas fa-edit me-1"></i> Edit Location
                                    </button>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        <div class="content container-fluid">
            <!-- Profile Header -->


            <!-- Main Content -->
            <div class="profile-content container-fluid mt-4">
                <!-- Profile Hero Section -->
                <div class="profile-hero card shadow-sm">
                    <div class="card-body d-flex align-items-center gap-4 flex-wrap">
                        <div class="profile-avatar position-relative">
                            <img src="{{ $businessLocation->business->logo ? asset('storage/' . $businessLocation->business->logo) : asset('https://cdn-icons-png.flaticon.com/512/8608/8608769.png') }}"
                                alt="Profile Image" class="avatar-img">
                            <span class="online-status"></span>
                        </div>
                        <div class="profile-info">
                            <div class="profile-title d-flex align-items-center gap-3 mb-3">
                                <h3 class="mb-0" style="font-size: 1rem;"> Location Name</h3>
                                <span
                                    class="role-badge badge bg-primary-subtle text-primary">{{ $businessLocation->location_name }}</span>
                            </div>
                            <div class="profile-meta d-flex gap-4 flex-wrap">
                                <div class="meta-item d-flex align-items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                    <span>Business Location: {{ $businessLocation->address }}</span>
                                </div>
                                {{-- <div class="meta-item d-flex align-items-center gap-2">
                                    <i class="fas fa-id-card text-primary"></i>
                                    <span>ID: USR-000001</span>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Grid -->
                <div class="profile-grid mt-4">
                    <!-- Personal Info Card -->
                    <div class="profile-card card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Location Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-item d-flex gap-3 py-3 border-bottom">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">Business Name</label>
                                    <p class="mb-0">{{ $businessLocation->business->business_name ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex gap-3 py-3 border-bottom">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">Address</label>
                                    <p class="mb-0">{{ $businessLocation->address ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex gap-3 py-3 border-bottom">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-city"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">City</label>
                                    <p class="mb-0">{{ $businessLocation->city ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex gap-3 py-3 border-bottom">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-flag"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">State</label>
                                    <p class="mb-0">{{ $businessLocation->state ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex gap-3 py-3 border-bottom">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">Country</label>
                                    <p class="mb-0">{{ $businessLocation->country ?: 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex gap-3 py-3">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-mail-bulk"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">Postal Code</label>
                                    <p class="mb-0">{{ $businessLocation->postal_code ?: 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About Card -->
                    <div class="profile-card wide-card card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Location Details</h3>
                        </div>
                        <div class="card-body">
                            <p class="about-text text-muted mb-4">Business location information provided.</p>
                            <div class="stats-grid">
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Location Status</label>
                                        <p class="mb-0">On</p>
                                    </div>
                                </div>
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Manager</label>
                                        <p class="mb-0">{{ $businessLocation->user->name ?: 'Not provided' }}</p>
                                    </div>
                                </div>
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Business ID</label>
                                        <p class="mb-0">Not provided</p>
                                    </div>
                                </div>
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Head Office</label>
                                        <p class="mb-0">Not provided</p>
                                    </div>
                                </div>
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Phone</label>
                                        <p class="mb-0">{{ $businessLocation->phone ?: 'Not provided' }}</p>
                                    </div>
                                </div>
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-toggle-on"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Status</label>
                                        <p class="mb-0">{{ $businessLocation->status ?: 'Not provided' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary: #4361ee;
            --primary-subtle: rgba(67, 97, 238, 0.1);
            --text-dark: #2b2d42;
            --text-muted: #6c757d;
            --border-radius: 0.75rem;
            --transition: all 0.3s ease;
        }

        .profile-container {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-dark);
        }

        /* Header Styles */
        .profile-header-container {
            padding: 1.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .breadcrumb {
            background: transparent;
            font-size: 0.875rem;
            padding: 0;
        }

        .breadcrumb-item a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--primary);
        }

        .breadcrumb-item.active {
            color: var(--primary);
        }

        .btn-icon {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: var(--transition);
        }

        .btn-icon:hover {
            background: var(--primary-subtle);
            color: var(--primary);
            transform: rotate(90deg);
        }

        .btn-primary {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: #3a56d4;
            box-shadow: 0 0.25rem 0.75rem rgba(67, 97, 238, 0.2);
            transform: translateY(-2px);
        }

        /* Profile Hero */
        .profile-hero {
            transition: var(--transition);
        }

        .avatar-img {
            width: 6rem;
            height: 6rem;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .avatar-img:hover {
            transform: scale(1.05);
        }

        .online-status {
            position: absolute;
            bottom: 0.5rem;
            right: 0.5rem;
            width: 0.875rem;
            height: 0.875rem;
            border-radius: 50%;
            background: #4cc9a0;
            border: 2px solid white;
        }

        .role-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
        }

        .profile-meta {
            font-size: 0.875rem;
        }

        .meta-item i {
            font-size: 1rem;
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: 1fr;
        }

        @media (min-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr 2fr;
            }
        }

        .profile-card {
            transition: var(--transition);
        }

        .profile-card:hover {
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }

        .card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        .info-item {
            transition: var(--transition);
        }

        .info-icon {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }

        .info-content label {
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .info-content p {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .about-text {
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
            gap: 1rem;
        }

        .stat-item {
            transition: var(--transition);
        }

        .stat-icon {
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }

        .stat-content label {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .stat-content p {
            font-size: 0.95rem;
            font-weight: 500;
        }
    </style>


    <div class="modal fade" id="add_companies" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content shadow-lg rounded-4">
                <div class="modal-header border-0 bg-light px-4 py-3">
                    <h5 class="modal-title fw-semibold text-dark">Edit Location Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('location.accounts.update', Crypt::encrypt($businessLocation->id)) }}" method="POST">
                           {{-- <form action="{{ route('location.accounts.update', Crypt::encrypt($businessLocation->id)) }}" method="POST"> --}}
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="location_name" class="form-label">Location Name</label>
                                    <input type="text" class="form-control" id="location_name" name="location_name"
                                        value="{{ old('location_name', $businessLocation->location_name) }}" required>
                                    @error('location_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        value="{{ old('phone', $businessLocation->phone) }}">
                                    @error('phone')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address"
                                        value="{{ old('address', $businessLocation->address) }}" required>
                                    @error('address')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        value="{{ old('city', $businessLocation->city) }}" required>
                                    @error('city')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="state" class="form-label">State/Province</label>
                                    <input type="text" class="form-control" id="state" name="state"
                                        value="{{ old('state', $businessLocation->state) }}" required>
                                    @error('state')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code"
                                        value="{{ old('postal_code', $businessLocation->postal_code) }}">
                                    @error('postal_code')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country"
                                        value="{{ old('country', $businessLocation->country) }}" required>
                                    @error('country')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active"
                                            {{ old('status', $businessLocation->status) == 'active' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="inactive"
                                            {{ old('status', $businessLocation->status) == 'inactive' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="manager_id" class="form-label">Manager</label>
                                    <select class="form-select" id="manager_id" name="manager_id" required>
                                        @foreach ($managers as $manager)
                                            <option value="{{ $manager->id }}"
                                                {{ old('manager_id', $businessLocation->manager_id) == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('manager_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('location.accounts.show', Crypt::encrypt($businessLocation->id)) }}"
                                    class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Location</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
