<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('settings.value.app_name') }} </title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/typicons/typicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/assets/js/select.dataTables.min.css') }}">
    <!-- End plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/select2/select2.min.css') }}">
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/css/style.css') }}">
    <!-- endinject -->
    
    {{-- favicon --}}
    <link rel="icon" href="{{ image_url('informasi', config('settings.value.favicon.file')) }}" type="image/x-icon">
    
    {{-- Remixicon --}}
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet" />
    
    <script src="{{ asset('assets/admin/assets/vendors/js/vendor.bundle.base.js') }}"></script>

    <!-- jQuery dan jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css">
    
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css"> --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> --}}
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.js"></script>

    <script src="{{ asset('assets/admin/assets/vendors/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/vendors/sweetalert/sweetalert2.min.js') }}"></script>
    <style>
        @media (min-width: 800px) {
            div.modal-dialog.modal-lg {
                width: 1000px;
            }
        }
    </style>

    <style>
      :root {
          --ck-z-default: 100;
          --ck-z-panel: calc( var(--ck-z-default) + 999 );
      }
      .ck-editor__editable {min-height: 250px;}
      .ck.ck-balloon-panel {
          z-index: 1200 !important; /* Lebih tinggi dari modal Bootstrap */
      }
      .ck.ck-tooltip {
          z-index: 1200 !important;
      }
      body {
          /* We need to assaign this CSS Custom property to the body instead of :root, because of CSS Specificity and codepen stylesheet placement before loaded CKE5 content. */
          --ck-z-default: 100;
          --ck-z-modal: calc( var(--ck-z-default) + 999 );
      }

        .ck-shortcode {
            background-color: #eee;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>

    {{-- Plugin Custom --}}
    <script src="{{ asset('assets/admin/assets/js/main.js') }}"></script>
    
    {{-- Toast --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/assets/vendors/jquery.toast.min.css') }}">
    <script src="{{ asset('assets/admin/assets/vendors/jquery.toast.min.js') }}"></script>

    @stack('codemirror')

    <script src="{{ asset('assets/admin/assets/js/ckeditor5.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/texteditor.js') }}"></script>
</head>
<body>
    
    <div class="container">
        <div class="mt-4">
            <textarea name="" id="editor" cols="30" rows="10"></textarea>
        </div>
    </div>

    <script>
        var editorInstance;
        ClassicEditor
        .create( document.querySelector( '#editor'), {
            extraPlugins: [
                function(editor) {
                    createCustomUploadAdapterPlugin({
                        url: '{{ route('admin.ckeditor.upload') }}',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })(editor);

                    new ImageRemovePlugin(editor);
                },
                function(editor) {
                    new Shortcode(editor);
                }
            ],
            removePlugins: ['Markdown'], // Matikan plugin Markdown
        })
        .then( editor => {
            editorInstance = editor;

            editor.on('image:removed', (event, {imageRemoved}) => {
                fetch('{{ route('admin.ckeditor.cleanup') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        images: imageRemoved
                    })
                });
            });

            editor.model.document.on('change:data', () => {
                const content = editorInstance.getData();
                const regex = /\[(\w+)\]/g;
                let match;

                while ((match = regex.exec(content)) !== null) {
                    const shortcode = match[1];
                    editorInstance.execute('insertShortcode', { value: shortcode });
                    break; // hanya ubah satu per cycle agar tidak infinite
                }
            });
        });
    </script>

    <script src="{{ asset('assets/admin/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="{{ asset('assets/admin/assets/vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/vendors/progressbar.js/progressbar.min.js') }}"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('assets/admin/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/admin/assets/js/hoverable-collapse.js') }}"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="{{ asset('assets/admin/assets/js/jquery.cookie.js') }}" type="text/javascript"></script>
    {{-- <script src="{{ asset('assets/admin/assets/js/dashboard.js') }}"></script> --}}
    <!-- <script src="assets/js/Chart.roundedBarCharts.js"></script> -->
    <!-- End custom js for this page-->
</body>
</html>