<?php

namespace App\Filament\Settings;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Traduce el estado "crudo" del formulario (que puede contener archivos
 * recién subidos, todavía no guardados) en datos listos para pintar en el
 * panel de preview: URLs, nombre de archivo y tamaño legible.
 *
 * Vive fuera de la página de Filament a propósito: así se puede testear
 * de forma aislada y reutilizar si en el futuro agregas más previews
 * (ej. una preview de "Términos" o de "SEO / resultado en Google").
 */
class SettingsPreviewBuilder
{
    public function build(array $general): array
    {
        return [
            'site_name' => $general['site_name'] ?? null,
            'description' => $general['description'] ?? null,
            'support_email' => $general['support_email'] ?? null,
            'support_phone' => $general['support_phone'] ?? null,
            'logo' => $this->resolveFile($general['logo'] ?? null, 'Logo'),
            'logo_dark' => $this->resolveFile($general['logo_dark'] ?? null, 'Logo (modo oscuro)'),
            'favicon' => $this->resolveFile($general['favicon'] ?? null, 'Favicon'),
        ];
    }

    /**
     * @return array{label: string, name: string, url: ?string, size: ?string}|null
     */
    protected function resolveFile(mixed $value, string $label): ?array
    {
        if (blank($value)) {
            return null;
        }

        // Filament entrega el estado como array (incluso con un solo archivo
        // cuando el campo se rehidrata desde la caché); aquí sólo interesa
        // el primer archivo, igual que en el formulario.
        if (is_array($value)) {
            $value = Arr::first($value);

            if (blank($value)) {
                return null;
            }
        }

        // Archivo recién seleccionado en el navegador, aún no guardado.
        if ($value instanceof TemporaryUploadedFile) {
            return [
                'label' => $label,
                'name' => $value->getClientOriginalName(),
                'url' => $value->temporaryUrl(),
                'size' => $this->humanFileSize($value->getSize()),
            ];
        }

        // Ruta ya persistida en el disco configurado.
        if (is_string($value)) {
            $disk = $this->disk();
            $exists = $disk->exists($value);

            return [
                'label' => $label,
                'name' => basename($value),
                'url' => $exists ? $this->publicUrl($value) : null,
                'size' => $exists ? $this->humanFileSize($disk->size($value)) : null,
            ];
        }

        return null;
    }

    /**
     * URL pública del archivo.
     *
     * Para el disco público usa `asset()`, o sea: relativa al **origen de la
     * página en curso**, nunca `APP_URL.'/storage/…'`. Así el preview (y el
     * logo del panel, §19.4) cargan igual con `localhost`, `127.0.0.1`, otro
     * puerto o `https`, y no hay error de CORS cuando el FileUpload vuelve a
     * pedir el archivo.
     *
     * Si algún día `settings.upload_disk` apunta a otro disco, se usa la URL
     * que ese disco declare.
     */
    protected function publicUrl(string $path): string
    {
        if ((string) config('settings.upload_disk', 'public') !== 'public') {
            return $this->disk()->url($path);
        }

        return asset('storage/'.$path);
    }

    /**
     * El disco vive en config para no tener dos fuentes de verdad: debe
     * coincidir con el ->disk() de los FileUpload de §7.3 / §7.6.
     */
    protected function disk(): Filesystem
    {
        return Storage::disk((string) config('settings.upload_disk', 'public'));
    }

    protected function humanFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }

        $units = ['KB', 'MB', 'GB'];
        $value = $bytes / 1024;

        foreach ($units as $unit) {
            if ($value < 1024) {
                return round($value, 1).' '.$unit;
            }

            $value /= 1024;
        }

        return round($value, 1).' TB';
    }
}
