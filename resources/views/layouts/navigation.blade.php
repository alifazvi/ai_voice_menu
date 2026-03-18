@php
    $navigationItems = [
        ['label' => __('Dashboard'), 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
        ['label' => __('Orders'), 'route' => route('orders.index'), 'active' => request()->routeIs('orders.*')],
        ['label' => __('Dine-Ins'), 'route' => route('dineins.index'), 'active' => request()->routeIs('dineins.*')],
        ['label' => __('Customers'), 'route' => route('customers.index'), 'active' => request()->routeIs('customers.*')],
        ['label' => __('Restaurants'), 'route' => route('restaurants.index'), 'active' => request()->routeIs('restaurants.*')],
        ['label' => __('Menus'), 'route' => route('menus.index'), 'active' => request()->routeIs('menus.*')],
        ['label' => __('Profile'), 'route' => route('profile.edit'), 'active' => request()->routeIs('profile.*')],
    ];
@endphp

<div x-cloak
     x-show="sidebarOpen"
     class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
     x-on:click="sidebarOpen = false"></div>

<aside class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 -translate-x-full flex-col border-r border-slate-200 bg-white shadow-xl transition-transform duration-200 ease-out lg:static lg:translate-x-0 lg:shadow-none"
       x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <x-application-logo class="h-10 w-10 shrink-0" />
            <div>
                <p class="text-lg font-semibold text-slate-900">{{ config('app.name', 'Laravel') }}</p>
            </div>
        </a>

        <button type="button"
                class="inline-flex items-center justify-center rounded-md p-2 text-slate-500 lg:hidden"
                x-on:click="sidebarOpen = false"
                aria-label="Close sidebar">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <div class="space-y-1">
            @foreach ($navigationItems as $item)
                <a href="{{ $item['route'] }}"
                   x-on:click="sidebarOpen = false"
                   @class([
                       'flex items-center rounded-xl px-4 py-3 text-sm font-medium transition',
                       'bg-slate-900 text-white shadow-sm' => $item['active'],
                       'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $item['active'],
                   ])>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <div class="border-t border-slate-200 px-3 py-4">
        <p class="px-4 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ __('Navigation') }}</p>
    </div>
</aside>
