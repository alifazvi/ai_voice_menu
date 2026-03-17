<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Customer;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\OpenAIController;

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

    /**
     * Search the OpenAI knowledge base for the price of a food item.
     */
    private function resolvePriceFromKnowledgeBase(string $foodName): float
    {
        $kb = \App\Models\OpenAIKnowledgeBase::first();
        if (!$kb || empty($kb->vector_store_id)) {
            return 0;
        }

        try {
            $openai = new OpenAIController();
            $searchResults = $openai->search($kb->vector_store_id, "price of {$foodName}", 3);

            foreach ($searchResults as $item) {
                foreach (($item['content'] ?? []) as $content) {
                    if (($content['type'] ?? null) === 'text' && !empty($content['text'])) {
                        $text = is_array($content['text']) ? ($content['text']['value'] ?? '') : $content['text'];
                        
                        // 1) Try to extract from JSON structure (e.g., "price": "2.00")
                        if (preg_match('/"price"\s*:\s*"?([\d.]+)"?/i', $text, $m)) {
                            return (float) $m[1];
                        }
                        
                        // 2) Match formatted text patterns
                        // Pattern: "Chicken Burger in Burgers costs 3.5 pounds"
                        $escaped = preg_quote($foodName, '/');
                        if (preg_match("/{$escaped}.*?costs\s+([\d.]+)\s+pounds/i", $text, $m)) {
                            return (float) $m[1];
                        }
                        
                        // 3) Pattern: "The price of Chicken Burger is 3.5 pounds"
                        if (preg_match("/price of\s+{$escaped}\s+is\s+([\d.]+)\s+pounds/i", $text, $m)) {
                            return (float) $m[1];
                        }
                        
                        // 4) Generic pattern: look for any price amount following the item name
                        if (preg_match("/{$escaped}[\\s\\-:£]*?([\d.]+)/i", $text, $m)) {
                            return (float) $m[1];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('KB price lookup failed', ['error' => $e->getMessage()]);
        }

        return 0;
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

                    // Fallback: search knowledge base by item name
                    if ($price == 0 && !empty($item['name'])) {
                        $price = $this->resolvePriceFromKnowledgeBase($item['name']);
                    }

                    $total += $price * $qty;
                }
            } elseif (!empty($data['menu_id'])) {
                $menu = Menu::find($data['menu_id']);
                $qty = $data['quantity'] ?? 1;
                $price = $this->resolveMenuPrice($menu);

                // Fallback: search knowledge base by menu name
                if ($price == 0 && $menu) {
                    $price = $this->resolvePriceFromKnowledgeBase($menu->name);
                }

                $total = $price * $qty;
            } elseif (!empty($data['food_name'])) {
                // No menu_id but food_name is provided (e.g. from VAPI)
                $qty = $data['quantity'] ?? 1;
                $price = $this->resolvePriceFromKnowledgeBase($data['food_name']);
                $total = $price * $qty;
            }

            $data['total_amount'] = $total;
        }

        Log::info('Order price calculation', [
            'menu_id' => $menu ? $menu->id : null,
            'menu_name' => $menu ? $menu->name : null,
            'food_name' => $data['food_name'] ?? null,
            'price' => $price,
            'total_amount' => $data['total_amount'],
        ]);

        Order::create($data);

        return redirect()->route('orders.index')->with('success', 'Order created');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('orders.index')->with('success', 'Order deleted');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|string|max:50',
        ]);

        $order->update([
            'status' => $data['status'],
        ]);

        return redirect()->route('orders.index')->with('success', 'Order status updated');
    }
}
