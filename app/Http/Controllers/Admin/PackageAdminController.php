<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\Audit;
use App\Support\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageAdminController extends Controller
{
    public function index() { return view('admin.packages.index', ['packages' => Package::withCount(['subscriptions as active_count' => fn ($q) => $q->where('status', 'active')])->orderBy('sort')->get()]); }
    public function create() { return view('admin.packages.form', ['package' => new Package(['currency' => 'USD', 'is_active' => true, 'billing_interval' => 'monthly'])]); }
    public function store(Request $r) { $p = Package::create($this->data($r)); Audit::log('package.created', $p); return redirect()->route('admin.packages.index')->with('status', 'Package created.'); }
    public function edit(Package $package) { return view('admin.packages.form', compact('package')); }
    public function update(Request $r, Package $package) { $package->update($this->data($r, $package)); Audit::log('package.updated', $package); return redirect()->route('admin.packages.index')->with('status', 'Package updated.'); }

    public function toggle(Package $package)
    {
        $package->update(['is_active' => ! $package->is_active]);
        Audit::log($package->is_active ? 'package.activated' : 'package.deactivated', $package);
        return back()->with('status', 'Package '.($package->is_active ? 'activated' : 'deactivated').'. Existing subscribers are not affected.');
    }

    private function data(Request $r, ?Package $existing = null): array
    {
        $d = $r->validate([
            'name' => 'required|string|max:120', 'tagline' => 'nullable|string|max:190', 'description' => 'nullable|string|max:3000',
            'price' => 'required|numeric|min:0', 'currency' => 'required|string|size:3', 'features' => 'nullable|string|max:3000',
            'max_requests_per_month' => 'nullable|integer|min:0', 'max_candidates_per_request' => 'nullable|integer|min:0', 'sort' => 'nullable|integer',
        ]);
        $slug = $existing?->slug ?? Str::slug($d['name']);
        if (! $existing && Package::where('slug', $slug)->exists()) { $slug .= '-'.Str::lower(Str::random(4)); }
        return [
            'name' => $d['name'], 'slug' => $slug, 'tagline' => $d['tagline'] ?? null, 'description' => $d['description'] ?? null, 'price' => $d['price'],
            'currency' => strtoupper($d['currency']), 'features' => Tags::parse(preg_split('/\r?\n/', (string) ($d['features'] ?? ''))),
            'max_requests_per_month' => $d['max_requests_per_month'] ?? null, 'max_candidates_per_request' => $d['max_candidates_per_request'] ?? null,
            'sort' => $d['sort'] ?? 0, 'is_active' => $r->boolean('is_active'), 'is_featured' => $r->boolean('is_featured'),
        ];
    }
}
