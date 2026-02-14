@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center py-4" style="min-height: 100vh;">
        <div class="col-11 col-sm-10 col-md-6 col-lg-8 col-xl-6">
            <!-- Enhanced Card with Glass Morphism -->
            <div class="card sleek-card border-0 shadow-sm rounded-4 overflow-hidden">
                <!-- Animated Gradient Background Layer -->
                <div class="position-absolute w-100 h-100 top-0 start-0"
                    style="background: linear-gradient(135deg, rgba(99,102,241,0.03) 0%, rgba(168,85,247,0.03) 100%); z-index: -1;">
                </div>

                <!-- Logo Section with Subtle Animation -->
                <div class="text-center pt-4">
                    <a href="/" class="d-inline-block transition-all hover-scale">
                        <img src="{{ asset('assets/img/mylogo.svg') }}" alt="Company Logo" style="height: 40px;">
                    </a>
                </div>

                <div class="card-header bg-transparent text-center border-0 pt-2 pb-0">
                    <small class="text-muted">{{ __('Sign in to continue') }}</small>
                </div>

                <div class="card-body px-4 pt-0 pb-4">
                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                        @csrf

                        <!-- Floating Label Style Inputs -->
                        <div class="form-floating mb-3">
                            <input id="email" type="email"
                                class="form-control sleek-input @error('email') is-invalid @enderror" name="email"
                                value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                            <label for="email" class="small text-muted">{{ __('Email Address') }}</label>
                            <div class="focus-indicator"></div>
                        </div>

                        <div class="form-floating mb-3">
                            <input id="password" type="password"
                                class="form-control sleek-input @error('password') is-invalid @enderror" name="password"
                                placeholder="Password" required>
                            <label for="password" class="small text-muted">{{ __('Password') }}</label>
                            <div class="focus-indicator"></div>
                        </div>

                        <!-- Forgot Password Link -->
                        <div class="d-flex justify-content-end mb-4">
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="small text-decoration-none text-primary fw-semibold hover-underline">
                                    {{ __('Forgot Password?') }}
                                </a>
                            @endif
                        </div>

                        <!-- Error Messages with Icons -->
                        @error('email')
                            <div class="alert alert-danger py-2 small d-flex align-items-center fade-in">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                        @error('password')
                            <div class="alert alert-danger py-2 small d-flex align-items-center fade-in">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror

                        <!-- Remember Me Checkbox -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">
                                {{ __('Keep me signed in') }}
                            </label>
                        </div>

                        <!-- Submit Button with Hover Effect -->
                        <div class="d-grid mb-3 position-relative">
                            <button type="submit"
                                class="btn btn-primary py-2 rounded-3 fw-semibold border-0 overflow-hidden">
                                <span class="position-relative z-index-1">{{ __('Sign In') }}</span>
                                <div class="btn-hover-effect"></div>
                            </button>
                        </div>

                        <!-- Registration Link -->
                        <div class="text-center small text-muted">
                            {{ __("Don't have an account?") }}
                            <a href="{{ route('register') }}"
                                class="fw-semibold text-primary text-decoration-none ms-1 hover-underline">
                                {{ __('Sign up') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>



    </div>




    
    <!-- Enhanced Styles -->
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

        .sleek-input:focus~.focus-indicator {
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
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

    <!-- Enhanced Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus email field
            const email = document.getElementById('email');
            if (email && !email.value) {
                setTimeout(() => {
                    email.focus();
                }, 300);
            }

            // Add animation to form elements
            const formElements = document.querySelectorAll('.form-floating, .form-check, .d-grid, .text-center');
            formElements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(15px)';
                el.style.animation = `fadeIn 0.4s ease forwards ${index * 0.1}s`;
            });
        });
    </script>
@endsection
