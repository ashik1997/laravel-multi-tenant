# Setup Instructions

## Installation Steps

### 1. Database Migration

Run the migration to add custom domain support:

```bash
php artisan migrate
```

This creates the necessary columns in the `domains` table for FTP configuration and upload settings.

### 2. Register Service Provider (if not auto-discovered)

In `config/app.php`, add the DomainServiceProvider to the providers array:

```php
'providers' => [
    // ... other providers
    App\Providers\DomainServiceProvider::class,
],
```

**Note**: In Laravel 11+, service providers are auto-discovered by default, so this step may not be necessary.

### 3. Register Facade (Optional)

Add to `config/app.php` aliases array if you want to use the facade:

```php
'aliases' => [
    // ... other aliases
    'DomainManager' => App\Facades\DomainManager::class,
],
```

### 4. Add Middleware (Optional)

To automatically resolve domains from hostnames, add the middleware to your HTTP kernel or routes:

```php
// In routes/web.php or bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(\App\Http\Middleware\ResolveDomainMiddleware::class);
})
```

### 5. Publish Configuration (Optional)

If you want to create a configuration file:

```bash
php artisan vendor:publish --tag=domain-config
```

## Quick Start

### 1. Create a Domain

```php
use App\Models\Domain;
use App\Models\Tenant;

$tenant = Tenant::first();

$domain = Domain::create([
    'tenant_id' => $tenant->id,
    'domain' => 'shop.example.com',
    'ftp_host' => 'ftp.example.com',
    'ftp_username' => 'ftpuser',
    'ftp_password' => 'ftppass',
    'ftp_port' => 21,
    'upload_path' => '/public_html/uploads',
    'max_upload_size' => 52428800, // 50MB
]);
```

### 2. Upload Files

```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);

fetch(`/api/uploads/domain/${domainId}`, {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

### 3. Switch Tenant

```javascript
fetch('/api/tenant/switch', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ tenant_id: tenantId })
})
.then(response => response.json())
.then(data => console.log(data));
```

## Environment Variables

Add these to your `.env` file:

```
DOMAIN_MAX_UPLOAD_SIZE=52428800
DOMAIN_UPLOAD_DISK=public
DOMAIN_ENABLE_FTP_SYNC=true
```

## API Routes

All routes are prefixed with `/api/` and are protected by authentication middleware.

### Domain Management
- `GET /domains` - List all domains
- `POST /domains` - Create a domain
- `GET /domains/{id}` - Get domain details
- `PATCH /domains/{id}` - Update domain
- `DELETE /domains/{id}` - Delete domain
- `POST /domains/{id}/test-ftp` - Test FTP connection

### Tenant Switching
- `GET /tenant/available` - List available tenants
- `POST /tenant/switch` - Switch to a tenant
- `GET /tenant/current` - Get current tenant context
- `POST /tenant/initialize/{id}` - Initialize tenant session

### File Uploads
- `POST /uploads/domain/{id}` - Upload single file
- `POST /uploads/domain/{id}/multiple` - Upload multiple files
- `GET /uploads/domain/{id}/config` - Get upload config
- `DELETE /uploads/domain/{id}/file` - Delete file

## Files Generated

### Models
- `app/Models/Domain.php` - Domain model with FTP configuration

### Controllers
- `app/Http/Controllers/DomainController.php` - Domain management endpoints
- `app/Http/Controllers/TenantSwitchController.php` - Tenant switching endpoints
- `app/Http/Controllers/UploadController.php` - File upload endpoints

### Services
- `app/Services/DomainService.php` - Domain utility service

### Middleware
- `app/Http/Middleware/ResolveDomainMiddleware.php` - Automatic domain resolution

### Providers
- `app/Providers/DomainServiceProvider.php` - Service provider for DI

### Facades
- `app/Facades/DomainManager.php` - Facade for DomainService

### Migrations
- `database/migrations/2026_05_13_000001_add_custom_domain_support.php` - Database schema

## Troubleshooting

### FTP Connection Issues

Test FTP connection via API:
```bash
POST /api/domains/{domainId}/test-ftp
```

Or in PHP:
```php
$domain = Domain::find($domainId);
$isConnected = app(DomainService::class)->testFtpConnection($domain);
```

### Upload Size Limits

Check upload configuration:
```bash
GET /api/uploads/domain/{domainId}/config
```

### Storage Statistics

```php
$service = app(DomainService::class);
$stats = $service->getStorageStats($domain);
// Returns: total_size, total_size_mb, file_count, max_size, usage_percentage
```

## Support

For more details, see `MULTI_DOMAIN_IMPLEMENTATION.md`
