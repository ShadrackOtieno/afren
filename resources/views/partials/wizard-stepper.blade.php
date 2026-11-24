<ol class="mb-6 grid gap-2" :style="`grid-template-columns: repeat(${total}, minmax(0, 1fr))`" aria-label="Progress">
    <template x-for="(t, i) in titles" :key="i">
        <li><button type="button" :disabled="!free && i + 1 > step" @click="jump(i + 1)" class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-sm font-medium transition"
            :class="step === i + 1 ? 'bg-brand-700 text-white' : (step > i + 1 || free ? 'bg-brand-100 text-brand-800 hover:bg-brand-200' : 'bg-slate-100 text-slate-500')" :aria-current="step === i + 1 ? 'step' : null">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/25 text-xs font-bold" x-text="step > i + 1 ? '✓' : i + 1"></span>
            <span class="hidden sm:inline" x-text="t"></span></button></li>
    </template>
</ol>
<p class="mb-4 text-sm text-slate-500 sm:hidden">Step <span x-text="step"></span> of <span x-text="total"></span>: <b x-text="titles[step - 1]"></b></p>
