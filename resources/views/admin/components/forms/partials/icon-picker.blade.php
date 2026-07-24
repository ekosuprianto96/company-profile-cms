@php
    // Picker ikon MaterialIcons: admin cukup memilih, tidak perlu hafal nama.
    // Nilai yang tersimpan = key MaterialIcons (kebab-case) yang dirender mobile.
    // Preview web memakai font "Material Icons" (ligatur underscore) → dash diganti underscore.
    $ipId = 'iconpick_' . uniqid();
    $ipField = $iconFieldName ?? 'icon';
    $ipLabel = $iconLabel ?? 'Ikon';
    $ipValue = old($ipField, $selectedIcon ?? '');
    $ipRequired = $iconRequired ?? false;
    $ipPopular = config('material_icons.popular', []);
    $ipAll = config('material_icons.icons', []);
@endphp

<div class="form-group">
    <label class="form-label">
        {{ $ipLabel }}
        @if ($ipRequired)<span class="text-danger">*</span>@endif
        <small class="text-muted">— pilih dari daftar, tidak perlu hafal nama</small>
    </label>

    <input type="hidden" name="{{ $ipField }}" id="{{ $ipId }}_value" value="{{ $ipValue }}">

    <div class="d-flex align-items-center p-2 border rounded" style="gap:12px; background:#fafafa">
        <span id="{{ $ipId }}_preview" class="material-icons"
              style="font-size:34px; width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center; color:#0e4751; background:#fff; border:1px solid #e5e7eb; border-radius:10px;">{{ $ipValue ? str_replace('-', '_', $ipValue) : 'add' }}</span>
        <div class="flex-grow-1">
            <div style="font-size:.85em; color:#334155">Ikon terpilih:
                <code id="{{ $ipId }}_name">{{ $ipValue ?: '—' }}</code>
            </div>
            <div class="mt-1" style="display:flex; gap:8px">
                <button type="button" class="btn btn-sm btn-primary" id="{{ $ipId }}_toggle">
                    <i class="ri-apps-2-line"></i> Pilih Ikon
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $ipId }}_clear">Hapus</button>
            </div>
        </div>
    </div>

    <div id="{{ $ipId }}_panel" class="border rounded mt-2 p-2" style="display:none; background:#fff">
        <input type="text" class="form-control form-control-sm mb-2" id="{{ $ipId }}_search"
               placeholder="Cari ikon… (mis. home, chair, build, paint)" autocomplete="off">
        <div id="{{ $ipId }}_grid"
             style="display:grid; grid-template-columns:repeat(auto-fill,minmax(84px,1fr)); gap:6px; max-height:280px; overflow-y:auto;"></div>
        <small class="text-muted" id="{{ $ipId }}_count"></small>
    </div>

    <div class="invalid-feedback d-block" data-error="{{ $ipField }}"><span class="text-danger" style="font-size:.8em"></span></div>
</div>

<script>
(function () {
    const POPULAR = @json(array_values($ipPopular));
    const ALL = @json(array_values($ipAll));
    const LIMIT = 240; // batas render agar tetap ringan

    const hidden = document.getElementById('{{ $ipId }}_value');
    const preview = document.getElementById('{{ $ipId }}_preview');
    const nameEl = document.getElementById('{{ $ipId }}_name');
    const toggle = document.getElementById('{{ $ipId }}_toggle');
    const clearBtn = document.getElementById('{{ $ipId }}_clear');
    const panel = document.getElementById('{{ $ipId }}_panel');
    const search = document.getElementById('{{ $ipId }}_search');
    const grid = document.getElementById('{{ $ipId }}_grid');
    const countEl = document.getElementById('{{ $ipId }}_count');

    const lig = (n) => (n || '').replace(/-/g, '_'); // nama kebab → ligatur font web

    function setValue(name) {
        hidden.value = name || '';
        nameEl.textContent = name || '—';
        preview.textContent = name ? lig(name) : 'add';
    }

    function render(list, headingWhenEmptySearch) {
        grid.innerHTML = '';
        const shown = list.slice(0, LIMIT);
        shown.forEach((name) => {
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.title = name;
            cell.className = 'icon-pick-cell';
            cell.style.cssText = 'display:flex; flex-direction:column; align-items:center; gap:2px; padding:6px 2px; border:1px solid ' +
                (name === hidden.value ? '#0e4751' : '#eef2f7') + '; border-radius:8px; background:' +
                (name === hidden.value ? '#e8f6f7' : '#fff') + '; cursor:pointer; overflow:hidden;';
            cell.innerHTML =
                '<span class="material-icons" style="font-size:24px; color:#0e4751">' + lig(name) + '</span>' +
                '<span style="font-size:.62em; color:#64748b; line-height:1.1; word-break:break-all; text-align:center">' + name + '</span>';
            cell.addEventListener('click', function () {
                setValue(name);
                render(list, headingWhenEmptySearch); // refresh highlight
            });
            grid.appendChild(cell);
        });
        const extra = list.length > LIMIT ? ' (menampilkan ' + LIMIT + ' pertama — persempit pencarian)' : '';
        countEl.textContent = (headingWhenEmptySearch || (list.length + ' ikon')) + extra;
    }

    function refresh() {
        const q = (search.value || '').trim().toLowerCase();
        if (!q) {
            render(POPULAR, 'Ikon populer (' + POPULAR.length + ') — ketik untuk cari dari ' + ALL.length + ' ikon');
        } else {
            const starts = [], contains = [];
            for (const n of ALL) {
                const i = n.indexOf(q);
                if (i === 0) starts.push(n);
                else if (i > 0) contains.push(n);
            }
            render(starts.concat(contains), null);
        }
    }

    toggle.addEventListener('click', function () {
        const open = panel.style.display === 'none';
        panel.style.display = open ? 'block' : 'none';
        if (open && !grid.children.length) refresh();
    });
    clearBtn.addEventListener('click', function () { setValue(''); if (panel.style.display !== 'none') refresh(); });
    let deb;
    search.addEventListener('input', function () { clearTimeout(deb); deb = setTimeout(refresh, 140); });

    setValue(hidden.value); // sinkron tampilan awal
})();
</script>
