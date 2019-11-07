<?php
/*
Plugin Name: Easy Appointments improved filter
Author: Rob Ruifrok
Text Domain: 
Description: Improved filter for Easy Appointments
*/

add_action( 'wp_enqueue_scripts', 'eaf_enqueue_scripts' );
	function eaf_enqueue_scripts() {
		wp_enqueue_style( 'ea-filter-style', plugins_url( '/filter-style.css', __FILE__ ) );
		wp_enqueue_script( 'ea-filter-script', plugins_url( '/ea-filter-script.js', __FILE__ ), array('jquery') );
		$eaf_filter_nonce = wp_create_nonce( 'eaf_filter' );
		wp_localize_script( 'ea-filter-script', 'eaffilter', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => $eaf_filter_nonce,		
		));
	}
	
add_action( 'wp_ajax_nopriv_eaf_filter', 'eaf_filter' );
add_action( 'wp_ajax_eaf_filter', 'eaf_filter' );
	function eaf_filter() {
		
		check_ajax_referer( 'eaf_filter' );

		echo eaf_ea_select_form();
				
		wp_die();
	}

function eaf_ea_select_form() {	

		$filter_options = eaf_ea_filter_options ();
		
		$locations = $filter_options['locations'];
		$services = $filter_options['services'];
		$workers = $filter_options['workers'];
		
		ob_start();	
		?>

		<!-- Construct the selectboxes  -->
		<form id="eaf-ea-filter" method="get" action=""> 

			<select class ="eaf-filter-select" name='eaflocation' id="eaf-location" >				
				<option value="">Select location</option>
				<?php
				foreach ($locations as $location) {
					$selected = '';
					if ($location->id == $_GET['eaflocation'] || count($locations) == 1) $selected = ' selected ';					
					echo '<option value="'.esc_attr($location->id).'"'.esc_attr($selected).'>'.esc_html($location->name).'</option>';				
				}
				?>
			</select>	
			<div class="ajax-loader"></div>
			<select class ="eaf-filter-select" name='eafworker' id="eaf-worker" >
				<option class="fysio" value="">Select worker</option>
				<?php
				foreach ($workers as $worker) {
					$selected = '';
					if ($worker->id == $_GET['eafworker'] || count($workers) == 1) $selected = ' selected ';
					echo '<option value="'.esc_attr($worker->id).'"'.esc_attr($selected).'>'.esc_html($worker->name).'</option>';				
				}
				?>
			</select>

			<select class ="eaf-filter-select" name='eafservice' id="eaf-service" >
				<option value="">Select service</option>
				<?php				
				foreach ($services as $service) {
					$selected = '';
					if ($service->id == $_GET['eafservice'] || count($services) == 1) $selected = ' selected ';					
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
add_action('init', 'eaf_ea_filter_init');
	function eaf_ea_filter_init(){
		add_shortcode('eaf-ea-filter', 'eaf_ea_select_form');
	}

//Determine locations, workers and services for chosen options
function eaf_ea_filter_options () {
	
	global $wpdb;
	
		$data = new EADBModels($wpdb, array(),array());
		
		$connects = $data->get_all_rows('ea_connections');
		$services = $data->get_all_rows('ea_services');
		$locations = $data->get_all_rows('ea_locations');
		$workers = $data->get_all_rows('ea_staff');
		$eafget = array();
		
		$filter_results = eaf_ea_filter($connects);
		$worker_ids = $filter_results['worker_ids'];
		$location_ids = $filter_results['location_ids'];
		$service_ids = $filter_results['service_ids'];
		
		if(empty($_GET['eafworker']) && count($worker_ids) == 1) $eafget['worker'] = $worker_ids[0];
		if(empty($_GET['eaflocation']) && count($location_ids) == 1) $eafget['location'] = $location_ids[0];	
		if(empty($_GET['eafservice']) && count($service_ids) == 1) $eafget['service'] = $service_ids[0];
		if (!empty($eafget)) {
			$filter_results  = eaf_ea_filter($connects, $eafget);
			$worker_ids = $filter_results['worker_ids'];
			$location_ids = $filter_results['location_ids'];
			$service_ids = $filter_results['service_ids'];
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
	
		$eaf_ea_filter_options = array(
			'workers'		=> $workers,
			'locations'		=> $locations,
			'services'		=> $services,
		);
	return 	$eaf_ea_filter_options;
}		

//Custom filter for easy appointments connections
function eaf_ea_filter($connections, $eafget=array()) {	
	
		//set $_GET when select is determined by option count
		if(isset($eafget['eafworker'])) $_GET['eafworker'] = $eafget['fworker'];
		if(isset($eafget['eaflocation'])) $_GET['eaflocation'] = $eafget['location'];
		if(isset($eafget['eafservice'])) $_GET['eafservice'] = $eafget['service'];
		
		//Select only connections with the selected worker and the selected location and the selected service
		
		//No option selected
		if (empty($_GET['eafworker']) && empty($_GET['eaflocation']) && empty($_GET['eafservice'])) $connections = array_filter($connections, function($connection) {return true;});

		//One option selected
		elseif (empty($_GET['eaflocation']) && empty($_GET['eafworker'])) $connections = array_filter($connections, function($connection) {return $connection->service == $_GET['eafservice'];});
		elseif (empty($_GET['eafworker']) && empty($_GET['eafservice'])) $connections = array_filter($connections, function($connection) {return $connection->location == $_GET['eaflocation'];});
		elseif (empty($_GET['eafservice']) && empty($_GET['eaflocation'])) $connections = array_filter($connections, function($connection) {return $connection->worker == $_GET['eafworker'];});	
		
		//Two options selected
		elseif (empty($_GET['eaflocation'])) $connections = array_filter($connections, function($connection) {
			return ($connection->service == $_GET['eafservice'] && $connection->worker == $_GET['eafworker']);
			});
		elseif (empty($_GET['eafworker'])) $connections = array_filter($connections, function($connection) {
			return ($connection->location == $_GET['eaflocation'] && $connection->service == $_GET['eafservice']);
		});
		elseif (empty($_GET['eafservice'])) $connections = array_filter($connections, function($connection) {
			return ($connection->worker == $_GET['eafworker'] && $connection->location == $_GET['eaflocation']);
		});
		
		//Three options selected
		else $connections = array_filter($connections, function($connection) {
			return (
				($connection->location == $_GET['eaflocation'] && $connection->worker == $_GET['eafworker']) 
				|| ($connection->service == $_GET['eafservice'] && $connection->worker == $_GET['eafworker'])
				|| ($connection->service == $_GET['eafservice'] && $connection->location == $_GET['eaflocation'])
			);
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

		$filter_results = array(
			'worker_ids'	=> $worker_ids,
			'location_ids'	=> $location_ids,
			'service_ids'	=> $service_ids,
		);
	return $filter_results;
}

