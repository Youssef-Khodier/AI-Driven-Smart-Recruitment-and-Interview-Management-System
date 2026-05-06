<?php

namespace App\Services;

/**
 * Calculates offer compensation packages based on offer type and role level.
 * Uses configurable multipliers for bonuses and stock options.
 */
final class OfferPackageCalculator
{
    // Base salary ranges by offer type (annual, in currency units)
    private const BASE_RANGES = [
        'FULL_TIME' => ['min' => 40000, 'max' => 200000],
        'CONTRACT'  => ['min' => 50000, 'max' => 250000],
        'INTERN'    => ['min' => 15000, 'max' => 40000],
    ];

    // Bonus percentage of base salary by offer type
    private const BONUS_RATES = [
        'FULL_TIME' => 0.15,
        'CONTRACT'  => 0.05,
        'INTERN'    => 0.0,
    ];

    // Stock option percentage of base salary
    private const STOCK_RATES = [
        'FULL_TIME' => 0.10,
        'CONTRACT'  => 0.0,
        'INTERN'    => 0.0,
    ];

    /**
     * Calculate a recommended offer package.
     *
     * @param string $offerType FULL_TIME|CONTRACT|INTERN
     * @param float $baseSalary The proposed base salary (CTC)
     * @param float|null $overrideBonus If set, use this instead of calculated bonus
     * @param float|null $overrideStock If set, use this instead of calculated stock
     * @return array ['ctc' => float, 'bonus' => float, 'stock_options' => float, 'total_compensation' => float, 'warnings' => array]
     */
    public function calculate(string $offerType, float $baseSalary, ?float $overrideBonus = null, ?float $overrideStock = null): array
    {
        $warnings = [];

        // Validate offer type
        if (!isset(self::BASE_RANGES[$offerType])) {
            $offerType = 'FULL_TIME';
            $warnings[] = 'Unknown offer type, defaulting to FULL_TIME.';
        }

        $range = self::BASE_RANGES[$offerType];

        // Warn if salary is outside typical range
        if ($baseSalary < $range['min']) {
            $warnings[] = sprintf('Base salary %.2f is below the typical minimum of %d for %s.', $baseSalary, $range['min'], $offerType);
        }
        if ($baseSalary > $range['max']) {
            $warnings[] = sprintf('Base salary %.2f exceeds the typical maximum of %d for %s.', $baseSalary, $range['max'], $offerType);
        }

        // Calculate bonus
        $bonusRate = self::BONUS_RATES[$offerType] ?? 0;
        $calculatedBonus = round($baseSalary * $bonusRate, 2);
        $bonus = $overrideBonus !== null ? $overrideBonus : $calculatedBonus;

        if ($overrideBonus !== null && abs($overrideBonus - $calculatedBonus) > $calculatedBonus * 0.5 && $calculatedBonus > 0) {
            $warnings[] = sprintf('Override bonus %.2f differs significantly from calculated bonus %.2f.', $overrideBonus, $calculatedBonus);
        }

        // Calculate stock options
        $stockRate = self::STOCK_RATES[$offerType] ?? 0;
        $calculatedStock = round($baseSalary * $stockRate, 2);
        $stockOptions = $overrideStock !== null ? $overrideStock : $calculatedStock;

        // Total compensation
        $totalCompensation = round($baseSalary + $bonus + $stockOptions, 2);

        return [
            'ctc' => $baseSalary,
            'bonus' => $bonus,
            'stock_options' => $stockOptions,
            'total_compensation' => $totalCompensation,
            'calculated_bonus' => $calculatedBonus,
            'calculated_stock' => $calculatedStock,
            'bonus_rate' => $bonusRate,
            'stock_rate' => $stockRate,
            'warnings' => $warnings,
        ];
    }

    /**
     * Suggest a compensation package based on offer type and experience level.
     *
     * @param string $offerType FULL_TIME|CONTRACT|INTERN
     * @param int $yearsExperience Candidate's years of experience
     * @return array Suggested package values
     */
    public function suggest(string $offerType, int $yearsExperience): array
    {
        $range = self::BASE_RANGES[$offerType] ?? self::BASE_RANGES['FULL_TIME'];

        // Scale within range based on experience (0-20+ years mapped to min-max)
        $factor = min(1.0, $yearsExperience / 20.0);
        $suggestedBase = round($range['min'] + ($range['max'] - $range['min']) * $factor, 2);

        return $this->calculate($offerType, $suggestedBase);
    }
}
