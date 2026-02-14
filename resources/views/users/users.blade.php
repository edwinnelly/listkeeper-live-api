@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h5 class="mb-0 fw-semibold">User Accounts</h5>
                        <p class="text-muted mb-0">Manage your organization's users</p>
                    </div>

                    <div class="btn-group">
                        <!-- Refresh Button -->
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()"
                            data-bs-toggle="tooltip" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>

                        <!-- Export Button -->
                        <button class="btn btn-sm btn-outline-primary ms-2" onclick="downloadCSV()">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>

                        <!-- Add User Button (Conditional) -->
                        @auth
                            @if (auth()->user()->hasPermission('users_create'))
                                <button class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal"
                                    data-bs-target="#add_companies">
                                    <i class="fas fa-plus-circle me-1"></i> Add User
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>


            <!-- /Page Header -->

            <div class="super-admin-list-head">

                <div class="content container-fluid">
                    <!-- Page Header -->

                    <!-- /Page Header -->

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card-table">
                                <div class="card-body">

                                    <input type="text" id="searchInput" placeholder="Search for names..."
                                        onkeyup="searchTable()">


                                    <div class="table-responsive">
                                        <table class="table table-center table-hover datatable" id="myTable">
                                            <thead>
                                                <tr>
                                                    <th>SN</th>
                                                    <th>Name / Email Address</th>
                                                    <th>Phone Number</th>
                                                    {{-- <th>Account Type</th> --}}
                                                    <th>Role</th>
                                                    <th>Branches / Locations</th>
                                                    <th>Created on</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($staffUsers as $index => $staff)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <h2 class="table-avatar">

                                                                <a href="profile.html" class="avatar avatar-sm me-2">
                                                                    <img class="avatar-img rounded-circle"
                                                                        src="{{ $staff->profile_photo ? asset('storage/' . $staff->profile_photo) : asset('https://cdn-icons-png.flaticon.com/512/8608/8608769.png') }}"
                                                                        alt="User Image">
                                                                </a>
                                                                <a href="profile.html">{{ $staff->name }}
                                                                    <span>
                                                                        <span
                                                                            class="__cf_email__">{{ $staff->email }}</span>
                                                                    </span>
                                                                </a>
                                                            </h2>
                                                        </td>
                                                        <td>{{ $staff->phone_number }}</td>
                                                        {{-- <td>{{ $staff->account_types ?? 'N/A' }}</td> --}}
                                                        <td>{{ $staff->role }}</td>

                                                        <td>{{ $staff->location->location_name ?? 'No location' }}</td>

                                                        <td>{{ $staff->created_at->format('d M, Y, h:i:s A') }}</td>

                                                        <td>
                                                            <span
                                                                class="badge {{ $staff->is_active ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $staff->is_active ? 'Active' : 'Inactive' }}
                                                            </span>


                                                        </td>
                                                        <td class="d-flex align-items-center">
                                                            <div class="dropdown dropdown-action">
                                                                <a href="users.html#" class="btn-action-icon"
                                                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                                                        class="fas fa-ellipsis-v"></i></a>
                                                                <div class="dropdown-menu dropdown-menu-right"
                                                                    style="">
                                                                    <ul>

                                                                        @if (auth()->user()->hasPermission('users_update'))
                                                                            <li>
                                                                                <a class="dropdown-item"
                                                                                    href="{{ route('users.account.edit', Crypt::encrypt($staff->id)) }}"><i
                                                                                        class="far fa-edit me-2"></i>Edit
                                                                                    Account</a>
                                                                            </li>
                                                                        @endif


                                                                        @if (Auth()->user()->creator === 'Host')
                                                                            <li>
                                                                                <a class="dropdown-item"
                                                                                    href="{{ url('primary-roles/' . Crypt::encrypt($staff->id)) }}">
                                                                                    <i class="fa fa-shield me-1"></i>Edit
                                                                                    Roles
                                                                                </a>
                                                                            </li>
                                                                        @endif

                                                                        @if (auth()->user()->hasPermission('users_delete'))
                                                                            <li>
                                                                                <a class="dropdown-item"
                                                                                    href="{{ route('users.account.delete_users', Crypt::encrypt($staff->id)) }}"><i
                                                                                        class="far fa-trash-alt me-2"></i>Delete</a>
                                                                            </li>
                                                                        @endif


                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9">No staff users found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Search Filter -->

            <!-- /Search Filter -->


        </div>
    </div>


    <!-- Add Companies Modal -->
    <div class="modal custom-modal custom-lg-modal fade p-20" id="add_companies" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div class="form-header modal-header-title text-start mb-0">
                        <h4 class="mb-0">Add New User</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <form action="{{ route('users.account.store') }}" method="POST" class="needs-validation" novalidate
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body">
                                    <div class="form-section">
                                        {{-- <h5 class="form-title mb-3">Profile Picture</h5> --}}
                                        <div class="profile-picture d-flex align-items-center mb-4">
                                            <div class="profile-img me-3">
                                                <img id="blah" class="avatar img-thumbnail rounded-circle shadow-sm"
                                                    src="assets/img/profiles/profile.webp" alt="profile-img" width="80"
                                                    height="80">
                                            </div>
                                            <div class="img-upload">
                                                <label class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                                    <i class="fas fa-upload me-2"></i>Upload Photo
                                                    <input type="file" name="logo" hidden>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-lg-4">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control sleek-input" id="fullName"
                                                        name="name" placeholder="Full Name" required>
                                                    <label for="fullName">Full Name</label>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-floating">
                                                    <select class="form-select sleek-input" id="locationSelect"
                                                        name="locations" required>
                                                        <option value="">Choose Location</option>
                                                        @foreach ($locations as $location)
                                                            <option selected value="{{ $location->id }}">
                                                                {{ $location->location_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <label for="locationSelect">Choose Location</label>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control sleek-input" name="address"
                                                        placeholder="Enter address" required>
                                                    <label>Address</label>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-floating">
                                                    <input type="email" class="form-control sleek-input"
                                                        name="email_address" placeholder="Email" required>
                                                    <label>Email</label>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-floating">
                                                    <input type="text" class="form-control sleek-input"
                                                        name="phone_number" placeholder="Phone Number" required>
                                                    <label>Phone Number</label>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-floating">
                                                    <select class="form-select sleek-input" name="role" required>
                                                        <option disabled>Select Role</option>
                                                        <option value="Admin">Admin</option>
                                                        <option value="Manager">Manager</option>
                                                        <option value="Inventory Clerk">Inventory Clerk</option>
                                                        <option selected value="Salesperson">Salesperson</option>
                                                        <option value="Purchasing Officer">Purchasing Officer</option>
                                                        <option value="Accountant">Accountant</option>
                                                        <option value="Viewer / Auditor">Viewer / Auditor</option>
                                                    </select>
                                                    <label>Role</label>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-floating position-relative">
                                                    <input type="password" class="form-control sleek-input"
                                                        name="pwd" placeholder="Password" required>
                                                    <label>Password</label>
                                                    <span class="toggle-password"><i class="feather-eye"></i></span>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-floating position-relative">
                                                    <input type="password" class="form-control sleek-input"
                                                        name="confirmed" placeholder="Confirm Password" required>
                                                    <label>Confirm Password</label>
                                                    <span class="toggle-password"><i class="feather-eye"></i></span>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="form-floating">
                                                    <select class="form-select sleek-input" name="status" required>
                                                        <option disabled selected>Select Status</option>
                                                        <option selected>Active</option>
                                                        <option>Inactive</option>
                                                    </select>
                                                    <label>Status</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- /.form-section -->
                                </div>
                            </div>
                        </div>
                    </div>
                    @auth
                        <div class="modal-footer">
                            <button type="button" data-bs-dismiss="modal"
                                class="btn btn-outline-secondary rounded-pill">Cancel</button>
                            @if (auth()->user()->hasPermission('users_create'))
                                <button type="submit" class="btn btn-primary rounded-pill px-4">Add User</button>
                            @endif
                        </div>
                    @endauth

                </form>

            </div>
        </div>
    </div>
    <!-- /Add Companies Modal -->
@endsection
