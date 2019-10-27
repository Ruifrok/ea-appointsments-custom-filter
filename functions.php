<?php

function print_obj($obj) {
	if (!current_user_can('manage_options')) return;
	?>
		<pre>
		<?php print_r($obj);?>
		</pre>
	<?php
	
}

add_action( 'wp_enqueue_scripts', 'fysiodynamics_enqueue_scripts', 99 );
	function fysiodynamics_enqueue_scripts() {

		wp_enqueue_script( 'fdyn-script', get_stylesheet_directory_uri() . '/js/fdyn-script.js', array('jquery') );
		$fdyn_nonce = wp_create_nonce( 'ea_filter' );
		wp_localize_script( 'fdyn-script', 'eafilter', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => $fdyn_nonce,		
		));
	}
	
add_action( 'wp_ajax_nopriv_ea_filter', 'ea_filter' );
add_action( 'wp_ajax_ea_filter', 'ea_filter' );
	function ea_filter() {
		
		check_ajax_referer( 'ea_filter' );

		echo fdyn_ea_filter();
				
		wp_die();
	}	

add_theme_support('editor-styles');
add_editor_style( 'style-editor.css' );

add_action( 'wp_enqueue_scripts', 'fysiodynamics_enqueue_styles' );
	function fysiodynamics_enqueue_styles() {

		wp_enqueue_style( 'parent_style', get_template_directory_uri() . '/style.css' );
	/*     wp_enqueue_style( 'child-style',
			get_stylesheet_directory_uri() . '/style.css',
			array( $parent_style )
		); */
	}
add_filter( 'comment_notification_recipients', 'filter_comment_recipients', 11, 2 );
add_filter( 'comment_moderation_recipients', 'filter_comment_recipients', 11, 2 );
	function filter_comment_recipients( $emails, $comment_id ) {
		$emails[] = 'm.ruifrok@fysiodynamics.nl';
		return $emails;
	}

add_image_size('pasfoto', 120, 180);

add_filter( 'image_size_names_choose', 'fysiodyn_custom_sizes' );
function fysiodyn_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'pasfoto' => __( 'Pasfoto' ),
    ) );
}

/**
* function to show the footer info, copyright information
*/
 function spacious_footer_copyright() {
	$site_link = '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" ><span>' . get_bloginfo( 'name', 'display' ) . '</span></a>';

	$wp_link = '<a href="'.esc_url( 'http://wordpress.org' ).'" target="_blank" title="' . esc_attr__( 'WordPress', 'spacious' ) . '"><span>' . __( 'WordPress', 'spacious' ) . '</span></a>';

	$tg_link =  '<a href="'.esc_url( 'http://themegrill.com/themes/spacious' ).'" target="_blank" title="'.esc_attr__( 'ThemeGrill', 'spacious' ).'" rel="designer"><span>'.__( 'ThemeGrill', 'spacious') .'</span></a>';

	$default_footer_value = sprintf( __( 'Copyright &copy; %1$s %2$s.', 'spacious' ), date( 'Y' ), $site_link );

	$spacious_footer_copyright = '<div class="copyright">'.$default_footer_value.'</div>';
	echo $spacious_footer_copyright;
}
//modified date en author tags voor hatom toevoegen
add_action ('spacious_after_post_content', 'extra_info');
function extra_info() {
	$post_type = get_post_type();   
    if ( $post_type == 'attachment' ) {
	?>
	<div class="extra-info">
						Voor het laatst bijgewerkt op: <span class="updated"><?php the_modified_date(); ?></span> door: <span class="vcard author"><span class="fn">Fysiodynamics</span></span>
						</div>						
	<?php	
		}
	else{ ?>
	<?php if (!is_home()) { ?>
	<div class="extra-info">
						Voor het laatst bijgewerkt op: <span class="updated"><?php the_modified_date(); ?></span> door: <span class="vcard author"><span class="fn"><?php the_modified_author(); ?></span></span>
						</div>
		<?php
	}
	}
}
/**
 * Shows the small info text on top header part. Altijd alt="fysiotherapie" instellen
 */
function spacious_render_header_image() {
	$header_image = get_header_image();
	if( !empty( $header_image ) ) {
	?>
		<img src="<?php echo esc_url( $header_image ); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="fysiotherapie">
	<?php
	}
}

// Voegt foto mark toe
function wplc_foto_mark ($msg) {
	$img_mark = '<img src="'.get_bloginfo('stylesheet_directory').'/images/mark.jpg">';	
	$msg = str_replace("background-size: cover;'></div>", "background-size: cover;'>".$img_mark."</div>" , $msg);
	return $msg;	
}

//Pas json structured data aan
add_filter( 'wpseo_schema_organization', 'fd_change_organization' );
	function fd_change_organization($data){
		$data['@type'] = 'MedicalBusiness';
		$data['additionalType'] = 'http://www.productontology.org/id/Physical_therapy';

		$data['openingHoursSpecification'] = array(
			array(
				'@type'		=> 'OpeningHoursSpecification',
				'dayOfWeek' => array('Mo', 'We'),
				'opens'		=> '08:00',
				'closes'	=> '21:00',	
			),
			array(
				'@type'		=> 'OpeningHoursSpecification',
				'dayOfWeek' => array('Tu', 'Th'),
				'opens'		=> '08:00',
				'closes'	=> '20:00',	
			),
			array(
				'@type'		=> 'OpeningHoursSpecification',
				'dayOfWeek' => 'Fr',
				'opens'		=> '08:00',
				'closes'	=> '17:00',	
			),			
		);

		$data['telephone'] = '0299 – 700 201';
		$data['email'] = 'info@fysiodynamics.nl';
		//$data['image'] = wp_get_attachment_url(1547);
		$data['address']['@type'] = 'Postaladdress';
		$data['address']['streetAddress'] = 'Suzegroenewegstraat 144';
		$data['address']['postalCode'] = '1442 NM';	
		$data['address']['addressLocality'] = 'Purmerend';		
		$data['hasPOS'][0]['@type'] = 'Place';
		$data['hasPOS'][0]['address']['@type'] = 'Postaladdress';
		$data['hasPOS'][0]['address']['streetAddress'] = 'Grotenhuysweg 100';
		$data['hasPOS'][0]['address']['postalCode'] = '1466 NM';
		$data['hasPOS'][0]['address']['addressLocality'] = 'Purmerend';
		$data['hasPOS'][1]['@type'] = 'Place';
		$data['hasPOS'][1]['address']['@type'] = 'Postaladdress';
		$data['hasPOS'][1]['address']['streetAddress'] = 'Dubbele Buurt 16';
		$data['hasPOS'][1]['address']['postalCode'] = '1441 CT';
		$data['hasPOS'][1]['address']['addressLocality'] = 'Purmerend';
		return $data;
	}


//Custom filter for easy appointments	
function fdyn_ea_filter() {
	
	global $wpdb;
	
		$data = new EADBModels($wpdb, array(),array());
		
		$connections = $data->get_all_rows('ea_connections');
		$services = $data->get_all_rows('ea_services');
		$locations = $data->get_all_rows('ea_locations');
		$workers = $data->get_all_rows('ea_staff');
		
		//Select only connections with the selected worker and the selected location and the selected service
		$connections = array_filter ($connections, function($connection) {
			//nothing selected
			if (empty($_GET['fdworker']) && empty($_GET['fdlocation']) && empty($_GET['fdservice'])) return true;
			
			//One option selected
			if (empty($_GET['fdworker']) && empty($_GET['fdlocation'])) return $connection->service == $_GET['fdservice'];
			if (empty($_GET['fdworker']) && empty($_GET['fdservice'])) return $connection->location == $_GET['fdlocation'];
			if (empty($_GET['fdservice']) && empty($_GET['fdlocation'])) return $connection->worker == $_GET['fdworker'];
			
			//Two options selected
			if (!empty($_GET['fdworker']) && !empty($_GET['fdlocation'])) return ($connection->worker == $_GET['fdworker'] && $connection->location == $_GET['fdlocation']);
			if (!empty($_GET['fdworker']) && !empty($_GET['fdservice'])) return ($connection->worker == $_GET['fdworker'] && $connection->service == $_GET['fdservice']);
			if (!empty($_GET['fdservice']) && !empty($_GET['fdlocation'])) return ($connection->service == $_GET['fdservice'] && $connection->location == $_GET['fdlocation']);				
		});	
		
		//Determine which workers, locations and services are present in the selected connections			
		$worker_ids = array();
		$location_ids = array();
		$service_ids = array();
		foreach ($connections as $connection) {
			if(!in_array($connection->worker, $worker_ids)) $worker_ids[] = $connection->worker;
			if(!in_array($connection->location, $location_ids)) $location_ids[] = $connection->location;
			if(!in_array($connection->service, $service_ids)) $service_ids[] = $connection->service;
		}
		
		//Remove workers not present in the selected connections from the array with all workers
		foreach ($workers as $key => $worker) {
			if(!in_array($worker->id, $worker_ids)) unset($workers[$key]);
		}
		//Remove locations not present in the selected connections from the array with all locations
		foreach ($locations as $key => $location) {
			if(!in_array($location->id, $location_ids)) unset($locations[$key]);
		}
		//Remove services not present in the selected connections from the array with all services
		foreach ($services as $key => $service) {
			if(!in_array($service->id, $service_ids)) unset($services[$key]);
		}
			
		ob_start();
		?>

		<!-- Construct the selctboxes  -->
		<form id="fdyn-ea-filter" method="get" action=""> 
			
			<select class ="fdyn-filter-select" name='fdworker' id="fd-worker" >
				<option class="fysio" value="">Kies fysiotherapeut</option>
				<?php
				foreach ($workers as $worker) {
					$selected = '';
					if ($worker->id == $_GET['fdworker'] || count($workers) == 1) $selected = ' selected ';
					echo '<option value="'.esc_attr($worker->id).'"'.esc_attr($selected).'>'.esc_html($worker->name).'</option>';				
				}
				?>
			</select>
			<div class="ajax-loader"></div>
			<select class ="fdyn-filter-select" name='fdlocation' id="fd-location" >				
				<option value="">Kies locatie</option>
				<?php
				foreach ($locations as $location) {
					$selected = '';
					if ($location->id == $_GET['fdlocation'] || count($locations) == 1) $selected = ' selected ';					
					echo '<option value="'.esc_attr($location->id).'"'.esc_attr($selected).'>'.esc_html($location->name).'</option>';				
				}
				?>
			</select>

			<select class ="fdyn-filter-select" name='fdservice' id="fd-service" >
				<option value="">Kies behandeling</option>
				<?php				
				foreach ($services as $service) {
					$selected = '';
					if ($service->id == $_GET['fdservice'] || count($services) == 1) $selected = ' selected ';					
					echo '<option data-duration="'.esc_attr($service->duration).'" data-slot-step="'.esc_attr($service->slot_step).'" value="'.esc_attr($service->id).'"'.esc_attr($selected).'>'.esc_html($service->name).'</option>';				
				}
				?>
			</select>
			
		</form>
		
		<?php 
		$html = ob_get_clean();
		return $html;	
}

//Short code voor custom ea filter
add_action('init', 'fdyn_ea_filter_init');
	function fdyn_ea_filter_init(){
		add_shortcode('fdyn-ea-filter', 'fdyn_ea_filter');
	}