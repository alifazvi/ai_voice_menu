<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-rounded.png') }}?v=3">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-rounded.png') }}?v=3">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon-rounded.png') }}?v=3">
        <link rel="apple-touch-icon" href="{{ asset('images/favicon-rounded.png') }}?v=3">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans antialiased text-slate-900">
        @if (request()->boolean('embed'))
            <div class="min-h-screen bg-slate-50 p-4 sm:p-6">
                <main class="mx-auto max-w-5xl">
                    @if (isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </main>
            </div>
        @elseif (auth()->check())
            @php
                $pageTitle = match (true) {
                    request()->routeIs('dashboard') => __('Dashboard'),
                    request()->routeIs('restaurants.*') => __('Restaurants'),
                    request()->routeIs('menus.*') => __('Menus'),
                    request()->routeIs('customers.*') => __('Customers'),
                    request()->routeIs('orders.*') => __('Orders'),
                    request()->routeIs('dineins.*') => __('Dine-Ins'),
                    request()->routeIs('profile.*') => __('Profile'),
                    default => config('app.name', 'Laravel'),
                };
            @endphp

            <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
                @include('layouts.navigation')

                <div class="flex min-h-screen min-w-0 flex-1 flex-col">
                    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                        <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                            <div class="flex min-w-0 items-center gap-3">
                                <button type="button"
                                        class="inline-flex items-center justify-center rounded-md border border-slate-200 bg-white p-2 text-slate-600 shadow-sm lg:hidden"
                                        x-on:click="sidebarOpen = true"
                                        aria-label="Open sidebar">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </button>

                                <div class="min-w-0">
                                    @isset($header)
                                        {{ $header }}
                                    @else
                                        <h1 class="truncate text-xl font-semibold text-slate-900">{{ $pageTitle }}</h1>
                                    @endisset
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="hidden text-right sm:block">
                                    <p class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                                </div>

                                <a href="{{ route('profile.edit') }}" class="hidden rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 sm:inline-flex">
                                    {{ __('Profile') }}
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="inline-flex rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800">
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </header>

                    <main class="flex-1 px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
                        @if (isset($slot))
                            {{ $slot }}
                        @else
                            @yield('content')
                        @endif
                    </main>
                </div>
            </div>
        @else
            <div class="min-h-screen">
                <main>
                    @if (isset($slot))
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endif
                </main>
            </div>
        @endauth

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('form.js-delete-form').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        event.preventDefault();

                        const message = form.dataset.confirmMessage || 'Are you sure you want to delete this record?';

                        Swal.fire({
                            title: 'Confirm Delete',
                            text: message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#dc2626',
                            reverseButtons: true,
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    });
                });
            });
        </script>

        @stack('scripts')
    </body>
</html>
