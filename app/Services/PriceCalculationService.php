<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class PriceCalculationService
{
    /**
     * Calculate the final selling price with admin commission and taxes
     * 
     * @param float $basePrice The base price set by the seller
     * @param float|null $amazonPrice Optional Amazon reference price
     * @return array Price breakdown with commission and taxes
     */
    public function calculatePrice($basePrice, $amazonPrice = null)
    {
        // Get system settings
        $commissionRate = (float) SystemSetting::get('commission_rate', 5.0); // Default 5%
        $taxRate = (float) SystemSetting::get('tax_rate', 12.0); // Default 12% VAT (Philippines)
        $transactionFee = (float) SystemSetting::get('transaction_fee', 0.0); // Default 0

        // Calculate commission (percentage of base price)
        $adminCommission = ($basePrice * $commissionRate) / 100;

        // Calculate tax (percentage of base price + commission)
        $taxAmount = (($basePrice + $adminCommission) * $taxRate) / 100;

        // Calculate final price (what customer pays)
        $finalPrice = $basePrice + $adminCommission + $taxAmount + $transactionFee;

        // Calculate seller earnings (what seller receives)
        $sellerEarnings = $basePrice - $adminCommission;

        return [
            'base_price' => round($basePrice, 2),
            'amazon_reference_price' => $amazonPrice ? round($amazonPrice, 2) : null,
            'admin_commission_rate' => $commissionRate,
            'admin_commission' => round($adminCommission, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'transaction_fee' => round($transactionFee, 2),
            'final_price' => round($finalPrice, 2),
            'seller_earnings' => round($sellerEarnings, 2),
            'breakdown' => [
                'base_price' => round($basePrice, 2),
                'admin_commission' => round($adminCommission, 2),
                'tax' => round($taxAmount, 2),
                'transaction_fee' => round($transactionFee, 2),
                'total' => round($finalPrice, 2),
            ],
        ];
    }

    /**
     * Calculate price breakdown for display
     * 
     * @param float $basePrice
     * @param float|null $amazonPrice
     * @return array
     */
    public function getPriceBreakdown($basePrice, $amazonPrice = null)
    {
        $calculation = $this->calculatePrice($basePrice, $amazonPrice);

        return [
            'base_price' => $calculation['base_price'],
            'amazon_reference_price' => $calculation['amazon_reference_price'],
            'admin_commission' => $calculation['admin_commission'],
            'admin_commission_rate' => $calculation['admin_commission_rate'],
            'tax_amount' => $calculation['tax_amount'],
            'tax_rate' => $calculation['tax_rate'],
            'transaction_fee' => $calculation['transaction_fee'],
            'final_price' => $calculation['final_price'],
            'seller_earnings' => $calculation['seller_earnings'],
        ];
    }

    /**
     * Calculate reverse price (what base price should be to get desired final price)
     * 
     * @param float $desiredFinalPrice The final price the seller wants to charge
     * @return array
     */
    public function calculateReversePrice($desiredFinalPrice)
    {
        $commissionRate = (float) SystemSetting::get('commission_rate', 5.0);
        $taxRate = (float) SystemSetting::get('tax_rate', 12.0);
        $transactionFee = (float) SystemSetting::get('transaction_fee', 0.0);

        // Reverse calculation:
        // finalPrice = basePrice + (basePrice * commissionRate/100) + ((basePrice + commission) * taxRate/100) + transactionFee
        // Solving for basePrice:
        // finalPrice - transactionFee = basePrice * (1 + commissionRate/100) * (1 + taxRate/100)
        
        $adjustedPrice = $desiredFinalPrice - $transactionFee;
        $denominator = (1 + $commissionRate / 100) * (1 + $taxRate / 100);
        $basePrice = $adjustedPrice / $denominator;

        return $this->calculatePrice($basePrice);
    }

    /**
     * Adjust price with custom commission and tax rates
     * 
     * @param float $basePrice
     * @param float|null $customCommissionRate
     * @param float|null $customTaxRate
     * @return array
     */
    public function calculateWithCustomRates($basePrice, $customCommissionRate = null, $customTaxRate = null)
    {
        $commissionRate = $customCommissionRate ?? (float) SystemSetting::get('commission_rate', 5.0);
        $taxRate = $customTaxRate ?? (float) SystemSetting::get('tax_rate', 12.0);
        $transactionFee = (float) SystemSetting::get('transaction_fee', 0.0);

        $adminCommission = ($basePrice * $commissionRate) / 100;
        $taxAmount = (($basePrice + $adminCommission) * $taxRate) / 100;
        $finalPrice = $basePrice + $adminCommission + $taxAmount + $transactionFee;
        $sellerEarnings = $basePrice - $adminCommission;

        return [
            'base_price' => round($basePrice, 2),
            'admin_commission_rate' => $commissionRate,
            'admin_commission' => round($adminCommission, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'transaction_fee' => round($transactionFee, 2),
            'final_price' => round($finalPrice, 2),
            'seller_earnings' => round($sellerEarnings, 2),
        ];
    }
}
