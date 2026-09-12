/**
 * Wishlist heart-button enhancement — the AJAX counterpart to cart.js's
 * "add to cart" upgrade.
 *
 * Every wishlist form is first and foremost a real <form method="POST">
 * (method-spoofed for DELETE), so add/remove work with zero JavaScript via
 * a normal submit + redirect + flashed session message. This script only
 * upgrades the heart-toggle button on product cards and the product detail
 * page to a no-reload experience — it deliberately does NOT intercept the
 * "remove" forms on the wishlist page itself (resources/views/wishlist.blade.php),
 * since removing there means dropping a whole row out of a list, the exact
 * same class of interaction cart.js leaves as a native submit on the full
 * cart page. Only forms marked data-wishlist-toggle (product-card and PDP)
 * are handled here.
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
        wrapper.setAttribute('data-wishlist-flash', '');
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

    function updateHeaderWishlistCount(count) {
        var el = document.getElementById('wishlist-count');
        if (el && typeof count !== 'undefined') {
            el.textContent = count;
        }
    }

    /** Flips a toggle form (and its button's visual state) between its "add" and "remove" shape. */
    function applyWishlistedState(form, wishlisted) {
        form.setAttribute('data-wishlisted', wishlisted ? 'true' : 'false');

        var existingMethodInput = form.querySelector('input[name="_method"]');
        var existingProductIdInput = form.querySelector('input[name="product_id"]');

        if (wishlisted) {
            form.setAttribute('action', form.getAttribute('data-destroy-url'));
            if (existingProductIdInput) {
                existingProductIdInput.remove();
            }
            if (! existingMethodInput) {
                var methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
            }
        } else {
            form.setAttribute('action', form.getAttribute('data-store-url'));
            if (existingMethodInput) {
                existingMethodInput.remove();
            }
            if (! existingProductIdInput) {
                var productIdInput = document.createElement('input');
                productIdInput.type = 'hidden';
                productIdInput.name = 'product_id';
                productIdInput.value = form.getAttribute('data-product-id');
                form.appendChild(productIdInput);
            }
        }

        var button = form.querySelector('button[type="submit"]');
        var path = form.querySelector('svg path');

        if (button) {
            button.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');
        }
        if (path) {
            path.setAttribute('fill', wishlisted ? 'currentColor' : 'none');
        }
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (! (form instanceof HTMLFormElement) || ! form.hasAttribute('data-wishlist-toggle')) {
            return;
        }

        event.preventDefault();

        var submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        var wasWishlisted = form.getAttribute('data-wishlisted') === 'true';
        var methodInput = form.querySelector('input[name="_method"]');
        var method = methodInput ? methodInput.value : (form.getAttribute('method') || 'POST');

        fetch(form.getAttribute('action'), {
            method: method,
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
                if (data.success) {
                    applyWishlistedState(form, ! wasWishlisted);
                }
                updateHeaderWishlistCount(data.wishlistCount);
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
