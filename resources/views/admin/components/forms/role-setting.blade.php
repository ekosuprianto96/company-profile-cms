<style>
    /* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 20px;
  float:right;
}

/* Hide default HTML checkbox */
.switch input {display:none;}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 13px;
  width: 13px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input.default:checked + .slider {
  background-color: #2582ec;
}
input.primary:checked + .slider {
  background-color: #2196F3;
}
input.success:checked + .slider {
  background-color: #8bc34a;
}
input.info:checked + .slider {
  background-color: #3de0f5;
}
input.warning:checked + .slider {
  background-color: #FFC107;
}
input.danger:checked + .slider {
  background-color: #f44336;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(20px);
  -ms-transform: translateX(20px);
  transform: translateX(20px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

#list_permission {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    width: 100%;
    gap: 10px;
    max-height: 600px;
    overflow: auto;
}

#list_permission div {
    padding: 8px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-width: 48%;
    background-color: #e0e0e0;
}
</style>
<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
        <div class="row">
            <div class="col-md-12">
                <button type="button" style="display: none" class="btn btn-danger float-end" id="unselectedAll">Batalkan Semua</button>
                <button type="button" class="btn btn-primary me-2 float-end" id="selectedAll">Pilih Semua</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="mt-4" id="list_permission">
                    @foreach (App\Models\Permission::where('an', 1)->get() as $permission)
                        <div class="rounded border mb-3">
                            <span>{{ $permission->name }}</span>
                            <span class="p-0 m-0">
                                <label class="switch m-0 p-0">
                                    <input {{ $permission->hasRole($role->id_role) ? 'checked' : '' }} type="checkbox" value="{{ $permission->id }}" class="default permission_check">
                                    <span class="slider round"></span>
                                </label>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        {{-- <select name="permission" id="permission" class="form-control" multiple="multiple">
            @foreach (App\Models\Permission::where('an', 1)->get() as $permission)
                <option {{ $permission->hasRole($role->id_role) ? 'selected' : '' }} value="{{ $permission->id }}">{{ $permission->name }}</option>
            @endforeach
        </select> --}}
        <div data-error="permission" class="invalid-fedback">
            <span class="text-danger" style="font-size: 0.8em"></span>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonSetting" class="btn btn-primary me-2">Submit</button>
    </div>
</form>

<script>
    $('#permission').select2({
        width: '100%',
        dropdownParent: $('#modalSetting')
    });

    $(document).ready(function() {
        const inputForms = $('input, select');
        $.each(inputForms, function(index, value) {
            if(value.tagName === 'INPUT') {
                $(value).keyup(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }else if(value.tagName === 'SELECT') {
                $(value).change(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }
        });

        $('.permission_check').change(function() {
            const checked = $('.permission_check:checked').length;
            if(checked > 0) {
                $('#unselectedAll').show();
            }else {
                $('#unselectedAll').hide();
            }
        })

        $('#selectedAll').click(function() {
            $('.permission_check').each(function() {
                $(this).prop('checked', true);
            });

            const checked = $('.permission_check:checked').length;
            if(checked > 0) {
                $('#unselectedAll').show();
            }else {
                $('#unselectedAll').hide();
            }
        });

        $('#unselectedAll').click(function() {
            $('.permission_check').each(function() {
                $(this).prop('checked', false);
            });

            setTimeout(() => {
                const checked = $('.permission_check:checked').length;
                if(checked > 0) {
                    $('#unselectedAll').show();
                }else {
                    $('#unselectedAll').hide();
                }
            }, 500);
        });

        $('#buttonSetting').click(function() {
            const values = [];
            const checked = $('.permission_check:checked').each(function() {
                values.push($(this).val());
            });

            $.post('{{ route('admin.roles.attach') }}', {
                permissions: values,
                id_role: '{{ $role->id_role }}',
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                $('#modalSetting').modal('hide');
                $table.ajax.reload();
                $.toast({
                    heading: 'Sukses!',
                    text: response.message,
                    showHideTransition: 'slide',
                    position: 'top-right',
                    icon: 'success'
                });
            }).fail(function(error) {
                const response = error.responseJSON;

                if(response) {
                    const { errors } = response;
                    
                    if(errors) {
                        $.parseErros(errors);
                    }

                    $.toast({
                        heading: 'Warning',
                        text: response.message,
                        showHideTransition: 'slide',
                        position: 'top-right',
                        icon: 'warning'
                    });
                }
            });
        });
    })
</script>