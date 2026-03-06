@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    
    <div class="mb-8 flex items-center gap-4">
        <div class="bg-orange-500 rounded-lg p-3 text-white shadow-lg shadow-orange-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-bold bg-clip-text dark:from-white dark:to-slate-300">
                Diagnóstico de Taller
            </h1>
            <p class="text-slate-500 mt-1">Registre reparaciones y refacciones utilizadas</p>
        </div>
    </div>

    <!-- Generador Info -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 mb-8 overflow-hidden flex flex-col md:flex-row shadow-sm">
        <div class="bg-slate-50 dark:bg-slate-900/50 p-6 md:w-1/3 border-b md:border-b-0 md:border-r border-slate-200 dark:border-slate-700/50">
            <h2 class="text-sm font-semibold text-slate-500 tracking-wider uppercase mb-4">Equipo en Reparación</h2>
            <div class="font-bold text-2xl text-slate-900 dark:text-white mb-1">{{ $generator->internal_folio }}</div>
            <div class="text-slate-500 mb-4">S/N: {{ $generator->serial_number }}</div>
            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                Modelo: {{ $generator->model }}
            </span>
        </div>
        <div class="p-6 md:w-2/3">
            @php $lastRevision = $generator->revisions()->latest()->first(); @endphp
            <h2 class="text-sm font-semibold text-slate-500 tracking-wider uppercase mb-3">Historial de Falla (Última Revisión)</h2>
            <p class="text-slate-700 dark:text-slate-300">
                {{ $lastRevision ? $lastRevision->observations : 'Sin información previa de la revisión.' }}
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 rounded-xl">
            <div class="flex gap-3">
                <svg class="w-5 h-5 shrink-0 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-red-600 dark:text-red-400">
                    <strong class="font-semibold text-sm">Hay errores en el formulario:</strong>
                    <ul class="list-disc pl-5 text-sm mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('operations.workshop.store') }}" method="POST" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/50 overflow-hidden">
        @csrf
        <input type="hidden" name="generator_id" value="{{ $generator->id }}">

        <div class="p-8">
            <!-- Diagnóstico -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"> 
                    Diagnóstico y Trabajo Realizado <span class="text-red-500">*</span>
                </label>
                <textarea name="diagnosis" rows="4" required
                          class="w-full bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 px-4 py-3 text-slate-900 dark:text-white placeholder:text-slate-400" 
                          placeholder="Describa el problema encontrado y los trabajos de reparación efectuados..."></textarea>
            </div>

            <!-- Refacciones -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300"> 
                        Refacciones Utilizadas (Opcional)
                    </label>
                    <button type="button" id="addPartBtn" class="text-orange-500 font-medium text-sm flex items-center gap-1 hover:text-orange-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Añadir Refacción
                    </button>
                </div>
                
                <div id="partsContainer" class="space-y-4">
                    <!-- Filas dinámicas se agregarán aquí -->
                </div>

                <div id="emptyPartsMessage" class="p-6 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-900/20 text-slate-500 text-sm">
                    No se han seleccionado refacciones. Presione "Añadir Refacción" si se necesitó usar partes del inventario.
                </div>
            </div>
        </div>

        <div class="px-8 py-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700/50 flex flex-col md:flex-row items-center justify-between gap-4 mt-8">
            <a href="{{ route('operations.workshop.index') }}" class="text-slate-600 dark:text-slate-400 font-medium hover:text-slate-900 dark:hover:text-white transition-colors">
                Cancelar y Volver
            </a>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <button type="submit" name="repair_result" value="failed" class="bg-white border-2 border-red-500 text-red-600 hover:bg-red-50 hover:text-red-700 py-3 px-6 rounded-xl font-bold shadow-sm transition-all flex items-center justify-center gap-2" onclick="return confirm('¿Está seguro que el equipo NO tiene arreglo? Se marcará de forma permanente como Fallido/No Procesado.')">
                    <i class="fas fa-times-circle"></i> No Tuvo Arreglo
                </button>
                <button type="submit" name="repair_result" value="fixed" class="bg-orange-500 hover:bg-orange-600 py-3 px-8 rounded-xl font-bold text-white shadow-lg shadow-orange-500/30 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> Finalizar (Sí tuvo arreglo)
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Template Oculto para fila de refacción -->
<template id="partRowTemplate">
    <div class="flex items-center gap-4 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 part-row">
        <div class="flex-1">
            <select name="parts[__INDEX__][id]" required class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 text-slate-900 dark:text-white focus:ring-orange-500 focus:border-orange-500">
                <option value="">-- Seleccionar Refacción --</option>
                @foreach($spareParts as $part)
                    <option value="{{ $part->id }}" data-stock="{{ $part->stock }}">
                        {{ $part->name }} (Stock: {{ $part->stock }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-24">
            <input type="number" name="parts[__INDEX__][quantity]" min="1" value="1" required class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 text-slate-900 dark:text-white focus:ring-orange-500 focus:border-orange-500" placeholder="Cant.">
        </div>
        <button type="button" class="text-slate-400 hover:text-red-500 transition-colors remove-part-btn w-10 h-10 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </button>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('partsContainer');
        const emptyMessage = document.getElementById('emptyPartsMessage');
        const addBtn = document.getElementById('addPartBtn');
        const template = document.getElementById('partRowTemplate');
        let partIndex = 0;

        function updateEmptyMessage() {
            if (container.children.length === 0) {
                emptyMessage.style.display = 'block';
            } else {
                emptyMessage.style.display = 'none';
            }
        }

        addBtn.addEventListener('click', function() {
            const rowHtml = template.innerHTML.replace(/__INDEX__/g, partIndex);
            
            // Convert to node
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = rowHtml;
            const rowNode = tempDiv.firstElementChild;
            
            // Add remove event listener
            const removeBtn = rowNode.querySelector('.remove-part-btn');
            removeBtn.addEventListener('click', function() {
                rowNode.remove();
                updateEmptyMessage();
            });

            // Add max validation logic dynamically based on selected option using JS
            const selectEl = rowNode.querySelector('select');
            const qtyInput = rowNode.querySelector('input[type="number"]');
            
            selectEl.addEventListener('change', function() {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                if(selectedOption && selectedOption.dataset.stock) {
                    qtyInput.max = selectedOption.dataset.stock;
                    if(parseInt(qtyInput.value) > parseInt(selectedOption.dataset.stock)) {
                        qtyInput.value = selectedOption.dataset.stock;
                    }
                } else {
                    qtyInput.removeAttribute('max');
                }
            });

            qtyInput.addEventListener('input', function() {
                if(this.max && parseInt(this.value) > parseInt(this.max)) {
                    this.value = this.max;
                }
            });

            container.appendChild(rowNode);
            partIndex++;
            updateEmptyMessage();
        });

        // Initialize state
        updateEmptyMessage();
    });
</script>
@endsection
