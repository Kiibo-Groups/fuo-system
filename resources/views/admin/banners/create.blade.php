@extends('layouts.app')

@section('content')
<div class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('admin.banners.index') }}" class="text-slate-400 hover:text-orange-500 text-sm font-bold flex items-center gap-2 mb-4 transition-colors">
                <i class="fas fa-arrow-left"></i> Volver a Banners
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Nuevo Banner</h1>
            <p class="text-slate-500 font-medium">Sube una nueva imagen publicitaria.</p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 md:p-8">
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Imagen del Banner (Recomendado: 1200x400)</label>
                    <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 hover:border-orange-300 transition-colors text-center cursor-pointer relative bg-slate-50" id="drop-area">
                        <input type="file" name="image" id="image-input" required accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                        <div id="preview-container" class="hidden">
                            <img id="image-preview" src="" alt="Preview" class="max-h-48 mx-auto rounded-xl shadow-sm mb-4">
                            <p class="text-xs font-bold text-orange-600">Cambiar imagen</p>
                        </div>
                        <div id="upload-prompt">
                            <i class="fas fa-cloud-upload-alt text-3xl text-slate-300 mb-3"></i>
                            <p class="text-sm font-bold text-slate-700">Arrastra o haz clic para subir</p>
                            <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">JPG, PNG, WEBP (Max 5MB)</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Título (Opcional)</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Ej: Gran Oferta de Mayo" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none font-semibold text-slate-700 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Orden de Aparición</label>
                        <input type="number" name="order" value="{{ old('order', 0) }}" required
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none font-semibold text-slate-700 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Audiencia Especial</label>
                        <select name="target_audience" required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none font-semibold text-slate-700 text-sm appearance-none">
                            <option value="both" {{ old('target_audience') == 'both' ? 'selected' : '' }}>Todos (Owners y Clientes)</option>
                            <option value="owner" {{ old('target_audience') == 'owner' ? 'selected' : '' }}>Solo Owners (Dashboard)</option>
                            <option value="client" {{ old('target_audience') == 'client' ? 'selected' : '' }}>Solo Clientes (Catálogo)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Enlace de Destino (Opcional)</label>
                        <input type="url" name="target_url" value="{{ old('target_url') }}" placeholder="https://..." 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 outline-none font-semibold text-slate-700 text-sm">
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-5 h-5 rounded border-slate-300 text-orange-500 focus:ring-orange-500 cursor-pointer">
                    <label for="is_active" class="text-sm font-bold text-slate-700 cursor-pointer">Activar Inmediatamente</label>
                </div>

                <div class="pt-4 text-right">
                    <button type="submit" class="bg-slate-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-slate-800 shadow-lg shadow-slate-200 transition-all uppercase tracking-widest text-xs flex items-center justify-center gap-2 w-full md:w-auto ml-auto">
                        <i class="fas fa-save text-orange-500"></i> Guardar Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('image-input').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const tempUrl = URL.createObjectURL(e.target.files[0]);
            document.getElementById('image-preview').src = tempUrl;
            document.getElementById('preview-container').classList.remove('hidden');
            document.getElementById('upload-prompt').classList.add('hidden');
        }
    });
</script>
@endsection
