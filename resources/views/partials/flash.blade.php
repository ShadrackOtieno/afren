@if(session('status'))<div class="mb-4 rounded-md bg-brand-50 px-4 py-3 text-sm text-brand-900" role="status">{{ session('status') }}</div>@endif
@if(session('error'))<div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">{{ session('error') }}</div>@endif
@if($errors->any())<div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-900" role="alert"><p class="font-semibold">Please fix the following:</p><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
