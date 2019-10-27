<?php

add_action( 'wp_enqueue_scripts', 'fdyn_enqueue_scripts', 99 );
	function fdyn_enqueue_scripts() {

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
