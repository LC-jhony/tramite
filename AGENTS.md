# AGENTS.md - Development Guidelines

## Project Overview

- **PHP**: 8.4.18
- **Laravel**: 12.x
- **Filament**: 5.x
- **Livewire**: 4.x
- **Pest**: 4.x
- **Tailwind CSS**: 4.x

## Commands

### Testing
```bash
# Run all tests
composer test
php artisan test

# Run tests with compact output
php artisan test --compact

# Run specific test
php artisan test --compact --filter=testName
php artisan pest --filter=testName

# Run tests in a specific file
php artisan test tests/Feature/DocumentTest.php
```

### Linting & Formatting
```bash
# Format PHP files (Pint)
vendor/bin/pint --format agent           # Format all
vendor/bin/pint --dirty --format agent   # Format only modified files

# Run Pint in test mode (doesn't change files)
vendor/bin/pint --test --format agent
```

### Frontend
```bash
npm run dev      # Development server with hot reload
npm run build    # Production build
```

### Development Server
```bash
composer run dev    # Runs: php artisan serve + queue + pail + vite
php artisan serve
```

### Artisan
```bash
php artisan make:model Document -mfs          # Model + Migration + Factory + Seeder
php artisan make:resource DocumentResource     # Filament Resource
php artisan make:test --pest DocumentTest      # Pest test
php artisan make:test --pest --unit UnitTest   # Unit test
php artisan make:migration create_documents_table
```

## Code Style

### PHP
- Always use curly braces for control structures
- Use PHP 8 constructor property promotion
- Always use explicit return types and type hints
- Enum keys should be TitleCase (e.g., `DocumentStatus::Registrado`)
- Prefer PHPDoc blocks over inline comments
- Use nullable types with `?` (e.g., `?string`)

### Laravel Conventions
- Use `php artisan make:` commands for new files
- Use Eloquent relationships over raw queries
- Avoid `DB::`; prefer `Model::query()`
- Use eager loading (`with()`) to prevent N+1 queries
- Use Form Request classes for validation
- Use `config()` not `env()` outside config files
- Create factories and seeders for new models

### Filament
- Put table configurations in `Tables/TableName.php`
- Put form configurations in `Schemas/SchemaName.php`
- Use traits for reusable actions (e.g., `HasForwardAction`)
- Follow existing resource patterns in `app/Filament/`

### Naming
- Models: `Document`, `Movement` (singular, PascalCase)
- Tables: `documents`, `movements` (plural, snake_case)
- Controllers: `DocumentController`
- Methods: `isRegistered()`, `getActiveOffices()` (camelCase, descriptive)
- Variables: `$document`, `$latestMovement` (camelCase)

### Database
- Use migrations for all schema changes
- Include all column attributes when modifying columns
- Use proper foreign key relationships

## Project Structure

```
app/
├── Filament/
│   └── User/
│       └── Resources/
│           └── Documents/           # Resource-based organization
│               ├── DocumentResource.php
│               ├── Pages/
│               ├── Tables/
│               └── Schemas/
├── Models/                         # Eloquent models
├── Enum/                           # Enums
├── Trait/                          # Traits
tests/
├── Feature/                        # Feature tests
├── Unit/                           # Unit tests
database/
├── migrations/
├── factories/
└── seeders/
```

## Important Notes

- Always run `vendor/bin/pint --dirty --format agent` after editing PHP files
- Use `search-docs` tool for Laravel/Pest/Filament documentation
- Activate relevant skills: `pest-testing`, `tailwindcss-development`
- Check existing code patterns before creating new files
