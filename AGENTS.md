# AGENTS.md - Development Guidelines

## Project Overview
- **PHP**: 8.2+ | **Laravel**: 12.x | **Filament**: 5.x | **Livewire**: 4.x | **Pest**: 4.x | **Tailwind**: 4.x

---

## Commands

### Testing
```bash
composer test                              # Run all tests
php artisan test --compact                 # Compact output
php artisan test --compact --filter=name  # Single test by name
php artisan test tests/Feature/Test.php    # Single file
php artisan pest --filter=name             # Using pest directly
```

### Linting & Formatting
```bash
vendor/bin/pint --dirty --format agent    # Format modified files only
vendor/bin/pint --format agent            # Format all files
```

### Frontend & Dev Server
```bash
npm run dev        # Vite hot reload
npm run build      # Production build
composer run dev   # Full dev: serve + queue + pail + vite
php artisan serve  # Simple server
```

### Code Generation
```bash
php artisan make:model Document -mfs    # Model + Migration + Factory + Seeder
php artisan make:resource DocumentResource
php artisan make:test --pest DocumentTest
```

---

## Code Style

### PHP
- Always use curly braces, even for single-line bodies
- Use PHP 8 constructor property promotion: `public function __construct(public Type $prop) { }`
- Always use explicit return types and type hints
- Enum keys: TitleCase (e.g., `DocumentStatus::Registrado`)
- Prefer PHPDoc blocks over inline comments
- Add useful array shape type definitions in PHPDoc when appropriate

### Laravel
- Use `php artisan make:` commands for new files
- Use Eloquent relationships; avoid `DB::` queries
- Use eager loading (`with()`) to prevent N+1
- Use Form Request classes for validation
- Use `config()` not `env()` outside config files
- Use `casts()` method on models instead of `$casts` property
- Create factories and seeders for new models
- Use queued jobs with `ShouldQueue` interface for time-consuming operations

### Filament
- Table configs: `Tables/TableName.php`
- Form configs: `Schemas/SchemaName.php`
- Use `Get $get` for conditional form logic
- Actions use `Filament\Actions\` namespace (not Tables/Actions)
- Icons use `Filament\Support\Icons\Heroicon` enum
- File visibility is `private` by default; use `->visibility('public')` when needed
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

## Testing with Pest

### Basic Test Structure
```php
it('has valid data', function () {
    expect(true)->toBeTrue();
});
```

### Testing Filament Components
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

### Rules
- Use `php artisan make:test --pest` to create tests
- Use factories: `Document::factory()->create()`
- Use `livewire()` helper for Livewire component testing
- Do NOT delete tests without approval
- Use specific assertions: `assertSuccessful()` not `assertStatus(200)`

### Database
- Use migrations for all schema changes
- Include all column attributes when modifying columns
- Limit eagerly loaded records: `$query->latest()->limit(10)`

---

## Laravel 12 Specifics
- Middleware in `bootstrap/app.php` (no `app/Http/Kernel.php`)
- Console commands auto-registered in `app/Console/Commands/`

---

## EditorConfig
- 4 spaces for indentation (except YAML: 2 spaces)
- UTF-8 charset, LF line endings

---

## Important Notes
- Always run `vendor/bin/pint --dirty --format agent` after editing PHP files
- Activate skills: `pest-testing`, `tailwindcss-development`, `filament-*`
- Use Laravel Boost tools: `tinker`, `database-query`, `database-schema`, `browser-logs`, `search-docs`
- Follow existing code conventions; check sibling files for correct patterns
- Do NOT delete tests without approval
- Do NOT change dependencies without approval
- Stick to existing directory structure; don't create new base folders without approval
- Be concise in replies; focus on what's important

---

## Laravel Boost Tools
| Tool | Purpose |
|------|---------|
| `database-query` | Execute read-only SQL queries |
| `database-schema` | Inspect table structure |
| `tinker` | Execute PHP code: `php artisan tinker --execute "..."` |
| `search-docs` | Search Laravel/Pest/Filament docs |
| `browser-logs` | Read browser errors and exceptions |
| `get-absolute-url` | Generate absolute URLs for routes |

---

## Directory Structure
```
app/
├── Filament/
│   └── Resources/
│       └── ModelResource/
│           ├── Resource.php
│           ├── Tables/ModelTable.php
│           ├── Schemas/ModelForm.php
│           └── Pages/
├── Models/
├── Traits/
├── Enums/
├── Http/Controllers/
└── Console/Commands/
bootstrap/app.php
routes/
```
