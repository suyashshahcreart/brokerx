function reinitalizeEditors() {
    tinymce.init({
        selector: '.editor',
        height: 800,
        skin: 'oxide',
        menubar: 'file edit view insert format tools table help',
        plugins: `
    preview searchreplace autolink directionality visualblocks visualchars
    fullscreen image link media codesample table charmap hr pagebreak
    nonbreaking anchor insertdatetime advlist lists wordcount help charmap
    code paste
  `.trim().replace(/\s+/g, ' '),

        toolbar1: `
    undo redo | p h1 h2 h3 h4 h5 h6 blockquote pre | bold italic underline strikethrough forecolor backcolor |
    alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat
  `.trim(),
        toolbar2: `
    link image media codesample blockquote insertdatetime table hr charmap |
    preview code fullscreen
  `.trim(),
        content_style: `
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      font-size: 14px;
      line-height: 1.6;
      padding: 16px;
    }
    blockquote {
      border-left: 4px solid #ccc;
      margin: 1em 0;
      padding-left: 1em;
      color: #555;
      font-style: italic;
    }
    pre {
      background: #f4f4f4;
      padding: 1em;
      overflow-x: auto;
    }
    h1 { font-size: 2em; margin: 0.67em 0; }
    h2 { font-size: 1.5em; margin: 0.75em 0; }
    h3 { font-size: 1.17em; margin: 0.83em 0; }
    h4 { font-size: 1em; margin: 1.12em 0; }
    h5 { font-size: 0.83em; margin: 1.5em 0; }
    h6 { font-size: 0.75em; margin: 1.67em 0; }
  `,
        style_formats: [
            { title: 'Paragraph', format: 'p' },
            { title: 'Heading 1', format: 'h1' },
            { title: 'Heading 2', format: 'h2' },
            { title: 'Heading 3', format: 'h3' },
            { title: 'Heading 4', format: 'h4' },
            { title: 'Heading 5', format: 'h5' },
            { title: 'Heading 6', format: 'h6' },
            { title: 'Blockquote', format: 'blockquote' },
            { title: 'Preformatted', format: 'pre' }
        ],
        // Enable style formats dropdown
        style_formats_merge: false,

        // Setup custom buttons for headings
        setup: function (editor) {
            // Paragraph Button
            editor.ui.registry.addButton('p', {
                text: 'Paragraph',
                tooltip: 'Paragraph',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'p');
                }
            });

            // H1 Button
            editor.ui.registry.addButton('h1', {
                text: 'H1',
                tooltip: 'Heading 1',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'h1');
                }
            });

            // H2 Button
            editor.ui.registry.addButton('h2', {
                text: 'H2',
                tooltip: 'Heading 2',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'h2');
                }
            });

            // H3 Button
            editor.ui.registry.addButton('h3', {
                text: 'H3',
                tooltip: 'Heading 3',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'h3');
                }
            });

            // H4 Button
            editor.ui.registry.addButton('h4', {
                text: 'H4',
                tooltip: 'Heading 4',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'h4');
                }
            });

            // H5 Button
            editor.ui.registry.addButton('h5', {
                text: 'H5',
                tooltip: 'Heading 5',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'h5');
                }
            });

            // H6 Button
            editor.ui.registry.addButton('h6', {
                text: 'H6',
                tooltip: 'Heading 6',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'h6');
                }
            });

            // Blockquote Button
            editor.ui.registry.addButton('blockquote', {
                text: 'Quote',
                tooltip: 'Blockquote',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'blockquote');
                }
            });

            // Preformatted Button
            editor.ui.registry.addButton('pre', {
                text: 'Preformatted',
                tooltip: 'Preformatted',
                onAction: function () {
                    editor.execCommand('FormatBlock', false, 'pre');
                }
            });
        },

        // 🔥 Allow script and custom attributes
        valid_elements: '*[*]',
        extended_valid_elements: `
    script[src|async|defer|type|charset],
    iframe[src|width|height|frameborder|allowfullscreen|loading|style],
    blockquote[class|data-*|cite|style],
    div[class|data-*|style],
    span[class|data-*|style],
    a[href|target|rel|data-*],
    img[src|alt|width|height|style|class|loading],
    source[src|type],
    video[controls|width|height|poster|preload|autoplay|loop|muted],
    audio[controls|preload|autoplay|loop|muted|src]
  `,
        verify_html: false,
        // content_css: 'style.css',
        browser_spellcheck: true,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        entity_encoding: 'raw',
        contextmenu: false,
        paste_data_images: true,
        image_caption: true,
        autoresize_bottom_margin: 20,
        branding: false
    });
}

export default reinitalizeEditors;