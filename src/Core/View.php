<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = base_path('src/Views/' . $view . '.php');
        if (!file_exists($viewFile)) {
            throw new RuntimeException('Vista no encontrada: ' . $view);
        }

        $layoutFile = base_path('src/Views/layouts/' . $layout . '.php');
        if (!file_exists($layoutFile)) {
            throw new RuntimeException('Layout no encontrado: ' . $layout);
        }

        extract($data, EXTR_SKIP);
        $pageTitle = $data['pageTitle'] ?? config('app.name', 'Aplicación');

        ob_start();
        include $viewFile;
        $content = (string) ob_get_clean();

        include $layoutFile;
    }
}
