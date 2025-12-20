'use strict';
(function ($) {
  // ==========================================
  //      Start Document Ready function
  // ==========================================
  $(document).ready(function () {

    // Variables
    var $customNiceSelect = $('.customNiceSelect select')


    // Nice Select
    if ($customNiceSelect.length > 0) {
      $customNiceSelect.niceSelect();
    };

    // ============== Header Hide Click On Body Js Start ========
    $('.header-button').on('click', function () {
      $('.body-overlay').toggleClass('show');
    });
    $('.body-overlay').on('click', function () {
      $('.header-button').trigger('click');
      $(this).removeClass('show');
    });
    // =============== Header Hide Click On Body Js End =========
    // // ========================= Header Sticky Js Start ==============
    $(window).on('scroll', function () {
      if ($(window).scrollTop() >= 100) {
        $('.header').addClass('fixed-header');
      } else {
        $('.header').removeClass('fixed-header');
      }
    });
    // // ========================= Header Sticky Js End===================

    // //============================ Scroll To Top Icon Js Start =========
    var btn = $('.scroll-top');

    $(window).scroll(function () {
      if ($(window).scrollTop() > 300) {
        btn.addClass('show');
      } else {
        btn.removeClass('show');
      }
    });

    btn.on('click', function (e) {
      e.preventDefault();
      $('html, body').animate({ scrollTop: 0 }, '300');
    });

    // ========================== Header Hide Scroll Bar Js Start =====================
    $('.navbar-toggler.header-button').on('click', function () {
      $('body').toggleClass('scroll-hide-sm');
    });
    $('.body-overlay').on('click', function () {
      $('body').removeClass('scroll-hide-sm');
    });
    // ========================== Header Hide Scroll Bar Js End =====================

    // ========================== Small Device Header Menu On Click Dropdown menu collapse Stop Js Start =====================
    $('.dropdown-item').on('click', function () {
      $(this).closest('.dropdown-menu').addClass('d-block');
    });
    // ========================== Small Device Header Menu On Click Dropdown menu collapse Stop Js End =====================

    // ========================== Add Attribute For Bg Image Js Start =====================
    $('.bg-img').css('background', function () {
      var bg = 'url(' + $(this).data('background-image') + ')';
      return bg;
    });
    // ========================== Add Attribute For Bg Image Js End =====================

    // ========================== add active class to ul>li top Active current page Js Start =====================
    function dynamicActiveMenuClass(selector) {
      let fileName = window.location.pathname.split('/').reverse()[0];
      selector.find('li').each(function () {
        let anchor = $(this).find('a');
        if ($(anchor).attr('href') == fileName) {
          $(this).addClass('active');
        }
      });
      // if any li has active element add class
      selector.children('li').each(function () {
        if ($(this).find('.active').length) {
          $(this).addClass('active');
        }
      });
      // if no file name return
      if ('' == fileName) {
        selector.find('li').eq(0).addClass('active');
      }
    }
    if ($('ul.sidebar-menu-list').length) {
      dynamicActiveMenuClass($('ul.sidebar-menu-list'));
    }
    // ========================== add active class to ul>li top Active current page Js End =====================

    // ================== Password Show Hide Js Start ==========
    $('.toggle-password').on('click', function () {
      $(this).toggleClass('fa-eye');
      var input = $($(this).attr('id'));
      
      if (input.attr('type') == 'password') {
        input.attr('type', 'text');
      } else {
        input.attr('type', 'password');
      }
    });
    // =============== Password Show Hide Js End =================

    // ========================= Slick Slider Js Start ==============
    $('.payment-methods-slider').slick({
      slidesToShow: 7,
      slidesToScroll: 1,
      autoplay: false,
      pauseOnHover: true,
      autoplaySpeed: 1500,
      speed: 1500,
      dots: false,
      arrows: false,
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 6
          },
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: 5
          },
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 4
          },
        },
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 3
          },
        },
        {
          breakpoint: 375,
          settings: {
            slidesToShow: 2
          },
        },
      ],
    });
    // ========================= Slick Slider Js End ===================

    // ========================= Swiper Slider Js Start ===================
    var swiper = new Swiper(".swiper", {
      effect: "coverflow",
      grabCursor: true,
      centeredSlides: true,
      speed: 1000,
      slidesPerView: "auto",
      coverflowEffect: {
        rotate: 0,
        stretch: 0,
        depth: 60,
        modifier: 2,
        slideShadows: true
      },
      keyboard: {
        enabled: true
      },
      mousewheel: {
        thresholdDelta: 70
      },
      spaceBetween: 0,
      loop: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      // autoplay: false
      autoplay: {
        delay: 2000,
        disableOnInteraction: false,
      }
    });

    // ========================= Swiper Slider Js End ===================

    // ================== Sidebar Menu Js Start ===============
    // Sidebar Dropdown Menu Start
    $('.has-dropdown > a').click(function () {
      $('.sidebar-submenu').slideUp(200);
      if ($(this).parent().hasClass('active')) {
        $('.has-dropdown').removeClass('active');
        $(this).parent().removeClass('active');
      } else {
        $('.has-dropdown').removeClass('active');
        $(this).next('.sidebar-submenu').slideDown(200);
        $(this).parent().addClass('active');
      }
    });
    // Sidebar Dropdown Menu End

    // Sidebar Icon & Overlay js
    $('.sm-sidebar-btn').on('click', function () {
      $('.sidebar-menu').addClass('show');
      $('.sidebar-overlay').addClass('show');
      $("body").toggleClass("scroll-hide-sm");
    });
    $('.sidebar-menu__close, .sidebar-overlay').on('click', function () {
      $('.sidebar-menu').removeClass('show');
      $('.sidebar-overlay').removeClass('show');
      $("body").removeClass("scroll-hide-sm");
    });
    // ===================== Sidebar Menu Js End =================

    // ===================== Header Search Class Add/Remove Js End =================
    $('.sm-search-btn').on('click', function () {
      $('.dashboard-header__left > form').toggleClass('show');
      $('.sm-search-btn').toggleClass('change-icon');
      $(".body-overlay").toggleClass('show');
      $("body").toggleClass("scroll-hide-sm");
    });
    $('.body-overlay').on('click', function () {
      $('.dashboard-header__left > form').removeClass('show');
      $('.sm-search-btn').removeClass('change-icon');
      $(".body-overlay").removeClass('show');
      $("body").removeClass("scroll-hide-sm");
    });
    // ===================== Header Search Class Add/Remove Js Start =================

    // ========================= User Profile Dropdown Js Start ==========
    $('.user-info__button').on('click', function () {
      $('.user-info-dropdown').toggleClass('show');
      $('.user-info__button-icon').toggleClass('rotate');
    });

    $(document).on('click', function (event) {
      var target = $(event.target);

      if (!target.closest('.user-info__button').length && !target.closest('.user-info-dropdown').length) {
        $('.user-info-dropdown').removeClass('show');
      }

      if (!target.closest('.user-info__button').length && !target.closest('.user-info__button-icon').length) {
        $('.user-info__button-icon').removeClass('rotate');
      }
    });
    // ========================= User Profile Dropdown Js End ==========

    // ========================= Notification Js Start ==========
    $('.notification__btn').on('click', function () {
      $('.notification-list').toggleClass('show');
    });

    $(document).on('click', function (event) {
      var target = $(event.target);

      if (!target.closest('.notification__btn').length && !target.closest('.notification-list').length) {
        $('.notification-list').removeClass('show');
      }
    });
    // ========================= Notification Js End ==========
  });
  // ==========================================
  //      End Document Ready function
  // ==========================================

  // ========================= Custom Language Dropdown Js Start =====================
  $('.language-dropdown > .language-dropdown__selected').on('click', function () {
    $(this).parent().toggleClass('open');
  });

  $('.language-dropdown > .language-dropdown__list > .language-dropdown__list__item').on('click', function () {
    $('.language-dropdown > .language-dropdown__list > .language-dropdown__list__item').removeClass('selected');
    $(this).addClass('selected').parent().parent().removeClass('open').children('.language-dropdown__selected').html($(this).html());
  });

  $(document).on('keyup', function (evt) {
    if ((evt.keyCode || evt.which) === 27) {
      $('.language-dropdown').removeClass('open');
    }
  });

  $(document).on('click', function (evt) {
    if ($(evt.target).closest(".language-dropdown > .language-dropdown__selected").length === 0) {
      $('.language-dropdown').removeClass('open');
    }
  });
  // ========================= Custom Language Dropdown Js End =====================

  // ========================== Increment & Decrement Js Start =====================
  const minus = $('.counter__decrement');
  const plus = $('.counter__increment');

  minus.click(function (e) {
    e.preventDefault();
    let value = $(this).parents('.counter').find('.counter__field').val() ?? 0;
    if (value > 0) {
      value--;
    }
    $(this).parent('.counter').find('.counter__field').val(value);
  });

  plus.click(function (e) {
    e.preventDefault();
    let value = $(this).parents('.counter').find('.counter__field').val() ?? 0;
    value++;
    $(this).parents('.counter').find('.counter__field').val(value);
  });
  // ========================== Increment & Decrement Js End =====================

  // ========================= Preloader Js Start =====================
  $(window).on('load', function () {
    $('.preloader').fadeOut();
  });
  // ========================= Preloader Js End=====================
})(jQuery);
