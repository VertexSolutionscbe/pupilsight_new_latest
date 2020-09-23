(function($) {
    var blog = $(".en-testimonial-slider");
    blog.owlCarousel({
        loop: true,
        margin: 0,
        nav: true,
        // navText: ["Prev", "Next"],
        navText: true,
        dots: true,
        lazyLoad: true,
        center: false,
        autoplay: true,
        autoplayTimeout: 6000,
        smartSpeed: 2000,
        autoplayHoverPause: true,
        items: 1,
        mouseDrag: false
    });

    var enhancedPostsSlider = $('.enhanced-slides-post-grid');
    enhancedPostsSlider.each(function () {
        var sectionId = '#' + $(this).attr('id');
        $(sectionId).children('.enhanced-blocks-post-grid-inner').slick({
            arrows: $(this).data('navigation') === 'dots' || $(this).data('navigation') === 'none' ? false : true,
            dots: $(this).data('navigation') === 'arrows' || $(this).data('navigation') === 'none' ? false : true,
            infinite: true,
            speed: 500,
            slidesToShow: $(this).data('count') === 1 ? 1 : $(this).data('slidesToShow'),
            slidesToScroll: 1,
            autoplay: $(this).data('autoplay'),
            autoplaySpeed: 3000,
            cssEase: "linear",
            responsive: [
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]

        });
    });
    
    var comparisonSlider = $('.enhanced_before_and_after_image');
    comparisonSlider.each(function() {
        let uniqueId = '#' + $(this).attr('id');
        let innerContainer = $(uniqueId).children('.img_area');
        let before = $(this).find('.enhanced_before_image');
        let after = $(this).find('.enhanced_after_image');
        innerContainer.children('.enhanced_image_comparison_container').twentytwenty({
            before_label: before.data('before'),
            after_label: after.data('after')
        });
    });
    
})(jQuery);
