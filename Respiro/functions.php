<?php session_start();
require_once( 'respiroFunctions.php' );
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );}


//======================================================================
// CUSTOM DASHBOARD
//======================================================================
// ADMIN FOOTER TEXT
function remove_footer_admin () {
    echo "Divi Child Theme by Monterey Premier";
}

add_filter('admin_footer_text', 'remove_footer_admin');



function pw_add_image_sizes() {
    add_image_size( 'pw-thumb', 300, 100, true );
    add_image_size( 'pw-large', 600, 300, true );
}
add_action( 'init', 'pw_add_image_sizes' );
 
function pw_show_image_sizes($sizes) {
    $sizes['pw-thumb'] = __( 'Custom Thumb', 'pippin' );
    $sizes['pw-large'] = __( 'Custom Large', 'pippin' );
 
    return $sizes;
}
add_filter('image_size_names_choose', 'pw_show_image_sizes');