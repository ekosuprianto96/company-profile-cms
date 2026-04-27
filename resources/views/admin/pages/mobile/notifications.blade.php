@extends('admin.layouts.main')

@section('content')
@php
    $filters = $filters ?? ['type' => '', 'read_status' => '', 'search' => ''];
@endphp

<div class="row">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Notifikasi Sistem</h4>
                    <p class="text-muted mb-0">Pantau notifikasi yang sudah dikirim ke user mobile lewat tabel yang lebih mudah dipindai.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <div class="badge badge-sm badge-info" id="notifications-unread-badge">{{ $unreadCount }} belum dibaca</div>
                    <a href="{{ route('admin.mobile.notifications.create') }}" class="btn btn-primary btn-sm">
                        <i class="ri-send-plane-2-line me-1"></i> Kirim Notifikasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form
                    method="GET"
                    action="{{ route('admin.mobile.notifications') }}"
                    class="row g-3 align-items-end mb-4"
                    id="notifications-filter-form">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Cari</label>
                        <input
                            type="search"
                            name="search"
                            value="{{ $filters['search'] }}"
                            class="form-control"
                            placeholder="Cari judul, pesan, atau tautan">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Tipe</label>
                        <select name="type" class="form-select">
                            <option value="">Semua Tipe</option>
                            <option value="promo" @selected($filters['type'] === 'promo')>Promo</option>
                            <option value="informasi" @selected($filters['type'] === 'informasi')>Informasi</option>
                            <option value="konfirmasi" @selected($filters['type'] === 'konfirmasi')>Konfirmasi</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label">Status Baca</label>
                        <select name="read_status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="unread" @selected($filters['read_status'] === 'unread')>Belum Dibaca</option>
                            <option value="read" @selected($filters['read_status'] === 'read')>Sudah Dibaca</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('admin.mobile.notifications') }}" class="btn btn-light w-100">Reset</a>
                    </div>
                </form>

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap: 12px;">
                    <div class="text-muted small" id="notifications-summary">
                        Menampilkan {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} dari {{ $notifications->total() ?? 0 }} notifikasi
                    </div>
                    <div class="text-muted small">
                        Per halaman
                        <span class="badge badge-light">{{ $perPage ?? 10 }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th style="min-width: 180px;">Judul</th>
                                <th style="min-width: 220px;">Pesan</th>
                                <th style="min-width: 120px;">Tipe</th>
                                <th style="min-width: 120px;">Status</th>
                                <th style="min-width: 160px;">Dibuat</th>
                                <th style="min-width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        @include('admin.pages.mobile.notifications-partials.table', ['notifications' => $notifications])
                    </table>
                </div>

                <div class="mt-4" id="notifications-pagination">
                    @include('admin.pages.mobile.notifications-partials.pagination', ['notifications' => $notifications])
                </div>
            </div>
        </div>
    </div>
</div>

@push('admin-scripts')
<script>
    (function () {
        const form = document.getElementById('notifications-filter-form');
        let tableBody = document.querySelector('#notifications-table-body');
        const pagination = document.getElementById('notifications-pagination');
        const summary = document.getElementById('notifications-summary');
        const unreadBadge = document.getElementById('notifications-unread-badge');

        if (!form || !tableBody || !pagination || !summary) {
            return;
        }

        function setLoading(isLoading) {
            form.querySelectorAll('button, input, select, a').forEach((el) => {
                if (el instanceof HTMLButtonElement || el instanceof HTMLInputElement || el instanceof HTMLSelectElement || el instanceof HTMLAnchorElement) {
                    el.disabled = isLoading && !(el instanceof HTMLAnchorElement);
                    if (el instanceof HTMLAnchorElement) {
                        el.style.pointerEvents = isLoading ? 'none' : '';
                        el.style.opacity = isLoading ? '0.7' : '';
                    }
                }
            });

            const currentTableShell = document.querySelector('[data-notifications-table-shell]');

            if (currentTableShell) {
                currentTableShell.style.opacity = isLoading ? '0.65' : '1';
            }
        }

        function buildUrl(pageUrl) {
            if (pageUrl) {
                return pageUrl;
            }

            const url = new URL(form.action, window.location.origin);
            const params = new URLSearchParams(new FormData(form));
            params.delete('page');
            url.search = params.toString();
            return url.toString();
        }

        async function loadNotifications(pageUrl = null) {
            const url = buildUrl(pageUrl);
            setLoading(true);

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok || !data.status) {
                    throw new Error(data.message || 'Gagal memuat notifikasi.');
                }

                tableBody.outerHTML = data.table;
                tableBody = document.querySelector('#notifications-table-body');
                pagination.innerHTML = data.pagination;

                const from = data.summary?.from ?? 0;
                const to = data.summary?.to ?? 0;
                const total = data.summary?.total ?? 0;
                summary.textContent = `Menampilkan ${from} - ${to} dari ${total} notifikasi`;

                if (unreadBadge && typeof data.unread_count === 'number') {
                    unreadBadge.textContent = `${data.unread_count} belum dibaca`;
                }

                bindPaginationLinks();
            } catch (error) {
                console.warn('Gagal memuat notifikasi admin:', error);
            } finally {
                setLoading(false);
            }
        }

        function bindPaginationLinks() {
            pagination.querySelectorAll('a[href]').forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    loadNotifications(link.href);
                });
            });
        }

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            loadNotifications();
        });

        form.querySelectorAll('select, input').forEach((input) => {
            input.addEventListener('change', () => {
                void loadNotifications();
            });
        });

        bindPaginationLinks();
    })();
</script>
@endpush
@endsection
