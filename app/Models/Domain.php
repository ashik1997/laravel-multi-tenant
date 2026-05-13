<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'tenant_id',
        'domain',
        'ftp_host',
        'ftp_username',
        'ftp_password',
        'ftp_port',
        'upload_path',
        'max_upload_size',
    ];

    protected $hidden = [
        'ftp_password',
    ];

    protected $casts = [
        'max_upload_size' => 'integer',
        'ftp_port' => 'integer',
    ];

    /**
     * Get the tenant that owns the domain.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the upload path for this domain.
     */
    public function getUploadPath()
    {
        return $this->upload_path ?? "uploads/{$this->domain}";
    }

    /**
     * Check if FTP is configured
     */
    public function hasFtpConfig(): bool
    {
        return !empty($this->ftp_host) && !empty($this->ftp_username);
    }

    /**
     * Get FTP configuration array
     */
    public function getFtpConfig(): array
    {
        return [
            'host' => $this->ftp_host,
            'username' => $this->ftp_username,
            'password' => decrypt($this->ftp_password),
            'port' => $this->ftp_port ?? 21,
            'root' => $this->upload_path ?? "/public_html",
        ];
    }

    /**
     * Store encrypted FTP password
     */
    public function setFtpPasswordAttribute($value)
    {
        $this->attributes['ftp_password'] = encrypt($value);
    }
}
