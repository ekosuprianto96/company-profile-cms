@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Penilaian Produk</h4>
                    <p class="text-muted mb-0">Ulasan &amp; bintang dari pembeli setelah pesanan selesai. Saring per produk untuk melihat penilaian produk tertentu.</p>
                </div>
                <a href="{{ route('admin.mobile.products') }}" class="btn btn-light btn-sm"><i class="ri-arrow-left-line me-1"></i> Produk</a>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="mb-3" style="max-width: 360px;">
                    <label class="form-label small text-muted mb-1">Saring berdasarkan produk</label>
                    <select id="filterProduct" class="form-control">
                        <option value="">Semua produk</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} — {{ number_format($p->rating, 1) }}★ ({{ $p->review_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table w-100" id="tableReviews">
                        <thead><tr>
                            <th>Produk &amp; Pembeli</th>
                            <th>Rating</th>
                            <th>Komentar</th>
                            <th>Tanggal</th>
                            <th class="text-center">Action</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalReviewDetail" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title>Detail Penilaian</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
</div>

<script>
    $(document).ready(function() {
        window.$reviewsTable = $('#tableReviews').DataTable({
            processing: true, serverSide: true, pageLength: 25,
            ajax: {
                method: 'get',
                url: '{{ route("admin.mobile.reviews.data") }}',
                data: function(d) { d.product_id = $('#filterProduct').val(); }
            },
            columns: [
                { data: 'product', name: 'product' },
                { data: 'rating', name: 'rating', orderable: false, searchable: false },
                { data: 'comment', name: 'comment', orderable: false, searchable: false },
                { data: 'date', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        $('#filterProduct').on('change', function() { window.$reviewsTable.ajax.reload(); });

        window.$reviewsTable.on('draw', function() {
            const md = $.modalCustom({ trigger: '.detailReview', modal: '#modalReviewDetail', options: { title: 'Detail Penilaian', bind: 'review', backdrop: 'static', keyboard: false, focus: false, show: false } });
            md.onShow((id) => {
                $(`[data-bind-review=${id}]`).spinner();
                $.get('{{ route("admin.mobile.reviews.forms") }}', { view: 'review-detail', id_review: id })
                    .done((r) => md.render(r))
                    .fail((e) => md.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat detail.'}</span>`))
                    .always(() => $(`[data-bind-review=${id}]`).spinner('hide'));
            });
        });
    });
</script>
@endsection
