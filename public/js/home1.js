function increment() {
    document.getElementById("counter-btn-counter").stepUp();
}

function decrement() {
    document.getElementById("counter-btn-counter").stepDown();
}

//  Venobox
$("#countdownOne").syotimer({
    year: 2022,
    month: 9,
    day: 25,
    hour: 20,
    minute: 30,
});

$("#countdownThree").syotimer({
    year: 2022,
    month: 9,
    day: 25,
    hour: 20,
    minute: 30,
});


/**
 * Homepage sliders (hero + benefits strip). Both are admin-managed
 * `home_banners` rows; these are scoped classes so they never collide with
 * the theme's other Swipers in main.js.
 */
(function () {
    if (typeof Swiper === 'undefined') {
        return;
    }

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('.home-hero__slider').forEach(function (el) {
        var slides = parseInt(el.getAttribute('data-slides'), 10) || 1;

        if (slides < 2) {
            return; // a single slide needs no controls, loop or autoplay
        }

        new Swiper(el, {
            loop: true,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            speed: 600,
            grabCursor: true,
            autoplay: reduceMotion ? false : { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true },
            keyboard: { enabled: true },
            navigation: {
                nextEl: el.querySelector('.home-hero__nav--next'),
                prevEl: el.querySelector('.home-hero__nav--prev'),
            },
            pagination: { el: el.querySelector('.home-hero__dots'), clickable: true },
        });
    });

    document.querySelectorAll('.home-benefits__slider').forEach(function (el) {
        new Swiper(el, {
            slidesPerView: 1.15,
            spaceBetween: 12,
            rewind: true,
            watchOverflow: true, // all cards fit (desktop) → no sliding/autoplay needed
            grabCursor: true,
            autoplay: reduceMotion ? false : { delay: 3500, disableOnInteraction: false, pauseOnMouseEnter: true },
            pagination: { el: el.querySelector('.home-benefits__dots'), clickable: true },
            breakpoints: {
                576: { slidesPerView: 2, spaceBetween: 14 },
                992: { slidesPerView: 4, spaceBetween: 16 },
            },
        });
    });
})();
