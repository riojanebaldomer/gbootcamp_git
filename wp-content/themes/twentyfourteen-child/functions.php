<?php

add_action( 'wp_enqueue_scripts', 'twentyfourteen_child_enqueue_styles' );
function twentyfourteen_child_enqueue_styles() {
  $parent_style = 'twentyfourteen-theme-styles';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}