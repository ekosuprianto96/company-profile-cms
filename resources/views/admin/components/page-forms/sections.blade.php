
<div class="card" id="{{ $id }}">
    <div class="card-body">
        <h4 class="card-title">Pengaturan Section</h4>
        <ul class="mt-4">
            @foreach($config['sections'] as $key => $value)
                <li class="border position-relative d-flex align-items-center px-3 py-3 rounded mb-3">
                    <div class="d-flex flex-column w-50">
                        <span style="font-size: 1.3em;font-weight: bold;"><i class="ri-draggable"></i> {{ $value['title'] }}</span>
                    </div>
                    <div class="position-absolute d-flex align-items-center justify-content-end" style="right: 10px">
                        @if(isset($value['action']))
                            @if($value['action']['type'] == 'redirect')
                                <a href="{{ url($value['action']['link']) }}" title="Edit" class="btn btn-rounded btn-sm">
                                    <i class="ri-pencil-line"></i>
                                </a>
                            @elseif($value['action']['type'] == 'modal')
                                <button 
                                    class="btn btn-rounded btn-sm btn__section" 
                                    data-bind-section="{{ $value['id'] ?? '' }}"
                                    data-bind-page="{{ $config['id'] ?? '' }}"
                                    id="btn_modal_{{ $value['id'] ?? '' }}"
                                >
                                    <i class="ri-pencil-line"></i>
                                </button>
                            @elseif($value['action']['type'] == 'modal-with-list')
                                <button 
                                    class="btn btn-rounded btn-sm btn__section" 
                                    data-bind-section="{{ $value['id'] ?? '' }}"
                                    data-bind-page="{{ $config['id'] ?? '' }}"
                                    id="btn_modal_{{ $value['id'] ?? '' }}"
                                >
                                    <i class="ri-pencil-line"></i>
                                </button>
                            @endif
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
   
    <div class="modal fade" id="modalUpdateSection" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" data-bind-title></h5>
              <button type="button" class="btn-close-edit btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" data-bind-content>
            </div>
          </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const modalEdit = $.modalCustom({
                trigger: '.btn__section',
                modal: '#modalUpdateSection',
                options: {
                    title: 'Edit Section',
                    bind: 'section'
                }
            });

            modalEdit.onShow(function(id) {
                const $this = $(`[data-bind-section=${id}]`);
                $this.spinner();

                $.get('{{ route("admin.sections.forms") }}', {
                    view: 'section-edit',
                    id_section: id,
                    id_page: $this.data('bind-page')
                }).done(function(response) {
                    modalEdit.render(response);
                }).fail(function(error) {
                    const { message = null } = error?.responseJSON || {};
                    $.toast({
                        heading: 'Warning',
                        text: message || error?.message || error,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                }).always(function() {
                    $this.spinner('hide');
                });
            })
        });
    </script>
</div>