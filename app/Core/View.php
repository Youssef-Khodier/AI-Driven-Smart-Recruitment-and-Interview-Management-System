<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): string
    {
        $path = dirname(__DIR__, 2) . '/views/' . $view . '.php';
        if (! is_file($path)) {
            throw new HttpException(500, 'View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        $content = ob_get_clean();

        if (($layout ?? 'app') === false) {
            return $content;
        }

        $layoutPath = dirname(__DIR__, 2) . '/views/layouts/' . ($layout ?? 'app') . '.php';
        ob_start();
        require $layoutPath;

        return ob_get_clean();
    }
}
