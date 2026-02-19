# AGENTS.md

Essential information for agentic coding agents working in this repository.

## Project Overview

Laravel 12 document management system ("tramite" = procedure/process) with Filament v5 admin panel. Spanish terminology throughout (tramite, gestion, origen).

## Tech Stack

- PHP 8.4, Laravel 12, Filament v5, Livewire v4
- Pest 4 for testing, Tailwind CSS v4, MariaDB, Vite 7

## Commands

### Development
```bash
composer run dev          # Full stack (server + queue + logs + vite)
php artisan serve         # Laravel server only
npm run dev               # Vite frontend
npm run build             # Build assets
```

### Testing (Pest)
```bash
php artisan test --compact                           # All tests
php artisan test --compact tests/Feature/XxxTest.php # Single file
php artisan test --compact --filter=testName         # Single test by name
./vendor/bin/pest                                     # Direct Pest
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
php artisan make:model Name -mfs # Model + migration + factory + seeder
```

### Artisan Scaffolding
```bash
php artisan make:test FeatureTest --pest           # Feature test
php artisan make:test UnitTest --pest --unit       # Unit test
php artisan make:filament-resource ModelName       # Filament resource
php artisan make:livewire ComponentName            # Livewire component
```

## Code Style

### PHP Standards
- 4-space indentation, LF line endings, no trailing whitespace
- Always use curly braces for control structures
- Constructor property promotion: `public function __construct(public Service $service) {}`
- Explicit return types and parameter types required
- No empty `__construct()` unless private

### Naming Conventions
- Models: PascalCase (`User`, `Document`)
- Methods/variables: camelCase, descriptive (`isRegisteredForDiscounts`)
- Database: snake_case plural tables, snake_case columns
- Enums: TitleCase keys (`REGISTERED`, `IN_PROCESS`)

### Imports
- Single-line `use` statements, grouped order: Laravel → third-party → app
- Remove unused imports (Pint handles this)

### Comments
- Prefer PHPDoc blocks over inline comments
- Add array shape type definitions when helpful

## Laravel Patterns

### Models
- Eloquent relationships with return type hints
- Casts in `casts()` method, not `$casts` property
- Use fillable arrays for mass assignment
- Create factories/seeders for all models

### Controllers & Validation
- Form Request classes for validation (not inline)
- Include validation rules AND custom error messages
- Check sibling Form Requests for array vs string validation rules

### Configuration
- Use `config('app.name')`, never `env('APP_NAME')` outside config files

### Database
- Prefer `Model::query()` over `DB::`
- Eager load to prevent N+1 problems
- When modifying columns in migrations, include all previously defined attributes

## Filament Patterns

### Components
- Static `make()` methods for initialization
- Layout: `Filament\Schemas\Components\` (Grid, Section, Fieldset, Tabs)
- Form fields: `Filament\Forms\Components\` (TextInput, Select)
- Infolists: `Filament\Infolists\Components\` (TextEntry, IconEntry)
- Utilities: `Filament\Schemas\Components\Utilities\Get`, `Set`
- Actions: `Filament\Actions\` (no `Filament\Tables\Actions\`)
- Icons: `Filament\Support\Icons\Heroicon` enum

### Key Patterns
```php
// Conditional visibility
TextInput::make('company_name')
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

// Computed column
TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),
```

### File Visibility
- Files are `private` by default in Filament
- Use `->visibility('public')` for public access

## Testing (Pest)

### Conventions
- Use factories; check existing factory states first
- Test happy paths, failure paths, edge cases
- Use datasets for validation rule tests
- Use `assertForbidden`/`assertNotFound` instead of `assertStatus(403)`
- Browser tests go in `tests/Browser/`

### Filament Testing
```php
livewire(CreateUser::class)
    ->fillForm(['name' => 'Test', 'email' => 'test@example.com'])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();
```

## Tailwind CSS v4

- Import: `@import "tailwindcss"` (not `@tailwind` directives)
- Config: CSS-first with `@theme` directive, no `tailwind.config.js`
- Spacing: Use `gap-*` utilities, not margins between items
- Dark mode: Use `dark:` prefix if existing pages support it

## Important Rules

- **Never** change dependencies without approval
- **Never** remove tests without approval
- **Always** check existing patterns before creating new ones
- **Always** use `php artisan make:` commands for scaffolding
- **Always** run `vendor/bin/pint --dirty` before committing
- **Always** use `--no-interaction` with Artisan commands

## Laravel 12 Structure

- Middleware configured in `bootstrap/app.php` via `withMiddleware()`
- `bootstrap/providers.php` for service providers
- No `app/Http/Kernel.php` or `app/Console/Kernel.php`
- Console commands in `app/Console/Commands/` auto-registered

## File Locations

- Models: `app/Models/`
- Enums: `app/Enum/`
- Filament Resources: `app/Filament/Resources/{ModelName}/`
- Livewire: `app/Livewire/`
- Factories: `database/factories/`
- Tests: `tests/Feature/`, `tests/Unit/`, `tests/Browser/`
