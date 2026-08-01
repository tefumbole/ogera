{{-- Shared look for the four client-facing agreements. Phone-first: these pages
     are almost always opened from a WhatsApp link on a handset. --}}
<style>
    :root {
        --primary: #033d2e;
        --primary-dark: #02261c;
        --accent: #c6ab47;
        --text: #ffffff;
        --muted: #b8c7e6;
    }
    * { box-sizing: border-box; }
    html { -webkit-text-size-adjust: 100%; }
    body {
        margin: 0;
        font-family: "Nunito", sans-serif;
        background: linear-gradient(180deg, #041f4a 0%, #033d2e 100%);
        color: var(--text);
        min-height: 100vh;
        overflow-x: hidden;
    }
    /* Bottom padding clears the fixed action bar, which is taller once stacked. */
    .wrap { max-width: 920px; margin: 0 auto; padding: 24px 16px 160px; }
    .hero { text-align: center; margin-bottom: 24px; }
    .hero img { width: 72px; height: 72px; object-fit: contain; margin-bottom: 10px; }
    .hero h1 { margin: 0 0 8px; font-size: clamp(21px, 5.6vw, 30px); line-height: 1.25; }
    .hero p { color: var(--muted); margin: 0; font-size: 14px; }
    .card {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 14px;
        overflow-wrap: anywhere;
    }
    .card-head { display: flex; gap: 12px; align-items: center; margin-bottom: 10px; }
    .num {
        width: 34px; height: 34px; border-radius: 8px; flex: 0 0 34px;
        border: 2px solid var(--accent); display: flex; align-items: center; justify-content: center;
        color: var(--accent); font-weight: 800;
    }
    .card h3 { margin: 0; color: var(--accent); font-size: 18px; }
    .card p, .card li { color: #e8efff; line-height: 1.6; font-size: 15px; }
    .table-wrap { margin-top: 10px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table.equipment { width: 100%; border-collapse: collapse; }
    table.equipment th, table.equipment td {
        border-bottom: 1px solid rgba(255,255,255,0.12);
        padding: 10px 8px; text-align: left; font-size: 14px;
    }
    table.equipment th { color: var(--accent); }
    .signature-box {
        background: #fff8dc;
        border: 2px solid var(--accent);
        border-radius: 14px;
        padding: 18px;
        color: #5c4a12;
        margin-top: 24px;
    }
    .signature-box h4 { margin: 0 0 8px; display: flex; align-items: center; gap: 8px; }
    .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        border-radius: 10px; padding: 12px 18px; font-weight: 700; cursor: pointer; border: 0;
        min-height: 48px; font-size: 15px; text-decoration: none;
        -webkit-tap-highlight-color: rgba(198,171,71,0.25);
    }
    .btn-outline { background: #fff; border: 2px solid #9a7b1f; color: #6b5612; }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-accent { background: var(--accent); color: #071711; }
    .btn-danger-outline { background: #fff; border: 2px solid #dc3545; color: #dc3545; }
    .checkbox-row { display: flex; gap: 12px; align-items: flex-start; margin-top: 14px; color: #e8efff; }
    .checkbox-row input { margin-top: 3px; width: 22px; height: 22px; flex: 0 0 22px; }
    .checkbox-row label { line-height: 1.5; }

    /* --- ID card: front and back are two independent attachments --- */
    .id-block { margin-top: 16px; }
    .id-block__title {
        display: block; font-weight: 800; font-size: 13px; letter-spacing: 0.5px;
        text-transform: uppercase; color: #6b5612; margin-bottom: 8px;
    }
    .id-tiles { display: flex; gap: 12px; flex-wrap: wrap; }
    .id-tile {
        flex: 1 1 140px; min-width: 0;
        border: 2px dashed #b99a2e; border-radius: 12px;
        background: #fffdf4; padding: 12px; text-align: center;
        display: flex; flex-direction: column;
    }
    .id-tile.is-ready { border-style: solid; border-color: #1f7a4d; background: #f1fbf6; }
    .id-tile__label { display: block; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .id-tile__thumb {
        display: none; width: 100%; height: 96px; object-fit: cover;
        border-radius: 8px; margin: 8px 0 2px; background: #fff;
    }
    .id-tile__doc { display: none; font-size: 30px; margin: 8px 0 2px; }
    .id-tile__thumb.show, .id-tile__doc.show { display: block; }
    .id-tile__state { display: block; font-size: 12px; color: #8a7735; margin: 8px 0 10px; overflow-wrap: anywhere; }
    .id-tile.is-ready .id-tile__state { color: #186b43; font-weight: 700; }
    .id-tile .btn { width: 100%; padding: 10px 12px; font-size: 14px; min-height: 44px; margin-top: auto; }
    .id-hint { font-size: 12px; margin: 12px 0 0; color: #7b6a2f; line-height: 1.5; }

    .footer-bar {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 900;
        background: rgba(4, 31, 74, 0.97); border-top: 1px solid rgba(255,255,255,0.12);
        padding: 12px 16px calc(12px + env(safe-area-inset-bottom, 0px));
    }
    .footer-inner {
        max-width: 920px; margin: 0 auto; display: flex; flex-wrap: wrap;
        gap: 12px; align-items: center; justify-content: space-between;
    }
    .footer-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .modal-backdrop {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1000;
        align-items: center; justify-content: center; padding: 16px;
    }
    .modal-backdrop.open { display: flex; }
    .modal {
        background: #fff; color: #1f2a44; border-radius: 16px; width: 100%; max-width: 720px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.35); overflow: hidden;
        display: flex; flex-direction: column; max-height: calc(100vh - 32px);
    }
    .modal-header { padding: 18px 20px; border-bottom: 1px solid #e5eaf3; }
    .modal-header h3 { margin: 0 0 6px; }
    .modal-body { padding: 18px 20px; overflow-y: auto; }
    .modal-footer { padding: 16px 20px; border-top: 1px solid #e5eaf3; display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap; }
    #signature-pad {
        width: 100%; height: 220px; border: 2px solid #d7deea; border-radius: 12px;
        touch-action: none; background: #fff;
    }
    .preview-signature { max-width: 100%; border: 1px dashed #c6ab47; border-radius: 8px; display: none; margin-top: 10px; }
    .alert { padding: 12px 14px; border-radius: 10px; margin-bottom: 14px; }
    .alert-danger { background: #ffe5e5; color: #842029; }
    .hidden-input { display: none; }

    @media (max-width: 640px) {
        .wrap { padding: 18px 12px 170px; }
        .card { padding: 14px 14px; border-radius: 14px; }
        .card h3 { font-size: 16px; }
        .card p, .card li { font-size: 14.5px; }
        .num { width: 30px; height: 30px; flex: 0 0 30px; font-size: 14px; }
        .signature-box { padding: 14px; }

        /* Wide item tables become one labelled card per row instead of a
           horizontal scroll the client is unlikely to discover. */
        .table-wrap { overflow-x: visible; }
        table.equipment thead { display: none; }
        table.equipment, table.equipment tbody, table.equipment tr, table.equipment td { display: block; width: 100%; }
        table.equipment tr {
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
            padding: 4px 12px;
            margin-bottom: 10px;
        }
        table.equipment td {
            display: flex; justify-content: space-between; align-items: baseline; gap: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 8px 0; text-align: right;
        }
        table.equipment tr td:last-child { border-bottom: 0; }
        table.equipment td::before {
            content: attr(data-label);
            color: var(--accent);
            font-weight: 700;
            text-align: left;
            flex: 0 0 auto;
        }

        .footer-inner { flex-direction: column; align-items: stretch; gap: 10px; }
        .footer-inner > div:first-child { font-size: 13.5px; line-height: 1.4; }
        .footer-actions { flex-wrap: nowrap; }
        .footer-actions .btn { flex: 1 1 0; padding: 12px 10px; }
        .modal { max-height: calc(100vh - 20px); }
        .modal-header, .modal-body, .modal-footer { padding: 14px 16px; }
        .modal-footer .btn { flex: 1 1 100%; }
    }

    @media (max-width: 640px) and (max-height: 700px) {
        #signature-pad { height: 170px; }
    }
</style>
