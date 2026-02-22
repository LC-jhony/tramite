# Mejoras para el Sistema de Trámite Documentario

## Estado Actual

- **PHP:** 8.4
- **Laravel:** 12.52
- **Filament:** v5
- **Base de datos:** MariaDB
- **Tests:** 45 tests (45 passing)

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
- [x] Verificar aplicación en recursos de Filament
- [x] Configurar FilamentShield si es necesario
- [x] Personalizar DocumentPolicy con lógica de negocio (Opción 1)

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

**Tareas:**
- [x] Crear `DocumentFactory`
- [x] Crear `MovementFactory`
- [x] Crear `OfficeFactory`
- [x] Crear `DocumentTypeFactory`
- [x] Crear `AdministrationFactory`
- [x] Crear `DocumentFileFactory`
- [x] Test: `DocumentTest` (CRUD, estados, relaciones)
- [x] Test: `MovementTest` (derivación, rechazo, recepción)
- [x] Test: `CustomerTest` (CRUD)
- [x] Test: `ReceptionDocumentTest` (flujo completo)
- [x] Test: `DocumentPolicyTest` (autorización)

---

### 4. Factories incompletos

Existen todos los factories necesarios.

**Tareas:**
- [x] `DocumentFactory`
- [x] `MovementFactory`
- [x] `OfficeFactory`
- [x] `DocumentTypeFactory`
- [x] `AdministrationFactory`
- [x] `DocumentFileFactory`
- [x] `PriorityFactory`

---

## Media Prioridad

### 5. Notificaciones

Agregar notificaciones cuando un documento llega a una oficina.

**Tareas:**
- [x] Crear `DocumentReceived` notification
- [x] Crear `DocumentDerivated` notification
- [x] Crear `DocumentRejected` notification
- [x] Implementar notificaciones en ReceptionDocument (canal database)
- [ ] **IMPLEMENTAR EMAIL** - Agregar canal email a las notificaciones
- [ ] Configurar mailtrap/smtp en .env
- [ ] Agregar preference de notificaciones por usuario

**Estado actual:** Solo canal `database` implementado. Email pendiente.

**Notificaciones integradas:**
- Recepcionar → Notifica al propietario (database)
- Derivar → Notifica a oficina destino (database)
- Rechazar → Notifica al propietario (database)

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
- [x] Crear migración para agregar índices

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

El modelo Document YA tiene el trait SoftDeletes. Solo falta agregar la columna a movements.

**Tareas:**
- [x] Agregar `SoftDeletes` trait en modelos (Document ya lo tiene)
- [x] Agregar `deleted_at` a `movements`
- [x] Actualizar recursos de Filament para manejar soft deletes

---

### 8. Auditoría

Agregar tracking de cambios con `owen-it/laravel-auditing`.

**Tareas:**
- [x] Instalar paquete: `composer require owen-it/laravel-auditing`
- [x] Instalar paquete: `composer require tapp/filament-auditing`
- [x] Publicar configuración
- [x] Agregar trait `Auditable` a modelos (Document, Movement)
- [x] Crear migración para tabla `audits`
- [ ] Agregar sección de historial en vista de documento

**Implementado:**
- Paquetes instalados (laravel-auditing + filament-auditing)
- Tabla `audits` creada
- Modelos Document y Movement con trait Auditable
- AuditsRelationManager agregado a DocumentResource (admin y user)

---

### 9. Ruta pública de seguimiento

El componente `CaseTrackingForm` ya existe en `app/Livewire/`. Completado con búsqueda por DNI y case_number, mostrando timeline de movimientos.

**Tareas:**
- [x] CaseTrackingForm existe
- [x] Formulario de búsqueda por case_number o DNI
- [x] Timeline de movimientos
- [x] Estado actual del documento

---

### 10. Soporte Multilenguaje

Sistema de traducciones implementado usando archivos JSON en `lang/`.

**Tareas:**
- [x] Crear archivo `lang/es.json` con traducciones
- [x] Implementar traducciones en DocumentForm
- [x] Implementar traducciones en CaseTrackingForm
- [ ] Crear archivo `lang/en.json` para inglés
- [ ] Agregar selector de idioma en frontend

**Traducciones implementadas:**
- Labels de formularios
- Mensajes de error
- Títulos y descripciones
- Textos de ayuda

---

### 11. API REST

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

### 12. Dashboard con widgets

Estadísticas visuales en el dashboard.

**Widgets sugeridos:**
- [x] Documentos por estado (gráfico de barras)
- [x] Documentos por oficina (gráfico pie)
- [ ] Tiempo promedio de respuesta
- [x] Documentos pendientes por vencer
- [x] Últimos movimientos

**Implementado:**
- Paquete `filament/widgets` (incluido en Filament v5)
- Widget `DashboardStats` - estadísticas de documentos
- Widget `DocumentsByStatus` - gráfico de documentos por estado
- Widget `RecentMovements` - tabla de últimos movimientos
- Widgets registrados en AdminPanelProvider

**Paquete recomendado:** `filament/spatie-laravel-widgets-plugin`

---

### 13. Reportes PDF/Excel

Exportar listados y reportes de gestión.

**Tareas:**
- [ ] Instalar `maatwebsite/excel`
- [ ] Instalar `barryvdh/laravel-dompdf`
- [ ] Crear exports para Documents, Movements
- [ ] Agregar acciones de exportación en Filament

---

### 14. Roles y permisos

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

### 15. Archivo automático

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

### 16. Firma digital

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

1. **Primera fase:** 1, 3, 4, 10 (priorities, tests, factories, multilenguaje) + verificar 2 y 13
2. **Segunda fase:** 5, 6, 7, 9 (notificaciones + email, índices, soft deletes, tracking)
3. **Tercera fase:** 8, 11, 12, 14 (auditoría, API, dashboard, reportes)
4. **Cuarta fase:** 15, 16 (archivo automático, firma digital)

---

*Documento actualizado: 2026-02-22*
