<?php

namespace App\Services;

class TemplateVersionDiffService
{
    /**
     * Computes a simple line-by-line diff between two text strings.
     * 
     * @param string $old
     * @param string $new
     * @return array Array of diff chunks, each with type ('unchanged', 'added', 'removed') and 'content'.
     */
    public static function diff(string $old, string $new): array
    {
        $oldLines = explode("\n", str_replace("\r\n", "\n", $old));
        $newLines = explode("\n", str_replace("\r\n", "\n", $new));

        $matrix = array_fill(0, count($oldLines) + 1, array_fill(0, count($newLines) + 1, 0));

        for ($i = 1; $i <= count($oldLines); $i++) {
            for ($j = 1; $j <= count($newLines); $j++) {
                if ($oldLines[$i - 1] === $newLines[$j - 1]) {
                    $matrix[$i][$j] = $matrix[$i - 1][$j - 1] + 1;
                } else {
                    $matrix[$i][$j] = max($matrix[$i - 1][$j], $matrix[$i][$j - 1]);
                }
            }
        }

        $i = count($oldLines);
        $j = count($newLines);
        $diff = [];

        while ($i > 0 && $j > 0) {
            if ($oldLines[$i - 1] === $newLines[$j - 1]) {
                array_unshift($diff, ['type' => 'unchanged', 'content' => $oldLines[$i - 1]]);
                $i--;
                $j--;
            } elseif ($matrix[$i - 1][$j] > $matrix[$i][$j - 1]) {
                array_unshift($diff, ['type' => 'removed', 'content' => $oldLines[$i - 1]]);
                $i--;
            } else {
                array_unshift($diff, ['type' => 'added', 'content' => $newLines[$j - 1]]);
                $j--;
            }
        }

        while ($i > 0) {
            array_unshift($diff, ['type' => 'removed', 'content' => $oldLines[$i - 1]]);
            $i--;
        }

        while ($j > 0) {
            array_unshift($diff, ['type' => 'added', 'content' => $newLines[$j - 1]]);
            $j--;
        }

        return $diff;
    }
}
