<?php
namespace App\Http\Controllers;

use App\Models\Package;

class PageController extends Controller
{
    public function home() { return view('public.home', ['packages' => Package::where('is_active', true)->orderBy('sort')->take(3)->get()]); }
    public function about() { return view('public.about'); }
    public function how() { return view('public.how'); }
    
    public function privacy() { return view('public.privacy'); }
    public function terms() { return view('public.terms'); }
}
