# Revision de Filament Resources - Reporte

## Paneles

| Hallazgo | Severidad | Descripcion |
|----------|-----------|-------------|
| **UserPanelProvider:45** - `CustomTopNavigation` esta commented-out. Si no se usa, eliminarlo. | Baja | `// ->topbarLivewireComponent(CustomTopNavigation::class)` |

---

## Resource Classes (7 recursos admin + 1 user)

| Recurso | Navegacion Icon | Observacion |
|---------|----------------|-------------|
| Todos los recursos | Usan `Heroicon::OutlinedRectangleStack` | **WARNING**: Todos usan el mismo icono. Cada recurso deberia tener un icono unico que lo represente. |
| UserResource | Correcto: `Heroicon::OutlinedUsers` | Correcto |

**Items:**

- **CustomerResource:19** - `$recordTitleAttribute = 'dni'` deberia ser `full_name` para mejor UX.
- **UsersTable:43** - `Badge::color()` con `default => 'primary'` hardcodeado siempre. El badge podria omitirse si no hay oficina asignada.
- **UserForm:45** - Campo `email` tiene `->label('Correo electronico')` y `->label('Email address')` duplicados. Se aplica solo el segundo (sobrescribe el primero).
- **UserForm:37** - Hay un segundo `Fieldset::make('Contraseña')` duplicado para el modo edit (lineas 50-58), pero no hay `->columnSpan()` definido en el Fieldset.

---

## Forms (Schemas)

| Archivo | Problemas |
|---------|-----------|
| **AdministrationForm.php** | Sin labels, sin `maxLength`, sin `numeric()` en campos de fecha. Los campos `start_period` y `end_period` deberian usar `DatePicker`. No hay validacion de negocio (ej. `end_period > start_period`). |
| **DocumentForm.php (Admin)** | Mezcla de `TextInput` y `Select` para relaciones. Usa `TextInput::make('document_type_id')` y `TextInput::make('gestion_id')` con `->numeric()` en lugar de `Select` con `->relationship()`. Inconsistente con el resto de forms que SÍ usan `Select` con relaciones. Faltan labels en español. Campo `origen` usa `TextInput` sin opciones predefinidas (deberia ser `Select` con opciones `interno/externo`). Campo `condition` usa `TextInput` en lugar de `Select`. No hay `unique()` validation en `document_number` ni `case_number`. |
| **DocumentForm.php (User)** | `Folio` es `TextInput` con `->numeric()` pero deberia usar `Select` o `TextInput` con `unique()`. `getNextSequentialNumber()` y `caseNumber()` son publicos y se llaman en `default()` de campos disabled, lo cual es correcto. |
| **PriorityForm.php** | Campo `days` usa `default(5)` y `required()` - si es `required` no necesita default. Verificar logica de negocio. |
| **OfficeForm.php** | Sin labels, sin `maxLength`. Campo `code` deberia tener `maxLength` definido (ej. 10). |
| **DocumentTypeForm.php** | Campo `response_days` usa `->default(null)` - redundante ya que es el valor por defecto de Eloquent. Falta `maxLength` en `code`. |
| **UserForm.php** | Duplicacion de labels en `email`. El `Fieldset::make('Contraseña')` para edit no tiene `->columnSpan()`, por lo que no ocupa todo el ancho. Campo `email` tiene labels duplicados que se sobrescriben. |

---

## Tables

| Archivo | Problemas |
|---------|-----------|
| **AdministrationsTable** | Falta `IconColumn` para `mayor` (campo de texto, no icono). Correcto. `IconColumn::make('status')` usa `->boolean()` lo cual es correcto si la DB guarda boolean. |
| **CustomersTable** | Linea 8: `IconColumn::make('representation')` - faltaria label (`->label('Representación')`). `full_name`, `first_name`, `last_name` - si `full_name` es una propiedad computed que concatena `first_name` + `last_name`, entonces `first_name` y `last_name` en la tabla son redundantes. Revisar si hay N+1 en `full_name` accessor. No hay `paginationPageOptions` ni `defaultSort`. |
| **CustomersTable** | No hay acciones de bulk ni toolbar, solo `recordActions` con `EditAction`. Falta `DeleteBulkAction` para consistencia con otros recursos. |
| **DocumentsTable (Admin)** | Linea 17: `TextColumn::make('customer.full_name')` con `->placeholder('No description.')` - placeholder en español seria `Sin descripción.`. Lineas 58-59 comentadas sobre `response_deadline` - si no se usan, eliminarlas. `->weight('medium')` en `document_number` - correcto. Filtros muy buenos y completos. |
| **OfficesTable** | `Code` usa `->copyable()` lo cual es util. Correcto. |
| **PrioritiesTable** | `ColorColumn` con `->searchable()` - `searchable()` no tiene efecto en `ColorColumn`. Linea 42: No tiene `paginationPageOptions`, ni `emptyStateHeading/Description/Icon`, ni `defaultSort` para consistencia. |
| **UsersTable** | `email_verified_at` usa `dateTime()` - si es null muestra "0" o algo raro. Deberia usar `->placeholder('No verificado')`. Linea 43: el badge de `office.name` siempre usa color `primary` - podria variar. `Action::make('resend_verification_email')` - el comment en `authorize()` esta desactivado. |
| **DocumentsTable (User)** | Linea 20: `->placeholder('N/A.')` en `customer.full_name` - correcto. Linea 22: `case_number` label dice "Expediente" pero podria ser mas descriptivo. Linea 44: `->label('derivar')` en minuscula - deberia ser "Derivar" (Title Case). |

---

## Panel Providers

| Archivo | Problema |
|---------|----------|
| **AdminPanelProvider** | `->topbar(false)` - esto oculta la barra superior, verificar si es intencional. No tiene `->discoverClusters()`. `registration()` y `emailVerification()` habilitados, lo cual es correcto. Plugins configurados (Shield + Backup). |
| **UserPanelProvider** | `->topNavigation()` esta activo pero `CustomTopNavigation` esta comentado. Posible inconsistencia. No hay `emailVerification()` (linea 23-24 lo tienen en Admin pero no en User). |

---

## Traits / Acciones Reutilizables

| Trait | Problema |
|-------|----------|
| **HasForwardAction.php:24** | `->disabled()` comentario indica que estaba intended para hacer `->visible()` pero se invirtio la logica. Hay `->visible()` comentado y `->disabled()` activo. Verificar si la logica de negocio es correcta. |
| **HasForwardAction.php:31** | `forwardAction()` actualiza `status` a `DocumentStatus::Respondido` pero el valor en el enum puede no existir (verificar `app/Enum/DocumentStatus.php`). Tambien nunca actualiza `current_office_id` del documento! El documento sigue con la oficina anterior. |
| **HasRejectAction.php:47** | `getRejectAction()` crea un nuevo `Movement` con `to_office_id = $record->current_office_id` - pero si es un rechazo, no deberia derivarse a otro lugar. Revisar logica de negocio. Tambien el metodo `RejectAction` esta en mixed case (`RejectAction` vs `rejectAction` - inconsistente con el resto del codigo). |
| **HasReceiveAction.php:37** | `->requiresConfirmation()` esta duplicado (linea 31 y 37). Campo `movement_Action` con underscore (`movement_Action`) - verificar si el modelo `DocumentReception` espera este nombre exacto o si deberia ser `movement_action` en camelCase. |

---

## Performance / N+1

| Archivo | Problema |
|---------|----------|
| **CustomersTable** | Si `full_name` es un accessor en el modelo que concatena `first_name` + `last_name`, no hay N+1. Pero si la tabla muestra `first_name` y `last_name` por separado ademas de `full_name`, son 3 columnas que leen de la misma fila - OK. |
| **DocumentsTable (Admin)** | Relaciones `type`, `customer`, `currentOffice`, `administration` se acceden via dot notation - **WARNING**: El `getEloquentQuery()` del recurso NO hace `with()` para eager loading. Esto causara N+1 en todas las tablas de documentos. **SOLUCION**: En `DocumentResource`, agregar `getEloquentQuery()` con `with(['type', 'customer', 'currentOffice', 'administration', 'priority'])`. |
| **DocumentReception.php:95** | `->query()` hace `->with(['latestMovement', 'latestMovement.toOffice', 'latestMovement.fromOffice'])` - esto SI hace eager loading, pero podria mejorarse agregando `->with(['type', 'currentOffice', 'customer', 'priority'])`. |

---

## Inconsistencias Generales

1. **Labels mixing**: Algunos recursos usan labels en español (`'Nombre'`, `'Creado'`), otros no usan labels en absoluto y confian en la traduccion automatica. Estandarizar a español en todos los campos.

2. **Navigation icons**: Todos los recursos usan `Heroicon::OutlinedRectangleStack` excepto Users. Cada recurso deberia tener un icono representativo.

3. **Vacío states**: `CustomersTable`, `UsersTable`, `PrioritiesTable` no tienen `emptyStateHeading/Description/Icon`. `DocumentsTable` (Admin) tampoco. Inconsistente con `AdministrationsTable`, `OfficesTable`, `DocumentTypesTable` que sí los tienen.

4. **Pagination**: `CustomersTable`, `UsersTable`, `DocumentsTable (User)`, `PrioritiesTable` usan `[5]`. `AdministrationsTable`, `OfficesTable`, `DocumentTypesTable` usan `[5]`. `DocumentsTable (Admin)` usa `[10, 25, 50]`. `UsersTable` usa `[5]`. Inconsistente.

5. **Admin DocumentForm vs User DocumentForm**: El form de Admin usa `TextInput` para relaciones (`document_type_id`, `gestion_id`, `priority_id`) mientras que el form de User usa `Select` con `->relationship()`. Estandarizar a `Select` con `->relationship()`.

---

## Issues de Seguridad / Bugs

| Severidad | Archivo | Descripcion |
|-----------|---------|-------------|
| **ALTA** | DocumentForm (Admin) | Campo `status` acepta cualquier string via `Select::make('status')->options([...])`. No usa el enum `DocumentStatus::class`. Deberia ser `Select::make('status')->options(DocumentStatus::class)`. |
| **ALTA** | HasForwardAction | `forwardAction()` actualiza el `status` del documento pero NO actualiza `current_office_id`. El documento sigue figurando en la oficina original, lo cual es incorrecto para la logica de derivacion. |
| **MEDIA** | DocumentForm (User) | `folio` usa `->required()->numeric()` pero NO tiene `unique()` validation. Podrian existir duplicados. |
| **MEDIA** | HasReceiveAction | Campo `movement_Action` con underscore en vez de camelCase. Verificar consistencia con el modelo `DocumentReception`. |
| **MEDIA** | UserForm | `email` tiene labels duplicados que se sobrescriben. Solo el ultimo (`Email address`) se aplica. El primero (`Correo electronico`) es ignorado. |
| **BAJA** | CustomersTable | No tiene acciones de bulk (DeleteBulkAction). Los registros no se pueden eliminar en masa. |

---

## Recomendaciones Prioritarias

1. **Corregir N+1 en DocumentsTable**: Agregar `getEloquentQuery()` en `DocumentResource` con `with(['type', 'customer', 'currentOffice', 'administration', 'priority'])`.

2. **Corregir HasForwardAction**: El `forwardAction()` debe actualizar `current_office_id` del documento a la oficina destino.

3. **Unificar icons de navegacion**: Asignar un icono unico a cada recurso.

4. **Estandarizar forms**: Usar `Select::make('status')->options(DocumentStatus::class)` en lugar de arrays hardcodeados. Usar `DatePicker` para campos de fecha. Agregar labels en español a todos los campos.

5. **Agregar empty states** a todas las tablas que faltan.
