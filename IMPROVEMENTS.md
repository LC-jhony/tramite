# Mejoras para el Sistema de Trámite Documentario

## Estado Actual

- **PHP:** 8.4
- **Laravel:** 12.52
- **Filament:** v5
- **Base de datos:** MariaDB
- **Tests:** 1 básico (sin cobertura real)

---

## Alta Prioridad

### 1. Tabla `priorities` faltante

✅ Completado - Tabla, modelo, factory, seeder y relación creados.

---

### 2. Policies de autorización

Ya existen todas las policies en `app/Policies/`. El paquete `spatie/laravel-permission` está instalado y `filament-shield` también. Verificar que estén correctamente aplicadas en Filament.

**Tareas:**
- [x] Las policies ya existen: DocumentPolicy, MovementPolicy, OfficePolicy, etc.
- [x] Paquete `spatie/laravel-permission` instalado
- [ ] Verificar aplicación en recursos de Filament
- [ ] Configurar FilamentShield si es necesario

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

Solo existe 1 test básico sin cobertura real.

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

Existen `UserFactory`, `CustomerFactory` y `PriorityFactory`.

**Tareas:**
- [ ] `DocumentFactory`
- [ ] `MovementFactory`
- [ ] `OfficeFactory`
- [ ] `DocumentTypeFactory`
- [ ] `AdministrationFactory`
- [ ] `DocumentFileFactory`
- [x] `PriorityFactory`

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

El componente `CaseTrackingForm` ya existe en `app/Livewire/`. Verificar implementación completa.

**Tareas:**
- [x] CaseTrackingForm existe
- [ ] Verificar formulario de búsqueda por case_number o DNI
- [ ] Verificar timeline de movimientos
- [ ] Verificar estado actual del documento
- [ ] Agregar estilos visuales al timeline si faltan

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

El paquete `spatie/laravel-permission` está instalado y `filament-shield` también. Ya existen policies que usan este patrón.

**Roles sugeridos:**
- `super-admin` - Acceso total
- `admin` - Administración de oficina
- `usuario` - Gestión de documentos propios
- `recepcionista` - Solo recepción y derivación

**Tareas:**
- [x] Paquete `spatie/laravel-permission` instalado
- [x] `filament-shield` instalado
- [ ] Configurar roles y permisos
- [ ] Crear seeder con roles iniciales
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
# Crear test
php artisan make:test DocumentTest --pest

# Crear factory
# (Crear manualmente en database/factories)

# Crear migración para índices
php artisan make:migration add_indexes_to_movements_table

# Paquetes ya instalados:
# - spatie/laravel-permission
# - filament-shield

# Paquetes por instalar si se necesitan:
# composer require owen-it/laravel-auditing
# composer require maatwebsite/excel
# composer require barryvdh/laravel-dompdf
```

---

## Priorización Sugerida

1. **Primera fase:** 1, 3, 4 (priorities, tests, factories) + verificar 2 y 13
2. **Segunda fase:** 5, 6, 7, 9 (notificaciones, índices, soft deletes, tracking)
3. **Tercera fase:** 8, 10, 11, 12 (auditoría, API, dashboard, reportes)
4. **Cuarta fase:** 14, 15 (archivo automático, firma digital)

---

*Documento actualizado: 2026-02-21*
