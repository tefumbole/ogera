{{--
    printDocument(rootId, title, extraCss)

    Prints the contents of an element without opening a pop-up window. The old
    approach used window.open, which fails silently when the browser blocks
    pop-ups and, because the window was named, quietly reused an already-open
    print window instead of raising a new one. A same-origin iframe has neither
    problem, and lets us wait for the letterhead, QR and barcode images to load
    before opening the dialog so nothing prints blank.
--}}
<script type="text/javascript">
    function printDocument(rootId, title, extraCss) {
        var root = document.getElementById(rootId);
        if (!root) { return; }

        var existing = document.getElementById('__print_frame');
        if (existing) { existing.parentNode.removeChild(existing); }

        var frame = document.createElement('iframe');
        frame.id = '__print_frame';
        frame.setAttribute('aria-hidden', 'true');
        frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden;';
        document.body.appendChild(frame);

        var win = frame.contentWindow;
        var doc = win.document;

        var css = '<link rel="stylesheet" type="text/css" href="{{ asset('public/vendor/bootstrap/css/bootstrap.min.css') }}">' +
            '<style type="text/css">' +
            '@page{size:A4;margin:10mm}' +
            'body{margin:0;background:#fff;font-size:12px;color:#1f2a44;-webkit-print-color-adjust:exact;print-color-adjust:exact}' +
            '.d-print-none,.modal-header .close{display:none!important}' +
            '.modal-content{border:0;box-shadow:none}' +
            'table{width:100%;border-collapse:collapse}' +
            'img{max-width:100%}' +
            (extraCss || '') +
            '</style>';

        doc.open();
        doc.write('<!DOCTYPE html><html><head><meta charset="utf-8"><title>' + (title || 'Document') + '</title>' + css + '</head><body>' + root.innerHTML + '</body></html>');
        doc.close();

        var fired = false;
        function go() {
            if (fired) { return; }
            fired = true;
            try {
                win.focus();
                win.print();
            } catch (e) {
                // Nothing useful to recover to; leave the page as it was.
            }
            window.setTimeout(function () {
                if (frame.parentNode) { frame.parentNode.removeChild(frame); }
            }, 1000);
        }

        // Wait for the stylesheet and every image, so the dialog never opens on
        // a half-rendered page. The timeout is the backstop for a dead asset.
        var assets = [].slice.call(doc.images).concat([].slice.call(doc.querySelectorAll('link[rel="stylesheet"]')));
        var pending = assets.length;

        if (!pending) {
            window.setTimeout(go, 80);
        } else {
            var settle = function () {
                pending -= 1;
                if (pending <= 0) { window.setTimeout(go, 80); }
            };
            assets.forEach(function (asset) {
                if (asset.tagName === 'IMG' && asset.complete) {
                    settle();
                    return;
                }
                asset.addEventListener('load', settle);
                asset.addEventListener('error', settle);
            });
        }

        window.setTimeout(go, 4000);
    }
</script>
