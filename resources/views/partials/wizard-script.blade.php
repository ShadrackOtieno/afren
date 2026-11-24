<script>
document.addEventListener('alpine:init', () => {
  // Shared step-by-step form behaviour. Sections are <section x-ref="s1" data-step="1">…; the last one is the review step.
  Alpine.data('wizard', (start, titles, free) => ({
    step: start || 1, titles: titles, free: !!free, review: [],
    get total() { return this.titles.length; },
    init() { if (this.step === this.total) this.buildReview(); },
    go(n) { this.step = n; this.$nextTick(() => this.$refs.top.scrollIntoView({ behavior: 'smooth', block: 'start' })); },
    sectionValid(n, report = true) {
      const sec = this.$refs['s' + n];
      const pw = sec.querySelector('input[name=password]'), pc = sec.querySelector('input[name=password_confirmation]');
      if (pw && pc) pc.setCustomValidity(pw.value !== pc.value ? 'Passwords do not match' : '');
      for (const el of sec.querySelectorAll('input,select,textarea')) {
        if (!el.checkValidity()) { if (report) el.reportValidity(); return false; }
      }
      return true;
    },
    next() { if (!this.sectionValid(this.step)) return; if (this.step === this.total - 1) this.buildReview(); this.go(this.step + 1); },
    back() { this.go(this.step - 1); },
    jump(n) {
      if (n <= this.step) { this.go(n); return; }
      if (!this.free) return;
      for (let i = this.step; i < n; i++) {
        if (!this.sectionValid(i, false)) { this.go(i); this.$nextTick(() => this.sectionValid(i)); return; }
      }
      if (n === this.total) this.buildReview();
      this.go(n);
    },
    submitGuard(e) {
      if (this.step < this.total) { e.preventDefault(); this.next(); return; }
      for (let i = 1; i <= this.total; i++) {
        if (!this.sectionValid(i, false)) { e.preventDefault(); this.go(i); this.$nextTick(() => this.sectionValid(i)); return; }
      }
    },
    buildReview() {
      const f = this.$refs.form, out = [], seen = {};
      const skip = ['hp', 'consent', 'terms', 'direct', 'password', 'password_confirmation', '_token', '_method', 'visible_hidden'];
      f.querySelectorAll('input,select,textarea').forEach(el => {
        if (!el.name || el.type === 'hidden' || el.type === 'password' || skip.includes(el.name) || seen[el.name]) return;
        const sec = el.closest('[data-step]'); if (!sec || Number(sec.dataset.step) >= this.total) return;
        let v;
        if (el.type === 'file') v = [...el.files].map(x => x.name).join(', ');
        else if (el.type === 'checkbox') v = el.checked ? 'Yes' : 'No';
        else if (el.tagName === 'SELECT') v = [...el.selectedOptions].map(o => o.value ? o.text : '').filter(Boolean).join(', ');
        else v = el.value;
        if (!v) return;
        seen[el.name] = 1;
        const lab = f.querySelector('label[for="' + el.id + '"]');
        out.push({ k: (lab ? lab.textContent : el.name).replace('*', '').replace(/\(.*?\)/g, '').trim(), v, step: Number(sec.dataset.step) });
      });
      this.review = out;
    },
  }));
});
</script>
