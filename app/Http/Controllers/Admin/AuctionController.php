<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auction;
use App\Models\Generator;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::with(['generator', 'winner'])->latest();
        
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $auctions = $query->paginate(15)->appends($request->all());
        
        return view('admin.auctions.index', compact('auctions'));
    }

    public function create(Request $request)
    {
        $generators = Generator::whereNotIn('status', ['En subasta', 'Vendido'])->get();
        $branches = \App\Models\Branch::orderBy('name')->get();
        return view('admin.auctions.create', compact('generators', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'generator_id' => 'required|exists:generators,id',
            'branch_id' => 'nullable|exists:branches,id',
            'start_price' => 'required|numeric|min:0',
            'min_increment' => 'required|numeric|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'payment_deadline' => 'required|date|after:end_time',
            'assets.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi|max:20480',
        ]);

        $generator = Generator::findOrFail($validated['generator_id']);
        
        // Determinar status inicial de la subasta
        $status = now()->isAfter($validated['start_time']) ? 'active' : 'pending';

        $auction = Auction::create(array_merge($validated, [
            'current_price' => 0,
            'status' => $status
        ]));

        if ($request->hasFile('assets')) {
            foreach ($request->file('assets') as $index => $file) {
                $path = $file->store('auctions', 'public');
                $mime = $file->getMimeType();
                $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
                
                \App\Models\AuctionAsset::create([
                    'auction_id' => $auction->id,
                    'file_path' => $path,
                    'type' => $type,
                    'order' => $index
                ]);
            }
        }

        // Actualizar el generador
        $generator->status = 'En subasta';
        $generator->save();

        return redirect()->route('admin.auctions.index')->with('success', 'Subasta creada correctamente.');
    }

    public function show(Auction $auction)
    {
        $auction->load(['generator.assignedBranch', 'winner', 'bids.user']);
        return view('admin.auctions.show', compact('auction'));
    }

    public function cancel(Auction $auction)
    {
        if (!in_array($auction->status, ['pending', 'active'])) {
            return back()->with('error', 'Esta subasta no puede ser cancelada.');
        }
        $auction->status = 'cancelled';
        $auction->save();

        // Return generator to Disponible
        $generator = $auction->generator;
        $generator->status = 'Disponible';
        $generator->save();

        event(new \App\Events\AuctionStatusChanged($auction));

        return back()->with('success', 'Subasta cancelada.');
    }
}
