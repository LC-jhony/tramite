# AGENTS.md

This file contains essential information for agentic coding agents working in this repository.

## Project Overview

This is a Laravel 12 application using PHP 8.4 with Filament v5 for admin interfaces. It's a document management system ("tramite" meaning "procedure/process" in Spanish) with document tracking, movements, and file management capabilities.

## Tech Stack & Versions

- **PHP**: 8.4.16
- **Laravel**: 12.48.1
- **Filament**: 5.1.0 (admin panel framework)
- **Livewire**: 4.0.3
- **Pest**: 4.3.1 (testing framework)
- **Tailwind CSS**: 4.1.18
- **Database**: MariaDB
- **Node.js**: Vite 7.0.7 for frontend bundling

## Build/Test/Development Commands

### Development
```bash
# Full development stack (server + queue + logs + vite)
composer run dev

# Start Laravel server only
php artisan serve

# Start Vite for frontend
npm run dev

# Build frontend assets
npm run build
```

### Testing
```bash
# Run all tests
php artisan test --compact

# Run specific test file
php artisan test --compact tests/Feature/ExampleTest.php

# Run filtered test by name
php artisan test --compact --filter=testName

# Run Pest directly
./vendor/bin/pest

# Run tests with coverage (if configured)
php artisan test --coverage
```

### Code Quality
```bash
# Format code with Laravel Pint (required before commits)
vendor/bin/pint --dirty

# Check formatting without fixing
vendor/bin/pint --test

# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models --reset
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name

# Factory/Seeder generation
php artisan make:model ModelName -mfs
```

## Code Style Guidelines

### PHP Standards
- **Indentation**: 4 spaces (configured in .editorconfig)
- **Line endings**: LF (Unix style)
- **Trailing whitespace**: Trimmed
- **PHP version**: 8.2+ features allowed
- **Constructor Property Promotion**: Use PHP 8+ syntax in `__construct()`
- **Type Declarations**: Always use explicit return types and parameter types
- **Braces**: Always use curly braces for control structures, even single lines

### Naming Conventions
- **Models**: PascalCase (User, Document, Movement)
- **Enums**: PascalCase with descriptive keys (DocumentStatus, MovementAction)
- **Controllers**: PascalCase + "Controller" suffix
- **Methods**: camelCase with descriptive names (isRegisteredForDiscounts, not discount())
- **Variables**: camelCase, descriptive names
- **Database Tables**: snake_case plural (documents, movements)
- **Columns**: snake_case (document_number, reception_date)

### Laravel-Specific Patterns

#### Models
- Use Eloquent relationships with proper return type hints
- Place casts in `casts()` method rather than `$casts` property (follow existing convention)
- Use fillable arrays for mass assignment
- Create factories and seeders for all models

#### Controllers & Validation
- Create Form Request classes for validation (not inline in controllers)
- Use proper HTTP status methods (`assertForbidden`, `assertNotFound` vs `assertStatus(403)`)

#### Filament Resources
- Use static `make()` methods for component initialization
- Follow the pattern: Resource → Pages → Schemas/Tables
- Use `Get $get` for conditional form field logic
- Use `state()` with Closure for computed column values

#### Enums
- Keys should be TitleCase in enums (REGISTERED, IN_PROCESS)
- Implement Filament's `HasLabel` interface for Filament integration

### File Structure Conventions
- **Models**: `app/Models/`
- **Enums**: `app/Enum/`
- **Filament Resources**: `app/Filament/Resources/{ModelName}/`
- **Livewire Components**: `app/Livewire/`
- **Factories**: `database/factories/`
- **Seeders**: `database/seeders/`
- **Migrations**: `database/migrations/`
- **Tests**: `tests/Feature/` and `tests/Unit/`

### Import Organization
- Use grouped imports with single-line `use` statements
- Order: Laravel/framework → third-party → application imports
- Remove unused imports (Pint will handle this)

### Error Handling
- Use Laravel's built-in exception handling
- Implement proper validation in Form Requests
- Use try-catch blocks only when necessary
- Log errors appropriately using Laravel's logging system

### Frontend (Tailwind CSS v4)
- Use Tailwind v4 `@import "tailwindcss"` syntax, not `@tailwind` directives
- Dark mode support with `dark:` prefix when existing pages support it
- Use gap utilities for spacing between items, not margins
- Configuration is CSS-first using `@theme` directive, no separate config file

### Testing Best Practices
- Write tests using Pest syntax with descriptive test names
- Use factories for test data; check existing factory states first
- Test happy paths, failure paths, and edge cases
- Use datasets for repetitive scenarios (especially validation rules)
- Use `assertForbidden`/`assertNotFound` instead of `assertStatus(403)`
- Browser tests go in `tests/Browser/` directory

### Code Quality Requirements
- **Always** run `vendor/bin/pint --dirty` before committing
- Use proper PHPDoc blocks for complex logic
- Add array shape type definitions for complex arrays when helpful
- Prefer PHPDoc blocks over inline comments for documentation

## Important Notes
- This is a document management system with Spanish terms (tramite, gestion, origen)
- File visibility in Filament is `private` by default - use `->visibility('public')` for public access
- Never change dependencies without approval
- Always check existing patterns before creating new ones
- Use `php artisan make:` commands for scaffolding new files
- Tests live in `tests/` directory and should not be removed without approval

## Laravel Boost MCP Tools
This project includes Laravel Boost with enhanced MCP server tools:
- **Database Query**: `laravel-boost_database-query` for read-only SQL operations
- **Database Schema**: `laravel-boost_database-schema` to inspect table structure  
- **Application Info**: `laravel-boost_application-info` for package versions and environment
- **Routes**: `laravel-boost_list-routes` to explore API endpoints
- **Logs**: `laravel-boost_read-log-entries` and `laravel-boost_browser-logs` for debugging
- **Config**: `laravel-boost_get-config` and `laravel-boost_list-available-config-keys`
- **Artisan**: `laravel-boost_list-artisan-commands` to discover commands
- **Tinker**: `laravel-boost_tinker` for PHP code execution
- **Search Docs**: `laravel-boost_search-docs` for version-specific documentation

## Common Artisan Commands
```bash
# List all available commands
php artisan list

# Make model with migration, factory, seeder
php artisan make:model ModelName -mfs

# Make Filament resource
php artisan make:filament-resource ModelName

# Make Livewire component
php artisan make:livewire ComponentName

# Make test
php artisan make:test FeatureTest --pest
php artisan make:test UnitTest --pest --unit
```

### Testing Rules (from .github/copilot-instructions.md)
```bash
# Run minimal tests before finalizing
php artisan test --compact --filter=testName

# Browser testing (Pest 4)
tests live in tests/Browser/ directory

# Use Pest datasets for validation rules
# Filament testing: use livewire() or Livewire::test()
# Test with specific methods: assertForbidden, assertNotFound
```