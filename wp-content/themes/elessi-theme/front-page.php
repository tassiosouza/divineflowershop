<?php
/* Template Name: Custom Front Page */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Bloom - Flower Shop HTML Template">

    <title>Bloom - HTML Template</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/favicon.png">

    <!-- All CSS files -->
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/bloom/assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/bloom/assets/css/vendor/fontawsome.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/bloom/assets/css/vendor/slick.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/bloom/assets/css/vendor/animate.min.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/bloom/assets/css/vendor/jquery.magnific-popup.css">
    <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/bloom/assets/css/app.css">

</head>

<body >

    <!-- Preloader-->
    <div id="preloader">
        <div class="holder">
            <div class="preloader flower1">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="preloader flower2">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="preloader flower3">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="preloader flower4">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>
    <!-- Preloader-->
    <?php get_header(); ?>
    <main class="x-hidden">

        <!-- Hero Section Start -->
        <section class="hero-banner">
            <div class="content">
                <div class="container-fluid">
                    <div class="row align-items-end justify-content-center">
                        <div class="col-xl-5">
                            <div class="text-block">
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/banner/image-vector-1.png" alt="" class="banner-vector vector-1 wow zoomIn" data-wow-delay="1.85s">
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/banner/flower-vector.png" alt="" class="banner-vector vector-2 wow zoomIn" data-wow-delay="1.8s">
                                <h1 class="title wow fadeInUp" data-wow-delay="1.95s">Bring the Outdoors In <br> with our <span class="font-sec-2 color-primary"> Beautiful </span> <span class="font-sec-2 color-primary"> Flowers</span><br> and Plant</h1>
                                <p class="wow fadeInUp" data-wow-delay="1.75s">Lorem ipsum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu. Libero aliquam est nullam sit. Congue mauris in convallis ut.</p>
                                <div class="btn-block">
                                    <a href="shop-grid.html" class="cus-btn wow fadeInUp" data-wow-delay="1s">
                                        Shop Now
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M20.734 14.4783C18.1512 20.8861 11.9637 17.0096 11.9637 17.0096C10.0652 20.8439 6.92931 23.3096 3.31056 23.3236C2.55587 23.3236 2.53243 22.1799 3.31056 22.1799C6.32931 22.1658 8.95899 20.1783 10.6793 17.0189C8.75274 17.7642 5.11993 18.3267 3.10431 13.1658C8.21368 11.0611 10.5621 13.6908 11.4621 15.2986C11.9262 14.1549 12.259 12.9127 12.4746 11.5627C12.4746 11.5627 5.92618 12.5892 5.46681 6.96425C11.0496 4.71893 12.6199 10.5596 12.6199 10.5596C12.6949 9.77675 12.7746 8.09393 12.7746 8.05643C12.7746 8.05643 7.79181 4.60175 10.9887 0.312683C16.8293 2.32831 13.8668 7.92518 13.8668 7.92518C13.8902 8.00018 13.8902 9.04081 13.8668 9.49081C13.8668 9.49081 15.9856 5.31893 20.2606 6.7955C20.0637 13.0767 13.609 11.783 13.609 11.783C13.4027 13.0674 13.084 14.2861 12.6715 15.4158C12.6715 15.4158 16.5621 11.1127 20.734 14.4783Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                    <a href="contact.html" class="cus-btn sec wow fadeInUp" data-wow-delay="1.2s">
                                        Contact Us
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M19.8739 7.98103C19.53 7.98103 19.2038 8.05711 18.9102 8.19234C18.6749 7.16583 17.7548 6.3975 16.658 6.3975C16.3065 6.3975 15.9733 6.47667 15.6748 6.61762C15.4112 5.63128 14.5103 4.90252 13.442 4.90252C13.121 4.90252 12.8151 4.96842 12.5371 5.0872V2.31112C12.5371 1.03673 11.5004 0 10.226 0C8.95161 0 7.91483 1.03673 7.91483 2.31112L7.91483 13.3079L6.72318 11.5389L6.70452 11.5157C5.8123 10.4066 4.2426 10.1601 3.05343 10.9425C2.43158 11.3516 2.01032 11.9791 1.86716 12.7095C1.7249 13.4354 1.87541 14.171 2.29096 14.7823L6.54388 21.4861L6.55804 21.5076C7.62997 23.0682 9.4003 24 11.2936 24H16.0657C19.4399 24 22.185 21.2549 22.185 17.8807V10.2921C22.185 9.01777 21.1483 7.98103 19.8739 7.98103ZM20.7788 17.8807C20.7788 20.4795 18.6645 22.5937 16.0657 22.5937H11.2936C9.86783 22.5937 8.53447 21.8941 7.72429 20.7216L3.47197 14.0188L3.45782 13.9974C3.25208 13.6978 3.17732 13.3365 3.24716 12.9799C3.31705 12.6234 3.52274 12.317 3.8263 12.1173C4.39935 11.7403 5.15347 11.8527 5.5918 12.3765L9.32108 17.9123V2.31112C9.32108 1.81214 9.72702 1.40625 10.226 1.40625C10.725 1.40625 11.1309 1.81214 11.1309 2.31112V10.6528H12.5371V7.21369C12.5371 6.7147 12.943 6.30881 13.442 6.30881C13.941 6.30881 14.3469 6.7147 14.3469 7.21369V10.6528H15.7531V8.70862C15.7531 8.20964 16.159 7.80375 16.658 7.80375C17.157 7.80375 17.5629 8.20964 17.5629 8.70862V10.6528H18.9691V10.2922C18.9691 9.79317 19.375 9.38728 19.874 9.38728C20.373 9.38728 20.7789 9.79317 20.7789 10.2922V17.8807H20.7788Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                </div>
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/banner/flower-vector.png" alt="" class="banner-vector vector-3 wow zoomIn" data-wow-delay="1.9s">
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/banner/image-vector-3.png" alt="" class="banner-vector vector-4 wow zoomIn" data-wow-delay="1.95s">
                            </div>
                        </div>
                        <div class="col-xl-7 col-lg-8 col-sm-10 col-11">
                            <div class="img-block">
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/banner/hero-image.png" alt="" class="wow fadeInUp" data-wow-delay="1.25s">
                            </div>
                        </div>
                    </div>
                </div>
                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/banner/image-vector-2.png" alt="" class="banner-vector vector-5 wow zoomIn" data-wow-delay="1.25s">
            </div>
        </section>
        <!-- Hero Section End --> 

        <!-- Categories Section Start -->
        <section class="categories py-80">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-xxl-11 col-lg-12 col-md-10 col-11">
                        <div class="row row-gap-4">
                            <div class="col-lg-3 col-sm-6">
                                <div class="category-card bg-gradient-1">
                                    <div class="category-title">
                                        <a href="shop-grid.html" class="h5 mb-24">Rose Varieties</a><br>
                                        <a href="shop-grid.html" class="link-btn">Shop Now</a>
                                    </div>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/categories/category-01.png" alt="">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="category-card bg-gradient-2">
                                    <div class="category-title">
                                        <a href="shop-grid.html" class="h5 mb-24">Rose Varieties</a><br>
                                        <a href="shop-grid.html" class="link-btn">Shop Now</a>
                                    </div>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/categories/category-02.png" alt="">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="category-card bg-gradient-3">
                                    <div class="category-title">
                                        <a href="shop-grid.html" class="h5 mb-24">Rose Varieties</a><br>
                                        <a href="shop-grid.html" class="link-btn">Shop Now</a>
                                    </div>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/categories/category-03.png" alt="">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="category-card bg-gradient-2">
                                    <div class="category-title">
                                        <a href="shop-grid.html" class="h5 mb-24">Lily Collection</a><br>
                                        <a href="shop-grid.html" class="link-btn">Shop Now</a>
                                    </div>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/categories/category-04.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Categories Section End -->

        <!-- About Section Start -->
        <section class="about py-80">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-xl-10 col-lg-11 col-md-10">
                        <div class="content">
                            <div class="row align-items-center justify-content-lg-start justify-content-center row-gap-4">
                                <div class="col-xl-7 col-lg-6">
                                    <div class="row">
                                        <div class="col-xxl-10 col-xl-11">
                                            <h2 class="mb-16 title">Bloom Chronicles,<br> Crafting Beauty, <span class="font-sec-2 color-primary">One Flower</span> <br>at a Time</h2>
                                            <p class="mb-16">Lorem ipsum dolor sit amet consectetur. Posuere odio a interdum morbi velit elit id ac et. Congue elit risus senectus arcu tortor maecenas commodo magna. Sed lobortis egestas fringilla elementum vulputate pellentesque velit. Lectus adipiscing faucibus semper quis gravida ut odio faucibus. Orci sit aliquam vestibulum varius ultricies sed. Ligula amet amet in curabitur sed nunc imperdiet sit venenatis. Habitasse aenean auctor sed odio et. Sed iaculis pulvinar morbi in commodo malesuada sed.<span class="br"></span>Sed quisque ipsum risus senectus quis curabitur quis. Lorem auctor cras elit quis rhoncus pretium arcu eget malesuada. Facilisi gravida maecenas aliquam eget nunc porttitor. In et ac magna cursus quisque. Aliquam laoreet ut quis velit. Euismod duis sem integer dolor facilisis ut. Tortor aenean aliquet quis aliquam diam vel. Ullamcorper risus semper hendrerit amet velit mauris donec. Sed sollicitudin at lacus donec velit. Ornare dignissim purus tincidunt dictum odio ac ut ut.</p>
                                            <a href="about.html" class="cus-btn">
                                                 Read More
                                                 <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 25" fill="none">
                                                     <path d="M23.1946 5.43081L21.5122 5.43073V3.68924C21.5122 3.29053 21.2205 2.95179 20.8262 2.8928C20.7573 2.88246 19.1184 2.6444 17.1448 3.00351C14.9447 3.40383 13.19 4.39106 12 5.8835C10.8098 4.39106 9.05516 3.40383 6.85509 3.00351C4.88144 2.64448 3.24249 2.88246 3.17373 2.8928C2.77938 2.95187 2.48767 3.29062 2.48767 3.68924V5.43073H0.805331C0.360645 5.43073 0 5.79137 0 6.23614V21.4123C0 21.673 0.126222 21.9176 0.338827 22.0687C0.551432 22.2198 0.823997 22.2586 1.07022 22.1728C1.13066 22.1519 7.16563 20.0923 11.6657 22.1449C11.8781 22.2418 12.1219 22.2418 12.3343 22.1449C16.8218 20.0981 22.8697 22.152 22.9298 22.1728C23.0161 22.2029 23.1055 22.2177 23.1945 22.2177C23.3596 22.2177 23.523 22.1669 23.6612 22.0688C23.8738 21.9177 24 21.6731 24 21.4123V6.23622C23.9999 5.79146 23.6394 5.43081 23.1946 5.43081ZM1.61074 20.3347V7.04147H2.48767V17.7668C2.48767 18.0008 2.59005 18.223 2.7671 18.376C2.94415 18.5289 3.17963 18.5976 3.41106 18.5634C3.4555 18.5569 6.95416 18.0766 9.51027 19.8304C6.36159 19.2919 3.21025 19.9147 1.61074 20.3347ZM11.1946 19.1041C10.0688 18.0868 8.60239 17.399 6.85509 17.0811C6.0142 16.9281 5.23408 16.8835 4.61025 16.8835C4.42504 16.8835 4.25356 16.8874 4.09849 16.8936V4.42879H4.09841C5.64628 4.35898 9.41168 4.52447 11.1946 7.54692V19.1041ZM12.8053 7.54701C14.5818 4.53748 18.3531 4.36447 19.9015 4.43081V16.8936C19.2241 16.8663 18.2353 16.8827 17.1448 17.0811C15.3975 17.399 13.9311 18.0868 12.8053 19.1041V7.54701ZM14.487 19.8308C17.0434 18.076 20.5439 18.5568 20.5876 18.5633C20.8196 18.5981 21.0549 18.5299 21.2325 18.377C21.4101 18.224 21.5122 18.0013 21.5122 17.7668V7.04155H22.3892V20.3347C20.7892 19.9146 17.6365 19.2915 14.487 19.8308Z"/>
                                                 </svg>
                                                 <span></span>
                                             </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-5 col-lg-5 col-md-6 col-sm-7 col-10">
                                    <div class="img-block mx-auto">
                                        <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/about/about-1.png" alt="">
                                        <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/about/about-1-object.png" alt="" class="img-object wow zoomIn" data-wow-delay="0.5s">
                                        <div class="about-tag">
                                            <h3>25 Years <br>Experience</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="vector-mockup">
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/bg/about-bg-vector.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- About Section End -->

        <!-- Featured Products Section Start -->
        <section class="py-80">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between mb-48">
                    <div class="heading">
                        <h2>Featured <span>Flowers</span></h2>
                    </div>
                    <a href="shop-grid.html" class="cus-btn">
                        View All
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                            <path d="M20.734 14.9784C18.1512 21.3862 11.9637 17.5096 11.9637 17.5096C10.0652 21.344 6.92931 23.8096 3.31056 23.8237C2.55587 23.8237 2.53243 22.6799 3.31056 22.6799C6.32931 22.6659 8.95899 20.6784 10.6793 17.519C8.75274 18.2643 5.11993 18.8268 3.10431 13.6659C8.21368 11.5612 10.5621 14.1909 11.4621 15.7987C11.9262 14.6549 12.259 13.4127 12.4746 12.0627C12.4746 12.0627 5.92618 13.0893 5.46681 7.46431C11.0496 5.21899 12.6199 11.0596 12.6199 11.0596C12.6949 10.2768 12.7746 8.59399 12.7746 8.55649C12.7746 8.55649 7.79181 5.10181 10.9887 0.812744C16.8293 2.82837 13.8668 8.42524 13.8668 8.42524C13.8902 8.50024 13.8902 9.54087 13.8668 9.99087C13.8668 9.99087 15.9856 5.81899 20.2606 7.29556C20.0637 13.5768 13.609 12.2831 13.609 12.2831C13.4027 13.5674 13.084 14.7862 12.6715 15.9159C12.6715 15.9159 16.5621 11.6127 20.734 14.9784Z"/>
                        </svg>
                        <span></span>
                    </a>
                </div>
                <div class="products-slider wow fadeInUp" data-wow-delay="0.4s">
                    <button class="arrow prev-btn" data-slide="featured-product-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none">
                            <path d="M12.8057 23C12.8057 20 10.0057 16 6.80566 16M6.80566 16C8.639 16 12.8057 15 12.8057 9M6.80566 16H25.8057" stroke="#1B1918" stroke-width="2"/>
                        </svg>
                    </button> 
                    <button class="arrow next-btn" data-slide="featured-product-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M19.3545 23C19.3545 20 22.1545 16 25.3545 16M25.3545 16C23.5212 16 19.3545 15 19.3545 9M25.3545 16H6.35449" stroke="#1B1918" stroke-width="2"/>
                        </svg>
                    </button>
                    <!-- PRODUCT_SLIDER_START -->
                    <div class="row featured-product-slider">
                        <?php
                        $args = array(
                            'post_type'      => 'product',
                            'posts_per_page' => 6,
                            'tax_query'      => array(
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field'    => 'slug',
                                    'terms'    => 'featured',
                                ),
                            ),
                        );
                        $featured_products = new WP_Query($args);
                        if ($featured_products->have_posts()) :
                            while ($featured_products->have_posts()) :
                                $featured_products->the_post();
                                global $product;
                        ?>
                        <div class="col-12">
                            <div class="product-card">
                                <figure>
                                    <a href="<?php the_permalink(); ?>">
                                        <?php echo $product->get_image(); ?>
                                    </a>
                                </figure>
                                <div class="text-block">
                                    <a href="<?php the_permalink(); ?>" class="h4 mb-16"><?php the_title(); ?></a>
                                    <p class="mb-24"><?php echo wp_trim_words(strip_tags($product->get_short_description()), 20); ?></p>
                                    <div class="price mb-32">
                                        <?php echo $product->get_price_html(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                    </div>
                    <!-- PRODUCT_SLIDER_END -->
                </div>
                <div class="slider-arrows d-lg-none mt-48">
                    <button class="arrow prev-btn" data-slide="featured-product-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none">
                            <path d="M12.8057 23C12.8057 20 10.0057 16 6.80566 16M6.80566 16C8.639 16 12.8057 15 12.8057 9M6.80566 16H25.8057" stroke="#1B1918" stroke-width="2"/>
                        </svg>
                    </button> 
                    <button class="arrow next-btn" data-slide="featured-product-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M19.3545 23C19.3545 20 22.1545 16 25.3545 16M25.3545 16C23.5212 16 19.3545 15 19.3545 9M25.3545 16H6.35449" stroke="#1B1918" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>
        </section>
        <!-- Featured Products Section End -->

        <!-- Features Area Start -->
        <section class="py-40">
            <div class="container-fluid">
                <div class="row row-gap-4">
                    <div class="col-xl-3 col-sm-6">
                        <div class="feature-block">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" fill="none">
                                    <path d="M21.3087 12.7173C20.0081 12.2376 18.7964 11.7153 18.4119 11.5472C18.1562 11.3358 18.1651 11.0445 18.1828 10.9222C18.2079 10.747 18.3363 10.3184 18.9833 10.1235C19.9577 9.83013 20.8935 10.2079 21.4204 10.5013C21.6279 10.6169 21.8146 10.7641 21.9963 10.9171C22.5261 11.3631 23.3173 11.3601 23.8448 10.8819C24.4203 10.3604 24.464 9.47118 23.9424 8.89568L23.6613 8.63132C23.5957 8.5762 22.5665 7.72875 21.0553 7.36633V7.22159C21.0553 6.43958 20.4214 5.80568 19.6394 5.80568C18.8573 5.80568 18.2235 6.43958 18.2235 7.22159V7.41667C18.2064 7.42164 18.1894 7.42549 18.1722 7.43064C16.6704 7.88305 15.6078 9.0678 15.3989 10.5226C15.2027 11.8893 15.7952 13.2041 16.9451 13.9538C17.0073 13.9943 17.0724 14.0298 17.1402 14.06C17.2021 14.0876 18.6758 14.7436 20.3353 15.3557C20.688 15.4857 21.8277 15.9694 21.6915 16.7322C21.59 17.3009 20.8898 17.8892 19.8941 17.8892C18.8548 17.8892 17.8552 17.4723 17.2181 16.7737C16.7026 16.2085 15.8088 16.1518 15.2367 16.6597C14.6509 17.1798 14.6034 18.0766 15.1283 18.6557C15.9424 19.5539 17.0231 20.187 18.2236 20.493V20.6833C18.2236 21.4653 18.8575 22.0992 19.6394 22.0992C20.4214 22.0992 21.0554 21.4653 21.0554 20.6833V20.571C22.805 20.1716 24.1636 18.8873 24.4602 17.2267C24.7311 15.7087 24.0455 13.7269 21.3087 12.7173Z"/>
                                    <path d="M47.6687 31.4965C47.2219 30.4599 46.3803 29.672 45.2991 29.2778C44.2616 28.8997 43.1225 28.9307 42.0859 29.3645L35.3072 31.7858C35.2518 31.4935 35.1631 31.2051 35.0452 30.9319C34.5978 29.8937 33.7548 29.1046 32.6718 28.7097L21.4461 24.6172C18.9605 23.711 16.2213 23.8011 13.7314 24.8696L0.879673 30.0637C0.159621 30.3547 -0.188171 31.1743 0.102812 31.8943C0.393888 32.6144 1.21359 32.9619 1.93345 32.6711L14.7995 27.4713C14.8094 27.4672 14.8193 27.4631 14.8292 27.4589C16.6349 26.6805 18.6955 26.6079 20.4829 27.2594L31.7086 31.3519C32.4107 31.5864 32.7585 32.3968 32.4488 33.0692C32.1213 33.8283 31.1886 34.2302 30.412 33.947L22.0638 30.9035C21.3345 30.6378 20.527 31.0134 20.261 31.743C19.995 32.4726 20.3708 33.2798 21.1005 33.5457L29.4487 36.5892C29.9299 36.7646 30.4275 36.8478 30.9209 36.8478C32.3199 36.8477 33.6832 36.1791 34.5281 35.0504L43.0702 31.9991C43.0984 31.9891 43.1264 31.978 43.1539 31.9661C43.5405 31.7995 43.9603 31.783 44.3357 31.9199C44.6823 32.0463 44.9488 32.2911 45.0859 32.6094C45.2232 32.9278 45.2182 33.2897 45.0721 33.6284C44.9172 33.9876 44.626 34.2768 44.2509 34.4452L31.2973 39.3668C31.2781 39.3741 31.2591 39.3818 31.2402 39.3899C29.9594 39.942 28.5553 39.9969 27.2866 39.5447L13.4945 34.6286C13.1506 34.506 12.7726 34.5222 12.4401 34.6732L0.824457 39.9566C0.117436 40.2782 -0.194921 41.1119 0.126623 41.8188C0.448167 42.5258 1.28193 42.8381 1.98876 42.5167L13.0863 37.469L26.3423 42.1939C27.2173 42.5058 28.1318 42.661 29.0497 42.661C30.157 42.661 31.2695 42.4351 32.3235 41.9855L45.2826 37.0619C45.3018 37.0546 45.3208 37.0469 45.3397 37.0387C46.3884 36.5868 47.2104 35.7715 47.6541 34.7427C48.1103 33.686 48.1154 32.5332 47.6687 31.4965Z"/>
                                    <path d="M28.7115 23.8925H46.5914C47.368 23.8925 47.9975 23.2629 47.9975 22.4863V5.54582C47.9975 4.76924 47.368 4.13965 46.5914 4.13965H28.7115C27.9349 4.13965 27.3053 4.76924 27.3053 5.54582V22.4863C27.3053 23.2629 27.9349 23.8925 28.7115 23.8925ZM36.6481 6.95198H39.4987V10.1363L38.8497 9.70657C38.3791 9.39497 37.7676 9.39497 37.2971 9.70657L36.6481 10.1363V6.95198ZM30.1176 6.95198H33.8356V12.7539C33.8356 13.2718 34.1202 13.7476 34.5763 13.9927C35.0323 14.2377 35.5864 14.2122 36.0181 13.9264L38.0733 12.5655L40.1284 13.9264C40.5608 14.2126 41.1149 14.2373 41.5702 13.9927C42.0264 13.7476 42.3109 13.2718 42.3109 12.7539V6.95198H45.1852V21.0801H30.1176V6.95198Z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="mb-8">Free Shipping</h5>
                                <p>Enjoy Hassle-Free Shipping</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="feature-block">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 48 48" fill="none">
                                    <path d="M47.9206 38.2293L43.4286 12.7542C43.1192 10.9997 41.9499 9.54994 40.3005 8.87626L29.5819 4.49788C29.1203 4.30935 28.6393 4.19272 28.1547 4.14463L27.576 0.862555C27.4412 0.0977466 26.7123 -0.412813 25.947 -0.278095C25.1821 -0.143283 24.6715 0.586087 24.8064 1.3509L25.3852 4.63335C24.9528 4.84138 24.5466 5.111 24.1816 5.43968L22.9676 6.53308L22.7876 6.36977C22.4183 6.03471 22.0063 5.76059 21.5673 5.54965L22.3077 1.3509C22.4425 0.586087 21.9318 -0.143283 21.167 -0.278095C20.4023 -0.413375 19.6729 0.0976528 19.538 0.862555L18.7976 5.06159C18.3201 5.10922 17.8462 5.22369 17.3908 5.40781L6.57549 9.7785C4.91641 10.449 3.74032 11.9014 3.42954 13.6638L0.0788459 32.667C-0.414839 35.467 1.46146 38.1465 4.26138 38.6402L17.9563 41.055L18.3766 43.4388C18.8171 45.9373 20.9979 47.7002 23.4519 47.7002C23.748 47.7002 24.0483 47.6745 24.3498 47.6213L43.7382 44.2026C46.538 43.7089 48.4143 41.0293 47.9206 38.2293ZM43.2497 41.4328L23.8614 44.8515C23.2448 44.9601 22.623 44.8223 22.1101 44.4632C21.5972 44.1041 21.255 43.5667 21.1462 42.9503L20.9037 41.5747L23.6495 42.0589C23.952 42.1122 24.255 42.1386 24.5558 42.1386C25.604 42.1386 26.6258 41.8185 27.5023 41.2047C28.6306 40.4148 29.3836 39.2327 29.6228 37.8764L32.9752 18.864C33.2845 17.1094 32.6814 15.3472 31.3621 14.1501L25.0608 8.43264L26.0635 7.52964C26.5 7.13664 27.0657 6.92936 27.6372 6.92936C27.9351 6.92936 28.2347 6.9858 28.5182 7.10158L39.2369 11.48C39.9866 11.7862 40.5181 12.4451 40.6588 13.2426L45.1508 38.7176C45.3753 39.9904 44.5224 41.2084 43.2497 41.4328ZM2.84849 33.1553L6.19937 14.1521C6.34065 13.351 6.87521 12.6908 7.62933 12.3861L18.2637 8.08839L17.9589 9.81713C17.8241 10.5819 18.3348 11.3113 19.0996 11.4461C19.1822 11.4607 19.2643 11.4677 19.3454 11.4677C20.0149 11.4677 20.6083 10.9876 20.7286 10.3054L21.0335 8.57598L29.4723 16.233C30.0719 16.7771 30.346 17.5781 30.2055 18.3756L26.8531 37.388C26.7444 38.0045 26.4021 38.5418 25.8893 38.9008C25.3765 39.26 24.7545 39.3978 24.138 39.2891L4.74963 35.8705C3.47698 35.646 2.62414 34.4281 2.84849 33.1553Z"/>
                                    <path d="M14.1659 32.4046L13.9414 33.6779C13.8515 34.1878 14.192 34.674 14.7019 34.7639C14.7569 34.7736 14.8117 34.7782 14.8658 34.7782C15.3121 34.7782 15.7077 34.4581 15.7879 34.0034L16.0249 32.6596C16.9883 32.6566 17.9079 32.3888 18.7239 31.8674C19.6953 31.2467 20.443 30.3055 20.8293 29.2171C21.6159 27.0013 20.7706 24.8048 18.6758 23.6212C18.3297 23.4256 18.0113 23.2406 17.7169 23.0641L18.6535 17.7521C19.3119 17.9946 19.6909 18.3941 19.9431 18.6605C20.0149 18.7364 20.0828 18.8081 20.1522 18.8722C20.5324 19.2237 21.1255 19.2003 21.477 18.8203C21.8284 18.4401 21.8052 17.847 21.425 17.4955C21.4015 17.4737 21.3523 17.4218 21.3048 17.3716C20.9648 17.0124 20.2543 16.2621 18.9821 15.8886L19.1757 14.7904C19.2656 14.2805 18.9251 13.7944 18.4152 13.7045C17.9053 13.6146 17.4191 13.9549 17.3292 14.4649L17.1161 15.6733C16.923 15.6805 16.7232 15.6949 16.5145 15.7197C14.9711 15.9028 13.6544 16.9653 13.0781 18.4928C12.5542 19.8812 12.7675 21.3444 13.6347 22.3112C14.1187 22.8507 14.7507 23.3779 15.6515 23.9793L14.4915 30.5578C13.5784 30.3473 13.0218 30.0525 12.1289 29.2144C11.7515 28.86 11.1582 28.8786 10.8038 29.2562C10.4494 29.6336 10.4681 30.2269 10.8456 30.5813C12.0609 31.722 12.9422 32.1319 14.1659 32.4046ZM19.0624 28.59C18.6965 29.6206 17.7313 30.6173 16.3589 30.7652L17.3693 25.0346C17.4938 25.1064 17.6218 25.1794 17.7533 25.2537C19.6804 26.3424 19.2425 28.0826 19.0624 28.59ZM15.0305 21.0592C14.6324 20.6154 14.5565 19.8856 14.8323 19.1547C15.0895 18.4728 15.7246 17.7016 16.7353 17.5816C16.7506 17.5799 16.7652 17.5786 16.7804 17.577L16.0117 21.9368C15.6014 21.6258 15.281 21.3383 15.0305 21.0592Z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="mb-8">Offer and Gift</h5>
                                <p>Irresistible Offers Await You!</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="feature-block">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="none">
                                    <path d="M30.9448 19.1044H27.4209C26.9413 19.1044 26.477 19.17 26.035 19.2905C25.1633 17.5768 23.3836 16.3994 21.3328 16.3994H15.1038C13.053 16.3994 11.2732 17.5768 10.4015 19.2905C9.95957 19.17 9.49522 19.1044 9.01562 19.1044H5.4917C2.58393 19.1044 0.218262 21.4701 0.218262 24.3779V30.0112C0.218262 31.7558 1.63766 33.1752 3.38232 33.1752H33.0542C34.7989 33.1752 36.2183 31.7558 36.2183 30.0112V24.3779C36.2183 21.4701 33.8526 19.1044 30.9448 19.1044ZM9.83033 21.673V31.0659H3.38232C2.80077 31.0659 2.32764 30.5928 2.32764 30.0113V24.378C2.32764 22.6333 3.74704 21.2139 5.4917 21.2139H9.01562C9.30193 21.2139 9.57917 21.2528 9.84313 21.3244C9.83554 21.4398 9.83033 21.5558 9.83033 21.673ZM24.4968 31.0659H11.9397V21.6729C11.9397 19.9282 13.3591 18.5088 15.1038 18.5088H21.3328C23.0774 18.5088 24.4968 19.9282 24.4968 21.6729V31.0659ZM34.1089 30.0113C34.1089 30.5928 33.6358 31.0659 33.0542 31.0659H26.6062V21.6729C26.6062 21.5557 26.601 21.4397 26.5934 21.3244C26.8573 21.2528 27.1346 21.2138 27.4209 21.2138H30.9448C32.6895 21.2138 34.1089 22.6332 34.1089 24.3779V30.0113Z"/>
                                    <path d="M7.25373 9.14482C4.66982 9.14482 2.56768 11.247 2.56768 13.8309C2.56761 16.4148 4.66982 18.5169 7.25373 18.5169C9.83757 18.5169 11.9398 16.4148 11.9398 13.8309C11.9398 11.247 9.83764 9.14482 7.25373 9.14482ZM7.25366 16.4075C5.83286 16.4075 4.67699 15.2517 4.67699 13.8309C4.67699 12.4101 5.83286 11.2542 7.25366 11.2542C8.67447 11.2542 9.83033 12.4101 9.83033 13.8309C9.83033 15.2517 8.67447 16.4075 7.25366 16.4075Z"/>
                                    <path d="M18.2183 3.32471C14.7662 3.32471 11.9578 6.13313 11.9578 9.58519C11.9578 13.0373 14.7662 15.8457 18.2183 15.8457C21.6703 15.8457 24.4787 13.0373 24.4787 9.58519C24.4787 6.1332 21.6703 3.32471 18.2183 3.32471ZM18.2183 13.7363C15.9293 13.7363 14.0672 11.8741 14.0672 9.58519C14.0672 7.29631 15.9293 5.43408 18.2183 5.43408C20.5072 5.43408 22.3694 7.29624 22.3694 9.58519C22.3694 11.8741 20.5072 13.7363 18.2183 13.7363Z"/>
                                    <path d="M29.1828 9.14482C26.5989 9.14482 24.4967 11.247 24.4967 13.8309C24.4968 16.4148 26.5989 18.5169 29.1828 18.5169C31.7667 18.5169 33.8688 16.4148 33.8688 13.8309C33.8688 11.247 31.7667 9.14482 29.1828 9.14482ZM29.1828 16.4075C27.7621 16.4075 26.6061 15.2517 26.6061 13.8309C26.6062 12.4101 27.7621 11.2542 29.1828 11.2542C30.6036 11.2542 31.7595 12.4101 31.7595 13.8309C31.7595 15.2517 30.6036 16.4075 29.1828 16.4075Z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="mb-8">Happy Client</h5>
                                <p>Customer Satisfaction Guaranteed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="feature-block">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="none">
                                    <path d="M43.2316 7.77539C40.8201 6.94702 38.8883 5.00427 38.0636 2.57849C37.5392 1.03601 36.0952 0 34.47 0H13.53C11.9052 0 10.4608 1.03601 9.9364 2.57849C9.11169 5.00427 7.17993 6.94702 4.76843 7.77539C3.23401 8.30273 2.20312 9.7511 2.20312 11.3796V16.6769C2.20312 23.5895 4.27441 30.2454 8.19324 35.9253C11.7788 41.1222 16.7197 45.189 22.4824 47.6865C22.9647 47.8956 23.4822 48 24 48C24.5175 48 25.0349 47.8956 25.5172 47.6865C31.2799 45.1893 36.2208 41.1222 39.8064 35.9253C43.7252 30.2454 45.7969 23.5891 45.7969 16.6769V11.3796C45.7969 9.7511 44.766 8.30273 43.2316 7.77539ZM42.9844 16.6769C42.9844 23.016 41.0848 29.1196 37.4912 34.3282C34.2052 39.0912 29.6777 42.8181 24.3988 45.1058C24.1458 45.2157 23.8546 45.2161 23.6008 45.1058C18.3215 42.8181 13.7944 39.0912 10.5084 34.3282C6.91516 29.12 5.01562 23.0164 5.01562 16.6769V11.3796C5.01562 10.9519 5.28369 10.5725 5.6825 10.4355C8.91064 9.32629 11.4965 6.72766 12.5991 3.48376C12.7357 3.0824 13.1096 2.8125 13.53 2.8125H34.47C34.89 2.8125 35.2643 3.0824 35.4009 3.48376C36.5035 6.72766 39.0894 9.32629 42.3175 10.4355C42.7163 10.5725 42.9844 10.9519 42.9844 11.3796V16.6769Z"/>
                                    <path d="M29.9755 18.2329H29.8799V14.228C29.8799 10.986 27.2421 8.34814 24 8.34814C20.7579 8.34814 18.1201 10.986 18.1201 14.228V18.2329H18.0245C16.1572 18.2329 14.6382 19.7523 14.6382 21.6196V29.7129C14.6382 33.1904 17.4675 36.0201 20.9447 36.0201H27.0553C30.5328 36.0201 33.3618 33.1904 33.3618 29.7129V21.6196C33.3618 19.7523 31.8428 18.2329 29.9755 18.2329ZM20.9326 14.228C20.9326 12.5369 22.3088 11.1606 24 11.1606C25.6912 11.1606 27.0674 12.5369 27.0674 14.228V18.2333H20.9326V14.228ZM30.5493 29.7129C30.5493 31.6399 28.9819 33.2073 27.0553 33.2073H20.9447C19.0181 33.2073 17.4507 31.6399 17.4507 29.7129V21.6196C17.4507 21.3032 17.7081 21.0458 18.0245 21.0458H29.9755C30.2919 21.0458 30.5493 21.3032 30.5493 21.6196V29.7129Z"/>
                                    <path d="M24 24.3918C23.2233 24.3918 22.5938 25.0214 22.5938 25.7981V28.455C22.5938 29.2317 23.2233 29.8612 24 29.8612C24.7767 29.8612 25.4062 29.2317 25.4062 28.455V25.7981C25.4062 25.0214 24.7767 24.3918 24 24.3918Z"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="mb-8">Secured Checkout</h5>
                                <p>Priority: Your Privacy & Security</p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

        </section>
        <!-- Features Area End -->

        <!-- Sale Banner Area Start -->
        <section class="py-40">
            <div class="container-fluid">
                <div class="banner wow fadeInUp" data-wow-delay="0.4s">
                    <div class="content">
                        <div class="bg-shape-vector">
                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/bg/banner-bg-vector.png" alt="">
                        </div>
                        <div class="row justify-content-end">
                            <div class="col-lg-6 col-6 order-lg-2">
                                <div class="text-end img-block">
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/bg/banner-img-xl.png" alt="" class="d-lg-block d-none ms-auto">
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/bg/banner-img.png" alt="" class="d-lg-none">
                                </div>
                            </div>
                            <div class="col-lg-6 col-12 order-lg-1">
                                <div class="text-block">
                                    <h3 class="mb-16">SPECIAL OFFER</h3>
                                    <span class="title-1">SUMMER</span>
                                    <span class="title-2 mb-12">Sale</span>
                                    <h1 class="mb-32">UP TO 50% OFF</h1>
                                    <a href="shop-grid.html" class="cus-btn mx-auto">
                                        Shop Now
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M20.734 14.4783C18.1512 20.8861 11.9637 17.0096 11.9637 17.0096C10.0652 20.8439 6.92931 23.3096 3.31056 23.3236C2.55587 23.3236 2.53243 22.1799 3.31056 22.1799C6.32931 22.1658 8.95899 20.1783 10.6793 17.0189C8.75274 17.7642 5.11993 18.3267 3.10431 13.1658C8.21368 11.0611 10.5621 13.6908 11.4621 15.2986C11.9262 14.1549 12.259 12.9127 12.4746 11.5627C12.4746 11.5627 5.92618 12.5892 5.46681 6.96425C11.0496 4.71893 12.6199 10.5596 12.6199 10.5596C12.6949 9.77675 12.7746 8.09393 12.7746 8.05643C12.7746 8.05643 7.79181 4.60175 10.9887 0.312683C16.8293 2.32831 13.8668 7.92518 13.8668 7.92518C13.8902 8.00018 13.8902 9.04081 13.8668 9.49081C13.8668 9.49081 15.9856 5.31893 20.2606 6.7955C20.0637 13.0767 13.609 11.783 13.609 11.783C13.4027 13.0674 13.084 14.2861 12.6715 15.4158C12.6715 15.4158 16.5621 11.1127 20.734 14.4783Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Sale Banner Area End -->

        <!-- Testimonials Section Start -->
        <section class="py-80">
            <div class="container-fluid">
                <div class="heading text-center mb-48">
                    <h2>Words from <span>Our Clients</span></h2>
                </div>
                <div class="row justify-content-center">
                    <div class="col-xl-10 col-lg-11 col-md-8 col-sm-10">
                        <div class="testimonials-slider-block">
                            <button class="arrow prev-btn" data-slide="testimonial-slick-slider">
                                <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none">
                                    <path d="M12.8057 23C12.8057 20 10.0057 16 6.80566 16M6.80566 16C8.639 16 12.8057 15 12.8057 9M6.80566 16H25.8057" stroke="#1B1918" stroke-width="2"/>
                                </svg>
                            </button> 
                            <button class="arrow next-btn" data-slide="testimonial-slick-slider">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                    <path d="M19.3545 23C19.3545 20 22.1545 16 25.3545 16M25.3545 16C23.5212 16 19.3545 15 19.3545 9M25.3545 16H6.35449" stroke="#1B1918" stroke-width="2"/>
                                </svg>
                            </button>
                            <div class="row testimonial-slick-slider">
                                <div class="col-6">
                                    <div class="testimonial-card">
                                        <div class="img-block">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/testimonials/t-1.png" alt="">
                                            <div class="quote-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 30 22" fill="none">
                                                    <path d="M22.4552 0.33317C23.6786 0.33317 24.7491 0.562561 25.6666 1.02134C26.5842 1.5311 27.3234 2.19378 27.8841 3.00939C28.3938 3.825 28.8016 4.79354 29.1075 5.91501C29.4134 7.03648 29.5663 8.20892 29.5663 9.43233C29.5663 11.9301 28.9291 14.275 27.6547 16.467C26.3293 18.6589 24.2903 20.3921 21.5376 21.6665L20.8494 20.2902C22.2768 19.6275 23.5512 18.6589 24.6726 17.3845C25.7431 16.1611 26.3803 14.8612 26.5842 13.4849C26.8901 12.4144 26.9155 11.3694 26.6607 10.3499C25.5392 11.5733 24.0354 12.185 22.1493 12.185C20.4161 12.185 18.9888 11.6498 17.8674 10.5793C16.7459 9.55977 16.1852 8.13245 16.1852 6.29733C16.1852 4.51318 16.7714 3.06037 17.9438 1.93891C19.1163 0.868416 20.62 0.33317 22.4552 0.33317ZM6.70368 0.33317C7.9271 0.33317 8.99759 0.562561 9.91515 1.02134C10.8327 1.5311 11.5719 2.19378 12.1326 3.00939C12.6424 3.825 13.0502 4.79354 13.356 5.91501C13.6619 7.03648 13.8148 8.20892 13.8148 9.43233C13.8148 11.9301 13.1776 14.275 11.9032 16.467C10.5778 18.6589 8.53881 20.3921 5.78612 21.6665L5.09795 20.2902C6.52527 19.6275 7.79966 18.6589 8.92113 17.3845C9.99162 16.1611 10.6288 14.8612 10.8327 13.4849C11.1386 12.4144 11.1641 11.3694 10.9092 10.3499C9.78771 11.5733 8.28393 12.185 6.39783 12.185C4.66466 12.185 3.23734 11.6498 2.11587 10.5793C0.994406 9.55977 0.433672 8.13245 0.433672 6.29733C0.433672 4.51318 1.01989 3.06037 2.19234 1.93891C3.36478 0.868416 4.86856 0.33317 6.70368 0.33317Z" fill="#BE70A7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="text-block">
                                            <div class="stars mb-8"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                                            <div class="info mb-16">
                                                <span class="h6 color-primary">Ethan Clarke</span>
                                                <span class="h6">Customer</span>
                                            </div>
                                            <p>
                                                “Lorem ipsum dolor sit amet consectetur. Volutpat egestas non posuere faucibus. Diam consequat eros convallis enim consequat arcu vitae. Est porta netus sit tellus non eget purus.”
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="testimonial-card">
                                        <div class="img-block">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/testimonials/t-2.png" alt="">
                                            <div class="quote-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 30 22" fill="none">
                                                    <path d="M22.4552 0.33317C23.6786 0.33317 24.7491 0.562561 25.6666 1.02134C26.5842 1.5311 27.3234 2.19378 27.8841 3.00939C28.3938 3.825 28.8016 4.79354 29.1075 5.91501C29.4134 7.03648 29.5663 8.20892 29.5663 9.43233C29.5663 11.9301 28.9291 14.275 27.6547 16.467C26.3293 18.6589 24.2903 20.3921 21.5376 21.6665L20.8494 20.2902C22.2768 19.6275 23.5512 18.6589 24.6726 17.3845C25.7431 16.1611 26.3803 14.8612 26.5842 13.4849C26.8901 12.4144 26.9155 11.3694 26.6607 10.3499C25.5392 11.5733 24.0354 12.185 22.1493 12.185C20.4161 12.185 18.9888 11.6498 17.8674 10.5793C16.7459 9.55977 16.1852 8.13245 16.1852 6.29733C16.1852 4.51318 16.7714 3.06037 17.9438 1.93891C19.1163 0.868416 20.62 0.33317 22.4552 0.33317ZM6.70368 0.33317C7.9271 0.33317 8.99759 0.562561 9.91515 1.02134C10.8327 1.5311 11.5719 2.19378 12.1326 3.00939C12.6424 3.825 13.0502 4.79354 13.356 5.91501C13.6619 7.03648 13.8148 8.20892 13.8148 9.43233C13.8148 11.9301 13.1776 14.275 11.9032 16.467C10.5778 18.6589 8.53881 20.3921 5.78612 21.6665L5.09795 20.2902C6.52527 19.6275 7.79966 18.6589 8.92113 17.3845C9.99162 16.1611 10.6288 14.8612 10.8327 13.4849C11.1386 12.4144 11.1641 11.3694 10.9092 10.3499C9.78771 11.5733 8.28393 12.185 6.39783 12.185C4.66466 12.185 3.23734 11.6498 2.11587 10.5793C0.994406 9.55977 0.433672 8.13245 0.433672 6.29733C0.433672 4.51318 1.01989 3.06037 2.19234 1.93891C3.36478 0.868416 4.86856 0.33317 6.70368 0.33317Z" fill="#BE70A7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="text-block">
                                            <div class="stars mb-8"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                                            <div class="info mb-16">
                                                <span class="h6 color-primary">Lily Martinez</span>
                                                <span class="h6">Customer</span>
                                            </div>
                                            <p>
                                                “Lorem ipsum dolor sit amet consectetur. Volutpat egestas non posuere faucibus. Diam consequat eros convallis enim consequat arcu vitae. Est porta netus sit tellus non eget purus.”
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="testimonial-card">
                                        <div class="img-block">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/testimonials/t-1.png" alt="">
                                            <div class="quote-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 30 22" fill="none">
                                                    <path d="M22.4552 0.33317C23.6786 0.33317 24.7491 0.562561 25.6666 1.02134C26.5842 1.5311 27.3234 2.19378 27.8841 3.00939C28.3938 3.825 28.8016 4.79354 29.1075 5.91501C29.4134 7.03648 29.5663 8.20892 29.5663 9.43233C29.5663 11.9301 28.9291 14.275 27.6547 16.467C26.3293 18.6589 24.2903 20.3921 21.5376 21.6665L20.8494 20.2902C22.2768 19.6275 23.5512 18.6589 24.6726 17.3845C25.7431 16.1611 26.3803 14.8612 26.5842 13.4849C26.8901 12.4144 26.9155 11.3694 26.6607 10.3499C25.5392 11.5733 24.0354 12.185 22.1493 12.185C20.4161 12.185 18.9888 11.6498 17.8674 10.5793C16.7459 9.55977 16.1852 8.13245 16.1852 6.29733C16.1852 4.51318 16.7714 3.06037 17.9438 1.93891C19.1163 0.868416 20.62 0.33317 22.4552 0.33317ZM6.70368 0.33317C7.9271 0.33317 8.99759 0.562561 9.91515 1.02134C10.8327 1.5311 11.5719 2.19378 12.1326 3.00939C12.6424 3.825 13.0502 4.79354 13.356 5.91501C13.6619 7.03648 13.8148 8.20892 13.8148 9.43233C13.8148 11.9301 13.1776 14.275 11.9032 16.467C10.5778 18.6589 8.53881 20.3921 5.78612 21.6665L5.09795 20.2902C6.52527 19.6275 7.79966 18.6589 8.92113 17.3845C9.99162 16.1611 10.6288 14.8612 10.8327 13.4849C11.1386 12.4144 11.1641 11.3694 10.9092 10.3499C9.78771 11.5733 8.28393 12.185 6.39783 12.185C4.66466 12.185 3.23734 11.6498 2.11587 10.5793C0.994406 9.55977 0.433672 8.13245 0.433672 6.29733C0.433672 4.51318 1.01989 3.06037 2.19234 1.93891C3.36478 0.868416 4.86856 0.33317 6.70368 0.33317Z" fill="#BE70A7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="text-block">
                                            <div class="stars mb-8"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                                            <div class="info mb-16">
                                                <span class="h6 color-primary">Ethan Clarke</span>
                                                <span class="h6">Customer</span>
                                            </div>
                                            <p>
                                                “Lorem ipsum dolor sit amet consectetur. Volutpat egestas non posuere faucibus. Diam consequat eros convallis enim consequat arcu vitae. Est porta netus sit tellus non eget purus.”
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="testimonial-card">
                                        <div class="img-block">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/testimonials/t-2.png" alt="">
                                            <div class="quote-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 30 22" fill="none">
                                                    <path d="M22.4552 0.33317C23.6786 0.33317 24.7491 0.562561 25.6666 1.02134C26.5842 1.5311 27.3234 2.19378 27.8841 3.00939C28.3938 3.825 28.8016 4.79354 29.1075 5.91501C29.4134 7.03648 29.5663 8.20892 29.5663 9.43233C29.5663 11.9301 28.9291 14.275 27.6547 16.467C26.3293 18.6589 24.2903 20.3921 21.5376 21.6665L20.8494 20.2902C22.2768 19.6275 23.5512 18.6589 24.6726 17.3845C25.7431 16.1611 26.3803 14.8612 26.5842 13.4849C26.8901 12.4144 26.9155 11.3694 26.6607 10.3499C25.5392 11.5733 24.0354 12.185 22.1493 12.185C20.4161 12.185 18.9888 11.6498 17.8674 10.5793C16.7459 9.55977 16.1852 8.13245 16.1852 6.29733C16.1852 4.51318 16.7714 3.06037 17.9438 1.93891C19.1163 0.868416 20.62 0.33317 22.4552 0.33317ZM6.70368 0.33317C7.9271 0.33317 8.99759 0.562561 9.91515 1.02134C10.8327 1.5311 11.5719 2.19378 12.1326 3.00939C12.6424 3.825 13.0502 4.79354 13.356 5.91501C13.6619 7.03648 13.8148 8.20892 13.8148 9.43233C13.8148 11.9301 13.1776 14.275 11.9032 16.467C10.5778 18.6589 8.53881 20.3921 5.78612 21.6665L5.09795 20.2902C6.52527 19.6275 7.79966 18.6589 8.92113 17.3845C9.99162 16.1611 10.6288 14.8612 10.8327 13.4849C11.1386 12.4144 11.1641 11.3694 10.9092 10.3499C9.78771 11.5733 8.28393 12.185 6.39783 12.185C4.66466 12.185 3.23734 11.6498 2.11587 10.5793C0.994406 9.55977 0.433672 8.13245 0.433672 6.29733C0.433672 4.51318 1.01989 3.06037 2.19234 1.93891C3.36478 0.868416 4.86856 0.33317 6.70368 0.33317Z" fill="#BE70A7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="text-block">
                                            <div class="stars mb-8"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                                            <div class="info mb-16">
                                                <span class="h6 color-primary">Lily Martinez</span>
                                                <span class="h6">Customer</span>
                                            </div>
                                            <p>
                                                “Lorem ipsum dolor sit amet consectetur. Volutpat egestas non posuere faucibus. Diam consequat eros convallis enim consequat arcu vitae. Est porta netus sit tellus non eget purus.”
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="slider-arrows d-sm-none mt-48">
                            <button class="arrow prev-btn" data-slide="testimonial-slick-slider">
                                <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none">
                                    <path d="M12.8057 23C12.8057 20 10.0057 16 6.80566 16M6.80566 16C8.639 16 12.8057 15 12.8057 9M6.80566 16H25.8057" stroke="#1B1918" stroke-width="2"/>
                                </svg>
                            </button> 
                            <button class="arrow next-btn" data-slide="testimonial-slick-slider">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                                    <path d="M19.3545 23C19.3545 20 22.1545 16 25.3545 16M25.3545 16C23.5212 16 19.3545 15 19.3545 9M25.3545 16H6.35449" stroke="#1B1918" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Testimonials Section End -->

        
        <!-- Blogs Area Start -->
        <section class="py-80">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between mb-48">
                    <div class="heading">
                        <h2>Our Latest <span>Articles</span></h2>
                    </div>
                    <a href="blogs.html" class="cus-btn">
                        View All
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
                            <path d="M20.734 14.9784C18.1512 21.3862 11.9637 17.5096 11.9637 17.5096C10.0652 21.344 6.92931 23.8096 3.31056 23.8237C2.55587 23.8237 2.53243 22.6799 3.31056 22.6799C6.32931 22.6659 8.95899 20.6784 10.6793 17.519C8.75274 18.2643 5.11993 18.8268 3.10431 13.6659C8.21368 11.5612 10.5621 14.1909 11.4621 15.7987C11.9262 14.6549 12.259 13.4127 12.4746 12.0627C12.4746 12.0627 5.92618 13.0893 5.46681 7.46431C11.0496 5.21899 12.6199 11.0596 12.6199 11.0596C12.6949 10.2768 12.7746 8.59399 12.7746 8.55649C12.7746 8.55649 7.79181 5.10181 10.9887 0.812744C16.8293 2.82837 13.8668 8.42524 13.8668 8.42524C13.8902 8.50024 13.8902 9.54087 13.8668 9.99087C13.8668 9.99087 15.9856 5.81899 20.2606 7.29556C20.0637 13.5768 13.609 12.2831 13.609 12.2831C13.4027 13.5674 13.084 14.7862 12.6715 15.9159C12.6715 15.9159 16.5621 11.6127 20.734 14.9784Z"/>
                        </svg>
                        <span></span>
                    </a>
                </div>
                <div class="blogs-slider-block">
                    <button class="arrow prev-btn" data-slide="blogs-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none">
                            <path d="M12.8057 23C12.8057 20 10.0057 16 6.80566 16M6.80566 16C8.639 16 12.8057 15 12.8057 9M6.80566 16H25.8057" stroke="#1B1918" stroke-width="2"/>
                        </svg>
                    </button> 
                    <button class="arrow next-btn" data-slide="blogs-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M19.3545 23C19.3545 20 22.1545 16 25.3545 16M25.3545 16C23.5212 16 19.3545 15 19.3545 9M25.3545 16H6.35449" stroke="#1B1918" stroke-width="2"/>
                        </svg>
                    </button>
                    <div class="row blogs-slider">
                        <div class="col-lg-4">
                            <div class="blog-card">
                                <figure>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/blogs/b-1.png" alt="">
                                </figure>
                                <div class="text-block">
                                    <div class="top-row mb-24">
                                        <div class="author">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/users/ua-1.png" alt="">
                                            <span class="bold-text color-primary">By: Williams</span>
                                        </div>
                                        <span class="date">14 April, 2024</span>
                                    </div>
                                    <a href="blog-detail.html" class="title h4 mb-16">A Beginner's Guide to Flower Arranging: Tips and Tricks</a>
                                    <p class="mb-24">Lorem ipsum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu libero aliquam est nullam sit.</p>
                                    <a href="blog-detail.html" class="cus-btn">
                                        Read More
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M23.1946 4.93105L21.5122 4.93097V3.18948C21.5122 2.79078 21.2205 2.45203 20.8262 2.39304C20.7573 2.3827 19.1184 2.14464 17.1448 2.50375C14.9447 2.90407 13.19 3.8913 12 5.38374C10.8098 3.8913 9.05516 2.90407 6.85509 2.50375C4.88144 2.14472 3.24249 2.3827 3.17373 2.39304C2.77938 2.45211 2.48767 2.79086 2.48767 3.18948V4.93097H0.805331C0.360645 4.93097 0 5.29162 0 5.73639V20.9125C0 21.1733 0.126222 21.4179 0.338827 21.569C0.551432 21.7201 0.823997 21.7589 1.07022 21.6731C1.13066 21.6521 7.16563 19.5925 11.6657 21.6452C11.8781 21.7421 12.1219 21.7421 12.3343 21.6452C16.8218 19.5983 22.8697 21.6523 22.9298 21.6731C23.0161 21.7031 23.1055 21.7179 23.1945 21.7179C23.3596 21.7179 23.523 21.6672 23.6612 21.5691C23.8738 21.4179 24 21.1733 24 20.9126V5.73647C23.9999 5.2917 23.6394 4.93105 23.1946 4.93105ZM1.61074 19.8349V6.54172H2.48767V17.267C2.48767 17.501 2.59005 17.7233 2.7671 17.8762C2.94415 18.0292 3.17963 18.0978 3.41106 18.0636C3.4555 18.0572 6.95416 17.5768 9.51027 19.3306C6.36159 18.7922 3.21025 19.415 1.61074 19.8349ZM11.1946 18.6043C10.0688 17.5871 8.60239 16.8993 6.85509 16.5814C6.0142 16.4283 5.23408 16.3837 4.61025 16.3837C4.42504 16.3837 4.25356 16.3877 4.09849 16.3939V3.92904H4.09841C5.64628 3.85922 9.41168 4.02471 11.1946 7.04717V18.6043ZM12.8053 7.04725C14.5818 4.03772 18.3531 3.86471 19.9015 3.93106V16.3939C19.2241 16.3666 18.2353 16.3829 17.1448 16.5814C15.3975 16.8993 13.9311 17.587 12.8053 18.6043V7.04725ZM14.487 19.3311C17.0434 17.5763 20.5439 18.0571 20.5876 18.0635C20.8196 18.0984 21.0549 18.0302 21.2325 17.8773C21.4101 17.7242 21.5122 17.5015 21.5122 17.267V6.5418H22.3892V19.8349C20.7892 19.4148 17.6365 18.7918 14.487 19.3311Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="blog-card">
                                <figure>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/blogs/b-2.png" alt="">
                                </figure>
                                <div class="text-block">
                                    <div class="top-row mb-24">
                                        <div class="author">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/users/ua-2.png" alt="">
                                            <span class="bold-text color-primary">By: Williams</span>
                                        </div>
                                        <span class="date">14 April, 2024</span>
                                    </div>
                                    <a href="blog-detail.html" class="title h4 mb-16">Exploring the Symbolism of Different Flowers</a>
                                    <p class="mb-24">Lorem ipsum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu libero aliquam est nullam sit.</p>
                                    <a href="blog-detail.html" class="cus-btn">
                                        Read More
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M23.1946 4.93105L21.5122 4.93097V3.18948C21.5122 2.79078 21.2205 2.45203 20.8262 2.39304C20.7573 2.3827 19.1184 2.14464 17.1448 2.50375C14.9447 2.90407 13.19 3.8913 12 5.38374C10.8098 3.8913 9.05516 2.90407 6.85509 2.50375C4.88144 2.14472 3.24249 2.3827 3.17373 2.39304C2.77938 2.45211 2.48767 2.79086 2.48767 3.18948V4.93097H0.805331C0.360645 4.93097 0 5.29162 0 5.73639V20.9125C0 21.1733 0.126222 21.4179 0.338827 21.569C0.551432 21.7201 0.823997 21.7589 1.07022 21.6731C1.13066 21.6521 7.16563 19.5925 11.6657 21.6452C11.8781 21.7421 12.1219 21.7421 12.3343 21.6452C16.8218 19.5983 22.8697 21.6523 22.9298 21.6731C23.0161 21.7031 23.1055 21.7179 23.1945 21.7179C23.3596 21.7179 23.523 21.6672 23.6612 21.5691C23.8738 21.4179 24 21.1733 24 20.9126V5.73647C23.9999 5.2917 23.6394 4.93105 23.1946 4.93105ZM1.61074 19.8349V6.54172H2.48767V17.267C2.48767 17.501 2.59005 17.7233 2.7671 17.8762C2.94415 18.0292 3.17963 18.0978 3.41106 18.0636C3.4555 18.0572 6.95416 17.5768 9.51027 19.3306C6.36159 18.7922 3.21025 19.415 1.61074 19.8349ZM11.1946 18.6043C10.0688 17.5871 8.60239 16.8993 6.85509 16.5814C6.0142 16.4283 5.23408 16.3837 4.61025 16.3837C4.42504 16.3837 4.25356 16.3877 4.09849 16.3939V3.92904H4.09841C5.64628 3.85922 9.41168 4.02471 11.1946 7.04717V18.6043ZM12.8053 7.04725C14.5818 4.03772 18.3531 3.86471 19.9015 3.93106V16.3939C19.2241 16.3666 18.2353 16.3829 17.1448 16.5814C15.3975 16.8993 13.9311 17.587 12.8053 18.6043V7.04725ZM14.487 19.3311C17.0434 17.5763 20.5439 18.0571 20.5876 18.0635C20.8196 18.0984 21.0549 18.0302 21.2325 17.8773C21.4101 17.7242 21.5122 17.5015 21.5122 17.267V6.5418H22.3892V19.8349C20.7892 19.4148 17.6365 18.7918 14.487 19.3311Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="blog-card">
                                <figure>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/blogs/b-3.png" alt="">
                                </figure>
                                <div class="text-block">
                                    <div class="top-row mb-24">
                                        <div class="author">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/users/ua-3.png" alt="">
                                            <span class="bold-text color-primary">By: Williams</span>
                                        </div>
                                        <span class="date">14 April, 2024</span>
                                    </div>
                                    <a href="blog-detail.html" class="title h4 mb-16">DIY Flower Crafts: Creative Projects for Flower Enthusiasts</a>
                                    <p class="mb-24">Lorem ipsum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu libero aliquam est nullam sit.</p>
                                    <a href="blog-detail.html" class="cus-btn">
                                        Read More
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M23.1946 4.93105L21.5122 4.93097V3.18948C21.5122 2.79078 21.2205 2.45203 20.8262 2.39304C20.7573 2.3827 19.1184 2.14464 17.1448 2.50375C14.9447 2.90407 13.19 3.8913 12 5.38374C10.8098 3.8913 9.05516 2.90407 6.85509 2.50375C4.88144 2.14472 3.24249 2.3827 3.17373 2.39304C2.77938 2.45211 2.48767 2.79086 2.48767 3.18948V4.93097H0.805331C0.360645 4.93097 0 5.29162 0 5.73639V20.9125C0 21.1733 0.126222 21.4179 0.338827 21.569C0.551432 21.7201 0.823997 21.7589 1.07022 21.6731C1.13066 21.6521 7.16563 19.5925 11.6657 21.6452C11.8781 21.7421 12.1219 21.7421 12.3343 21.6452C16.8218 19.5983 22.8697 21.6523 22.9298 21.6731C23.0161 21.7031 23.1055 21.7179 23.1945 21.7179C23.3596 21.7179 23.523 21.6672 23.6612 21.5691C23.8738 21.4179 24 21.1733 24 20.9126V5.73647C23.9999 5.2917 23.6394 4.93105 23.1946 4.93105ZM1.61074 19.8349V6.54172H2.48767V17.267C2.48767 17.501 2.59005 17.7233 2.7671 17.8762C2.94415 18.0292 3.17963 18.0978 3.41106 18.0636C3.4555 18.0572 6.95416 17.5768 9.51027 19.3306C6.36159 18.7922 3.21025 19.415 1.61074 19.8349ZM11.1946 18.6043C10.0688 17.5871 8.60239 16.8993 6.85509 16.5814C6.0142 16.4283 5.23408 16.3837 4.61025 16.3837C4.42504 16.3837 4.25356 16.3877 4.09849 16.3939V3.92904H4.09841C5.64628 3.85922 9.41168 4.02471 11.1946 7.04717V18.6043ZM12.8053 7.04725C14.5818 4.03772 18.3531 3.86471 19.9015 3.93106V16.3939C19.2241 16.3666 18.2353 16.3829 17.1448 16.5814C15.3975 16.8993 13.9311 17.587 12.8053 18.6043V7.04725ZM14.487 19.3311C17.0434 17.5763 20.5439 18.0571 20.5876 18.0635C20.8196 18.0984 21.0549 18.0302 21.2325 17.8773C21.4101 17.7242 21.5122 17.5015 21.5122 17.267V6.5418H22.3892V19.8349C20.7892 19.4148 17.6365 18.7918 14.487 19.3311Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="blog-card">
                                <figure>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/blogs/b-1.png" alt="">
                                </figure>
                                <div class="text-block">
                                    <div class="top-row mb-24">
                                        <div class="author">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/users/ua-1.png" alt="">
                                            <span class="bold-text color-primary">By: Williams</span>
                                        </div>
                                        <span class="date">14 April, 2024</span>
                                    </div>
                                    <a href="blog-detail.html" class="title h4 mb-16">A Beginner's Guide to Flower Arranging: Tips and Tricks</a>
                                    <p class="mb-24">Lorem ipsum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu libero aliquam est nullam sit.</p>
                                    <a href="blog-detail.html" class="cus-btn">
                                        Read More
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M23.1946 4.93105L21.5122 4.93097V3.18948C21.5122 2.79078 21.2205 2.45203 20.8262 2.39304C20.7573 2.3827 19.1184 2.14464 17.1448 2.50375C14.9447 2.90407 13.19 3.8913 12 5.38374C10.8098 3.8913 9.05516 2.90407 6.85509 2.50375C4.88144 2.14472 3.24249 2.3827 3.17373 2.39304C2.77938 2.45211 2.48767 2.79086 2.48767 3.18948V4.93097H0.805331C0.360645 4.93097 0 5.29162 0 5.73639V20.9125C0 21.1733 0.126222 21.4179 0.338827 21.569C0.551432 21.7201 0.823997 21.7589 1.07022 21.6731C1.13066 21.6521 7.16563 19.5925 11.6657 21.6452C11.8781 21.7421 12.1219 21.7421 12.3343 21.6452C16.8218 19.5983 22.8697 21.6523 22.9298 21.6731C23.0161 21.7031 23.1055 21.7179 23.1945 21.7179C23.3596 21.7179 23.523 21.6672 23.6612 21.5691C23.8738 21.4179 24 21.1733 24 20.9126V5.73647C23.9999 5.2917 23.6394 4.93105 23.1946 4.93105ZM1.61074 19.8349V6.54172H2.48767V17.267C2.48767 17.501 2.59005 17.7233 2.7671 17.8762C2.94415 18.0292 3.17963 18.0978 3.41106 18.0636C3.4555 18.0572 6.95416 17.5768 9.51027 19.3306C6.36159 18.7922 3.21025 19.415 1.61074 19.8349ZM11.1946 18.6043C10.0688 17.5871 8.60239 16.8993 6.85509 16.5814C6.0142 16.4283 5.23408 16.3837 4.61025 16.3837C4.42504 16.3837 4.25356 16.3877 4.09849 16.3939V3.92904H4.09841C5.64628 3.85922 9.41168 4.02471 11.1946 7.04717V18.6043ZM12.8053 7.04725C14.5818 4.03772 18.3531 3.86471 19.9015 3.93106V16.3939C19.2241 16.3666 18.2353 16.3829 17.1448 16.5814C15.3975 16.8993 13.9311 17.587 12.8053 18.6043V7.04725ZM14.487 19.3311C17.0434 17.5763 20.5439 18.0571 20.5876 18.0635C20.8196 18.0984 21.0549 18.0302 21.2325 17.8773C21.4101 17.7242 21.5122 17.5015 21.5122 17.267V6.5418H22.3892V19.8349C20.7892 19.4148 17.6365 18.7918 14.487 19.3311Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="blog-card">
                                <figure>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/blogs/b-2.png" alt="">
                                </figure>
                                <div class="text-block">
                                    <div class="top-row mb-24">
                                        <div class="author">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/users/ua-2.png" alt="">
                                            <span class="bold-text color-primary">By: Williams</span>
                                        </div>
                                        <span class="date">14 April, 2024</span>
                                    </div>
                                    <a href="blog-detail.html" class="title h4 mb-16">Exploring the Symbolism of Different Flowers</a>
                                    <p class="mb-24">Lorem ipsum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu libero aliquam est nullam sit.</p>
                                    <a href="blog-detail.html" class="cus-btn">
                                        Read More
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M23.1946 4.93105L21.5122 4.93097V3.18948C21.5122 2.79078 21.2205 2.45203 20.8262 2.39304C20.7573 2.3827 19.1184 2.14464 17.1448 2.50375C14.9447 2.90407 13.19 3.8913 12 5.38374C10.8098 3.8913 9.05516 2.90407 6.85509 2.50375C4.88144 2.14472 3.24249 2.3827 3.17373 2.39304C2.77938 2.45211 2.48767 2.79086 2.48767 3.18948V4.93097H0.805331C0.360645 4.93097 0 5.29162 0 5.73639V20.9125C0 21.1733 0.126222 21.4179 0.338827 21.569C0.551432 21.7201 0.823997 21.7589 1.07022 21.6731C1.13066 21.6521 7.16563 19.5925 11.6657 21.6452C11.8781 21.7421 12.1219 21.7421 12.3343 21.6452C16.8218 19.5983 22.8697 21.6523 22.9298 21.6731C23.0161 21.7031 23.1055 21.7179 23.1945 21.7179C23.3596 21.7179 23.523 21.6672 23.6612 21.5691C23.8738 21.4179 24 21.1733 24 20.9126V5.73647C23.9999 5.2917 23.6394 4.93105 23.1946 4.93105ZM1.61074 19.8349V6.54172H2.48767V17.267C2.48767 17.501 2.59005 17.7233 2.7671 17.8762C2.94415 18.0292 3.17963 18.0978 3.41106 18.0636C3.4555 18.0572 6.95416 17.5768 9.51027 19.3306C6.36159 18.7922 3.21025 19.415 1.61074 19.8349ZM11.1946 18.6043C10.0688 17.5871 8.60239 16.8993 6.85509 16.5814C6.0142 16.4283 5.23408 16.3837 4.61025 16.3837C4.42504 16.3837 4.25356 16.3877 4.09849 16.3939V3.92904H4.09841C5.64628 3.85922 9.41168 4.02471 11.1946 7.04717V18.6043ZM12.8053 7.04725C14.5818 4.03772 18.3531 3.86471 19.9015 3.93106V16.3939C19.2241 16.3666 18.2353 16.3829 17.1448 16.5814C15.3975 16.8993 13.9311 17.587 12.8053 18.6043V7.04725ZM14.487 19.3311C17.0434 17.5763 20.5439 18.0571 20.5876 18.0635C20.8196 18.0984 21.0549 18.0302 21.2325 17.8773C21.4101 17.7242 21.5122 17.5015 21.5122 17.267V6.5418H22.3892V19.8349C20.7892 19.4148 17.6365 18.7918 14.487 19.3311Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="blog-card">
                                <figure>
                                    <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/blogs/b-3.png" alt="">
                                </figure>
                                <div class="text-block">
                                    <div class="top-row mb-24">
                                        <div class="author">
                                            <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/users/ua-3.png" alt="">
                                            <span class="bold-text color-primary">By: Williams</span>
                                        </div>
                                        <span class="date">14 April, 2024</span>
                                    </div>
                                    <a href="blog-detail.html" class="title h4 mb-16">DIY Flower Crafts: Creative Projects for Flower Enthusiasts</a>
                                    <p class="mb-24">Lorem ipsum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu libero aliquam est nullam sit.</p>
                                    <a href="blog-detail.html" class="cus-btn">
                                        Read More
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                            <path d="M23.1946 4.93105L21.5122 4.93097V3.18948C21.5122 2.79078 21.2205 2.45203 20.8262 2.39304C20.7573 2.3827 19.1184 2.14464 17.1448 2.50375C14.9447 2.90407 13.19 3.8913 12 5.38374C10.8098 3.8913 9.05516 2.90407 6.85509 2.50375C4.88144 2.14472 3.24249 2.3827 3.17373 2.39304C2.77938 2.45211 2.48767 2.79086 2.48767 3.18948V4.93097H0.805331C0.360645 4.93097 0 5.29162 0 5.73639V20.9125C0 21.1733 0.126222 21.4179 0.338827 21.569C0.551432 21.7201 0.823997 21.7589 1.07022 21.6731C1.13066 21.6521 7.16563 19.5925 11.6657 21.6452C11.8781 21.7421 12.1219 21.7421 12.3343 21.6452C16.8218 19.5983 22.8697 21.6523 22.9298 21.6731C23.0161 21.7031 23.1055 21.7179 23.1945 21.7179C23.3596 21.7179 23.523 21.6672 23.6612 21.5691C23.8738 21.4179 24 21.1733 24 20.9126V5.73647C23.9999 5.2917 23.6394 4.93105 23.1946 4.93105ZM1.61074 19.8349V6.54172H2.48767V17.267C2.48767 17.501 2.59005 17.7233 2.7671 17.8762C2.94415 18.0292 3.17963 18.0978 3.41106 18.0636C3.4555 18.0572 6.95416 17.5768 9.51027 19.3306C6.36159 18.7922 3.21025 19.415 1.61074 19.8349ZM11.1946 18.6043C10.0688 17.5871 8.60239 16.8993 6.85509 16.5814C6.0142 16.4283 5.23408 16.3837 4.61025 16.3837C4.42504 16.3837 4.25356 16.3877 4.09849 16.3939V3.92904H4.09841C5.64628 3.85922 9.41168 4.02471 11.1946 7.04717V18.6043ZM12.8053 7.04725C14.5818 4.03772 18.3531 3.86471 19.9015 3.93106V16.3939C19.2241 16.3666 18.2353 16.3829 17.1448 16.5814C15.3975 16.8993 13.9311 17.587 12.8053 18.6043V7.04725ZM14.487 19.3311C17.0434 17.5763 20.5439 18.0571 20.5876 18.0635C20.8196 18.0984 21.0549 18.0302 21.2325 17.8773C21.4101 17.7242 21.5122 17.5015 21.5122 17.267V6.5418H22.3892V19.8349C20.7892 19.4148 17.6365 18.7918 14.487 19.3311Z"/>
                                        </svg>
                                        <span></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-arrows d-lg-none mt-48">
                    <button class="arrow prev-btn" data-slide="blogs-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="33" height="32" viewBox="0 0 33 32" fill="none">
                            <path d="M12.8057 23C12.8057 20 10.0057 16 6.80566 16M6.80566 16C8.639 16 12.8057 15 12.8057 9M6.80566 16H25.8057" stroke="#1B1918" stroke-width="2"/>
                        </svg>
                    </button> 
                    <button class="arrow next-btn" data-slide="blogs-slider">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none">
                            <path d="M19.3545 23C19.3545 20 22.1545 16 25.3545 16M25.3545 16C23.5212 16 19.3545 15 19.3545 9M25.3545 16H6.35449" stroke="#1B1918" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
            </div>
        </section>
        <!-- Blogs Area End --> 

        <!-- Footer Section Start -->
        <footer class="footer pt-80">
            <!-- Footer Newsletter Start -->
            <div class="footer-newsletter mb-80">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-xl-10 col-md-11">
                            <!-- Newsletter Box Start -->
                            <div class="newsletter-box">
                                <div class="row align-items-center row-gap-4">
                                    <div class="col-lg-6">
                                        <!-- Section Title Start -->
                                        <div class="newsletter-title">
                                            <h3 class="mb-16">Subscribe to our newsletter</h3>
                                            <p>Lorem ipsum dolor sit amet consectetur. Mauris amet<br> ultrices aliquet arcu libero.</p>
                                        </div>
                                        <!-- Section Title End -->
                                    </div>
                                    <div class="col-lg-6">
                                        <!-- Newsletter Form Start -->
                                        <form action="index.html">
                                            <div class="newsletter-field">
                                                <input type="email" name="newsletter" id="newsletter" placeholder="Your Email" required>
                                                <button type="submit" class="cus-btn">
                                                    Subscribe
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                                        <path d="M19.8739 7.98103C19.53 7.98103 19.2038 8.05711 18.9102 8.19234C18.6749 7.16583 17.7548 6.3975 16.658 6.3975C16.3065 6.3975 15.9733 6.47667 15.6748 6.61762C15.4112 5.63128 14.5103 4.90252 13.442 4.90252C13.121 4.90252 12.8151 4.96842 12.5371 5.0872V2.31112C12.5371 1.03673 11.5004 0 10.226 0C8.95161 0 7.91483 1.03673 7.91483 2.31112L7.91483 13.3079L6.72318 11.5389L6.70452 11.5157C5.8123 10.4066 4.2426 10.1601 3.05343 10.9425C2.43158 11.3516 2.01032 11.9791 1.86716 12.7095C1.7249 13.4354 1.87541 14.171 2.29096 14.7823L6.54388 21.4861L6.55804 21.5076C7.62997 23.0682 9.4003 24 11.2936 24H16.0657C19.4399 24 22.185 21.2549 22.185 17.8807V10.2921C22.185 9.01777 21.1483 7.98103 19.8739 7.98103ZM20.7788 17.8807C20.7788 20.4795 18.6645 22.5937 16.0657 22.5937H11.2936C9.86783 22.5937 8.53447 21.8941 7.72429 20.7216L3.47197 14.0188L3.45782 13.9974C3.25208 13.6978 3.17732 13.3365 3.24716 12.9799C3.31705 12.6234 3.52274 12.317 3.8263 12.1173C4.39935 11.7403 5.15347 11.8527 5.5918 12.3765L9.32108 17.9123V2.31112C9.32108 1.81214 9.72702 1.40625 10.226 1.40625C10.725 1.40625 11.1309 1.81214 11.1309 2.31112V10.6528H12.5371V7.21369C12.5371 6.7147 12.943 6.30881 13.442 6.30881C13.941 6.30881 14.3469 6.7147 14.3469 7.21369V10.6528H15.7531V8.70862C15.7531 8.20964 16.159 7.80375 16.658 7.80375C17.157 7.80375 17.5629 8.20964 17.5629 8.70862V10.6528H18.9691V10.2922C18.9691 9.79317 19.375 9.38728 19.874 9.38728C20.373 9.38728 20.7789 9.79317 20.7789 10.2922V17.8807H20.7788Z"/>
                                                    </svg>
                                                    <span></span>
                                                </button>
                                            </div>
                                        </form>
                                        <!-- Newsletter Form End -->
                                    </div>
                                </div>
                            </div>
                            <!-- Newsletter Box End -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer Newsletter End -->

            <!-- Footer Main Start -->
            <div class="footer-main">
                <div class="container-fluid">
                    <div class="row row-gap-4  mb-32">
                        <div class="col-lg-3 col-md-6 col-sm-6 order-lg-1">
                            <div class="footer-widget">
                                <a href="index.html" class="mb-16"><img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/logo.png" alt=""></a>
                                <p class="mb-24">Lorem ipsum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu libero aliquam est nullam sit. psum dolor sit amet consectetur. Mauris amet ultrices aliquet arcu libero aliquam...</p>
                                <div class="time">
                                    <div class="icon"><i class="fa-light fa-clock"></i></div>
                                    <p>
                                        <span class="accent-dark">MON - FRI</span><br>
                                        <span class="accent-dark">08:00 AM - 05:00 PM</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-7 order-lg-5">
                            <div class="footer-widget">
                                <h4 class="mb-32">Contact Us</h4>
                                <ul class="unstyled contacts-list mb-32">
                                    <li><a href="" class="accent-dark"><span class="icon"><i class="fa-light fa-phone"></i></span>+1 123 456 789</a></li>
                                    <li><a href="" class="accent-dark"><span class="icon"><i class="fa-light fa-envelope"></i></span>example@gmail.com</a></li>
                                    <li><p class="accent-dark"><span class="icon"><i class="fa-light fa-location-dot"></i></span>Suite 600, 1201 Broadway</p></li>
                                </ul>
                                <ul class="unstyled social-icons">
                                    <li><a href=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M17.625 5.625C18.0131 5.625 18.3281 5.31 18.3281 4.92188V0.703125C18.3281 0.315 18.0131 0 17.625 0H13.4062C10.6922 0 8.48437 2.20781 8.48437 4.92188V8.4375H6.375C5.98687 8.4375 5.67188 8.7525 5.67188 9.14062V13.3594C5.67188 13.7475 5.98687 14.0625 6.375 14.0625H8.48437V23.2969C8.48437 23.685 8.79937 24 9.1875 24H13.4062C13.7944 24 14.1094 23.685 14.1094 23.2969V14.0625H16.9219C17.2655 14.0625 17.5589 13.8141 17.6156 13.4752L18.3188 9.25641C18.3525 9.0525 18.2953 8.84391 18.1617 8.68594C18.0281 8.52844 17.8317 8.4375 17.625 8.4375H14.1094V5.625H17.625ZM13.4062 9.84375H16.7948L16.3261 12.6562H13.4062C13.0181 12.6562 12.7031 12.9713 12.7031 13.3594V22.5938H9.89062V13.3594C9.89062 12.9713 9.57562 12.6562 9.1875 12.6562H7.07812V9.84375H9.1875C9.57562 9.84375 9.89062 9.52875 9.89062 9.14062V4.92188C9.89062 2.98359 11.468 1.40625 13.4062 1.40625H16.9219V4.21875H13.4062C13.0181 4.21875 12.7031 4.53375 12.7031 4.92188V9.14062C12.7031 9.52875 13.0181 9.84375 13.4062 9.84375Z" /></svg></a></li>
                                    <li><a href=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M14.2418 10.1624L22.9842 0H20.9125L13.3215 8.82384L7.25852 0H0.265625L9.43399 13.3432L0.265625 24H2.33742L10.3538 14.6817L16.7567 24H23.7496L14.2413 10.1624H14.2418ZM11.4042 13.4608L10.4752 12.1321L3.08391 1.55961H6.26607L12.2309 10.0919L13.1599 11.4206L20.9135 22.5113H17.7313L11.4042 13.4613V13.4608Z"/></svg></a></li>
                                    <li><a href=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M12.2362 24C12.1567 24 12.0773 24 11.9973 23.9996C10.116 24.0042 8.37781 23.9564 6.68738 23.8535C5.13757 23.7592 3.7229 23.2236 2.59607 22.3048C1.50879 21.4182 0.766296 20.2194 0.389282 18.7421C0.0611572 17.456 0.0437622 16.1935 0.0270996 14.9723C0.0150146 14.0962 0.00256348 13.058 0 12.0022C0.00256348 10.942 0.0150146 9.9038 0.0270996 9.02764C0.0437622 7.80669 0.0611572 6.54418 0.389282 5.25787C0.766296 3.78057 1.50879 2.58178 2.59607 1.69518C3.7229 0.776361 5.13757 0.240777 6.68756 0.146478C8.37799 0.0437559 10.1166 -0.00421772 12.0018 0.000359916C13.8836 -0.0036684 15.6213 0.0437559 17.3117 0.146478C18.8615 0.240777 20.2762 0.776361 21.403 1.69518C22.4905 2.58178 23.2328 3.78057 23.6098 5.25787C23.9379 6.544 23.9553 7.80669 23.972 9.02764C23.9841 9.9038 23.9967 10.942 23.9991 11.9978V12.0022C23.9967 13.058 23.9841 14.0962 23.972 14.9723C23.9553 16.1933 23.9381 17.4558 23.6098 18.7421C23.2328 20.2194 22.4905 21.4182 21.403 22.3048C20.2762 23.2236 18.8615 23.7592 17.3117 23.8535C15.6929 23.952 14.0299 24 12.2362 24ZM11.9973 22.5132C13.8479 22.5176 15.5471 22.4707 17.1978 22.3703C18.3697 22.2991 19.752 21.5304 20.5844 20.8517C21.3538 20.2242 21.8837 19.3584 22.1593 18.2785C22.4325 17.2079 22.4482 16.0583 22.4634 14.9467C22.4753 14.0764 22.4878 13.0455 22.4903 12C22.4878 10.9543 22.4753 9.92358 22.4634 9.05328C22.4482 7.94164 22.4325 6.79211 22.1593 5.72131C21.8837 4.64135 21.3538 3.77563 20.5844 3.14813C19.752 2.46954 18.3697 1.72338 17.1978 1.65215C15.5471 1.55162 13.8479 1.5053 12.0016 1.50933C10.1514 1.50493 8.45196 1.55712 6.80127 1.65765C5.62939 1.72888 4.37526 2.23146 3.54286 2.91005C2.77346 3.53755 2.10105 4.64135 1.82548 5.72131C1.55229 6.79211 1.53654 7.94146 1.52134 9.05328C1.50944 9.92431 1.49699 10.9557 1.49443 12.0022C1.49699 13.0441 1.50944 14.0757 1.52134 14.9467C1.53654 16.0583 1.55229 17.2079 1.82548 18.2785C2.10105 19.3584 2.63096 20.2242 3.40037 20.8517C4.23277 21.5303 5.62939 22.2991 6.80127 22.3703C8.45196 22.4709 10.1517 22.5177 11.9973 22.5132ZM11.9526 17.8594C8.72186 17.8594 6.0932 15.2309 6.0932 12C6.0932 8.7691 8.72186 6.14062 11.9526 6.14062C15.1835 6.14062 17.8119 8.7691 17.8119 12C17.8119 15.2309 15.1835 17.8594 11.9526 17.8594ZM12.0016 7.50495C9.36718 7.50495 7.51314 9.35899 7.51314 11.9978C7.51314 14.1947 9.17436 16.5131 11.9757 16.5131C14.1728 16.5131 16.457 14.4316 16.457 11.9978C16.457 9.8009 14.781 7.50495 12.0016 7.50495ZM18.4682 4.26562C17.6916 4.26562 17.0619 4.89513 17.0619 5.67187C17.0619 6.4486 17.6916 7.07812 18.4682 7.07812C19.2449 7.07812 19.8744 6.4486 19.8744 5.67187C19.8744 4.89513 19.2449 4.26562 18.4682 4.26562Z"/></svg></a></li>
                                    <li><a href=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M5.64368 7.53113H1.125C0.736633 7.53113 0.421875 7.84607 0.421875 8.23425V23.2969C0.421875 23.6852 0.736633 24 1.125 24H5.64368C6.03204 24 6.3468 23.6852 6.3468 23.2969V8.23425C6.3468 7.84607 6.03204 7.53113 5.64368 7.53113ZM4.94055 22.5938H1.82812V8.93738H4.94055V22.5938Z"/><path d="M3.38452 0C1.75085 0 0.421875 1.32898 0.421875 2.96228C0.421875 4.59595 1.75085 5.92474 3.38452 5.92474C5.01801 5.92474 6.3468 4.59576 6.3468 2.96228C6.3468 1.32898 5.01801 0 3.38452 0ZM3.38452 4.51849C2.52631 4.51849 1.82812 3.8205 1.82812 2.96228C1.82812 2.10425 2.52631 1.40625 3.38452 1.40625C4.24255 1.40625 4.94055 2.10425 4.94055 2.96228C4.94055 3.8205 4.24255 4.51849 3.38452 4.51849Z"/><path d="M16.9411 7.4317C15.8723 7.4317 14.8189 7.68933 13.8755 8.17126C13.8435 7.81256 13.5423 7.53113 13.1752 7.53113H8.65613C8.26794 7.53113 7.953 7.84607 7.953 8.23425V23.2969C7.953 23.6852 8.26794 24 8.65613 24H13.1752C13.5635 24 13.8783 23.6852 13.8783 23.2969V15.0125C13.8783 13.9468 14.7455 13.0798 15.8112 13.0798C16.8768 13.0798 17.7437 13.9468 17.7437 15.0125V23.2969C17.7437 23.6852 18.0586 24 18.4468 24H22.9656C23.354 24 23.6688 23.6852 23.6688 23.2969V14.1594C23.6688 10.4496 20.6508 7.4317 16.9411 7.4317ZM22.2625 22.5938H19.1501V15.0125C19.1501 13.1713 17.6523 11.6735 15.8113 11.6735C13.97 11.6735 12.472 13.1713 12.472 15.0125V22.5938H9.35944V8.93738H12.472V9.45685C12.472 9.72729 12.6271 9.97375 12.871 10.0908C13.1147 10.2078 13.4041 10.1744 13.6152 10.0054C14.5673 9.2417 15.7176 8.83795 16.9411 8.83795C19.8754 8.83795 22.2625 11.2251 22.2625 14.1594V22.5938Z"/></svg></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 col-5 order-lg-3">
                            <div class="footer-widget">
                                <h4 class="mb-32">Shop</h4>
                                <ul class="unstyled links-list">
                                    <li><a href="about.html">About Us</a></li>
                                    <li><a href="shop-grid.html">Product Items</a></li>
                                    <li><a href="contact.html">Contact us</a></li>
                                    <li><a href="checkout.html">Checkout</a></li>
                                    <li><a href="wishlist.html">Wishlist</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 col-6 order-lg-2">
                            <div class="footer-widget">
                                <h4 class="mb-32">Useful Links</h4>
                                <ul class="unstyled links-list">
                                    <li><a href="about.html">About Us</a></li>
                                    <li><a href="shop-grid.html">Product Items</a></li>
                                    <li><a href="contact.html">Contact us</a></li>
                                    <li><a href="checkout.html">Checkout</a></li>
                                    <li><a href="wishlist.html">Wishlist</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 col-6 order-lg-4">
                            <div class="footer-widget">
                                <h4 class="mb-32">Information</h4>
                                <ul class="unstyled links-list">
                                    <li><a href="about.html">About Us</a></li>
                                    <li><a href="shop-grid.html">Product Items</a></li>
                                    <li><a href="contact.html">Contact us</a></li>
                                    <li><a href="checkout.html">Checkout</a></li>
                                    <li><a href="wishlist.html">Wishlist</a></li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>
                    <hr class="dash-line">
                    <div class="footer-bottom">
                        <div class="row row-gap-4">
                            <div class="col-sm-9">
                                <p class="accent-dark text-sm-start text-center">@2025 All Rights Copyright Bloom. Design & Developed By UIPARADOX</p>
                            </div>
                            <div class="col-sm-3 text-sm-end text-center">
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/icons/payments.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer Main End -->


        </footer>
        <!-- Footer Section End -->

    </main>

    <!-- Mobile Menu Start -->
    <div class="mobile-nav__wrapper">
        <div class="mobile-nav__overlay mobile-nav__toggler"></div>
        <div class="mobile-nav__content">
            <span class="mobile-nav__close mobile-nav__toggler"></span>
            <div class="logo-box">
                <a href="index.html" aria-label="logo image"><img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/logo.png" alt=""></a>
            </div>
            <div class="mobile-nav__container"></div>
            <ul class="mobile-nav__contact list-unstyled">
                <li>
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:example@company.com">example@company.com</a>
                </li>
                <li>
                    <i class="fa fa-phone-alt"></i>
                    <a href="tel:+12345678">+123 (4567) -890</a>
                </li>
            </ul>
        </div>
    </div>
    <!-- Mobile Menu End -->

    <!--  Product Eye View Popup Start -->
    <div class="modal fade" id="productQuickView" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog">
            <div class="row justify-content-center">
                <div class="col-xxl-8 col-xl-10 col-lg-11">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="close-icon mb-8">
                                <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close"><i class="fa-light fa-xmark"></i></button>
                            </div>
                            <div class="product-detail">
                                <div class="row row-gap-4">
                                    <!-- product-detail-image-slider -->
                                    <div class="col-md-6">
                                        <div class="row align-items-center row-gap-3">
                                            <div class="list">
                                                <button class="slider-btn prev-btn" data-slide="preview-slider-nav">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 17 16" fill="none">
                                                        <path d="M0.857543 12.1506C1.14152 12.4346 1.60203 12.4347 1.88605 12.1506L8.64436 5.39213L15.403 12.1506C15.687 12.4346 16.1475 12.4347 16.4315 12.1506C16.7155 11.8666 16.7155 11.4061 16.4315 11.1221L9.15859 3.84935C9.0222 3.71296 8.83723 3.63635 8.64436 3.63635C8.45148 3.63635 8.26647 3.71301 8.13013 3.84939L0.857592 11.1221C0.573519 11.4061 0.573519 11.8666 0.857543 12.1506Z"/>
                                                    </svg>
                                                </button> 
                                                <div class="wrap-modal-slider">
                                                    <div class="preview-slider-nav mt-3">
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/product-image-slider-1.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/product-image-slider-2.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/product-image-slider-3.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/product-image-slider-4.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/product-image-slider-1.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/product-image-slider-2.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/product-image-slider-3.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/product-image-slider-4.png">
                                                        </div>
                                                    </div>
                                                </div>
                                                 <button class="slider-btn next-btn" data-slide="preview-slider-nav">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 17 16" fill="none">
                                                        <path d="M16.4315 3.84935C16.1475 3.56537 15.687 3.56532 15.403 3.84939L8.6447 10.6078L1.88606 3.84935C1.60208 3.56537 1.14157 3.56532 0.857549 3.84939C0.573525 4.13342 0.573525 4.59388 0.857549 4.8779L8.13047 12.1506C8.26686 12.287 8.45183 12.3636 8.6447 12.3636C8.83757 12.3636 9.02259 12.287 9.15893 12.1506L16.4315 4.87786C16.7155 4.59388 16.7155 4.13337 16.4315 3.84935Z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="preview">
                                                <div class="wrap-modal-slider">
                                                    <div class="preview-slider">
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/pd-1.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/pd-2.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/pd-3.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/pd-4.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/pd-1.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/pd-2.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/pd-3.png">
                                                        </div>
                                                        <div class="detail-img-block">
                                                            <img alt=""  src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/pd-4.png">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Product-detail -->
                                    <div class="col-md-6">
                                        <div class="product-detail-content">
                                            <div class="d-flex align-items-center column-gap-2 row-gap-3 mb-16">
                                                <h4>Jasmine Junction</h4>
                                                <p class="green-tag">In Stock</p>
                                            </div>
                                            <ul class="unstyled mb-24 pro-rel">
                                                <li class="d-flex align-items-center">
                                                    <span class="rating-stars me-1"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
                                                    <span class="text-decoration-underline">2 Reviews</span>
                                                </li>
                                                <li>
                                                    <span class="bold-text accent-dark me-1">SKU:</span><span>3,24,672</span>
                                                </li>
                                            </ul>
                                            <div class="d-flex align-items-center column-gap-2 row-gap-3 mb-24">
                                                <div class="price">
                                                    <del class="h6 dark-gray">$50.00</del>
                                                    <h3>$40.00</h3>
                                                </div>
                                                <p class="red-tag">10% off</p>
                                            </div>
                                            <p class="mb-24">Lorem ipsum dolor sit amet consectetur. Mauris volutpat sollicitudin nunc nisl. Ac euismod lorem odio consequat.</p>
                                            <hr class="dash-line mb-16">
                                            <div class="action-block mb-16">
                                                <div class="quantity-wrap">
                                                    <div class="decrement"><i class="fa-solid fa-dash"></i></div>
                                                    <input type="text" name="quantity" value="1" maxlength="1" size="1" class="number">
                                                    <div class="increment"><i class="fa-solid fa-plus-large"></i></div>
                                                </div>
                                                <a href="cart.html" class="cus-btn w-100">
                                                    Shop Now
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                                        <path d="M7.73254 15.5158H7.73364L7.73638 15.5156H20.4844C20.7982 15.5156 21.0741 15.3074 21.1604 15.0057L23.9729 5.16192C24.0335 4.9497 23.991 4.72156 23.8583 4.54541C23.7253 4.36926 23.5175 4.26562 23.2969 4.26562H6.11096L5.60833 2.00372C5.53674 1.68201 5.25146 1.45312 4.92187 1.45312H0.703125C0.314758 1.45312 0 1.76788 0 2.15625C0 2.54462 0.314758 2.85937 0.703125 2.85937H4.35791C4.4469 3.26019 6.76318 13.6836 6.89648 14.2833C6.14923 14.6081 5.625 15.3532 5.625 16.2187C5.625 17.3818 6.57129 18.3281 7.73437 18.3281H20.4844C20.8727 18.3281 21.1875 18.0134 21.1875 17.625C21.1875 17.2366 20.8727 16.9219 20.4844 16.9219H7.73437C7.34674 16.9219 7.03125 16.6064 7.03125 16.2187C7.03125 15.8317 7.34564 15.5167 7.73254 15.5158ZM22.3647 5.67187L19.9539 14.1094H8.29834L6.42334 5.67187H22.3647Z"/>
                                                        <path d="M7.03125 20.4375C7.03125 21.6006 7.97753 22.5469 9.14062 22.5469C10.3037 22.5469 11.25 21.6006 11.25 20.4375C11.25 19.2744 10.3037 18.3281 9.14062 18.3281C7.97753 18.3281 7.03125 19.2744 7.03125 20.4375ZM9.14062 19.7344C9.52825 19.7344 9.84374 20.0499 9.84374 20.4375C9.84374 20.8251 9.52825 21.1406 9.14062 21.1406C8.75299 21.1406 8.43749 20.8251 8.43749 20.4375C8.43749 20.0499 8.75299 19.7344 9.14062 19.7344Z"/>
                                                        <path d="M16.9687 20.4375C16.9687 21.6006 17.915 22.5469 19.0781 22.5469C20.2412 22.5469 21.1875 21.6006 21.1875 20.4375C21.1875 19.2744 20.2412 18.3281 19.0781 18.3281C17.915 18.3281 16.9687 19.2744 16.9687 20.4375ZM19.0781 19.7344C19.4657 19.7344 19.7812 20.0499 19.7812 20.4375C19.7812 20.8251 19.4657 21.1406 19.0781 21.1406C18.6905 21.1406 18.375 20.8251 18.375 20.4375C18.375 20.0499 18.6905 19.7344 19.0781 19.7344Z"/>
                                                    </svg>
                                                    <span></span>
                                                </a>
                                                <a href="javascript:;" class="icon wishlist-icon"><i class="fa-light fa-heart"></i></a>
                                            </div>
                                            <hr class="dash-line mb-24">
                                            <div class="mb-16">
                                                <span class="bold-text accent-dark me-1">SKU:</span><span>Flower</span>
                                            </div>
                                            <div class="tags mb-24">
                                                <span class="bold-text accent-dark me-1">Tag:</span><span class="me-1">Birthday Bouquets, </span><span class="me-1 active">Flower,</span><span>Rose</span>
                                            </div>
                                            <hr class="dash-line mb-16">
                                            <div class="d-flex justify-content-between align-items-center gap-2 mb-16">
                                                <span class="bold-text accent-dark">Share:</span>
                                                <ul class="unstyled social-icons">
                                                    <li><a href=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M17.625 5.625C18.0131 5.625 18.3281 5.31 18.3281 4.92188V0.703125C18.3281 0.315 18.0131 0 17.625 0H13.4062C10.6922 0 8.48437 2.20781 8.48437 4.92188V8.4375H6.375C5.98687 8.4375 5.67188 8.7525 5.67188 9.14062V13.3594C5.67188 13.7475 5.98687 14.0625 6.375 14.0625H8.48437V23.2969C8.48437 23.685 8.79937 24 9.1875 24H13.4062C13.7944 24 14.1094 23.685 14.1094 23.2969V14.0625H16.9219C17.2655 14.0625 17.5589 13.8141 17.6156 13.4752L18.3188 9.25641C18.3525 9.0525 18.2953 8.84391 18.1617 8.68594C18.0281 8.52844 17.8317 8.4375 17.625 8.4375H14.1094V5.625H17.625ZM13.4062 9.84375H16.7948L16.3261 12.6562H13.4062C13.0181 12.6562 12.7031 12.9713 12.7031 13.3594V22.5938H9.89062V13.3594C9.89062 12.9713 9.57562 12.6562 9.1875 12.6562H7.07812V9.84375H9.1875C9.57562 9.84375 9.89062 9.52875 9.89062 9.14062V4.92188C9.89062 2.98359 11.468 1.40625 13.4062 1.40625H16.9219V4.21875H13.4062C13.0181 4.21875 12.7031 4.53375 12.7031 4.92188V9.14062C12.7031 9.52875 13.0181 9.84375 13.4062 9.84375Z" /></svg></a></li>
                                                    <li><a href=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M14.2418 10.1624L22.9842 0H20.9125L13.3215 8.82384L7.25852 0H0.265625L9.43399 13.3432L0.265625 24H2.33742L10.3538 14.6817L16.7567 24H23.7496L14.2413 10.1624H14.2418ZM11.4042 13.4608L10.4752 12.1321L3.08391 1.55961H6.26607L12.2309 10.0919L13.1599 11.4206L20.9135 22.5113H17.7313L11.4042 13.4613V13.4608Z"/></svg></a></li>
                                                    <li><a href=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M12.2362 24C12.1567 24 12.0773 24 11.9973 23.9996C10.116 24.0042 8.37781 23.9564 6.68738 23.8535C5.13757 23.7592 3.7229 23.2236 2.59607 22.3048C1.50879 21.4182 0.766296 20.2194 0.389282 18.7421C0.0611572 17.456 0.0437622 16.1935 0.0270996 14.9723C0.0150146 14.0962 0.00256348 13.058 0 12.0022C0.00256348 10.942 0.0150146 9.9038 0.0270996 9.02764C0.0437622 7.80669 0.0611572 6.54418 0.389282 5.25787C0.766296 3.78057 1.50879 2.58178 2.59607 1.69518C3.7229 0.776361 5.13757 0.240777 6.68756 0.146478C8.37799 0.0437559 10.1166 -0.00421772 12.0018 0.000359916C13.8836 -0.0036684 15.6213 0.0437559 17.3117 0.146478C18.8615 0.240777 20.2762 0.776361 21.403 1.69518C22.4905 2.58178 23.2328 3.78057 23.6098 5.25787C23.9379 6.544 23.9553 7.80669 23.972 9.02764C23.9841 9.9038 23.9967 10.942 23.9991 11.9978V12.0022C23.9967 13.058 23.9841 14.0962 23.972 14.9723C23.9553 16.1933 23.9381 17.4558 23.6098 18.7421C23.2328 20.2194 22.4905 21.4182 21.403 22.3048C20.2762 23.2236 18.8615 23.7592 17.3117 23.8535C15.6929 23.952 14.0299 24 12.2362 24ZM11.9973 22.5132C13.8479 22.5176 15.5471 22.4707 17.1978 22.3703C18.3697 22.2991 19.752 21.5304 20.5844 20.8517C21.3538 20.2242 21.8837 19.3584 22.1593 18.2785C22.4325 17.2079 22.4482 16.0583 22.4634 14.9467C22.4753 14.0764 22.4878 13.0455 22.4903 12C22.4878 10.9543 22.4753 9.92358 22.4634 9.05328C22.4482 7.94164 22.4325 6.79211 22.1593 5.72131C21.8837 4.64135 21.3538 3.77563 20.5844 3.14813C19.752 2.46954 18.3697 1.72338 17.1978 1.65215C15.5471 1.55162 13.8479 1.5053 12.0016 1.50933C10.1514 1.50493 8.45196 1.55712 6.80127 1.65765C5.62939 1.72888 4.37526 2.23146 3.54286 2.91005C2.77346 3.53755 2.10105 4.64135 1.82548 5.72131C1.55229 6.79211 1.53654 7.94146 1.52134 9.05328C1.50944 9.92431 1.49699 10.9557 1.49443 12.0022C1.49699 13.0441 1.50944 14.0757 1.52134 14.9467C1.53654 16.0583 1.55229 17.2079 1.82548 18.2785C2.10105 19.3584 2.63096 20.2242 3.40037 20.8517C4.23277 21.5303 5.62939 22.2991 6.80127 22.3703C8.45196 22.4709 10.1517 22.5177 11.9973 22.5132ZM11.9526 17.8594C8.72186 17.8594 6.0932 15.2309 6.0932 12C6.0932 8.7691 8.72186 6.14062 11.9526 6.14062C15.1835 6.14062 17.8119 8.7691 17.8119 12C17.8119 15.2309 15.1835 17.8594 11.9526 17.8594ZM12.0016 7.50495C9.36718 7.50495 7.51314 9.35899 7.51314 11.9978C7.51314 14.1947 9.17436 16.5131 11.9757 16.5131C14.1728 16.5131 16.457 14.4316 16.457 11.9978C16.457 9.8009 14.781 7.50495 12.0016 7.50495ZM18.4682 4.26562C17.6916 4.26562 17.0619 4.89513 17.0619 5.67187C17.0619 6.4486 17.6916 7.07812 18.4682 7.07812C19.2449 7.07812 19.8744 6.4486 19.8744 5.67187C19.8744 4.89513 19.2449 4.26562 18.4682 4.26562Z"/></svg></a></li>
                                                    <li><a href=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path d="M5.64368 7.53113H1.125C0.736633 7.53113 0.421875 7.84607 0.421875 8.23425V23.2969C0.421875 23.6852 0.736633 24 1.125 24H5.64368C6.03204 24 6.3468 23.6852 6.3468 23.2969V8.23425C6.3468 7.84607 6.03204 7.53113 5.64368 7.53113ZM4.94055 22.5938H1.82812V8.93738H4.94055V22.5938Z"/><path d="M3.38452 0C1.75085 0 0.421875 1.32898 0.421875 2.96228C0.421875 4.59595 1.75085 5.92474 3.38452 5.92474C5.01801 5.92474 6.3468 4.59576 6.3468 2.96228C6.3468 1.32898 5.01801 0 3.38452 0ZM3.38452 4.51849C2.52631 4.51849 1.82812 3.8205 1.82812 2.96228C1.82812 2.10425 2.52631 1.40625 3.38452 1.40625C4.24255 1.40625 4.94055 2.10425 4.94055 2.96228C4.94055 3.8205 4.24255 4.51849 3.38452 4.51849Z"/><path d="M16.9411 7.4317C15.8723 7.4317 14.8189 7.68933 13.8755 8.17126C13.8435 7.81256 13.5423 7.53113 13.1752 7.53113H8.65613C8.26794 7.53113 7.953 7.84607 7.953 8.23425V23.2969C7.953 23.6852 8.26794 24 8.65613 24H13.1752C13.5635 24 13.8783 23.6852 13.8783 23.2969V15.0125C13.8783 13.9468 14.7455 13.0798 15.8112 13.0798C16.8768 13.0798 17.7437 13.9468 17.7437 15.0125V23.2969C17.7437 23.6852 18.0586 24 18.4468 24H22.9656C23.354 24 23.6688 23.6852 23.6688 23.2969V14.1594C23.6688 10.4496 20.6508 7.4317 16.9411 7.4317ZM22.2625 22.5938H19.1501V15.0125C19.1501 13.1713 17.6523 11.6735 15.8113 11.6735C13.97 11.6735 12.472 13.1713 12.472 15.0125V22.5938H9.35944V8.93738H12.472V9.45685C12.472 9.72729 12.6271 9.97375 12.871 10.0908C13.1147 10.2078 13.4041 10.1744 13.6152 10.0054C14.5673 9.2417 15.7176 8.83795 16.9411 8.83795C19.8754 8.83795 22.2625 11.2251 22.2625 14.1594V22.5938Z"/></svg></a></li>
                                                </ul>
                                            </div>
                                            <hr class="dash-line">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--  Product Eye View Popup End -->

    <!-- Mini Cart Start -->
    <aside id="sidebar-cart">
        <div class="cart-block">

            <div class="upper-block">
                <div class="d-flex align-items-center justify-content-between mb-24">
                    <h5>Shopping Cart   (2)</h5>
                    <a href="#" class="close-button"><i class="fa-regular fa-xmark close-icon"></i></a>
                </div>
                <hr class="dash-line mb-32">
                <ul class="product-list">
                    <li class="product-item d-flex align-items-start justify-content-between mb-24">
                        <div class="product-block">
                            <a href="product-detail.html" class="img-block">
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/m-cart-1.png" alt="Product Photo">
                            </a>
                            <div class="product-text">
                                <a href="product-detail.html" class="h6 mb-16">Jasmine Junction</a>
                                <p class="mb-8 accent-dark">Quantity: 1</p>
                                <p class="bold-text accent-dark">$30.00</p>
                            </div>
                        </div>
                        <a href="#" class="delete-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 16" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7812 1.875H13.125C13.9004 1.875 14.5312 2.50584 14.5312 3.28125C14.5312 3.904 14.1242 4.43316 13.5623 4.6175L12.7263 14.7103C12.6661 15.4336 12.0505 16 11.3249 16H3.67513C2.94953 16 2.33397 15.4336 2.27375 14.7105L1.43766 4.61753C0.875781 4.43316 0.46875 3.904 0.46875 3.28125C0.46875 2.50584 1.09959 1.875 1.875 1.875H4.21875V1.40625C4.21875 0.630844 4.84959 0 5.625 0H9.375C10.1504 0 10.7812 0.630844 10.7812 1.40625V1.875ZM5.625 0.9375C5.36653 0.9375 5.15625 1.14778 5.15625 1.40625V1.875H9.84375V1.40625C9.84375 1.14778 9.63347 0.9375 9.375 0.9375H5.625ZM11.3249 15.0625C11.5667 15.0625 11.7719 14.8737 11.792 14.6327L12.6158 4.6875H2.38419L3.20806 14.6329C3.22809 14.8737 3.43328 15.0625 3.67513 15.0625H11.3249ZM1.875 3.75H13.125C13.3835 3.75 13.5938 3.53972 13.5938 3.28125C13.5938 3.02278 13.3835 2.8125 13.125 2.8125H1.875C1.61653 2.8125 1.40625 3.02278 1.40625 3.28125C1.40625 3.53972 1.61653 3.75 1.875 3.75ZM5.15538 6.06472L5.62413 13.6272C5.64079 13.8964 5.42729 14.125 5.15582 14.125C4.91019 14.125 4.70382 13.9338 4.68841 13.6853L4.21966 6.12272C4.20366 5.86435 4.40013 5.64191 4.65854 5.62588C4.91569 5.60978 5.13935 5.80631 5.15538 6.06472ZM7.5 5.625C7.24113 5.625 7.03125 5.83488 7.03125 6.09375V13.6562C7.03125 13.9151 7.24113 14.125 7.5 14.125C7.75887 14.125 7.96875 13.9151 7.96875 13.6562V6.09375C7.96875 5.83488 7.75887 5.625 7.5 5.625ZM9.84466 6.06475C9.86066 5.80634 10.0837 5.60988 10.3415 5.62591C10.5999 5.64191 10.7964 5.86434 10.7804 6.12275L10.3116 13.6853C10.2956 13.9435 10.0733 14.1401 9.81478 14.1241C9.55641 14.1081 9.35994 13.8856 9.37591 13.6273L9.84466 6.06475Z"/>
                            </svg>
                        </a>
                    </li>
                    <li>
                        <hr class="dash-line mb-24">
                    </li>
                    <li class="product-item d-flex align-items-start justify-content-between mb-24">
                        <div class="product-block">
                            <a href="product-detail.html" class="img-block">
                                <img src="<?php echo get_template_directory_uri(); ?>/bloom/assets/media/products/m-cart-2.png" alt="Product Photo">
                            </a>
                            <div class="product-text">
                                <a href="product-detail.html" class="h6 mb-16">Jasmine Junction</a>
                                <p class="mb-8 accent-dark">Quantity: 1</p>
                                <p class="bold-text accent-dark">$30.00</p>
                            </div>
                        </div>
                        <a href="#" class="delete-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 16" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.7812 1.875H13.125C13.9004 1.875 14.5312 2.50584 14.5312 3.28125C14.5312 3.904 14.1242 4.43316 13.5623 4.6175L12.7263 14.7103C12.6661 15.4336 12.0505 16 11.3249 16H3.67513C2.94953 16 2.33397 15.4336 2.27375 14.7105L1.43766 4.61753C0.875781 4.43316 0.46875 3.904 0.46875 3.28125C0.46875 2.50584 1.09959 1.875 1.875 1.875H4.21875V1.40625C4.21875 0.630844 4.84959 0 5.625 0H9.375C10.1504 0 10.7812 0.630844 10.7812 1.40625V1.875ZM5.625 0.9375C5.36653 0.9375 5.15625 1.14778 5.15625 1.40625V1.875H9.84375V1.40625C9.84375 1.14778 9.63347 0.9375 9.375 0.9375H5.625ZM11.3249 15.0625C11.5667 15.0625 11.7719 14.8737 11.792 14.6327L12.6158 4.6875H2.38419L3.20806 14.6329C3.22809 14.8737 3.43328 15.0625 3.67513 15.0625H11.3249ZM1.875 3.75H13.125C13.3835 3.75 13.5938 3.53972 13.5938 3.28125C13.5938 3.02278 13.3835 2.8125 13.125 2.8125H1.875C1.61653 2.8125 1.40625 3.02278 1.40625 3.28125C1.40625 3.53972 1.61653 3.75 1.875 3.75ZM5.15538 6.06472L5.62413 13.6272C5.64079 13.8964 5.42729 14.125 5.15582 14.125C4.91019 14.125 4.70382 13.9338 4.68841 13.6853L4.21966 6.12272C4.20366 5.86435 4.40013 5.64191 4.65854 5.62588C4.91569 5.60978 5.13935 5.80631 5.15538 6.06472ZM7.5 5.625C7.24113 5.625 7.03125 5.83488 7.03125 6.09375V13.6562C7.03125 13.9151 7.24113 14.125 7.5 14.125C7.75887 14.125 7.96875 13.9151 7.96875 13.6562V6.09375C7.96875 5.83488 7.75887 5.625 7.5 5.625ZM9.84466 6.06475C9.86066 5.80634 10.0837 5.60988 10.3415 5.62591C10.5999 5.64191 10.7964 5.86434 10.7804 6.12275L10.3116 13.6853C10.2956 13.9435 10.0733 14.1401 9.81478 14.1241C9.55641 14.1081 9.35994 13.8856 9.37591 13.6273L9.84466 6.06475Z"/>
                            </svg>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="bottom-block">
                <div class="d-flex justify-content-between align-items-center mb-24">
                    <h6>Total:</h6>
                    <h6 class="color-primary">$80:00</h6>
                </div>
                <hr class="dash-line mb-24">
                <div class="row">
                    <div class="col-6">
                        <a href="cart.html" class="cus-btn w-100">View Cart<span></span></a>
                    </div>
                    <div class="col-6">
                        <a href="cart.html" class="cus-btn w-100">Check Out<span></span></a>
                    </div>
                </div>
            </div>
        </div>
    </aside>
    <div id="sidebar-cart-curtain"></div>
    <!-- Mini Cart End -->

    <!-- back-to-top-start -->
    <a href="#" class="scroll-top">
        <svg class="scroll-top__circle" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </a>
    <!-- back-to-top-end -->
    <!-- Jquery Js -->
    <script src="<?php echo get_template_directory_uri(); ?>/bloom/assets/js/vendor/jquery-3.6.3.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/bloom/assets/js/vendor/bootstrap.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/bloom/assets/js/vendor/jquery-appear.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/bloom/assets/js/vendor/jquery.magnific-popup.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/bloom/assets/js/vendor/jquery.nice-select.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/bloom/assets/js/vendor/wow.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/bloom/assets/js/vendor/slick.min.js"></script>
    <script src="<?php echo get_template_directory_uri(); ?>/bloom/assets/js/app.js"></script>
</body>

</html>
<?php get_footer(); ?>