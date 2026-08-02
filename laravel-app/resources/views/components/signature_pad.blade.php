{{--
    Drop-in signature pad.

    Include this once per page, then anywhere on the page add:

        <input type="hidden" name="sign_data" id="sign_data">
        <button type="button" data-signature-pad="sign_data">Draw signature</button>
        <img data-signature-preview="sign_data">          {{-- optional --}}
        <button type="button" data-signature-clear="sign_data">Remove</button>  {{-- optional --}}

    The pad writes a transparent PNG data URL into the hidden input. Nothing is
    painted behind the strokes, so the server receives ink on alpha; the white
    sheet the signer sees is CSS only.
--}}
<div class="sigpad-backdrop" id="sigpad-backdrop" aria-hidden="true">
    <div class="sigpad-dialog" role="dialog" aria-modal="true" aria-labelledby="sigpad-title">
        <div class="sigpad-head">
            <h3 id="sigpad-title">Draw signature</h3>
            <p>Sign inside the box using your mouse, trackpad or finger.</p>
        </div>
        <div class="sigpad-body">
            <canvas id="sigpad-canvas"></canvas>
        </div>
        <div class="sigpad-foot">
            <button type="button" class="sigpad-btn" id="sigpad-clear">Clear</button>
            <button type="button" class="sigpad-btn" id="sigpad-cancel">Cancel</button>
            <button type="button" class="sigpad-btn sigpad-btn-primary" id="sigpad-save">Use signature</button>
        </div>
    </div>
</div>

<style>
    .sigpad-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        top: 0; right: 0; bottom: 0; left: 0;
        background: rgba(15, 23, 42, .55);
        z-index: 20000;
        padding: 16px;
        overflow: auto;
    }
    .sigpad-backdrop.is-open { display: flex; align-items: center; justify-content: center; }
    .sigpad-dialog {
        background: #fff;
        border-radius: 14px;
        width: 100%;
        max-width: 620px;
        box-shadow: 0 24px 60px rgba(2, 8, 23, .35);
        overflow: hidden;
    }
    .sigpad-head { padding: 18px 22px 0; }
    .sigpad-head h3 { margin: 0; font-size: 18px; color: #033d2e; }
    .sigpad-head p { margin: 6px 0 0; font-size: 13px; color: #64748b; }
    .sigpad-body { padding: 16px 22px; }
    #sigpad-canvas {
        width: 100%;
        height: 220px;
        border: 2px solid #d7deea;
        border-radius: 12px;
        background: #fff;
        touch-action: none;
        cursor: crosshair;
        display: block;
    }
    .sigpad-foot {
        padding: 0 22px 18px;
        text-align: right;
    }
    .sigpad-btn {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #1f2a44;
        border-radius: 9px;
        padding: 8px 16px;
        font-size: 14px;
        margin-left: 8px;
        cursor: pointer;
    }
    .sigpad-btn-primary { background: #033d2e; border-color: #033d2e; color: #fff; }
    .sigpad-btn:disabled { opacity: .55; cursor: not-allowed; }
    [data-signature-preview] { max-height: 70px; max-width: 100%; }
    [data-signature-preview]:not([src]), [data-signature-preview][src=""] { display: none; }
</style>

<script>
(function () {
    var backdrop = document.getElementById('sigpad-backdrop');
    var canvas = document.getElementById('sigpad-canvas');
    if (!backdrop || !canvas) { return; }

    var ctx = canvas.getContext('2d');
    var targetId = null;
    var drawing = false;
    var hasInk = false;

    function reset() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        var width = canvas.clientWidth || 560;
        canvas.width = width * ratio;
        canvas.height = 220 * ratio;
        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.clearRect(0, 0, width, 220);
        ctx.lineWidth = 2.2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#0b2545';
        hasInk = false;
    }

    function pointFrom(e) {
        var rect = canvas.getBoundingClientRect();
        var source = e.touches && e.touches.length ? e.touches[0] : e;
        return { x: source.clientX - rect.left, y: source.clientY - rect.top };
    }

    function start(e) {
        e.preventDefault();
        drawing = true;
        var p = pointFrom(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        // A tap with no drag should still leave a mark.
        ctx.lineTo(p.x + 0.1, p.y);
        ctx.stroke();
        hasInk = true;
    }

    function move(e) {
        if (!drawing) { return; }
        e.preventDefault();
        var p = pointFrom(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        hasInk = true;
    }

    function stop() { drawing = false; }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    document.addEventListener('mouseup', stop);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', stop);

    function open(id) {
        targetId = id;
        backdrop.classList.add('is-open');
        backdrop.setAttribute('aria-hidden', 'false');
        // Width is only measurable once the dialog is laid out.
        window.setTimeout(reset, 0);
    }

    function close() {
        backdrop.classList.remove('is-open');
        backdrop.setAttribute('aria-hidden', 'true');
        targetId = null;
    }

    function applyToTarget(id, dataUrl) {
        var input = document.getElementById(id);
        if (input) {
            input.value = dataUrl || '';
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
        var previews = document.querySelectorAll('[data-signature-preview="' + id + '"]');
        for (var i = 0; i < previews.length; i++) {
            if (dataUrl) {
                previews[i].setAttribute('src', dataUrl);
                previews[i].style.display = '';
            } else {
                previews[i].removeAttribute('src');
                previews[i].style.display = 'none';
            }
        }
        var flags = document.querySelectorAll('[data-signature-state="' + id + '"]');
        for (var j = 0; j < flags.length; j++) {
            flags[j].style.display = dataUrl ? 'none' : '';
        }
    }

    document.getElementById('sigpad-clear').addEventListener('click', reset);
    document.getElementById('sigpad-cancel').addEventListener('click', close);

    document.getElementById('sigpad-save').addEventListener('click', function () {
        if (!hasInk) {
            alert('Please draw a signature first.');
            return;
        }
        applyToTarget(targetId, canvas.toDataURL('image/png'));
        close();
    });

    backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) { close(); }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && backdrop.classList.contains('is-open')) { close(); }
    });

    document.addEventListener('click', function (e) {
        var opener = e.target.closest ? e.target.closest('[data-signature-pad]') : null;
        if (opener) {
            e.preventDefault();
            open(opener.getAttribute('data-signature-pad'));
            return;
        }
        var clearer = e.target.closest ? e.target.closest('[data-signature-clear]') : null;
        if (clearer) {
            e.preventDefault();
            applyToTarget(clearer.getAttribute('data-signature-clear'), '');
        }
    });
})();
</script>
