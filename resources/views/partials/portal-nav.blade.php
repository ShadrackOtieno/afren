<nav class="mt-5 flex flex-wrap items-center gap-2 text-sm font-medium" aria-label="Portal">
    @foreach([['overview', 'Subscription & billing', route('portal.show')], ['requests', 'My recruitment requests', route('portal.requests')]] as [$k, $l, $u])
    <a href="{{ $u }}" class="rounded-lg px-4 py-2 {{ $current === $k ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-800 hover:bg-brand-100' }}">{{ $l }}</a>@endforeach
    <a href="{{ route('request.create') }}" class="ml-auto rounded-lg bg-accent-500 px-4 py-2 text-white hover:bg-accent-600">+ New request</a>
</nav>
