# Tramita YA - Project Context

## Project Overview

**Tramita YA** is a document management and tracking system built with **Laravel 12** and **Filament v5**. It provides a complete workflow for document registration, tracking, and routing between offices.

### Core Features
- **Document Management**: Registration, tracking, and routing of documents
- **Workflow States**: Documents progress through states (Registrado → En Proceso → Respondido → Finalizado, with Rechazado/Cancelado as terminal states)
- **Multi-Office**: Document routing between different offices
- **User Roles**: Authentication with FilamentShield RBAC
- **Audit Trail**: Change logging via Laravel Auditing
- **Backups**: Automated database backups with FilamentSpatieLaravelBackup
- **Self-Service Portal**: User panel for document registration
- **Admin Panel**: Complete system administration

---

## Technology Stack

| Component | Version |
|-----------|---------|
| PHP | 8.2+ |
| Laravel | 12.x |
| Filament | 5.x |
| Livewire | 4.x |
| Tailwind CSS | 4.x |
| Pest | 4.x |
| Vite | 7.x |

### Key Dependencies
- **FilamentShield**: Role-based access control
- **Laravel Auditing**: Model change tracking
- **FilamentSpatieLaravelBackup**: Database backups
- **Filament Media Action**: PDF file viewing
- **AsmitFilamentUpload**: File uploads

---

## Building & Running

### Installation
```bash
# Clone and setup
git clone https://github.com/LC-jhony/tramite.git
cd tramite

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan make:filament-user

# Build assets
npm run build
```

### Development Commands
```bash
# Full dev environment (server + queue + logs + vite)
composer run dev

# Individual services
php artisan serve              # Laravel development server
npm run dev                    # Vite hot reload
php artisan queue:listen       # Queue worker
php artisan pail             # Log viewer

# Production build
npm run build
```

### Testing
```bash
composer test                          # Run all tests
php artisan test --compact             # Compact output
php artisan test --filter=name         # Single test by name
php artisan test tests/Feature/X.php   # Single test file
```

### Code Quality
```bash
vendor/bin/pint --dirty --format agent    # Format modified files
vendor/bin/pint --format agent            # Format all files
```

---

## Project Structure

```
app/
├── Enum/
│   ├── DocumentStatus.php      # Document workflow states
│   └── MovementAction.php      # Movement action types
├── Filament/
│   ├── Resources/              # Admin panel resources
│   │   ├── Administrations/    # Administrative periods
│   │   ├── Customers/          # Customer management
│   │   ├── Documents/          # Document management
│   │   ├── DocumentTypes/      # Document types
│   │   ├── Offices/            # Office management
│   │   ├── Priorities/         # Priority levels
│   │   └── Users/              # User management
│   └── User/                   # User-facing panel
│       ├── Resources/
│       │   └── Documents/
│       └── Pages/
├── Http/
├── Livewire/
├── Mail/
├── Models/
│   ├── Administration.php      # Administrative period
│   ├── Customer.php            # Customers/remitters
│   ├── Document.php            # Main document model
│   ├── DocumentFile.php        # Attached files
│   ├── DocumentReception.php   # Reception records
│   ├── DocumentType.php        # Document types
│   ├── Movement.php            # Document movements
│   ├── Office.php              # Offices
│   ├── Priority.php            # Priority levels
│   └── User.php                # Users
├── Providers/
└── Traits/
    ├── HasForwardAction.php    # Forward action trait
    ├── HasReceiveAction.php    # Receive action trait
    └── HasRejectAction.php     # Reject action trait
```

---

## Database Models

| Model | Description |
|-------|-------------|
| `Document` | Core document with number, case, subject, status |
| `DocumentType` | Types: Oficio, Carta, Memorando, etc. |
| `Customer` | Customers/remitters (individuals or companies) |
| `Office` | Offices that receive and route documents |
| `Administration` | Administrative period/year |
| `Priority` | Priority levels: Urgente, Normal, Diferido |
| `Movement` | Document movements between offices |
| `DocumentReception` | Reception records |
| `DocumentFile` | Attached files to documents |
| `User` | System users |

### Document Workflow
```
Registrado → En Proceso → Respondido → Finalizado
                ↓
         Rechazado / Cancelado
```

---

## Development Conventions

### PHP Code Style
- Always use curly braces (even for single-line bodies)
- PHP 8 constructor property promotion: `public function __construct(public Type $prop) {}`
- Explicit return types and type hints required
- Enum keys: TitleCase (e.g., `DocumentStatus::Registrado`)
- Prefer PHPDoc blocks over inline comments
- Use array shape type definitions in PHPDoc when appropriate

### Laravel Conventions
- Use `php artisan make:` commands for scaffolding
- Use Eloquent relationships; avoid `DB::` queries
- Use eager loading (`with()`) to prevent N+1
- Use Form Request classes for validation
- Use `config()` not `env()` outside config files
- Use `casts()` method on models (not `$casts` property)
- Create factories and seeders for new models
- Use `ShouldQueue` interface for time-consuming jobs

### Filament Conventions
- Table configs: `Tables/TableName.php`
- Form configs: `Schemas/SchemaName.php`
- Use `Get $get` for conditional form logic
- Actions use `Filament\Actions\` namespace
- Icons use `Filament\Support\Icons\Heroicon` enum
- File visibility is `private` by default
- Grid/Section/Fieldset don't span all columns by default

### Naming Conventions
| Type | Convention | Example |
|------|------------|---------|
| Models | Singular PascalCase | `Document`, `Movement` |
| Tables | Plural snake_case | `documents`, `movements` |
| Methods | camelCase | `isRegistered()`, `getActiveOffices()` |
| Variables | snake_case | `$document`, `$latest_movement` |
| Enums | TitleCase keys | `DocumentStatus::Registrado` |

---

## Testing (Pest)

### Test Structure
```php
it('has valid data', function () {
    expect(true)->toBeTrue();
});
```

### Filament Component Testing
```php
// Table test
livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name);

// Form test
livewire(CreateUser::class)
    ->fillForm(['name' => 'Test'])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

// Validation test
livewire(CreateUser::class)
    ->fillForm(['name' => null])
    ->call('create')
    ->assertHasFormErrors(['name' => 'required']);
```

### Testing Rules
- Use `php artisan make:test --pest` to create tests
- Use factories: `Document::factory()->create()`
- Use `livewire()` helper for Livewire components
- Do NOT delete tests without approval
- Use specific assertions: `assertSuccessful()` not `assertStatus(200)`

---

## Configuration

### Environment Variables
```env
APP_NAME="Tramita YA"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tramite
DB_USERNAME=root
DB_PASSWORD=
```

### Available Panels
- **User Panel**: `http://localhost/user`
- **Admin Panel**: `http://localhost/admin`
- **Public Registration**: `http://localhost/` (DocumentRegister)

---

## Important Notes

- **Always run** `vendor/bin/pint --dirty --format agent` after editing PHP files
- **Laravel 12**: Middleware configured in `bootstrap/app.php` (no `app/Http/Kernel.php`)
- **Console commands**: Auto-registered from `app/Console/Commands/`
- **EditorConfig**: 4 spaces indentation (2 spaces for YAML), UTF-8, LF line endings
- **Do NOT delete tests** without approval
- **Do NOT change dependencies** without approval
- **Stick to existing directory structure**; don't create new base folders without approval

---

## Laravel Boost Tools Available

| Tool | Purpose |
|------|---------|
| `database-query` | Execute read-only SQL queries |
| `database-schema` | Inspect table structure |
| `tinker` | Execute PHP code in tinker |
| `search-docs` | Search Laravel/Pest/Filament docs |
| `browser-logs` | Read browser errors and exceptions |
| `get-absolute-url` | Generate absolute URLs for routes |

---

## Routes

- **Public**: `/` → Document registration (Livewire: `DocumentRegister`)
- **Admin**: `/admin` → Filament admin panel
- **User**: `/user` → Filament user panel

---

## Security

- Authentication via Laravel Sanctum
- Email verification enabled
- Role-based authorization policies
- Audit trail on all model changes
