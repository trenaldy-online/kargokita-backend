<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- KOLOM KIRI: PENGATURAN PROMPT --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sticky top-6">
                <h3 class="text-lg font-bold mb-4">Pengaturan Prompt (Nano Banana)</h3>
                
                {{-- wire:model akan otomatis menghubungkan textarea ini dengan variabel $prompt di PHP --}}
                <textarea
                    wire:model="prompt"
                    rows="8"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                    placeholder="Tulis prompt di sini..."
                ></textarea>
                
                <p class="text-xs text-gray-500 mt-2 mb-4">
                    Variabel dinamis: <br><strong>[kota_asal]</strong> dan <strong>[kota_tujuan]</strong>
                </p>

                <x-filament::button wire:click="savePrompt" class="w-full">
                    💾 Simpan Prompt Permanen
                </x-filament::button>
            </div>
        </div>

        {{-- KOLOM KANAN: TABEL DATA RUTE --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                
                <div class="flex justify-between items-center mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <label class="flex items-center text-sm font-medium cursor-pointer">
                        <input type="checkbox" wire:model="forceRegenerate" class="mr-2 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                        Tumpuk Gambar Lama (Regenerate)
                    </label>
                    <x-filament::button wire:click="generateBulk" color="success">
                        🚀 Generate Gambar Terpilih
                    </x-filament::button>
                </div>

                <div class="overflow-x-auto" wire:poll.3s>
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                <th class="p-3 border-b dark:border-gray-700 font-bold">Pilih</th>
                                <th class="p-3 border-b dark:border-gray-700 font-bold">Rute (Asal - Tujuan)</th>
                                <th class="p-3 border-b dark:border-gray-700 font-bold">Status</th>
                                <th class="p-3 border-b dark:border-gray-700 font-bold">Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($this->rutes as $rute)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="p-3">
                                        <input type="checkbox" wire:model="selectedRutes" value="{{ $rute->id }}" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                                    </td>
                                    <td class="p-3 font-medium text-gray-900 dark:text-white">
                                        {{ $rute->kota_asal }} &rarr; {{ $rute->kota_tujuan }}
                                    </td>
                                    <td class="p-3">
                                        @if($rute->image_path)
                                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">Selesai</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">Belum</span>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        @if($rute->image_path)
                                            <img src="{{ asset($rute->image_path) }}" class="h-10 w-24 object-cover rounded shadow-sm border border-gray-200">
                                        @else
                                            <div class="h-10 w-24 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-xs text-gray-400 rounded border border-gray-200 border-dashed dark:border-gray-700">Kosong</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada rute.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-filament-panels::page>