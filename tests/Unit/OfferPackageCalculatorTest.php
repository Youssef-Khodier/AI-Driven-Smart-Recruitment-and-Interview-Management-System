<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\OfferPackageCalculator;
use PHPUnit\Framework\TestCase;

final class OfferPackageCalculatorTest extends TestCase
{
    public function testItCalculatesFullTimeCompensation(): void
    {
        $package = (new OfferPackageCalculator())->calculate('FULL_TIME', 100000);

        $this->assertSame(100000.0, $package['ctc']);
        $this->assertSame(15000.0, $package['bonus']);
        $this->assertSame(10000.0, $package['stock_options']);
        $this->assertSame(125000.0, $package['total_compensation']);
        $this->assertSame([], $package['warnings']);
    }

    public function testItWarnsWhenUnknownOfferTypeDefaultsToFullTime(): void
    {
        $package = (new OfferPackageCalculator())->calculate('PART_TIME', 100000);

        $this->assertSame(15000.0, $package['bonus']);
        $this->assertSame(10000.0, $package['stock_options']);
        $this->assertContains('Unknown offer type, defaulting to FULL_TIME.', $package['warnings']);
    }

    public function testItUsesOverrideValuesWhenProvided(): void
    {
        $package = (new OfferPackageCalculator())->calculate('CONTRACT', 80000, 2000, 500);

        $this->assertSame(2000.0, $package['bonus']);
        $this->assertSame(500.0, $package['stock_options']);
        $this->assertSame(82500.0, $package['total_compensation']);
    }

    public function testItSuggestsSalaryFromExperience(): void
    {
        $package = (new OfferPackageCalculator())->suggest('INTERN', 10);

        $this->assertSame(27500.0, $package['ctc']);
        $this->assertSame(27500.0, $package['total_compensation']);
    }
}
