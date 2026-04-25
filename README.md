# Tramita YA

Sistema de gestion de tramites documentarios desarrollado con Laravel 12 y Filament v5.

## Caracteristicas

- **Gestion de Documentos**: Registro, seguimiento y derivation de documentos
- **Flujo de Trabajo**: Estados de documento (Registrado, En Proceso, Respondido, Finalizado, Rechazado, Cancelado)
- **Multi-Oficina**: Derivacion de documentos entre oficinas
- **Usuarios y Roles**: Sistema de autenticacion con FilamentShield
- **Auditoria**: Registro de cambios con Laravel Auditing
- **Copias de Seguridad**: Respaldos automaticos con FilamentSpatieLaravelBackup
- **Panel de Usuario**: Portal self-service para registro de documentos
- **Panel de Administracion**: Gestion completa del sistema

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ / SQLite / PostgreSQL

## Instalacion

```bash
# Clonar repositorio
git clone https://github.com/LC-jhony/tramite.git
cd tramite

# Instalar dependencias PHP
composer install

# Instalar dependencias JS
npm install

# Copiar archivo de configuracion
cp .env.example .env

# Generar clave de aplicacion
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Crear usuario administrador
php artisan make:filament-user

# Compilar assets
npm run build
```

## Configuracion

### Variables de Entorno

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

### Paneles Disponibles

- **Panel de Usuario**: `http://localhost/user`
- **Panel de Administracion**: `http://localhost/admin`

## Documentación

- [Manual de Usuario (Español)](MANUAL_USUARIO.md)
- [Estructura de la Aplicación](#estructura-de-la-aplicacion)


```
app/
├── Enum/                    # Enums del sistema
│   └── DocumentStatus.php   # Estados de documento
├── Filament/
│   ├── Resources/           # Recursos admin
│   │   ├── Administrations/
│   │   ├── Customers/
│   │   ├── DocumentTypes/
│   │   ├── Documents/
│   │   ├── Offices/
│   │   ├── Priorities/
│   │   └── Users/
│   └── User/
│       ├── Resources/      # Recursos usuario
│       │   └── Documents/
│       └── Pages/          # Paginas personalizadas
├── Models/
│   ├── Administration.php   # Gestion/Periodo
│   ├── Customer.php         # Clientes
│   ├── Document.php         # Documentos
│   ├── DocumentFile.php     # Archivos adjuntos
│   ├── DocumentReception.php # Recepciones
│   ├── DocumentType.php     # Tipos de documento
│   ├── Movement.php         # Movimientos/Derivaciones
│   ├── Office.php           # Oficinas
│   ├── Priority.php         # Prioridades
│   └── User.php             # Usuarios
└── Trait/
    ├── HasForwardAction.php   # Accion derivar
    ├── HasReceiveAction.php  # Accion recibir
    └── HasRejectAction.php    # Accion rechazar
```

## Modelos y Relaciones

| Modelo | Descripcion |
|--------|-------------|
| **Document** | Documentos del sistema con numero, expediente, asunto |
| **DocumentType** | Tipos de documento (Oficio, Carta, Memorando, etc.) |
| **Customer** | Clientes/Remitentes (personas o empresas) |
| **Office** | Oficinas que reciben y derivan documentos |
| **Administration** | Gestion/Periodo administrativo |
| **Priority** | Prioridades (Urgente, Normal, Diferido) |
| **Movement** | Movimientos de derivacion entre oficinas |
| **DocumentReception** | Registro de recepciones |
| **DocumentFile** | Archivos adjuntos a documentos |
| **User** | Usuarios del sistema |

## Estados de Documento

```
Registrado → En Proceso → Respondido → Finalizado
                ↓
           Rechazado / Cancelado
```

## Comandos Utiles

```bash
# Desarrollo
php artisan serve              # Servidor de desarrollo
npm run dev                    # Vite hot reload
composer run dev               # Dev completo (serve + queue + pail + vite)

# Testing
composer test                  # Ejecutar pruebas
php artisan test --compact     # Salida compacta

# Linting
vendor/bin/pint --dirty       # Formatear archivos modificados

# Base de datos
php artisan migrate            # Migrar
php artisan migrate:fresh      # Migrar desde cero
php artisan db:seed           # Sembrar datos
```

## Plugins Instalados

| Plugin | Proposito |
|--------|-----------|
| **Filament v5** | Admin panel framework |
| **FilamentShield** | Control de acceso basado en roles |
| **FilamentAuditing** | Auditoria de cambios |
| **FilamentSpatieLaravelBackup** | Respaldos de base de datos |
| **FilamentMediaAction** | Visualizacion de archivos PDF |
| **AsmitFilamentUpload** | Carga de archivos |

## Seguridad

- Autenticacion via Laravel Sanctum
- Verificacion de email
- Politicas de autorizacion por rol
- Auditoria de cambios en modelos

## Licencia

MIT License
