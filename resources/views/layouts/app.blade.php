<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

        <style>
            body, html {
                font-family: 'Cambria', serif;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-responsive::-webkit-scrollbar {
                height: 8px;
            }

            .table-responsive::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .table-responsive::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 4px;
            }

            .table-responsive::-webkit-scrollbar-thumb:hover {
                background: #555;
            }

            .table thead th {
                white-space: nowrap;
            }

            .table tbody td {
                white-space: nowrap;
            }

            .hover-bg-light:hover {
                background-color: rgba(0, 0, 0, 0.05);
                transition: background-color 0.2s ease-in-out;
            }

            @media (max-width: 991.98px) {
                .offcanvas {
                    max-width: 280px;
                }
            }
            @media (max-width: 767.98px) {
                .table-responsive {
                    margin: 0 -1rem;
                    padding: 0 1rem;
                }
            }
        </style>
    
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <!-- Alpine.js -->
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>

    <body class="antialiased">
        <div class="min-h-screen bg-gray-100">
            @php
                $isVehicleBookings = request()->routeIs('vehicle.bookings'); 
            @endphp

            @include('layouts.navigation')
            <!-- Sidebar Toggle Button for Mobile -->
            @if(!$isVehicleBookings)
                <button class="btn btn-primary d-lg-none position-fixed start-0 top-0 mt-2 ms-2 z-index-1031"
                        type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                    <i class="bi bi-list"></i>
                </button>
            @endif

            <div class="container-fluid">
                <div class="row">
                    @if(!$isVehicleBookings)
                        <div class="col-lg-2 d-none d-lg-block p-0">
                            @include('components.sidebar')
                        </div>
                    @endif

                    <!-- Offcanvas Sidebar for mobile -->
                    @if(!$isVehicleBookings)
                        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title">Menu</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                            </div>

                            <div class="offcanvas-body">
                                @include('components.sidebar')
                            </div>
                        </div>
                    @endif

                    <!-- Main Content -->
                    <div class="col-12 {{ !$isVehicleBookings ? 'col-lg-10' : '' }} p-4">
                        <main>
                            {{ $slot ?? '' }}
                            @yield('content')
                        </main>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        
        <!-- Include toast component -->
        @include('components.toast')
        
        <!-- Stack for additional scripts -->
        @stack('scripts')

        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Please Confirm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmationMessage">Are you sure you want to proceed?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" id="confirmActionBtn">Yes, Proceed</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            (function() {
                const INACTIVITY_TIMEOUT = 5 * 60 * 1000; 

                let inactivityTimer;

                function resetTimer() {
                    clearTimeout(inactivityTimer);
                    inactivityTimer = setTimeout(logoutUser, INACTIVITY_TIMEOUT);
                }

                function logoutUser() {
                    fetch("{{ route('logout') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    }).then(() => {
                        window.location.href = "{{ route('login') }}";
                    });
                }

                ['mousemove', 'keydown', 'scroll', 'click'].forEach(evt => {
                    document.addEventListener(evt, resetTimer);
                });

                resetTimer(); 
            })();
        </script>
    </body>
</html>