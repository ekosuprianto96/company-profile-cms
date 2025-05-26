(function($) {
    
    $.fn.spinner = function(status = 'show', opts = {}) {
        const { size = 'sm' } = opts;
        const spinnerHtml = `<div class="spinner-border spinner-border-${size}" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>`;
    
        return this.each(function() {
            const $this = $(this);
            if (status === 'show') {
                if (!$this.data('original-html')) {
                    $this.data('original-html', $this.html());
                }
                $this.html(spinnerHtml);
            } else if (status === 'hide') {
                if ($this.data('original-html')) {
                    $this.html($this.data('original-html'));
                    $this.removeData('original-html');
                }
            }
        });
    };

    const customTab = function({ tabs, targets, defaulShow }) {
        const settings = $.extend({
            tabs: tabs,
            targets: targets,
            defaulShow: defaulShow || 0
        });

        showDefaultTab(settings.defaulShow);

        $.each(settings.tabs, function(index, value) {
            $(`[data-tabs=${value}]`).click(function() {
                const target = $(this).data('tabs-target');
                if (target) {
                    for (let i = 0; i < settings.targets.length; i++) {
                        $(settings.targets[i]).hide();
                    }
                    $(target).show();

                }

                for (let i = 0; i < settings.tabs.length; i++) {
                    $(`[data-tabs=${tabs[i]}]`).removeClass('active');
                }

                $(this).addClass('active');
            });
        });

        function showDefaultTab(defaultIndex) {
            for (let i = 0; i < settings.targets.length; i++) {
                if (i === defaultIndex) {
                    $(settings.targets[i]).show();
                } else {
                    $(settings.targets[i]).hide();
                }
            }

            for (let i = 0; i < settings.tabs.length; i++) {
                $(`[data-tabs=${tabs[i]}]`).removeClass('active');
            }
            $(`[data-tabs=${tabs[defaultIndex]}]`).addClass('active');
        }
    };

    const parseErros = function(errors) {
        $.each(Object.keys(errors), function(index, value) {
            $(`[data-error=${value}]`).find('span')?.text(errors[value].join(', '))
            $(`[name=${value}]`).addClass('is-invalid').focus();
        });
    }

    const modalCustom = function({trigger, modal, options = {}}) {
        let dataBind = null;
        $(modal).modal(options);
        if(trigger.startsWith('.')) {
            $.each($(trigger), function(index, value) {
                $(value).click(function(event) {
                    event.preventDefault();
                    if(options?.bind) {
                        dataBind = $(value).data(`bind-${options.bind}`);
                    }
                    if(options.title) $(modal).find('[data-bind-title').text(options.title);
                    $(modal).modal('show');
                });
            })
        }else {
            $(trigger).click(function(event) {
                event.preventDefault();
                dataBind = $(event).attr(`data-bind-${options.bind}`);
                if(options.title) $(modal).find('[data-bind-title').text(options.title);
                $(modal).modal('show');
            });
        }

        $(modal).on('hide.bs.modal', function() {
            render('');
        });

        $(modal).off('show.bs.modal');

        function onClose(callback) {
            $(modal).on('hide.bs.modal', function() {
                callback(dataBind);
            });
        }

        function close(callback = null) {
            $(modal).modal('hide');
            if(callback) callback(dataBind);
        }

        function show(callback = null) {
            $(modal).modal('show');
            if(callback) callback(dataBind);
        }

        function render(html) {
            $(modal).find('[data-bind-content]').html(html);
        }

        function onShow(callback) {
            $(modal).on('show.bs.modal', function() {
                callback(dataBind)
            });
        }

        return {onClose, onShow, render, close, show};
    }

    $.fn.customTable = function(opts) {
        const methods = [];
    
        this.each(function() {
            const defaultOptions = {
                data: [],
                columns: [],
                footerCallback: null,
                textEmptyData: 'Tidak ada data'
            }
            
            const options = mergeOptions();
            const $table = $(this);
    
            // functions
            function mergeOptions() {
                return {...defaultOptions, ...opts};
            }
    
            function createColumn(data, index) {
                let td = '';
                options.columns.forEach(column => {
                    if (!column?.styles) {
                        column.styles = '';
                    }
                    if (column?.render) {
                        td += `<td style="${(column?.styles)}">${column.render(data, index)}</td>`;
                    } else {
                        td += `<td style="${(column?.styles)}">${data[column.data]}</td>`;
                    }
                });
                return td;
            }
    
            function createRow() {
                if (options.data.length <= 0) {
                    renderEmptyColumns(options.textEmptyData);
                    return $table;
                }
    
                $.each(options.data, function(index, value) {
                    const column = createColumn(value, index);
                    if ($table.find('tbody')?.length <= 0) {
                        const createBody = document.createElement('tbody');
                        createBody.innerHTML = column;
                        $table.append(createBody);
                    }
    
                    $table.find('tbody')?.append(`<tr>${(column)}</tr>`);
                });
            }
    
            function renderEmptyColumns(text) {
                const body = $table.find('tbody');
                if (body.length <= 0) {
                    const createBody = document.createElement('tbody');
                    createBody.innerHTML = `<tr><td style="padding: 20px;text-align: center;" colspan="${$table.find('thead tr th').length}">${text}</td></tr>`;
                    $table.append(createBody);
                    return $table;
                }
    
                body.html(`<tr><td style="padding: 20px;text-align: center;" colspan="${$table.find('thead tr th').length}">${text}</td></tr>`);
                return $table;
            }
    
            function init() {
                $table.find('tbody').html('');
                if(options.footerCallback) {
                    const footer = $table.find('tfooter');
                    if(footer.length <= 0) {
                        const tfooter = `<tfooter><tr><td>${(options.footerCallback(options.data))}</td></tr></tfooter>`;
                        $table.append(tfooter);
                    }else {
                        $table.find('tfooter').html(`<tr><td>${options.footerCallback(options.data)}</td></tr>`);
                    }
                }
                createRow();
            }
    
            function reloadTable() {
                init();
            }

            function add(object, clear = false) {
                if(clear) {
                    options.data = [];
                }
                options.data = object;
                reloadTable();
            }

            function getData() {
                return options.data;
            }
    
            // Store the reloadTable method in methods array for each table
            methods.push({ reloadTable, add, data: options.data });
    
            // Initialize the table
            init();
        });
    
        // Return an object that provides access to reloadTable method
        return {
            reloadTable: function() {
                methods.forEach(method => method.reloadTable());
            },
            add: function(object, clear) {
                methods.forEach(method => method.add(object, clear));
            },
            data: function() {
                let __data; 
                methods.forEach(method => __data = method.data);
                return __data;
            }
        };
    };

    function customSelect2(selector, options = {}) {
        const defaultOptions = {
            width: '100%',
            data: []
        }

        init();

        function merge(object1, object2) {
            return {...object1, ...object2};
        }

        function init() {
            $select2 = $(selector).select2(merge(defaultOptions, options));

            if(options?.clear) {
                $select2.empty().trigger('change');
            }
        }

    }

    function uploadImage({ 
        url = '', 
        token = '',
        file = null, 
        onProgress = () => {} } = {}
    ) {
        return new Promise((resolve, reject) => {
            if (!file) {
                return reject("File tidak boleh kosong");
            }
    
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', token);
    
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function () {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            onProgress(percentComplete); // Kirim progress ke callback
                        }
                    }, false);
                    return xhr;
                }
            })
            .done(resolve)
            .fail(reject);
        });
    }

    $.customTab = customTab;
    $.modalCustom = modalCustom;
    $.parseErros = parseErros;
    $.customSelect2 = customSelect2;
    $.uploadImage = uploadImage;
})(jQuery);