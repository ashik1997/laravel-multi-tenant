<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->after('collation');
            $table->string('pm_type')->nullable()->after('stripe_id');
            $table->string('pm_last_four')->nullable()->after('pm_type');
            $table->string('card_brand')->nullable()->after('pm_last_four');
            $table->string('card_last_four')->nullable()->after('card_brand');
            $table->timestamp('trial_ends_at')->nullable()->after('card_last_four');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_id',
                'pm_type',
                'pm_last_four',
                'card_brand',
                'card_last_four',
                'trial_ends_at',
            ]);
        });
    }
};
