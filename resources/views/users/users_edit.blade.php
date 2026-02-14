@extends('inc.base')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Profile Header -->
            <div class="profile-header-container bg-white shadow-sm">
                <div class="container-fluid">
                    <div class="header-content d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="header-text">
                            <h1 class="page-title d-flex align-items-center gap-2 mb-2" style="font-size: 1.5rem;">
                                <i class="fas fa-user-edit text-primary"></i> User Profile
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="../dashboard">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="../employee">Users</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="header-actions d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-icon" data-bs-toggle="tooltip" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <a href="{{ route('users.account.update', Crypt::encrypt($user->id)) }}" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="profile-content container-fluid mt-4">
                <!-- Profile Hero Section -->
                <div class="profile-hero card shadow-sm">
                    <div class="card-body d-flex align-items-center gap-4 flex-wrap">
                        <div class="profile-avatar position-relative">
                            <img src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : asset('https://cdn-icons-png.flaticon.com/512/8608/8608769.png') }}"
                                 alt="Profile Image" class="avatar-img">
                            <span class="online-status"></span>
                        </div>
                        <div class="profile-info">
                            <div class="profile-title d-flex align-items-center gap-3 mb-3">
                                <h2 class="mb-0" style="font-size: 1rem;">{{ $user->name }}</h2>
                                <span class="role-badge badge bg-primary-subtle text-primary">{{ $user->role }}</span>
                            </div>
                            <div class="profile-meta d-flex gap-4 flex-wrap">
                                <div class="meta-item d-flex align-items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                    <span>{{ $user->country ?? 'Not specified' }}</span>
                                </div>
                                <div class="meta-item d-flex align-items-center gap-2">
                                    <i class="fas fa-id-card text-primary"></i>
                                    <span>ID: USR-{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Grid -->
                <div class="profile-grid mt-4">
                    <!-- Personal Info Card -->
                    <div class="profile-card card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">Personal Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-item d-flex gap-3 py-3 border-bottom">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">Email</label>
                                    <p class="mb-0">{{ $user->email ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex gap-3 py-3 border-bottom">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">Phone</label>
                                    <p class="mb-0">{{ $user->phone_number ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex gap-3 py-3 border-bottom">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-birthday-cake"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">Date of Birth</label>
                                    <p class="mb-0">{{ $user->age ? $user->age : 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-item d-flex gap-3 py-3">
                                <div class="info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <div class="info-content">
                                    <label class="text-uppercase small text-muted">Address</label>
                                    <p class="mb-0">{{ $user->address ?? 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About Card -->
                    <div class="profile-card wide-card card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title mb-0">About</h3>
                        </div>
                        <div class="card-body">
                            <p class="about-text text-muted mb-4">{{ $user->about ?? 'No information provided.' }}</p>
                            <div class="stats-grid">
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Roles</label>
                                        <p class="mb-0">{{ $user->role ?? 'Not assigned' }}</p>
                                    </div>
                                </div>
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Joined Date</label>
                                        <p class="mb-0">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Not available' }}</p>
                                    </div>
                                </div>
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Employee ID</label>
                                        <p class="mb-0">EMP-{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                                <div class="stat-item d-flex gap-3 p-3 bg-light rounded">
                                    <div class="stat-icon bg-primary-subtle text-primary">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="stat-content">
                                        <label class="text-uppercase small text-muted">Reports To</label>
                                        <p class="mb-0">{{ $user->reports_to ?? 'Host' }}</p>
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
@endsection
