<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Archive;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN, 'is_active' => true]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR, 'is_active' => true]);
    }

    public function test_admin_can_view_document_types(): void
    {
        DocumentType::create(['name' => 'PDF', 'code' => 'PDF']);

        $response = $this->actingAs($this->admin)->get('/document-types');

        $response->assertStatus(200);
        $response->assertSee('PDF');
    }

    public function test_admin_can_create_document_type(): void
    {
        $response = $this->actingAs($this->admin)->post('/document-types', [
            'name'        => 'Microsoft Word',
            'code'        => 'docx',
            'description' => 'Format file docx',
            'is_active'   => '1',
        ]);

        $response->assertRedirect('/document-types');
        $response->assertSessionHas('success', 'Jenis dokumen berhasil dibuat.');

        $this->assertDatabaseHas('document_types', [
            'name' => 'Microsoft Word',
            'code' => 'DOCX',
        ]);
    }

    public function test_document_type_code_must_be_unique(): void
    {
        DocumentType::create(['name' => 'PDF', 'code' => 'PDF']);

        $response = $this->actingAs($this->admin)->post('/document-types', [
            'name' => 'PDF Duplicate',
            'code' => 'PDF',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_document_type_deletion_rejected_if_used_by_archives(): void
    {
        $dt = DocumentType::create(['name' => 'PDF', 'code' => 'PDF']);

        Archive::create([
            'archive_number'    => 'ARSIP-003',
            'title'             => 'Test Doc Type',
            'document_type'     => 'pdf',
            'year'              => 2026,
            'original_filename' => 'doc.pdf',
            'stored_filename'   => 'doc.pdf',
            'file_path'         => 'archives/doc.pdf',
            'mime_type'         => 'application/pdf',
            'file_size'         => 100,
            'uploaded_by'       => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->delete("/document-types/{$dt->id}");

        $response->assertSessionHas('error', 'Jenis dokumen tidak dapat dihapus karena masih digunakan oleh arsip.');
        $this->assertDatabaseHas('document_types', ['id' => $dt->id]);
    }

    public function test_operator_cannot_mutate_document_types(): void
    {
        $dt = DocumentType::create(['name' => 'PDF', 'code' => 'PDF']);

        $response = $this->actingAs($this->operator)->post('/document-types', ['name' => 'X', 'code' => 'X']);
        $response->assertStatus(403);

        $response = $this->actingAs($this->operator)->delete("/document-types/{$dt->id}");
        $response->assertStatus(403);
    }
}
