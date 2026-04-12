@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-6 lg:p-8">

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3 font-bold text-sm">
        <i class="fas fa-check-circle text-emerald-500 text-lg"></i> {!! session('success') !!}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-bold">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $errors->first() }}
    </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Biblioteca de Fotos de Equipos</h1>
            <p class="text-slate-500 font-medium mt-1">Sube imágenes y asígnalas a un folio. Se auto-asignan al importar el Excel.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Re-match manual --}}
            @if($stats['pending'] > 0)
            <form action="{{ route('admin.generator-images.rematch') }}" method="POST">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white font-black text-xs uppercase tracking-widest px-4 py-2.5 rounded-xl shadow-lg shadow-violet-500/20 transition-all">
                    <i class="fas fa-magic"></i> Re-asignar Pendientes ({{ $stats['pending'] }})
                </button>
            </form>
            @endif
            <button onclick="openUploadModal()"
                class="inline-flex items-center gap-2 bg-slate-900 hover:bg-violet-700 text-white font-black text-xs uppercase tracking-widest px-5 py-2.5 rounded-xl shadow-lg transition-all">
                <i class="fas fa-cloud-upload-alt"></i> Subir Imágenes
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-center">
            <p class="text-3xl font-black text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Total subidas</p>
        </div>
        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5 text-center">
            <p class="text-3xl font-black text-emerald-600">{{ $stats['matched'] }}</p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Asignadas</p>
        </div>
        <div class="bg-white rounded-2xl border border-violet-100 shadow-sm p-5 text-center">
            <p class="text-3xl font-black text-violet-600">{{ $stats['pending'] }}</p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">En espera</p>
        </div>
    </div>

    <!-- Grid de Imágenes -->
    @if($images->isEmpty())
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-20 text-center">
        <div class="w-20 h-20 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-6 border border-slate-100">
            <i class="fas fa-images text-3xl text-slate-300"></i>
        </div>
        <h3 class="font-black text-slate-700 text-lg mb-2">Sin imágenes todavía</h3>
        <p class="text-slate-400 text-sm mb-6">Sube fotos de equipos y asígnalas a un folio para comenzar.</p>
        <button onclick="openUploadModal()"
            class="inline-flex items-center gap-2 bg-slate-900 text-white font-black text-xs uppercase tracking-widest px-6 py-3 rounded-xl shadow-lg hover:bg-violet-700 transition-all">
            <i class="fas fa-cloud-upload-alt"></i> Subir Primera Imagen
        </button>
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4">
        @foreach($images as $img)
        <div class="group relative bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-violet-200 transition-all">
            {{-- Imagen --}}
            <div class="aspect-square overflow-hidden bg-slate-50">
                <img src="{{ Storage::url($img->file_path) }}"
                    alt="{{ $img->internal_folio }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    onerror="this.src=''; this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-full text-slate-200\'><i class=\'fas fa-image text-3xl\'></i></div>'">
            </div>

            {{-- Folio badge --}}
            <div class="p-3">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="font-black text-slate-900 text-[10px] uppercase tracking-tight truncate">{{ $img->internal_folio }}</span>
                    @if($img->matched)
                    <span class="shrink-0 w-2 h-2 rounded-full bg-emerald-400" title="Asignada al generador"></span>
                    @else
                    <span class="shrink-0 w-2 h-2 rounded-full bg-violet-400 animate-pulse" title="Esperando importación"></span>
                    @endif
                </div>
                @if($img->generator)
                <a href="{{ route('inventory.generators.show', $img->generator_id) }}"
                    class="text-[9px] text-emerald-600 font-bold hover:underline truncate block">
                    <i class="fas fa-link text-[7px]"></i> Ver generador
                </a>
                @else
                <p class="text-[9px] text-violet-500 font-bold">En espera de importación</p>
                @endif
                <p class="text-[9px] text-slate-300 mt-0.5">{{ $img->created_at->format('d/m/Y') }}</p>
            </div>

            {{-- Delete overlay --}}
            <form action="{{ route('admin.generator-images.destroy', $img->id) }}" method="POST"
                onsubmit="return confirm('¿Eliminar esta imagen?')"
                class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                @csrf @method('DELETE')
                <button type="submit"
                    class="bg-red-500 hover:bg-red-600 text-white rounded-lg w-7 h-7 flex items-center justify-center text-[10px] shadow-lg transition-all">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
        @endforeach
    </div>

    {{-- Paginación --}}
    @if($images->hasPages())
    <div class="mt-6">{{ $images->links() }}</div>
    @endif
    @endif
</div>

<!-- ===== MODAL: Subir Imágenes ===== -->
<div id="modal-upload" class="hidden fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-violet-900">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-black text-white uppercase tracking-tight">Subir Fotos de Equipos</h3>
                    <p class="text-[10px] text-violet-300 mt-1">Asigna las imágenes a un folio interno. Se auto-enlazan al importar.</p>
                </div>
                <button onclick="closeUploadModal()" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('admin.generator-images.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">
                    Folio Interno del Equipo *
                </label>
                <input type="text" name="internal_folio" required
                    placeholder="Ej. FUO-2024-001"
                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-black text-slate-800 focus:ring-2 focus:ring-violet-500 outline-none uppercase tracking-widest"
                    oninput="this.value=this.value.toUpperCase()">
                <p class="text-[10px] text-slate-400 mt-1">Debe coincidir exactamente con el folio del Excel para el match automático.</p>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">
                    Imágenes del Equipo *
                </label>
                <div id="drop-zone"
                    class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center hover:border-violet-400 transition-colors cursor-pointer relative bg-slate-50">
                    <input type="file" name="images[]" multiple accept="image/jpeg,image/jpg,image/png,image/webp"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        id="file-input" onchange="updateFileStatus(this)">
                    <div id="drop-content">
                        <i class="fas fa-cloud-upload-alt text-4xl text-slate-300 mb-3"></i>
                        <p class="text-sm font-bold text-slate-400">Arrastra imágenes aquí o haz click</p>
                        <p class="text-[10px] text-slate-300 mt-1">JPG, PNG, WEBP · Máx 5MB por imagen · Múltiples permitidas</p>
                    </div>
                    <div id="file-preview" class="hidden flex-wrap gap-2 justify-center"></div>
                </div>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="closeUploadModal()"
                    class="flex-1 py-3 text-xs font-black text-slate-500 hover:bg-slate-50 rounded-xl transition-all uppercase border border-slate-200">
                    Cancelar
                </button>
                <button type="submit"
                    class="flex-1 py-3 text-xs font-black text-white bg-violet-600 hover:bg-violet-700 rounded-xl shadow-lg shadow-violet-500/20 transition-all uppercase tracking-widest flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Guardar Imágenes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUploadModal() { document.getElementById('modal-upload').classList.remove('hidden'); }
    function closeUploadModal() { document.getElementById('modal-upload').classList.add('hidden'); }

    document.getElementById('modal-upload').addEventListener('click', function(e) {
        if (e.target === this) closeUploadModal();
    });

    function updateFileStatus(input) {
        const files = Array.from(input.files);
        const dropContent = document.getElementById('drop-content');
        const preview = document.getElementById('file-preview');
        const zone = document.getElementById('drop-zone');

        if (files.length === 0) {
            dropContent.classList.remove('hidden');
            preview.classList.add('hidden');
            preview.innerHTML = '';
            return;
        }

        dropContent.classList.add('hidden');
        preview.classList.remove('hidden');
        preview.classList.add('flex');
        preview.innerHTML = '';

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-20 h-20 object-cover rounded-xl border-2 border-violet-200 shadow-sm">
                    <span class="absolute -bottom-1 left-0 right-0 text-center text-[8px] bg-violet-600 text-white py-0.5 rounded-b-xl font-bold truncate px-1">${file.name.substring(0,12)}</span>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });

        // Badge count
        const badge = document.createElement('div');
        badge.className = 'absolute top-2 right-2 bg-violet-600 text-white text-[10px] font-black px-2 py-0.5 rounded-full';
        badge.textContent = `${files.length} img`;
        zone.style.position = 'relative';

        // Remove old badge
        const old = zone.querySelector('.img-badge');
        if (old) old.remove();
        badge.classList.add('img-badge');
        zone.appendChild(badge);
    }
</script>
@endsection
