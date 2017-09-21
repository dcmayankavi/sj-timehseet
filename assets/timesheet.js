( function( $ ) {
	$(document).ready(function($) {

		jQuery( '.sj-add-new-task .button' ).on( 'click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var $this 	= jQuery( this ),
				$parent	= $this.parent(),
				id 		= $parent.attr( 'data-repeater' ),
				new_id 	= parseInt(id) + 1,
				template  = $( '#sj-task-repeater-template' ).html();

			
			var ready_template = template.replace(/{{id}}/g, new_id);
			
			$parent.before( ready_template );
			$parent.attr( 'data-repeater', new_id );
		});

		jQuery( '.sj-timesheet-container' ).on( 'click', '.sj-add-new-sub-task .button', function(e) {
			e.preventDefault();
			e.stopPropagation();

			var $this 		= jQuery( this ),
				$parent		= $this.parent(),
				id 			= $parent.attr( 'data-repeater' ),
				template  	= $( '#sj-sub-task-repeater-template' ).html();
			
			var ready_template = template.replace(/{{id}}/g, id);
			
			$parent.before( ready_template );
		});
	});
} )( jQuery );