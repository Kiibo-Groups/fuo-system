<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneratorImage;
use App\Models\Generator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GeneratorImageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.role:admin']);
    }

    public function index()
    {
        $images = GeneratorImage::with(['uploader', 'generator'])
            ->orderByDesc('created_at')
            ->paginate(40);

        $stats = [
            'total'    => GeneratorImage::count(),
            'matched'  => GeneratorImage::where('matched', true)->count(),
            'pending'  => GeneratorImage::where('matched', false)->count(),
        ];

        return view('admin.generator-images.index', compact('images', 'stats'));
    }

    /**
     * Sube una o varias imágenes y las asigna al folio indicado.
     * Si ya existe un generador con ese folio, asigna la imagen directamente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'internal_folio' => 'required|string|max:100',
            'images'         => 'required|array|min:1',
            'images.*'       => 'required|file|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $folio = strtoupper(trim($request->internal_folio));

        // ¿Existe ya un generador con ese folio?
        $generator = Generator::where('internal_folio', $folio)->first();

        $uploaded = 0;
        foreach ($request->file('images') as $file) {
            $path = $file->store("generator-images/{$folio}", 'public');

            $imgRecord = GeneratorImage::create([
                'internal_folio' => $folio,
                'file_path'      => $path,
                'original_name'  => $file->getClientOriginalName(),
                'uploaded_by'    => Auth::id(),
                'generator_id'   => $generator?->id,
                'matched'        => $generator !== null,
            ]);

            // Si el generador existe, asignar la primera imagen al campo `image`
            if ($generator && !$generator->image) {
                $generator->update(['image' => $path]);
            }

            $uploaded++;
        }

        $msg = "{$uploaded} imagen(es) subida(s) para el folio <strong>{$folio}</strong>";
        if ($generator) {
            $msg .= " y asignada(s) al generador existente.";
        } else {
            $msg .= ". Se asignará automáticamente cuando se importe el generador.";
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Elimina una imagen de la biblioteca y des-linkea si está asignada.
     */
    public function destroy(GeneratorImage $generatorImage)
    {
        // Si era la imagen principal del generador, limpiarla
        if ($generatorImage->generator_id && $generatorImage->generator) {
            $gen = $generatorImage->generator;
            if ($gen->image === $generatorImage->file_path) {
                // Buscar otra imagen del mismo generador
                $next = GeneratorImage::where('generator_id', $gen->id)
                    ->where('id', '!=', $generatorImage->id)
                    ->first();
                $gen->update(['image' => $next?->file_path]);
            }
        }

        Storage::disk('public')->delete($generatorImage->file_path);
        $generatorImage->delete();

        return redirect()->back()->with('success', 'Imagen eliminada.');
    }

    /**
     * Re-ejecutar el matching: busca imágenes sin asignar cuyo folio
     * ya existe en generators y las asocia.
     */
    public function rematch()
    {
        $matched = 0;

        // Recorre todos los generadores sin imagen y busca una imagen
        // cuyo folio coincida exacto O sea prefijo del folio del generador.
        $generators = Generator::whereNull('image')->orWhere('image', '')->get();

        foreach ($generators as $gen) {
            $folioNorm = strtoupper(trim($gen->internal_folio));

            $imageRecord = GeneratorImage::whereRaw(
                "UPPER(TRIM(internal_folio)) = ? OR UPPER(TRIM(internal_folio)) = LEFT(?, LENGTH(TRIM(internal_folio)))",
                [$folioNorm, $folioNorm]
            )->first();

            if ($imageRecord) {
                $gen->update(['image' => $imageRecord->file_path]);

                $isExact = strtoupper(trim($imageRecord->internal_folio)) === $folioNorm;
                if ($isExact) {
                    $imageRecord->update(['generator_id' => $gen->id, 'matched' => true]);
                }
                $matched++;
            }
        }

        return redirect()->back()->with('success', "Re-matching completado: {$matched} generador(es) con imagen asignada.");
    }
}
