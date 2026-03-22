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

### 6. Página: `app/Filament/User/Pages/DocumentReception.php`

Modificado para pasar `$this` al método `getReceiveAction()`:
```php
self::getReceiveAction($this),
```

### 7. Enum: `app/Enum/DocumentStatus.php`

Agregado nuevo status `Respondido`:
```php
case Respondido = 'respondido';
```
- Color: `primary`
- Icono: `heroicon-o-chat-bubble-left-right`
- Transiciones permitidas desde `Respondido`: `Finalizado`, `Rechazado`, `Cancelado`

### 8. Trait: `app/Trait/HasForwardAction.php`

Modificado el método `forwardAction()` para actualizar el status según la acción seleccionada:
```php
$status = match ($selectedAction) {
    'derivado' => DocumentStatus::EnProceso,
    'respondido' => DocumentStatus::Respondido,
};
```

También se agregaron estilos Tailwind CSS al helperText y hint del Select de acción.

### 9. Trait: `app/Trait/HasReceiveAction.php`

Actualizado para usar el nuevo método `hasActionByCurrentUser()` para deshabilitar acciones.

### 10. Trait: `app/Trait/HasRejectAction.php`

Actualizado para usar el nuevo método `hasActionByCurrentUser()` para deshabilitar acciones.

### 5. Página: `app/Filament/User/Pages/DocumentReception.php`

Modificado para pasar `$this` al método `getReceiveAction()`:
```php
self::getReceiveAction($this),
```

### 8. Modelo: `app/Models/Document.php`

Agregado nuevo método:
```php
public function hasActionByCurrentUser(): bool
{
    return $this->movements()
        ->where('user_id', auth()->id())
        ->exists();
}
```

### 9. Trait: `app/Trait/HasForwardAction.php`

Actualizado `disabled` para verificar si el usuario ya realizó alguna acción:
```php
->disabled(fn (Document $record): bool => 
    $record->wasDerivedBy(auth()->id()) || 
    $record->hasActionByCurrentUser()
)
```

### 10. Trait: `app/Trait/HasRejectAction.php`

Actualizado `disabled` para verificar si el usuario ya realizó alguna acción:
```php
->disabled(
    fn (Document $record): bool => 
        ! $record->wasReceived() 
        || $record->isClosed()
        || $record->hasActionByCurrentUser()
)
```

## Flujo de Uso

1. Un documento es derivado a una oficina → se crea un `movement` (acción: `derivado`)
2. La oficina recibe el documento → hace clic en "Recibir"
3. Se crea un registro en `document_receptions` vinculado al `movement`
4. La tabla se refresca automáticamente
5. El botón "Recibir" se oculta (porque `wasReceived()` ahora retorna `true`)
6. Los botones "Responder" y "Otro" se muestran
7. Al derivar o responder un documento:
   - Si selecciona "Derivar" → status cambia a `EnProceso`
   - Si selecciona "Responder" → status cambia a `Respondido`
8. **Deshabilitación de acciones**:
   - Si el usuario hace clic en "Responder" → ambas acciones (Responder y Otro) se deshabilitan para ese usuario
   - Si el usuario hace clic en "Otro" → ambas acciones (Responder y Otro) se deshabilitan para ese usuario
   - Otro usuario podría ejecutar las acciones si el documento aún no está cerrado

## Archivos Modificados/Creados

- `database/migrations/2026_03_08_235830_create_document_receptions_table.php` (nuevo)
- `app/Models/DocumentReception.php` (nuevo)
- `app/Models/Document.php` (modificado)
- `app/Enum/DocumentStatus.php` (modificado)
- `app/Trait/HasReceiveAction.php` (modificado)
- `app/Trait/HasForwardAction.php` (modificado)
- `app/Trait/HasRejectAction.php` (modificado)
- `app/Filament/User/Pages/DocumentReception.php` (modificado)
