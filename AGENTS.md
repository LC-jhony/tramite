# AGENTS.md

Essential information for agentic coding agents working in this repository.

## Project Overview

Laravel 12 document management system ("tramite" = procedure/process) with Filament v5 admin panel. Spanish terminology throughout.

## Tech Stack

- PHP 8.4, Laravel 12.52, Filament v5, Livewire v4
- Pest 4 for testing, Tailwind CSS v4, MariaDB

---

## Commands

### Running Tests
```bash
php artisan test --compact                           # All tests
php artisan test --compact tests/Feature/XxxTest.php # Single file
php artisan test --compact --filter=testName         # Single test by name
```

### Code Quality
```bash
vendor/bin/pint --dirty    # Format changed files (run before commit)
vendor/bin/pint --test     # Check without fixing
```

### Database
```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Fresh with seeders
```

### Scaffolding
```bash
php artisan make:model Name -mfs           # Model + migration + factory + seeder
php artisan make:test NameTest --pest     # Feature test
php artisan make:filament-resource ModelName # Filament resource
php artisan make:livewire ComponentName    # Livewire component
```

---

## Code Style Guidelines

### PHP Standards
- **Indentation:** 4 spaces, LF line endings, no trailing whitespace
- **Control structures:** Always use curly braces, even for single lines
- **Constructor property promotion:** Use PHP 8 constructor promotion
- **Types:** Explicit return types and parameter types required
- **Empty constructors:** Don't use empty `__construct()` unless private

### Naming Conventions
- **Models/Classes:** PascalCase (`User`, `Document`)
- **Methods/variables:** camelCase, descriptive (`isRegisteredForDiscounts`)
- **Database:** snake_case plural tables, snake_case columns
- **Enums:** TitleCase keys (`REGISTERED`, `IN_PROCESS`)

### Imports
- Single-line `use` statements
- Grouped order: Laravel → third-party → app
- Remove unused imports (Pint handles this)

### Comments
- Prefer PHPDoc blocks over inline comments
- Add array shape type definitions when helpful

---

## Laravel Patterns

### Models
- Use `casts()` method, not `$casts` property
- Eloquent relationships with return type hints
- Fillable arrays for mass assignment
- Create factories/seeders for all models

### Controllers & Validation
- Use Form Request classes (not inline validation)
- Include validation rules AND custom error messages
- Check sibling Form Requests for array vs string validation rules

### Database
- Prefer `Model::query()` over `DB::`
- Eager load to prevent N+1 problems
- When modifying columns in migrations, include all previously defined attributes

### Configuration
- Use `config('app.name')`, never `env('APP_NAME')` outside config files

---

## Filament Patterns

### Components
- Static `make()` methods for initialization
- Form fields: `Filament\Forms\Components\` (TextInput, Select)
- Layout: `Filament\Schemas\Components\` (Grid, Section, Fieldset)
- Actions: `Filament\Actions\` (not `Filament\Tables\Actions\`)
- Icons: `Filament\Support\Icons\Heroicon` enum

### Key Patterns
```php
TextInput::make('company_name')
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),
```

### File Visibility
- Files are `private` by default
- Use `->visibility('public')` for public access

---

## Tailwind CSS v4

- Import: `@import "tailwindcss"` (not `@tailwind` directives)
- Config: CSS-first with `@theme` directive, no `tailwind.config.js`
- Spacing: Use `gap-*` utilities, not margins between items

---

## Testing (Pest)

### Conventions
- Use factories; check existing factory states first
- Test happy paths, failure paths, edge cases
- Use datasets for validation rule tests
- Use `assertForbidden`/`assertNotFound` instead of `assertStatus(403)`

### Authentication
```php
beforeEach(function () {
    actingAs(User::factory()->create());
});
```

### Filament Testing
```php
// Render
livewire(ListDocuments::class)->assertSuccessful();

// Fill form
livewire(CreateDocument::class)
    ->fillForm(['subject' => 'Test'])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

// Validation
->assertHasFormErrors(['subject' => 'required'])
->assertHasNoFormErrors();
```

---

## Important Rules

- **Never** change dependencies without approval
- **Never** remove tests without approval
- **Always** check existing patterns before creating new ones
- **Always** use `php artisan make:` commands for scaffolding
- **Always** run `vendor/bin/pint --dirty` before committing
- **Always** use `--no-interaction` with Artisan commands

---

## File Locations

| Type | Location |
|------|----------|
| Models | `app/Models/` |
| Enums | `app/Enum/` |
| Filament Resources | `app/Filament/Resources/{ModelName}/` |
| Livewire | `app/Livewire/` |
| Factories | `database/factories/` |
| Tests | `tests/Feature/`, `tests/Unit/`, `tests/Browser/` |

---

## Laravel 12 Structure

- Middleware configured in `bootstrap/app.php` via `withMiddleware()`
- `bootstrap/providers.php` for service providers
- No `app/Http/Kernel.php` or `app/Console/Kernel.php`
- Console commands in `app/Console/Commands/` auto-registered
