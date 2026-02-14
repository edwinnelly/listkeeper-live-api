@extends('inc.base')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            {{-- <div class="page-header"></div> --}}
            <!-- /Page Header -->

            <div class="row justify-content-center">
                <div class="col-xl-12">
                    <!-- Floating Form Container -->
                    <div class="floating-form-container shadow-lg">
                        <!-- Form Header -->
                        <div class="form-header text-center mb-4">
                            <h2 class="text-gradient"><i class="fas fa-user-edit me-2"></i>Edit Profile</h2>
                            <p class="text-muted">Update your personal information</p>
                        </div>

                        <!-- Edit Form -->
                        <form class="needs-validation" method="POST"
                            action="{{ route('account.updated', Crypt::encrypt(['id' => $user->id])) }}"
                            enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <!-- Left Column -->
                                <div class="col-lg-4">
                                    <!-- Profile Picture Upload -->
                                    <div class="card floating-card border-0 shadow-sm mb-4">
                                        <div class="card-body text-center">
                                            <div class="avatar-upload">
                                                <div class="avatar-preview">
                                                    <img id="imagePreview"
                                                        src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : asset('https://cdn-icons-png.flaticon.com/512/8608/8608769.png') }}"
                                                        class="rounded-circle shadow" alt="Profile Preview">
                                                </div>
                                                <div class="avatar-edit mt-3">
                                                    <input type="file" id="imageUpload" name="profile_photo"
                                                        accept=".png, .jpg, .jpeg" class="d-none">
                                                    <label for="imageUpload" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-camera me-2"></i>Change Photo
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Security Settings -->
                                    <div class="card floating-card border-0 shadow-sm">
                                        <div class="card-header bg-transparent border-0">
                                            <h5 class="mb-0">Security</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Current Password</label>
                                                <div class="input-group">
                                                    <input type="password" name="current_password" class="form-control"
                                                        placeholder="••••••••">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">New Password</label>
                                                <input type="password" name="password" class="form-control"
                                                    placeholder="••••••••">
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label">Confirm Password</label>
                                                <input type="password" name="password_confirmation" class="form-control"
                                                    placeholder="••••••••">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-lg-8">
                                    <div class="card floating-card border-0 shadow-sm">
                                        <div class="card-body">
                                            <h5 class="mb-4">Personal Information</h5>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Full Name</label>
                                                    <input type="text" name="name" class="form-control"
                                                        value="{{ old('name', $user->name) }}" required>
                                                    <div class="invalid-feedback">
                                                        Please provide your full name.
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control"
                                                        value="{{ old('email', $user->email) }}" required>
                                                    <div class="invalid-feedback">
                                                        Please provide a valid email.
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Phone Number</label>
                                                    <input type="tel" name="phone_number" class="form-control"
                                                        value="{{ old('phone_number', $user->phone_number) }}">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Date of Birth</label>
                                                    <input type="date" name="age" class="form-control"
                                                        value="{{ old('age', $user->age) }}">
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">Address</label>
                                                    <input type="text" name="address" class="form-control mb-2"
                                                        placeholder="Street" value="{{ old('address', $user->address) }}">
                                                    <div class="row g-2">
                                                        <div class="col-md-4">
                                                            <input type="text" name="city" class="form-control"
                                                                placeholder="City" value="{{ old('city', $user->city) }}">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" name="state" class="form-control"
                                                                placeholder="State"
                                                                value="{{ old('state', $user->state) }}">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" name="zip" class="form-control"
                                                                placeholder="ZIP"
                                                                value="{{ old('zip', $user->postal_code) }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">Update Emergency Contact</label>
                                                    <input type="text" name="emergency" class="form-control mb-2"
                                                        placeholder="Emergency Contact"
                                                        value="{{ old('emergency', $user->emergency) }}">
                                                    <div class="row g-2">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Change Roles</label>
                                                            <select class="form-select" name="role" required>
                                                                @php
                                                                    $roles = [
                                                                        'Admin',
                                                                        'Manager',
                                                                        'Inventory Clerk',
                                                                        'Salesperson',
                                                                        'Purchasing Officer',
                                                                        'Accountant',
                                                                        'Viewer / Auditor',
                                                                    ];
                                                                @endphp
                                                                <option disabled selected>Current: {{ $user->role }}
                                                                </option>
                                                                @foreach ($roles as $role)
                                                                    <option value="{{ $role }}"
                                                                        {{ old('role', $user->role) == $role ? 'selected' : '' }}>
                                                                        {{ $role }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Change Location</label>
                                                            <select class="form-select" name="locations" required>
                                                                <option value="">Choose Location</option>
                                                                @foreach ($locations as $location)
                                                                    <option selected value="{{ $location->id }}">
                                                                        {{ $location->location_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Suspend Account</label>
                                                            <select class="form-select" name="is_active" required>
                                                                <option value="1">No</option>
                                                                <option value="0">Yes</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Country</label>
                                                    <select class="form-select" name="country" required>
                                                        <option value="{{ $user->country }}">{{ $user->country }}
                                                        </option>
                                                        <option value="Afghanistan">Afghanistan</option>
                                                        <option value="Albania">Albania</option>
                                                        <option value="Algeria">Algeria</option>
                                                        <option value="Angola">Angola</option>
                                                        <option value="Argentina">Argentina</option>
                                                        <option value="Armenia">Armenia</option>
                                                        <option value="Australia">Australia</option>
                                                        <option value="Austria">Austria</option>
                                                        <option value="Bangladesh">Bangladesh</option>
                                                        <option value="Belgium">Belgium</option>
                                                        <option value="Benin">Benin</option>
                                                        <option value="Brazil">Brazil</option>
                                                        <option value="Cameroon">Cameroon</option>
                                                        <option value="Canada">Canada</option>
                                                        <option value="China">China</option>
                                                        <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                                                        <option value="Denmark">Denmark</option>
                                                        <option value="Egypt">Egypt</option>
                                                        <option value="Finland">Finland</option>
                                                        <option value="France">France</option>
                                                        <option value="Germany">Germany</option>
                                                        <option value="Ghana">Ghana</option>
                                                        <option value="India">India</option>
                                                        <option value="Indonesia">Indonesia</option>
                                                        <option value="Italy">Italy</option>
                                                        <option value="Japan">Japan</option>
                                                        <option value="Kenya">Kenya</option>
                                                        <option value="Mexico">Mexico</option>
                                                        <option value="Netherlands">Netherlands</option>
                                                        <option value="Nigeria">Nigeria</option>
                                                        <option value="Norway">Norway</option>
                                                        <option value="Pakistan">Pakistan</option>
                                                        <option value="Philippines">Philippines</option>
                                                        <option value="Poland">Poland</option>
                                                        <option value="Portugal">Portugal</option>
                                                        <option value="Russia">Russia</option>
                                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                                        <option value="South Africa">South Africa</option>
                                                        <option value="South Korea">South Korea</option>
                                                        <option value="Spain">Spain</option>
                                                        <option value="Sweden">Sweden</option>
                                                        <option value="Switzerland">Switzerland</option>
                                                        <option value="Tanzania">Tanzania</option>
                                                        <option value="Thailand">Thailand</option>
                                                        <option value="Turkey">Turkey</option>
                                                        <option value="Uganda">Uganda</option>
                                                        <option value="Ukraine">Ukraine</option>
                                                        <option value="United Arab Emirates">United Arab Emirates</option>
                                                        <option value="United Kingdom">United Kingdom</option>
                                                        <option value="United States">United States</option>
                                                        <option value="Vietnam">Vietnam</option>
                                                        <option value="Zambia">Zambia</option>
                                                        <option value="Zimbabwe">Zimbabwe</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Timezone</label>
                                                    <select class="form-select" name="timezone">
                                                        <option value="">Select timezone</option>
                                                        @foreach (timezone_identifiers_list() as $timezone)
                                                            <option value="{{ $timezone }}"
                                                                {{ old('timezone', $user->timezone) == $timezone ? 'selected' : '' }}>
                                                                {{ $timezone }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">About</label>
                                                    <textarea class="form-control" name="about" rows="3" placeholder="Tell us about yourself...">{{ old('about', $user->about) }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-footer bg-transparent border-0 pt-0">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" onclick="window.history.back();"
                                                    class="btn btn-outline-secondary rounded-pill px-4">
                                                    <i class="bi bi-arrow-left"></i> Back
                                                </button>

                                                <button type="submit" class="btn btn-primary rounded-pill px-4"
                                                    id="saveBtn">
                                                    <span id="saveIcon"><i class="fas fa-save me-2"></i></span> Save
                                                    Changes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Floating Form Styles */
        .floating-form-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .floating-card {
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
        }

        .floating-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .form-header h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .text-gradient {
            background: linear-gradient(135deg, #1f3290, #3a0ca3);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .avatar-upload {
            position: relative;
            max-width: 200px;
            margin: 0 auto;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
            margin: 0 auto;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }

        .btn {
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }

        .btn-primary:hover {
            background-color: #3a56d4;
            border-color: #3a56d4;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-outline-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.1);
        }
    </style>

    <script>
        document.querySelector('#saveBtn').addEventListener('click', function() {
            const icon = document.querySelector('#saveIcon');
            icon.innerHTML =
                `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>`;
        });

        // Image preview functionality
        document.getElementById('imageUpload').addEventListener('change', function(event) {
            const [file] = event.target.files;
            if (file) {
                document.getElementById('imagePreview').src = URL.createObjectURL(file);
            }
        });

        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
@endsection
