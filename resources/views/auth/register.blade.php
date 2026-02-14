@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center py-4" style="min-height: 100vh;">
    <div class="col-11 col-sm-10 col-md-6 col-lg-5 col-xl-6">
        <div class="card sleek-card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="text-center pt-4">
                <a href="/" class="d-inline-block transition-all hover-scale">
                    <img src="{{ asset('assets/img/mylogo.svg') }}" alt="Company Logo" style="height: 40px;">
                </a>
            </div>

            <div class="card-header bg-transparent text-center border-0 pt-2 pb-0">
                <small class="text-muted">{{ __('Create your account') }}</small>
            </div>

            <div class="card-body px-4 pt-0 pb-4">
                <form method="POST" action="{{ route('register') }}" class="needs-validation" novalidate>
                    @csrf

                    <!-- Name Field with Floating Label -->
                    <div class="form-floating mb-3">
                        <input id="name" type="text"
                            class="form-control sleek-input @error('name') is-invalid @enderror"
                            name="name" value="{{ old('name') }}"
                            placeholder=" " required autofocus>
                        <label for="name" class="small text-muted">{{ __('Full Name') }}</label>
                         <div class="focus-indicator"></div>
                        @error('name')
                            <div class="invalid-feedback d-flex align-items-center mt-1">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Email Field with Floating Label -->
                    <div class="form-floating mb-3">
                        <input id="email" type="email"
                            class="form-control sleek-input @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}"
                            placeholder=" " required>
                        <label for="email" class="small text-muted">{{ __('Email Address') }}</label>
                         <div class="focus-indicator"></div>
                        @error('email')
                            <div class="invalid-feedback d-flex align-items-center mt-1">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Password Field with Floating Label -->
                    <div class="form-floating mb-3 position-relative">
                        <input id="password" type="password"
                            class="form-control sleek-input @error('password') is-invalid @enderror"
                            name="password" placeholder=" " required>
                        <label for="password" class="small text-muted">{{ __('Password') }}</label>
                         <div class="focus-indicator"></div>
                        <button type="button" class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-toggle" style="z-index: 5;">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback d-flex align-items-center mt-1">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Confirm Password Field with Floating Label -->
                    <div class="form-floating mb-4 position-relative">
                        <input id="password-confirm" type="password"
                            class="form-control sleek-input"
                            name="password_confirmation" placeholder=" " required>
                        <label for="password-confirm" class="small text-muted">{{ __('Confirm Password') }}</label>
                         <div class="focus-indicator"></div>
                        <button type="button" class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-toggle" style="z-index: 5;">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                        <label class="form-check-label small text-muted" for="terms">
                            {{ __('I agree to the') }} <a href="#" class="text-primary text-decoration-none hover-underline">{{ __('Terms') }}</a> {{ __('and') }} <a href="#" class="text-primary text-decoration-none hover-underline">{{ __('Privacy Policy') }}</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 rounded-3 fw-semibold border-0 overflow-hidden">
                            {{ __('Create Account') }}
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center small text-muted">
                        {{ __('Already have an account?') }}
                        <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-none ms-1 hover-underline">
                            {{ __('Sign in') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Style (matches your login page with floating label adjustments) -->
<style>
    .sleek-card {
        backdrop-filter: blur(12px);
        background-color: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
    }

    .sleek-card:hover {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    /* Floating Label Adjustments */
    .form-floating>label {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }

    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label {
        transform: scale(0.85) translateY(-1.5rem) translateX(0.15rem);
        opacity: 0.5;
    }

    /* Input Styles */
    .sleek-input {
        border: none;
        border-bottom: 1.5px solid #e0e0e0;
        border-radius: 0;
        padding: 0.5rem 0.75rem;
        background: transparent;
        box-shadow: none !important;
    }

    .sleek-input:focus {
        border-color: transparent;
        box-shadow: none;
    }

    /* Focus Indicator Animation */
    .focus-indicator {
        height: 2px;
        background: linear-gradient(90deg, #6366f1, #a855f7);
        width: 0;
        transition: width 0.3s ease;
        margin-top: -1px;
    }

    .sleek-input:focus ~ .focus-indicator {
        width: 100%;
    }

    /* Button Effects */
    .btn-primary {
        background-color: #000;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .btn-hover-effect {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, #333, #222);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 0;
    }

    .btn-primary:hover .btn-hover-effect {
        opacity: 1;
    }

    /* Link Underline Effects */
    .hover-underline {
        position: relative;
    }

    .hover-underline::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 1px;
        background-color: currentColor;
        transition: width 0.3s ease;
    }

    .hover-underline:hover::after {
        width: 100%;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-in {
        animation: fadeIn 0.3s ease forwards;
    }

    .hover-scale {
        transition: transform 0.3s ease;
    }

    .hover-scale:hover {
        transform: scale(1.05);
    }

    /* Responsive Adjustments */
    @media (max-width: 576px) {
        .sleek-card {
            border-radius: 1rem;
            backdrop-filter: blur(8px);
        }
    }
</style>

<!-- Password Toggle Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Password toggle functionality
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.closest('.form-floating').querySelector('input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye-fill');
                    icon.classList.add('bi-eye-slash-fill');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash-fill');
                    icon.classList.add('bi-eye-fill');
                }
            });
        });

        // Add animation to form elements
        const formElements = document.querySelectorAll('.form-floating, .form-check, .d-grid, .text-center');
        formElements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(10px)';
            el.style.animation = `fadeIn 0.3s ease forwards ${index * 0.1}s`;
        });

        // Animation definition
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);
    });
</script>
@endsection
