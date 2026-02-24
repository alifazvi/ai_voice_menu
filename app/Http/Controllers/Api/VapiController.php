<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Assistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\RequestException;
use App\Http\Controllers\Api\OpenAIController;


class VapiController extends Controller
{
    /**
     * Return menu details. Accepts `menu_id` or `restaurant_id`.
     */
    public function getMenu(Request $request)
    {
        $v = Validator::make($request->all(), [
            'menu_id' => 'nullable|exists:menus,id',
            'restaurant_id' => 'nullable|exists:restaurants,id',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        if ($request->filled('menu_id')) {
            $menu = Menu::with('restaurant')->find($request->menu_id);
            return response()->json(['menu' => $menu]);
        }

        if ($request->filled('restaurant_id')) {
            $menus = Menu::where('restaurant_id', $request->restaurant_id)->get();
            return response()->json(['menus' => $menus]);
        }

        return response()->json(['message' => 'Provide menu_id or restaurant_id'], 400);
    }


    /**
     * Track order by id. Accepts `order_id` as route parameter or `order_id` in POST.
     */
    public function trackOrder(Request $request, $orderId = null)
    {
        $id = $orderId ?? $request->get('order_id');
        if (empty($id)) {
            return response()->json(['message' => 'order_id required'], 400);
        }

        $order = Order::with(['restaurant', 'customer', 'menu'])->find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'total' => $order->total_amount,
            'items' => $order->items,
            'booked_at' => $order->booked_at,
            'placed_at' => $order->placed_at,
            'delivery_address' => $order->delivery_address,
        ]);
    }

    /**
     * Generic assistant event receiver for VAPI-style tool-calls.
     * Dispatches recognized function names to internal handlers and
     * returns a wrapped "results" payload that includes the original toolCallId.
     */
    public function assistantEvent(Request $request)
    {
        $content = $request->getContent();
        $payload = json_decode($content, true) ?: $request->all();

        $messageType = $payload['message']['type'] ?? null;
        if ($messageType !== 'tool-calls') {
            return response()->json(['message' => 'Not a tool-calls payload'], 400);
        }

        Log::info('assistantEvent received', ['payload' => $payload]);

        $toolCall = $payload['message']['toolCalls'][0] ?? null;
        $toolCallId = $toolCall['id'] ?? null;
        $functionName = $toolCall['function']['name'] ?? null;
        $arguments = $toolCall['function']['arguments'] ?? [];
        $args = is_array($arguments) ? $arguments : (json_decode($arguments, true) ?: []);

        Log::info('assistantEvent received', ['function' => $functionName, 'arguments' => $args]);

        try {
            $result = null;
            switch ($functionName) {
                case 'get_info':
                    Log::info('Dispatching get_info with args', ['args' => $args]);

                    $query = $args['customer_query'] ?? null;
                    $restaurantId = $args['restaurant_id'] ?? null; // optional

                    if ($query) {
                        Log::info('VapiController: get_info tool-call received', ['customer_query' => $query, 'toolCallId' => $toolCallId]);

                        $openai = new OpenAIController();

                        // Get knowledge base for the restaurant, fallback to default
                        if ($restaurantId) {
                            $kb = \App\Models\OpenAIKnowledgeBase::where('restaurant_id', $restaurantId)->first();
                        }
                        $kb = $kb ?? \App\Models\OpenAIKnowledgeBase::first();

                        $rag = [];
                        if ($kb && !empty($kb->vector_store_id)) {
                            // Use OpenAIController search function
                            $searchResults = $openai->search($kb->vector_store_id, $query, 3);

                            Log::info('Raw vector store search results', ['searchResults' => $searchResults]);

                            $rag = array_values(array_filter(array_map(function ($item) {
                                $textParts = [];
                                foreach (($item['content'] ?? []) as $content) {
                                    if (($content['type'] ?? null) === 'text' && !empty($content['text'])) {
                                        $textParts[] = is_array($content['text'])
                                            ? ($content['text']['value'] ?? '')
                                            : $content['text'];
                                    }
                                }

                                $text = trim(implode("\n", array_filter($textParts)));
                                if ($text === '' && !empty($item['text'])) {
                                    $text = is_array($item['text']) ? ($item['text']['value'] ?? '') : $item['text'];
                                }

                                if ($text === '') return null;

                                return [
                                    'score' => $item['score'] ?? null,
                                    'text'  => $text,
                                    'file_id' => $item['file_id'] ?? null,
                                ];
                            }, $searchResults)));
                        } 

                        Log::info('VapiController: get_info rag results', ['results_count' => count($rag), 'toolCallId' => $toolCallId]);

                        if (empty($rag)) {
    $result = "I'm sorry, I couldn't find any information about that menu item.";
} else {
    // Flatten matches into a readable string
    $menuText = '';
    foreach ($rag as $match) {
        $menuText .= ($match['text'] ?? '') . "\n";
    }
    $result = trim($menuText);
}
break;
                    }

                    $result = ['error' => 'Missing required argument: customer_query'];
                    break;
                    Log::info('Dispatching get_info with args', ['args' => $args]);

                    // Expect a single argument named `customer_query` containing the user's query.
                    $query = $args['customer_query'] ?? null;

                    if ($query) {
                        Log::info('VapiController: get_info tool-call received', ['customer_query' => $query, 'toolCallId' => $toolCallId]);
                        $openai = new OpenAIController();
                        // Use vector store id from knowledge base for search
                        $kb = \App\Models\OpenAIKnowledgeBase::first();
                        $vectorStoreId = $kb ? $kb->vector_store_id : null;
                        if (!empty($vectorStoreId)) {
                            $searchResponse = Http::withHeaders([
                                'Authorization' => 'Bearer ' . config('services.openai.key'),
                                'Content-Type' => 'application/json',
                            ])
                                ->timeout(15)
                                ->retry(1, 250)
                                ->post("https://api.openai.com/v1/vector_stores/{$vectorStoreId}/search", [
                                    'query' => $query,
                                    'max_num_results' => 3,
                                ]);

                            if ($searchResponse->successful()) {
                                $matches = $searchResponse->json('data') ?? [];
                                $rag = array_values(array_filter(array_map(function ($item) {
                                    $textParts = [];
                                    foreach (($item['content'] ?? []) as $content) {
                                        if (($content['type'] ?? null) === 'text' && !empty($content['text'])) {
                                            $textParts[] = is_array($content['text'])
                                                ? ($content['text']['value'] ?? '')
                                                : $content['text'];
                                        }
                                    }

                                    $text = trim(implode("\n", array_filter($textParts)));
                                    if ($text === '' && !empty($item['text'])) {
                                        $text = is_array($item['text']) ? ($item['text']['value'] ?? '') : $item['text'];
                                    }

                                    if ($text === '') {
                                        return null;
                                    }

                                    return [
                                        'score' => $item['score'] ?? null,
                                        'text' => $text,
                                        'file_id' => $item['file_id'] ?? null,
                                    ];
                                }, $matches)));
                            } else {
                                Log::warning('VapiController: vector store search failed, falling back to embeddings KB', [
                                    'status' => $searchResponse->status(),
                                    'body' => $searchResponse->body(),
                                    'vector_store_id' => $vectorStoreId,
                                ]);
                                $rag = $openai->queryKnowledgeBase($query);
                            }
                        } else {
                            $rag = $openai->queryKnowledgeBase($query);
                        }
                        Log::info('VapiController: get_info rag results', ['results_count' => is_array($rag) ? count($rag) : 0, 'toolCallId' => $toolCallId]);

                        // Normalize "no data found" responses from the KB into a plain text response.
                        $noData = false;
                        if (empty($rag)) {
                            $noData = true;
                        } elseif (is_string($rag) && strtolower($rag) === 'no data found') {
                            $noData = true;
                        } elseif (is_array($rag) && count($rag) === 1 && isset($rag[0]['message']) && strtolower($rag[0]['message']) === 'no data found') {
                            $noData = true;
                        }

                        if ($noData) {
                            $result = 'no data found';
                        } else {
                            $result = ['customer_query' => $query, 'matches' => $rag];
                        }

                        break;
                    }

                    // If customer_query not provided, return a helpful error for the caller.
                    $result = ['error' => 'Missing required argument: customer_query'];
                    break;
                case 'createOrder':
                    Log::info('Dispatching createOrder with args', ['args' => $args]);

                    // required args: name, phone_number, food_name, delivery_address, quantity
                    $required = ['name', 'phone_number', 'food_name', 'delivery_address', 'quantity'];
                    $missing = [];
                    foreach ($required as $r) {
                        if (empty($args[$r])) {
                            $missing[] = $r;
                        }
                    }

                    if (!empty($missing)) {
                        $result = ['error' => 'Missing required arguments', 'missing' => $missing];
                        break;
                    }

                    // create customer (split full name into first/last)
                    $fullName = trim($args['name']);
                    $parts = preg_split('/\s+/', $fullName, 2, PREG_SPLIT_NO_EMPTY);
                    $first = $parts[0] ?? '';
                    $last = $parts[1] ?? '';

                    $customer = Customer::create([
                        'first_name' => $first,
                        'last_name' => $last,
                        'phone' => $args['phone_number'],
                    ]);

                    // try to find a matching menu by exact name
                    $menu = Menu::where('name', $args['food_name'])->first();

                    $quantityRaw = $args['quantity'];
                    // Extract the first integer from the string, fallback to 1 if not found
                    if (preg_match('/\d+/', $quantityRaw, $matches)) {
                        $quantity = (int)$matches[0];
                    } else {
                        $quantity = 1;
                    }
                   
                    // create order
                    $orderData = [
                        'customer_id' => $customer->id,
                        'menu_id' => $menu ? $menu->id : null,
                        'restaurant_id' => $menu ? $menu->restaurant_id : 1,
                        'food_name' => $args['food_name'],
                        'quantity' => $quantity,
                        'delivery_address' => $args['delivery_address'],
                        'status' => 'placed',
                        'total_amount' => 0,
                    ];

                    $order = Order::create($orderData);

                    $result = [
                        'message' => 'Order created',
                        'order_id' => $order->id,
                        'customer_id' => $customer->id,
                        'menu_found' => $menu ? true : false,
                    ];

                    break;
                case 'trackOrder':
                    // allow passing order_id as argument or in path
                    $orderId = $args['order_id'] ?? ($args['id'] ?? null);
                    break;
                default:
                    return response()->json([
                        'results' => [[
                            'toolCallId' => $toolCallId,
                            'result' => "Unknown function: {$functionName}"
                        ]]
                    ]);
            }

            return response()->json([
                'results' => [[
                    'toolCallId' => $toolCallId,
                    'result' => $result
                ]]
            ]);
        } catch (\Throwable $e) {
            Log::error('assistantEvent error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'results' => [[
                    'toolCallId' => $toolCallId,
                    'result' => 'Internal error executing tool call'
                ]]
            ], 500);
        }
    }

    /**
     * Uploads a file to Vapi and returns the file ID.
     */
    public function uploadToVapi(string $filePath, string $fileName, string $apiKey)
    {
        try {

            $endpoint = config('services.vapi.endpoint', 'https://api.vapi.ai');

            $response = Http::withToken($apiKey)
                ->attach('file', fopen($filePath, 'r'), $fileName)
                ->post("{$endpoint}/file");

            if (!$response->successful()) {
                Log::error('Vapi Upload Error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            return $response->json('id');
        } catch (\Throwable $e) {
            Log::error('Vapi Upload Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Safe update: Fetches existing config and appends the new file ID.
     */

    public function assignFileToAssistant(string $assistantId, string $fileId, string $apiKey): array
    {
        try {
            // First, get the current assistant to preserve existing configuration
            $currentResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey
            ])->get("https://api.vapi.ai/assistant/{$assistantId}");

            if (!$currentResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch current assistant configuration',
                    'error' => $currentResponse->json()
                ];
            }

            $currentAssistant = $currentResponse->json();

            // Create or update knowledge base first
            $kbResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.vapi.ai/knowledge-base', [
                'provider' => 'canonical',
                'fileIds' => [$fileId]
            ]);

            if (!$kbResponse->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to create knowledge base',
                    'error' => $kbResponse->json()
                ];
            }

            $knowledgeBase = $kbResponse->json();
            $knowledgeBaseId = $knowledgeBase['id'];

            // Now update the assistant with the knowledge base ID
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])
                ->timeout(30)
                ->retry(3, 1000)
                ->patch("https://api.vapi.ai/assistant/{$assistantId}", [
                    'model' => array_merge($currentAssistant['model'] ?? [], [
                        'knowledgeBaseId' => $knowledgeBaseId
                    ])
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'knowledge_base_id' => $knowledgeBaseId,
                    'message' => 'File successfully assigned to assistant'
                ];
            }

            return [
                'success' => false,
                'error' => $response->json(),
                'status_code' => $response->status(),
                'message' => 'Failed to assign file to assistant'
            ];
        } catch (RequestException $e) {
            Log::error('HTTP request failed when assigning file to assistant', [
                'assistant_id' => $assistantId,
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'response' => $e->getResponse() ? json_decode((string) $e->getResponse()->getBody(), true) : null
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Request failed'
            ];
        } catch (\Exception $e) {
            Log::error('Unexpected error when assigning file to assistant', [
                'assistant_id' => $assistantId,
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Unexpected error occurred'
            ];
        }
    }
}
