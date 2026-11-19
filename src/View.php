<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore;

use RuntimeException;

/**
 * Renders PHP templates from <root>/views and wraps them into a layout from <root>/views/layouts.
 * Inside a template `$this` is the View, so templates can set the page title.
 */
class View
{
    public string $title = '';

    /**
     * @param array<string, mixed> $params
     */
    public function renderView(string $view, array $params = []): string
    {
        $viewContent = $this->renderFile(Application::$ROOT_DIR . "/views/$view.php", $params);
        $layout = Application::$app->controller?->layout ?? Application::$app->layout;
        $layoutContent = $this->renderFile(Application::$ROOT_DIR . "/views/layouts/$layout.php", []);

        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function renderFile(string $file, array $params): string
    {
        if (!is_file($file)) {
            throw new RuntimeException("View file not found: $file");
        }

        extract($params, EXTR_SKIP);
        ob_start();
        include $file;

        return (string) ob_get_clean();
    }
}
