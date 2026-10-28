<?php

namespace App\Filament\Settings;

use Filament\Support\Colors\Color;

/**
 * Catálogo de paletas disponibles para el color primario del panel.
 *
 * Es la lista blanca que comparten dos lados que deben hablar lo mismo:
 *
 *  - `ColorThemeSettingsSchema` la usa para poblar el selector y la rejilla
 *    de vista previa.
 *  - `AdminPanelProvider` la usa para traducir el valor guardado en la paleta
 *    concreta que Filament registra como color `primary`.
 *
 * De este modo un valor manipulado a mano en la BD nunca puede romper el
 * panel: cae siempre en la paleta por defecto.
 */
class ThemeColorPalette
{
    /**
     * Paleta que se usa cuando la setting no existe o trae un valor no válido.
     * Es la que Filament trae de fábrica (`ColorManager::DEFAULT_COLORS`).
     */
    public const DEFAULT = 'amber';

    /**
     * Tonos de cada paleta, de claro a oscuro, en el mismo orden que las
     * constantes de `Color`. Todas las paletas de Filament tienen estos 11.
     *
     * @var array<int, int>
     */
    public const SHADES = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

    /**
     * Clave del selector (minúsculas) => nombre visible y constante de `Color`.
     *
     * Primero los colores cromáticos, que son los útiles como color primario;
     * al final los neutros, por si se quiere un panel sin color.
     *
     * @var array<string, string>
     */
    private const CATALOG = [
        'red' => 'Red',
        'orange' => 'Orange',
        'amber' => 'Amber',
        'yellow' => 'Yellow',
        'lime' => 'Lime',
        'green' => 'Green',
        'emerald' => 'Emerald',
        'teal' => 'Teal',
        'cyan' => 'Cyan',
        'sky' => 'Sky',
        'blue' => 'Blue',
        'indigo' => 'Indigo',
        'violet' => 'Violet',
        'purple' => 'Purple',
        'fuchsia' => 'Fuchsia',
        'pink' => 'Pink',
        'rose' => 'Rose',
        'slate' => 'Slate',
        'gray' => 'Gray',
        'zinc' => 'Zinc',
        'neutral' => 'Neutral',
        'stone' => 'Stone',
        'mauve' => 'Mauve',
        'olive' => 'Olive',
        'mist' => 'Mist',
        'taupe' => 'Taupe',
    ];

    /**
     * Opciones para el `Select`: valor en minúsculas => etiqueta.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::CATALOG;
    }

    /**
     * Normaliza cualquier valor guardado a una clave válida del catálogo.
     * Lo que no esté en la lista blanca vuelve a la paleta por defecto.
     */
    public static function key(?string $name): string
    {
        $key = strtolower(trim((string) $name));

        return array_key_exists($key, self::CATALOG) ? $key : self::DEFAULT;
    }

    /**
     * Paleta completa de Filament (11 tonos en oklch) para un nombre dado.
     *
     * @return array<int, string>
     */
    public static function palette(?string $name): array
    {
        $constant = Color::class.'::'.self::CATALOG[self::key($name)];

        /** @var array<int, string> */
        return constant($constant);
    }
}
