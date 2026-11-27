(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    initMobileNav();
    initHeroSlider();
    initModal();
    initRegisterForm();
  });

  function initMobileNav() {
    var toggle = document.querySelector('.menu-toggle');
    var nav = document.getElementById('main-nav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
      var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', !isExpanded);
      nav.classList.toggle('is-open');

      if (!isExpanded) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    });

    var navLinks = nav.querySelectorAll('.nav__link');
    navLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 992) {
          toggle.setAttribute('aria-expanded', 'false');
          nav.classList.remove('is-open');
          document.body.style.overflow = '';
        }
      });
    });
  }

  function initHeroSlider() {
    var slides = document.querySelectorAll('.hero-slide');
    var dots = document.querySelectorAll('#hero-dots .hero-dot');
    var prevBtn = document.getElementById('hero-prev');
    var nextBtn = document.getElementById('hero-next');
    var slider = document.getElementById('hero-slider');

    if (!slides.length) return;

    var currentSlide = 0;
    var totalSlides = slides.length;
    var autoplayTimer = null;
    var autoplayInterval = 6000;

    function goToSlide(index) {
      slides[currentSlide].classList.remove('hero-slide--active');
      if (dots[currentSlide]) {
        dots[currentSlide].classList.remove('hero-dot--active');
        dots[currentSlide].setAttribute('aria-selected', 'false');
      }

      currentSlide = (index + totalSlides) % totalSlides;

      slides[currentSlide].classList.add('hero-slide--active');
      if (dots[currentSlide]) {
        dots[currentSlide].classList.add('hero-dot--active');
        dots[currentSlide].setAttribute('aria-selected', 'true');
      }
    }

    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }

    if (nextBtn) nextBtn.addEventListener('click', function () { nextSlide(); resetAutoplay(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { prevSlide(); resetAutoplay(); });

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        goToSlide(i);
        resetAutoplay();
      });
    });

    function startAutoplay() {
      stopAutoplay();
      autoplayTimer = setInterval(nextSlide, autoplayInterval);
    }

    function stopAutoplay() {
      if (autoplayTimer) clearInterval(autoplayTimer);
    }

    function resetAutoplay() {
      stopAutoplay();
      startAutoplay();
    }

    if (slider) {
      slider.addEventListener('mouseenter', stopAutoplay);
      slider.addEventListener('mouseleave', startAutoplay);

      var touchStartX = 0;
      var touchEndX = 0;

      slider.addEventListener('touchstart', function (e) {
        touchStartX = e.touches[0].clientX;
      }, { passive: true });

      slider.addEventListener('touchend', function (e) {
        touchEndX = e.changedTouches[0].clientX;
        var diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 40) {
          if (diff > 0) nextSlide();
          else prevSlide();
          resetAutoplay();
        }
      }, { passive: true });
    }

    startAutoplay();
  }

  function initModal() {
    var modal = document.getElementById('register-interest-modal');
    var headerApplyBtn = document.getElementById('header-apply-btn');
    var mobileDrawerApplyBtn = document.getElementById('mobile-drawer-apply-btn');
    var closeBtn = document.getElementById('modal-close-btn');
    var formView = document.getElementById('modal-form-view');
    var successView = document.getElementById('modal-success-view');

    if (!modal) return;

    function openModal() {
      modal.removeAttribute('hidden');
      modal.offsetHeight;
      modal.classList.add('is-open');
      document.body.style.overflow = 'hidden';

      var firstInput = modal.querySelector('select, input');
      if (firstInput) {
        setTimeout(function () { firstInput.focus(); }, 120);
      }
    }

    function closeModal() {
      modal.classList.remove('is-open');
      setTimeout(function () {
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';

        if (formView && successView) {
          formView.removeAttribute('hidden');
          successView.setAttribute('hidden', '');
        }
      }, 220);
    }

    var modalTriggers = document.querySelectorAll('.open-apply-modal, #header-apply-btn, #mobile-drawer-apply-btn');
    modalTriggers.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var nav = document.getElementById('main-nav');
        var toggle = document.querySelector('.menu-toggle');
        if (nav && toggle && nav.classList.contains('is-open')) {
          toggle.setAttribute('aria-expanded', 'false');
          nav.classList.remove('is-open');
        }
        openModal();
      });
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        closeModal();
      });
    }

    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('is-open')) {
        closeModal();
      }
    });
  }

  function initRegisterForm() {
    var form = document.getElementById('register-form');
    var formView = document.getElementById('modal-form-view');
    var successView = document.getElementById('modal-success-view');

    if (!form || !formView || !successView) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      var requiredInputs = form.querySelectorAll('[required]');
      var isValid = true;

      requiredInputs.forEach(function (input) {
        if (!input.value || input.value.trim() === '') {
          isValid = false;
          input.style.borderColor = '#E53E3E';
        } else {
          input.style.borderColor = '';
        }
      });

      if (!isValid) return;

      formView.setAttribute('hidden', '');
      successView.removeAttribute('hidden');
      form.reset();
    });
  }
})();
