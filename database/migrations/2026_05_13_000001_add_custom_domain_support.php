<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            // Add FTP configuration columns
            if (!Schema::hasColumn('domains', 'ftp_host')) {
                $table->string('ftp_host')->nullable()->after('domain');
            }
            if (!Schema::hasColumn('domains', 'ftp_username')) {
                $table->string('ftp_username')->nullable()->after('ftp_host');
            }
            if (!Schema::hasColumn('domains', 'ftp_password')) {
                $table->string('ftp_password')->nullable()->after('ftp_username');
            }
            if (!Schema::hasColumn('domains', 'ftp_port')) {
                $table->integer('ftp_port')->default(21)->after('ftp_password');
            }
            if (!Schema::hasColumn('domains', 'upload_path')) {
                $table->string('upload_path')->nullable()->after('ftp_port');
            }
            if (!Schema::hasColumn('domains', 'max_upload_size')) {
                $table->bigInteger('max_upload_size')->default(52428800)->after('upload_path'); // 50MB default
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn([
                'ftp_host',
                'ftp_username',
                'ftp_password',
                'ftp_port',
                'upload_path',
                'max_upload_size'
            ]);
        });
    }
};
