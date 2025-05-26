<style>
    .list_{{ $name }}_selection li:hover {
        cursor: grab;
    }
</style>

<div class="form-group">
    <label for="{{ $id }}" class="mb-2">{{ $label }}</label>
    <div class="row">
        <div class="col-md-10 pe-0">
            @php($disabled = array_values(array_map(fn($item) => $item['id'], $value)))
            <select name="{{ $name }}" id="{{ $id }}" class="form-control">
                <option value="">{{ $placeholder }}</option>
                @foreach ($collections as $key => $collect)
                    <option
                        @disabled(isset($disabled) && in_array($collect[$option['value'] ?? 'id'], $disabled))
                        value="{{ trim($collect[$option['value'] ?? 'id']) }}"
                    >
                        {{ trim($collect[$option['text'] ?? 'id']) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <div class="w-100 h-100 d-flex justify-content-center align-items-center">
                <button type="button" id="btn_add__list_{{ $name }}" class="btn btn-primary btn-sm">
                    <i class="ri-add-line"></i>
                </button>
            </div>
        </div>
    </div>
    <div data-error="{{ $name }}" class="invalid-fedback">
      <span class="text-danger" style="font-size: 0.8em"></span>
    </div>

    {{-- list --}}
    @if(isset($value))
        <div class="mt-2">
            <label for="" class="mb-2">List</label>
            <ul class="m-0 p-0 list_{{ $name }}_selection">
                @foreach ($value as $key => $item)
                    <li class="p-3 border rounded mb-2" data-id="{{ $item['id'] ?? '' }}">
                        <input type="hidden" data-type="id" name="{{ $name }}[{{ $key+1 }}][id]" value="{{ $item['id'] ?? '' }}">
                        <input type="hidden" data-type="text" name="{{ $name }}[{{ $key+1 }}][text]" value="{{ $item['text'] ?? '' }}">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <span style="max-width: 80%" class="text-truncate d-block">
                                <i class="ri-draggable"></i> {{ $item['text'] ?? '-' }}
                            </span>
                            <span class="float-right">
                                <a href="javascript:void(0)" class="text-danger" onclick="removeListSelection{{ $name }}({{ $item['id'] }}, {{ $key+1 }})">Hapus</a>
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <script>
        $(document).ready(function() {
            $('#{{ $id }}').select2({
                width: '100%',
                dropdownParent: $('#{{ $id }}').closest('.modal')
            });

            $('#btn_add__list_{{ $name }}').click(function() {
                let val = $('#{{ $id }}').val();
                let text = $('#{{ $id }} option:selected').text();
                let id = $('#{{ $id }} option:selected').val();

                if(!$('.list_{{ $name }}_selection').length) {
                    $.toast({
                        heading: 'Warning',
                        text: 'Attribute value pada form tidak tersedia.',
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });

                    return;
                }

                if (val) {
                    addListSelection{{ $name }}(id, text);
                    $('#{{ $id }}')
                    .val('')
                    .trigger('change');
                }
            });

            $(".list_{{ $name }}_selection").sortable({
                placeholder: "ui-state-highlight", // Efek placeholder saat dragging
                update: function (event, ui) {
                    let orderedIDs = [];
                    $(".list_{{ $name }}_selection li").each(function () {
                        orderedIDs.push($(this).attr("data-id"));
                    });

                    console.log("Urutan Baru:", orderedIDs);
                    // Kirim ke backend jika perlu
                    // $.post('/update-order', { order: orderedIDs });
                }
            });
        });

        @isset($value)
            function addListSelection{{ $name }}(id, text) {
                let currentLength = $('.list_{{ $name }}_selection li').length;
                let html = ` 
                    <li class="p-3 border rounded mb-2" data-id="${id}">
                        <input type="hidden" data-type="id" name="{{ $name }}[${currentLength+1}][id]" value="${id}">
                        <input type="hidden" data-type="text" name="{{ $name }}[${currentLength+1}][text]" value="${text}">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <span style="max-width: 80%" class="text-truncate d-block">
                                <i class="ri-draggable"></i> ${text}
                            </span>
                            <span class="float-right">
                                <a href="javascript:void(0)" class="text-danger" onclick="removeListSelection{{ $name }}(${id}, ${currentLength+1})">Hapus</a>
                            </span>
                        </div>
                    </li>
                `;
                disabledOption{{ $name }}(id);
                $('.list_{{ $name }}_selection').append(html);
            }

            function disabledOption{{ $name }}(id) {
                $('#{{ $id }} option[value="' + id + '"]').attr('disabled', true);
            }

            function enableOption{{ $name }}(id) {
                $('#{{ $id }} option[value="' + id + '"]').attr('disabled', false);
            }

            function removeListSelection{{ $name }}(id, key) {
                let currentLength = $('.list_{{ $name }}_selection li').length;
                $('.list_{{ $name }}_selection')
                .find(`[data-id=${id}]`)
                .remove();
                
                $('.list_{{ $name }}_selection')
                .find('li')
                .each(function(index, value) {
                    $(value).find('[data-type=id]').attr('name', '{{ $name }}['+(index+1)+'][id]');
                    $(value).find('[data-type=text]').attr('name', '{{ $name }}['+(index+1)+'][text]');
                    // $(value).find('[name="{{ $name }}['+key+'][text]]"').attr('name', '{{ $name }}['+index+'][text]');
                });
                
                enableOption{{ $name }}(id);
            }
        @endisset
    </script>
</div>