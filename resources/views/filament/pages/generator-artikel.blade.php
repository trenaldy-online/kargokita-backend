<x-filament-panels::page>
    <form wire:submit="generateAi">
        {{ $this->form }}

        <div class="mt-6 text-center">
            <x-filament::button type="submit" color="success" size="lg">
                🚀 Mulai Generate Artikel Sekarang
            </x-filament::button>
        </div>
    </form>

    @php
        $stats = $this->getQueueStats();
        $totalSelesaiVal = $stats['completed'] + $stats['failed'];
        $totalBatchVal = $stats['pending'] + $stats['processing'] + $totalSelesaiVal;
    @endphp

    <div wire:poll.2s class="mt-12 bg-white p-10 rounded-3xl shadow-sm border border-gray-200">
        
        <h3 class="text-3xl font-extrabold text-center text-gray-900 mb-10 tracking-tight">
            Live Monitor Pekerja AI
        </h3>
        
        <div class="max-w-3xl mx-auto mb-10">
            <div class="flex justify-between text-xs font-bold text-gray-400 mb-2 px-1">
                <span>0%</span>
                <span>25%</span>
                <span>50%</span>
                <span>75%</span>
                <span>100%</span>
            </div>

            <div class="w-full bg-gray-100 rounded-full h-12 overflow-hidden shadow-inner">
                <div class="bg-gradient-to-r from-orange-400 to-orange-500 h-12 rounded-full transition-all duration-700 ease-out flex items-center justify-end px-4" 
                     style="width: {{ $stats['persentase'] }}%">
                    @if($stats['persentase'] > 10)
                        <span class="text-white font-black text-xl drop-shadow-md">{{ $stats['persentase'] }}%</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="text-center mb-8">
            <h4 class="text-2xl font-black text-gray-900 tracking-tight">
                Total Artikel Selesai: <span class="text-orange-500">{{ $stats['completed'] }}</span>
            </h4>
            @if($totalBatchVal > 0)
                <p class="mt-1 text-sm font-medium text-gray-500">Dari total target batch: {{$totalBatchVal}} rute</p>
            @endif
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
            
            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 text-center flex flex-col justify-center">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Menunggu Antrean</div>
                <div class="text-2xl font-black text-gray-700">{{ $stats['pending'] }}</div>
            </div>

            <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100 text-center flex flex-col justify-center">
                <div class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-1">Sedang Diproses</div>
                <div class="text-2xl font-black text-blue-700 flex justify-center items-center gap-2">
                    @if($stats['processing'] > 0)
                        <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    @endif
                    {{ $stats['processing'] }}
                </div>
            </div>

            <div class="bg-red-50 p-4 rounded-2xl border border-red-100 text-center flex flex-col justify-center">
                <div class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Gagal (Error)</div>
                <div class="text-2xl font-black text-red-700">{{ $stats['failed'] }}</div>
            </div>

        </div>

    </div>
</x-filament-panels::page>