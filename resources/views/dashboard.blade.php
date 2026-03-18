<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="mx-auto max-w-7xl">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="p-6 text-gray-900 lg:p-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Welcome back') }}, {{ Auth::user()->name }}</h3>
                        <p class="text-sm text-gray-600">{{ __('Use the links below to manage the modules from your existing setup.') }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <a href="{{ route('restaurants.index') }}" class="rounded-lg border border-gray-200 p-4 transition hover:border-indigo-300 hover:shadow-sm">
                        <h4 class="text-base font-semibold text-gray-900">{{ __('Restaurants') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('View and create restaurant records.') }}</p>
                    </a>
                    <a href="{{ route('menus.index') }}" class="rounded-lg border border-gray-200 p-4 transition hover:border-indigo-300 hover:shadow-sm">
                        <h4 class="text-base font-semibold text-gray-900">{{ __('Menus') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Manage menus and uploaded menu files.') }}</p>
                    </a>
                    <a href="{{ route('customers.index') }}" class="rounded-lg border border-gray-200 p-4 transition hover:border-indigo-300 hover:shadow-sm">
                        <h4 class="text-base font-semibold text-gray-900">{{ __('Customers') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Track customer details and records.') }}</p>
                    </a>
                    <a href="{{ route('orders.index') }}" class="rounded-lg border border-gray-200 p-4 transition hover:border-indigo-300 hover:shadow-sm">
                        <h4 class="text-base font-semibold text-gray-900">{{ __('Orders') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Create orders and update statuses.') }}</p>
                    </a>
                    <a href="{{ route('dineins.index') }}" class="rounded-lg border border-gray-200 p-4 transition hover:border-indigo-300 hover:shadow-sm">
                        <h4 class="text-base font-semibold text-gray-900">{{ __('Dine-Ins') }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ __('Manage dine-in bookings and tables.') }}</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
