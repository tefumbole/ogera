@extends('layout.main')

@section('content')
@php
    $anTab = 'announcements.compose';
    $clone = $clone ?? null;
    $brandTitle = \App\Support\SiteBrand::siteTitle($general_setting ?? null);
    $defaultHeader = old('header', $clone['header'] ?? ($settings->default_header ?? $brandTitle));
@endphp
<section class="forms">
    <div class="container-fluid an-shell">
        @include('announcement_manager.partials.tabs')
        <div class="mb-4">
            <h1 class="an-title"><i class="fa fa-bullhorn"></i> Compose Announcement</h1>
            <p class="an-subtitle">Write the message, choose who receives it, then send. Recipients get a WhatsApp message — nothing is required from them in return.</p>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

<style>
    .an-compose-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 16px; align-items: start; }
    .an-two-col { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    @media (max-width: 992px) {
        .an-compose-grid, .an-two-col { grid-template-columns: 1fr; }
    }

    .an-step { display: flex; align-items: flex-start; gap: 10px; margin: 0 0 14px; }
    .an-step__num {
        width: 26px; height: 26px; flex: 0 0 26px; border-radius: 50%;
        background: #033d2e; color: #fff; font-weight: 800; font-size: 13px;
        display: flex; align-items: center; justify-content: center; margin-top: 1px;
    }
    .an-step__title { margin: 0; font-weight: 700; color: #033d2e; font-size: 1.05rem; }
    .an-step__hint { margin: 2px 0 0; font-size: 12px; color: #6b7280; }

    .an-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .an-label .opt { font-weight: 400; color: #9ca3af; }
    .an-field { width: 100%; border: 1px solid #d7deea; border-radius: 8px; padding: 9px 12px; font-size: 14px; }
    .an-field:focus { outline: none; border-color: #033d2e; box-shadow: 0 0 0 3px rgba(3,61,46,.12); }
    textarea.an-field { min-height: 170px; resize: vertical; }
    .an-counter { font-size: 11.5px; color: #9ca3af; text-align: right; margin: 4px 0 0; }

    .an-ph {
        display: inline-block; border: 1px solid #9bb6e0; color: #033d2e; border-radius: 999px;
        padding: 2px 10px; font-size: 12px; margin: 2px 2px 0 0; cursor: pointer; background: #f0f6ff; font-weight: 600;
    }
    .an-ph:hover { background: #033d2e; color: #fff; border-color: #033d2e; }
    .an-pill {
        border: 0; border-radius: 999px; padding: 6px 12px; font-size: 12px; font-weight: 600;
        background: #f1f5f9; color: #334155; cursor: pointer; margin: 0 4px 6px 0;
    }
    .an-pill.active { background: #033d2e; color: #fff; }

    .an-pane-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .an-pane-head h6 { margin: 0; font-weight: 700; color: #033d2e; }
    .an-countpill {
        background: #eef4ff; color: #033d2e; border: 1px solid #cfe0ff;
        border-radius: 999px; padding: 2px 10px; font-size: 12px; font-weight: 700;
    }
    .an-user-list { max-height: 280px; overflow: auto; border: 1px solid #e6efe9; border-radius: 10px; background: #fff; }
    .an-user-item { display: block; width: 100%; text-align: left; padding: 10px 12px; border: 0; border-bottom: 1px solid #f0f3f8; background: #fff; cursor: pointer; }
    .an-user-item:hover { background: #f6f9ff; }
    .an-user-item.selected { background: #eef4ff; box-shadow: inset 3px 0 0 #033d2e; }
    .an-user-item .meta { color: #6b7280; font-size: 12px; }
    .an-chip {
        display: inline-flex; align-items: center; gap: 6px; border: 1px solid #033d2e; color: #033d2e;
        background: #eef4ff; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 600; margin: 2px;
    }
    .an-chip button { border: 0; background: transparent; color: #033d2e; font-weight: 800; cursor: pointer; padding: 0 2px; }

    .an-send-opt {
        border: 1px solid #d7deea; border-radius: 10px; padding: 12px 14px; cursor: pointer;
        font-size: 13px; font-weight: 600; background: #fff; flex: 1 1 200px;
    }
    .an-send-opt.active { border-color: #033d2e; background: #eef4ff; color: #033d2e; box-shadow: 0 0 0 3px rgba(3,61,46,.10); }
    .an-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; border-radius: 8px; padding: 8px 10px; font-size: 12px; }

    .an-more { border-top: 1px solid #eef2f7; margin-top: 12px; }
    .an-more > summary { cursor: pointer; font-weight: 600; font-size: 13px; color: #374151; padding: 10px 0 4px; list-style: none; }
    .an-more > summary::-webkit-details-marker { display: none; }
    .an-more > summary::before { content: '\25B8'; margin-right: 8px; color: #9ca3af; }
    .an-more[open] > summary::before { content: '\25BE'; }

    /* Live preview of the WhatsApp message, built the same way the sender builds it. */
    .an-preview-wrap { position: sticky; top: 16px; }
    .an-phone { background: #e4ddd5; border-radius: 12px; padding: 14px; }
    .an-bubble {
        background: #fff; border-radius: 10px 10px 10px 2px; padding: 10px 12px;
        font-size: 13.5px; line-height: 1.5; color: #111b21;
        box-shadow: 0 1px 1px rgba(0,0,0,.13); white-space: pre-wrap; overflow-wrap: anywhere;
    }
    .an-bubble:empty::before { content: 'Your message preview appears here.'; color: #9ca3af; }
    .an-bubble__time { text-align: right; font-size: 10.5px; color: #667781; margin-top: 4px; }

    .an-actionbar {
        position: sticky; bottom: 0; z-index: 20;
        background: #fff; border: 1px solid #eef2f7; border-radius: 14px;
        box-shadow: 0 -6px 18px rgba(15,23,42,.10);
        padding: 12px 16px; margin-bottom: 1rem;
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .an-actionbar__count { font-size: 13px; color: #6b7280; }
    .an-actionbar__count strong { color: #033d2e; font-size: 1.05rem; }
    #an-submit { padding: 11px 22px; font-size: 15px; }
    #an-submit[disabled] { opacity: .45; cursor: not-allowed; }

    .an-drop { border: 1px dashed #94a3b8; border-radius: 10px; padding: 14px; text-align: center; color: #64748b; font-size: 13px; }
</style>

        <form method="POST" action="{{ route('announcements.store') }}" enctype="multipart/form-data" id="an-form">
            @csrf
            <input type="hidden" name="cloned_from_id" value="{{ $clone['cloned_from_id'] ?? '' }}">
            <input type="hidden" name="send_mode" id="an-send-mode" value="now">
            <input type="hidden" name="send_whatsapp" value="1">

            {{-- Step 1 — the message, with a live preview beside it --}}
            <div class="an-compose-grid">
                <div class="an-page-card">
                    <div class="an-step">
                        <div class="an-step__num">1</div>
                        <div>
                            <h5 class="an-step__title">Write your message</h5>
                            <p class="an-step__hint">Start from a template or write from scratch. Placeholders are replaced per recipient.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="an-label" for="an-template-pick">Start from template</label>
                            <select class="an-field" id="an-template-pick">
                                <option value="">Blank message</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->id }}"
                                        data-subject="{{ e($tpl->subject) }}"
                                        data-header="{{ e($tpl->header) }}"
                                        data-body="{{ e($tpl->body) }}"
                                        data-category="{{ $tpl->category_id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="an-label" for="an-category">Category</label>
                            <select name="category_id" class="an-field" id="an-category">
                                <option value="">General</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string)old('category_id', $clone['category_id'] ?? '') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="an-label" for="an-subject">Subject <span class="opt">— shown to recipients in italics</span></label>
                        <input type="text" name="subject" id="an-subject" class="an-field" required placeholder="e.g. Office closed on Monday" value="{{ old('subject', $clone['subject'] ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label class="an-label" for="an-header">Header <span class="opt">— bold opening line, usually your company name</span></label>
                        <input type="text" name="header" id="an-header" class="an-field" value="{{ $defaultHeader }}">
                    </div>

                    <div class="form-group">
                        <label class="an-label" for="an-body">Message</label>
                        <div class="mb-2" style="font-size:12px;color:#9ca3af;">
                            Click to insert:
                            @foreach(['{name}','{email}','{phone}','{address}','{date}','{institution_name}','{reference}'] as $tok)
                                <span class="an-ph" data-token="{{ $tok }}">{{ $tok }}</span>
                            @endforeach
                        </div>
                        <textarea name="body" id="an-body" class="an-field" rows="9" required placeholder="Dear {name},">{{ old('body', $clone['body'] ?? "Dear {name},\n\n") }}</textarea>
                        <p class="an-counter"><span id="an-counter">0</span> characters in the full message</p>
                    </div>

                    <div class="form-group">
                        <label class="an-label" for="an-footer">Footer <span class="opt">— optional sign-off</span></label>
                        <input type="text" name="footer" id="an-footer" class="an-field" value="{{ old('footer', $clone['footer'] ?? '') }}" placeholder="{{ $brandTitle }}">
                    </div>

                    <div class="an-drop">
                        <div class="mb-2">Attachment <span class="opt">— optional, PDF or image, max 10MB</span></div>
                        <input type="file" name="attachment" accept=".pdf,image/*" class="an-field">
                    </div>
                </div>

                <div class="an-preview-wrap">
                    <div class="an-page-card">
                        <h6 style="font-weight:700;color:#033d2e;">Preview</h6>
                        <p style="font-size:12px;color:#6b7280;margin-bottom:10px;">How it arrives on WhatsApp, using the first selected recipient.</p>
                        <div class="an-phone">
                            <div class="an-bubble" id="an-preview"></div>
                            <div class="an-bubble__time">now</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2 — who receives it. Given the full width: this is the fiddliest part. --}}
            <div class="an-page-card">
                <div class="an-step">
                    <div class="an-step__num">2</div>
                    <div>
                        <h5 class="an-step__title">Choose who receives it</h5>
                        <p class="an-step__hint">Pick at least one recipient. CC recipients get the same message, marked as a copy.</p>
                    </div>
                </div>

                <div class="an-two-col">
                    <div>
                        <div class="an-pane-head">
                            <h6>Recipients</h6>
                            <span class="an-countpill"><span id="an-to-count">0</span> selected</span>
                        </div>
                        <div class="mb-2">
                            <button type="button" class="an-pill active an-rf" data-role="customers">Customers</button>
                            <button type="button" class="an-pill an-rf" data-role="staff">System Users</button>
                            <button type="button" class="an-pill an-rf" data-role="all">All</button>
                        </div>
                        <div class="d-flex mb-2" style="gap:8px;">
                            <input type="search" class="an-field an-rsearch" placeholder="Search name, email, phone…">
                            <button type="button" class="an-btn-outline an-rselect-all" style="white-space:nowrap;">Select all</button>
                            <button type="button" class="an-btn-outline an-rclear" style="white-space:nowrap;">Clear</button>
                        </div>
                        <div class="an-user-list an-rlist"></div>
                        <div class="an-rchips mt-2"></div>
                        <div class="an-rhiddens"></div>
                    </div>

                    <div>
                        <div class="an-pane-head">
                            <h6>CC <span style="font-weight:400;color:#6b7280;font-size:12px;">(optional)</span></h6>
                            <span class="an-countpill"><span id="an-cc-count">0</span> selected</span>
                        </div>
                        <div class="mb-2">
                            <button type="button" class="an-pill active an-cf" data-role="all">All</button>
                            <button type="button" class="an-pill an-cf" data-role="staff">Staff</button>
                            <button type="button" class="an-pill an-cf" data-role="customers">Customers</button>
                        </div>
                        <div class="d-flex mb-2" style="gap:8px;">
                            <input type="search" class="an-field an-csearch" placeholder="Search CC…">
                            <button type="button" class="an-btn-outline an-cclear" style="white-space:nowrap;">Clear</button>
                        </div>
                        <div class="an-user-list an-clist"></div>
                        <div class="an-cchips mt-2"></div>
                        <div class="an-chiddens"></div>
                    </div>
                </div>
            </div>

            {{-- Step 3 — timing, with the rarely-used extras folded away --}}
            <div class="an-page-card">
                <div class="an-step">
                    <div class="an-step__num">3</div>
                    <div>
                        <h5 class="an-step__title">Choose when to send</h5>
                        <p class="an-step__hint">Messages go out one recipient every 6 seconds.</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap" style="gap:10px;">
                    <div class="an-send-opt active" data-mode="now">✈ Send immediately</div>
                    <div class="an-send-opt" data-mode="schedule"><i class="dripicons-clock"></i> Schedule for later</div>
                </div>
                <input type="datetime-local" name="schedule_at" id="an-schedule-at" class="an-field d-none mt-2" style="max-width:280px;">

                <details class="an-more">
                    <summary>Add reminders</summary>
                    <p style="font-size:12px;color:#6b7280;">Send the same announcement again at a later date.</p>
                    <div id="an-reminders"></div>
                    <button type="button" class="an-btn-outline" id="an-add-reminder">+ Add reminder</button>
                </details>

                <details class="an-more">
                    <summary>Save this message as a template</summary>
                    <label class="d-flex align-items-center" style="gap:8px;">
                        <input type="checkbox" name="save_as_template" value="1" id="an-save-template"> Save as template for reuse
                    </label>
                    <input type="text" name="template_name" class="an-field mt-2" placeholder="Template name (optional)" style="max-width:340px;">
                </details>
            </div>

            <div class="an-actionbar">
                <div class="an-actionbar__count">
                    <strong id="an-count">0</strong> recipient(s) selected
                    <span id="an-send-hint" style="display:block;">Select at least one recipient in step 2 to send.</span>
                </div>
                <button type="submit" class="an-btn-primary" id="an-submit" disabled>
                    <i class="dripicons-rocket"></i> <span id="an-submit-label">Send Now</span>
                </button>
            </div>
        </form>
    </div>
</section>

<script>
window.AN_USERS = @json($users);
window.AN_USERS_SEARCH = @json(route('announcements.users.search'));
window.AN_BRAND = @json($brandTitle);
window.AN_PRESELECT = @json([
    'recipients' => $clone['recipient_ids'] ?? [],
    'cc' => $clone['cc_ids'] ?? [],
]);
(function () {
    var recipients = (window.AN_PRESELECT.recipients || []).slice();
    var ccs = (window.AN_PRESELECT.cc || []).slice();
    var rRole = 'customers', cRole = 'all';
    var searchTimers = {};

    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
        });
    }

    function mergeUsers(list) {
        var map = {};
        (window.AN_USERS || []).forEach(function (u) { map[u.id] = u; });
        (list || []).forEach(function (u) { map[u.id] = u; });
        window.AN_USERS = Object.keys(map).map(function (k) { return map[k]; });
    }

    function filterUsersLocal(query, roleFilter) {
        var q = (query || '').toLowerCase();
        return (window.AN_USERS || []).filter(function (u) {
            var role = (u.role || '').toLowerCase();
            var source = (u.source || '').toLowerCase();
            if (roleFilter === 'staff' && (source === 'customer' || role === 'customer' || role === 'client')) return false;
            if (roleFilter === 'customers' && source !== 'customer' && role !== 'customer' && role !== 'client') return false;
            if (!q) return true;
            return (u.name||'').toLowerCase().indexOf(q) !== -1
                || (u.email||'').toLowerCase().indexOf(q) !== -1
                || (u.phone||'').toLowerCase().indexOf(q) !== -1;
        });
    }

    function searchUsers(query, roleFilter, done) {
        var q = (query || '').trim();
        if (!q || q.length < 2 || !window.AN_USERS_SEARCH) {
            done(filterUsersLocal(query, roleFilter));
            return;
        }
        var filter = roleFilter === 'staff' ? 'staff' : (roleFilter === 'customers' ? 'customers' : 'all');
        fetch(window.AN_USERS_SEARCH + '?q=' + encodeURIComponent(q) + '&filter=' + encodeURIComponent(filter), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (rows) {
            mergeUsers(rows);
            done(filterUsersLocal(query, roleFilter));
        }).catch(function () {
            done(filterUsersLocal(query, roleFilter));
        });
    }

    function filterUsers(query, roleFilter) {
        return filterUsersLocal(query, roleFilter);
    }

    function renderList(el, users, selected, onToggle) {
        el.innerHTML = users.map(function (u) {
            var sel = selected.indexOf(u.id) !== -1 ? ' selected' : '';
            return '<button type="button" class="an-user-item'+sel+'" data-id="'+esc(u.id)+'">'
                + '<div class="font-weight-bold">'+esc(u.name||'Untitled')+'</div>'
                + '<div class="meta">'+esc(u.email||'')+' · '+esc(u.phone||'')+'</div>'
                + '</button>';
        }).join('') || '<div class="p-3 text-muted small text-center">No people found.</div>';
        el.querySelectorAll('.an-user-item').forEach(function (item) {
            item.addEventListener('click', function () { onToggle(item.getAttribute('data-id')); });
        });
    }

    function renderChips(el, selected, onRemove, prefix) {
        var map = {};
        (window.AN_USERS || []).forEach(function (u) { map[u.id] = u; });
        el.innerHTML = selected.map(function (id) {
            var u = map[id] || { name: id };
            return '<span class="an-chip" data-id="'+esc(id)+'">'+esc((prefix?prefix+' ':'')+(u.name||id))
                + ' <button type="button">×</button></span>';
        }).join('');
        el.querySelectorAll('.an-chip button').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                onRemove(btn.parentNode.getAttribute('data-id'));
            });
        });
    }

    function updateCounts() {
        document.getElementById('an-count').textContent = String(recipients.length);
        document.getElementById('an-to-count').textContent = String(recipients.length);
        document.getElementById('an-cc-count').textContent = String(ccs.length);

        var ready = recipients.length > 0;
        var submit = document.getElementById('an-submit');
        var hint = document.getElementById('an-send-hint');
        submit.disabled = !ready;
        if (ready) {
            hint.textContent = ccs.length ? ccs.length + ' copied in CC' : 'Ready to send.';
        } else {
            hint.textContent = 'Select at least one recipient in step 2 to send.';
        }
        updatePreview();
    }

    function syncHiddens(el, selected, name) {
        el.innerHTML = selected.map(function (id) {
            return '<input type="hidden" name="'+name+'[]" value="'+esc(id)+'">';
        }).join('');
        updateCounts();
    }

    function refreshRecipients() {
        var q = document.querySelector('.an-rsearch').value;
        searchUsers(q, rRole, function (users) {
            renderList(document.querySelector('.an-rlist'), users, recipients, function (id) {
                var i = recipients.indexOf(id);
                if (i === -1) recipients.push(id); else recipients.splice(i, 1);
                refreshRecipients();
            });
            renderChips(document.querySelector('.an-rchips'), recipients, function (id) {
                recipients = recipients.filter(function (x) { return x !== id; });
                refreshRecipients();
            });
            syncHiddens(document.querySelector('.an-rhiddens'), recipients, 'recipient_ids');
        });
    }

    function refreshCc() {
        var q = document.querySelector('.an-csearch').value;
        searchUsers(q, cRole, function (users) {
            renderList(document.querySelector('.an-clist'), users, ccs, function (id) {
                var i = ccs.indexOf(id);
                if (i === -1) ccs.push(id); else ccs.splice(i, 1);
                refreshCc();
            });
            renderChips(document.querySelector('.an-cchips'), ccs, function (id) {
                ccs = ccs.filter(function (x) { return x !== id; });
                refreshCc();
            }, 'CC:');
            syncHiddens(document.querySelector('.an-chiddens'), ccs, 'cc_ids');
        });
    }

    /* ---- Live preview: mirrors AnnouncementPersonalization::buildMessage ---- */

    function previewVars() {
        var map = {};
        (window.AN_USERS || []).forEach(function (u) { map[u.id] = u; });
        var first = recipients.length ? map[recipients[0]] : null;
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var d = new Date();
        return {
            name: (first && first.name) || 'Amina Njoya',
            email: (first && first.email) || 'amina@example.com',
            phone: (first && first.phone) || '675321739',
            address: (first && first.address) || 'Douala, Cameroon',
            date: ('0' + d.getDate()).slice(-2) + ' ' + months[d.getMonth()] + ' ' + d.getFullYear(),
            institution_name: document.getElementById('an-header').value || window.AN_BRAND,
            reference: 'ANN-PREVIEW'
        };
    }

    function applyTokens(text, vars) {
        return String(text || '').replace(/\{(\w+)\}/g, function (whole, key) {
            var k = key.toLowerCase();
            return Object.prototype.hasOwnProperty.call(vars, k) ? vars[k] : whole;
        });
    }

    function waToHtml(text) {
        return esc(text)
            .replace(/\*([^*\n]+)\*/g, '<strong>$1</strong>')
            .replace(/_([^_\n]+)_/g, '<em>$1</em>');
    }

    function updatePreview() {
        var vars = previewVars();
        var header = applyTokens(document.getElementById('an-header').value, vars);
        var subject = applyTokens(document.getElementById('an-subject').value, vars);
        var body = applyTokens(document.getElementById('an-body').value, vars);
        var footer = applyTokens(document.getElementById('an-footer').value, vars);

        var lines = ['Ref: *' + vars.reference + '*'];
        if (header) lines.push('*' + header + '*');
        if (subject) lines.push('_' + subject + '_');
        if (body) { lines.push(''); lines.push(body); }
        if (footer) { lines.push(''); lines.push(footer); }

        var text = lines.join('\n');
        document.getElementById('an-preview').innerHTML = waToHtml(text);
        document.getElementById('an-counter').textContent = String(text.length);
    }

    ['an-header', 'an-subject', 'an-body', 'an-footer'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', updatePreview);
    });

    /* ---- Wiring ---- */

    document.querySelector('.an-rsearch').addEventListener('input', function () {
        clearTimeout(searchTimers.r);
        searchTimers.r = setTimeout(refreshRecipients, 250);
    });
    document.querySelector('.an-csearch').addEventListener('input', function () {
        clearTimeout(searchTimers.c);
        searchTimers.c = setTimeout(refreshCc, 250);
    });
    document.querySelectorAll('.an-rf').forEach(function (btn) {
        btn.addEventListener('click', function () {
            rRole = btn.getAttribute('data-role');
            document.querySelectorAll('.an-rf').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            refreshRecipients();
        });
    });
    document.querySelectorAll('.an-cf').forEach(function (btn) {
        btn.addEventListener('click', function () {
            cRole = btn.getAttribute('data-role');
            document.querySelectorAll('.an-cf').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            refreshCc();
        });
    });
    document.querySelector('.an-rselect-all').addEventListener('click', function () {
        filterUsers(document.querySelector('.an-rsearch').value, rRole).forEach(function (u) {
            if (recipients.indexOf(u.id) === -1) recipients.push(u.id);
        });
        refreshRecipients();
    });
    document.querySelector('.an-rclear').addEventListener('click', function () {
        recipients = [];
        refreshRecipients();
    });
    document.querySelector('.an-cclear').addEventListener('click', function () {
        ccs = [];
        refreshCc();
    });

    document.querySelectorAll('.an-ph').forEach(function (ph) {
        ph.addEventListener('click', function () {
            var ta = document.getElementById('an-body');
            var token = ph.getAttribute('data-token');
            var start = ta.selectionStart;
            var end = ta.selectionEnd;
            if (typeof start === 'number' && typeof end === 'number') {
                ta.value = ta.value.slice(0, start) + token + ta.value.slice(end);
                ta.focus();
                var caret = start + token.length;
                ta.setSelectionRange(caret, caret);
            } else {
                ta.value = (ta.value || '') + token;
                ta.focus();
            }
            updatePreview();
        });
    });

    document.querySelectorAll('.an-send-opt').forEach(function (opt) {
        opt.addEventListener('click', function () {
            document.querySelectorAll('.an-send-opt').forEach(function (o) { o.classList.remove('active'); });
            opt.classList.add('active');
            var mode = opt.getAttribute('data-mode');
            document.getElementById('an-send-mode').value = mode;
            document.getElementById('an-schedule-at').classList.toggle('d-none', mode !== 'schedule');
            document.getElementById('an-submit-label').textContent = mode === 'schedule' ? 'Schedule' : 'Send Now';
        });
    });

    document.getElementById('an-add-reminder').addEventListener('click', function () {
        var box = document.getElementById('an-reminders');
        var row = document.createElement('div');
        row.className = 'd-flex mb-2';
        row.style.gap = '8px';
        row.style.maxWidth = '340px';
        row.innerHTML = '<input type="datetime-local" name="reminders[]" class="an-field">'
            + '<button type="button" class="an-btn-outline text-danger">×</button>';
        row.querySelector('button').addEventListener('click', function () { row.remove(); });
        box.appendChild(row);
    });

    document.getElementById('an-template-pick').addEventListener('change', function () {
        var opt = this.options[this.selectedIndex];
        if (!opt || !opt.value) return;
        document.getElementById('an-subject').value = opt.getAttribute('data-subject') || '';
        document.getElementById('an-header').value = opt.getAttribute('data-header') || '';
        document.getElementById('an-body').value = opt.getAttribute('data-body') || '';
        var cat = opt.getAttribute('data-category');
        if (cat) document.getElementById('an-category').value = cat;
        updatePreview();
    });

    // Ticking "save as template" is what people forget after typing a name.
    document.querySelector('[name=template_name]').addEventListener('input', function () {
        if (this.value.trim()) document.getElementById('an-save-template').checked = true;
    });

    document.getElementById('an-form').addEventListener('submit', function (e) {
        if (!recipients.length) {
            e.preventDefault();
            alert('Select at least one recipient in step 2.');
            document.querySelector('.an-rlist').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    refreshRecipients();
    refreshCc();
})();
</script>
@endsection
