(function () {
    'use strict';

    class MyUploadAdapter {
        constructor(loader, url = '', headers = {}) {
            this.loader = loader;
            this.url = url;
            this.headers = headers;
        }

        upload() {
            return this.loader.file
                .then(file => new Promise((resolve, reject) => {
                    const data = new FormData();
                    data.append('upload', file);

                    fetch(this.url, {
                        method: 'POST',
                        body: data,
                        headers: this.headers
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (!result.url) {
                            return reject('No URL in response');
                        }

                        resolve({
                            default: result.url
                        });
                    })
                    .catch(error => {
                        reject(error);
                    });
                }));
        }

        abort() {
            // Optional: you can abort the upload process here if needed
        }
    }

    function createCustomUploadAdapterPlugin({ url = '', headers = {} } = {}) {
        return function (editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new MyUploadAdapter(loader, url, headers);
            };
        };
    }

    class ImageRemovePlugin {
        constructor(editor) {
            this.editor = editor;
            this.previousImages = this.getImagesFromEditor();
    
            this.init();
        }
    
        init() {

            const defaultElementTypes = [
                'image',
                'imageInline',
                'inlineImage',
            ]
            const additionalElementTypes = null;
    
            let elementTypes = [
                ...defaultElementTypes,
            ]
    
            if (Array.isArray(additionalElementTypes)) {
                elementTypes = elementTypes.concat(additionalElementTypes)
            }

            this.editor.model.document.on('change:data', (event) => {
                const differ = event.source.differ
    
                // if no difference
                if (differ.isEmpty) {
                    return;
                }
    
                const changes = differ.getChanges({
                    includeChangesInGraveyard: true
                });
        
                if (changes.length === 0) {
                    return;
                }
    
                let hasNoImageRemoved = true
    
                // check any image remove or not
                for (let i = 0; i < changes.length; i++) {
                    const change = changes[i]
                    // if image remove exists
                    if (change && change.type === 'remove' && elementTypes.includes(change.name)) {
                        hasNoImageRemoved = false
                        break
                    }
                }
    
                // if not image remove stop execution
                if (hasNoImageRemoved) {
                    return;
                }
    
                // get removed nodes
                const removedNodes = changes.filter(change => (change.type === 'insert' && elementTypes.includes(change.name)))

                // removed images src
                const removedImagesSrc = [];
                // removed image nodes
                const removedImageNodes = []
    
                removedNodes.forEach(node => {
                    const removedNode = node.position.nodeAfter
                    removedImageNodes.push(removedNode)
                    removedImagesSrc.push(removedNode.getAttribute('src'))
                });
                // invoke the callback
                return this.editor.fire('image:removed', { imageRemoved: removedImagesSrc });
            })
        }
    
        getImagesFromEditor() {
            const editorData = this.editor.getData();
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = editorData;
    
            return Array.from(tempDiv.querySelectorAll('img')).map(img => img.getAttribute('src'));
        }
    } 

    class Shortcode {
        constructor(editor) {
            this.editor = editor;
            this.init();
        }

        init() {
            const editor = this.editor;

            // Register schema
            editor.model.schema.register('shortcode', {
                allowWhere: '$text',
                isInline: true,
                isObject: true,
                allowAttributes: ['value']
            });

            // Downcast untuk menyimpan ke database
            editor.conversion.for('dataDowncast').elementToElement({
                model: 'shortcode',
                view: (modelItem, { writer }) => {
                    const value = modelItem.getAttribute('value');
                    return writer.createText(`[${value}]`);
                }
            });

            // Downcast untuk menampilkan di editor (editable view)
            editor.conversion.for('editingDowncast').elementToElement({
                model: 'shortcode',
                view: (modelItem, { writer }) => {
                    const value = modelItem.getAttribute('value');
                    const span = writer.createContainerElement('span', {
                        class: 'ck-shortcode',
                        'data-shortcode': value
                    });

                    const innerText = writer.createText(`[${value}]`);
                    writer.insert(writer.createPositionAt(span, 0), innerText);

                    return toWidget(span, writer, { label: 'shortcode' });
                }
            });

            // Upcast untuk load dari HTML ke model CKEditor
            editor.conversion.for('upcast').elementToElement({
                view: {
                    name: 'span',
                    classes: 'ck-shortcode'
                },
                model: (viewElement, { writer }) => {
                    const value = viewElement.getAttribute('data-shortcode');
                    return writer.createElement('shortcode', { value });
                }
            });

            // Command insert shortcode
            editor.commands.add('insertShortcode', {
                execute() {
                    const value = prompt('Masukkan nama shortcode (tanpa kurung):');
                    if (!value) return;

                    editor.model.change(writer => {
                        const shortcode = writer.createElement('shortcode', { value });

                        const selection = editor.model.document.selection;
                        const position = selection.getFirstPosition();

                        editor.model.insertContent(shortcode, position);
                    });
                }
            });
        }
    }

    // Util fungsi ringan untuk toWidget
    function toWidget(viewElement, writer, options = {}) {
        writer.setCustomProperty('widget', true, viewElement);
        writer.addClass('ck-widget', viewElement);

        if (options.label) {
            writer.setCustomProperty('widgetLabel', options.label, viewElement);
        }

        return viewElement;
    }

    // Expose to global scope
    window.ImageRemovePlugin = ImageRemovePlugin;
    window.Shortcode = Shortcode;
    window.createCustomUploadAdapterPlugin = createCustomUploadAdapterPlugin;
})();