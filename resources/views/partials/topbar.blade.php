@php($site = \App\Support\Site::all())
<div class="bg-brand-900 text-xs text-brand-100">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-1 px-4 py-2">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
            @if($site['phone'])<a class="flex items-center gap-1.5 hover:text-white" href="{{ \App\Support\Site::telHref($site['phone']) }}">@include('partials.social-icon', ['name' => 'phone']){{ $site['phone'] }}</a>@endif
            @if($site['email'])<a class="flex items-center gap-1.5 hover:text-white" href="mailto:{{ $site['email'] }}">@include('partials.social-icon', ['name' => 'mail']){{ $site['email'] }}</a>@endif
            <span class="hidden text-brand-300 md:inline">Serving researchers and organizations across Africa</span>
        </div>
        @if(\App\Support\Site::socials())<ul class="flex items-center gap-1" aria-label="Social media">
            @foreach(\App\Support\Site::socials() as $k => $url)
            <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-800 hover:bg-accent-500 hover:text-white" aria-label="{{ \App\Support\Site::label($k) }}">@include('partials.social-icon', ['name' => $k])</a></li>@endforeach
        </ul>@endif
    </div>
</div>
