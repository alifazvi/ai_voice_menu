<?php

namespace App\Http\Controllers;

use App\Models\DineIn;
use App\Models\Restaurant;
use App\Models\Customer;
use App\Models\Menu;
use Illuminate\Http\Request;

class DineInController extends Controller
{
    public function index()
    {
        $dineins = DineIn::with('customer')
            ->latest()
            ->paginate(10);
        return view('dineins.index', compact('dineins'));
    }

    public function create()
    {
        $restaurants = Restaurant::orderBy('name')->get();
        $customers = Customer::orderBy('first_name')->get();
        $menus = Menu::orderBy('name')->get();
        return view('dineins.create', compact('restaurants', 'customers', 'menus'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'table_number' => 'nullable|string|max:50',
            'guest_count' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'booking_slot' => 'nullable|string|max:255',
            'special_request' => 'nullable|string|max:255',
        ]);

        DineIn::create([
            'customer_id' => $data['customer_id'] ?? null,
            'table_number' => $data['table_number'] ?? 'TBD',
            'seats' => (int) ($data['guest_count'] ?? 1),
            'location' => $data['location'] ?? 'Main Hall',
            'is_available' => 0,
            'booking_date' => now()->toDateString(),
            'booking_slot' => $data['booking_slot'] ?? null,
            'special_request' => $data['special_request'] ?? null,
        ]);

        return redirect()->route('dineins.index')->with('success', 'Dine-in booking created');
    }

    public function destroy(DineIn $dinein)
    {
        $dinein->delete();

        return redirect()->route('dineins.index')->with('success', 'Dine-in booking deleted');
    }
}
