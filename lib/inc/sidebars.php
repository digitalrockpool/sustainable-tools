<?php

/* Includes: SIDEBARS

@package	Yardstick
@author		Digital Rockpool
@link		https://yardstick.co.uk
@copyright	Copyright (c) 2018, Digital Rockpool LTD
@license	GPL-2.0+ */


function yardstick_widgets_init() {
	
 
    register_sidebar( array(
		'name'          => __( 'Edit Data', 'yardstick' ),
        'id'            => 'edit-data',
        'before_widget' => '',
        'after_widget'  => '',
        'before_title'  => '',
        'after_title'   => '',
    ) );
}
add_action( 'widgets_init', 'yardstick_widgets_init' );