<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Filesystem;

class UploadController extends Controller
{
    /**
     * Upload file to domain-specific storage
     */
    public function uploadFile(Request $request, $domainId)
    {
        $domain = Domain::findOrFail($domainId);

        // Authorize user
        $this->authorize('upload', $domain);

        $validated = $request->validate([
            'file' => 'required|file|max:' . ($domain->max_upload_size / 1024),
        ]);

        $file = $validated['file'];
        $uploadPath = $domain->getUploadPath();

        try {
            // Store file locally first
            $storedPath = $file->store($uploadPath, 'public');

            // If FTP is configured, sync to FTP server
            if ($domain->hasFtpConfig()) {
                $this->syncToFtp($domain, $storedPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'path' => $storedPath,
                'url' => Storage::url($storedPath),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple(Request $request, $domainId)
    {
        $domain = Domain::findOrFail($domainId);

        $this->authorize('upload', $domain);

        $validated = $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:' . ($domain->max_upload_size / 1024),
        ]);

        $uploadPath = $domain->getUploadPath();
        $uploadedFiles = [];

        try {
            foreach ($validated['files'] as $file) {
                $storedPath = $file->store($uploadPath, 'public');

                if ($domain->hasFtpConfig()) {
                    $this->syncToFtp($domain, $storedPath);
                }

                $uploadedFiles[] = [
                    'path' => $storedPath,
                    'url' => Storage::url($storedPath),
                    'name' => $file->getClientOriginalName(),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => count($uploadedFiles) . ' files uploaded successfully',
                'files' => $uploadedFiles,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch upload failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Sync file to FTP server
     */
    protected function syncToFtp(Domain $domain, $filePath)
    {
        $ftpConfig = $domain->getFtpConfig();

        try {
            $adapter = new FtpAdapter([
                'host' => $ftpConfig['host'],
                'username' => $ftpConfig['username'],
                'password' => $ftpConfig['password'],
                'port' => $ftpConfig['port'],
                'root' => $ftpConfig['root'],
            ]);

            $filesystem = new Filesystem($adapter);
            $localFile = storage_path('app/public/' . $filePath);
            $remoteFile = $filePath;

            $filesystem->write($remoteFile, file_get_contents($localFile));

        } catch (\Exception $e) {
            // Log FTP sync error but don't fail the upload
            \Log::error('FTP sync failed for domain ' . $domain->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Get upload configuration for domain
     */
    public function getUploadConfig($domainId)
    {
        $domain = Domain::findOrFail($domainId);

        $this->authorize('view', $domain);

        return response()->json([
            'success' => true,
            'max_upload_size' => $domain->max_upload_size,
            'max_upload_size_mb' => $domain->max_upload_size / (1024 * 1024),
            'upload_path' => $domain->getUploadPath(),
            'ftp_enabled' => $domain->hasFtpConfig(),
        ]);
    }

    /**
     * Delete uploaded file
     */
    public function deleteFile(Request $request, $domainId)
    {
        $domain = Domain::findOrFail($domainId);

        $this->authorize('upload', $domain);

        $validated = $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            Storage::disk('public')->delete($validated['file_path']);

            // Also delete from FTP if configured
            if ($domain->hasFtpConfig()) {
                $this->deleteFromFtp($domain, $validated['file_path']);
            }

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete file from FTP
     */
    protected function deleteFromFtp(Domain $domain, $filePath)
    {
        $ftpConfig = $domain->getFtpConfig();

        try {
            $adapter = new FtpAdapter([
                'host' => $ftpConfig['host'],
                'username' => $ftpConfig['username'],
                'password' => $ftpConfig['password'],
                'port' => $ftpConfig['port'],
                'root' => $ftpConfig['root'],
            ]);

            $filesystem = new Filesystem($adapter);
            $filesystem->delete($filePath);

        } catch (\Exception $e) {
            \Log::error('FTP delete failed for domain ' . $domain->id . ': ' . $e->getMessage());
        }
    }
}
