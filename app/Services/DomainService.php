<?php

namespace App\Services;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Filesystem;

class DomainService
{
    /**
     * Get all domains for a tenant
     */
    public function getTenantDomains(Tenant $tenant)
    {
        return $tenant->domains()->get();
    }

    /**
     * Create a new domain for a tenant
     */
    public function createDomain(Tenant $tenant, array $data)
    {
        $data['tenant_id'] = $tenant->id;
        return Domain::create($data);
    }

    /**
     * Update domain configuration
     */
    public function updateDomain(Domain $domain, array $data)
    {
        return $domain->update($data);
    }

    /**
     * Delete a domain
     */
    public function deleteDomain(Domain $domain)
    {
        // Clean up uploaded files if needed
        $this->cleanupDomainFiles($domain);
        return $domain->delete();
    }

    /**
     * Get file storage path for domain
     */
    public function getStoragePath(Domain $domain): string
    {
        return $domain->getUploadPath();
    }

    /**
     * Get file storage URL for domain
     */
    public function getStorageUrl(Domain $domain, $filePath): string
    {
        return Storage::url($filePath);
    }

    /**
     * Initialize FTP connection
     */
    public function getFtpFilesystem(Domain $domain): ?Filesystem
    {
        if (!$domain->hasFtpConfig()) {
            return null;
        }

        try {
            $config = $domain->getFtpConfig();
            $adapter = new FtpAdapter([
                'host' => $config['host'],
                'username' => $config['username'],
                'password' => $config['password'],
                'port' => $config['port'] ?? 21,
                'root' => $config['root'] ?? '/',
            ]);

            return new Filesystem($adapter);
        } catch (\Exception $e) {
            \Log::error('FTP filesystem initialization failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Test FTP connection
     */
    public function testFtpConnection(Domain $domain): bool
    {
        if (!$domain->hasFtpConfig()) {
            return false;
        }

        try {
            $config = $domain->getFtpConfig();
            $conn = ftp_connect($config['host'], $config['port']);

            if (!$conn) {
                return false;
            }

            $login = ftp_login($conn, $config['username'], $config['password']);
            ftp_close($conn);

            return $login;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sync file to FTP
     */
    public function syncFileToFtp(Domain $domain, $filePath): bool
    {
        if (!$domain->hasFtpConfig()) {
            return false;
        }

        try {
            $fs = $this->getFtpFilesystem($domain);
            if (!$fs) {
                return false;
            }

            $localPath = storage_path('app/public/' . $filePath);
            if (!file_exists($localPath)) {
                return false;
            }

            $fs->write($filePath, file_get_contents($localPath));
            return true;

        } catch (\Exception $e) {
            \Log::error('FTP sync failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get domain uploads directory
     */
    public function getDomainUploadDirectory(Domain $domain): string
    {
        return storage_path('app/public/' . $domain->getUploadPath());
    }

    /**
     * Clean up files for a domain
     */
    public function cleanupDomainFiles(Domain $domain): void
    {
        try {
            $uploadPath = $domain->getUploadPath();
            Storage::disk('public')->deleteDirectory($uploadPath);
        } catch (\Exception $e) {
            \Log::error('Domain cleanup failed: ' . $e->getMessage());
        }
    }

    /**
     * Get domain storage statistics
     */
    public function getStorageStats(Domain $domain): array
    {
        $directory = $this->getDomainUploadDirectory($domain);
        $totalSize = 0;
        $fileCount = 0;

        if (is_dir($directory)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory)
            );

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                    $fileCount++;
                }
            }
        }

        return [
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / (1024 * 1024), 2),
            'file_count' => $fileCount,
            'max_size' => $domain->max_upload_size,
            'max_size_mb' => round($domain->max_upload_size / (1024 * 1024), 2),
            'usage_percentage' => round(($totalSize / $domain->max_upload_size) * 100, 2),
        ];
    }

    /**
     * List files in domain directory
     */
    public function listDomainFiles(Domain $domain, $path = null): array
    {
        $uploadPath = $domain->getUploadPath();
        $fullPath = $uploadPath . ($path ? '/' . $path : '');

        try {
            $files = Storage::disk('public')->files($fullPath);
            $directories = Storage::disk('public')->directories($fullPath);

            return [
                'files' => $files,
                'directories' => $directories,
            ];
        } catch (\Exception $e) {
            return [
                'files' => [],
                'directories' => [],
            ];
        }
    }

    /**
     * Get domain upload limit remaining
     */
    public function getUploadLimitRemaining(Domain $domain): int
    {
        $stats = $this->getStorageStats($domain);
        $remaining = $domain->max_upload_size - $stats['total_size'];
        return max(0, $remaining);
    }

    /**
     * Check if domain can upload file of given size
     */
    public function canUpload(Domain $domain, int $fileSize): bool
    {
        $remaining = $this->getUploadLimitRemaining($domain);
        return $fileSize <= $remaining;
    }
}
