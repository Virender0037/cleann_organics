/**
 * Cart enhancement layer (Phase G).
 *
 * Every cart mutation is first and foremost a real <form method="POST">
 * (method-spoofed for PATCH/DELETE) — see resources/views/components/
 * frontend/{product-card,cart-mini-contents}.blade.php, products/show.blade.php
 * and shopping-cart.blade.php. That means add/update/remove/clear all work
 * correctly with zero JavaScript, via a normal submit + redirect + flashed
 * session message.
 *
 * This script only upgrades two specific interactions to a no-reload
 * experience, since they'd otherwise navigate the shopper away from the
 * page they're looking at:
 *   - "Add to cart" from a product card or the product detail page.
 *   - "Remove" from the header mini-cart sidebar.
 *
 * Quantity updates, removes, and "Clear Cart" on the cart page itself are
 * deliberately left as native submits: the cart page already re-renders
 * everything the server needs to tell it via a normal reload, and
 * reproducing that whole table/summary in JS here would just be another
 * place for the pricing/stock rules to drift out of sync with the server.
 */
(function () {
    'use strict';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function flashMessage(type, message) {
        if (! message) {
            return;
        }

        var wrapper = document.createElement('div');
        wrapper.className = 'container';
        wrapper.setAttribute('data-cart-flash', '');
        wrapper.innerHTML =
            '<div class="alert alert-' + (type === 'error' ? 'danger' : 'success') + ' alert-dismissible fade show" role="alert" style="margin-top:16px;">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';

        document.body.insertBefore(wrapper, document.body.firstChild);

        window.setTimeout(function () {
            if (wrapper.parentNode) {
                wrapper.parentNode.removeChild(wrapper);
            }
        }, 5000);
    }

    function updateHeaderCart(data) {
        var countEl = document.getElementById('mini-cart-count');
        var subtotalEl = document.getElementById('mini-cart-subtotal');
        var miniCart = document.getElementById('mini-cart');

        if (countEl && typeof data.itemCount !== 'undefined') {
            countEl.textContent = data.itemCount;
        }
        if (subtotalEl && typeof data.subtotal !== 'undefined') {
            subtotalEl.textContent = '₹' + Number(data.subtotal).toFixed(2);
        }
        if (miniCart && typeof data.miniCartHtml === 'string') {
            miniCart.innerHTML = data.miniCartHtml;
        }
    }

    function shouldIntercept(form) {
        var action = form.getAttribute('data-cart-form');

        if (action === 'add') {
            return true;
        }

        if (action === 'remove' && form.closest('#mini-cart')) {
            return true;
        }

        return false;
    }

    function methodFor(form) {
        var spoof = form.querySelector('input[name="_method"]');

        return spoof ? spoof.value : (form.getAttribute('method') || 'POST');
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (! (form instanceof HTMLFormElement) || ! form.hasAttribute('data-cart-form')) {
            return;
        }

        if (! shouldIntercept(form)) {
            return;
        }

        event.preventDefault();

        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        fetch(form.getAttribute('action'), {
            method: methodFor(form),
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body: new FormData(form),
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return { success: false, message: 'Something went wrong. Please try again.' };
                });
            })
            .then(function (data) {
                updateHeaderCart(data);
                flashMessage(data.success ? 'success' : 'error', data.message);
            })
            .catch(function () {
                flashMessage('error', 'Something went wrong. Please try again.');
            })
            .finally(function () {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            });
    });
})();
