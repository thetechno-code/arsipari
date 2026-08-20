@extends('layouts.app')

@section('title', 'Tambah Arsip Digital')
@section('page-title', 'Tambah Arsip Baru')

@section('breadcrumb')
    <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600">Dashboard</a>
    <span class="text-gray-400">/</span>
    <a href="{{ route('archives.index') }}" class="text-gray-400 hover:text-gray-600">Arsip</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">Tambah</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    documentDate: '{{ old('document_date', date('Y-m-d')) }}',
    year: '{{ old('year', date('Y')) }}',
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
            <h3 class="text-lg font-bold text-gray-900">Tambah Arsip Digital Baru</h3>
            <p class="text-xs text-gray-500 mt-0.5">Unggah berkas dokumen yang telah selesai diproses beserta metadatanya</p>
        </div>
        <a href="{{ route('archives.index') }}" class="btn-secondary btn-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <form action="{{ route('archives.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

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
                    <input id="title" type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: SK Kepala Madrasah tentang Pembagian Tugas Guru" class="input @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nomor Dokumen --}}
                <div>
                    <label for="document_number" class="label">Nomor Dokumen <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input id="document_number" type="text" name="document_number" value="{{ old('document_number') }}" placeholder="Contoh: 800/123/MTsN/2026" class="input @error('document_number') border-red-500 @enderror">
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
                    <p class="text-[11px] text-gray-400 mt-0.5">Otomatis terisi dari tahun tanggal dokumen</p>
                    @error('year')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Catatan Nomor Arsip Otomatis --}}
                <div class="flex items-center p-3 rounded-lg bg-blue-50 border border-blue-100 text-blue-800 text-xs sm:col-span-2">
                    <svg class="w-4 h-4 flex-shrink-0 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><strong>Nomor Arsip</strong> akan digenerate otomatis oleh sistem (Format: ARSIP-2026-XXXXXX) setelah berhasil disimpan.</span>
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
                                <option value="{{ $parentCat->id }}" {{ old('category_id') == $parentCat->id ? 'selected' : '' }}>
                                    {{ $parentCat->name }} (Induk)
                                </option>
                                @foreach($parentCat->children as $childCat)
                                    <option value="{{ $childCat->id }}" {{ old('category_id') == $childCat->id ? 'selected' : '' }}>
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
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
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
                            <option value="{{ strtolower($dt->code) }}" {{ old('document_type') == strtolower($dt->code) ? 'selected' : '' }}>
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
                            <option value="{{ $rp->id }}" {{ old('retention_policy_id') == $rp->id ? 'selected' : '' }}>
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
                    <label for="keywords" class="label">Kata Kunci (Keywords) <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input id="keywords" type="text" name="keywords" value="{{ old('keywords') }}" placeholder="Contoh: SK, kepala madrasah, pembagian tugas, 2026 (dipisahkan koma)" class="input @error('keywords') border-red-500 @enderror">
                    <p class="text-[11px] text-gray-400 mt-0.5">Memudahkan pencarian cepat arsip berdasarkan kata kunci</p>
                    @error('keywords')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="label">Deskripsi / Uraian Isi <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <textarea id="description" name="description" rows="3" placeholder="Uraian singkat mengenai isi dokumen..." class="input @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ─── SECTION 4: Unggah Berkas Dokumen ─── --}}
        <div class="card p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700 text-xs flex items-center justify-center font-bold">4</span>
                    Unggah Berkas Dokumen Versi 1 <span class="text-red-500">*</span>
                </h4>
            </div>

            {{-- Drag & Drop Upload Dropzone --}}
            <div class="relative border-2 border-dashed rounded-xl p-6 text-center transition-colors duration-150"
                 :class="isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-gray-400 bg-gray-50/50'"
                 @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="isDragging = false; handleFileSelect($event); $refs.fileInput.files = $event.dataTransfer.files">

                <input x-ref="fileInput" id="file" type="file" name="file" required class="sr-only" @change="handleFileSelect($event)">

                <div class="space-y-2" x-show="!fileName">
                    <div class="w-12 h-12 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <div>
                        <label for="file" class="cursor-pointer text-sm font-bold text-primary-600 hover:text-primary-700 hover:underline">
                            Pilih berkas dari komputer
                        </label>
                        <span class="text-xs text-gray-500"> atau seret dan lepas file di sini</span>
                    </div>
                    <p class="text-[11px] text-gray-400">
                        Maksimal ukuran: <strong>{{ env('ARSIPARI_MAX_FILE_SIZE_MB', 20) }} MB</strong>. Format: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, ZIP.
                    </p>
                </div>

                <div class="flex items-center justify-center gap-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm" x-show="fileName" style="display:none;">
                    <div class="w-9 h-9 rounded-lg bg-green-100 text-green-700 flex items-center justify-center flex-shrink-0 font-bold text-xs uppercase">
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

            @error('file')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- ─── Action Buttons ─── --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('archives.index') }}" class="btn-secondary">Batal</a>
            <button type="submit" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Simpan Arsip
            </button>
        </div>

    </form>
</div>
@endsection
