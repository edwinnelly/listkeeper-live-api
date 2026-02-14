@extends('inc.base')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="page-title">
                        <h5 class="mb-0 fw-semibold">Edit Product Category</h5>
                        <p class="text-muted mb-0">Update Category Details</p>
                    </div>
                    <a href="{{ route('products.category.index') }}" class="btn btn-outline-primary rounded-pill">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            <!-- /Page Header -->

            <!-- Edit Form Card -->
            <div class="profile-header-container bg-white shadow-sm">
                <div class="container-fluid">

                            <div class="card card-table my-4 mx-2" style="margin: 10px">
                                <div class="card-body">
                                    <form action="{{ route('products.edit.update', Crypt::encrypt($category->id)) }}"
                                        method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        <div class="form-section">
                                            <div class="row g-3">
                                                <!-- Category Name -->
                                                <div class="col-lg-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control sleek-input"
                                                            id="categoryName" name="name"
                                                            value="{{ old('name', $category->name) }}" required>
                                                        <label for="categoryName">Category Name</label>
                                                    </div>
                                                </div>

                                                <!-- Slug -->
                                                <div class="col-lg-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control sleek-input"
                                                            name="slug" value="{{ old('slug', $category->slug) }}"
                                                            required>
                                                        <label>Category Code</label>
                                                    </div>
                                                </div>

                                                <!-- Description -->
                                                <div class="col-lg-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control sleek-input" name="description" style="height: 100px">{{ old('description', $category->description) }}</textarea>
                                                        <label>Description</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="mt-4 text-end">
                                            <a href="{{ route('products.category.index') }}"
                                                class="btn btn-outline-secondary rounded-pill">Cancel</a>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                Update Category
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Edit Form Card -->


                </div>
            </div>
        @endsection
