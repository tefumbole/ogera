{{-- Row action menus in the list tables (bookings, sales, purchases, …) are cut
     off two ways: `.table-responsive` clips them, and a long menu on the last
     row runs past the bottom of the window with no page left to scroll. Pinning
     an open menu to the viewport avoids both, and a max-height keeps even the
     11-item booking menu reachable. The menu stays where it is in the DOM,
     because the pages delegate their click handlers through the table row. --}}
<style>
    .dropdown-menu.edit-options.is-pinned {
        position: fixed !important;
        top: var(--action-menu-top) !important;
        left: var(--action-menu-left) !important;
        right: auto !important;
        bottom: auto !important;
        transform: none !important;
        margin: 0 !important;
        max-height: var(--action-menu-max-height, none) !important;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        z-index: 1060;
    }
</style>
<script>
(function () {
    if (window.__tableActionMenuInit) return;
    window.__tableActionMenuInit = true;

    var MENU_SELECTOR = '.dropdown-menu.edit-options';
    var GAP = 4;
    var EDGE = 8;
    var MIN_HEIGHT = 120;
    var open = null;

    function place() {
        var anchor = open.toggle.getBoundingClientRect();
        var below = window.innerHeight - anchor.bottom - GAP - EDGE;
        var above = anchor.top - GAP - EDGE;
        var dropUp = open.height > below && above > below;
        var room = Math.max(dropUp ? above : below, MIN_HEIGHT);

        var top = dropUp
            ? Math.max(EDGE, anchor.top - GAP - Math.min(open.height, room))
            : anchor.bottom + GAP;
        // These menus are `dropdown-menu-right`: right edges line up.
        var left = Math.max(EDGE, Math.min(anchor.right - open.width, window.innerWidth - open.width - EDGE));

        open.menu.style.setProperty('--action-menu-top', Math.round(top) + 'px');
        open.menu.style.setProperty('--action-menu-left', Math.round(left) + 'px');
        open.menu.style.setProperty('--action-menu-max-height', Math.round(room) + 'px');
    }

    function reposition() {
        if (!open) return;
        if (!document.contains(open.toggle) || !document.contains(open.menu)) {
            release();
            return;
        }
        place();
    }

    function release() {
        if (!open) return;
        open.menu.classList.remove('is-pinned');
        ['--action-menu-top', '--action-menu-left', '--action-menu-max-height'].forEach(function (prop) {
            open.menu.style.removeProperty(prop);
        });
        open = null;
    }

    $(document).on('shown.bs.dropdown', function (e) {
        if (!e.target || !e.target.querySelector) return;
        var menu = e.target.querySelector(MENU_SELECTOR);
        var toggle = e.relatedTarget || e.target.querySelector('[data-toggle="dropdown"]');
        if (!menu || !toggle) return;

        release();
        // Measure while the menu is still unpinned and unconstrained, so a menu
        // that fits is never given a scrollbar and never wraps against an edge.
        open = { menu: menu, toggle: toggle, width: menu.offsetWidth, height: menu.offsetHeight };
        menu.classList.add('is-pinned');
        place();
    });

    // `hidden`, not `hide`: a page that cancels the close keeps its menu pinned.
    $(document).on('hidden.bs.dropdown', release);

    // The menu is pinned to the viewport, so anything that moves the button
    // underneath it — page scroll, the table's own sideways scroll, a resize —
    // has to move the menu too.
    window.addEventListener('scroll', reposition, true);
    window.addEventListener('resize', reposition);
})();
</script>
