<?php

// Set Up
add_theme_support( 'title-tag' );
add_theme_support( 'menus' );
add_theme_support( 'post-thumbnails' );


// Includes
require get_theme_file_path('/includes/search-route.php');
require get_theme_file_path('/includes/admin/menus.php');
require get_theme_file_path('/includes/admin/options-page.php');
require get_theme_file_path('/includes/admin/init.php');
require get_theme_file_path('/process/save-options.php');


// Action Hooks
function fu_enqueue() {
	wp_enqueue_media();
	
	wp_register_style( 'fu-font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
	wp_register_style( 'fu-font-google', 'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i' );
	wp_register_style( 'fu-style', get_stylesheet_uri() );

	wp_enqueue_style( 'fu-font-awesome' );
	wp_enqueue_style( 'fu-font-google' );
	wp_enqueue_style( 'fu-style' );

	wp_register_script( 'google-map', '//maps.googleapis.com/maps/api/js?key=AIzaSyANrMC61_txfgIc4UDf9C0YUpSe60vQWZU', array(), false, true );
	wp_register_script( 'main-university-js', get_theme_file_uri('/js/scripts-bundled.js'), array(), false, true );
	wp_register_script( 'fu-app-script',      get_template_directory_uri() . '/js/app-script.js', array(), false, true );

	wp_enqueue_script( 'jquery' );

	wp_enqueue_script( 'google-map' );
	wp_enqueue_script( 'main-university-js' );
	wp_enqueue_script( 'fu-app-script' );

	wp_localize_script( 'main-university-js', 'universityData', array(
	    'root_url' => get_site_url(),
	    'nonce'    => wp_create_nonce('wp_rest'), // nonce number used once
    ) );
}
add_action( 'wp_enqueue_scripts', 'fu_enqueue' );

function fu_features() {
	register_nav_menu( 'headerMenuLocation', 'Header Menu Location' );
	register_nav_menu( 'footerLocationOne',  'Footer Location One' );
	register_nav_menu( 'footerLocationTwo',  'Footer Location Two' );

	add_image_size( 'professorLandscape', 400, 260, true );
	add_image_size( 'professorPortrait', 480, 650, true );
	add_image_size( 'pageBanner', 1500, 350, true );
}
add_action( 'after_setup_theme', 'fu_features' );

function fu_adjust_queries( $query ) {
	if ( ! is_admin() && is_post_type_archive( 'program' ) && $query->is_main_query()) {
		$query->set('orderby', 'title');
		$query->set('order', 'ASC');
		$query->set('posts_per_page', -1);
	}

	if ( ! is_admin() && is_post_type_archive( 'event' ) && $query->is_main_query()) {
		$query->set('meta_key', 'event_date');
		$query->set('orderby', 'meta_value_num');
		$query->set('order', 'ASC');
		$query->set('meta_query', array(
			array(
				'key' => 'event_date',
				'compare' => '>=',
				'value'   => date('Ymd'),
				'type'    => 'numeric'
			),
		));
	}

	if ( ! is_admin() && is_post_type_archive( 'campus' ) && $query->is_main_query()) {
		$query->set('posts_per_page', -1);
	}
}
add_action( 'pre_get_posts', 'fu_adjust_queries' );

// Filter Hooks


function universityMapKey( $api ) {
	$api['key'] = 'AIzaSyANrMC61_txfgIc4UDf9C0YUpSe60vQWZU';
	return $api;
}
add_filter( 'acf/fields/google_map/api', 'universityMapKey' );

// Reusable functions

function pageBanner( $args = [] ) {
	if ( ! isset($args['title']) ) {
		$args['title'] = get_the_title();
	}

	if ( ! isset($args['subtitle']) ) {
		$args['subtitle'] = get_field( 'page_banner_subtitle' );
	}

	if ( ! isset($args['photo']) ) {
		if ( get_field( 'page_banner_background_image' ) ) {
			$args['photo'] = get_field( 'page_banner_background_image' )['sizes']['pageBanner'];
		} else {
			$args['photo'] = get_theme_file_uri( '/images/ocean.jpg' );
		}
	}
	?>
    <div class="page-banner">
		<?php $pageBannerImage = get_field('page_banner_background_image'); ?>

        <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo'] ?>);"></div>
        <div class="page-banner__content container container--narrow">
            <h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
            <div class="page-banner__intro">
                <p><?php echo $args['subtitle'] ?></p>
            </div>
        </div>
    </div>
	<?php
}

function university_custom_rest() {
    register_rest_field( 'post', 'authorName', array(
        'get_callback' => function(){ return get_the_author(); })
    );
}
add_action( 'rest_api_init', 'university_custom_rest' );

// Redirect subscriber accounts out of admin and onto homepage
function redirectSubscribersToFrontend() {
    $ourCurrentUser = wp_get_current_user();

    if (count($ourCurrentUser->roles) == 1 && $ourCurrentUser->roles[0] == 'subscriber') {
        wp_redirect(site_url('/'));
        exit;
    }
}
add_action( 'admin_init', 'redirectSubscribersToFrontend' );

function noAdminBarForSubscribers() {
	$ourCurrentUser = wp_get_current_user();

	if (count($ourCurrentUser->roles) == 1 && $ourCurrentUser->roles[0] == 'subscriber') {
		show_admin_bar(false);
	}
}
add_action( 'wp_loaded', 'noAdminBarForSubscribers' );

// Customize Login Screen
function ourLoginHeaderUrl() {
    return esc_url(site_url('/'));
}
add_filter( 'login_headerurl', 'ourLoginHeaderUrl' );

function ourLoginHeaderTitle() {
	return sanitize_text_field('Fictional University');
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertitle', 'ourLoginHeaderTitle' );

function ourLoginCSS() {
	wp_register_style( 'fu-font-google', 'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i' );
	wp_register_style( 'fu-style', get_stylesheet_uri() );

	wp_enqueue_style( 'fu-font-google' );
	wp_enqueue_style( 'fu-style' );
}
add_action( 'login_enqueue_scripts', 'ourLoginCSS' );

function fu_activate() {
	if ( version_compare( get_bloginfo( 'version' ), '4.2', '<' )) {
		wp_die( 'You must have a minimum version of 4.2 to use this theme.' );
	}

	$theme_opts = get_option( 'fu_opts' );

	if ( !$theme_opts ) {
		$opts = array(
			'favicon'   => '',
			'phone'     => '',
			'facebook'  => '',
			'twitter'   => '',
			'youtube'   => '',
			'linkedin'  => '',
			'instagram' => '',
		);

		add_option( 'fu_opts', $opts);
	}
}
add_action( 'after_switch_theme', 'fu_activate' );

add_action( 'admin_menu', 'fu_admin_menus' );
add_action( 'admin_init', 'fu_admin_init' );
