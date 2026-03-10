# AGENTS.md - Development Guidelines

## Project Overview
- **PHP**: 8.4.18 | **Laravel**: 12.x | **Filament**: 5.x | **Livewire**: 4.x | **Pest**: 4.x | **Tailwind**: 4.x

## Commands

### Testing
```bash
composer test                              # Run all tests
php artisan test --compact                # Compact output
php artisan test --compact --filter=name   # Single test
php artisan pest --filter=name             # Using pest directly
php artisan test tests/Feature/FileTest.php # Single file
```

### Linting & Formatting
```bash
vendor/bin/pint --dirty --format agent    # Format modified files only
vendor/bin/pint --format agent             # Format all files
```

### Frontend & Dev Server
```bash
npm run dev        # Hot reload dev server
npm run build      # Production build
composer run dev   # Full dev: serve + queue + pail + vite
php artisan serve  # Simple server
```

### Code Generation
```bash
php artisan make:model Document -mfs       # Model + Migration + Factory + Seeder
php artisan make:resource DocumentResource # Filament Resource
php artisan make:test --pest DocumentTest  # Feature test
php artisan make:test --pest --unit Test   # Unit test
```

## Code Style

### PHP
- Always use curly braces for control structures, even single-line bodies
- Use PHP 8 constructor property promotion: `public function __construct(public Type $prop) { }`
- Always use explicit return types and type hints
- Enum keys: TitleCase (e.g., `DocumentStatus::Registrado`)
- Use nullable types with `?` (e.g., `?string`)
- Prefer PHPDoc blocks over inline comments

### Laravel
- Use `php artisan make:` commands for new files
- Use Eloquent relationships; avoid `DB::` queries
- Use eager loading (`with()`) to prevent N+1
- Use Form Request classes for validation
- Use `config()` not `env()` outside config files
- Create factories and seeders for new models

### Filament
- Table configs: `Tables/TableName.php`
- Form configs: `Schemas/SchemaName.php`
- Follow existing patterns in `app/Filament/`

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

## Laravel 12 Specifics
- Middleware in `bootstrap/app.php`, not `app/Http/Kernel.php`
- Console commands auto-registered in `app/Console/Commands/`
- Use `casts()` method on models instead of `$casts` property

## Error Handling
- Use `try/catch` with specific exception handling
- Throw meaningful exceptions with context
- Use Laravel's exception rendering

## Important Notes
- Always run `vendor/bin/pint --dirty --format agent` after editing PHP files
- Use `search-docs` tool for Laravel/Pest/Filament documentation
- Activate skills: `pest-testing`, `tailwindcss-development`
- Use Laravel Boost tools: `tinker`, `database-query`, `database-schema`, `browser-logs`
- Do NOT delete tests without approval
