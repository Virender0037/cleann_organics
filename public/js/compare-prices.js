/**
 * Marketplace price comparison (storefront).
 *
 * 1. Product page: swaps the comparison block when the shopper picks another variant. Every variant's block is
 *    shipped as <template data-compare-variant="ID"> by the server; nothing is rendered here, so there is one
 *    markup source (resources/views/components/frontend/compare-offers.blade.php).
 * 2. Product cards: ONE shared <dialog> per page (created on first use, so it never duplicates IDs or gets cloned
 *    by carousels). A card's "Compare prices" button clones that card's <template data-compare-template> into it.
 *    Native <dialog> gives focus trapping and ESC; this adds backdrop click, scroll lock and focus restore.
 */
(function () {
    'use strict';

    if (window.__compareInit) {
        return;
    }
    window.__compareInit = true;

    var dialog = null;
    var titleEl = null;
    var bodyEl = null;
    var lastTrigger = null;

    /* ---------- Product page: variant switching ---------- */
    window.CompareOffers = {
        showVariant: function (variantId) {
            var section = document.getElementById('compare-prices');
            if (!section) {
                return;
            }

            var template = section.querySelector('template[data-compare-variant="' + String(variantId) + '"]');
            var body = section.querySelector('[data-compare-body]');

            if (!template || !body) {
                section.hidden = true;
                return;
            }

            body.replaceChildren(template.content.cloneNode(true));
            section.hidden = false;
        }
    };

    /* ---------- Shared dialog for product cards ---------- */
    function supportsDialog() {
        return typeof HTMLDialogElement === 'function' && typeof HTMLDialogElement.prototype.showModal === 'function';
    }

    function buildDialog() {
        dialog = document.createElement('dialog');
        dialog.className = 'compare-dialog';
        dialog.setAttribute('aria-labelledby', 'compare-dialog-title');

        var panel = document.createElement('div');
        panel.className = 'compare-dialog__panel';

        var head = document.createElement('header');
        head.className = 'compare-dialog__head';

        var heading = document.createElement('div');
        var eyebrow = document.createElement('p');
        eyebrow.className = 'compare-dialog__eyebrow';
        eyebrow.textContent = 'Compare prices';
        titleEl = document.createElement('h2');
        titleEl.className = 'compare-dialog__title';
        titleEl.id = 'compare-dialog-title';
        heading.appendChild(eyebrow);
        heading.appendChild(titleEl);

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'compare-dialog__close';
        close.setAttribute('aria-label', 'Close price comparison');
        close.innerHTML = '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
        close.addEventListener('click', function () { dialog.close(); });

        head.appendChild(heading);
        head.appendChild(close);

        bodyEl = document.createElement('div');
        bodyEl.className = 'compare-dialog__body';

        panel.appendChild(head);
        panel.appendChild(bodyEl);
        dialog.appendChild(panel);
        document.body.appendChild(dialog);

        // A click on the backdrop lands on the <dialog> element itself (the panel fills the rest).
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) {
                dialog.close();
            }
        });

        dialog.addEventListener('close', function () {
            document.documentElement.classList.remove('compare-lock');
            bodyEl.replaceChildren();
            if (lastTrigger && document.contains(lastTrigger)) {
                lastTrigger.focus();
            }
            lastTrigger = null;
        });

        // After "Add to Cart" inside the dialog, step out of the way so the cart feedback is visible.
        dialog.addEventListener('submit', function (event) {
            if (event.target && event.target.hasAttribute && event.target.hasAttribute('data-compare-cart')) {
                window.setTimeout(function () { if (dialog.open) { dialog.close(); } }, 250);
            }
        });
    }

    function openFor(trigger) {
        var wrapper = trigger.closest('.cards-md__compare');
        var template = wrapper ? wrapper.querySelector('template[data-compare-template]') : null;
        if (!template) {
            return;
        }

        if (!supportsDialog()) {
            // Very old browser: fall back to the product page, which has the full comparison.
            var card = trigger.closest('.cards-md');
            var link = card ? card.querySelector('a[href]') : null;
            if (link) {
                window.location.href = link.href;
            }
            return;
        }

        if (!dialog) {
            buildDialog();
        }

        lastTrigger = trigger;
        titleEl.textContent = trigger.getAttribute('data-product-name') || '';
        bodyEl.replaceChildren(template.content.cloneNode(true));
        document.documentElement.classList.add('compare-lock');
        dialog.showModal();
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest ? event.target.closest('[data-compare-open]') : null;
        if (trigger) {
            event.preventDefault();
            openFor(trigger);
        }
    });
})();
