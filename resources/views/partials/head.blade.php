@php($pal = \App\Support\Brand::palette())
<link rel="icon" href="{{ \App\Support\Brand::svg('favicon') }}" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme: { extend: {
  fontFamily: { sans: ['"DM Sans"', 'system-ui', 'sans-serif'], display: ['"DM Serif Display"', 'Georgia', 'serif'] },
  colors: {
    brand: @json($pal['brand']),
    accent: @json($pal['accent']),
    slate: { 50:'#f8fafc',100:'#f1f5f9',200:'#e2e8f0',300:'#cbd5e1',400:'#94a3b8',500:'#64748b',600:'#475569',700:'#334155',800:'#1e293b',900:'#0f172a' }
  } } } }
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
@php($dot = fn ($hex, $op) => 'url("data:image/svg+xml;utf8,'.rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24'><circle cx='3' cy='3' r='1.5' fill='{$hex}' fill-opacity='{$op}'/></svg>").'")')
<style>.bg-dots-brand{background-image:{!! $dot($pal['brand'][300], .45) !!}}.bg-dots-accent{background-image:{!! $dot($pal['accent'][300], .5) !!}}.bg-dots-light{background-image:{!! $dot('#ffffff', .12) !!}}
[x-cloak]{display:none!important} :focus-visible{outline:2px solid {{ $pal['accent'][500] }};outline-offset:2px}</style>
