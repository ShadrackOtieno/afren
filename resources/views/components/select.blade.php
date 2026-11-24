@props(['name', 'label', 'options' => [], 'value' => null, 'required' => false, 'placeholder' => 'Select…'])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700">{{ $label }}@if($required)<span class="text-red-600" aria-hidden="true"> *</span>@endif</label>
    <select id="{{ $name }}" name="{{ $name }}" @required($required) {{ $attributes->except('class') }}
        class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-brand-600 focus:ring-brand-600">
        @if($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $k => $v)
            @php($optValue = array_is_list(is_array($options) ? $options : $options->all()) ? $v : $k)
            <option value="{{ $optValue }}" @selected((string) old($name, $value) === (string) $optValue)>{{ $v }}</option>
        @endforeach
    </select>
    @error($name)<p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
</div>
