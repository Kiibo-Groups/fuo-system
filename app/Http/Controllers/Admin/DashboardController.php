<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Metrics
        $assignedCount = 0;
        $inTransitCount = 0;
        $inWorkshopCount = 0;
        $availableCount = 0;
        
        $query = \App\Models\Generator::query();
        
        if ($user->role === 'owner') {
            // Owner: ve todos los generadores ASIGNADOS a su sucursal,
            // sin importar el estado operativo actual
            $assignedCount = \App\Models\Generator::where('assigned_branch_id', $user->branch_id)->count();

            $inTransitCount = \App\Models\Generator::whereIn('status', ['Pedido en tránsito', 'Recibido en almacén', 'En revisión', 'En taller', 'Lista para envío', 'Enviado'])
                ->where('assigned_branch_id', $user->branch_id)->count();

            $inWorkshopCount = \App\Models\Generator::whereIn('status', ['En revisión', 'En taller'])
                ->where('assigned_branch_id', $user->branch_id)->count();

            $availableCount = \App\Models\Generator::where('status', 'Disponible')
                ->where('assigned_branch_id', $user->branch_id)->count();

            $query->where('assigned_branch_id', $user->branch_id);
        } else {
            // Admin metrics overall
            $inTransitCount = \App\Models\Generator::whereIn('status', ['Pedido en tránsito', 'Enviado'])->count();
            $inWorkshopCount = \App\Models\Generator::whereIn('status', ['En revisión', 'En taller'])->count();
            $availableCount = \App\Models\Generator::where('status', 'Disponible')->count();
        }

        $lowStockPartsCount = \App\Models\SparePart::where('stock', '<=', 5)->count();

        // Active Banners for this dashboard
        $activeBanners = [];
        if (in_array($user->role, ['owner', 'admin'])) {
            $activeBanners = \App\Models\Banner::where('is_active', true)
                ->whereIn('target_audience', ['owner', 'both'])
                ->orderBy('order')
                ->get();
        }

        // Recent inventory — carga también assignedBranch para mostrarlo en la tabla
        $recentGenerators = $query->with(['branch', 'assignedBranch', 'workshopLogs'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();


        return view('admin.dashboard', compact(
            'assignedCount',
            'inTransitCount', 
            'inWorkshopCount', 
            'availableCount', 
            'lowStockPartsCount',
            'recentGenerators',
            'activeBanners'
        ));
    }
}
