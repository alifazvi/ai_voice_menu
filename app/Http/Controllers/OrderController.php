<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Customer;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['restaurant','customer','menu'])->latest()->paginate(15);
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $restaurants = Restaurant::orderBy('name')->get();
        $customers = Customer::orderBy('first_name')->get();
        $menus = Menu::orderBy('name')->get();
        return view('orders.create', compact('restaurants','customers','menus'));
    }

    /**
     * Helper: resolve the effective price from a Menu model.
     * Falls back to pricing_taxes[0]['basePrice'] when price is empty.
     */
    private function resolveMenuPrice(?Menu $menu): float
    {
        if (!$menu) {
            return 0;
        }

        $price = $menu->price ?? 0;

        if (($price == 0 || $price === null) && !empty($menu->pricing_taxes) && isset($menu->pricing_taxes[0]['basePrice'])) {
            $price = $menu->pricing_taxes[0]['basePrice'];
        }

        if (($price == 0 || $price === null) && ($menu->base_price ?? 0) > 0) {
            $price = $menu->base_price;
        }

        return (float) $price;
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
            'food_name' => 'nullable|string',
            'quantity' => 'nullable|numeric',
        ]);

        // Decode items if present
        if (!empty($data['items']) && is_string($data['items'])) {
            $decoded = json_decode($data['items'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['items'] = $decoded;
            }
        }

        // Calculate total_amount if not provided
        $menu = null;
        $price = 0;

        if (empty($data['total_amount']) || $data['total_amount'] == 0) {
            $total = 0;

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $menu = null;
                    if (!empty($item['menu_id'])) {
                        $menu = Menu::find($item['menu_id']);
                    } elseif (!empty($item['name'])) {
                        $menu = Menu::where('name', $item['name'])->first();
                    }
                    $qty = $item['qty'] ?? 1;
                    $price = $this->resolveMenuPrice($menu);
                    $total += $price * $qty;
                }
            } elseif (!empty($data['menu_id'])) {
                $menu = Menu::find($data['menu_id']);
                $qty = $data['quantity'] ?? 1;
                $price = $this->resolveMenuPrice($menu);
                $total = $price * $qty;
            }

            $data['total_amount'] = $total;
        }

        Log::info('Order price calculation', [
            'menu_id' => $menu ? $menu->id : null,
            'menu_name' => $menu ? $menu->name : null,
            'price' => $price,
            'pricing_taxes' => $menu ? $menu->pricing_taxes : null,
        ]);

        Order::create($data);

        return redirect()->route('orders.index')->with('success', 'Order created');
    }
}
