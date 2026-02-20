# Mejoras para el Sistema de Trámite Documentario

## Estado Actual

- **PHP:** 8.4
- **Laravel:** 12.52
- **Filament:** v5
- **Base de datos:** MariaDB
- **Tests:** 2 básicos (sin cobertura real)

---

## Alta Prioridad

### 1. Tabla `priorities` faltante

El modelo `Document` tiene el campo `priority_id` pero no existe la tabla ni el modelo.

**Tareas:**
- [ ] Crear migración `create_priorities_table`
- [ ] Crear modelo `Priority`
- [ ] Crear factory y seeder
- [ ] Agregar relación en `Document`
- [ ] Agregar campo en formularios de Filament

**Campos sugeridos:**
```php
Schema::create('priorities', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Alta, Media, Baja
    $table->string('color')->default('gray');
    $table->integer('days')->default(5); // Días para respuesta
    $table->boolean('status')->default(true);
    $table->timestamps();
});
```

---

### 2. Policies de autorización

No existen policies. Usuarios pueden ver/editar documentos de otras oficinas.

**Tareas:**
- [ ] Crear `DocumentPolicy`
- [ ] Crear `MovementPolicy`
- [ ] Crear `OfficePolicy`
- [ ] Registrar policies en `AppServiceProvider`
- [ ] Aplicar en recursos de Filament

**DocumentPolicy sugerido:**
```php
class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $user->office_id === $document->area_origen_id
            || $user->office_id === $document->id_office_destination
            || $user->id === $document->user_id;
    }

    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->user_id
            && $document->status === DocumentStatus::REGISTERED;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->user_id;
    }
}
```

---

### 3. Tests

Solo existen 2 tests básicos sin cobertura real.

**Tareas:**
- [ ] Crear `DocumentFactory`
- [ ] Crear `MovementFactory`
- [ ] Crear `OfficeFactory`
- [ ] Crear `DocumentTypeFactory`
- [ ] Crear `AdministrationFactory`
- [ ] Test: `DocumentTest` (CRUD, estados, relaciones)
- [ ] Test: `MovementTest` (derivación, rechazo, recepción)
- [ ] Test: `CustomerTest` (CRUD)
- [ ] Test: `ReceptionDocumentTest` (flujo completo)
- [ ] Test: `DocumentPolicyTest` (autorización)

**Ejemplo de test:**
```php
it('can create a document', function () {
    $user = User::factory()->create();
    actingAs($user);

    livewire(CreateDocument::class)
        ->fillForm([
            'document_number' => 'DOC-001',
            'case_number' => 'CASE-001',
            'subject' => 'Test Subject',
            'origen' => 'Interno',
            'document_type_id' => DocumentType::factory()->create()->id,
            'area_origen_id' => Office::factory()->create()->id,
            'gestion_id' => Administration::factory()->create()->id,
            'reception_date' => now(),
            'status' => DocumentStatus::REGISTERED,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Document::count())->toBe(1);
});
```

---

### 4. Factories incompletos

Solo existen `UserFactory` y `CustomerFactory`.

**Tareas:**
- [ ] `DocumentFactory`
- [ ] `MovementFactory`
- [ ] `OfficeFactory`
- [ ] `DocumentTypeFactory`
- [ ] `AdministrationFactory`
- [ ] `DocumentFileFactory`
- [ ] `PriorityFactory` (después de crear la tabla)

---

## Media Prioridad

### 5. Notificaciones

Agregar notificaciones cuando un documento llega a una oficina.

**Tareas:**
- [ ] Crear `DocumentReceived` notification
- [ ] Crear `DocumentDerivated` notification
- [ ] Crear `DocumentRejected` notification
- [ ] Configurar canales (database, mail)
- [ ] Agregar preference de notificaciones por usuario

**Ejemplo:**
```php
class DocumentReceived extends Notification
{
    public function __construct(public Document $document) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nuevo documento recibido: {$this->document->case_number}")
            ->line("Ha recibido un documento de {$this->document->areaOrigen->name}")
            ->action('Ver documento', url("/documents/{$this->document->id}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'case_number' => $this->document->case_number,
            'message' => "Documento recibido de {$this->document->areaOrigen->name}",
        ];
    }
}
```

---

### 6. Índices en movements

La tabla `movements` no tiene índices en columnas clave.

**Tareas:**
- [ ] Crear migración para agregar índices

```php
Schema::table('movements', function (Blueprint $table) {
    $table->index('document_id');
    $table->index('origin_office_id');
    $table->index('destination_office_id');
    $table->index('status');
    $table->index('receipt_date');
    $table->index(['destination_office_id', 'status']);
});
```

---

### 7. Soft Deletes

Agregar soft deletes para historial.

**Tareas:**
- [ ] Agregar `deleted_at` a `documents`
- [ ] Agregar `deleted_at` a `movements`
- [ ] Agregar `SoftDeletes` trait en modelos
- [ ] Actualizar recursos de Filament para manejar soft deletes

```php
// Migración
Schema::table('documents', function (Blueprint $table) {
    $table->softDeletes();
});

// Modelo
class Document extends Model
{
    use SoftDeletes;
}
```

---

### 8. Auditoría

Agregar tracking de cambios con `owen-it/laravel-auditing`.

**Tareas:**
- [ ] Instalar paquete: `composer require owen-it/laravel-auditing`
- [ ] Publicar configuración
- [ ] Agregar trait `Auditable` a modelos
- [ ] Crear migración para tabla `audits`
- [ ] Agregar sección de historial en vista de documento

---

### 9. Ruta pública de seguimiento

Completar `CaseTrackingForm` para que clientes consulten sus casos.

**Tareas:**
- [ ] Implementar formulario de búsqueda por case_number o DNI
- [ ] Mostrar timeline de movimientos
- [ ] Mostrar estado actual del documento
- [ ] Agregar estilos visuales al timeline

**Ejemplo de implementación:**
```php
class CaseTrackingForm extends Component
{
    public ?string $case_number = null;
    public ?Document $document = null;
    public bool $found = false;

    public function search(): void
    {
        $this->validate(['case_number' => 'required']);

        $this->document = Document::with('movements.originOffice', 'movements.destinationOffice')
            ->where('case_number', $this->case_number)
            ->first();

        $this->found = $this->document !== null;
    }
}
```

---

### 10. API REST

Exponer endpoints para integración externa.

**Tareas:**
- [ ] Crear `ApiController`
- [ ] Implementar endpoints:
  - `GET /api/documents` - Listar documentos
  - `GET /api/documents/{id}` - Ver documento
  - `POST /api/documents` - Crear documento
  - `GET /api/documents/{id}/movements` - Ver movimientos
- [ ] Agregar autenticación con Sanctum
- [ ] Crear resources: `DocumentResource`, `MovementResource`

---

## Baja Prioridad

### 11. Dashboard con widgets

Estadísticas visuales en el dashboard.

**Widgets sugeridos:**
- [ ] Documentos por estado (gráfico de barras)
- [ ] Documentos por oficina (gráfico pie)
- [ ] Tiempo promedio de respuesta
- [ ] Documentos pendientes por vencer
- [ ] Últimos movimientos

**Paquete recomendado:** `filament/spatie-laravel-widgets-plugin`

---

### 12. Reportes PDF/Excel

Exportar listados y reportes de gestión.

**Tareas:**
- [ ] Instalar `maatwebsite/excel`
- [ ] Instalar `barryvdh/laravel-dompdf`
- [ ] Crear exports para Documents, Movements
- [ ] Agregar acciones de exportación en Filament

---

### 13. Roles y permisos

Control de acceso granular con `spatie/laravel-permission`.

**Roles sugeridos:**
- `super-admin` - Acceso total
- `admin` - Administración de oficina
- `usuario` - Gestión de documentos propios
- `recepcionista` - Solo recepción y derivación

**Tareas:**
- [ ] Instalar `spatie/laravel-permission`
- [ ] Crear seeder con roles y permisos
- [ ] Aplicar middleware en rutas
- [ ] Filtrar recursos según permisos

---

### 14. Archivo automático

Job para archivar documentos completados.

**Tareas:**
- [ ] Crear job `ArchiveCompletedDocuments`
- [ ] Programar en scheduler (diario)
- [ ] Cambiar status a `ARCHIVED` después de X días
- [ ] Notificar antes de archivar

```php
class ArchiveCompletedDocuments implements ShouldQueue
{
    public function handle(): void
    {
        Document::where('status', DocumentStatus::COMPLETED)
            ->where('updated_at', '<', now()->subDays(30))
            ->update(['status' => DocumentStatus::ARCHIVED]);
    }
}
```

---

### 15. Firma digital

Integración con firma electrónica.

**Opciones:**
- FirmaEC (Ecuador)
- DocuSign
- Adobe Sign

**Tareas:**
- [ ] Investigar proveedor según país
- [ ] Crear integración API
- [ ] Agregar campo `signed_at` y `signature_hash`
- [ ] Agregar verificación de firma

---

## Resumen de Comandos

```bash
# Crear modelo con todo
php artisan make:model Priority -mfsc

# Crear policy
php artisan make:policy DocumentPolicy --model=Document

# Crear test
php artisan make:test DocumentTest --pest

# Crear factory
# (Crear manualmente en database/factories)

# Crear migración para índices
php artisan make:migration add_indexes_to_movements_table

# Instalar paquetes
composer require spatie/laravel-permission
composer require owen-it/laravel-auditing
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
```

---

## Priorización Sugerida

1. **Primera fase:** 1, 2, 3, 4 (Fundamentos)
2. **Segunda fase:** 5, 6, 7, 9 (Funcionalidad)
3. **Tercera fase:** 8, 10, 11, 12 (Integración)
4. **Cuarta fase:** 13, 14, 15 (Avanzado)

---

*Documento generado: 2026-02-20*
