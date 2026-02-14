<!DOCTYPE html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="light" data-sidebar-size="lg"
    data-sidebar-image="none">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Kanakku provides clean Admin Templates for managing Sales, Payment, Invoice, Accounts and Expenses in HTML, Bootstrap 5, ReactJs, Angular, VueJs and Laravel.">
    <meta name="keywords"
        content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">


    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">


    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        /* the page header */
        .page-header {
            padding: 1rem 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .page-title h5 {
            font-weight: 600;
            color: #2c3e50;
        }

        .btn-group .btn {
            border-radius: 6px;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-sm i {
            font-size: 0.8rem;
        }
    </style>


    <style>
        * {
            font-family: "Lato", sans-serif;
            font-weight: bold;
        }

        .lato-thin {
            font-family: "Lato", sans-serif;
            font-weight: 100;
            font-style: normal;
        }

        .lato-light {
            font-family: "Lato", sans-serif;
            font-weight: 300;
            font-style: normal;
        }

        .lato-regular {
            font-family: "Lato", sans-serif;
            font-weight: 400;
            font-style: normal;
        }

        .lato-bold {
            font-family: "Lato", sans-serif;
            font-weight: 700;
            font-style: normal;
        }

        .lato-black {
            font-family: "Lato", sans-serif;
            font-weight: 900;
            font-style: normal;
        }

        .lato-thin-italic {
            font-family: "Lato", sans-serif;
            font-weight: 100;
            font-style: italic;
        }

        .lato-light-italic {
            font-family: "Lato", sans-serif;
            font-weight: 300;
            font-style: italic;
        }

        .lato-regular-italic {
            font-family: "Lato", sans-serif;
            font-weight: 400;
            font-style: italic;
        }

        .lato-bold-italic {
            font-family: "Lato", sans-serif;
            font-weight: 700;
            font-style: italic;
        }

        .lato-black-italic {
            font-family: "Lato", sans-serif;
            font-weight: 900;
            font-style: italic;
        }
    </style>
    <style>
        .hover-shadow-sm:hover {
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.08);
            transition: box-shadow 0.3s ease-in-out;
        }
    </style>
    <style>
        /* modal css for location  */
        /* Optional: Smooth modal animation */
        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }

        /* For dark select dropdown arrow */
        .form-select.bg-secondary {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 4 5' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2 0L0 2h4L2 0zM2 5L0 3h4L2 5z'/%3E%3C/svg%3E");
        }
    </style>
    <style>
        /* Core Toast Styling */
        /* Sleek toast base */
        .sleek-toast {
            font-size: 12px !important;
            padding: 6px 10px !important;
            min-width: 220px !important;
            max-width: 300px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        .sleek-title-sm {
            font-size: 14px !important;
            font-weight: 500 !important;
            margin-bottom: 4px;
        }

        .sleek-progress-sm {
            height: 4px !important;
            border-radius: 2px;
        }

        .sleek-light {
            background-color: #ffffff !important;
            color: #333 !important;
        }

        .sleek-dark {
            background-color: #1e1e2f !important;
            color: #f0f0f0 !important;
        }


        /* Animation */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    {{-- styles for card --}}
    <style>
        .bg-primary-light {
            background-color: #e6f0ff;
        }

        .hover-shadow:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
            transition: all 0.3s ease;
        }
    </style>
    <style>
        /* use ai button */
        .btn-ai {
            background: linear-gradient(135deg, #1cc88a, #4e73df);
            color: white;
            border: none;
            padding: 10px 18px;
            font-weight: 600;
            border-radius: 25px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .btn-ai:hover {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
    </style>

    <style>
        /* add user form */
        .sleek-input {
            border-radius: 12px;
            border: 1px solid #dee2e6;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .sleek-input:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 16px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .form-title {
            font-weight: 600;
            color: #333;
        }

        .btn-upload {
            font-weight: 500;
            border-radius: 20px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 3px solid #f1f1f1;
        }
    </style>

    <style>
        /* search bar css */
        #searchInput {
            width: 100%;
            max-width: 250px;
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 10px;
            outline: none;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        #searchInput::placeholder {
            color: #aaa;
            font-style: italic;
        }

        #searchInput:focus {
            border-color: #007BFF;
            box-shadow: 0 0 6px rgba(0, 123, 255, 0.25);
        }
    </style>

    <style>
        /* table bold css */
        .table-bold {
            font-weight: bold
        }
    </style>

    <style>
        /* permission table */
        .permission-section {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            border: 1px solid #f0f0f0;
            transition: all 0.2s ease;
        }

        .permission-section:hover {
            border-color: #e0e0e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .section-title {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
            letter-spacing: 0.2px;
        }

        .permission-items {
            border-left: 2px solid #f0f0f0;
            margin-left: 12px;
            padding-left: 20px;
            transition: border-color 0.2s ease;
        }

        .permission-section:hover .permission-items {
            border-left-color: #e0e0e0;
        }

        .form-check-label {
            cursor: pointer;
            user-select: none;
            color: #4a5568;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .form-check-input {
            width: 1.1em;
            height: 1.1em;
            margin-top: 0.15em;
        }

        .form-check-input:checked {
            background-color: #2d3748;
            border-color: #2d3748;
        }

        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.3em;
        }

        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .btn-dark {
            background-color: #2d3748;
            border-color: #2d3748;
        }

        .btn-dark:hover {
            background-color: #1a202c;
            border-color: #1a202c;
        }
    </style>




    </style>
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Feather CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/feather/feather.css') }}">

    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">

    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.min.css') }}">

    <!-- Datatables CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables/datatables.min.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <!-- Layout Js -->
    <script src="{{ asset('assets/js/layout.js') }}" type="text/javascript"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Indie+Flower&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap"
        rel="stylesheet">


    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #3498db;
            animation: spin 1s ease-in-out infinite;
        }

        .loader-text {
            margin-top: 15px;
            color: #333;
            font-family: 'Arial', sans-serif;
            font-size: 14px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .page-loader {
            background-color: rgba(0, 0, 0, 0.9);
        }

        .loader-text {
            color: #fff;
        }

        .loader-spinner {
            border-top-color: #fff;
        }



        /* .loader-spinner {
            width: 15px;
            height: 15px;
            background-color: #3498db;
            border-radius: 50%;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.2);
                opacity: 1;
            }
        } */
    </style>

    <style>
        /* Enhanced Delete Card Styles */
        <style>

        /* Glassmorphic background */
        .delete-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-radius: 1.5rem;
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        .delete-background {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05), rgba(220, 38, 38, 0.1));
            animation: bgMove 6s ease-in-out infinite;
            background-size: 200% 200%;
            z-index: 0;
        }

        @keyframes bgMove {

            0%,
            100% {
                background-position: left top;
            }

            50% {
                background-position: right bottom;
            }
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .pulse-soft {
            animation: pulseSoft 2s infinite;
        }

        @keyframes pulseSoft {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .btn-danger:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>

    <style>
        .page-header {
            position: sticky;
            top: 0;
            z-index: 1020;
            /* box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05); */
        }

        .page-title {
            position: relative;
            padding-left: 16px;
        }

        .page-title:before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 18px;
            width: 4px;
            background-color: #3b7ddd;
            border-radius: 2px;
        }

        .breadcrumb {
            padding: 0;
            background: transparent;
            font-size: 0.85rem;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>

</head>

<body>

    {{-- <div class="page-loader">
        <div class="loader-spinner"></div>
        <div class="loader-text">Loading...</div>
    </div> --}}


    <!-- Main Wrapper -->
    <div class="main-wrapper">
        {{-- @php $roles = auth()->user()->roles; @endphp --}}
        <!-- Header -->
        @include('inc.header')

        @include('inc.sidebar_admin')

        @yield('content')

        @include('inc.footer')
        <!-- /Header -->


    </div>
    <!-- /Main Wrapper -->

    <!--Theme Setting -->

    <!-- /Theme Setting -->
    <!-- jQuery -->
    {{-- <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}" type="text/javascript') }}"></script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap Core JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}" type="text/javascript"></script>

    <!-- Datatable JS -->
    <script src="{{ asset('assets/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>

    <!-- select CSS -->
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}" type="text/javascript"></script>

    <!-- Slimscroll JS -->
    <script src="{{ asset('assets/plugins/slimscroll/jquery.slimscroll.min.js') }}" type="text/javascript"></script>

    <!-- Datepicker Core JS -->
    <script src="{{ asset('assets/plugins/moment/moment.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>

    <!-- Apexchart JS -->
    <script src="{{ asset('assets/plugins/apexchart/apexcharts.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/apexchart/chart-data.js') }}" type="text/javascript"></script>

    <!-- multiselect JS -->
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}" type="text/javascript"></script>

    <!-- Theme Settings JS -->
    <script src="{{ asset('assets/js/theme-settings.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/js/greedynav.js') }}" type="text/javascript"></script>

    <!-- Custom JS -->
    <script src="{{ asset('assets/js/script.js') }}" type="text/javascript"></script>

    <script src="{{ asset('assets/js/form-validation.js') }}" type="text/javascript"></script>

    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const themeClass = prefersDark ? 'sleek-dark' : 'sleek-light';

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                timer: 3000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                customClass: {
                    popup: `sleek-toast ${themeClass}`,
                    title: 'sleek-title-sm',
                    timerProgressBar: 'sleek-progress-sm'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @elseif (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: "{{ session('error') }}",
                timer: 4000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                customClass: {
                    popup: `sleek-toast ${themeClass}`,
                    title: 'sleek-title-sm',
                    timerProgressBar: 'sleek-progress-sm'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

        // Debounce function to limit the frequency of searchTable calls
        function debounce(func, delay) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, arguments), delay);
            };
        }

        function searchTable() {
            const input = document.getElementById("searchInput").value.toLowerCase();
            const rows = document.querySelectorAll("#myTable tbody tr");
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? "" : "none";
            });
        }

        document.getElementById("searchInput").addEventListener("keyup", debounce(searchTable, 200));
    </script>
    <script>
        function downloadCSV() {
            const table = document.getElementById("myTable");
            let csv = [];
            for (let row of table.rows) {
                let cols = Array.from(row.cells).map(cell => `"${cell.textContent.trim()}"`);
                csv.push(cols.join(","));
            }
            const csvContent = csv.join("\n");

            const blob = new Blob([csvContent], {
                type: "text/csv;charset=utf-8;"
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.setAttribute("href", url);
            a.setAttribute("download", "table_data.csv");
            a.style.display = "none";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>


    <script>
        //manage permssions
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle all permissions for a section
            document.querySelectorAll('.select-all').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const module = this.dataset.module;
                    const section = this.closest('.permission-section');
                    const checks = section.querySelectorAll('.permission-check');
                    checks.forEach(check => {
                        check.checked = this.checked;
                    });
                });
            });

            // Update "select all" checkbox when individual permissions change
            document.querySelectorAll('.permission-check').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const section = this.closest('.permission-section');
                    const checks = section.querySelectorAll('.permission-check');
                    const allCheckbox = section.querySelector('.select-all');

                    const allChecked = Array.from(checks).every(check => check.checked);
                    allCheckbox.checked = allChecked;
                });
            });
        });

        //loader
        window.addEventListener('load', function() {
            const loader = document.querySelector('.page-loader');
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500); // matches the CSS transition time
        });

        //delete card
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('confirmDelete');
            const button = document.getElementById('deleteBtn');

            input.addEventListener('input', () => {
                const isValid = input.value.trim().toUpperCase() === 'DELETE';
                input.classList.toggle('is-valid', isValid);
                input.classList.toggle('is-invalid', !isValid);
                button.disabled = !isValid;
            });

            document.querySelector('form.needs-validation').addEventListener('submit', function(e) {
                if (input.value.trim().toUpperCase() !== 'DELETE') {
                    e.preventDefault();
                    input.classList.add('is-invalid');
                    return;
                }

                // Show loading
                button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Deleting...`;
                button.disabled = true;
            });
        });
    </script>


</body>

</html>
