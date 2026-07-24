@php
    // Picker kategori bertingkat: mulai dari kategori utama; kalau kategori
    // terpilih punya sub, dropdown sub muncul otomatis untuk dipilih — dst.
    $pickerId = 'catpick_' . uniqid();
    $catFieldName = $categoryFieldName ?? 'category_id';
    $catSelected = old($catFieldName, $selectedCategoryId ?? null);
    $catData = ($categoryTree ?? collect())
        ->map(fn ($c) => ['id' => (int) $c->id, 'parent_id' => $c->parent_id ? (int) $c->parent_id : null, 'name' => $c->name])
        ->values();
@endphp

<div class="form-group">
    <label class="form-label">Kategori</label>
    <input type="hidden" name="{{ $catFieldName }}" id="{{ $pickerId }}_value" value="{{ $catSelected }}">
    <div id="{{ $pickerId }}_wrap" class="d-flex flex-column" style="gap:8px"></div>
    <small class="text-muted">Pilih kategori utama. Jika ada sub-kategori, pilihannya akan muncul otomatis.</small>
    <div class="invalid-feedback d-block" data-error="{{ $catFieldName }}"><span></span></div>
</div>

<script>
(function () {
    const CATS = @json($catData);
    const hidden = document.getElementById('{{ $pickerId }}_value');
    const wrap = document.getElementById('{{ $pickerId }}_wrap');

    const childrenOf = (pid) => CATS.filter((c) => (c.parent_id === null ? '' : String(c.parent_id)) === String(pid ?? ''));
    const byId = (id) => CATS.find((c) => String(c.id) === String(id));
    const pathTo = (id) => {
        const path = [];
        let cur = byId(id);
        while (cur) {
            path.unshift(String(cur.id));
            cur = cur.parent_id ? byId(cur.parent_id) : null;
        }
        return path;
    };

    function build() {
        wrap.innerHTML = '';
        const path = hidden.value ? pathTo(hidden.value) : [];
        let parent = '';
        let level = 0;

        while (true) {
            const opts = childrenOf(parent);
            if (opts.length === 0) break; // tidak ada level lebih dalam

            const sel = path[level] || '';
            const label = level === 0 ? 'Kategori Utama' : 'Sub-kategori';
            const placeholder = level === 0 ? '— Pilih kategori —' : '— Pilih sub-kategori (opsional) —';

            const group = document.createElement('div');
            const select = document.createElement('select');
            select.className = 'form-control';
            select.dataset.parent = parent || '';
            select.innerHTML =
                `<option value="">${placeholder}</option>` +
                opts.map((o) => `<option value="${o.id}" ${String(o.id) === String(sel) ? 'selected' : ''}>${o.name}</option>`).join('');
            select.addEventListener('change', function () {
                // Nilai final = pilihan terdalam; kalau dikosongkan, jatuh ke induknya.
                hidden.value = this.value || (this.dataset.parent || '');
                build();
            });

            group.innerHTML = `<label class="text-muted" style="font-size:.78em; margin-bottom:2px">${label}</label>`;
            group.appendChild(select);
            wrap.appendChild(group);

            if (!sel) break;     // belum pilih di level ini → berhenti
            parent = sel;
            level++;
        }
    }

    build();
})();
</script>
