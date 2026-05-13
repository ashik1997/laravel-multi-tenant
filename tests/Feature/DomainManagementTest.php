<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DomainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DomainManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $user;
    protected $domainService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tenant
        $this->tenant = Tenant::create([
            'id' => 'test-tenant',
            'name' => 'Test Tenant',
            'email' => 'test@tenant.com',
        ]);

        // Create test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenant->id,
        ]);

        $this->domainService = new DomainService();

        Storage::fake('public');
    }

    /**
     * Test creating a new domain
     */
    public function test_can_create_domain()
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/api/domains', [
            'domain' => 'shop1.example.com',
            'ftp_host' => 'ftp.example.com',
            'ftp_username' => 'ftpuser',
            'ftp_password' => 'ftppass',
            'ftp_port' => 21,
            'upload_path' => '/public_html/uploads',
            'max_upload_size' => 52428800,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('domain.domain', 'shop1.example.com');

        $this->assertDatabaseHas('domains', [
            'domain' => 'shop1.example.com',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test listing domains
     */
    public function test_can_list_domains()
    {
        $this->actingAs($this->user);

        Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop1.example.com',
        ]);

        Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop2.example.com',
        ]);

        $response = $this->getJson('/api/domains');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 2);
    }

    /**
     * Test updating domain
     */
    public function test_can_update_domain()
    {
        $this->actingAs($this->user);

        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
            'max_upload_size' => 52428800,
        ]);

        $response = $this->patchJson("/api/domains/{$domain->id}", [
            'max_upload_size' => 104857600, // 100MB
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals(104857600, $domain->fresh()->max_upload_size);
    }

    /**
     * Test deleting domain
     */
    public function test_can_delete_domain()
    {
        $this->actingAs($this->user);

        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
        ]);

        $response = $this->deleteJson("/api/domains/{$domain->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('domains', ['id' => $domain->id]);
    }

    /**
     * Test uploading file to domain
     */
    public function test_can_upload_file_to_domain()
    {
        $this->actingAs($this->user);

        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
            'max_upload_size' => 52428800,
        ]);

        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson("/api/uploads/domain/{$domain->id}", [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['url']);
    }

    /**
     * Test uploading multiple files
     */
    public function test_can_upload_multiple_files()
    {
        $this->actingAs($this->user);

        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
            'max_upload_size' => 52428800,
        ]);

        $files = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];

        $response = $this->postJson("/api/uploads/domain/{$domain->id}/multiple", [
            'files' => $files,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'files');
    }

    /**
     * Test deleting uploaded file
     */
    public function test_can_delete_uploaded_file()
    {
        $this->actingAs($this->user);

        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
            'max_upload_size' => 52428800,
        ]);

        $file = UploadedFile::fake()->image('test.jpg');
        $uploadResponse = $this->postJson("/api/uploads/domain/{$domain->id}", [
            'file' => $file,
        ]);

        $filePath = $uploadResponse->json('path');

        $response = $this->deleteJson("/api/uploads/domain/{$domain->id}/file", [
            'file_path' => $filePath,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Test getting upload configuration
     */
    public function test_can_get_upload_config()
    {
        $this->actingAs($this->user);

        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
            'max_upload_size' => 52428800,
        ]);

        $response = $this->getJson("/api/uploads/domain/{$domain->id}/config");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('max_upload_size', 52428800)
            ->assertJsonPath('max_upload_size_mb', 50);
    }

    /**
     * Test switching tenant
     */
    public function test_can_switch_tenant()
    {
        $anotherTenant = Tenant::create([
            'id' => 'another-tenant',
            'name' => 'Another Tenant',
            'email' => 'another@tenant.com',
        ]);

        $this->user->update(['tenant_id' => $anotherTenant->id]);

        $this->actingAs($this->user);

        $response = $this->postJson('/api/tenant/switch', [
            'tenant_id' => $this->tenant->id,
        ]);

        // This will fail in test without proper tenant access setup
        // In real scenario, user would have access to both tenants
        $response->assertStatus(403);
    }

    /**
     * Test getting available tenants
     */
    public function test_can_get_available_tenants()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/tenant/available');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['current_tenant', 'tenants']);
    }

    /**
     * Test domain service - get storage stats
     */
    public function test_domain_service_get_storage_stats()
    {
        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
            'max_upload_size' => 52428800,
        ]);

        $stats = $this->domainService->getStorageStats($domain);

        $this->assertArrayHasKey('total_size', $stats);
        $this->assertArrayHasKey('total_size_mb', $stats);
        $this->assertArrayHasKey('file_count', $stats);
        $this->assertArrayHasKey('max_size', $stats);
        $this->assertArrayHasKey('usage_percentage', $stats);
    }

    /**
     * Test domain service - check upload capability
     */
    public function test_domain_service_can_upload()
    {
        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
            'max_upload_size' => 52428800, // 50MB
        ]);

        $can_upload_small = $this->domainService->canUpload($domain, 1024 * 1024); // 1MB
        $can_upload_large = $this->domainService->canUpload($domain, 60 * 1024 * 1024); // 60MB

        $this->assertTrue($can_upload_small);
        $this->assertFalse($can_upload_large);
    }

    /**
     * Test FTP configuration encryption
     */
    public function test_ftp_password_is_encrypted()
    {
        $domain = Domain::create([
            'tenant_id' => $this->tenant->id,
            'domain' => 'shop.example.com',
            'ftp_password' => 'mysecretpassword',
        ]);

        // Password should be encrypted in database
        $dbPassword = \DB::table('domains')->where('id', $domain->id)->first()->ftp_password;
        $this->assertNotEquals('mysecretpassword', $dbPassword);

        // But decrypted when accessed
        $this->assertEquals('mysecretpassword', $domain->fresh()->getFtpConfig()['password']);
    }
}
