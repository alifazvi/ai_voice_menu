<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Customer;
use App\Models\Menu;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['restaurant','customer','menu'])->latest()->paginate(12);
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $restaurants = Restaurant::orderBy('name')->get();
        $customers = Customer::orderBy('first_name')->get();
        $menus = Menu::orderBy('name')->get();
        return view('orders.create', compact('restaurants','customers','menus'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'customer_id' => 'nullable|exists:customers,id',
            'menu_id' => 'nullable|exists:menus,id',
            'status' => 'nullable|string',
            'total_amount' => 'nullable|numeric',
            'items' => 'nullable|string',
            'booked_at' => 'nullable|date',
            'placed_at' => 'nullable|date',
            'payment_method' => 'nullable|string',
        ]);

        if (!empty($data['items']) && is_string($data['items'])) {
            $decoded = json_decode($data['items'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['items'] = $decoded;
            }
        }

        Order::create($data);

        return redirect()->route('orders.index')->with('success', 'Order created');
    }
}
