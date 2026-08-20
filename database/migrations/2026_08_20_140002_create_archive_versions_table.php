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
        Schema::create('archive_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('archive_id')->constrained('archives')->cascadeOnDelete();
            $table->integer('version_number');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path', 550);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->text('change_note')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['archive_id', 'version_number']);
            $table->index('archive_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_versions');
    }
};
