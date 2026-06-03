<?php
namespace App\Core;

/**
 * Renderer de vues.
 * Charge un template + l'enveloppe dans le layout principal.
 */
final class View
{
    public static function render(string $template, array $data = [], string $layout = 'main'): void
    {
        extract($data, EXTR_SKIP);
        $tplFile = __DIR__ . '/../Views/' . $template . '.php';
        if (!is_file($tplFile)) {
            die("Vue introuvable : $template");
        }
        ob_start();
        require $tplFile;
        $content = ob_get_clean();
        $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        if (is_file($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /** Échappement HTML — alias court pour les vues. */
    public static function e($s): string
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
