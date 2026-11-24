<div class="mt-6 flex items-center justify-between">
    <button type="button" x-show="step > 1" @click="back()" class="rounded-md bg-slate-200 px-5 py-2.5 font-medium text-slate-800 hover:bg-slate-300">← Back</button><span x-show="step === 1"></span>
    <button type="button" x-show="step < total" @click="next()" class="rounded-md bg-brand-700 px-6 py-2.5 font-semibold text-white hover:bg-brand-800">Next →</button>
    <button type="submit" x-show="step === total" x-cloak class="rounded-md bg-accent-500 px-6 py-2.5 font-semibold text-white hover:bg-accent-600">{{ $submit }}</button>
</div>
