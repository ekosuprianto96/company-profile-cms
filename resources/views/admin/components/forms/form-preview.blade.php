@php
    /** @var array $schema */
    $fields = $schema['fields'] ?? [];
    $formName = $schema['name'] ?? 'Form Pengajuan';
@endphp

<style>
    .fp-wrap { display:flex; justify-content:center; padding:6px 0 2px; }
    .fp-phone { width:360px; height:660px; border:9px solid #111827; border-radius:40px; overflow:hidden;
                background:#fcf9f6; box-shadow:0 18px 44px rgba(39,90,86,.18); display:flex; flex-direction:column; }
    .fp-status { display:flex; justify-content:space-between; align-items:center; padding:8px 16px 4px;
                 font-size:10.5px; font-weight:700; color:#0f172a; background:#fff; }
    .fp-head { background:#fff; padding:6px 16px 12px; border-bottom:1px solid #eef2f7; }
    .fp-head .fp-title { font-size:15px; font-weight:800; color:#0f172a; }
    .fp-head .fp-sub { font-size:10.5px; color:#94a3b8; }
    .fp-body { flex:1; overflow-y:auto; padding:14px 16px 18px; }
    .fp-body::-webkit-scrollbar { width:5px; } .fp-body::-webkit-scrollbar-thumb { background:#d7e7e4; border-radius:4px; }
    .fp-cta { padding:10px 16px 14px; background:#fff; border-top:1px solid #eef2f7; }
    .fp-cta div { background:#275a56; color:#fff; text-align:center; padding:11px; border-radius:15px;
                  font-size:13px; font-weight:700; }

    .fp-sec { font-size:10px; font-weight:800; letter-spacing:.9px; text-transform:uppercase; color:#275a56;
              margin:16px 0 8px; padding-bottom:4px; border-bottom:1px solid #e5eeec; }
    .fp-sec:first-child { margin-top:0; }
    .fp-note { background:#eef5f4; border:1px solid #d7e7e4; color:#14322f; border-radius:12px;
               padding:9px 11px; font-size:11px; margin-bottom:13px; }
    .fp-f { margin-bottom:13px; }
    .fp-lb { font-size:11.5px; font-weight:700; color:#0f172a; margin-bottom:5px; display:block; }
    .fp-lb .fp-req { color:#b3261e; }
    .fp-cond { display:inline-block; background:#fff4e5; color:#8a5a00; border:1px solid #ffd8a8;
               border-radius:20px; padding:1px 6px; font-size:8.5px; font-weight:700; margin-left:4px; }
    .fp-in { background:#fff; border:1px solid #e2e8f0; border-radius:13px; padding:10px 12px;
             font-size:11.5px; color:#94a3b8; display:flex; align-items:center; justify-content:space-between; }
    .fp-in.fp-ta { height:64px; align-items:flex-start; }
    .fp-help { font-size:9.5px; color:#94a3b8; margin-top:4px; display:block; }
    .fp-chips { display:flex; flex-wrap:wrap; gap:6px; }
    .fp-chip { border:1px solid #e2e8f0; background:#fff; border-radius:20px; padding:5px 10px;
               font-size:10.5px; color:#475569; display:flex; align-items:center; gap:5px; }
    .fp-chip .fp-box { width:11px; height:11px; border:1.5px solid #cbd5e1; border-radius:3px; display:inline-block; }
    .fp-chip .fp-radio { width:11px; height:11px; border:1.5px solid #cbd5e1; border-radius:50%; display:inline-block; }
    .fp-up { border:1.5px dashed #cddedb; background:#fff; border-radius:14px; padding:16px 10px;
             text-align:center; color:#64748b; font-size:10px; }
    .fp-map { border-radius:14px; overflow:hidden; border:1px solid #e2e8f0; background:#fff; }
    .fp-map .fp-mapbox { height:96px; background:linear-gradient(135deg,#e8f6f7,#d7e7e4); display:flex;
                 align-items:center; justify-content:center; color:#0e4751; font-size:11px; font-weight:600; gap:5px; }
    .fp-map .fp-mapfields { padding:8px 10px; border-top:1px solid #eef2f7; }
    .fp-map .fp-mapfields div { background:#f8fafc; border:1px solid #eef2f7; border-radius:9px; padding:7px 9px;
                     font-size:10.5px; color:#94a3b8; margin-bottom:5px; }
    .fp-map .fp-mapfields div:last-child { margin-bottom:0; }
    .fp-mini { font-size:9px; color:#cbd5e1; margin-top:3px; display:block; }
</style>

<div class="mb-2 d-flex align-items-center" style="gap:8px">
    <span class="badge badge-sm badge-info">Pratinjau</span>
    <small class="text-muted">Perkiraan tampilan di aplikasi mobile. Hanya ilustrasi — input tidak aktif.</small>
</div>

@if (empty($fields))
    <div class="alert alert-warning mb-0">Form ini belum punya field.</div>
@else
<div class="fp-wrap">
    <div class="fp-phone">
        <div class="fp-status">
            <span>09:41</span>
            <span style="display:flex; gap:4px; align-items:center;">
                <span class="material-icons" style="font-size:12px">signal_cellular_alt</span>
                <span class="material-icons" style="font-size:12px">wifi</span>
                <span class="material-icons" style="font-size:12px">battery_full</span>
            </span>
        </div>

        <div class="fp-head">
            <div class="fp-title">{{ $formName }}</div>
            <div class="fp-sub">Lengkapi data pengajuanmu</div>
        </div>

        <div class="fp-body">
            @foreach ($fields as $f)
                @php
                    $type = $f['type'];
                    $opts = $f['options'] ?? [];
                    $val = $f['validation'] ?? [];
                    $cond = $f['conditional'] ?? null;
                @endphp

                @if ($type === 'section')
                    <div class="fp-sec">{{ $f['label'] }}</div>
                    @continue
                @endif

                @if ($type === 'note')
                    <div class="fp-note">{{ $f['label'] }}</div>
                    @continue
                @endif

                <div class="fp-f">
                    <span class="fp-lb">
                        {{ $f['label'] }}@if (!empty($f['is_required']))<span class="fp-req"> *</span>@endif
                        @if ($cond)<span class="fp-cond">jika {{ $cond['field'] }} {{ $cond['operator'] }} "{{ $cond['value'] ?? '' }}"</span>@endif
                    </span>

                    @switch($type)
                        @case('textarea')
                            <div class="fp-in fp-ta">{{ $f['placeholder'] ?: 'Tulis di sini…' }}</div>
                            @break

                        @case('select')
                        @case('multiselect')
                            <div class="fp-in">
                                <span>{{ $f['placeholder'] ?: ($type === 'multiselect' ? 'Pilih satu atau lebih' : 'Pilih salah satu') }}</span>
                                <span class="material-icons" style="font-size:16px; color:#cbd5e1">expand_more</span>
                            </div>
                            <span class="fp-mini">{{ count($opts) }} opsi tersedia</span>
                            @break

                        @case('radio')
                        @case('checkbox_group')
                            <div class="fp-chips">
                                @foreach (array_slice($opts, 0, 7) as $o)
                                    <span class="fp-chip"><span class="{{ $type === 'radio' ? 'fp-radio' : 'fp-box' }}"></span>{{ $o['label'] }}</span>
                                @endforeach
                                @if (count($opts) > 7)<span class="fp-chip">+{{ count($opts) - 7 }} lainnya</span>@endif
                            </div>
                            @break

                        @case('checkbox')
                            <span class="fp-chip"><span class="fp-box"></span>{{ $f['placeholder'] ?: 'Ya' }}</span>
                            @break

                        @case('image')
                        @case('file')
                            <div class="fp-up">
                                <span class="material-icons" style="font-size:20px; color:#94a3b8">
                                    {{ $type === 'image' ? 'add_photo_alternate' : 'upload_file' }}
                                </span>
                                <div>{{ $type === 'image' ? 'Unggah gambar' : 'Unggah dokumen' }}</div>
                                <div style="font-size:9px; color:#94a3b8">
                                    @if (!empty($val['accept'])){{ $val['accept'] }}@endif
                                    @if (!empty($val['max_size_mb'])) · maks {{ $val['max_size_mb'] }}MB @endif
                                    @if (!empty($val['max_files'])) · maks {{ $val['max_files'] }} berkas @endif
                                </div>
                            </div>
                            @break

                        @case('location')
                            <div class="fp-map">
                                <div class="fp-mapbox"><span class="material-icons" style="font-size:17px">place</span> Pilih titik lokasi</div>
                                <div class="fp-mapfields">
                                    <div>Alamat — terisi otomatis</div>
                                    <div>Wilayah — terisi otomatis</div>
                                </div>
                            </div>
                            @break

                        @case('date')
                        @case('time')
                        @case('datetime')
                            <div class="fp-in">
                                <span>{{ $type === 'time' ? 'Pilih jam' : 'Pilih tanggal' }}</span>
                                <span class="material-icons" style="font-size:15px; color:#cbd5e1">
                                    {{ $type === 'time' ? 'schedule' : 'calendar_today' }}
                                </span>
                            </div>
                            @break

                        @case('number')
                            <div class="fp-in"><span>{{ $f['placeholder'] ?: '0' }}</span></div>
                            @break

                        @default
                            <div class="fp-in"><span>{{ $f['placeholder'] ?: 'Ketik di sini…' }}</span></div>
                    @endswitch

                    @if (!empty($f['help_text']))<span class="fp-help">{{ $f['help_text'] }}</span>@endif
                </div>
            @endforeach
        </div>

        <div class="fp-cta"><div>Kirim Pengajuan</div></div>
    </div>
</div>
@endif
