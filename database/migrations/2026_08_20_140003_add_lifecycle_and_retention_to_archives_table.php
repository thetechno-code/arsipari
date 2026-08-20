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
        Schema::table('archives', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->index()->after('description');
            $table->foreignId('retention_policy_id')->nullable()->after('status')->constrained('retention_policies')->nullOnDelete();
            $table->date('retention_until')->nullable()->index()->after('retention_policy_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            $table->dropForeign(['retention_policy_id']);
            $table->dropColumn(['status', 'retention_policy_id', 'retention_until']);
        });
    }
};
