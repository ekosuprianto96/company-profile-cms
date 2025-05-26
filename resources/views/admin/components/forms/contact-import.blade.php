<form class="forms-sample" id="form_upload_file">
    @csrf
    <div class="row" id="wrapper_upload_file">
        <div class="col-md-12 mb-3">
            <div class="rounded d-flex justify-content-center align-items-center" style="position: relative;height: 200px;width: 100%;background-color: rgb(233, 233, 233);">
                <input type="file" id="input_file" name="input_file" accept=".xlsx, .xls, .csv" class="position-absolute" style="opacity: 0;top:0;right:0;bottom:0;left:0">
                <div class="w-100 d-flex align-items-center justify-content-center flex-column">
                    <i class="ri-file-excel-2-line" style="font-size: 2em;"></i>
                    <span class="d-block mt-3" style="font-size: 0.8em">Click Atau Drag File Disini</span>
                    <span class="d-block text-danger" style="font-size: 0.6em">File yang diizinkan hanya (.xlsx, .xls, .csv)</span>
                </div>
                <div data-error="input_file" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" disabled id="buttonImport" class="btn btn-primary me-2">Submit</button>
    </div>
</form>

<script>
    $(document).ready(function() {

        const inputForms = $('input, select, file');
        $.each(inputForms, function(index, value) {
            if(value.tagName === 'INPUT' || value.tagName === 'FILE') {
                $(value).keyup(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }else if(value.tagName === 'SELECT') {
                $(value).change(function() {
                    $(this).removeClass('is-invalid').parent().find(`[data-error=${$(this).attr('name')}]`).find('span').text('')
                })
            }
        });

        $('#input_file').change(function() {
            const file = this.files[0];

            if(file) {
                prosesUploadFile(file)
                .then(response => {
                    $('#wrapper_upload_file').html(response);
                    $('#buttonImport').prop('disabled', false);
                })
                .catch(error => {
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
            }
        });

        $('#buttonImport').click(function() {
            const data = $('#form_upload_file').serialize();
            $.post('{{ route('admin.email.contact.mapped') }}', data)
            .done(function(response) {

                $('#modalImport').modal('hide');

                $(document).trigger('contact-created', response);

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
            })
        });
    });

    function prosesUploadFile(file) {
        const form = new FormData();
        form.append('file', file);
        form.append('_token', '{{ csrf_token() }}');

        return new Promise((resolve, reject) => {
            $.ajax({
                url: '{{ route('admin.email.contact.read_file') }}',
                type: 'POST',
                data: form,
                processData: false,
                contentType: false
            }).done(resolve)
            .fail(reject);
        });
    }
</script>