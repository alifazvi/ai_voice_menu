<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\VapiController;
use App\Models\Assistant;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\OpenAIController;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('restaurant')->latest()->paginate(10);
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        $restaurants = Restaurant::orderBy('name')->get();
        return view('menus.create', compact('restaurants'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'attachments' => 'nullable',
            'attachments.*' => 'file|mimes:pdf,csv,txt,json,jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // 1️⃣ Handle uploaded attachments
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (!$file->isValid()) continue;

                $path = $file->store('menus', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'path' => $path,
                    'url'  => Storage::url($path),
                ];
            }
        }

        if (!empty($attachments)) {
            $data['attachments'] = $attachments;
        }

        // 2️⃣ Create menu record
        $menu = Menu::create($data);

        if (empty($attachments)) {
            return redirect()->route('menus.index')->with('success', 'Menu created');
        }

        Log::info('Uploading attachments for restaurant_id: ' . $data['restaurant_id']);

        $assistant = Assistant::where('restaurant_id', $data['restaurant_id'])->first();
        if (!$assistant) {
            return redirect()->route('menus.index')->with('success', 'Menu created (no assistant found)');
        }

        $vapi = new VapiController();
        $openai = new OpenAIController();
        $apiKey = config('services.vapi.private_key');

        // 3️⃣ Load or create knowledge base for this restaurant
        $openaiKb = \App\Models\OpenAIKnowledgeBase::firstOrCreate(
            ['restaurant_id' => $data['restaurant_id']],
            ['name' => 'default', 'file_ids' => [], 'embeddings' => [], 'vector_store_id' => null]
        );

        // 4️⃣ Ensure vector store exists
        if (empty($openaiKb->vector_store_id)) {
            $vectorStoreId = $openai->createVectorStore('Restaurant ' . $data['restaurant_id']);
            if ($vectorStoreId) {
                $openaiKb->vector_store_id = $vectorStoreId;
                $openaiKb->save();
            } else {
                return redirect()->route('menus.index')->with('error', 'Failed to create vector store');
            }
        }
        $vectorStoreId = $openaiKb->vector_store_id;

        // 5️⃣ Process attachments
        foreach ($attachments as $attachment) {
            $fullPath = Storage::disk('public')->path($attachment['path']);
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            // Convert JSON menu file to readable text for semantic search
            $filePathToUpload = $fullPath;
            if ($extension === 'json') {
                $json = json_decode(file_get_contents($fullPath), true);
                if ($json && isset($json['categories'])) {
                    $lines = [];
                    foreach ($json['categories'] as $category) {
                        $catName = $category['name'] ?? '';
                        foreach ($category['products'] ?? [] as $product) {
                            $prodName = $product['name'] ?? '';
                            $rawPrice = $product['price'] ?? 0;

                            // Extract basePrice from pricing_taxes if price is zero
                            $basePrice = null;
                            if (
                                ($rawPrice === 0 || $rawPrice === '0' || $rawPrice === null || $rawPrice === '') &&
                                !empty($product['pricing_taxes']) &&
                                isset($product['pricing_taxes'][0]['basePrice'])
                            ) {
                                $basePrice = $product['pricing_taxes'][0]['basePrice'];
                            }

                            $price = ($rawPrice === 0 || $rawPrice === '0' || $rawPrice === null || $rawPrice === '') && $basePrice !== null
                                ? $basePrice
                                : $rawPrice;

                            $sku = $product['sku'] ?? '';
                            $desc = $product['description'] ?? '';

                            // Add a general info line (no price)
                            $lines[] = "$prodName is a menu item in $catName. $desc SKU: $sku.";

                            // Add a price line (for price-specific queries)
                            $lines[] = "The price of $prodName is $price pounds.";
                        }
                    }
                    $searchableText = implode("\n", $lines);
                    $tempPath = storage_path('app/temp_' . uniqid() . '.txt');
                    file_put_contents($tempPath, $searchableText);
                    $filePathToUpload = $tempPath;
                }
            }

            // 5️⃣ Upload to VAPI
            $vapiFileId = $vapi->uploadToVapi($fullPath, $attachment['name'], $apiKey);
            if ($vapiFileId) {
                $vapi->assignFileToAssistant($assistant->vapi_assistant_id, $vapiFileId, $apiKey);
                Log::info('VAPI upload success', ['fileId' => $vapiFileId]);
            }

            // 6️⃣ Upload to OpenAI and attach to vector store
            $openaiFileId = $openai->uploadFile($filePathToUpload);
            if ($openaiFileId) {
                $openai->attachFileToVectorStore($vectorStoreId, $openaiFileId);
                $openaiKb->file_ids = array_values(array_unique(array_merge($openaiKb->file_ids ?? [], [$openaiFileId])));
                $openaiKb->save();

                Log::info('File attached to OpenAI vector store', [
                    'fileId' => $openaiFileId,
                    'vector_store_id' => $vectorStoreId
                ]);
            }

            // Cleanup temp file
            if (isset($tempPath) && file_exists($tempPath)) {
                unlink($tempPath);
            }
        }

        return redirect()->route('menus.index')->with('success', 'Menu created and synced with AI');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu deleted');
    }
}
