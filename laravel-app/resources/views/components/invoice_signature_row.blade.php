{{--
    Builds the signature line used at the foot of the quotation and sale detail
    modals: client on the left, whoever issued the document on the right, both
    resting on the same baseline.

    Styles are inline because the print pop-up only loads Bootstrap, not this
    page's stylesheet.
--}}
<script type="text/javascript">
    function signatureRow(options) {
        var o = options || {};
        var clean = function (v) {
            return (v === undefined || v === null || v === 'null') ? '' : String(v).trim();
        };

        var clientSignature = clean(o.clientSignature);
        var issuerSignature = clean(o.issuerSignature);
        var issuerName = clean(o.issuerName);

        if (!clientSignature && !issuerName && !issuerSignature) {
            return '';
        }

        var LABEL = 'display:block;font-size:9px;font-weight:bold;text-transform:uppercase;letter-spacing:.6px;color:#1f2a44;margin-bottom:3px;';
        var IMG = 'max-height:56px;max-width:100%;';
        var NAME = 'font-weight:bold;font-size:11px;border-top:1px solid #c9cfdf;padding-top:3px;margin-top:2px;';
        var META = 'font-size:9px;color:#6b7386;';

        var cell = function (align, label, signature, name, meta) {
            var pad = align === 'right' ? 'padding-left:14px;' : 'padding-right:14px;';
            var html = '<td style="width:50%;vertical-align:bottom;padding:0;' + pad + 'text-align:' + align + ';">';
            if (label) {
                html += '<span style="' + LABEL + '">' + label + '</span>';
            }
            if (signature) {
                html += '<img src="' + signature + '" alt="Signature" style="' + IMG + '">';
            }
            if (name) {
                html += '<div style="' + NAME + '">' + name + '</div>';
            }
            if (meta) {
                html += '<div style="' + META + '">' + meta + '</div>';
            }
            return html + '</td>';
        };

        var left = clientSignature
            ? cell('left',
                clean(o.clientLabel) || 'Signed by client',
                clientSignature,
                clean(o.clientName) || 'Client',
                clean(o.clientSignedAt) ? 'Signed ' + clean(o.clientSignedAt) : '')
            : '<td style="width:50%;padding:0;"></td>';

        // Only claim to sign for the company when there is a signature to show;
        // otherwise this is just the credit line it replaced.
        var issuerLabel = clean(o.issuerLabel) || (issuerSignature
            ? 'For and on behalf of {{ addslashes(\App\Support\SiteBrand::siteTitle()) }}'
            : '{{ addslashes(trans('file.Created By')) }}');

        var right = cell('right', issuerLabel, issuerSignature, issuerName, clean(o.issuerEmail));

        return '<table style="width:100%;border-collapse:collapse;margin:12px 0 10px;page-break-inside:avoid;"><tr>'
            + left + right + '</tr></table>';
    }
</script>
