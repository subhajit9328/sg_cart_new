<?php

namespace SGCart\Tax\Services;

use SGCart\Tax\Models\TaxRate;
use SGCart\Tax\Models\TaxSetting;

class TaxCalculator
{
    /**
     * Resolve and calculate the applicable tax for a given subtotal and optional address details.
     * Returns an array with 'amount', 'rate', 'type', 'name', and 'label'.
     */
    public function calculate(float $subtotal, ?array $address = null): array
    {
        $mode = TaxSetting::getVal('tax_calculation_mode', 'single_standard');
        
        $resolvedRate = null;

        if ($mode === 'regional_dynamic' && !empty($address)) {
            $country = $address['country'] ?? null;
            $state = $address['state'] ?? null;
            $zip = $address['zip'] ?? null;

            // Search for regional rates
            $rates = TaxRate::where('is_active', true)
                ->where(function($query) {
                    $query->whereNotNull('country')
                        ->orWhereNotNull('state')
                        ->orWhereNotNull('zip');
                })
                ->get();

            // Find best match by specificity
            $bestMatch = null;
            $bestScore = -1;

            foreach ($rates as $rate) {
                $score = 0;
                
                // Match zip
                if ($rate->zip) {
                    if (strcasecmp(trim($rate->zip), trim($zip)) === 0) {
                        $score += 4;
                    } else {
                        continue; // Mismatch zip
                    }
                }
                
                // Match state
                if ($rate->state) {
                    if (strcasecmp(trim($rate->state), trim($state)) === 0) {
                        $score += 2;
                    } else {
                        continue; // Mismatch state
                    }
                }

                // Match country
                if ($rate->country) {
                    if (strcasecmp(trim($rate->country), trim($country)) === 0) {
                        $score += 1;
                    } else {
                        continue; // Mismatch country
                    }
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $rate;
                }
            }

            if ($bestMatch) {
                $resolvedRate = $bestMatch;
            }
        }

        // Fallback to first global rate or first active rate if no regional match
        if (!$resolvedRate) {
            $resolvedRate = TaxRate::where('is_active', true)
                ->whereNull('country')
                ->whereNull('state')
                ->whereNull('zip')
                ->first();
        }

        if (!$resolvedRate) {
            // Fallback to absolute first active rate in DB
            $resolvedRate = TaxRate::where('is_active', true)->first();
        }

        if ($resolvedRate) {
            $taxAmount = $resolvedRate->calculateTax($subtotal);
            $rateStr = $resolvedRate->type === 'percent' ? $resolvedRate->rate . '%' : '₹' . $resolvedRate->rate;
            return [
                'amount' => $taxAmount,
                'rate' => (float) $resolvedRate->rate,
                'type' => $resolvedRate->type,
                'name' => $resolvedRate->name,
                'label' => $resolvedRate->name . ' (' . $rateStr . ')'
            ];
        }

        // Standard hardcoded fallback (8%) if no rates in database
        return [
            'amount' => round($subtotal * 0.08, 2),
            'rate' => 8.00,
            'type' => 'percent',
            'name' => 'Tax',
            'label' => 'Tax (8%)'
        ];
    }
}
