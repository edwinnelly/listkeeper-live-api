@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            {{-- <div class="page-header">

            </div> --}}
            <!-- /Page Header -->

            <div class="super-admin-list-head">

                <div class="content container-fluid">
                    <!-- Page Header -->

                    <!-- /Page Header -->
                    <div class="row">

                        <div class="container d-flex justify-content-center align-items-center min-vh-10 px-3">
                            <div class="card delete-card border-0 shadow-lg rounded-4 p-4 position-relative">
                                <!-- Decorative animated background -->
                                <div class="delete-background position-absolute w-100 h-100 top-0 start-0 rounded-4"></div>

                                <!-- Icon & Title -->
                                <div class="text-center mb-4 z-1 position-relative">
                                    <div class="icon-circle bg-danger bg-opacity-10 text-danger mx-auto mb-3 pulse-soft">
                                        <i class="bi bi-person-x-fill fs-2"></i>
                                    </div>
                                    <h4 class="fw-bold text-danger">Delete User Account</h4>
                                    <p class="text-muted small mb-0">This action is <strong>permanent</strong> and cannot be
                                        undone.</p>
                                </div>

                                <!-- User Being Deleted -->
                                <div
                                    class="alert alert-light border border-danger-subtle text-center rounded-3 z-1 position-relative">
                                    <div class="small text-muted">You are about to delete:</div>
                                    <h5 class="text-danger fw-bold mb-0">{{ $fetchUser->name }}</h5>
                                </div>

                                <!-- Confirm Input -->

                                    <div class="form-floating mb-3">
                                        <input type="text" id="confirmDelete" name="confirm_delete"
                                            class="form-control border-danger" placeholder="Type DELETE to confirm"
                                            pattern="DELETE" required autocomplete="off">
                                        <label for="confirmDelete">Type <strong>"DELETE"</strong> to confirm</label>
                                        <div class="invalid-feedback">
                                            Please type "DELETE" exactly to confirm.
                                        </div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="d-flex flex-column flex-sm-row gap-2">
                                        <a href="../{{ 'employee' }}" class="btn btn-outline-secondary w-100">
                                            <i class="bi bi-arrow-left-short me-1"></i> Cancel
                                        </a>

                                        <form action="{{ route('users.account.destroy', Crypt::encrypt($fetchUser->id)) }}" method="POST"
                                            class="w-100">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" id="deleteBtn" class="btn btn-danger w-100" disabled>
                                                <i class="bi bi-trash-fill me-1"></i> Delete Permanently
                                            </button>
                                        </form>

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

    <!-- /Add Companies Modal -->
@endsection
