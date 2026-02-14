@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">

            </div>
            <!-- /Page Header -->

            <div class="super-admin-list-head">

                <div class="content container-fluid">
                    <!-- Sleek Page Header -->






                    <div class="row">
                        <div class="page-header bg-white border-bottom shadow-sm py-3">
                            <div class="container-fluid">
                                <div class="d-flex flex-wrap align-items-center justify-content-between">

                                    <!-- Title & Breadcrumb -->
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <h3 class="page-title mb-0 fs-4 fw-semibold text-dark">
                                            <i class="fas fa-user-edit text-primary me-2"></i> Edit User Profile
                                        </h3>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb mb-0 small text-muted">
                                                <li class="breadcrumb-item"><a href="#"
                                                        class="text-decoration-none text-muted">Dashboard</a></li>
                                                <li class="breadcrumb-item"><a href="#"
                                                        class="text-decoration-none text-muted">Users</a></li>
                                                <li class="breadcrumb-item active text-dark" aria-current="page">Edit
                                                    Profile
                                                </li>
                                            </ol>
                                        </nav>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">

                                        <!-- Refresh -->
                                        <button class="btn btn-light border shadow-sm btn-icon rounded-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Refresh"
                                            onclick="window.location.reload()">
                                            <i class="fe fe-refresh-ccw"></i>
                                        </button>

                                        <!-- Edit Profile -->
                                        <button class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                                            <i class="fas fa-edit me-2"></i>Edit Profile
                                        </button>

                                        <!-- Add User Button (if permitted) -->
                                        @auth
                                            {{-- @if (auth()->user()->hasPermission('users_create')) --}}
                                            <button class="btn btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
                                                data-bs-target="#add_companies">
                                                <i class="fa fa-plus-circle me-1"></i> Add User
                                            </button>
                                            {{-- @endif --}}
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="container-fluid">
                            <div class="row">
                                <!-- Sleek Profile Header -->
                                <div class="col-12">
                                    <div class="profile-header bg-white bg-gradient p-4 rounded-4 shadow-sm mb-4 position-relative overflow-hidden"
                                        style="background: linear-gradient(135deg, rgba(13,110,253,0.08), rgba(255,255,255,0));">

                                        <div class="d-flex align-items-center flex-wrap gap-3">

                                            <!-- Avatar with Soft Border -->
                                            <div class="position-relative">
                                                <img src="{{ asset('https://cdn-icons-png.flaticon.com/512/8608/8608769.png') }}"
                                                    class="rounded-circle border border-4 border-white shadow"
                                                    style="width: 90px; height: 90px; object-fit: cover;"
                                                    alt="Profile Image">
                                            </div>

                                            <!-- Profile Details -->
                                            <div>
                                                <h2 class="fw-semibold mb-1 text-dark d-flex align-items-center gap-2">
                                                    John Doe
                                                    <i class="fas fa-circle text-success small" title="Online"></i>
                                                </h2>
                                                <p class="text-muted mb-2 small">Sales Manager</p>

                                                <div class="d-flex flex-wrap gap-2">
                                                    <span
                                                        class="badge bg-primary bg-opacity-10 text-primary fw-medium px-3 py-1 rounded-pill d-flex align-items-center">
                                                        <i class="fas fa-map-marker-alt me-1"></i> New York, USA
                                                    </span>

                                                    <span
                                                        class="badge bg-info bg-opacity-10 text-info fw-medium px-3 py-1 rounded-pill d-flex align-items-center">
                                                        <i class="fas fa-user-tag me-1"></i> ID: USR-001245
                                                    </span>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>



                                <!-- Main Content -->
                                <div class="col-lg-4">
                                    <!-- Personal Info Card -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h5 class="mb-0">Personal Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-3 d-flex">
                                                    <div class="icon-container text-primary me-3">
                                                        <i class="fas fa-envelope"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-muted small">Email</h6>
                                                        <p class="mb-0">john.doe@example.com</p>
                                                    </div>
                                                </li>
                                                <li class="mb-3 d-flex">
                                                    <div class="icon-container text-primary me-3">
                                                        <i class="fas fa-phone"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-muted small">Phone</h6>
                                                        <p class="mb-0">+1 (555) 123-4567</p>
                                                    </div>
                                                </li>
                                                <li class="mb-3 d-flex">
                                                    <div class="icon-container text-primary me-3">
                                                        <i class="fas fa-birthday-cake"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-muted small">Date of Birth</h6>
                                                        <p class="mb-0">15 March 1985</p>
                                                    </div>
                                                </li>
                                                <li class="d-flex">
                                                    <div class="icon-container text-primary me-3">
                                                        <i class="fas fa-map-marked-alt"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-muted small">Address</h6>
                                                        <p class="mb-0">123 Main Street, Apt 4B<br>New York, NY 10001</p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Skills Card -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h5 class="mb-0">Skills & Expertise</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap gap-2">
                                                <span
                                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                                                    Sales Strategy
                                                </span>
                                                <span
                                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                                                    Client Relations
                                                </span>
                                                <span
                                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                                                    Market Analysis
                                                </span>
                                                <span
                                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                                                    CRM Software
                                                </span>
                                                <span
                                                    class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">
                                                    Team Leadership
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Emergency Contact -->
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h5 class="mb-0">Emergency Contact</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-start mb-3">
                                                <div
                                                    class="icon-container bg-danger bg-opacity-10 text-danger rounded-circle p-2 me-3">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">Jane Doe</h6>
                                                    <p class="text-muted small mb-1">Spouse</p>
                                                    <p class="mb-0">+1 (555) 987-6543</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-8">
                                    <!-- About Card -->
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h5 class="mb-0">About</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>Results-driven sales professional with over 10 years of experience in
                                                building client relationships and driving revenue growth. Proven track
                                                record of exceeding sales targets and developing high-performing teams.
                                                Passionate about understanding customer needs and delivering tailored
                                                solutions.</p>

                                            <div class="row mt-4">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div
                                                            class="icon-container bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                                            <i class="fas fa-briefcase"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">Department</h6>
                                                            <p class="text-muted mb-0">Sales & Marketing</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div
                                                            class="icon-container bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">Joined Date</h6>
                                                            <p class="text-muted mb-0">15 June 2018</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div
                                                            class="icon-container bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                                            <i class="fas fa-id-card"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">Employee ID</h6>
                                                            <p class="text-muted mb-0">EMP-20245</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div
                                                            class="icon-container bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                                            <i class="fas fa-user-tie"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">Reports To</h6>
                                                            <p class="text-muted mb-0">Sarah Johnson (Director of Sales)
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Performance Stats -->

                                    <!-- Recent Activity -->
                                    <div class="card border-0 shadow-sm">
                                        <div
                                            class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Recent Activity</h5>
                                            <a href="#"
                                                class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
                                        </div>
                                        <div class="card-body">
                                            <div class="timeline">
                                                <div class="timeline-item">
                                                    <div class="timeline-badge bg-success"></div>
                                                    <div class="timeline-content">
                                                        <div class="d-flex justify-content-between">
                                                            <h6 class="mb-1">Closed Deal with Acme Corp</h6>
                                                            <small class="text-muted">2 hours ago</small>
                                                        </div>
                                                        <p class="mb-0 small">Successfully negotiated and closed $250,000
                                                            annual contract.</p>
                                                    </div>
                                                </div>
                                                <div class="timeline-item">
                                                    <div class="timeline-badge bg-primary"></div>
                                                    <div class="timeline-content">
                                                        <div class="d-flex justify-content-between">
                                                            <h6 class="mb-1">Team Meeting</h6>
                                                            <small class="text-muted">Yesterday</small>
                                                        </div>
                                                        <p class="mb-0 small">Led quarterly sales strategy meeting with 12
                                                            team members.</p>
                                                    </div>
                                                </div>
                                                <div class="timeline-item">
                                                    <div class="timeline-badge bg-info"></div>
                                                    <div class="timeline-content">
                                                        <div class="d-flex justify-content-between">
                                                            <h6 class="mb-1">Completed Training</h6>
                                                            <small class="text-muted">3 days ago</small>
                                                        </div>
                                                        <p class="mb-0 small">Finished advanced CRM software certification
                                                            course.</p>
                                                    </div>
                                                </div>
                                                <div class="timeline-item">
                                                    <div class="timeline-badge bg-warning"></div>
                                                    <div class="timeline-content">
                                                        <div class="d-flex justify-content-between">
                                                            <h6 class="mb-1">Sales Target Exceeded</h6>
                                                            <small class="text-muted">1 week ago</small>
                                                        </div>
                                                        <p class="mb-0 small">Achieved 127% of monthly sales quota.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            .profile-header {
                                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                            }

                            .avatar {
                                object-fit: cover;
                            }

                            .avatar-xl {
                                width: 120px;
                                height: 120px;
                            }

                            .icon-container {
                                width: 40px;
                                height: 40px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }

                            .timeline {
                                position: relative;
                                padding-left: 30px;
                            }

                            .timeline-item {
                                position: relative;
                                padding-bottom: 20px;
                            }

                            .timeline-badge {
                                position: absolute;
                                left: -30px;
                                top: 0;
                                width: 20px;
                                height: 20px;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                z-index: 1;
                            }

                            .timeline-content {
                                position: relative;
                                padding-left: 15px;
                            }

                            .timeline-item:not(:last-child) .timeline-content:after {
                                content: '';
                                position: absolute;
                                left: -25px;
                                top: 20px;
                                bottom: 0;
                                width: 2px;
                                background-color: #e9ecef;
                            }
                        </style>

                    </div>


                </div>

            </div>

            <!-- Search Filter -->

            <!-- /Search Filter -->


        </div>
    </div>


    <!-- Add Companies Modal -->

    <!-- /Add Companies Modal -->
@endsection
