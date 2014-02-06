<?php
/*
Plugin Name: Slushman Portfolio
Plugin URI: http://slushman.com/plugins/slushman-portfolio
Description: Create a portfolio to display your portfolio.  As used on slushman.com.
Version: 0.1
Author: Slushman
Author URI: http://www.slushman.com
License: GPL2

**************************************************************************

  Copyright (C) 2012 Slushman

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General License for more details.

  You should have received a copy of the GNU General License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************

Dev Notes:

Each layout is in its own file. They are called through a layout function. They are added in the file_includer function.

Naming Scheme: layout_{type}_{name}.php

example: layout_client_sheetsy.php


*/

if ( !class_exists( 'Slushman_Portfolio_Plugin' ) ) { //Start Class

	class Slushman_Portfolio_Plugin {
	
		public static $instance;
		
		const PLUGIN_NAME 	= 'Slushman Portfolio';
		const SETS_NAME		= 'slushman_portfolio_settings';
		const DASHES		= 'slushman-portfolio';
		const CPT_NAME		= 'slushman_portfolio';
		const TAX_NAME		= 'slushman_portfolio_types';
		const GENSETS_NAME	= 'slushman_portfolio_general_settings';
		const LAYSETS_NAME	= 'slushman_portfolio_layout_settings';		
		
		private $settings_tabs = array();

		function __construct() {
		
			self::$instance = $this;
			
			// Runs when plugin is activated
			register_activation_hook( __FILE__, array( $this, 'install' ) );
			
			// Create custom post type slushman_portfolio
			add_action( 'init', array( $this, 'create_cpt' ) );
			
			// Set up the taxonomies for slushman_portfolio
			add_action( 'init', array( $this, 'create_tax' ) );
			
			// Prepopulate the taxonomy options
			add_action( 'init', array( $this, 'pop_taxes' ) );
			
			// Include other files
			add_action( 'init', array( $this, 'file_includer' ) );
			
			// Add meta boxes
			add_action( 'admin_init', array( $this, 'create_meta_boxes' ) );
			
			// Save meta data
			add_action( 'save_post', array( $this, 'save_meta' ) );
			
			// Create shortcode [slushman-portfolio]
			add_shortcode( self:DASHES, array( $this, 'shortcode' ) );
			
			// Add columns to the seminar list
			add_filter( 'manage_edit-' . self::CPT_NAME . '_columns', array( $this, 'add_columns' ) );
			add_action( 'manage_' . self::CPT_NAME . '_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
			
			// Sort columns
			// add_filter( 'manage_edit-cemb_seminar_sortable_columns', array( $this, 'sortable_columns' ) );
			
			// Queues stylesheets and scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
			
			// Add jQuery datepicker
			add_action( 'admin_footer', array( $this, 'datepickers' ) );
			
			// Add jQuery scripts to the admin for hiding the metaboxes
			add_action( 'admin_footer', array( $this, 'metabox_script' ) );
			
			$this->prepops['App'] 		= 'Display an app, plugin, widget, or any software in your portfolio.' ;
			$this->prepops['Client'] 	= 'Display a client list in your portfolio.' ;
			$this->prepops['Gallery'] 	= 'Display a gallery in your portfolio.' ;
			$this->prepops['Position'] 	= 'Display a position or internship in your portfolio.' ;
			$this->prepops['Site'] 		= 'Display a site in your portfolio.' ;
			$this->prepops['Theme'] 	= 'Display a theme, skin, or template in your portfolio.' ;
			$this->prepops['Video'] 	= 'Display a video, movie, or film in your portfolio.' ;
			
			$this->settings_tabs[self::GENSETS_NAME] = 'Settings';
			$this->settings_tabs[self::LAYSETS_NAME] = 'Layout Previews';

			// $this->gensets = (array) get_option( 'slushman_portfolio_general_settings' );

		} // End of __construct()
		
/**
 * Creates the initial table for storing attendance data
 * Creates the plugin options
 *
 * @since	0.1
 *
 * @global	$wpdb
 *
 * @uses	determine_semester
 * @uses	create_new_table
 * @uses	settings_init
 */	
		function install() {
		
			$this->settings_init();
			
		} // End of install()
		
		
		
/* ==========================================================================
   Plugin Settings
   ========================================================================== */		
		
/**
 * Creates the settings page
 *
 * @since	0.1
 *
 * @uses	plugins_url
 * @uses	settings_fields
 * @uses	do_settings_sections
 * @uses	submit_button
 */
		function settings() { 
		
			$plugin = get_plugin_data( __FILE__ ); ?>
			
			<div class="wrap">
			<div class="icon32 slushman_portfolio_icon"></div>
			<h2><?php echo $plugin['Name']; ?> Settings</h2>
			<form method="post" action="options.php"><?php
				
				settings_fields( self::SETS_NAME );
				do_settings_sections( self::SETS_NAME );
				submit_button(); ?>
				
			</form><br />
			
			</div><?php
		
		} // End of settings()		
				
/*
		function options_page() { 
		
			$tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'slushman_portfolio_general_settings' ); ?>
			
			<div class="wrap">
			
		    	<div class="icon32" style="background-image:url(<?php echo plugins_url( 'images/icon.png', __FILE__ ); ?>); background-repeat:no-repeat;"><br /></div>
		    	
		    	<h2 class="nav-tab-wrapper"><?php
		    
		    foreach ( $this->settings_tabs as $key => $caption ) {
		    
		        $active = ( $tab == $key ? 'nav-tab-active' : '' );
		    
		        echo '<a href="?page=' . self::DASHES . '&tab=' . $key . '" class="nav-tab ' . $active . '">' . $caption . '</a>';
		
		    } // End of settings_tabs foreach ?>
		
		    	</h2>
		    	
				<form method="post" action="options.php"><?php
				
					settings_fields( $tab );
					do_settings_sections( $tab );
					submit_button(); ?>
					
				</form>
			
			</div><!-- End of .wrap --><?php

		} // End of options_page()
*/

/**
 * Adds a link to the plugin settings page to the plugin's listing on the plugin page
 *
 * @since	0.1
 * 
 * @uses	admin_url
 */			
		function settings_link( $links ) {
		
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=' . self::CPT_NAME . '&page=' . self::PLUGIN_SLUG . '-settings' ), __( 'Settings' ) );
			
			array_unshift( $links, $settings_link );
			
			return $links;
			
		} // End of settings_link()
		
/**
 * Creates the submenu
 *
 * Inside/outside refers to where the callback function is located
 * 	If ths callback is in this file, its "inside"
 * 	If ths callback is somewhere else, its "outside"
 * 
 * @since	0.1
 *
 * @uses	add_submenu_page
 */		
		function add_menu() {
		
			$submenus[] = array( 'Settings', 'edit_posts', 'inside' );
			$submenus[] = array( 'Test Page', 'manage_options', 'outside' );
		
			foreach ( $submenus as $submenu ) {
			
				$cb_fn 		= strtolower( str_replace( ' ', '_', $submenu[0] ) );
				$callback 	= ( $submenu[2] == 'inside' ? array( $this, $cb_fn ) : $cb_fn );
			
				add_submenu_page( 
					self::UNDER, 
					__( $submenu[0] ), 
					__( $submenu[0] ), 
					$submenu[1], 
					strtolower( str_replace( ' ', '-', $submenu[0] ) ),
					$callback
				);

			} // End of $submenus foreach
			
		} // End of add_menu()
		
/**
 * Registers the plugin option, settings, and sections
 *
 * Instead of writing the registration for each field, I used a foreach loop to write them all.
 * add_settings_field has an argument that can pass data to the callback, which I used to send the specifics
 * of each setting field to the callback that actually creates the setting field. 
 *
 * $options array: ( $group, $field_name, $input_type, $optional_description )
 *
 * @since	0.1
 * 
 * @uses	register_setting
 * @uses	add_settings_section
 * @uses	add_settings_field
 */	
		function gen_settings_reg() {
		
			$this->settings_tabs[self::GENSETS_NAME] = self::PLUGIN_NAME . ' Settings';
			
			register_setting( 
				self::GENSETS_NAME, 
				self::GENSETS_NAME,
				array( $this, 'validate_options' )
			);
			
			$prefix 	= 'slushman_port_';
			$sections 	= array(  );
			$options 	= array();
			$options[] 	= array( 'section', 'name', 'type', 'description' );
			$count		= 0;
			
			foreach ( $sections as $section ) {
			
				$secname = strtolower( str_replace( ' ', '_', $section ) );
			
				add_settings_section( 
					$prefix . $secname, 
					$section . ' Settings', 
					array( $this, $secname . '_settings_fn' ), 
					self::GENSETS_NAME
				);
				
			} // End of $sections foreach
			
			foreach ( $options as $option ) {
			
				$selections = array();
			
				$corv 	= ( $option[2] == 'checkbox' ? 'check' : 'value' );
				$desc 	= ( !empty( $option[3] ) ? $option[3] : '' );
				$dorl	= ( $option[2] == 'checkbox' ? 'label' : 'desc' );
				$sels	= ( !empty( $selections ) ? $selections : '' );
				
				add_settings_field(
					$prefix . $option[1] . '_field', 
					ucwords( str_replace( '_', ' ', $option[1] ) ), 
					array( $this, 'create_settings_fn' ), 
					self::GENSETS_NAME,
					$prefix . $option[0],
					array( 'id' => $option[1], 'name' => self::GENSETS_NAME . '[' . $option[1] . ']', 'inputtype'=> $option[2], $corv => $this->options[$option[1]], $dorl => $desc, 'selections' => $sels )
				);
					
			} // End of $fields foreach

		} // End of settings_reg()		
		
/**
 * Creates the settings fields
 *
 * Accepts the $params from settings_reg() and creates a setting field
 *
 * @since	0.1
 *
 * @params	$params		The data specific to this setting field, comes from settings_reg()
 * 
 * @uses	checkbox
 */	
 		function create_settings_fn( $params ) {
 		
 			$check = array( 'check', 'class', 'desc', 'id', 'inputtype', 'label', 'name', 'select', 'value' );
 			
 			foreach ( $check as $field ) {
	 			
	 			$args[$field] = ( !empty( $params[$field] ) ? $params[$field] : '' );
	 			
 			} // End of $params foreach
 			
 			$args['selections'] = ( isset( $params['selections'] ) ? $params['selections'] : '' );
		
 			extract( $args );
 			
 			switch ( $inputtype ) {
	 			
	 			case ( 'urls' ) 		: 
	 			case ( 'text' ) 		: echo $this->input_field( $args ); break;
	 			case ( 'checkbox' ) 	: echo $this->checkbox( $args ); break;
	 			case ( 'textarea' )		: echo $this->textarea( $args ); break;
	 			case ( 'checkgroup' ) 	: echo $this->make_checkboxes( $args ); break;
	 			case ( 'radios' ) 		: echo $this->make_radios( $args ); break;
	 			case ( 'dropmenu' )		: echo $this->input_group( $args ); break;
	 			
 			} // End of $inputtype switch
			
		} // End of create_settings_fn()		
		
/**
 * Validates the plugin settings before they are saved
 *
 * Loops through each plugin setting and sanitizes the data before returning it.
 *
 * @since	0.1
 */	
		function validate_options( $input ) {
		
			$selects	= array(  );
			$textareas 	= array(  );
			$checkboxes = array(  );
			$textfields	= array(  );
			
			foreach ( $selects as $select ) {
				
				$under			= 'slushport_' . str_replace( ' ', '_', strtolower( $select ) );
				$valid[$select] = intval( $input[$select] );
				
			} // End of $drops foreach
			
			foreach ( $textareas as $textarea ) {
				
				$valid[$textarea] = esc_textarea( $input[$textarea] );
				
			} // End of $textareas foreach
		
			foreach ( $checkboxes as $checkbox ) {
			
				$valid[$checkbox] = ( !isset( $input[$checkbox] ) ? $this->options[$checkbox] : $input[$checkbox] );

				$valid[$checkbox] = ( isset( $input[$checkbox] ) && $input[$checkbox] == 1 ? 1 : 0 );
				
			} // End of $fields foreach
			
			foreach ( $textfields as $textfield ) {
				
				$valid[$textfield] = sanitize_text_field( $input[$textfield] );
				
			} // End of $textfields foreach
		
			return $valid;			
			
		} // End of validate_options()		
		
				
		
/* ==========================================================================
   Custom Post Types and Taxonomies
   ========================================================================== */
		
/**
 * Create custom post type slushman_portfolio
 * 
 * @since 0.1
 */			
		function create_cpt() {
		
			$cpt_args['capability_type'] 				= 'post';
    		$cpt_args['hierarchical'] 					= FALSE;
    		$cpt_args['menu_position']  				= '25';
    		$cpt_args['public'] 						= TRUE;
			$cpt_args['publicly_querable']				= TRUE;
			$cpt_args['register_meta_box_cb']			= array( $this, 'create_meta_boxes' );
			$cpt_args['query_var']						= TRUE;
			$cpt_args['rewrite']						= FALSE;
			$cpt_args['rewrite']['slug'] 				= 'portfolio';
			$cpt_args['rewrite']['with_front'] 			= FALSE;
			$cpt_args['show_ui']						= TRUE;
			$cpt_args['show_in_menu'] 					= TRUE;
			$cpt_args['supports'] 						= array( 'title', 'editor', 'thumbnail' );
			$cpt_args['taxonomies']						= array( self::TAX_NAME );
			
			$cpt_args['labels']['name'] 				= _x( 'Portfolio Items', 'post type general name' );
			$cpt_args['labels']['singular_name'] 		= _x( 'Portfolio Item', 'post type singular name' );
			$cpt_args['labels']['add_new'] 				= __( 'Add New Portfolio Item' );
			$cpt_args['labels']['add_new_item'] 		= __( 'Add New Portfolio Item' );
			$cpt_args['labels']['edit_item'] 			= __( 'Edit Portfolio Item' );
			$cpt_args['labels']['new_item'] 			= __( 'New Portfolio Item' );
			$cpt_args['labels']['view_item'] 			= __( 'View Portfolio Item' );
			$cpt_args['labels']['search_items'] 		= __( 'Search Portfolio Items' );
			$cpt_args['labels']['not_found'] 			= __( 'No Portfolio Items Found' );
			$cpt_args['labels']['not_found_in_trash'] 	= __( 'No Portfolio Items Found in Trash' );
			$cpt_args['labels']['menu_name']			= __( 'Portfolio' );
			
			// Register Slushman Portfolio custom post type
			register_post_type( self::CPT_NAME, $cpt_args );
			
		} // End of create_cpt()
		
		function create_tax() {
			
			$tax_args['labels']['name'] 				= _x( 'Item Types', 'taxonomy general name' );
			$tax_args['labels']['singular_name']		= _x( 'Item Type', 'taxonomy singular name' );
			$tax_args['labels']['search_items']			= __( 'Search Item Types' );
			$tax_args['labels']['popular_items'] 		= __( 'Popular Item Types' );
			$tax_args['labels']['all_items']			= __( 'All Item Types' );
			$tax_args['labels']['parent_item']			= __( 'Parent Item Type' );
			$tax_args['labels']['parent_item_colon']	= __( 'Parent Item Type:' );
			$tax_args['labels']['edit_item']			= __( 'Edit Item Type' );
			$tax_args['labels']['update_item']			= __( 'Update Item Type' );
			$tax_args['labels']['add_new_item']			= __( 'Add New Item Type' );
			$tax_args['labels']['new_item_name']		= __( 'New Item Type Name' );
			
			$tax_args['hierarchical']					= TRUE;
			$tax_args['query_var']						= TRUE;
			$tax_args['rewrite']						= FALSE;
			 
			//Register Type taxonomy
			register_taxonomy( self::TAX_NAME, array( self::CPT_NAME ), $tax_args );
		
		} // End of create_tax()

/**
 * Pre-populates some options for taxonomies
 *
 * An array of taxonomy terms are run through term_exists (to make sure they aren't already there)
 * then if they aren't, they are added
 * 
 * @uses term_exists
 * @uses wp_insert_term
 * 
 * @since 0.1
 */			
		function pop_taxes() {
			
			foreach ( $this->prepops as $type=>$description ) {
			
				$lower 			= strtolower( $type );
				$parent_term 	= term_exists( $lower, self::TAX_NAME ); // returns an array
				
				if ( $parent_term == 0 ) {
					
					wp_insert_term(
						$type, // the term 
						self::TAX_NAME, // the taxonomy
						array(
							'description'=> $description,
							'slug' => $lower
						)
					);
					
					$int++;

				} // End of $parent_term check
				
			} // End of $prepops foreach
			
		} // End of pop_taxes()



/* ==========================================================================
   Styles & Scripts
   ========================================================================== */
		
		function add_styles() {
			
			wp_register_style( self::CPT_NAME,  plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_style( self::CPT_NAME );

		} // End of add_styles
		
		function admin_enqueue( $hook ) {
	
			// Queues the included stylesheet
			wp_register_style( self::CPT_NAME . '_admin_styles',  plugins_url( 'css/admin.css', __FILE__ ) );
			wp_enqueue_style( self::CPT_NAME . '_admin_styles' );
	 		
 		} // End of admin_enqueue


		
/* ==========================================================================
   Metaboxes
   ========================================================================== */ 		   		
		
/**
 * Creates the metaboxes for the CPT post editor
 *
 * Creates the metaboxes for the CPT post editor
 *
 * @link	http://wordpress.stackexchange.com/questions/49773/how-to-add-a-class-to-meta-box
 * 
 * @uses add_meta_box
 * @uses add_filter
 * @uses remove_meta_box
 * 
 * @since 0.1
 */		
		function create_meta_boxes() {
			
			foreach ( $this->prepops as $type=>$description ) {
			
				$name = 'slushman_portfolio_' . strtolower( $type ) . '_fields';
				$fn_name = strtolower( $type ) . '_fields_meta';
				
				add_meta_box( $name, $type . ' Details', array ( $this, $fn_name ), self::CPT_NAME, 'normal', 'default' );

				add_filter( 'postbox_classes_slushman_portfolio_' . $name, array( $this, 'add_metabox_classes' ) );
				
			} // End of meta boxes foreach
						
			remove_meta_box( 'slushman_portfolio_typesdiv', self::CPT_NAME, 'normal' );
			
			add_meta_box( 'slushman_portfolio_types_customdiv', 'Items Types', array ( $this, 'types_meta' ), self::CPT_NAME, 'side', 'default' );
						
		} // End of create_meta_boxes()
		
/**
 * Adds a class to the metaboxes
 *
 * @link	http://wordpress.stackexchange.com/questions/49773/how-to-add-a-class-to-meta-box
 * 
 * @since 0.1
 *
 * @param	array	$classes	The classes already there
 */		
		function add_metabox_classes( $classes ) {
			
			array_push( $classes, 'slushman_portfolio_type_fields' );
			
			return $classes;
			
		} // End of add_metabox_classes()
		
/**
 * Creates a meta box
 *
 * Creates the portfolio type metabox allowing the poster to select one type of portfolio item
 *
 * @since 0.1
 *
 * @global	$post
 *
 * @return	mixed	$output		The form for the metabox
 */		
		function types_meta() {
		
			global $post;
			
		    $custom 					= get_post_custom( $post->ID );
		
			$group_args['grouptype'] 	= 'radio';
			$group_args['id'] 			= 'slushman_portfolio_types_radios';
			$group_args['name'] 		= 'slushman_portfolio_type';
			$group_args['value'] 		= $custom[self::CPT_NAME]['types_radio'][0];
			
			$terms 	= get_terms( self::TAX_NAME, array( 'get' => 'all' ) );			
			$i 		= 0;
			
			foreach ( $terms as $term ) {
			
				$group_args['selections'][$i]['id'] = 'slushman_portfolio_type_' . $i;
				$group_args['selections'][$i]		= array( 'label' => $term->name, 'value' => $term->slug );
				
				++$i;
				
			} // End of $terms foreach
						
			$output = $this->toolkit->input_group( $group_args );
		
			echo $output;
		
		} // End of types_meta()
		
		function app_fields_meta() {
		
			$forms_args[0]['tableclass']	= 'slushman_portfolio_fields_table';
			$forms_args[0]['nonce']			= 'slushman_portfolio_fields_nonce';
			
			$forms_args[0]['type']			= 'input_field';
			$forms_args[0]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[0]['description'] 	= 'Enter the URL to download, purchase, or get info about this software.';
			$forms_args[0]['id'] 			= 'slushman_portfolio_app_url_field';
			$forms_args[0]['inputtype'] 	= 'url';
			$forms_args[0]['label'] 		= 'URL:';
			
			$forms_args[1]['label'] 			= 'Price:';
			$forms_args[1]['type']				= 'price';
			
			$forms_args[1][0]['description'] 	= 'Enter the price of the software';

			$forms_args[1][1]['id'] 			= 'slushman_portfolio_app_currency_field';
			$forms_args[1][1]['show']			= false;
			$forms_args[1][1]['value'] 			= ''; 

			$forms_args[1][2]['class'] 			= 'slushman_portfolio_input_field';
			$forms_args[1][2]['id'] 			= 'slushman_portfolio_app_price_field';
			$forms_args[1][2]['inputtype']		= 'text';
			$forms_args[1][2]['value'] 			= ''; 
			
			echo $this->toolkit->build_form( $forms_args );
			
		} // End of app_fields_meta()
		
		function client_fields_meta() {
		
			$forms_args[0]['tableclass']	= 'slushman_portfolio_fields_table';
			$forms_args[0]['nonce']			= 'slushman_portfolio_fields_nonce';
			
			$forms_args[0]['type']			= 'input_field';
			$forms_args[0]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[0]['description'] 	= 'Enter the client\'s street address';
			$forms_args[0]['id'] 			= 'slushman_portfolio_client_address_field';
			$forms_args[0]['inputtype'] 	= 'text';
			$forms_args[0]['label'] 		= 'Street Address:';
			
			$forms_args[1]['type']			= 'input_field';
			$forms_args[1]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[1]['description'] 	= 'Enter the client\'s city';
			$forms_args[1]['id'] 			= 'slushman_portfolio_client_city_field';
			$forms_args[1]['inputtype'] 	= 'text';
			$forms_args[1]['label'] 		= 'City:';
			
			$forms_args[2]['type']			= 'states';
			$forms_args[2]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[2]['description'] 	= 'Enter the client\'s state';
			$forms_args[2]['id'] 			= 'slushman_portfolio_client_state_field';
			$forms_args[2]['label'] 		= 'State:';
			
			$forms_args[3]['type']			= 'countries';
			$forms_args[3]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[3]['description'] 	= 'Enter the client\'s country';
			$forms_args[3]['id'] 			= 'slushman_portfolio_client_country_field';
			$forms_args[3]['label'] 		= 'Country:';
			
			$forms_args[4]['type']			= 'input_field';
			$forms_args[4]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[4]['description'] 	= 'Enter the client\'s phone number';
			$forms_args[4]['id'] 			= 'slushman_portfolio_client_phone_field';
			$forms_args[4]['inputtype'] 	= 'tel';
			$forms_args[4]['label'] 		= 'Phone Number:';
			
			$forms_args[5]['type']			= 'input_field';
			$forms_args[5]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[5]['description'] 	= 'Enter the client\'s website URL';
			$forms_args[5]['id'] 			= 'slushman_portfolio_client_website_field';
			$forms_args[5]['inputtype'] 	= 'url';
			$forms_args[5]['label'] 		= 'Website URL:';
			
			echo $this->toolkit->build_form( $forms_args );
			
		} // End of client_fields_meta()
		
		function gallery_fields_meta() {
			
			/*
			$forms_args[0]['class']			= 'slushman_portfolio_fields_table';
			
			echo $this->toolkit->build_form( $forms_args );
			*/
		
		} // End of gallery_fields_meta()
		
		function position_fields_meta() {
		
			$forms_args[0]['tableclass']	= 'slushman_portfolio_fields_table';
			$forms_args[0]['nonce']			= 'slushman_portfolio_fields_nonce';
			
			$forms_args[0]['type']			= 'date_picker';
			$forms_args[0]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[0]['description'] 	= 'When did you start this position?';
			$forms_args[0]['id'] 			= 'slushman_portfolio_job_startdate_field';
			$forms_args[0]['label'] 		= 'Start Date:';
			
			$forms_args[1]['type']			= 'date_picker';
			$forms_args[1]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[1]['description'] 	= 'When did you finish this position?';
			$forms_args[1]['id'] 			= 'slushman_portfolio_job_enddate_field';
			$forms_args[1]['label'] 		= 'End Date:';
			
			$forms_args[2]['type']			= 'input_field';
			$forms_args[2]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[2]['description'] 	= 'Enter the name of the organization.';
			$forms_args[2]['id'] 			= 'slushman_portfolio_job_org_field';
			$forms_args[2]['inputtype'] 	= 'text';
			$forms_args[2]['label'] 		= 'Organization:';
			
			$forms_args[3]['type']			= 'input_field';
			$forms_args[3]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[3]['description'] 	= 'Enter the organization\'s city';
			$forms_args[3]['id'] 			= 'slushman_portfolio_job_city_field';
			$forms_args[3]['inputtype'] 	= 'text';
			$forms_args[3]['label'] 		= 'City:';
			
			$forms_args[4]['type']			= 'states';
			$forms_args[4]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[4]['description'] 	= 'Enter the organization\'s state';
			$forms_args[4]['id'] 			= 'slushman_portfolio_job_state_field';
			$forms_args[4]['label'] 		= 'State:';
			
			$forms_args[5]['type']			= 'input_field';
			$forms_args[5]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[5]['description'] 	= 'Enter the name of your supervisor.';
			$forms_args[5]['id'] 			= 'slushman_portfolio_job_super_field';
			$forms_args[5]['inputtype'] 	= 'text';
			$forms_args[5]['label'] 		= 'Supervisor Name:';
			
			$forms_args[6]['type']			= 'input_field';
			$forms_args[6]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[6]['description'] 	= 'Enter your supervisor\'s title.';
			$forms_args[6]['id'] 			= 'slushman_portfolio_job_suptitle_field';
			$forms_args[6]['inputtype'] 	= 'text';
			$forms_args[6]['label'] 		= 'Supervisor Title:';
			
			$forms_args[7]['type']			= 'input_field';
			$forms_args[7]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[7]['description'] 	= 'Enter your supervisor\'s phone number.';
			$forms_args[7]['id'] 			= 'slushman_portfolio_job_supphone_field';
			$forms_args[7]['inputtype'] 	= 'tel';
			$forms_args[7]['label'] 		= 'Supervisor Phone:';
			
			$forms_args[8]['type']			= 'input_field';
			$forms_args[8]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[8]['description'] 	= 'Enter your supervisor\'s email address.';
			$forms_args[8]['id'] 			= 'slushman_portfolio_job_supemail_field';
			$forms_args[8]['inputtype'] 	= 'email';
			$forms_args[8]['label'] 		= 'Supervisor Email:';
			
			echo $this->toolkit->build_form( $forms_args );
			
		} // End of position_fields_meta()
		
		function site_fields_meta() {
		
			$forms_args[0]['tableclass']	= 'slushman_portfolio_fields_table';
			$forms_args[0]['nonce']			= 'slushman_portfolio_fields_nonce';
			
			$forms_args[0]['type']			= 'input_field';
			$forms_args[0]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[0]['description'] 	= 'Enter the URL for the site.';
			$forms_args[0]['id'] 			= 'slushman_portfolio_site_url_field';
			$forms_args[0]['inputtype'] 	= 'url';
			$forms_args[0]['label'] 		= 'Website URL:';
			
			$forms_args[1]['type']			= 'date_picker';
			$forms_args[1]['class'] 		= 'slushman_portfolio_input_field';
			$forms_args[1]['description'] 	= 'Enter the publication or completion date.';
			$forms_args[1]['id'] 			= 'slushman_portfolio_site_date_field';
			$forms_args[1]['label'] 		= 'Date Published:';
			
			$forms_args[2]['type']			= 'input_group';
			$forms_args[2]['description'] 	= 'What is the status of this site?';
			$forms_args[2]['grouptype'] 	= 'radio';
			$forms_args[2]['id']			= 'slushman_portfolio_site_status';
			$forms_args[2]['label'] 		= 'Site Status';
			
			$radios['Active'] 		= 'This site is currently using your design/work.';
			$radios['Redesigned'] 	= 'This site is not currently using your design/work, but is still online.';
			$radios['Inactive'] 	= 'This site is no longer online.';
			$i = 0;
						
			foreach ( $radios as $key => $value ) {
					
				$forms_args[2]['selections'][$i]['label'] = $key . ' - ' . $value;
				$forms_args[2]['selections'][$i]['value'] = strtolower( $key );
					
				++$i;
			
			} // End of $radios foreach
			
			echo $this->toolkit->build_form( $forms_args );
		
		} // End of site_fields_meta()
		
		function theme_fields_meta() {
		
			$forms_args[0]['tableclass']		= 'slushman_portfolio_fields_table';
			$forms_args[0]['nonce']				= 'slushman_portfolio_fields_nonce';
			
			$forms_args[0]['type']				= 'input_field';
			$forms_args[0]['class'] 			= 'slushman_portfolio_input_field';
			$forms_args[0]['description'] 		= 'Enter the URL where this theme, skin, or template can be seen.';
			$forms_args[0]['id'] 				= 'slushman_portfolio_theme_demourl_field';
			$forms_args[0]['inputtype'] 		= 'url';
			$forms_args[0]['label'] 			= 'Demo URL:';
			
			$forms_args[1]['type']				= 'input_field';
			$forms_args[1]['class'] 			= 'slushman_portfolio_input_field';
			$forms_args[1]['description'] 		= 'Enter the URL to download, purchase, or get info about this theme, skin, or template.';
			$forms_args[1]['id'] 				= 'slushman_portfolio_theme_acquireurl_field';
			$forms_args[1]['inputtype'] 		= 'url';
			$forms_args[1]['label'] 			= 'Download/Purchase URL:';
			
			$forms_args[2]['label'] 			= 'Price:';
			$forms_args[2]['type']				= 'price';			

			$forms_args[2][0]['description'] 	= 'Enter the price of the theme / skin / template';

			$forms_args[2][1]['id'] 			= 'slushman_portfolio_theme_currency_field';
			$forms_args[2][1]['show']			= false;
			$forms_args[2][1]['value'] 			= ''; 

			$forms_args[2][2]['class'] 			= 'slushman_portfolio_input_field';
			$forms_args[2][2]['id'] 			= 'slushman_portfolio_theme_price_field';
			$forms_args[2][2]['inputtype']		= 'text';
			$forms_args[2][2]['value'] 			= ''; 
			
			echo $this->toolkit->build_form( $forms_args );
		
		} // End of theme_fields_meta()
		
		function video_fields_meta() {
		
			/*
			$forms_args[0]['class']			= 'slushman_portfolio_fields_table';
			
			echo $this->toolkit->build_form( $forms_args );
			*/
		
		} // End of video_fields_meta()
		
		function save_meta( $post_id ) {
		
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }
			if ( !current_user_can( 'edit_post', $post_id ) ) { return $post_id; }
			if ( !current_user_can( 'edit_page', $post_id ) ) { return $post_id; }
			if ( !isset( $_POST['slushman_staff_dir_nonce'] ) || !wp_verify_nonce( $_POST['slushman_staff_dir_nonce'], basename( __FILE__ ) ) ) { return $post_id; }
			
			$custom = get_post_custom( $post_id );
			$checks = array( 'types_radios', 'app_currency_field', 'app_price_field', 'client_address_field', 'client_city_field', 'client_state_field', 'client_country_field', 'client_phone_field', 'job_startdate_field', 'job_enddate_field', 'job_org_field', 'job_city_field', 'job_state_field', 'job_super_field', 'job_suptitle_field', 'job_supphone_field', 'job_supemail_field', 'site_date_field', 'site_status', 'theme_currency_field', 'theme_price_field', 'app_url_field', 'client_website_field', 'site_url_field', 'theme_acquireurl_field', 'theme_demourl_field' );
	      
			foreach ( $checks as $check ) {
			
				$meta_key  	= 'slushman_portfolio_' . $check;
				$posted		= $_POST[$meta_key];
				$meta_value = ( !empty( $custom[$meta_key][0] ) ? $custom[$meta_key][0] : '' );
				
				if ( $check == 'job_startdate_field' || $check == 'job_enddate_field' || $check == 'site_date_field' ) {
				
					// Get the posted date and convert it to PHP time
					$new_meta_value = ( !empty( $posted ) ? strtotime( $posted ) : '' );
				
				} elseif ( $check == 'app_url_field' || $check == 'client_website_field' || $check == 'site_url_field' || $check == 'theme_acquireurl_field' || $check == 'theme_demourl_field' ) {
				
					// Get the posted URL and sanitize it
					$new_meta_value = ( !empty( $posted ) ? esc_url( $url ) : '' );
				
				} else {
				
					// Get the posted data and sanitize it for use as an HTML class.
					$new_meta_value = ( !empty( $posted ) ? sanitize_html_class( $posted ) : '' );
				
				} // End of $check check
		
				if ( $new_meta_value && $new_meta_value != $meta_value ) {
				
					// If the new meta value does not match the old value, update it.
					update_post_meta( $post_id, $meta_key, $new_meta_value );
	
				} elseif ( $new_meta_value == '' && $meta_value ) {
	
					// If there is no new meta value but an old value exists, delete it.
					delete_post_meta( $post_id, $meta_key, $meta_value );
	
				} elseif ( $new_meta_value && $meta_value == '' ) {
				
					// If a new meta value was added and there was no previous value, add it.
					$test = add_post_meta( $post_id, $meta_key, $new_meta_value, true );
				
				} // End of meta value checks
					
			} // End of $checks foreach
		    
		} // End of create_meta_boxes()
		
/**
 * Enqueues the jQuery script for showing/hiding metaboxes
 *
 * Enqueues the jQuery script for showing/hiding metaboxes
 *
 * Inspiration: {@link http://stackoverflow.com/questions/5940963/jquery-show-and-hide-divs-based-on-radio-button-click}
 * 
 * @uses wp_enqueue_script
 * 
 * @since 0.1
 */			
		function metabox_script() { ?>
		
			<script type="text/javascript">
			    jQuery(document).ready(function($) {
			    
			    	$.each(['app', 'client', 'gallery', 'position', 'site', 'theme', 'video'], function() {
			    		$( '#slushman_portfolio_' + this + '_fields' ).hide();
			    	});
			    	
			    	$("input[name$=\'slushman_portfolio_type\']").click(function() {
				        var type = $(this).val();
				
				        $('div.slushman_portfolio_type_fields').hide();
				        $('#slushman_portfolio_' + type + '_fields').show();
				    });
			    	
			    });
			</script><?php
		
		} // End of metabox_script()
		
		
		function datepickers() { ?>
			
			<script type="text/javascript">
				jQuery(document).ready(function() {
				    jQuery('.datepicker').datepicker({
				        dateFormat : 'mm-dd-yyyy'
				    });
				});
			</script><?php
			
		} // End of datepickers()
		
		
		
/***** Custom columns *****/
		
		// Add the Seminar Type column to the seminar list
		function add_columns( $columns ) {
		
			/*
		
			// Array of all the columns for the seminar list
			$columns['cb']					= '<input type="checkbox" />';
			$columns['title'] 				= __( 'Item Name' );
			$columns['item_type'] 			= __( 'Item Type' );
			$columns['seminar_location'] 	= __( 'Seminar Location' );
			$columns['seminar_date_col'] 	= __( 'Seminar Date' );
			$columns['seminar_upload_col'] 	= __( 'Upload Attendance' );

			return $columns;
			
			*/
		
		} // End of add_columns()
		
		// Display custom columns
		function manage_seminar_columns( $column, $post_id ) {
	
		    global $post;
		    $custom = get_post_custom();
		    
		    /*
			
		    switch ( $column ) {
		    
	            case 'item_type':
	                
	                // Get the seminar types for the post.
					$terms = get_the_terms( $post->ID, 'cemb_seminar_type' );
					$seminarterms = array();
	                
	                // Does $terms contain data?
	                if ( $terms ) {
	                
						// Loop through each term, linking to the 'edit posts' page for the specific term.
						// http://devpress.com/blog/custom-columns-for-custom-post-types/
						foreach ( $terms as $term ) {
						
							$seminarterms[] = sprintf( '<a href="%s">%s</a>',
								esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'seminar_type' => $term->slug ), 'edit.php' ) ),
								esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'seminar_type', 'display' ) )
							);
						
						}
		
						// Join the terms, separating them with a comma.
						echo join( ', ', $seminarterms );
						
					} else {
					
						// If no terms were found, output a default message.
						_e( 'No seminar type set' );
					
					}
					
				break; // End of case 'seminar_type'
				
				case 'seminar_location':
	                
	                if ( !empty( $custom['cemb_seminar_location'] ) ) {
	                
		                $semloc = $custom['cemb_seminar_location'][0];		                
		                
		                echo $semloc;
	                
	                }
					
				break; // End of case 'seminar_location'
	            
	            case 'seminar_date_col':
	                
	                if ( !empty( $custom['cemb_seminar_date'] ) ) {
	                
	                	$eventdate = date( 'D, n/j/Y \a\t g:i a', $custom['cemb_seminar_date'][0] );
		                echo $eventdate;
	                
	                }
	                
	            break; // End of case 'seminar_date_col'

	        } // End of switch
	        
	        */
	        
		} // End of manage_columns()

		function sortable_columns( $columns ) {
		
			/*
		
			$columns['seminar_date_col'] = 'seminar_date_col';
		
			return $columns;
			
			*/
			
		} // End of sortable_columns()



/* ==========================================================================
   Plugin Functions
   ========================================================================== */
		
/**
 * Output the list of portfolio posts
 *
 * Gets the current time, then gets the current semester
 * then determines which set of args you want from the parameter
 * then initiates a new WP_Query using the selected args
 * and returns the results
 *
 * Inspiration: {@link http://wordpress.stackexchange.com/questions/19305/how-do-i-list-the-next-7-days-and-any-events-cpt-contained-in-those-days}
 *
 * @since 0.1
 *
 * @uses Slushman_Toolkit
 * @uses make_array
 * @uses WP_Query
 *
 * @param array $params Includes: type, quantity, sort
 *
 * @return array An array of the posts and their data
 */			
		function port_posts( $params ) {
			
			axtract( $params );
			
			$types = $this->toolkit->make_array( $type );
			
			if ( count( $types ) > 1 ) {
				
				$args['tax_query']['relation'] = 'AND';
				$int = 0;
				
				foreach ( $types as $type ) {
					
					
					
				}
				
			} else {
				
				
				
			}
			
			$quantity = ( empty( $quantity ) ? '-1' : $quantity );
			// $today = ;
			
			$args['post_type'] 					= self::CPT_NAME;
			$args['posts_per_page'] 			= $quantity;
			$args['post_status'] 				= 'publish';
			
			// If there are more than one tax, add releation=AND
			
			if ( !empty( $type ) ) {
				
				$args['tax_query'][0]['field']		= 'slug';
				$args['tax_query'][0]['taxonomy']	= self::TAX_NAME;
				$args['tax_query'][0]['terms']		= $type;
				
			}
			
			if ( !empty( $sort ) && $sort == 'ASC' ) {
				
				$args['meta_key'] 					=
				$args['meta_query'][0]['key'] 		= 'slushman_portfolio_date';
				$args['meta_query'][0]['compare'] 	= '>=';
				$args['meta_query'][0]['type'] 		= 'NUMERIC';
				$args['meta_query'][0]['value'] 	= $today;
				$args['order'] 						= 'ASC';
				$args['orderby'] 					= 'meta_value';
				
				
			} elseif ( !empty( $sort ) && $sort == 'DESC' ) {
				
				$args['meta_key'] 					=
				$args['meta_query'][0]['key'] 		= 'slushman_portfolio_date';
				$args['meta_query'][0]['compare'] 	= '<';
				$args['meta_query'][0]['type'] 		= 'NUMERIC';
				$args['meta_query'][0]['value'] 	= $today;
				$args['order'] 						= 'DESC';
				$args['orderby'] 					= 'meta_value';
				
			}
			
			$portlist = new WP_Query( $args );
			
			return $portlist;
			
		} // End of port_posts()
		
/**
 * Create shortcode
 *
 * Starts output buffering, the sets $type and $list based on the $atts passed in.
 * Calls port_posts and processes each post and output the corresponding HTML.
 * If no portfolio posts are found, display empty message.
 *
 * @since 0.1
 *
 * @uses
 *
 * @param array $atts Array of attributes, requires a "type" for port_posts
 *
 * @return mixed HTML output
 */			
		function shortcode( $atts ) {
			
			ob_start();
			
			if ( $atts ) {
				
				// stuff
				
				wp_reset_postdata();
			
			} else {
			
				echo '<p>' . $emptymsg . '</p>';
			
			} // End of if ( $semlist )
			
			$output = ob_get_contents();
			
			ob_end_clean();
			
			return $output;
		
		} // End of shortcode()

/**
 * Include other files
 *
 * Includes all files containing functions outside the class.
 *
 * @since 0.1
 */
		function file_includer() {
	
		} // End of file_includer()		
		


/* ==========================================================================
   Slushman Toolkit Functions
   ========================================================================== */

/**
 * Creates an hidden field based on the params
 *
 * @params are:
 *  name - (optional), can be a separate value from ID
 *	value - used for the value attribute
 * 
 * How to use:
 * 
 * $hidden['name']			= '';
 * $hidden['value'] 		= '';
 * 
 * echo Slushman_Toolkit::hidden_field( $hidden );
 * 
 * 
 * 
 * @since	0.1
 * 
 * @param	array	$params		An array of the data for the hidden field
 *
 * @return	mixed	$output		A properly formatted HTML hidden field
 */			
		function hidden_field( $params ) { 
		
			extract( $params );
						
			$showname 	= ( !empty( $name ) ? '" name="' . $name . '"' : '' );
			$showvalue	= ( !empty( $value ) ? ' value="' . $value . '"' : 'value=""' );
			$output 	= '<input type="hidden"' . $showname . $showvalue . ' />';
			
			return $output;
			
		} // End of hidden_field()		
		
/**
 * Creates an input field based on the params
 *
 * @params are:
 * 	class - used for the class attribute
 * 	desc - description used for the description span
 * 	id - used for the id and name attributes
 *  type - detemines the particular type of input field to be created
 *	label - the label to use in front of the field
 *  name - (optional), can be a separate value from ID
 *  placeholder - The that appears in th field before a value is entered.
 *	value - used for the value attribute
 * 
 * Inputtype options: 
 *  email - email address
 *  file - file upload
 *  text - standard text field
 *  tel - phone numbers
 *  url - urls
 *
 * How to use:
 * 
 * $input_args['class'] 		= '';
 * $input_args['desc'] 			= '';
 * $input_args['id'] 			= '';
 * $input_args['type']			= '';
 * $input_args['label'] 		= '';
 * $input_args['name']			= '';
 * $input_args['placeholder'] 	= '';
 * $input_args['value'] 		= '';
 * 
 * echo Slushman_Toolkit::input_field( $input_args );
 * 
 * 
 * 
 * @since	0.1
 * 
 * @param	array	$params		An array of the data for the text field
 *
 * @return	mixed	$output		A properly formatted HTML input field with optional label and description
 */			
		function input_field( $params ) { 
		
			extract( $params );
						
			$showid 	= ( !empty( $id ) ? '" id="' . $id . '" name="' . ( !empty( $name ) ? $name : $id ) . '"' : '' );
			$showclass 	= ( !empty( $class ) ? ' class="' . $class . '"' : '' );
			$showtype	= ( !empty( $type ) ? ' type="' . $type . '"' : '' );
			$showvalue	= ( !empty( $value ) ? ' value="' . $value . '"' : 'value=""' );
			$showph		= ( !empty( $placeholder ) ? ' placeholder="' . $placeholder . '"' : '' );
			
			$output 	= ( !empty( $label ) ? '<label for="' . $id . '">' . $label . '</label>' : '' );
			$output 	.= '<input' . $showtype . $showid . $showvalue . $showclass . $showph . ' />';
			$output 	.= ( !empty( $desc ) ? '<br /><span class="description">' . $desc . '</span>' : '' );
			
			return $output;
			
		} // End of input_field()	
		
/**
 * Creates a select menu based on the params
 *
 * @params are:
 *  blank - false for none, true if you want a blank option, or enter text for the blank selector
 * 	class - used for the class attribute
 * 	desc - description used for the description span
 * 	id - used for the id and name attributes
 *	label - the label to use in front of the field
 *  name - the name of the field
 *	value - used in the selected function
 *	selections - an array of data to use as the selections in the menu
 *
 * How to use:
 * 
 * $make_select['blank'] 		= '';
 * $make_select['class'] 		= '';
 * $make_select['desc'] 		= '';
 * $make_select['id'] 			= '';
 * $make_select['label'] 		= '';
 * $make_select['name'] 		= ''; 
 * $make_select['value'] 		= '';
 * 
 * $i = 0;
 *
 * $selections = array(  );
 *			
 * foreach ( $selections as $selection ) {
 *				
 * 		$make_select['selections'][$i]['label'] = '';
 *		$make_select['selections'][$i]['value'] = '';
 *				
 *		++$i;
 * 
 * } // End of $selections foreach
 * 
 * echo Slushman_Toolkit::make_select( $make_select );
 * 
 * 
 *
 * @since	0.1
 * 
 * @param	array	$params		An array of the data for the select menu
 *
 * @return	mixed	$output		A properly formatted HTML select menu with optional label and description
 */	
		function make_select( $params ) {
			
			extract( $params );
			
			$showid 	= ( !empty( $id ) ? ' id="' . $id . '"' : '' );
			$showname 	= ' name="' . ( !empty( $name ) ? $name : ( !empty( $id ) ? $id : '' ) ) . '"';
			$showclass 	= ( !empty( $class ) ? ' class="' . $class . '"' : '' );
			
			$output = ( !empty( $label ) ? '<label for="' . $id . '">' . $label . '</label>' : '' );
			$output .= '<select' . $showid . $showname . $showclass .'>';
			$output .= ( !empty( $blank ) ? '<option>' . ( !is_bool( $blank ) ? __( $blank ) : '' ) . '</option>' : '' );
			
			if ( !empty( $selections ) ) {
			
				foreach ( $selections as $selection ) {
				
					extract( $selection, EXTR_PREFIX_ALL, 'sel' );
				
					$optvalue 	= ( !empty( $sel_value ) ? ' value="' . $sel_value . '"' : '' );
					$output 	.= '<option' . $optvalue . selected( $value, $sel_value, FALSE ) . ' >' . $sel_label . '</option>';
					
				} // End of $selections foreach
				
			} // End of $selections empty check
			
			$output .= '</select>';
			$output .= ( !empty( $desc ) ? '<br /><span class="description">' . $desc . '</span>' : '' );
			
			return $output;
						
		} // End of make_select()
		
/**
 * Display an array in a nice format
 *
 * @param	array	The array you wish to view
 */			
		public function print_array( $array ) {

		  echo '<pre>';
		  
		  print_r( $array );
		  
		  echo '</pre>';
		
		} // End of print_array()
						
			
	} // End of class Slushman_Portfolio_Plugin

} // End of class check

new Slushman_Portfolio_Plugin;

?>