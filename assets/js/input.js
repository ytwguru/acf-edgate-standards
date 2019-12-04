(function($){
	
	
	/**
	*  initialize_field
	*
	*  This function will initialize the $field.
	*
	*  @date	30/11/17
	*  @since	5.6.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize_field( $field ) {
		
		//$field.doStuff();
		
	}
	
	
  /*
  *  ready & append (ACF5)
  *
  *  These two events are called when a field element is ready for initizliation.
  *  - ready: on page load similar to $(document).ready()
  *  - append: on new DOM elements appended via repeater field or other AJAX calls
  *
  *  @param	n/a
  *  @return	n/a
  */
  
  acf.add_action('ready_field/type=edgate-standards', initialize_field);
  acf.add_action('append_field/type=edgate-standards', initialize_field);

})(jQuery);
