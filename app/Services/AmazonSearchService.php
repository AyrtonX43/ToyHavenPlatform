<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AmazonSearchService
{
    protected $accessKey;
    protected $secretKey;
    protected $partnerTag;
    protected $host;
    protected $region;
    protected $marketplace;

    public function __construct()
    {
        $this->accessKey = config('services.amazon.access_key');
        $this->secretKey = config('services.amazon.secret_key');
        $this->partnerTag = config('services.amazon.partner_tag');
        $this->host = config('services.amazon.host', 'webservices.amazon.com');
        $this->region = config('services.amazon.region', 'us-east-1');
        $this->marketplace = config('services.amazon.marketplace', 'www.amazon.com');
    }
    /**
     * Search Amazon by URL or ASIN
     * 
     * @param string $urlOrAsin Amazon product URL or ASIN
     * @return array|null Product details or null if not found
     */
    public function searchByUrl($urlOrAsin)
    {
        // Extract ASIN from URL if it's a URL
        $asin = $this->extractAsin($urlOrAsin);
        
        if (!$asin) {
            return null;
        }

        return $this->fetchProductDetails($asin);
    }

    /**
     * Search Amazon by product name
     * 
     * @param string $query Product name to search
     * @return array|null Search results or null if error
     */
    public function searchByName($query)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Amazon API credentials not configured. Please enter price manually.',
                'results' => []
            ];
        }

        try {
            $payload = [
                'Keywords' => $query,
                'SearchIndex' => 'ToysAndGames',
                'ItemCount' => 10,
                'Resources' => [
                    'ItemInfo.Title',
                    'ItemInfo.ByLineInfo',
                    'ItemInfo.Classifications',
                    'ItemInfo.Images',
                    'Offers.Listings.Price',
                    'Offers.Listings.Availability',
                ],
                'PartnerTag' => $this->partnerTag,
                'PartnerType' => 'Associates',
                'Marketplace' => $this->marketplace,
            ];

            $response = $this->makeApiRequest('com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems', $payload);

            if ($response && isset($response['SearchResult'])) {
                return $this->parseSearchResults($response);
            }

            return [
                'success' => false,
                'message' => isset($response['__type']) ? ($response['Errors'][0]['Message'] ?? 'No products found.') : 'No products found.',
                'results' => []
            ];
        } catch (\Exception $e) {
            Log::error('Amazon search error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error searching Amazon. Please enter price manually.',
                'results' => []
            ];
        }
    }

    /**
     * Fetch product details by ASIN
     * 
     * @param string $asin Amazon ASIN
     * @return array|null Product details or null
     */
    public function fetchProductDetails($asin)
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Amazon API credentials not configured. Please enter price manually.',
                'product' => null
            ];
        }

        try {
            $payload = [
                'ItemIds' => [$asin],
                'Resources' => [
                    'ItemInfo.Title',
                    'ItemInfo.ByLineInfo',
                    'ItemInfo.Classifications',
                    'ItemInfo.Images',
                    'ItemInfo.Features',
                    'ItemInfo.ProductInfo',
                    'Offers.Listings.Price',
                    'Offers.Listings.Availability',
                    'Offers.Listings.MerchantInfo',
                ],
                'PartnerTag' => $this->partnerTag,
                'PartnerType' => 'Associates',
                'Marketplace' => $this->marketplace,
            ];

            $response = $this->makeApiRequest('com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems', $payload);

            if ($response && isset($response['ItemsResult']['Items'][0])) {
                return $this->parseProductDetails($response);
            }

            return [
                'success' => false,
                'message' => isset($response['__type']) ? ($response['Errors'][0]['Message'] ?? 'Product not found.') : 'Product not found.',
                'product' => null
            ];
        } catch (\Exception $e) {
            Log::error('Amazon fetch error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fetching product details. Please enter price manually.',
                'product' => null
            ];
        }
    }

    /**
     * Extract ASIN from Amazon URL
     * 
     * @param string $url Amazon product URL or ASIN
     * @return string|null ASIN or null if not found
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

    /**
     * Check if Amazon API is configured
     */
    private function isConfigured(): bool
    {
        return !empty($this->accessKey) && !empty($this->secretKey) && !empty($this->partnerTag);
    }

    /**
     * Make API request to Amazon Product Advertising API 5.0
     */
    private function makeApiRequest(string $operation, array $payload): ?array
    {
        $endpoint = '/paapi5/' . strtolower(str_replace('com.amazon.paapi5.v1.ProductAdvertisingAPIv1.', '', $operation));
        $url = 'https://' . $this->host . $endpoint;
        
        $timestamp = Carbon::now('UTC')->format('Ymd\THis\Z');
        $date = Carbon::now('UTC')->format('Ymd');
        
        $headers = [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Amz-Target' => $operation,
            'X-Amz-Date' => $timestamp,
        ];

        $canonicalRequest = $this->createCanonicalRequest('POST', $endpoint, $headers, json_encode($payload));
        $stringToSign = $this->createStringToSign($timestamp, $date, $canonicalRequest);
        $signature = $this->calculateSignature($stringToSign, $date);
        $authorization = $this->createAuthorizationHeader($timestamp, $date, $signature);
        
        $headers['Authorization'] = $authorization;

        $response = Http::withHeaders($headers)
            ->timeout(10)
            ->post($url, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Amazon API error', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response->json();
    }

    /**
     * Create canonical request for AWS Signature Version 4
     */
    private function createCanonicalRequest(string $method, string $uri, array $headers, string $payload): string
    {
        $canonicalHeaders = [];
        $signedHeaders = [];
        
        // Add host header
        $headers['Host'] = $this->host;
        
        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            $canonicalHeaders[$lowerKey] = trim($value);
            $signedHeaders[] = $lowerKey;
        }
        
        ksort($canonicalHeaders);
        sort($signedHeaders);
        
        $canonicalHeadersString = '';
        foreach ($canonicalHeaders as $key => $value) {
            $canonicalHeadersString .= $key . ':' . $value . "\n";
        }
        
        $signedHeadersString = implode(';', $signedHeaders);
        $payloadHash = hash('sha256', $payload);
        
        return $method . "\n" . $uri . "\n\n" . $canonicalHeadersString . "\n" . $signedHeadersString . "\n" . $payloadHash;
    }

    /**
     * Create string to sign
     */
    private function createStringToSign(string $timestamp, string $date, string $canonicalRequest): string
    {
        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = $date . '/' . $this->region . '/ProductAdvertisingAPI/aws4_request';
        $canonicalRequestHash = hash('sha256', $canonicalRequest);
        
        return $algorithm . "\n" . $timestamp . "\n" . $credentialScope . "\n" . $canonicalRequestHash;
    }

    /**
     * Calculate signature
     */
    private function calculateSignature(string $stringToSign, string $date): string
    {
        $kDate = hash_hmac('sha256', $date, 'AWS4' . $this->secretKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 'ProductAdvertisingAPI', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        
        return hash_hmac('sha256', $stringToSign, $kSigning);
    }

    /**
     * Create authorization header
     */
    private function createAuthorizationHeader(string $timestamp, string $date, string $signature): string
    {
        $credentialScope = $date . '/' . $this->region . '/ProductAdvertisingAPI/aws4_request';
        $algorithm = 'AWS4-HMAC-SHA256';
        
        return $algorithm . ' ' .
               'Credential=' . $this->accessKey . '/' . $credentialScope . ', ' .
               'SignedHeaders=content-type;host;x-amz-date;x-amz-target, ' .
               'Signature=' . $signature;
    }

    /**
     * Parse search results from API response
     * 
     * @param array $response API response
     * @return array Parsed results
     */
    private function parseSearchResults($response)
    {
        $results = [];
        
        if (isset($response['SearchResult']['Items'])) {
            foreach ($response['SearchResult']['Items'] as $item) {
                $parsed = $this->parseItem($item);
                if ($parsed) {
                    $results[] = $parsed;
                }
            }
        }

        return [
            'success' => true,
            'results' => $results
        ];
    }

    /**
     * Parse product details from API response
     * 
     * @param array $response API response
     * @return array Product details
     */
    private function parseProductDetails($response)
    {
        if (isset($response['ItemsResult']['Items'][0])) {
            $item = $response['ItemsResult']['Items'][0];
            $parsed = $this->parseItem($item);
            
            return [
                'success' => true,
                'product' => $parsed
            ];
        }

        return [
            'success' => false,
            'product' => null
        ];
    }

    /**
     * Parse individual item from API response
     */
    private function parseItem(array $item): ?array
    {
        $asin = $item['ASIN'] ?? null;
        $title = $item['ItemInfo']['Title']['DisplayValue'] ?? 'Unknown Product';
        
        // Get price
        $price = null;
        $currency = 'USD';
        
        if (isset($item['Offers']['Listings'][0]['Price']['Amount'])) {
            $price = (float) $item['Offers']['Listings'][0]['Price']['Amount'];
            $currency = $item['Offers']['Listings'][0]['Price']['Currency'] ?? 'USD';
        }
        
        // Get product URL
        $url = 'https://' . $this->marketplace . '/dp/' . $asin;
        
        // Get image - prefer Large, then Medium; normalize to HD (1500px) for zoom
        $image = null;
        if (isset($item['Images']['Primary']['Large']['URL'])) {
            $image = $this->imageUrlToHd($item['Images']['Primary']['Large']['URL']);
        } elseif (isset($item['Images']['Primary']['Medium']['URL'])) {
            $image = $this->imageUrlToHd($item['Images']['Primary']['Medium']['URL']);
        }

        // Amazon PA-API GetItems does not return child variation ASINs/prices in this call; include empty variations so frontend can add variant options when available from other sources
        $variations = [];

        return [
            'asin' => $asin,
            'title' => $title,
            'price' => $price,
            'currency' => $currency,
            'url' => $url,
            'image' => $image,
            'variations' => $variations,
        ];
    }

    /**
     * Convert Amazon (or similar) image URL to HD size for zoom/display.
     * Replaces common size params (e.g. _SL500_, _AC_SX200_) with _AC_SL1500_ for higher resolution.
     */
    public function imageUrlToHd(string $url): string
    {
        if ($url === '') {
            return $url;
        }
        // Amazon: _SL500_ → _SL1500_, _AC_SL500_ → _AC_SL1500_, _SX300_ → _AC_SL1500_
        $url = preg_replace('/_S[LX]\d+_/', '_AC_SL1500_', $url);
        $url = preg_replace('/_SL\d+_/', '_SL1500_', $url);
        $url = preg_replace('/_AC_SL\d+_/', '_AC_SL1500_', $url);
        return $url;
    }
}
