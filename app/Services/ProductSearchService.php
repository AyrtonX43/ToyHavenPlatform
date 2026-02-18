<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductSearchService
{
    protected $provider;
    
    public function __construct()
    {
        $this->provider = config('services.product_api.provider', 'scraperapi');
    }
    
    /**
     * Search product by URL or ASIN
     * 
     * @param string $urlOrAsin Amazon product URL or ASIN
     * @return array Product details or error response
     */
    public function searchByUrl($urlOrAsin)
    {
        if ($this->provider === 'amazon') {
            $amazonService = app(AmazonSearchService::class);
            return $amazonService->searchByUrl($urlOrAsin);
        }
        if ($this->provider === 'scraperapi') {
            return $this->searchScraperApiByUrl($urlOrAsin);
        }
        return $this->searchCanopyByUrl($urlOrAsin);
    }
    
    /**
     * Search product by name
     * 
     * @param string $query Product name to search
     * @return array Search results or error response
     */
    public function searchByName($query)
    {
        // Ensure query is a string and not empty
        $query = is_string($query) ? trim($query) : (string) $query;
        
        if (empty($query)) {
            return [
                'success' => false,
                'message' => 'Search query cannot be empty.',
                'results' => []
            ];
        }
        
        Log::info('ProductSearchService::searchByName called', [
            'query' => $query,
            'query_type' => gettype($query),
            'provider' => $this->provider,
        ]);
        
        if ($this->provider === 'amazon') {
            $amazonService = app(AmazonSearchService::class);
            return $amazonService->searchByName($query);
        }
        if ($this->provider === 'scraperapi') {
            return $this->searchScraperApiByName($query);
        }
        return $this->searchCanopyByName($query);
    }
    
    /**
     * Search ScraperAPI by URL or ASIN (structured Amazon product endpoint)
     */
    private function searchScraperApiByUrl($urlOrAsin)
    {
        $apiKey = config('services.product_api.scraperapi.api_key');
        $baseUrl = config('services.product_api.scraperapi.base_url');
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'ScraperAPI key not configured. Please configure SCRAPER_API_KEY in your .env file.',
                'product' => null
            ];
        }
        try {
            $asin = $this->extractAsin($urlOrAsin);
            if (!$asin) {
                return [
                    'success' => false,
                    'message' => 'Invalid Amazon URL or ASIN. Please provide a valid Amazon product URL or ASIN.',
                    'product' => null
                ];
            }
            $url = rtrim($baseUrl, '/') . '/structured/amazon/product';
            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(30)->get($url, [
                'api_key' => $apiKey,
                'asin' => $asin,
                'tld' => 'com',
            ]);
            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    $product = $this->parseScraperApiProduct($data, $asin);
                    if ($product) {
                        return [
                            'success' => true,
                            'product' => $product
                        ];
                    }
                }
            }
            Log::error('ScraperAPI product error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'asin' => $asin,
            ]);
            return [
                'success' => false,
                'message' => 'Product not found. Please enter price manually.',
                'product' => null
            ];
        } catch (\Exception $e) {
            Log::error('ScraperAPI exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Error fetching product details. Please enter price manually.',
                'product' => null
            ];
        }
    }
    
    /**
     * Search ScraperAPI by product name (structured Amazon search endpoint)
     */
    private function searchScraperApiByName($query)
    {
        $apiKey = config('services.product_api.scraperapi.api_key');
        $baseUrl = config('services.product_api.scraperapi.base_url');
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'ScraperAPI key not configured. Please configure SCRAPER_API_KEY in your .env file.',
                'results' => []
            ];
        }
        try {
            $query = trim((string) $query);
            if (empty($query)) {
                return [
                    'success' => false,
                    'message' => 'Search query cannot be empty.',
                    'results' => []
                ];
            }
            $url = rtrim($baseUrl, '/') . '/structured/amazon/search';
            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(30)->get($url, [
                'api_key' => $apiKey,
                'query' => $query,
                'tld' => 'com',
            ]);
            if ($response->successful()) {
                $data = $response->json();
                $results = [];
                $items = is_array($data) ? $data : (isset($data['results']) ? $data['results'] : []);
                if (isset($data['results']) && is_array($data['results'])) {
                    $items = $data['results'];
                }
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $parsed = $this->parseScraperApiSearchResult($item);
                        if ($parsed) {
                            $results[] = $parsed;
                        }
                    }
                }
                if (!empty($results)) {
                    return [
                        'success' => true,
                        'results' => $results
                    ];
                }
            }
            Log::warning('ScraperAPI search returned no results', ['query' => $query]);
            return [
                'success' => false,
                'message' => 'No products found. Please try a different search term.',
                'results' => []
            ];
        } catch (\Exception $e) {
            Log::error('ScraperAPI search exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'query' => $query,
            ]);
            return [
                'success' => false,
                'message' => 'Error searching products. Please try again.',
                'results' => []
            ];
        }
    }
    
    /**
     * Parse ScraperAPI product response to app format
     */
    private function parseScraperApiProduct($data, $asin = null)
    {
        if (!is_array($data)) {
            return null;
        }
        $info = $data['product_information'] ?? [];
        $productAsin = $asin ?? ($info['asin'] ?? $data['asin'] ?? null);
        if (!$productAsin) {
            return null;
        }
        $title = $data['name'] ?? $info['title'] ?? 'Unknown Product';
        $description = $data['full_description'] ?? null;
        // Prefer product_information.brand (usually clean e.g. "LEGO"); normalize "Visit the X Store" from data.brand
        $brand = $info['brand'] ?? $data['brand'] ?? null;
        $brand = $this->normalizeAmazonBrand($brand);
        $featureBullets = $data['feature_bullets'] ?? [];
        $categories = isset($data['product_category']) ? [$data['product_category']] : [];
        $price = null;
        $currency = 'USD';
        if (!empty($data['pricing'])) {
            $p = $data['pricing'];
            $price = is_array($p) ? ($p['value'] ?? $p['amount'] ?? $p['current'] ?? null) : $p;
            $currency = is_array($p) ? ($p['currency'] ?? 'USD') : 'USD';
        }
        if ($price === null && !empty($data['list_price'])) {
            $price = is_array($data['list_price']) ? ($data['list_price']['value'] ?? $data['list_price']['amount'] ?? null) : $data['list_price'];
        }
        if (is_string($price)) {
            $price = preg_replace('/[^0-9.]/', '', $price);
            $price = $price !== '' ? (float) $price : null;
        }
        $images = $data['images'] ?? [];
        $image = is_array($images) && !empty($images) ? $this->imageUrlToHd(is_string($images[0]) ? $images[0] : ($images[0]['url'] ?? $images[0]['src'] ?? $images[0])) : null;
        $allImages = is_array($images) ? array_map(function ($img) { return $this->imageUrlToHd(is_string($img) ? $img : ($img['url'] ?? $img['src'] ?? $img ?? '')); }, array_filter($images)) : ($image ? [$image] : []);
        if ($image && !in_array($image, $allImages)) {
            array_unshift($allImages, $image);
        }
        $url = "https://www.amazon.com/dp/{$productAsin}";
        if (empty($description) && !empty($featureBullets)) {
            $description = implode("\n\n", array_slice($featureBullets, 0, 5));
        }
        $sku = 'SKU-' . $productAsin;

        // Extract variations (multiple variant options with prices from Amazon reference)
        $variations = $this->parseScraperApiVariations($data, $price);

        return [
            'asin' => $productAsin,
            'title' => $title,
            'description' => $description,
            'brand' => $brand,
            'sku' => $sku,
            'categories' => $categories,
            'price' => $price,
            'currency' => $currency,
            'url' => $url,
            'image' => $image,
            'images' => $allImages,
            'videos' => [],
            'variations' => $variations,
        ];
    }

    /**
     * Parse variation options with prices from ScraperAPI (or similar) product response.
     * Syncs Color, Size, Style, and other options from Amazon reference so they auto-fill product variations.
     *
     * @param array $data Raw API response
     * @param float|null $basePrice Main product price for computing price_adjustment
     * @return array List of variation entries: type, value, price_adjustment, stock_quantity
     */
    private function parseScraperApiVariations(array $data, $basePrice): array
    {
        $variations = [];
        $seen = []; // avoid duplicate type+value
        $baseFloat = $basePrice !== null ? (float) $basePrice : 0;

        $add = function ($type, $value, $priceAdj = 0, $stock = 0) use (&$variations, &$seen) {
            $type = trim((string) $type) ?: 'Variant';
            $value = trim((string) $value);
            if ($value === '') {
                return;
            }
            $key = strtolower($type) . '|' . strtolower($value);
            if (isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;
            $variations[] = [
                'type' => $type,
                'value' => $value,
                'price_adjustment' => (float) $priceAdj,
                'stock_quantity' => (int) $stock,
            ];
        };

        // 1) Top-level variations array
        if (isset($data['variations']) && is_array($data['variations'])) {
            foreach ($data['variations'] as $v) {
                $value = $v['value'] ?? $v['displayValue'] ?? $v['name'] ?? '';
                $type = $v['type'] ?? $v['variation_type'] ?? $v['attribute'] ?? 'Variant';
                $variantPrice = null;
                if (isset($v['price'])) {
                    $variantPrice = is_array($v['price']) ? ($v['price']['value'] ?? $v['price']['amount'] ?? null) : $v['price'];
                }
                if (isset($v['price_value'])) {
                    $variantPrice = $v['price_value'];
                }
                if (is_string($variantPrice)) {
                    $variantPrice = preg_replace('/[^0-9.]/', '', $variantPrice);
                    $variantPrice = $variantPrice !== '' ? (float) $variantPrice : null;
                }
                $priceAdjustment = isset($v['price_adjustment']) ? (float) $v['price_adjustment'] : null;
                if ($priceAdjustment === null && $variantPrice !== null && $baseFloat > 0) {
                    $priceAdjustment = round($variantPrice - $baseFloat, 2);
                }
                $add($type, $value, $priceAdjustment ?? 0, $v['stock_quantity'] ?? $v['availability'] ?? 0);
            }
        }

        // 2) variation_attributes: { "Style": ["A","B"], "Color": ["Red"], "Size": ["M"] } – sync Style, Color, Size, etc.
        $variationAttrs = $data['variation_attributes'] ?? $data['variationAttributes'] ?? [];
        if (is_array($variationAttrs)) {
            foreach ($variationAttrs as $attrName => $options) {
                if (! is_array($options)) {
                    $options = $options !== null && $options !== '' ? [$options] : [];
                }
                $type = is_string($attrName) ? $attrName : 'Variant';
                foreach ($options as $opt) {
                    $value = is_array($opt) ? ($opt['value'] ?? $opt['name'] ?? $opt['displayValue'] ?? '') : (string) $opt;
                    $value = trim($value);
                    if ($value !== '') {
                        $add($type, $value, 0, 0);
                    }
                }
            }
        }

        // 3) product_variations (each variant with attributes and optional price)
        if (isset($data['product_variations']) && is_array($data['product_variations'])) {
            foreach ($data['product_variations'] as $pv) {
                $attrs = $pv['attributes'] ?? $pv['variation_attributes'] ?? $pv['dimensions'] ?? [];
                $variantPrice = null;
                if (isset($pv['price'])) {
                    $variantPrice = is_array($pv['price']) ? ($pv['price']['value'] ?? $pv['price']['amount'] ?? null) : $pv['price'];
                }
                if (is_string($variantPrice)) {
                    $variantPrice = preg_replace('/[^0-9.]/', '', $variantPrice);
                    $variantPrice = $variantPrice !== '' ? (float) $variantPrice : null;
                }
                $priceAdjustment = 0;
                if ($variantPrice !== null && $baseFloat > 0) {
                    $priceAdjustment = round($variantPrice - $baseFloat, 2);
                }
                foreach ($attrs as $attrName => $attrValue) {
                    $v = is_string($attrValue) ? $attrValue : (string) ($attrValue['value'] ?? $attrValue['name'] ?? $attrValue);
                    if ($v !== '') {
                        $add(is_string($attrName) ? $attrName : 'Variant', $v, $priceAdjustment, 0);
                    }
                }
                if (empty($attrs) && ($variantPrice !== null || isset($pv['asin']))) {
                    $add('Variant', $pv['title'] ?? $pv['asin'] ?? ('Option ' . (count($variations) + 1)), $priceAdjustment, 0);
                }
            }
        }

        // 4) Top-level style_options / styles / sizes / colors arrays (Amazon-style selectors)
        $topLevelKeys = [
            'styles' => 'Style', 'style_options' => 'Style', 'styleOptions' => 'Style',
            'sizes' => 'Size', 'colors' => 'Color', 'patterns' => 'Pattern', 'materials' => 'Material',
        ];
        foreach ($topLevelKeys as $key => $label) {
            $raw = $data[$key] ?? null;
            if ($raw === null || ! is_array($raw)) {
                continue;
            }
            foreach ($raw as $opt) {
                $value = is_array($opt) ? ($opt['value'] ?? $opt['name'] ?? $opt['displayValue'] ?? '') : (string) $opt;
                if (trim($value) !== '') {
                    $add($label, $value, 0, 0);
                }
            }
        }

        // 5) product_information: color, size, style, model, pattern, material (single or array)
        $info = $data['product_information'] ?? $data['productInformation'] ?? [];
        if (is_array($info)) {
            $infoKeys = [
                'color' => 'Color', 'colors' => 'Color', 'size' => 'Size', 'sizes' => 'Size',
                'style' => 'Style', 'styles' => 'Style', 'model' => 'Model', 'pattern' => 'Pattern',
                'patterns' => 'Pattern', 'material' => 'Material', 'materials' => 'Material',
            ];
            foreach ($infoKeys as $key => $label) {
                $raw = $info[$key] ?? null;
                if ($raw === null) {
                    continue;
                }
                $options = is_array($raw) ? $raw : [trim((string) $raw)];
                foreach ($options as $opt) {
                    $value = is_array($opt) ? ($opt['value'] ?? $opt['name'] ?? '') : (string) $opt;
                    if (trim($value) !== '') {
                        $add($label, $value, 0, 0);
                    }
                }
            }
        }

        // 6) variant_links (links to all variants – use title or "Variant N")
        $variantLinks = $data['variant_links'] ?? $data['variantLinks'] ?? $data['variation_links'] ?? [];
        if (is_array($variantLinks)) {
            $idx = 0;
            foreach ($variantLinks as $link) {
                $idx++;
                if (is_string($link)) {
                    $add('Variant', 'Option ' . $idx, 0, 0);
                    continue;
                }
                $title = $link['title'] ?? $link['name'] ?? $link['label'] ?? null;
                $value = $title ?: ($link['asin'] ?? 'Option ' . $idx);
                $price = $link['price'] ?? null;
                $priceAdj = 0;
                if (is_numeric($price) && $baseFloat > 0) {
                    $priceAdj = round((float) $price - $baseFloat, 2);
                }
                $add('Variant', $value, $priceAdj, 0);
            }
        }

        return $variations;
    }
    
    /**
     * Parse ScraperAPI search result item to app format.
     * ScraperAPI search returns: asin, name, image (thumbnail URL), url, price, price_string, price_symbol, etc.
     */
    private function parseScraperApiSearchResult($data)
    {
        if (!is_array($data)) {
            return null;
        }
        $productAsin = $data['asin'] ?? null;
        if (!$productAsin) {
            return null;
        }
        $title = $data['name'] ?? $data['title'] ?? 'Unknown Product';
        $price = null;
        $currency = 'USD';
        if (isset($data['price'])) {
            $p = $data['price'];
            $price = is_array($p) ? ($p['value'] ?? $p['amount'] ?? null) : $p;
            $currency = is_array($p) ? ($p['currency'] ?? 'USD') : 'USD';
        }
        if ($price === null && isset($data['pricing'])) {
            $p = $data['pricing'];
            $price = is_array($p) ? ($p['value'] ?? $p['amount'] ?? null) : $p;
        }
        if ($price === null && isset($data['price_string'])) {
            $price = $data['price_string'];
        }
        if (isset($data['price_symbol']) && $data['price_symbol'] === '€') {
            $currency = 'EUR';
        } elseif (isset($data['price_symbol']) && $data['price_symbol'] === '£') {
            $currency = 'GBP';
        }
        if (is_string($price)) {
            $price = preg_replace('/[^0-9.]/', '', $price);
            $price = $price !== '' ? (float) $price : null;
        }
        // ScraperAPI search returns singular "image" for thumbnail; also support "images" array (use HD URLs)
        $image = $data['image'] ?? null;
        if (!$image && isset($data['images']) && is_array($data['images']) && !empty($data['images'])) {
            $image = is_string($data['images'][0]) ? $data['images'][0] : ($data['images'][0]['url'] ?? $data['images'][0]['src'] ?? null);
        }
        $image = $image ? $this->imageUrlToHd($image) : null;
        $allImages = $image ? [$image] : [];
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $img) {
                $url = is_string($img) ? $img : ($img['url'] ?? $img['src'] ?? null);
                if ($url) {
                    $url = $this->imageUrlToHd($url);
                    if (!in_array($url, $allImages)) {
                        $allImages[] = $url;
                    }
                }
            }
        }
        $url = $data['url'] ?? "https://www.amazon.com/dp/{$productAsin}";
        $brand = $data['brand'] ?? null;
        return [
            'asin' => $productAsin,
            'title' => $title,
            'brand' => $brand,
            'price' => $price,
            'currency' => $currency,
            'url' => $url,
            'image' => $image,
            'images' => $allImages,
        ];
    }
    
    /**
     * Search Canopy API by URL or ASIN
     */
    private function searchCanopyByUrl($urlOrAsin)
    {
        $apiKey = config('services.product_api.canopy.api_key');
        $restUrl = config('services.product_api.canopy.rest_url');
        
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'Canopy API key not configured. Please configure CANOPY_API_KEY in your .env file.',
                'product' => null
            ];
        }
        
        try {
            $asin = $this->extractAsin($urlOrAsin);
            if (!$asin) {
                return [
                    'success' => false,
                    'message' => 'Invalid Amazon URL or ASIN. Please provide a valid Amazon product URL or ASIN.',
                    'product' => null
                ];
            }
            
            // Use REST API endpoint
            $response = Http::withHeaders([
                'API-KEY' => $apiKey,
            ])->withOptions([
                'verify' => false, // Disable SSL verification for XAMPP/Windows
            ])->timeout(15)->get("{$restUrl}/api/amazon/product", [
                'asin' => $asin,
                'domain' => 'US',
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Parse Canopy API response - structure: data.amazonProduct
                if (isset($data['data']['amazonProduct'])) {
                    $product = $this->parseCanopyProduct($data['data']['amazonProduct'], $asin);
                } else {
                    $product = $this->parseCanopyProduct($data, $asin);
                }
                
                if ($product) {
                    return [
                        'success' => true,
                        'product' => $product
                    ];
                }
            }
            
            // Log error for debugging
            Log::error('Canopy API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'asin' => $asin,
            ]);
            
            return [
                'success' => false,
                'message' => 'Product not found. Please enter price manually.',
                'product' => null
            ];
        } catch (\Exception $e) {
            Log::error('Canopy API exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Error fetching product details. Please enter price manually.',
                'product' => null
            ];
        }
    }
    
    /**
     * Search Canopy API by product name
     */
    private function searchCanopyByName($query)
    {
        $apiKey = config('services.product_api.canopy.api_key');
        $restUrl = config('services.product_api.canopy.rest_url');
        
        if (!$apiKey) {
            return [
                'success' => false,
                'message' => 'Canopy API key not configured. Please configure CANOPY_API_KEY in your .env file.',
                'results' => []
            ];
        }
        
        try {
            // Ensure query is a non-empty string
            $query = trim((string) $query);
            if (empty($query)) {
                return [
                    'success' => false,
                    'message' => 'Search query cannot be empty.',
                    'results' => []
                ];
            }
            
            // Use REST API search endpoint - correct parameter is 'searchTerm'
            // Log the query to debug
            Log::info('Canopy API search request', [
                'query' => $query,
                'query_length' => strlen($query),
                'api_key_set' => !empty($apiKey),
                'rest_url' => $restUrl,
            ]);
            
            // Build query string manually to ensure proper encoding
            $queryParams = http_build_query([
                'searchTerm' => $query,
                'domain' => 'US',
                'limit' => 10,
            ]);
            
            $fullUrl = "{$restUrl}/api/amazon/search?{$queryParams}";
            
            Log::info('Canopy API full URL', ['url' => $fullUrl]);
            
            $response = Http::withHeaders([
                'API-KEY' => $apiKey,
            ])->withOptions([
                'verify' => false, // Disable SSL verification for XAMPP/Windows
            ])->timeout(15)->get($fullUrl);
            
            // Log the actual request details for debugging
            Log::info('Canopy API request completed', [
                'url' => "{$restUrl}/api/amazon/search",
                'searchTerm' => $query,
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $results = [];
                
                // Log response for debugging
                Log::info('Canopy API search response', [
                    'query' => $query,
                    'status' => $response->status(),
                    'response_structure' => is_array($data) ? array_keys($data) : 'not_array',
                    'has_data' => isset($data['data']),
                    'has_search_results' => isset($data['data']['amazonProductSearchResults']),
                ]);
                
                // Parse Canopy API search results - response structure: data.amazonProductSearchResults.productResults.results
                if (isset($data['data']['amazonProductSearchResults']['productResults']['results'])) {
                    foreach ($data['data']['amazonProductSearchResults']['productResults']['results'] as $item) {
                        $parsed = $this->parseCanopySearchResult($item);
                        if ($parsed) {
                            $results[] = $parsed;
                        }
                    }
                } elseif (isset($data['data']['amazonProductSearchResults']['productResults']) && is_array($data['data']['amazonProductSearchResults']['productResults'])) {
                    // Handle if results is directly in productResults
                    foreach ($data['data']['amazonProductSearchResults']['productResults'] as $item) {
                        $parsed = $this->parseCanopySearchResult($item);
                        if ($parsed) {
                            $results[] = $parsed;
                        }
                    }
                }
                
                if (!empty($results)) {
                    return [
                        'success' => true,
                        'results' => $results
                    ];
                } else {
                    // No results found but API call was successful
                    Log::warning('Canopy API search returned no results', [
                        'query' => $query,
                        'response_data' => $data,
                    ]);
                }
            } else {
                // Log error for debugging
                $errorBody = $response->body();
                $errorData = $response->json();
                
                Log::error('Canopy API search error', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'json' => $errorData,
                    'query' => $query,
                ]);
                
                // Check for specific error messages
                if (isset($errorData['errors']) && is_array($errorData['errors'])) {
                    $errorMessage = $errorData['errors'][0]['message'] ?? 'API request failed';
                    return [
                        'success' => false,
                        'message' => $errorMessage,
                        'results' => []
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'No products found. Please try a different search term.',
                'results' => []
            ];
        } catch (\Exception $e) {
            Log::error('Canopy API search exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'query' => $query,
            ]);
            
            return [
                'success' => false,
                'message' => 'Error searching products. Please try again.',
                'results' => []
            ];
        }
    }
    
    /**
     * Parse Canopy API search result (from search endpoint)
     */
    private function parseCanopySearchResult($data)
    {
        if (!is_array($data)) {
            return null;
        }
        
        // Extract ASIN
        $productAsin = $data['asin'] ?? null;
        
        if (!$productAsin) {
            return null;
        }
        
        // Extract title
        $title = $data['title'] ?? 'Unknown Product';
        
        // Extract price - Canopy API uses price.value and price.currency
        $price = null;
        $currency = 'USD';
        
        if (isset($data['price']) && is_array($data['price'])) {
            $price = $data['price']['value'] ?? null;
            $currency = $data['price']['currency'] ?? 'USD';
        }
        
        // Extract image URL - Canopy API search results use mainImageUrl (prefer HD)
        $image = $data['mainImageUrl'] ?? null;
        $image = $image ? $this->imageUrlToHd($image) : null;
        $allImages = [];
        if ($image) {
            $allImages[] = $image;
        }
        if (isset($data['imageUrls']) && is_array($data['imageUrls']) && !empty($data['imageUrls'])) {
            foreach ($data['imageUrls'] as $imgUrl) {
                if (!empty($imgUrl)) {
                    $imgUrl = $this->imageUrlToHd($imgUrl);
                    if (!in_array($imgUrl, $allImages)) {
                        $allImages[] = $imgUrl;
                    }
                }
            }
        }
        
        // Extract product URL
        $url = $data['url'] ?? "https://www.amazon.com/dp/{$productAsin}";
        
        // Extract additional fields for search results (limited data)
        $brand = $data['brand'] ?? null;
        
        return [
            'asin' => $productAsin,
            'title' => $title,
            'brand' => $brand,
            'price' => $price,
            'currency' => $currency,
            'url' => $url,
            'image' => $image, // Main image (for backward compatibility)
            'images' => $allImages, // All images array
        ];
    }
    
    /**
     * Parse Canopy API product response (from product endpoint)
     */
    private function parseCanopyProduct($data, $asin = null)
    {
        // Handle different response formats from Canopy API
        $product = null;
        
        if (!is_array($data)) {
            return null;
        }
        
        // Extract ASIN - try multiple possible keys
        $productAsin = $asin ?? $data['asin'] ?? $data['ASIN'] ?? $data['productId'] ?? null;
        
        // If still no ASIN, try to extract from URL if present
        if (!$productAsin && isset($data['url'])) {
            $extractedAsin = $this->extractAsin($data['url']);
            if ($extractedAsin) {
                $productAsin = $extractedAsin;
            }
        }
        
        // If we still don't have an ASIN, skip this product
        if (!$productAsin) {
            return null;
        }
        
        // Extract title - try multiple possible keys
        $title = $data['title'] ?? $data['Title'] ?? $data['name'] ?? $data['productTitle'] ?? 'Unknown Product';
        
        // Extract description/subtitle
        $description = $data['description'] ?? $data['Description'] ?? $data['subtitle'] ?? $data['Subtitle'] ?? null;
        
        // Extract brand and normalize "Visit the X Store" to "X"
        $brand = $this->normalizeAmazonBrand($data['brand'] ?? $data['Brand'] ?? null);
        
        // Extract feature bullets (can be used for description if description is empty)
        $featureBullets = $data['featureBullets'] ?? $data['features'] ?? [];
        
        // Extract categories (for category matching)
        $categories = $data['categories'] ?? [];
        
            // Extract price - Canopy API uses price.value and price.currency
            $price = null;
            $currency = 'USD';
            
            // Try different price field names and formats
            if (isset($data['price'])) {
                if (is_array($data['price'])) {
                    $price = $data['price']['value'] ?? $data['price']['amount'] ?? $data['price']['display'] ?? $data['price']['current'] ?? null;
                    $currency = $data['price']['currency'] ?? $data['price']['Currency'] ?? 'USD';
                } else {
                    $price = $data['price'];
                }
            } elseif (isset($data['Price'])) {
                if (is_array($data['Price'])) {
                    $price = $data['Price']['value'] ?? $data['Price']['Amount'] ?? $data['Price']['Display'] ?? $data['Price']['Current'] ?? null;
                    $currency = $data['Price']['Currency'] ?? 'USD';
                } else {
                    $price = $data['Price'];
                }
            } elseif (isset($data['currentPrice'])) {
                $price = is_array($data['currentPrice']) ? ($data['currentPrice']['value'] ?? $data['currentPrice']['amount'] ?? null) : $data['currentPrice'];
            } elseif (isset($data['listPrice'])) {
                $price = is_array($data['listPrice']) ? ($data['listPrice']['value'] ?? $data['listPrice']['amount'] ?? null) : $data['listPrice'];
            }
        
        // Convert price string to float if needed
        if (is_string($price)) {
            // Remove currency symbols and extract numeric value
            $price = preg_replace('/[^0-9.]/', '', $price);
            $price = $price ? (float) $price : null;
        }
        
        // Extract image URL - try multiple possible keys (prefer HD)
        $image = $data['mainImageUrl'] ?? $data['image'] ?? $data['Image'] ?? $data['imageUrl'] ?? $data['thumbnail'] ?? null;
        if (is_array($image)) {
            $image = $image['url'] ?? $image['large'] ?? $image[0] ?? null;
        }
        if (!$image && isset($data['images']) && is_array($data['images'])) {
            $image = $data['images'][0]['url'] ?? $data['images'][0] ?? null;
        }
        $image = $image ? $this->imageUrlToHd($image) : null;
        $allImages = [];
        if ($image) {
            $allImages[] = $image;
        }
        if (isset($data['imageUrls']) && is_array($data['imageUrls']) && !empty($data['imageUrls'])) {
            foreach ($data['imageUrls'] as $imgUrl) {
                if (!empty($imgUrl)) {
                    $imgUrl = $this->imageUrlToHd($imgUrl);
                    if (!in_array($imgUrl, $allImages)) {
                        $allImages[] = $imgUrl;
                    }
                }
            }
        }
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $img) {
                $imgUrl = is_array($img) ? ($img['url'] ?? $img['large'] ?? $img[0] ?? null) : $img;
                if (!empty($imgUrl)) {
                    $imgUrl = $this->imageUrlToHd($imgUrl);
                    if (!in_array($imgUrl, $allImages)) {
                        $allImages[] = $imgUrl;
                    }
                }
            }
        }
        
        // Extract video URLs (if available in API response)
        $videos = [];
        if (isset($data['videos']) && is_array($data['videos'])) {
            foreach ($data['videos'] as $video) {
                $videoUrl = is_array($video) ? ($video['url'] ?? $video['src'] ?? $video[0] ?? null) : $video;
                if (!empty($videoUrl)) {
                    $videos[] = $videoUrl;
                }
            }
        }
        
        // Extract video from videoUrl field
        if (isset($data['videoUrl']) && !empty($data['videoUrl']) && !in_array($data['videoUrl'], $videos)) {
            $videos[] = $data['videoUrl'];
        }
        
        // Extract variations (sizes, colors, styles, etc.)
        $variations = [];
        
        // Check for variations in different possible formats
        if (isset($data['variations']) && is_array($data['variations'])) {
            foreach ($data['variations'] as $variation) {
                $variations[] = [
                    'type' => $variation['type'] ?? $variation['name'] ?? 'Unknown',
                    'value' => $variation['value'] ?? $variation['displayValue'] ?? '',
                    'price_adjustment' => $variation['price_adjustment'] ?? $variation['priceDifference'] ?? 0,
                    'stock_quantity' => $variation['stock_quantity'] ?? $variation['availability'] ?? 0,
                ];
            }
        }
        
        // Check for size variations
        if (isset($data['sizes']) && is_array($data['sizes'])) {
            foreach ($data['sizes'] as $size) {
                $variations[] = [
                    'type' => 'Size',
                    'value' => is_array($size) ? ($size['value'] ?? $size['name'] ?? '') : $size,
                    'price_adjustment' => is_array($size) ? ($size['price_adjustment'] ?? 0) : 0,
                    'stock_quantity' => is_array($size) ? ($size['stock_quantity'] ?? 0) : 0,
                ];
            }
        }
        
        // Check for color variations
        if (isset($data['colors']) && is_array($data['colors'])) {
            foreach ($data['colors'] as $color) {
                $variations[] = [
                    'type' => 'Color',
                    'value' => is_array($color) ? ($color['value'] ?? $color['name'] ?? '') : $color,
                    'price_adjustment' => is_array($color) ? ($color['price_adjustment'] ?? 0) : 0,
                    'stock_quantity' => is_array($color) ? ($color['stock_quantity'] ?? 0) : 0,
                ];
            }
        }
        
        // Extract product URL
        $url = $data['url'] ?? $data['Url'] ?? $data['productUrl'] ?? "https://www.amazon.com/dp/{$productAsin}";
        
        // Build description from available sources
        if (empty($description) && !empty($featureBullets) && is_array($featureBullets)) {
            $description = implode("\n\n", array_slice($featureBullets, 0, 5)); // Use first 5 features
        }
        
        // Generate SKU from ASIN (prefix with SKU-)
        $sku = 'SKU-' . $productAsin;
        
        $product = [
            'asin' => $productAsin,
            'title' => $title,
            'description' => $description,
            'brand' => $brand,
            'sku' => $sku,
            'categories' => $categories,
            'price' => $price,
            'currency' => $currency,
            'url' => $url,
            'image' => $image, // Main image (for backward compatibility)
            'images' => $allImages, // All images array
            'videos' => $videos, // All videos array
            'variations' => $variations, // Product variations
        ];
        
        return $product;
    }
    
    /**
     * Normalize Amazon brand string to actual brand name.
     * API often returns "Visit the LEGO Store" or "Visit the Hot Wheels Store" — extract "LEGO", "Hot Wheels".
     */
    private function normalizeAmazonBrand($brand)
    {
        if ($brand === null || $brand === '') {
            return null;
        }
        $brand = trim((string) $brand);
        if ($brand === '') {
            return null;
        }
        // Match "Visit the X Store" or "Visit the X store" and return X
        if (preg_match('/^Visit the (.+?) Store$/i', $brand, $m)) {
            return trim($m[1]);
        }
        return $brand;
    }

    /**
     * Convert Amazon-style image URL to HD/HDR size (1500px) for zoom/display and seller import.
     * Non-Amazon URLs are returned unchanged.
     */
    private function imageUrlToHd($url)
    {
        if ($url === null || $url === '') {
            return $url;
        }
        $url = (string) $url;
        if (strpos($url, 'media-amazon.com') === false && strpos($url, 'images-amazon.com') === false) {
            return $url;
        }
        // Prefer 1500px for HD; also normalize _SX_/_SY_ thumbnails to _AC_SL1500_
        $url = preg_replace('/_S[LX]\d+_/', '_AC_SL1500_', $url);
        $url = preg_replace('/_SL\d+_/', '_SL1500_', $url);
        $url = preg_replace('/_AC_SL\d+_/', '_AC_SL1500_', $url);
        return $url;
    }

    /**
     * Extract ASIN from Amazon URL
     */
    private function extractAsin($url)
    {
        // If it's already an ASIN (10 characters, alphanumeric)
        if (preg_match('/^[A-Z0-9]{10}$/i', trim($url))) {
            return strtoupper(trim($url));
        }
        
        // Extract ASIN from URL patterns
        // Pattern 1: /dp/ASIN or /product/ASIN
        if (preg_match('/\/(?:dp|product)\/([A-Z0-9]{10})/i', $url, $matches)) {
            return strtoupper($matches[1]);
        }
        
        // Pattern 2: /gp/product/ASIN
        if (preg_match('/\/gp\/product\/([A-Z0-9]{10})/i', $url, $matches)) {
            return strtoupper($matches[1]);
        }
        
        // Pattern 3: ASIN in query parameter
        if (preg_match('/[?&]asin=([A-Z0-9]{10})/i', $url, $matches)) {
            return strtoupper($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Convert price to PHP (Philippine Peso)
     * 
     * @param float $price Original price
     * @param string $currency Original currency code
     * @return float Price in PHP
     */
    public function convertToPHP($price, $currency = 'USD')
    {
        if ($currency === 'PHP') {
            return $price;
        }
        
        // For now, use a simple conversion rate
        // In production, use a currency conversion API
        $conversionRates = [
            'USD' => 56.0, // Example: 1 USD = 56 PHP
            'EUR' => 60.0,
            'GBP' => 70.0,
        ];
        
        $rate = $conversionRates[$currency] ?? 56.0;
        return round($price * $rate, 2);
    }
}
