{{-- resources/views/admin/generate-image/index.blade.php --}}
@extends('layouts.app') {{-- Sesuaikan dengan nama file layout admin Anda, misal: layouts.admin --}}

@section('content')
<div class="container mx-auto px-4 py-8">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Generate Gambar Rute (AI)</h1>
    </div>

    {{-- Notifikasi Sukses/Error --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- KOLOM KIRI: PENGATURAN PROMPT --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 sticky top-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Pengaturan Prompt</h3>
                
                <form action="{{ route('admin.generate-image.save-prompt') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Prompt Gemini Nano Banana 2:</label>
                        <textarea 
                            name="prompt" 
                            id="master_prompt"
                            rows="6" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Tulis prompt di sini..."
                        >{{ old('prompt', $savedPrompt) }}</textarea>
                        <p class="text-xs text-gray-500 mt-2">
                            Gunakan variabel <strong>[kota_asal]</strong> dan <strong>[kota_tujuan]</strong> agar sistem bisa menggantinya secara otomatis.
                        </p>
                    </div>
                    <button type="submit" class="w-full bg-gray-800 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-900 transition">
                        💾 Simpan Prompt Permanen
                    </button>
                </form>
            </div>
        </div>

        {{-- KOLOM KANAN: TABEL DATA RUTE --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Daftar Rute Ekspedisi</h3>
                
                <form action="{{ route('admin.generate-image.generate-bulk') }}" method="POST" id="form-generate-bulk">
                    @csrf
                    {{-- Hidden input ini akan mengambil isi text-area prompt secara otomatis via Javascript --}}
                    <input type="hidden" name="prompt" id="hidden_prompt_to_submit">
                    
                    <div class="flex justify-between items-center mb-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="flex items-center text-sm text-gray-700 font-medium cursor-pointer">
                            <input type="checkbox" name="force_regenerate" value="1" class="mr-2 h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                            Tumpuk / Regenerate (Pilih ini jika ingin mengganti gambar rute yang sudah ada)
                        </label>
                        <button type="button" onclick="submitBulkGenerate()" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition shadow-md">
                            🚀 Generate Gambar Terpilih
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700 text-sm">
                                    <th class="p-3 border-b border-gray-200">
                                        <input type="checkbox" id="check-all" class="h-4 w-4 rounded border-gray-300">
                                    </th>
                                    <th class="p-3 border-b border-gray-200 font-bold">Rute (Asal - Tujuan)</th>
                                    <th class="p-3 border-b border-gray-200 font-bold">Status Gambar</th>
                                    <th class="p-3 border-b border-gray-200 font-bold">Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rutes as $rute)
                                    <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                                        <td class="p-3">
                                            <input type="checkbox" name="rute_ids[]" value="{{ $rute->id }}" class="rute-checkbox h-4 w-4 rounded border-gray-300 text-blue-600">
                                        </td>
                                        <td class="p-3 text-gray-800 font-medium">
                                            {{ $rute->kota_asal }} &rarr; {{ $rute->kota_tujuan }}
                                        </td>
                                        <td class="p-3">
                                            @if($rute->image_path)
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">Selesai</span>
                                            @else
                                                <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">Belum Ada</span>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            @if($rute->image_path)
                                                <img src="{{ asset($rute->image_path) }}" alt="Preview" class="h-10 w-24 object-cover rounded shadow-sm border border-gray-200">
                                            @else
                                                <div class="h-10 w-24 bg-gray-100 flex items-center justify-center text-xs text-gray-400 rounded border border-gray-200 border-dashed">Kosong</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-6 text-center text-gray-500">Belum ada data rute.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

{{-- SCRIPT SEDERHANA UNTUK MENGATUR CHECKBOX & FORM --}}
<script>
    // Fitur Check/Uncheck Semua Rute
    document.getElementById('check-all').addEventListener('change', function(e) {
        let checkboxes = document.querySelectorAll('.rute-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
    });

    // Fungsi submit form generate massal
    function submitBulkGenerate() {
        let selected = document.querySelectorAll('.rute-checkbox:checked');
        if (selected.length === 0) {
            alert('Silakan pilih minimal 1 rute untuk di-generate gambarnya!');
            return;
        }

        // Salin isi text-area prompt ke hidden input sebelum submit
        let promptText = document.getElementById('master_prompt').value;
        document.getElementById('hidden_prompt_to_submit').value = promptText;

        if (confirm(`Anda akan mengirim request generate gambar untuk ${selected.length} rute ke background job. Lanjutkan?`)) {
            document.getElementById('form-generate-bulk').submit();
        }
    }
</script>
@endsection