
<style>
    .ck-editor__editable {min-height: 250px;}
</style>
<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="form-group">
        <a href="javascript:void(0)" id="add_email" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Add Email</a>
    </div>
    <div id="emails">
        @if(count($informasi->value->emails) > 0)
            @foreach($informasi->value->emails as $key => $value)
                <div class="form-group">
                    <label for="emails">Email {{ $key + 1 }}</label>
                    <div class="row">
                        <div class="col-md-11">
                            <input name="emails" value="{{ $value }}" type="text" class="form-control" id="emails_{{ $key }}" placeholder="Email">
                        </div>
                        <div class="col-md-1">
                            <a href="javascript:void(0)" data-id="{{ $key }}" class="btn btn-sm btn-danger remove-email"><i class="ri-delete-bin-line"></i></a>
                        </div>
                    </div>
                    <div data-error="emails" class="invalid-fedback">
                        <span class="text-danger" style="font-size: 0.8em"></span>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-danger" id="alert-email" role="alert">
                Belum ada email Yang Ditambahkan
            </div>
        @endif
    </div>

    <div class="form-group">
        <a href="javascript:void(0)" id="add_phone" class="btn btn-sm btn-primary"><i class="ri-add-line"></i> Add Phone</a>
    </div>
    <div id="phones">
        @if(count($informasi->value->phones) > 0)
            @foreach($informasi->value->phones as $key => $value)
                <div class="form-group">
                    <label for="phones">Phone {{ $key + 1 }}</label>
                    <div class="row">
                        <div class="col-md-11">
                            <input name="phones" value="{{ $value }}" type="text" class="form-control" id="phones_{{ $key }}" placeholder="Phone">
                        </div>
                        <div class="col-md-1">
                            <a href="javascript:void(0)" data-id="{{ $key }}" class="btn btn-sm btn-danger remove-phone"><i class="ri-delete-bin-line"></i></a>
                        </div>
                    </div>
                    <div data-error="phones" class="invalid-fedback">
                        <span class="text-danger" style="font-size: 0.8em"></span>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-danger" id="alert-phone" role="alert">
                Belum ada Telepon Yang Ditambahkan
            </div>
        @endif
    </div>
    
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonUpdate" class="btn btn-primary me-2">Submit</button>
    </div>
</form>

<script>
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

        $('#add_email').click(function() {
            $('#alert-email').remove();
            const emails = $('#emails').children().length + 1;
            $('#emails').append(`
                <div class="form-group">
                    <label for="emails">Email ${emails}</label>
                    <div class="row">
                        <div class="col-md-11">
                            <input name="emails" value="" type="text" class="form-control" id="emails_${emails}" placeholder="Email">
                        </div>
                        <div class="col-md-1">
                            <a href="javascript:void(0)" data-id="${emails}" class="btn btn-sm btn-danger remove-email"><i class="ri-delete-bin-line"></i></a>
                        </div>
                    </div>
                    <div data-error="emails" class="invalid-fedback">
                        <span class="text-danger" style="font-size: 0.8em"></span>
                    </div>
                </div>
            `);
        });

        $('#add_phone').click(function() {
            $('#alert-phone').remove();
            const phones = $('#phones').children().length + 1;
            $('#phones').append(`
                <div class="form-group">
                    <label for="phones">Phone ${phones}</label>
                    <div class="row">
                        <div class="col-md-11">
                            <input name="phones" value="" type="text" class="form-control" id="phones_${phones}" placeholder="Phone">
                        </div>
                        <div class="col-md-1">
                            <a href="javascript:void(0)" data-id="${phones}" class="btn btn-sm btn-danger remove-phone"><i class="ri-delete-bin-line"></i></a>
                        </div>
                    </div>
                    <div data-error="phones" class="invalid-fedback">
                        <span class="text-danger" style="font-size: 0.8em"></span>
                    </div>
                </div>
            `);
        });

        $(document).on('click', '.remove-email', function() {
            $(this)
            .closest('.form-group')
            .remove();

            $('#emails')
            .find('label')
            .each(function(index, value) {
                $(value).text(`Email ${index + 1}`);
            });

            $('#emails')
            .find('input')
            .each(function(index, value) {
                $(value).attr('id', `emails_${index + 1}`);
            });

            if($('#emails').children().length === 0) {
                $('#emails').append(`
                    <div class="alert alert-danger" id="alert-email" role="alert">
                        Belum ada Email Yang Ditambahkan
                    </div>
                `);
            }
        });

        $(document).on('click', '.remove-phone', function() {
            const id = $(this).data('id');

            $(this)
            .closest('.form-group')
            .remove();

            $('#phones')
            .find('label')
            .each(function(index, value) {
                $(value).text(`Phone ${index + 1}`);
            });

            $('#phones')
            .find('input')
            .each(function(index, value) {
                $(value).attr('id', `phones_${index + 1}`);
            });

            if($('#phones').children().length === 0) {
                $('#phones').append(`
                    <div class="alert alert-danger" id="alert-phone" role="alert">
                        Belum ada Telepon Yang Ditambahkan
                    </div>
                `);
            }
        });

        $('#buttonUpdate').click(function() {
            const emails = [];
            const phones = [];
            $('[name=emails]').map(function() {
                return emails.push($(this).val());
            });

            $('[name=phones]').map(function() {
                return phones.push($(this).val());
            });

            $.post('{{ route('admin.informasi.update', $informasi->id) }}', {
                emails: emails,
                phones: phones,
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                $('#modalUpdate').modal('hide');
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
    })
</script>