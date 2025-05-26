<style>
    .ck-editor__editable {min-height: 250px;}
</style>
<form class="forms-sample" action="" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="name">Nama</label>
                <input name="name" type="text" class="form-control" id="name" placeholder="Nama...">
                <div data-error="name" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email</label>
                <input name="email" type="text" class="form-control" id="email" placeholder="Email...">
                <div data-error="email" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="phone">Phone</label>
                <input name="phone" type="text" class="form-control" id="phone" placeholder="Phone...">
                <div data-error="phone" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" class="form-control" style="height: 100px"></textarea>
                <div data-error="address" class="invalid-fedback">
                    <span class="text-danger" style="font-size: 0.8em"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" id="buttonAddModule" class="btn btn-primary me-2">Submit</button>
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

        $('#buttonAddModule').click(function() {
            $.post('{{ route('admin.email.contact.store') }}', {
                name: $('[name=name]').val(),
                email: $('[name=email]').val(),
                phone: $('[name=phone]').val(),
                address: $('[name=address]').val(),
                _token: '{{ csrf_token() }}'
            }).done(function(response) {

                $('#modalAdd').modal('hide');

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
    })
</script>