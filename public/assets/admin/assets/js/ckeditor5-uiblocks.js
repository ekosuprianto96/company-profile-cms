(function () {
    if (typeof CKEDITOR.ClassicEditor === 'undefined') {
        console.error('CKEditor belum dimuat! Pastikan CKEditor telah di-load sebelum plugin ini.');
        return;
    }

    function UIBlockPlugin(editor) {
        console.log(editor, window)
        // const t = editor.t;
        // const view = editor.editing.view;

        // // 1️⃣ Daftarkan model "uiblock"
        // editor.model.schema.register('uiblock', {
        //     allowWhere: '$block',
        //     isObject: true,
        //     allowContentOf: '$block'
        // });

        // // 2️⃣ Konversi HTML ke model (upcast)
        // editor.conversion.for('upcast').elementToElement({
        //     model: 'uiblock',
        //     view: {
        //         name: 'div',
        //         classes: 'uiblock'
        //     }
        // });

        // // 3️⃣ Konversi model ke tampilan editor (downcast)
        // editor.conversion.for('editingDowncast').elementToElement({
        //     model: 'uiblock',
        //     view: (modelElement, { writer }) => {
        //         const div = writer.createContainerElement('div', { class: 'uiblock' });
        //         return div;
        //     }
        // });

        // // 4️⃣ Konversi model ke data (dataDowncast)
        // editor.conversion.for('dataDowncast').elementToElement({
        //     model: 'uiblock',
        //     view: {
        //         name: 'div',
        //         classes: 'uiblock'
        //     }
        // });

        // // 5️⃣ Buat tombol sendiri menggunakan `editor.ui.componentFactory.add()`
        // editor.ui.componentFactory.add('uiblock', locale => {
        //     const button = new window.CKEDITOR5.button.ButtonView(locale); // ✅ Ambil ButtonView dari CKEditor CDN

        //     button.set({
        //         label: 'Insert UI Block',
        //         withText: true,
        //         tooltip: true
        //     });

        //     // ⏩ Aksi saat tombol ditekan
        //     button.on('execute', () => {
        //         editor.model.change(writer => {
        //             const uiblock = writer.createElement('uiblock');
        //             editor.model.insertContent(uiblock);
        //         });
        //     });

        //     return button;
        // });
    }

    if (typeof window.CKEDITOR_PLUGINS === 'undefined') {
        window.CKEDITOR_PLUGINS = {};
    }
    window.CKEDITOR_PLUGINS.UIBlockPlugin = UIBlockPlugin;
})();
