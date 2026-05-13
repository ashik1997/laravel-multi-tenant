# Multi-Domain Support Implementation Guide

## Overview

This implementation adds comprehensive multi-domain support to the Laravel POS SaaS system, enabling:
- ✅ Multiple custom domains per tenant
- ✅ Per-domain database support
- ✅ Per-domain FTP configuration
- ✅ Per-domain file uploads with size limits
- ✅ Shared Laravel codebase
- ✅ Dynamic tenant switching

---

## Features

### 1. Multiple Domain Support

Each tenant can have multiple custom domains pointing to their instance.

**Database Migration**: `2026_05_13_000001_add_custom_domain_support.php`
- Adds `ftp_host`, `ftp_username`, `ftp_password`, `ftp_port`
- Adds `upload_path` and `max_upload_size` fields

### 2. Domain Model

```php
// Location: app/Models/Domain.php

// Get domain upload path
$domain->getUploadPath();

// Check FTP configuration
$domain->hasFtpConfig();

// Get FTP configuration
$domain->getFtpConfig();
```

### 3. Domain Management API

**Endpoints**:

#### List Domains
```
GET /api/domains
```
Returns all domains for the current tenant.

#### Create Domain
```
POST /api/domains
Content-Type: application/json

{
  "domain": "shop1.com",
  "ftp_host": "ftp.shop1.com",
  "ftp_username": "ftpuser",
  "ftp_password": "ftppass",
  "ftp_port": 21,
  "upload_path": "/public_html/uploads",
  "max_upload_size": 52428800
}
```

#### Get Domain Details
```
GET /api/domains/{domainId}
```

#### Update Domain
```
PATCH /api/domains/{domainId}
```

#### Delete Domain
```
DELETE /api/domains/{domainId}
```

#### Test FTP Connection
```
POST /api/domains/{domainId}/test-ftp
```

### 4. Per-Domain File Uploads

**Upload Single File**:
```
POST /api/uploads/domain/{domainId}
Content-Type: multipart/form-data

file: <file>
```

**Upload Multiple Files**:
```
POST /api/uploads/domain/{domainId}/multiple
Content-Type: multipart/form-data

files[]: <file1>
files[]: <file2>
```

**Get Upload Configuration**:
```
GET /api/uploads/domain/{domainId}/config
```

Returns:
```json
{
  "max_upload_size": 52428800,
  "max_upload_size_mb": 50,
  "upload_path": "uploads/shop1.com",
  "ftp_enabled": true
}
```

**Delete File**:
```
DELETE /api/uploads/domain/{domainId}/file
Content-Type: application/json

{
  "file_path": "uploads/shop1.com/document.pdf"
}
```

### 5. Dynamic Tenant Switching

**Get Available Tenants**:
```
GET /api/tenant/available
```

Returns all tenants accessible by the current user.

**Switch Tenant**:
```
POST /api/tenant/switch
Content-Type: application/json

{
  "tenant_id": "tenant-uuid"
}
```

**Get Current Tenant Context**:
```
GET /api/tenant/current
```

**Initialize Tenant Session**:
```
POST /api/tenant/initialize/{tenantId}
```

### 6. FTP Integration

The system supports automatic FTP synchronization for uploaded files:

- Files are stored locally first
- If FTP is configured, files are automatically synced to the FTP server
- Delete operations also remove files from FTP

**FTP Configuration Fields**:
- `ftp_host`: FTP server hostname
- `ftp_username`: FTP username
- `ftp_password`: FTP password (encrypted in database)
- `ftp_port`: FTP port (default: 21)
- `upload_path`: Remote FTP root directory

---

## Usage Examples

### JavaScript/Fetch

```javascript
// Switch tenant
async function switchTenant(tenantId) {
  const response = await fetch('/api/tenant/switch', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ tenant_id: tenantId })
  });
  return await response.json();
}

// Upload file
async function uploadFile(domainId, file) {
  const formData = new FormData();
  formData.append('file', file);

  const response = await fetch(`/api/uploads/domain/${domainId}`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: formData
  });
  return await response.json();
}

// Test FTP connection
async function testFtpConnection(domainId) {
  const response = await fetch(`/api/domains/${domainId}/test-ftp`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  });
  return await response.json();
}
```

### PHP/Laravel

```php
use App\Services\DomainService;
use App\Models\Domain;

$domainService = new DomainService();

// Get domain stats
$domain = Domain::find($domainId);
$stats = $domainService->getStorageStats($domain);

// Check upload capability
$canUpload = $domainService->canUpload($domain, $fileSize);

// Get remaining upload space
$remaining = $domainService->getUploadLimitRemaining($domain);

// List domain files
$files = $domainService->listDomainFiles($domain);

// Sync file to FTP
$synced = $domainService->syncFileToFtp($domain, 'uploads/shop1.com/file.pdf');
```

---

## Running Migrations

```bash
# Run all migrations
php artisan migrate

# Run specific migration
php artisan migrate --path=database/migrations/2026_05_13_000001_add_custom_domain_support.php

# Run migrations for tenants
php artisan tenants:migrate
```

---

## Configuration

### Environment Variables

Add to `.env`:

```
# Domain upload settings
DOMAIN_MAX_UPLOAD_SIZE=52428800    # 50MB in bytes
DOMAIN_UPLOAD_DISK=public
DOMAIN_ENABLE_FTP_SYNC=true
```

### Storage Configuration

Update `config/filesystems.php`:

```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'path' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    // ... other disks
]
```

---

## Security Considerations

1. **FTP Password Encryption**: FTP passwords are automatically encrypted/decrypted
2. **Upload Size Limits**: Each domain has configurable upload size limits
3. **File Authorization**: All upload/download operations check user permissions
4. **Tenant Isolation**: Users can only access domains/tenants they have permission for
5. **Session Management**: Tenant switching requires explicit authorization

---

## Error Handling

All endpoints return standardized JSON responses:

**Success**:
```json
{
  "success": true,
  "message": "Operation completed",
  "data": { }
}
```

**Error**:
```json
{
  "success": false,
  "message": "Error description"
}
```

---

## Troubleshooting

### FTP Connection Failed
- Check FTP credentials
- Verify FTP server is accessible
- Check firewall/port configuration
- Use `/api/domains/{domainId}/test-ftp` endpoint to debug

### File Upload Failures
- Verify upload size limit: `GET /api/uploads/domain/{domainId}/config`
- Check directory permissions
- Ensure sufficient disk space

### Tenant Switching Issues
- Verify user has access to tenant
- Check session configuration
- Ensure Stancl Tenancy is properly initialized

---

## Database Schema

### domains table

```
id (string, primary)
tenant_id (string, foreign)
domain (string, unique)
ftp_host (string, nullable)
ftp_username (string, nullable)
ftp_password (string, nullable, encrypted)
ftp_port (integer)
upload_path (string, nullable)
max_upload_size (bigInteger)
created_at (timestamp)
updated_at (timestamp)
```

---

## Files Created/Modified

### New Files
- `app/Models/Domain.php` - Domain model
- `app/Http/Controllers/DomainController.php` - Domain management
- `app/Http/Controllers/TenantSwitchController.php` - Tenant switching
- `app/Http/Controllers/UploadController.php` - File uploads
- `app/Http/Middleware/ResolveDomainMiddleware.php` - Domain resolution
- `app/Services/DomainService.php` - Domain utilities
- `database/migrations/2026_05_13_000001_add_custom_domain_support.php` - Database schema

### Modified Files
- `routes/web.php` - Added new API routes
- `app/Models/Tenant.php` - Added domain relationships

---

## Next Steps

1. Run migrations: `php artisan migrate`
2. Test API endpoints using provided examples
3. Configure FTP details for domains
4. Set up environment variables
5. Test file uploads and FTP sync
6. Implement UI for domain management

---

## Support

For issues or questions, refer to:
- Stancl Tenancy Documentation: https://tenancyforlaravel.com
- Laravel Documentation: https://laravel.com/docs
- FTP Configuration: Check your hosting provider's FTP setup guide
