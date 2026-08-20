@extends('layouts.app')

@section('title', 'Edit Arsip — ' . $archive->archive_number)
@section('page-title', 'Edit Metadata Arsip')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('archives.index') }}" class="text-gray-400 hover:text-gray-600">Arsip</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('archives.show', $archive) }}" class="text-gray-400 hover:text-gray-600">{{ $archive->archive_number }}</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Edit</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    documentDate: '{{ old('document_date', $archive->document_date ? $archive->document_date->format('Y-m-d') : '') }}',
    year: '{{ old('year', $archive->year) }}',
    fileName: '',
    fileSize: '',
    isDragging: false,
    updateYear() {
        if (this.documentDate) {
            const d = new Date(this.documentDate);
            if (!isNaN(d.getFullYear())) {
                this.year = d.getFullYear();
            }
        }
    },
    handleFileSelect(e) {
        const file = e.target.files[0] || (e.dataTransfer && e.dataTransfer.files[0]);
        if (file) {
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        }
    }
}">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <code class="text-xs px-2.5 py-0.5 rounded font-mono font-bold bg-primary-50 text-primary-800 border border-primary-200">
                    {{ $archive->archive_number }}
                </code>
                <span class="text-xs text-gray-500 font-medium">(Nomor Arsip bersifat tetap)</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mt-1">Edit Metadata & Unggah Versi Baru</h3>
        </div>
        <a href="{{ route('archives.show', $archive) }}" class="btn-secondary btn-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Batal
        </a>
    </div>

    <form action="{{ route('archives.update', $archive) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ─── SECTION 1: Informasi Dokumen ─── --}}
        <div class="card p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 text-xs flex items-center justify-center font-bold">1</span>
                    Informasi Utama Dokumen
                </h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Judul Arsip --}}
                <div class="sm:col-span-2">
                    <label for="title" class="label">Judul Arsip <span class="text-red-500">*</span></label>
                    <input id="title" type="text" name="title" value="{{ old('title', $archive->title) }}" required class="input @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nomor Dokumen --}}
                <div>
                    <label for="document_number" class="label">Nomor Dokumen <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input id="document_number" type="text" name="document_number" value="{{ old('document_number', $archive->document_number) }}" class="input @error('document_number') border-red-500 @enderror">
                    @error('document_number')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Dokumen --}}
                <div>
                    <label for="document_date" class="label">Tanggal Dokumen <span class="text-red-500">*</span></label>
                    <input id="document_date" type="date" name="document_date" x-model="documentDate" @change="updateYear()" required class="input @error('document_date') border-red-500 @enderror">
                    @error('document_date')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tahun --}}
                <div>
                    <label for="year" class="label">Tahun Arsip <span class="text-red-500">*</span></label>
                    <input id="year" type="number" name="year" x-model="year" min="1900" max="2100" required class="input @error('year') border-red-500 @enderror">
                    @error('year')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ─── SECTION 2: Klasifikasi & Pengelompokan ─── --}}
        <div class="card p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 text-xs flex items-center justify-center font-bold">2</span>
                    Klasifikasi & Kebijakan Retensi
                </h4>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Kategori --}}
                <div>
                    <label for="category_id" class="label">Kategori Arsip <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required class="input @error('category_id') border-red-500 @enderror">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $parentCat)
                            <optgroup label="{{ $parentCat->name }} ({{ $parentCat->code }})">
                                <option value="{{ $parentCat->id }}" {{ old('category_id', $archive->category_id) == $parentCat->id ? 'selected' : '' }}>
                                    {{ $parentCat->name }} (Induk)
                                </option>
                                @foreach($parentCat->children as $childCat)
                                    <option value="{{ $childCat->id }}" {{ old('category_id', $archive->category_id) == $childCat->id ? 'selected' : '' }}>
                                        └─ {{ $childCat->name }} ({{ $childCat->code }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Unit / Bidang --}}
                <div>
                    <label for="department_id" class="label">Unit / Bidang Kerja <span class="text-red-500">*</span></label>
                    <select id="department_id" name="department_id" required class="input @error('department_id') border-red-500 @enderror">
                        <option value="">-- Pilih Unit / Bidang --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $archive->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jenis Dokumen --}}
                <div>
                    <label for="document_type" class="label">Jenis Dokumen <span class="text-red-500">*</span></label>
                    <select id="document_type" name="document_type" required class="input @error('document_type') border-red-500 @enderror">
                        <option value="">-- Pilih Jenis Dokumen --</option>
                        @foreach($documentTypes as $dt)
                            <option value="{{ strtolower($dt->code) }}" {{ old('document_type', strtolower($archive->document_type)) == strtolower($dt->code) ? 'selected' : '' }}>
                                {{ $dt->name }} ({{ $dt->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('document_type')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kebijakan Retensi --}}
                <div>
                    <label for="retention_policy_id" class="label">Kebijakan Retensi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <select id="retention_policy_id" name="retention_policy_id" class="input @error('retention_policy_id') border-red-500 @enderror">
                        <option value="">-- Pilih Retensi (Default: Permanen) --</option>
                        @foreach($retentionPolicies as $rp)
                            <option value="{{ $rp->id }}" {{ old('retention_policy_id', $archive->retention_policy_id) == $rp->id ? 'selected' : '' }}>
                                {{ $rp->name }} {{ $rp->is_permanent ? '(Permanen)' : "({$rp->duration_years} Tahun)" }}
                            </option>
                        @endforeach
                    </select>
                    @error('retention_policy_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ─── SECTION 3: Informasi Tambahan ─── --}}
        <div class="card p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 text-xs flex items-center justify-center font-bold">3</span>
                    Informasi Tambahan & Pencarian
                </h4>
            </div>

            <div class="space-y-4">
                {{-- Kata Kunci --}}
                <div>
                    <label for="keywords" class="label">Kata Kunci (Keywords)</label>
                    <input id="keywords" type="text" name="keywords" value="{{ old('keywords', $archive->keywords) }}" class="input @error('keywords') border-red-500 @enderror">
                    @error('keywords')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="label">Deskripsi / Uraian Isi</label>
                    <textarea id="description" name="description" rows="3" class="input @error('description') border-red-500 @enderror">{{ old('description', $archive->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ─── SECTION 4: Unggah Berkas Dokumen Versi Baru (Opsional) ─── --}}
        <div class="card p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 text-xs flex items-center justify-center font-bold">4</span>
                    Unggah Berkas Dokumen Versi Baru <span class="text-gray-400 font-normal">(Opsional)</span>
                </h4>
            </div>

            {{-- Existing File Notice --}}
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <span>Berkas saat ini: <strong>{{ $archive->original_filename }}</strong> ({{ $archive->file_size_formatted }}).</span>
                    <span class="block text-[11px] text-amber-700 mt-0.5">Mengunggah berkas baru akan secara otomatis membuat <strong>versi baru</strong> tanpa menghapus riwayat versi sebelumnya.</span>
                </div>
            </div>

            {{-- Dropzone --}}
            <div class="relative border-2 border-dashed rounded-xl p-6 text-center transition-colors duration-150"
                 :class="isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50/50'"
                 @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="isDragging = false; handleFileSelect($event); $refs.fileInput.files = $event.dataTransfer.files">

                <input x-ref="fileInput" id="file" type="file" name="file" class="sr-only" @change="handleFileSelect($event)">

                <div class="space-y-2" x-show="!fileName">
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <div>
                        <label for="file" class="cursor-pointer text-sm font-bold text-amber-700 hover:text-amber-800 hover:underline">
                            Pilih berkas baru dari komputer
                        </label>
                        <span class="text-xs text-gray-500"> atau seret dan lepas file di sini</span>
                    </div>
                    <p class="text-[11px] text-gray-400">
                        Maksimal ukuran: <strong>{{ env('ARSIPARI_MAX_FILE_SIZE_MB', 20) }} MB</strong>. Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP.
                    </p>
                </div>

                <div class="flex items-center justify-center gap-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm" x-show="fileName" style="display:none;">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 font-bold text-xs uppercase">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="text-left min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-900 truncate" x-text="fileName"></p>
                        <p class="text-xs text-gray-500" x-text="fileSize"></p>
                    </div>
                    <button type="button" @click="fileName = ''; fileSize = ''; $refs.fileInput.value = ''" class="text-gray-400 hover:text-red-600 p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div x-show="fileName">
                <label for="change_note" class="label">Catatan Perubahan Versi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <textarea id="change_note" name="change_note" rows="2" placeholder="Catatan perubahan atau revisi berkas baru..." class="input"></textarea>
            </div>

            @error('file')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- ─── Action Buttons ─── --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('archives.show', $archive) }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Perubahan
            </button>
        </div>

    </form>
</div>
@endsection
