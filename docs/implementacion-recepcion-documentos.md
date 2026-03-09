# Implementación: Sistema de Recepción de Documentos

## Fecha: 08/03/2026

## Problema Original
En la página `DocumentReception`, cuando un usuario hace clic en "Recibir", los botones "Responder" y "Otro" no se mostraban después de ejecutar la acción.

## Solución Implementada

### 1. Migración: `create_document_receptions_table`

Se creó una nueva tabla para registrar las recepciones de documentos vinculadas a movimientos.

```php
Schema::create('document_receptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('document_id')->constrained()->onDelete('cascade');
    $table->foreignId('movement_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('office_id')->constrained()->onDelete('cascade');
    $table->date('reception_date');
    $table->timestamps();
});
```

### 2. Modelo: `app/Models/DocumentReception.php`

Modelo Eloquent para la tabla `document_receptions` con relaciones:
- `document()` - BelongsTo Document
- `movement()` - BelongsTo Movement
- `user()` - BelongsTo User
- `office()` - BelongsTo Office

### 3. Modelo: `app/Models/Document.php`

Modificaciones:
- Agregada relación `receptions()` (HasMany)
- Modificado método `wasReceived()`:
```php
public function wasReceived(): bool
{
    return $this->receptions()->exists();
}
```

### 4. Trait: `app/Trait/HasReceiveAction.php`

Modificaciones:
- Agregado parámetro `$livewire` al método `getReceiveAction()`
- Agregado `action()` que crea el registro de recepción:
```php
->action(function (Document $record) {
    $movement = $record->latestMovement;
    if ($movement) {
        DocumentReception::create([
            'document_id' => $record->id,
            'movement_id' => $movement->id,
            'user_id' => Auth::id(),
            'office_id' => Auth::user()->office_id,
            'reception_date' => now()->toDateString(),
        ]);
    }
})
```
- Agregado `after()` para refrescar la tabla:
```php
->after(function () use ($livewire) {
    if ($livewire) {
        $livewire->dispatch('refreshTable');
    }
})
```

### 5. Página: `app/Filament/User/Pages/DocumentReception.php`

Modificado para pasar `$this` al método `getReceiveAction()`:
```php
self::getReceiveAction($this),
```

## Flujo de Uso

1. Un documento es derivado a una oficina → se crea un `movement` (acción: `derivado`)
2. La oficina recibe el documento → hace clic en "Recibir"
3. Se crea un registro en `document_receptions` vinculado al `movement`
4. La tabla se refresca automáticamente
5. El botón "Recibir" se oculta (porque `wasReceived()` ahora retorna `true`)
6. Los botones "Responder" y "Otro" se muestran

## Archivos Modificados/Creados

- `database/migrations/xxxx_xx_xx_create_document_receptions_table.php` (nuevo)
- `app/Models/DocumentReception.php` (nuevo)
- `app/Models/Document.php` (modificado)
- `app/Trait/HasReceiveAction.php` (modificado)
- `app/Filament/User/Pages/DocumentReception.php` (modificado)
