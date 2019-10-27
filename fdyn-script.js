jQuery( document ).ready(function($) {	

	$('#primary').on('change', '.fdyn-filter-select', function() {		
		
		let fdworkerName = '';
		let fdlocationName = '';
		let fdserviceName = '';		
		let fdduration = '';
		let fdslotStep = '';
		
		let fdworker = $('#fd-worker').val();
		let fdlocation = $('#fd-location').val();
		let fdservice = $('#fd-service').val();
		
		$('.ajax-loader').addClass('active');
		$('.fdyn-filter-select').prop('disabled', true).css('opacity', '0.5');

		
		$.ajax({
			url : eafilter.ajax_url,
			type : 'get',
			data : {
				action : 'ea_filter',
				_ajax_nonce : eafilter.nonce,
				fdworker: fdworker,
				fdlocation: fdlocation,
				fdservice: fdservice,
			},
			success : function( response ) {
				
				//replace filter with reduced set of options
				$('#fdyn-ea-filter' ).replaceWith(response);
				
				//worker, location and service can be selected because there was only one left
				fdworker = $('#fd-worker').val();
				fdlocation = $('#fd-location').val();
				fdservice = $('#fd-service').val();
				
				//replace content of original EA-plugin select boxes
				if(fdlocation > 0) {
					fdlocationName = $('#fd-location option[value=' + fdlocation + ']').text();
					$('select[name=location]').html('<option selected="selected" value=' + fdlocation + '>'+fdlocationName+'</option>');					
				};
				if(fdworker > 0) {
					fdworkerName = $('#fd-worker option[value=' + fdworker + ']').text();
					$('select[name=worker]').html('<option selected="selected" value=' + fdworker + '>'+fdworkerName+'</option>');					
				};
				if(fdservice > 0) {
					fdserviceName = $('#fd-service option[value=' + fdservice + ']').text();
					fdduration = $('#fd-service option[value=' + fdservice + ']').attr('data-duration');
					fdslotStep = $('#fd-service option[value=' + fdservice + ']').attr('data-slot-step');
					$('select[name=service]').html('<option  data-duration="' + fdduration + '" data-slot-step="' + fdslotStep + '" selected="selected" value=' + fdservice + '>'+fdserviceName+'</option>');
				};								

				//When worker, location and service are selected trigger chenge event on worker to start next step
				if (fdworker > 0 && fdlocation > 0 && fdservice > 0) {					
					$('select[name=worker]').change();
				};
				
			}
		});		
	});
	
	//New reset button to reset custom filter
	$('.ea-actions-group').append('<button class="btn btn-default ea-btn fd-ea-reset">Opnieuw</button>');
	
	//Reset options in the custom filter
	$('#primary').on('click', '.fd-ea-reset', function(event) {
		event.preventDefault();
		
		let fdworker = '';
		let fdlocation = '';
		let fdservice = '';		
		
		$.ajax({
			url : eafilter.ajax_url,
			type : 'get',
			data : {
				action : 'ea_filter',
				_ajax_nonce : eafilter.nonce,
				fdworker: fdworker,
				fdlocation: fdlocation,
				fdservice: fdservice,
			},
			success : function( response ) {
				
				//replace filter with complete set of options
				$('#fdyn-ea-filter' ).replaceWith(response);
				//trigger click event on original cancel button
				$('.ea-cancel').click();
			}
		});		
		
	});
	
	//Hide the new reset button when form is submitted
	$(document).ajaxStop(function() {
		if($('.ea-submit:hidden').length > 0) {
			
			$('.fd-ea-reset').hide();
		};
	});
		

});