<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('edtoday_acf_field_edgate_standards') ) :


class edtoday_acf_field_edgate_standards extends acf_field {

    var $field_name = "";
    private static $standards = [];

	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function __construct( $settings, $name = "standards" ) {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->field_name = $name;
		$this->name = 'edgate_' . $name;


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('Edgate ' . ucfirst($name) . ' Select', 'acf-edgate-select');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'choice';


		/*
		*  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
		*/

		$this->defaults = array(
			'standards'	=> [],
		);


		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('FIELD_NAME', 'error');
		*/

		$this->l10n = array(
			'error'	=> __('An unknown error occurred. Please contact support.', 'acf-edgate-select'),
		);


		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/

		$this->settings = $settings;


		// do not delete!
    	parent::__construct();

	}


    /**
     * This adds a setting to the ACF Field groups to activate a field group in GraphQL.
     *
     * If a field group is set to active and is set to "show_in_graphql", the fields in the field
     * group will be exposed to the GraphQL Schema based on the matching location rules.
     *
     * @param array $field_group The field group to add settings to.
     */
    function render_field_group_settings( $field_group ) {

        /**
         * Render a field in the Field Group settings to allow for a Field Group to be shown in GraphQL.
         */
        acf_render_field_wrap(
            [
                'label'        => __( 'Show in GraphQL', 'acf' ),
                'instructions' => __( 'If the field group is active, and this is set to show, the fields in this group will be available in the WPGraphQL Schema based on the respective Location rules.' ),
                'type'         => 'true_false',
                'name'         => 'show_in_graphql',
                'prefix'       => 'acf_field_group',
                'value'        => isset( $field_group['show_in_graphql'] ) ? (bool) $field_group['show_in_graphql'] : false,
                'ui'           => 1,
            ]
        );

        /**
         * Render a field in the Field Group settings to allow for a Field Group to be shown in GraphQL.
         */
        acf_render_field_wrap(
            [
                'label'        => __( 'GraphQL Field Name', 'acf' ),
                'instructions' => __( 'The name of the field group in the GraphQL Schema.', 'wp-graphql-acf' ),
                'type'         => 'text',
                'prefix'       => 'acf_field_group',
                'name'         => 'graphql_field_name',
                'required'     => true,
                'placeholder'  => ! empty( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : null,
                'value'        => ! empty( $field_group['graphql_field_name'] ) ? $field_group['graphql_field_name'] : null,
            ]
        );

    }

	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {

		/*
		*  acf_render_field_setting
		*
		*  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
		*  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
		*
		*  More than one setting can be added by copy/paste the above code.
		*  Please note that you must also have a matching $defaults value for the field name (font_size)
		*/

        acf_render_field_setting(
            $field,
            [
                'label'         => __( 'Show in GraphQL', 'wp-graphql-acf' ),
                'instructions'  => __( 'Whether the field should be queryable via GraphQL', 'wp-graphql-acf' ),
                'name'          => 'show_in_graphql',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
                'value'        => isset( $field['show_in_graphql'] ) ? (bool) $field['show_in_graphql'] : true,
            ],
            true
        );
	}



	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {


		/*
		*  Review the data of $field.
		*  This will show what data is available
		*/

        if (!defined('EDGATE_STANDARDS_API_URL')) {
            ?>
                <h3 style="color: red">Error: EDGATE_STANDARDS_API_URL is not defined</h3>
            <?php

            return;
        }

        if(count((array)edtoday_acf_field_edgate_standards::$standards) === 0){
            edtoday_acf_field_edgate_standards::$standards = @json_decode(wp_remote_retrieve_body(
                wp_remote_get(rtrim(EDGATE_STANDARDS_API_URL, '/') . '/profile')
            ));
        }

        if (!edtoday_acf_field_edgate_standards::$standards || !is_object(edtoday_acf_field_edgate_standards::$standards)) {
            ?>
                <h3 style="color: red">Error: Unable get standards from Edgate API</h3>
            <?php
            return;
        }

		/*
		*  Create a simple text input using the 'font_size' setting.
		*/
        ?>

        <input type="hidden" name="<?php echo esc_attr($field['name']) ?>" value="" />
        <ul class="acf-checkbox-list <?php echo esc_attr($field['class']) ?> <?php //echo esc_attr($field['layout']) ?>">
            <li><label><b><?php echo ucfirst($this->field_name) ?></b></label></li>
        <?php
        $values = [];
        if (isset($field['value']) && is_array($field['value'])) {
            $values = array_flip($field['value']);
        }

        switch($this->field_name){
            case "standards":
                $this->get_list($field, $values, edtoday_acf_field_edgate_standards::$standards->standards_sets);
                break;
            case "grades":
                $this->get_list($field, $values, edtoday_acf_field_edgate_standards::$standards->grades);
                break;
            case "subjects":
                $this->get_list($field, $values, edtoday_acf_field_edgate_standards::$standards->subjects);
                break;
        }

	}
    /**
     * Helper function to return the list of options and values
     *
     * @param $field
     * @param $values
     * @param $standard_list
     */
	function get_list($field, $values, $standard_list){
        foreach ($standard_list as $standard) :
            $standard_id = $this->field_name === "standards" ? "$standard->set_id:$standard->name" : $standard;
            $standard_name = $this->field_name === "standards" ? $standard->name : $standard;
            $attrs = '';
            $id = $field['id'] . '-' . $standard_id;
            if (isset($values[$standard_id]) || count($values) === 0) {
                $attrs .= ' checked="checked"';
            }
        ?>
        <li>
            <label>
                <input
                        id="<?php echo esc_attr($id) ?>"
                        type="checkbox"
                        class="<?php echo esc_attr($field['class']) ?>"
                        name="<?php echo esc_attr($field['name']) ?>[]"
                        value="<?php echo esc_attr($standard_id) ?>"
                    <?php echo $attrs ?>
                /> <?php echo esc_attr($standard_name) ?>
            </label>
        </li>
        <?php
        endforeach;
    }

	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_enqueue_scripts() {

		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];


		// register & include JS
		wp_register_script('acf-edgate-select', "{$url}assets/js/input.js", array('acf-input'), $version);
		wp_enqueue_script('acf-edgate-select');


		// register & include CSS
		wp_register_style('acf-edgate-select', "{$url}assets/css/input.css", array('acf-input'), $version);
		wp_enqueue_style('acf-edgate-select');

	}

	*/


	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_head() {



	}

	*/


	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/

   	/*

   	function input_form_data( $args ) {



   	}

   	*/


	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_footer() {



	}

	*/


	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_enqueue_scripts() {

	}

	*/


	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_head() {

	}

	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

    /*

	function load_value( $value, $post_id, $field ) {

		return $value;

	}

    */


	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

    /*

	function update_value( $value, $post_id, $field ) {

		return $value;

	}

    */


	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/

	function format_value( $value, $post_id, $field ) {

		// bail early if no value
		if(empty($value) || !is_array($value)) {
	        return [];
		}

		// return
		return $value;
	}


	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/

	/*

	function validate_value( $valid, $value, $field, $input ){

		// Basic usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = false;
		}


		// Advanced usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = __('The value is too little!','TEXTDOMAIN'),
		}


		// return
		return $valid;

	}

	*/


	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/

	/*

	function delete_value( $post_id, $key ) {



	}

	*/


	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function load_field( $field ) {

		return $field;

	}

	*/


	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function update_field( $field ) {

		return $field;

	}

	*/


	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/

	/*

	function delete_field( $field ) {



	}

	*/


}


// initialize
new edtoday_acf_field_edgate_standards( $this->settings );
new edtoday_acf_field_edgate_standards( $this->settings, "grades" );
new edtoday_acf_field_edgate_standards( $this->settings, "subjects" );


// class_exists check
endif;

?>
