<?php

/* Includes: CUSTOM POSTS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */

// ADD TEMPLATES TO CUSTOM POSTS
add_filter('single_template', function($original){
  global $post;
  $post_name = $post->post_name;
  $post_type = $post->post_type;
  $base_name = 'single-' . $post_type . '-' . $post_name . '.php';
  $template = locate_template($base_name);
  if ($template && ! empty($template)) return $template;
  return $original;
});	

function custom_post_type() {

// HELP ARTICLES
	$labels = array(
		'name'                => _x( 'Help', 'Post Type General Name', 'yardstick' ),
		'singular_name'       => _x( 'Help', 'Post Type Singular Name', 'yardstick' ),
		'menu_name'           => __( 'Help', 'yardstick' ),
		'parent_item_colon'   => __( 'Parent Help', 'yardstick' ),
		'all_items'           => __( 'All Help Articles', 'yardstick' ),
		'view_item'           => __( 'View Help Articles', 'yardstick' ),
		'add_new_item'        => __( 'Add New Help Article', 'yardstick' ),
		'add_new'             => __( 'Add New', 'yardstick' ),
		'edit_item'           => __( 'Edit Help Article', 'yardstick' ),
		'update_item'         => __( 'Update Help Article', 'yardstick' ),
		'search_items'        => __( 'Search Help Articles', 'yardstick' ),
		'not_found'           => __( 'Not Found', 'yardstick' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'yardstick' ),
	);
	
	$args = array(
		'label'               => __( 'help', 'yardstick' ),
		'description'         => __( 'help entry', 'yardstick' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'page-attributes', 'thumbnail', 'excerpt'),
		'taxonomies'          => array( 'post_tag', 'category' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'rewrite' => array( 'slug' => 'help', 'with_front' => false ),
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-editor-help',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	
	register_post_type( 'help', $args );

}

add_action( 'init', 'custom_post_type', 0 );