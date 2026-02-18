<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PriceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PriceCalculationController extends Controller
{
    protected $priceService;

    public function __construct(PriceCalculationService $priceService)
    {
        $this->priceService = $priceService;
    }

    /**
     * Calculate price with commission and taxes
     */
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'base_price' => 'required|numeric|min:0',
            'amazon_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $calculation = $this->priceService->calculatePrice(
            $request->base_price,
            $request->amazon_price
        );

        return response()->json([
            'success' => true,
            'data' => $calculation,
        ]);
    }

    /**
     * Calculate reverse price (what base price should be for desired final price)
     */
    public function calculateReverse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'final_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $calculation = $this->priceService->calculateReversePrice($request->final_price);

        return response()->json([
            'success' => true,
            'data' => $calculation,
        ]);
    }
}
