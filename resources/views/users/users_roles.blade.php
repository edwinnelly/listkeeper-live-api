@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="content-page-header">
                    <h5>User Management and Permissions</h5>
                    <div class="page-content">
                        <div class="list-btn">
                            <ul class="filter-list">
                                <li>
                                    <a class="btn-filters" href="" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                        title="Refresh"><span><i class="fe fe-refresh-ccw"></i></span></a>
                                </li>

                                @auth
                                    @if (auth()->user()->hasPermission('users_create'))
                                        <li>
                                            <a href="../{{ 'employee' }}" class="btn btn-filters">
                                                <i class="fa fa-user-circle me-2" aria-hidden="true"></i> Manage Users
                                            </a>
                                        </li>
                                    @endif
                                @endauth


                            </ul>
                        </div>
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
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 py-3">
                                    <h5 class="card-title mb-0 text-dark">Manage access levels with precision</h5>
                                </div>
                                <div class="card-body px-4 py-3">
                                    <form method="POST"
                                        action="{{ route('account.setPermssions', ['id' => $fetch_users_roles->id]) }}"
                                        class="permissions-form">
                                        @csrf
                                        @method('PUT')

                                        <!-- User Permissions Section -->
                                        <div class="permission-section mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="section-title mb-0 d-flex align-items-center">
                                                    <span class="material-icons-outlined me-2"
                                                        style="font-size: 1.1rem;">people</span>
                                                    User Permissions
                                                </h6>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input select-all" type="checkbox"
                                                        id="select-all-users" data-module="user">
                                                </div>
                                            </div>

                                            <div class="permission-items ps-4">
                                                <div class="form-check mb-3 d-flex align-items-center">
                                                    <input class="form-check-input permission-check me-3" type="checkbox"
                                                        name="users_read" value="1" id="user-view"
                                                        @if ($fetch_users_roles->users_read == 'yes') checked @endif>
                                                    <label class="form-check-label" for="user-view">View users</label>
                                                </div>

                                                <div class="form-check mb-3 d-flex align-items-center">
                                                    <input class="form-check-input permission-check me-3" type="checkbox"
                                                        name="users_create" value="1" id="user-add"
                                                        @if ($fetch_users_roles->users_create == 'yes') checked @endif>
                                                    <label class="form-check-label" for="user-add">Add new users</label>
                                                </div>

                                                <div class="form-check mb-3 d-flex align-items-center">
                                                    <input class="form-check-input permission-check me-3" type="checkbox"
                                                        name="users_update" value="1" id="user-edit"
                                                        @if ($fetch_users_roles->users_update == 'yes') checked @endif>
                                                    <label class="form-check-label" for="user-edit">Edit user
                                                        details</label>
                                                </div>

                                                <div class="form-check d-flex align-items-center">
                                                    <input class="form-check-input permission-check me-3" type="checkbox"
                                                        name="users_delete" value="1" id="user-delete"
                                                        @if ($fetch_users_roles->users_delete == 'yes') checked @endif>
                                                    <label class="form-check-label" for="user-delete">Remove users</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Locations Permissions Section -->
                                        <div class="permission-section mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="section-title mb-0 d-flex align-items-center">
                                                    <span class="material-icons-outlined me-2"
                                                        style="font-size: 1.1rem;">Locations</span>
                                                    Location Permissions
                                                </h6>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input select-all" type="checkbox"
                                                        id="select-all-roles" data-module="role">
                                                </div>
                                            </div>

                                            <div class="permission-items ps-4">
                                                <div class="form-check mb-3 d-flex align-items-center">
                                                    <input class="form-check-input permission-check me-3" type="checkbox"
                                                        name="locations_read" value="1" id="role-view"
                                                        @if ($fetch_users_roles->locations_read == 'yes') checked @endif>
                                                    <label class="form-check-label" for="role-view">View locations</label>
                                                </div>

                                                <div class="form-check mb-3 d-flex align-items-center">
                                                    <input class="form-check-input permission-check me-3" type="checkbox"
                                                        name="locations_create" value="1" id="role-add"
                                                        @if ($fetch_users_roles->locations_create == 'yes') checked @endif>
                                                    <label class="form-check-label" for="role-add">Create locations</label>
                                                </div>

                                                <div class="form-check mb-3 d-flex align-items-center">
                                                    <input class="form-check-input permission-check me-3" type="checkbox"
                                                        name="locations_update" value="1" id="role-edit"
                                                        @if ($fetch_users_roles->locations_update == 'yes') checked @endif>
                                                    <label class="form-check-label" for="role-edit">Modify locations</label>
                                                </div>

                                                <div class="form-check d-flex align-items-center">
                                                    <input class="form-check-input permission-check me-3" type="checkbox"
                                                        name="locations_delete" value="1" id="role-delete"
                                                        @if ($fetch_users_roles->locations_delete == 'yes') checked @endif>
                                                    <label class="form-check-label" for="role-delete">Delete locations</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between mt-4 pt-3">
                                            <a href="../{{ 'employee' }}" class="btn btn-light px-4">
                                                <i class="fas fa-arrow-left me-2"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary btn-filters">
                                                <i class="ph ph-floppy-disk"></i>
                                                Save Permissions
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>




                </div>

            </div>




        </div>
    </div>


    <!-- Add Companies Modal -->

    <!-- /Add Companies Modal -->
@endsection
