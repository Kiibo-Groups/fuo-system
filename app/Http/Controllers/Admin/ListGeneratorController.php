<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GeneratorImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ListGeneratorController extends Controller
{
    public function index()
    {
        return view('admin.list-generator.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'raw_data' => 'required|string',
            'percentage' => 'required|numeric|min:0',
            'exchange_rate' => 'required|numeric|min:0',
            'multiplier' => 'required|numeric|min:0',
        ]);

        $rawData = $request->input('raw_data');
        $percentage = $request->input('percentage') / 100;
        $exchangeRate = $request->input('exchange_rate');
        $multiplier = $request->input('multiplier');

        // Parse paste data (TSV)
        $lines = explode("\n", str_replace("\r", "", trim($rawData)));
        $items = [];
        $missingSkus = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $columns = explode("\t", $line);
            
            // Suponiendo formato: SKU, Description, List Price
            $sku = trim($columns[0] ?? '');
            $description = trim($columns[1] ?? '');
            
            $listPriceRaw = trim($columns[2] ?? '0');
            $listPrice = (float) preg_replace('/[^0-9.]/', '', $listPriceRaw);

            if (empty($sku)) continue;

            // Calcular precio de venta: precio * (1 + %) * cambio DL * multiplicador, redondeado hacia arriba
            // La fórmula del cliente es "precio * % * cambio DL * 3.3". Asumiré (1 + %) si el margen se suma, o simplemente % si % = 1.20
            // Usaré precio * porcentaje como factor directo
            $salePrice = ceil($listPrice * $percentage * $exchangeRate * $multiplier);

            // Redondeo a numero cerrado superior sugerido en el PDF. ceil() hace esto a enteros, 
            // pero si quieren múltiplos de 50 o 100, se puede ajustar después.

            $items[] = [
                'sku' => $sku,
                'description' => $description,
                'list_price' => $listPrice,
                'sale_price' => $salePrice,
                'image_url' => null,
            ];
            $missingSkus[] = $sku;
        }

        // Buscar imágenes existentes en la DB
        $existingImages = GeneratorImage::whereIn('internal_folio', $missingSkus)->get()->keyBy('internal_folio');

        $skusToScrape = [];
        foreach ($items as &$item) {
            $sku = $item['sku'];
            if (isset($existingImages[$sku])) {
                $item['image_url'] = Storage::url($existingImages[$sku]->file_path);
            } else {
                $skusToScrape[] = $sku;
            }
        }
        unset($item);

        // Si faltan imágenes, llamar a la API del scraper
        if (!empty($skusToScrape)) {
            $scraperApiUrl = env('SCRAPER_API_URL');
            
            if ($scraperApiUrl) {
                try {
                    $response = Http::timeout(120)->post($scraperApiUrl . '/scrape', [
                        'skus' => array_values(array_unique($skusToScrape))
                    ]);

                    if ($response->successful()) {
                        $results = $response->json('results');

                        if ($results) {
                            foreach ($items as &$item) {
                                $sku = $item['sku'];
                                if (isset($results[$sku]) && $results[$sku]['success'] && !empty($results[$sku]['image'])) {
                                    $base64 = $results[$sku]['image'];
                                    
                                    // Parse base64
                                    @list($type, $fileData) = explode(';', $base64);
                                    @list(, $fileData)      = explode(',', $fileData);
                                    $fileData = base64_decode($fileData);
                                    
                                    // Determine extension
                                    $ext = 'jpg';
                                    if (strpos($type, 'png') !== false) $ext = 'png';
                                    if (strpos($type, 'webp') !== false) $ext = 'webp';

                                    $fileName = "{$sku}_" . time() . ".{$ext}";
                                    $path = "generator-images/{$sku}/{$fileName}";
                                    
                                    Storage::disk('public')->put($path, $fileData);

                                    // Insertar en BD
                                    GeneratorImage::create([
                                        'internal_folio' => $sku,
                                        'file_path'      => $path,
                                        'original_name'  => "scraped_{$fileName}",
                                        'uploaded_by'    => Auth::id() ?? 1, // Fallback si Auth::id no está (aunque estará protegido)
                                        'matched'        => false, // Aun no está en inventario real
                                    ]);

                                    $item['image_url'] = Storage::url($path);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Scraper API Error: " . $e->getMessage());
                }
            }
        }

        return view('admin.list-generator.preview', compact('items', 'percentage', 'exchangeRate', 'multiplier'));
    }
}
