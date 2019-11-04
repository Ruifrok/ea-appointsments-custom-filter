jQuery( document ).ready(function($) {	

	//hide the EA-plugin select boxes
	$('.filter.form-control').hide();
	$('.ea-label.control-label').hide();
	
	$(document).on('change', '.eaf-filter-select', function() {		
		
		let eafworkerName = '';
		let eaflocationName = '';
		let eafserviceName = '';		
		let eafduration = '';
		let eafslotStep = '';
		
		let eafworker = $('#eaf-worker').val();
		let eaflocation = $('#eaf-location').val();
		let eafservice = $('#eaf-service').val();
		
		$('.ajax-loader').addClass('active');
		$('.eaf-filter-select').prop('disabled', true).css('opacity', '0.5');

		
		$.ajax({
			url : eaffilter.ajax_url,
			type : 'get',
			data : {
				action : 'eaf_filter',
				_ajax_nonce : eaffilter.nonce,
				eafworker: eafworker,
				eaflocation: eaflocation,
				eafservice: eafservice,
			},
			success : function( response ) {
				
				//replace filter with reduced set of options
				$('#eaf-ea-filter' ).replaceWith(response);
				
				//worker, location and service can be selected because there was only one left
				eafworker = $('#eaf-worker').val();
				eaflocation = $('#eaf-location').val();
				eafservice = $('#eaf-service').val();
				
				//replace content of original EA-plugin select boxes
				if(eaflocation > 0) {
					eaflocationName = $('#eaf-location option[value=' + eaflocation + ']').text();
					$('select[name=location]').html('<option selected="selected" value=' + eaflocation + '>'+eaflocationName+'</option>');					
				};
				if(eafworker > 0) {
					eafworkerName = $('#eaf-worker option[value=' + eafworker + ']').text();
					$('select[name=worker]').html('<option selected="selected" value=' + eafworker + '>'+eafworkerName+'</option>');					
				};
				if(eafservice > 0) {
					eafserviceName = $('#eaf-service option[value=' + eafservice + ']').text();
					eafduration = $('#eaf-service option[value=' + eafservice + ']').attr('data-duration');
					eafslotStep = $('#eaf-service option[value=' + eafservice + ']').attr('data-slot-step');
					$('select[name=service]').html('<option  data-duration="' + eafduration + '" data-slot-step="' + eafslotStep + '" selected="selected" value=' + eafservice + '>'+eafserviceName+'</option>');
				};								
				
				//When worker, location and service are selected trigger change event on worker to start next step
				if (eafworker > 0 && eaflocation > 0 && eafservice > 0) {					
					$('select[name=worker]').change();
				};
				
			}
		});		
	});
	
	//New reset button to reset custom filter
	$('.ea-actions-group').append('<button class="btn btn-default ea-btn eaf-ea-reset">Opnieuw</button>');
	$('.ea-cancel').hide();
	
	//Reset options in the custom filter
	$('#primary').on('click', '.eaf-ea-reset', function(event) {
		event.preventDefault();
		
		$('.ajax-loader').addClass('active');
		let eafworker = '';
		let eaflocation = '';
		let eafservice = '';		
		
		$.ajax({
			url : eafilter.ajax_url,
			type : 'get',
			data : {
				action : 'eaf_filter',
				_ajax_nonce : eaffilter.nonce,
				eafworker: eafworker,
				eaflocation: eaflocation,
				eafservice: eafservice,
			},
			success : function( response ) {
				
				//replace filter with complete set of options
				$('#eaf-ea-filter' ).replaceWith(response);
								
				//trigger click event on original cancel button
				$('.ea-cancel').click();
			}
		});		
		
	});
	
	//Hide the new reset button when form is submitted
	$(document).ajaxStop(function() {
		if($('.ea-submit:hidden').length > 0) {			
			$('.eaf-ea-reset').hide();

			
			
		};
	});

});
