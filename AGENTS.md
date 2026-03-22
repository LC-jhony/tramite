# AGENTS.md - Development Guidelines

## Project Overview
- **PHP**: 8.4 | **Laravel**: 12.x | **Filament**: 5.x | **Livewire**: 4.x | **Pest**: 4.x | **Tailwind**: 4.x

---

## Commands

### Testing
```bash
composer test                              # Run all tests
php artisan test --compact                 # Compact output
php artisan test --compact --filter=name   # Single test by name
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
php artisan make:test --pest --unit Test
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
- Do not allow empty `__construct()` methods with zero parameters

### Laravel
- Use `php artisan make:` commands for new files
- Use Eloquent relationships; avoid `DB::` queries
- Use eager loading (`with()`) to prevent N+1
- Use Form Request classes for validation
- Use `config()` not `env()` outside config files
- Use `casts()` method on models instead of `$casts` property
- Create factories and seeders for new models
- When generating links, prefer named routes and `route()` function
- Use queued jobs for time-consuming operations with `ShouldQueue` interface
- Use Laravel's built-in auth features (gates, policies, Sanctum)

### Filament
- Table configs: `Tables/TableName.php`
- Form configs: `Schemas/SchemaName.php`
- Follow existing patterns in `app/Filament/`
- Use `Get $get` for conditional form logic
- Actions use `Filament\Actions\` namespace (not Tables/Actions)
- Icons use `Filament\Support\Icons\Heroicon` enum
- File visibility is `private` by default; use `->visibility('public')` when needed
- Grid/Section/Fieldset don't span all columns by default

### Naming Conventions
- Models: `Document`, `Movement` (singular, PascalCase)
- Tables: `documents`, `movements` (plural, snake_case)
- Controllers: `DocumentController`
- Methods: `isRegistered()`, `getActiveOffices()` (camelCase)
- Variables: `$document`, `$latestMovement`

### Database
- Use migrations for all schema changes
- Include all column attributes when modifying columns
- Use proper foreign key relationships
- Laravel 12: limit eagerly loaded records with `$query->latest()->limit(10)`

---

## Laravel 12 Specifics
- Middleware in `bootstrap/app.php`, not `app/Http/Kernel.php`
- Console commands auto-registered in `app/Console/Commands/`
- No `app/Console/Kernel.php` or `app/Http/Kernel.php`

---

## Error Handling
- Use `try/catch` with specific exception handling
- Throw meaningful exceptions with context
- Use Laravel's exception rendering

---

## Testing with Pest

### Testing Filament Components
```php
// Table test with livewire
livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name);

// Form test
livewire(CreateUser::class)
    ->fillForm(['name' => 'Test', 'email' => 'test@example.com'])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

// Validation test
livewire(CreateUser::class)
    ->fillForm(['name' => null])
    ->call('create')
    ->assertHasFormErrors(['name' => 'required']);

// Calling actions in tables
livewire(ListUsers::class)
    ->callAction(Action::make('promote'), ['role' => 'admin'])
    ->assertNotified();
```

### Rules
- Use `php artisan make:test --pest` to create tests
- Use factories for model creation: `Document::factory()->create()`
- Use `livewire()` helper for Livewire component testing
- Faker: Use `fake()->word()` or `$this->faker->word()`
- Do NOT delete tests without approval

---

## EditorConfig
- 4 spaces for indentation (except YAML: 2 spaces)
- UTF-8 charset, LF line endings
- Trim trailing whitespace

---

## Important Notes
- Always run `vendor/bin/pint --dirty --format agent` after editing PHP files
- Use `search-docs` tool for Laravel/Pest/Filament documentation
- Activate skills: `pest-testing`, `tailwindcss-development`
- Use Laravel Boost tools: `tinker`, `database-query`, `database-schema`, `browser-logs`
- Follow existing code conventions; check sibling files for correct patterns
- Do NOT delete tests without approval
- Do NOT change dependencies without approval
- Stick to existing directory structure; don't create new base folders without approval

---

## Laravel Boost Tools
- **database-query**: Execute read-only SQL queries against the database
- **database-schema**: Inspect table structure before writing migrations
- **tinker**: Execute PHP code for debugging: `php artisan tinker --execute "..."`
- **search-docs**: Search Laravel ecosystem docs (Laravel, Filament, Pest, Livewire)
- **browser-logs**: Read browser errors and exceptions
- **get-absolute-url**: Generate absolute URLs for named routes

---

## Directory Structure
```
app/
  Filament/
    Resources/
      ModelResource/
        Resource.php
        Tables/ModelTable.php
        Schemas/ModelForm.php
        Pages/ (List, Create, Edit)
    Pages/
    Widgets/
  Models/
  Traits/ (HasForwardAction, HasReceiveAction, HasRejectAction)
  Enums/
  Http/
    Controllers/
    Requests/ (Form Request classes)
  Console/
    Commands/
bootstrap/
  app.php
  providers.php
```
