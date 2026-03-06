@extends('layouts.app')

@section('content')
<div class="h-[calc(100vh-80px)] overflow-hidden flex gap-6 p-4 lg:p-6 bg-slate-50 relative">
    <!-- Panel Izquierdo: Productos -->
    <div class="flex-1 flex flex-col min-h-0 bg-white rounded-3xl border border-slate-100 shadow-sm">
        <div class="p-6 border-b border-slate-100">
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight uppercase">Punto de Venta</h1>
            <p class="text-slate-500 font-medium text-sm mt-1">Selecciona los equipos a vender.</p>
            
            <div class="mt-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="posSearch" placeholder="Buscar por modelo o folio..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 font-bold text-sm outline-none transition-all">
                </div>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="productsGrid">
                @forelse($generators as $generator)
                    <div class="product-card cursor-pointer bg-white border-2 border-slate-100 hover:border-orange-400 rounded-2xl p-4 transition-all"
                         data-id="{{ $generator->id }}"
                         data-folio="{{ $generator->internal_folio }}"
                         data-model="{{ $generator->model }}"
                         data-price="{{ $generator->sale_price }}"
                         data-search="{{ strtolower($generator->internal_folio . ' ' . $generator->model . ' ' . $generator->serial_number) }}"
                         onclick="addToCart(this)">
                        
                        <div class="text-xs text-slate-400 font-mono mb-1">FO: {{ $generator->internal_folio }}</div>
                        <h3 class="font-bold text-slate-900 text-sm line-clamp-2 mb-2">{{ $generator->model }}</h3>
                        <div class="flex items-center justify-between mt-autpt-2 border-t border-slate-50">
                            <span class="bg-emerald-50 text-emerald-600 px-2 rounded font-black text-[10px] uppercase">Stock: 1</span>
                            <span class="font-black text-orange-600">${{ number_format($generator->sale_price, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400 font-bold uppercase tracking-widest text-xs bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        No hay equipos con precio de venta configurado
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Panel Derecho: Carrito / Ticket -->
    <div id="cartPanel" class="fixed inset-0 z-40 lg:relative lg:inset-auto lg:z-auto w-full lg:w-96 flex flex-col min-h-0 bg-slate-900 text-white lg:rounded-3xl shadow-xl overflow-hidden transform translate-x-full lg:translate-x-0 transition-transform duration-300">
        <div class="p-4 lg:p-6 border-b border-slate-800 flex justify-between items-center">
            <h2 class="text-base lg:text-lg font-black uppercase tracking-widest text-orange-500 flex items-center gap-2">
                <i class="fas fa-shopping-cart"></i> Ticket de Venta
            </h2>
            <button onclick="toggleCart()" class="lg:hidden text-slate-400 hover:text-white p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4" id="cartItems">
            <div class="h-full flex flex-col items-center justify-center text-slate-600 opactiy-50" id="emptyCart">
                <i class="fas fa-box-open text-4xl mb-3"></i>
                <p class="font-bold text-sm uppercase tracking-widest">Carrito Vacío</p>
            </div>
        </div>

        <div class="bg-slate-800 p-6 rounded-t-3xl mt-auto">
            <div class="flex justify-between items-center mb-4 text-slate-300">
                <span class="font-bold text-sm">Subtotal</span>
                <span class="font-mono text-sm" id="subtotalLabel">$0.00</span>
            </div>
            <div class="flex justify-between items-center mb-6">
                <span class="font-black text-lg text-white">Total a Pagar</span>
                <span class="font-mono text-2xl font-black text-orange-500" id="totalLabel">$0.00</span>
            </div>
            
            <button onclick="openCheckoutModal()" class="w-full bg-orange-600 hover:bg-orange-500 text-white font-black uppercase tracking-widest py-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2" id="btnCheckout" disabled>
                <i class="fas fa-check-circle"></i> Procesar Venta
            </button>
        </div>
    </div>
    </div>

    <!-- Botón Flotante Carrito Mobile -->
    <button onclick="toggleCart()" class="lg:hidden fixed bottom-6 right-6 z-30 bg-orange-600 text-white w-14 h-14 rounded-full shadow-xl shadow-orange-600/40 flex items-center justify-center text-xl transition-transform active:scale-95">
        <div class="relative">
            <i class="fas fa-shopping-cart"></i>
            <span id="mobileCartBadge" class="absolute -top-3 -right-3 bg-red-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center hidden border-2 border-orange-600">0</span>
        </div>
    </button>
</div>

<!-- Modal Checkout -->
<div id="modalCheckout" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl relative">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-widest">Finalizar Venta</h3>
            <button onclick="closeCheckoutModal()" class="text-slate-400 hover:text-red-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="checkoutForm" onsubmit="processSale(event)">
                <div class="mb-4">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Cliente (Opcional)</label>
                    <input type="text" id="clientName" placeholder="Público en general" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 font-bold text-slate-800 outline-none">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Método de Pago</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="paymentMethod" value="Efectivo" checked class="peer sr-only">
                            <div class="bg-slate-50 border-2 border-slate-100 peer-checked:border-orange-500 peer-checked:text-orange-600 rounded-xl p-3 text-center font-bold text-slate-500 transition-all flex flex-col items-center gap-2">
                                <i class="fas fa-money-bill-wave text-xl"></i> <span class="text-xs uppercase">Efectivo</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="paymentMethod" value="Transferencia" class="peer sr-only">
                            <div class="bg-slate-50 border-2 border-slate-100 peer-checked:border-orange-500 peer-checked:text-orange-600 rounded-xl p-3 text-center font-bold text-slate-500 transition-all flex flex-col items-center gap-2">
                                <i class="fas fa-university text-xl"></i> <span class="text-xs uppercase">Transferencia</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="paymentMethod" value="Tarjeta" class="peer sr-only">
                            <div class="bg-slate-50 border-2 border-slate-100 peer-checked:border-orange-500 peer-checked:text-orange-600 rounded-xl p-3 text-center font-bold text-slate-500 transition-all flex flex-col items-center gap-2">
                                <i class="fas fa-credit-card text-xl"></i> <span class="text-xs uppercase">Tarjeta</span>
                            </div>
                        </label>
                    </div>
                </div>
                <button type="submit" class="w-full bg-slate-900 text-white font-black uppercase tracking-widest py-4 rounded-xl shadow-lg hover:bg-slate-800 transition-all flex items-center justify-center gap-2" id="btnConfirmSale">
                    Confirmar $<span id="modalTotalLabel">0.00</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let cart = [];
    
    // Búsqueda en POS
    document.getElementById('posSearch').addEventListener('input', function(e) {
        const value = e.target.value.toLowerCase();
        document.querySelectorAll('.product-card').forEach(card => {
            const searchStr = card.getAttribute('data-search');
            if (searchStr.includes(value)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    function addToCart(element) {
        const id = element.getAttribute('data-id');
        const folio = element.getAttribute('data-folio');
        const model = element.getAttribute('data-model');
        const price = parseFloat(element.getAttribute('data-price'));

        // Ya está en carrito? un generador es un item unico
        if (cart.find(c => c.id === id)) {
            alert('Este equipo ya está en el carrito.');
            return;
        }

        cart.push({ id, folio, model, price });
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(c => c.id !== id);
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        const emptyState = document.getElementById('emptyCart');
        const btnCheckout = document.getElementById('btnCheckout');
        
        // Limpiar
        container.innerHTML = '';
        
        let total = 0;

        if (cart.length === 0) {
            container.appendChild(emptyState);
            emptyState.style.display = 'flex';
            btnCheckout.disabled = true;
            btnCheckout.classList.add('opacity-50', 'cursor-not-allowed');
            document.getElementById('subtotalLabel').innerText = '$0.00';
            document.getElementById('totalLabel').innerText = '$0.00';
            return;
        }

        emptyState.style.display = 'none';
        btnCheckout.disabled = false;
        btnCheckout.classList.remove('opacity-50', 'cursor-not-allowed');

        cart.forEach(item => {
            total += item.price;
            
            const div = document.createElement('div');
            div.className = 'bg-slate-800 p-4 rounded-2xl mb-3 flex items-start gap-3 border border-slate-700/50 relative group';
            div.innerHTML = `
                <div class="flex-1 min-w-0">
                    <p class="text-[10px] text-slate-400 font-mono mb-1">FO: ${item.folio}</p>
                    <h4 class="font-bold text-sm text-white line-clamp-2 leading-tight">${item.model}</h4>
                    <p class="text-orange-500 font-black mt-2 font-mono">$${item.price.toFixed(2)}</p>
                </div>
                <button onclick="removeFromCart('${item.id}')" class="text-slate-500 hover:text-red-500 transition-colors p-2 shrink-0">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(div);
        });

        const formattedTotal = '$' + total.toFixed(2);
        document.getElementById('subtotalLabel').innerText = formattedTotal;
        document.getElementById('totalLabel').innerText = formattedTotal;
        document.getElementById('modalTotalLabel').innerText = total.toFixed(2);

        // Actualizar Badge Mobile
        const badge = document.getElementById('mobileCartBadge');
        if(cart.length > 0) {
            badge.innerText = cart.length;
            badge.classList.remove('hidden');
            // Animar un poco el botón si se añade algo nuevo (esto podría mejorarse)
            badge.parentElement.parentElement.classList.add('scale-110');
            setTimeout(() => { badge.parentElement.parentElement.classList.remove('scale-110'); }, 200);
        } else {
            badge.classList.add('hidden');
        }
    }

    function toggleCart() {
        const panel = document.getElementById('cartPanel');
        if(panel.classList.contains('translate-x-full')) {
            panel.classList.remove('translate-x-full');
            panel.classList.add('translate-x-0');
        } else {
            panel.classList.add('translate-x-full');
            panel.classList.remove('translate-x-0');
        }
    }

    function openCheckoutModal() {
        if(cart.length === 0) return;
        document.getElementById('modalCheckout').classList.remove('hidden');
    }

    function closeCheckoutModal() {
        document.getElementById('modalCheckout').classList.add('hidden');
    }

    async function processSale(e) {
        e.preventDefault();
        
        if (cart.length === 0) return;

        const btn = document.getElementById('btnConfirmSale');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        btn.disabled = true;

        const clientName = document.getElementById('clientName').value;
        const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
        const total = cart.reduce((acc, curr) => acc + curr.price, 0);

        const items = cart.map(c => ({ generator_id: c.id }));

        try {
            const response = await fetch("{{ route('owner.pos.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    client_name: clientName,
                    payment_method: paymentMethod,
                    total_amount: total,
                    items: items
                })
            });

            const data = await response.json();

            if (data.success) {
                cart = [];
                closeCheckoutModal();
                alert('¡Venta realizada exitosamente!');
                window.location.reload();
            } else {
                alert(data.message || 'Ocurrió un error al procesar la venta.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión o servidor.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>
@endsection
