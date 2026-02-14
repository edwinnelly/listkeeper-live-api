@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center py-4" style="min-height: 100vh;">
    <div class="col-11 col-sm-10 col-md-6 col-lg-5 col-xl-4">
        <div class="card sleek-card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="text-center pt-4">
                <a href="/" class="d-inline-block transition-all hover-scale">
                    <img src="{{ asset('assets/img/mylogo.svg') }}" alt="Company Logo" style="height: 40px;">
                </a>
            </div>

            <div class="card-header bg-transparent text-center border-0 pt-2 pb-0">
                <small class="text-muted">{{ __('Reset your password') }}</small>
            </div>

            <div class="card-body px-4 pt-0 pb-4">
                @if (session('status'))
                    <div class="alert alert-success py-2 small d-flex align-items-center mb-3 fade-in">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <!-- Email Field -->
                    <div class="form-floating mb-4">
                        <input id="email" type="email"
                            class="form-control sleek-input @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}"
                            placeholder=" " required autofocus>
                        <label for="email" class="small text-muted">{{ __('Email Address') }}</label>
                        @error('email')
                            <div class="invalid-feedback d-flex align-items-center mt-1">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 rounded-3 fw-semibold">
                            {{ __('Send Reset Link') }}
                        </button>
                    </div>

                    <!-- Back to Login Link -->
                    <div class="text-center small text-muted">
                        <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-none hover-underline">
                            <i class="bi bi-arrow-left me-1"></i> {{ __('Back to login') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Style (matches your other auth pages) -->
<style>
    .sleek-card {
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.85);
        transition: all 0.3s ease-in-out;
    }

    .sleek-card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }

    /* Floating Label Adjustments */
    .form-floating>.form-control {
        height: calc(3rem + 2px);
        padding: 0.6rem 0.75rem;
    }

    .form-floating>label {
        padding: 0.6rem 0.75rem;
        font-size: 0.95rem;
        color: #6b7280;
    }

    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label {
        transform: scale(0.85) translateY(-0.9rem) translateX(0.15rem);
        color: #6b7280;
    }

    .sleek-input {
        border: 1.5px solid #e0e0e0;
        border-radius: 0.375rem;
        font-size: 0.95rem;
        transition: border-color 0.3s ease;
    }

    .sleek-input:focus {
        border-color: #6366f1;
        box-shadow: none;
    }

    .btn-primary {
        background-color: #000;
        border-color: #000;
    }

    .btn-primary:hover {
        background-color: #333;
        border-color: #333;
    }

    .invalid-feedback {
        font-size: 0.85rem;
    }

    .alert-success {
        font-size: 0.85rem;
        border-radius: 6px;
    }

    .hover-scale {
        transition: transform 0.3s ease;
    }

    .hover-scale:hover {
        transform: scale(1.05);
    }

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

    @media (max-width: 576px) {
        .sleek-card {
            border-radius: 1rem;
        }
    }
</style>

<!-- Animation Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Add animation to form elements
        const formElements = document.querySelectorAll('.form-floating, .d-grid, .text-center');
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
