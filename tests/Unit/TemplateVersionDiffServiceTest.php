<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TemplateVersionDiffService;
use PHPUnit\Framework\TestCase;

final class TemplateVersionDiffServiceTest extends TestCase
{
    public function testItMarksAddedAndRemovedLines(): void
    {
        $diff = TemplateVersionDiffService::diff(
            "Hello\nOld salary\nRegards",
            "Hello\nNew salary\nRegards"
        );

        $this->assertSame([
            ['type' => 'unchanged', 'content' => 'Hello'],
            ['type' => 'removed', 'content' => 'Old salary'],
            ['type' => 'added', 'content' => 'New salary'],
            ['type' => 'unchanged', 'content' => 'Regards'],
        ], $diff);
    }

    public function testItNormalizesWindowsLineEndings(): void
    {
        $diff = TemplateVersionDiffService::diff(
            "Line one\r\nLine two",
            "Line one\nLine two\nLine three"
        );

        $this->assertSame([
            ['type' => 'unchanged', 'content' => 'Line one'],
            ['type' => 'unchanged', 'content' => 'Line two'],
            ['type' => 'added', 'content' => 'Line three'],
        ], $diff);
    }
}
