<div class="mt-6 space-y-6">
    <template x-for="s in total - 1" :key="s">
        <div x-show="review.some(r => r.step === s)"><div class="flex items-center justify-between border-b border-slate-200 pb-1"><h3 class="font-semibold text-brand-800" x-text="titles[s - 1]"></h3><button type="button" class="text-sm text-accent-700 underline" @click="go(s)">Edit</button></div>
            <dl class="mt-2 grid gap-x-6 gap-y-1 text-sm sm:grid-cols-2"><template x-for="i in review.filter(r => r.step === s)" :key="i.k"><div class="flex gap-2"><dt class="shrink-0 text-slate-500" x-text="i.k + ':'"></dt><dd class="break-words font-medium" x-text="i.v"></dd></div></template></dl></div>
    </template>
</div>
