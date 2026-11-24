<?php
namespace App\Http\Controllers;

use App\Support\Brand;

/** Serves the illustrations recoloured with the current brand colours. */
class BrandAssetController extends Controller
{
    public function svg(string $name)
    {
        abort_unless(in_array($name, ['hero', 'professionals', 'organisations', 'favicon'], true), 404);
        return response(Brand::applyToSvg(file_get_contents(resource_path("brand/{$name}.svg"))), 200, [
            'Content-Type' => 'image/svg+xml', 'Cache-Control' => 'public, max-age=31536000, immutable', // URL is versioned by colour hash
        ]);
    }
}
