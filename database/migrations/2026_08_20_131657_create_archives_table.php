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
        Schema::create('archives', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('archive_number')->unique();
            $table->string('document_number')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->restrictOnDelete();
            $table->integer('year');
            $table->date('document_date')->nullable();
            $table->string('document_type')->nullable();
            $table->text('keywords')->nullable();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for search and reporting performance
            $table->index('archive_number');
            $table->index('category_id');
            $table->index('department_id');
            $table->index('year');
            $table->index('document_date');
            $table->index('document_type');
            $table->index('uploaded_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
