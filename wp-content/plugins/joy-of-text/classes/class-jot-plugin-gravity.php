<?php
/**
* Joy_Of_Text Gravity. Integrates Gravity Forms into JOT. Allowing gForms to be used instead of JOT forms.
*
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_Gravity {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
               function __construct() {
		    
                    add_action( 'gform_confirmation', array($this,'process_submitted_gf_form'), 10, 3 );
		    add_filter('jot_render_grouplisttabs', array($this,'add_gf_tab'),10,1 );
		    add_action('jot_render_extension_subtab',array($this,'render_gf_tab'),10,3);
		    add_filter('jot_get_settings_fields',array($this, 'get_settings_fields'),10,2);
		    
		    add_action( 'wp_ajax_process_get_gf_fields', array( $this, 'process_get_gf_fields' ) );
		    add_action( 'wp_ajax_process_save_gf_fields', array( $this, 'process_save_gf_fields' ) );
                                    
               } // end constructor
            
               private static $_instance = null;
                   
               public static function instance () {
                   if ( is_null( self::$_instance ) )
                       self::$_instance = new self();
                   return self::$_instance;
               } // End instance()

               public function get_gf_forms() {
		    $forms = GFFormsModel::get_forms(1, 'id ASC');
		    return $forms;
	       }
	       	       
	       public function add_gf_tab($tabs) {
		   $tabs['jottabgravity'] = "Gravity Forms";                    
		   return $tabs;
               }
		
	       /**
		* Retrieve the settings fields details
		*/
		public function get_settings_fields ( $settings_fields, $section  ) {
		    
		   	    
		    switch ( $section ) {
			case 'group-list':			   
			    $settings_fields['jot-gravityforms'] = array(
			      'name' => __('Select Gravity Form :', 'jot-plugin' ),
			      'type' => 'select',
			      'default' => '',                      
			      'section' => 'group-list',
			      'subform' => 'main',                        
			      'description' => 'Select the Gravity Form you want to use for this JOT Group.'
			    );		   
			    $settings_fields['jot-gf-map-name'] = array(
			      'name' => __('Name field :', 'jot-plugin' ),
			      'type' => 'select',
			      'default' => '',                      
			      'section' => 'group-list',
			      'subform' => 'main',                        
			      'description' => ''
			    );
			    $settings_fields['jot-gf-map-phone'] = array(
			      'name' => __('Phone field :', 'jot-plugin' ),
			      'type' => 'select',
			      'default' => '',                      
			      'section' => 'group-list',
			      'subform' => 'main',                        
			      'description' => ''
			     );
			     $settings_fields['jot-gf-map-email'] = array(
			      'name' => __('Email field :', 'jot-plugin' ),
			      'type' => 'select',
			      'default' => '',                      
			      'section' => 'group-list',
			      'subform' => 'main',                        
			      'description' => ''
			     );
			     $settings_fields['jot-gf-map-address'] = array(
			      'name' => __('Address field :', 'jot-plugin' ),
			      'type' => 'select',
			      'default' => '',                      
			      'section' => 'group-list',
			      'subform' => 'main',                        
			      'description' => ''
			     );
			     $settings_fields['jot-gf-map-city'] = array(
			      'name' => __('City field :', 'jot-plugin' ),
			      'type' => 'select',
			      'default' => '',                      
			      'section' => 'group-list',
			      'subform' => 'main',                        
			      'description' => ''
			     );
			     $settings_fields['jot-gf-map-state'] = array(
			      'name' => __('State field :', 'jot-plugin' ),
			      'type' => 'select',
			      'default' => '',                      
			      'section' => 'group-list',
			      'subform' => 'main',                        
			      'description' => ''
			     );
			     $settings_fields['jot-gf-map-zipcode'] = array(
			      'name' => __('Zipcode field :', 'jot-plugin' ),
			      'type' => 'select',
			      'default' => '',                      
			      'section' => 'group-list',
			      'subform' => 'main',                        
			      'description' => ''
			     );
			break;
		        
			default:
			    
			break;
		    }
		    
		    return (array) apply_filters( 'jot_notify_get_settings_fields', $settings_fields );
		} // End get_settings_fields()
		
		public function render_gf_tab($sections, $tab, $lastid) {
		       
			$grpmeta = Joy_Of_Text_Plugin()->settings->get_groupmeta($lastid, 'gf_mappings');
			$groupmeta = json_decode($grpmeta,true);
						
			if (isset($_GET['subtab'])) {
				if ($_GET['subtab'] == 'jottabgravity') {
				    $style = "style='display:block'";
				} else {
				    $style = "style='display:none'";
				}
			} else {
				$style = "style='display:none'";
			}
				  
			$html = "<div id='jottabgravity' $style>"; 
			$html .= "<h3>Gravity Forms Integration</h3>";
			$html .= "<p class='description'>";
		        $html .= __("Select the Gravity Form you wish to use as the subscription form this group.","jot-plugin");
		        $html .= "</p>";
			$html .= "<form id='jot-group-gravity-form' action='' method='post'>";
			$html .= "<input type=\"hidden\"  id=\"jot_grpid\" name=\"jot_grpid\" value=\"" . $lastid . "\">";
			$html .= "<input type=\"hidden\"  id=\"jot_tab\" name=\"jot_tab\" value=\"" . $tab . "\">";
			$html .= "<table class=\"jot-formtab  form-table\">\n";
			
			if (Joy_Of_Text_Plugin()->gravity) {
			      $forms = Joy_Of_Text_Plugin()->gravity->get_gf_forms();
			      
			      $allforms[99999999] = __("No form selected","jot-plugin"); 
			      foreach ($forms as $form) {
				 $allforms[$form->id] = $form->title;
			      }
			     
			      if (empty($groupmeta)) {
				   $defaultform = 99999999;				   
			      } else {
				   $defaultform = isset($groupmeta['jot-gravityforms']) ? $groupmeta['jot-gravityforms'] : 99999999;
			      }
			      
			      $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-gravityforms','',$allforms,$defaultform,$tab);
						     
			}		        
			$html .= "</table>";
			
			// Render form fields
			$html .= "<p class='description'>";
		        $html .= __("Map your Gravity Form fields onto the JOT form fields.","jot-plugin");
		        $html .= "</p>";
			$html .= "<div id='jotgravityfieldsmap'>";
			
			
			if ($defaultform <> 99999999) {			   
			   $html .= $this->render_gf_form_fields($defaultform,$tab,$lastid);
			}
			
			$html .= "</div>";
			
			$html .= "</form>";
			$html .= "<p>";
			$html .= "<input type=\"button\" id=\"jot-savegrpgravity\" class=\"button\" value=\"Save form details\">";
			$html .= "<div id=\"jot-grpgravity-message\"></div>";
			$html .= "</div>";
						
			echo $html;
		}
		
		public function render_gf_form_fields($gf_formid, $tab, $jot_grpid) {
		    
		    $mappings_json =  Joy_Of_Text_Plugin()->settings->get_groupmeta($jot_grpid, 'gf_mappings');
		    $mappings = json_decode($mappings_json,true);
		    
		    // Is this metadata for the current selected group?
		    if (isset($mappings['jot-gravityforms'])) {
			 if ($mappings['jot-gravityforms'] != $gf_formid) {
			      $mappings = "";
			 }
		    }
		   	    
		    $html = "";
		    $formmeta = GFFormsModel::get_form_meta($gf_formid);		    
		    
		   
		    		
		    $allfields[-1] =  __("Not selected","jot-plugin");
		    if (isset($formmeta['fields'])) {
			 foreach ( $formmeta['fields'] as $field ) {
			 
			    if (!empty($field['label'])) {
			       $fieldid = (string) $field['id'];
			       $allfields[$fieldid] = $field['label'];
			      			    
			      // Get all subfields			      
			      if (!empty($field['inputs'])) {
				foreach ($field['inputs'] as $input) {
			          $id = (string) $input['id'];
				  $allfields[$id] = "--- " . $input['label'] ;
				}
			      }
			      
			      
			    }
			 			 
			 }
		    }
		    
		    $html .= "<table class=\"jot-formtab form-table\">\n";
		    
		    $html .= "<tr>";
		    $html .= "<td style='text-decoration: underline;'>";
		    $html .= __("JOT Fields","jot-plugin");
		    $html .= "</td>";
		    $html .= "<td style='text-decoration: underline;'>";
		    $html .= __("Gravity Form Fields","jot-plugin");
		    $html .= "</td>";	
		    $html .= "</tr>";
		    
		    $gf_name_map = isset($mappings['jot-gf-map-name']) ? $mappings['jot-gf-map-name'] : -1;
		    $gf_phone_map = isset($mappings['jot-gf-map-phone']) ? $mappings['jot-gf-map-phone'] : -1;
		    $gf_email_map = isset($mappings['jot-gf-map-phone']) ? $mappings['jot-gf-map-email'] : -1;
		    $gf_address_map = isset($mappings['jot-gf-map-address']) ? $mappings['jot-gf-map-address'] : -1;
		    $gf_city_map = isset($mappings['jot-gf-map-city']) ? $mappings['jot-gf-map-city'] : -1;
		    $gf_state_map = isset($mappings['jot-gf-map-state']) ? $mappings['jot-gf-map-state'] : -1;
		    $gf_zipcode_map = isset($mappings['jot-gf-map-zipcode']) ? $mappings['jot-gf-map-zipcode'] : -1;
		    
		    /*		    
		    echo "<br>>>> ". $gf_name_map;
		    echo "<br>>>> ". $gf_phone_map;
		    echo "<br>>>> ". $gf_email_map;
		    echo "<br>>>> ". $gf_address_map;
		    echo "<br>>>> ". $gf_city_map;
		    echo "<br>>>> ". $gf_state_map;
		    echo "<br>>>> ". $gf_zipcode_map;
		    */
		    
		    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-gf-map-name','',$allfields,$gf_name_map,$tab);
		    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-gf-map-phone','',$allfields,$gf_phone_map,$tab);
		    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-gf-map-email','',$allfields,$gf_email_map,$tab);
		    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-gf-map-address','',$allfields,$gf_address_map,$tab);
		    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-gf-map-city','',$allfields,$gf_city_map,$tab);
		    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-gf-map-state','',$allfields,$gf_state_map,$tab);
		    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-gf-map-zipcode','',$allfields,$gf_zipcode_map,$tab);
		    $html .= "</table>";		    
		    
		    return $html;
		    
		}
		
		/*
	        *
	        * Get GF fields for form passed by AJAX
	        *
	        */
	       public function process_get_gf_fields() {		    
		    $formdata = $_POST['formdata'];
		    $gf_formid = $formdata['jot_gfformid'];
		    $jot_tab = $formdata['jot_tab'];
		    $jot_grpid = $formdata['jot_grpid'];
		   
		    $html = $this->render_gf_form_fields($gf_formid, $jot_tab, $jot_grpid);
		    
		    echo $html;
		    wp_die();
	       }
	       
	       /*
	        *
	        * Save GF field mappings passed by AJAX
	        *
	        */
	       public function process_save_gf_fields() {		    
		    $formdata = $_POST['formdata'];
		    parse_str($formdata, $output);
		    		    
		    $groupfields = $output['jot-plugin-group-list'];
		    $groupfields_json = json_encode($groupfields);
		    
		    $jot_grpid = $output['jot_grpid'];
		   
		    $return = Joy_Of_Text_Plugin()->settings->save_groupmeta($jot_grpid, 'gf_mappings', $groupfields_json);
		    $errmsg = __("An error occured saving your Gravity Form preferences.","jot-plugin");
	            Joy_Of_Text_Plugin()->settings->check_save_error($return,$errmsg);
		    		    
		    // If we've got this far, there have been no save errors
		    $return['errormsg'] = __("Gravity Forms preferences saved successfully.","jot-plugin");
		    echo json_encode($return);
		    wp_die();
		    
	       }
           
    
	       /*
	        *
	        * Get all the JOT groups mapped to the given GF form
	        *
	        */
	       public function get_jot_groups($gf_formid, $key) {
		    
		    global $wpdb;
		    
		    $allgroups = array();
		    
		    $table = $wpdb->prefix."jot_groupmeta";
		    $sql = " SELECT jot_groupid, jot_groupmetaval " .
		    " FROM " . $table .
		    " WHERE jot_groupmetakey = %s";
		    
		    $sqlprep = $wpdb->prepare($sql, $key );
                    $allmetaval = $wpdb->get_results($sqlprep);
		    
		    foreach ($allmetaval as $metaval) {			
			 $thisval = json_decode($metaval->jot_groupmetaval,true);
			 $this_gf_group = $thisval['jot-gravityforms'];
			 if ($gf_formid == $this_gf_group) {
			      $allgroups[$metaval->jot_groupid] = $thisval;
			 }
		    }		    
		     
		    return $allgroups ;
		    
	       }
	       
	       public function process_submitted_gf_form( $confirmation, $form, $entry) {
		    
		    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"form >>>>" . print_r($form,true));		  
		    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"entry>>>>" . print_r($entry,true));
		    	    
		    // Find all fields that have multiple inputs
		    $field_has_inputs = array();
		    foreach($form['fields'] as $field) {
			  if ($field->inputs != null) {
			      $field_has_inputs[$field->id] = $field->inputs;
			 }
		    }
		    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Inputs" . print_r($field_has_inputs,true));
		    
		    $response = "";
		    $message_error = "";
		    
		    // Get all the JOT groups mapped to this GF form
		    $allgroups = $this->get_jot_groups($entry['form_id'], 'gf_mappings');
		    foreach ($allgroups as $key => $value ) {
			 		 
			 $jot_temp_grpmemname    = $value['jot-gf-map-name']    > 0 ? $this->get_field_value($value['jot-gf-map-name'],$form, $entry, $field_has_inputs)  : "";
			 $jot_temp_grpmemnum     = $value['jot-gf-map-phone']   > 0 ? $this->get_field_value($value['jot-gf-map-phone'],$form, $entry, $field_has_inputs)   : "";
			 
			 $jot_grpmemname    = substr($jot_temp_grpmemname, 0, 40);
			 $jot_grpmemnum     = substr($jot_temp_grpmemnum,0,40);
			 
			 //Extended info
			 $jot_grpmememail   = $value['jot-gf-map-email']   > 0 ?  $this->get_field_value($value['jot-gf-map-email'],$form, $entry, $field_has_inputs)   : "";
			 $jot_grpmemaddress = $value['jot-gf-map-address'] > 0 ?  $this->get_field_value($value['jot-gf-map-address'],$form, $entry, $field_has_inputs) : "";
			 $jot_grpmemcity    = $value['jot-gf-map-city']    > 0 ?  $this->get_field_value($value['jot-gf-map-city'],$form, $entry, $field_has_inputs)    : "";
			 $jot_grpmemstate   = $value['jot-gf-map-state']   > 0 ?  $this->get_field_value($value['jot-gf-map-state'],$form, $entry, $field_has_inputs)   : "";
			 $jot_grpmemzip     = $value['jot-gf-map-zipcode'] > 0 ?  $this->get_field_value($value['jot-gf-map-zipcode'],$form, $entry, $field_has_inputs) : "";
		     	    
			 $jot_ext = array();   
			 $jot_ext['jot_grpmememail']   = substr($jot_grpmememail,0,90);
			 $jot_ext['jot_grpmemaddress'] = substr($jot_grpmemaddress,0,240);
			 $jot_ext['jot_grpmemcity']    = substr($jot_grpmemcity,0,40);
			 $jot_ext['jot_grpmemstate']   = substr($jot_grpmemstate,0,40);
			 $jot_ext['jot_grpmemzip']     = substr($jot_grpmemzip,0,20);
		    	
			 
			 // Check that the number have been mapped
			 if ($jot_grpmemnum != "") {
			      
			      $add_return = Joy_Of_Text_Plugin()->options->process_add_member($jot_grpmemname,
											      $jot_grpmemnum,
											      $key,
											      $jot_ext);			      
			     		       
			      // Send welcome message to the subscriber if required
			      if ($add_return['errorcode'] == 0) {
				   if ($add_return['verified_number'] != "") {
				       $message_error = Joy_Of_Text_Plugin()->options->send_welcome_message($key, $add_return['verified_number'],$add_return['lastid']);
				       if ($message_error != "") {
					     if ($message_error['send_message_errorcode'] != 0) { 
					         Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Gravity Form welcome message error : " . $message_error['send_message_errorcode'] . " " . $message_error['send_message_msg']);
					          // An error occurred when adding the member 
				                  $confirmation .= __("An error occurred sending a Welcome message. ","jot-plugin") . $message_error['send_message_msg']  . "<br>";
					     }
				       }
				   }
				   
			      } else {
				   // An error occurred when adding the member 
				   $confirmation .= $add_return['errormsg']  . " (" . $key . ")<br>";
			      }
			 }
			 
		    }
		    return $confirmation;		    
	       }
	       
	       
	       /*
	        *
	        * Get field value which could be a sub-field value.
	        *
	        */
	       public function get_field_value($mapped_gf_field, $form, $entry, $field_has_inputs) {
		    
		    $returnval = "";
		    if (array_key_exists($mapped_gf_field,$field_has_inputs)) {
			 $this_fields_inputs = $field_has_inputs[$mapped_gf_field];
			 
			 foreach ($this_fields_inputs as $field_part_key => $field_part_values) {
			      
			      $idx = (string)$field_part_values['id'];
			      if (isset($entry[$idx])) {				   
				   $part_value = $entry[$idx];
			      } else {
				   $part_value = "";
			      }
			      $returnval .= $part_value . " ";
			 }			 
		    } else {			 
			 $returnval = $entry[$mapped_gf_field];			  
		    }
		    
		    return trim($returnval);		    
	       }
	       
	        
    
} // end class
 