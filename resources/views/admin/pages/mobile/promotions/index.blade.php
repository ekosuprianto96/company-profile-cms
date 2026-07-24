@extends('admin.layouts.main')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-start" style="gap: 16px;">
                <div>
                    <h4 class="card-title mb-1">Promosi</h4>
                    <p class="text-muted mb-0">Banner beranda aplikasi — slider utama di paling atas, atau strip pada section promosi. Saat diklik, user dibawa ke halaman detail.</p>
                </div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="javascript:void(0)" id="tambahPromotion" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Tambah Promosi</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table w-100" id="tablePromotions">
                        <thead>
                            <tr>
                                <th>Promosi</th>
                                <th>Penempatan</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPromotionEdit" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
    <div class="modal fade" id="modalPromotionCreate" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" data-bind-title></h5><button type="button" class="btn-close-create btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" data-bind-content></div>
    </div></div></div>
</div>

<script>
    $(document).ready(function() {
        window.$promotionsTable = $('#tablePromotions').DataTable({
            processing: true, serverSide: true, pageLength: 25,
            ajax: { method: 'get', url: '{{ route("admin.mobile.promotions.data") }}' },
            columns: [
                {data:'promotion', name:'promotion'},
                {data:'placement', name:'placement', orderable:false, searchable:false},
                {data:'period', name:'period', orderable:false, searchable:false},
                {data:'status', name:'status', orderable:false, searchable:false},
                {data:'action', name:'action', orderable:false, searchable:false}
            ]
        });

        window.$promotionsTable.on('draw', function() {
            const modalCreate = $.modalCustom({ trigger:'#tambahPromotion', modal:'#modalPromotionCreate', options:{ title:'Tambah Promosi', backdrop:'static', keyboard:false, focus:false, show:false } });
            const modalEdit = $.modalCustom({ trigger:'.editPromotion', modal:'#modalPromotionEdit', options:{ title:'Edit Promosi', bind:'promotion', backdrop:'static', keyboard:false, focus:false, show:false } });

            modalCreate.onShow(function() {
                $('#tambahPromotion').spinner('show');
                $.get('{{ route("admin.mobile.promotions.forms") }}', { view:'promotion-create' })
                    .done((r)=>modalCreate.render(r))
                    .fail((e)=>modalCreate.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`))
                    .always(()=>$('#tambahPromotion').spinner('hide'));
            });
            modalEdit.onShow(function(id) {
                $(`[data-bind-promotion=${id}]`).spinner();
                $.get('{{ route("admin.mobile.promotions.forms") }}', { view:'promotion-edit', id_promotion:id })
                    .done((r)=>modalEdit.render(r))
                    .fail((e)=>modalEdit.render(`<span class="alert my-3 d-block alert-danger">${(e.responseJSON||{}).message||'Gagal memuat form.'}</span>`))
                    .always(()=>$(`[data-bind-promotion=${id}]`).spinner('hide'));
            });
        });
    });

    function deletePromotion(id_promotion) {
        Swal.fire({ title:'Kamu yakin?', text:'Promosi ini akan dihapus.', icon:'warning', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', showConfirmButton:true, showCancelButton:true, customClass:{ cancelButton:'bg-danger', confirmButton:'bg-primary' } }).then((result)=>{
            if (!result.isConfirmed) return;
            $.post('{{ route("admin.mobile.promotions.destroy") }}', { id_promotion, _token:'{{ csrf_token() }}' })
                .done((r)=>{ window.$promotionsTable.ajax.reload(); $.toast({ heading:'Sukses', text:r.message, showHideTransition:'plain', position:'top-right', icon:'success' }); })
                .fail((e)=>$.toast({ heading:'Warning', text:(e.responseJSON||{}).message||'Terjadi kesalahan.', showHideTransition:'slide', position:'top-right', icon:'warning' }));
        });
    }
</script>
@endsection
