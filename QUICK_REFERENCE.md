# Multi-Domain Features - Quick Reference

## 🎯 Key Endpoints

### Domain Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/domains` | List all domains for tenant |
| POST | `/api/domains` | Create new domain |
| GET | `/api/domains/{id}` | Get domain details |
| PATCH | `/api/domains/{id}` | Update domain settings |
| DELETE | `/api/domains/{id}` | Delete domain |
| POST | `/api/domains/{id}/test-ftp` | Test FTP connection |

### Tenant Switching
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/tenant/available` | Get accessible tenants |
| POST | `/api/tenant/switch` | Switch to tenant |
| GET | `/api/tenant/current` | Get current context |
| POST | `/api/tenant/initialize/{id}` | Initialize tenant |

### File Uploads
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/uploads/domain/{id}` | Upload single file |
| POST | `/api/uploads/domain/{id}/multiple` | Upload multiple files |
| GET | `/api/uploads/domain/{id}/config` | Get upload config |
| DELETE | `/api/uploads/domain/{id}/file` | Delete file |

---

## 💻 Code Snippets

### Create Domain
```php
use App\Models\Domain;

$domain = Domain::create([
    'tenant_id' => $tenant->id,
    'domain' => 'shop.example.com',
    'ftp_host' => 'ftp.example.com',
    'ftp_username' => 'user',
    'ftp_password' => 'pass',
    'ftp_port' => 21,
    'upload_path' => '/public_html/uploads',
    'max_upload_size' => 52428800,
]);
```

### Upload File
```php
use App\Http\Controllers\UploadController;

// Via API
POST /api/uploads/domain/{domainId}
Content-Type: multipart/form-data
file: <file>

// Via PHP
$controller = new UploadController();
$controller->uploadFile($request, $domainId);
```

### Switch Tenant
```php
use App\Models\Tenant;
use Illuminate\Support\Facades\Session;

$tenant = Tenant::find($tenantId);
Session::put(['tenant_id' => $tenant->id]);
tenancy()->initialize($tenant);
```

### Get Domain Stats
```php
use App\Services\DomainService;

$service = new DomainService();
$stats = $service->getStorageStats($domain);

// Returns:
[
    'total_size' => 10485760,      // bytes
    'total_size_mb' => 10,         // MB
    'file_count' => 5,
    'max_size' => 52428800,
    'max_size_mb' => 50,
    'usage_percentage' => 20
]
```

---

## 🔐 Security

### FTP Password Encryption
```php
// Automatically encrypted on create/update
$domain->ftp_password = 'plaintext';
$domain->save(); // Password is encrypted

// Automatically decrypted when accessed
$config = $domain->getFtpConfig();
echo $config['password']; // Decrypted
```

### Authorization
```php
// All controllers check authorization
$this->authorize('upload', $domain);
$this->authorize('delete', $domain);
```

---

## 📦 Installation

```bash
# 1. Run migration
php artisan migrate

# 2. Register provider (if needed)
# Add to config/app.php: App\Providers\DomainServiceProvider::class

# 3. Add middleware (optional)
# Add to bootstrap/app.php middleware

# 4. Test
php artisan test tests/Feature/DomainManagementTest.php
```

---

## 🐛 Debugging

### Test FTP Connection
```php
$domain = Domain::find($id);
$service = new DomainService();
$connected = $service->testFtpConnection($domain);
```

### Check Upload Config
```bash
GET /api/uploads/domain/{id}/config
```

### View Storage Stats
```php
$stats = $service->getStorageStats($domain);
dd($stats);
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

---

## 📝 Common Tasks

### Add Custom Domain to Tenant
```php
$tenant->domains()->create([
    'domain' => 'newshop.com',
    'max_upload_size' => 52428800,
]);
```

### Update FTP Settings
```php
$domain->update([
    'ftp_host' => 'new-ftp.example.com',
    'ftp_username' => 'newuser',
    'ftp_password' => 'newpass',
]);
```

### List User's Accessible Tenants
```php
$tenants = Tenant::whereHas('users', function($q) {
    $q->where('user_id', Auth::id());
})->get();
```

### Get Domain from Hostname
```php
$domain = Domain::where('domain', request()->getHost())->first();
$tenant = $domain?->tenant;
```

---

## 🧪 Testing

```bash
# Run all domain tests
php artisan test tests/Feature/DomainManagementTest.php

# Run specific test
php artisan test tests/Feature/DomainManagementTest.php --filter=test_can_create_domain

# With coverage
php artisan test --coverage
```

---

## 📚 Files to Review

| File | Purpose |
|------|---------|
| `app/Models/Domain.php` | Domain model |
| `app/Services/DomainService.php` | Utility service |
| `app/Http/Controllers/DomainController.php` | Domain CRUD |
| `app/Http/Controllers/UploadController.php` | File uploads |
| `app/Http/Controllers/TenantSwitchController.php` | Tenant switching |
| `SETUP_INSTRUCTIONS.md` | Setup guide |
| `MULTI_DOMAIN_IMPLEMENTATION.md` | Full documentation |

---

## ⚙️ Configuration

### Environment Variables
```
DOMAIN_MAX_UPLOAD_SIZE=52428800
DOMAIN_UPLOAD_DISK=public
DOMAIN_ENABLE_FTP_SYNC=true
```

### Storage Disk
```php
// config/filesystems.php
'public' => [
    'driver' => 'local',
    'path' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
]
```

---

## 🚨 Error Messages

| Error | Solution |
|-------|----------|
| FTP connection failed | Check credentials and FTP server accessibility |
| File too large | Check upload limit: `GET /api/uploads/domain/{id}/config` |
| Unauthorized | User doesn't have access to domain/tenant |
| Domain not found | Verify domain ID exists for current tenant |
| Storage full | Check storage usage and increase limit |

---

## 🔗 Related Links

- Stancl Tenancy: https://tenancyforlaravel.com
- Laravel Storage: https://laravel.com/docs/storage
- FTP Functions: https://www.php.net/manual/en/book.ftp.php

---

**Last Updated**: May 13, 2026
**Status**: ✅ Complete and Production Ready
