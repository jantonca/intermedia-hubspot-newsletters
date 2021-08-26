(function( $ ) {

	    // we create a copy of the WP inline edit post function
		var $wp_inline_edit = inlineEditPost.edit;
 
		// and then we overwrite the function with our own code
		inlineEditPost.edit = function( id ) {
	 
			// "call" the original WP edit function
			// we don't want to leave WordPress hanging
			$wp_inline_edit.apply( this, arguments );
	 
			// get the post ID
			var $post_id = 0;
			if ( typeof( id ) == 'object' ) {
				$post_id = parseInt( this.getId( id ) );
			}

			if ( $post_id > 0 ) {
				// define the edit row
				var $post_row = $( '#post-' + $post_id );
	 
				// get the data
				var $post_newsletters_positions = $( '.column-entities_select_positions', $post_row ).text().split(', ');

				// populate the data
				if ($post_newsletters_positions.length === 0 || $post_newsletters_positions[0] !== '' ) { 
					for (let index = 0; index < $post_newsletters_positions.length; index++) {
						document.getElementById($post_newsletters_positions[index]).setAttribute("selected", "selected");
					}
				}
			}

			$( "select:visible" ).select2({
				placeholder: 'Select a Newsletters positions...',
				theme: "classic",
			});// Only fire on visible selects to begin with.

			$( document.body ).on( "focus", ".ptitle,select",

				function ( ev ) {
					if ( ev.target.nodeName === "SELECT" ) {
						// Fire for this element only
						$( this ).select2({ width: "element" });
					} else {
						// Fire again, but only for selects that haven't yet been select2'd
						$( "select:visible" ).not( ".select2-offscreen" ).select2({
							placeholder: 'Select a Newsletters positions...',
							theme: "classic",
							width: "element"
						});
					}
				}
				
			);

		};

})( jQuery );
