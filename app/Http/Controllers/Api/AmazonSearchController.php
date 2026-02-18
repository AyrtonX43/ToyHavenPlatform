<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AmazonSearchController extends Controller
{
    protected $productService;

    public function __construct(ProductSearchService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Search Amazon by URL or ASIN
     */
    public function searchByUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid Amazon URL or ASIN.',
            ], 400);
        }

        $result = $this->productService->searchByUrl($request->url);

        if ($result && isset($result['product']) && $result['product']) {
            // Convert price to PHP
            $product = $result['product'];
            if (isset($product['price']) && isset($product['currency'])) {
                $product['price_php'] = $this->productService->convertToPHP(
                    $product['price'],
                    $product['currency']
                );
            }
            // Ensure variations key exists so frontend can sync Style, Color, Size, etc.
            if (! isset($product['variations']) || ! is_array($product['variations'])) {
                $product['variations'] = [];
            }

            return response()->json([
                'success' => true,
                'product' => $product,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Product not found. Please enter price manually.',
        ], 404);
    }

    /**
     * Search Amazon by product name
     */
    public function searchByName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a search query (minimum 3 characters).',
            ], 400);
        }

        // Get the query parameter and ensure it's not empty
        $query = trim($request->input('query', ''));
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query cannot be empty.',
            ], 400);
        }

        $result = $this->productService->searchByName($query);

        if ($result && $result['success'] && !empty($result['results'])) {
            // Convert prices to PHP
            foreach ($result['results'] as &$item) {
                if (isset($item['price']) && isset($item['currency'])) {
                    $item['price_php'] = $this->productService->convertToPHP(
                        $item['price'],
                        $item['currency']
                    );
                }
            }

            return response()->json([
                'success' => true,
                'results' => $result['results'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'No products found. Please enter price manually.',
            'results' => [],
        ]);
    }
}
