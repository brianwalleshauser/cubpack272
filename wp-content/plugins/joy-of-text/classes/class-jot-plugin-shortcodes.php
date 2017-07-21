<?php
/**
* Joy_Of_Text shortcodes. Processes shortcode requests
*
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_Shortcodes {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
    function __construct() {
         add_shortcode('jotform',array($this, 'process_jotform_shortcode'));
         add_shortcode('jottextus',array($this, 'process_jottextus_shortcode'));
         add_shortcode('jotgroupsend',array($this, 'process_jotgroupsend_shortcode'));
         add_shortcode('jotoptout',array($this, 'process_jotoptout_shortcode'));
         add_shortcode('jotinbox',array($this, 'process_jotinbox_shortcode'));
 
    }
 
    private static $_instance = null;
        
        public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
        } // End instance()

     public function process_jotform_shortcode ($atts) {
                
               $error = 0;
               $subhtml = "";
               
	       // Style      - new|old - option for display old style form or the new form
	       // multitype - select|multiselect|checkbox
	       $atts = shortcode_atts(
		array(
                    'id'          => '',
                    'group_id'    => '',
		    'formstyle'   => 'new',
		    'multitype'   => '',
		    'name'        => 'yes',
                    'email'       => 'no',
                    'address'     => 'no',
                    'city'        => 'no',
                    'state'       => 'no',
                    'zip'         => 'no'  			
		), $atts, 'jotform' );
              
	       	      
               // Group id in 'id' or 'group_id' fields
               if ($atts['group_id'] != "") {
                    $all_group_id = explode(",",$atts['group_id']);		   
               } elseif ($atts['id'] != "") {
                    $all_group_id = explode(",",$atts['id']);
               }
	       
	       // Select the primary group id - i.e. the first in the list    
	       $group_id = isset($all_group_id[0]) ? $all_group_id[0] : "";
          
	   
                if ( !$group_id ) {
                    // Id is not an integer          
                    $error = 1;
                } else {
                
                    //Get group invite details from database.
                    global $wpdb;
                    $table = $wpdb->prefix."jot_groupinvites";
                    $sql = " SELECT jot_grpid, jot_grpinvdesc, jot_grpinvnametxt, jot_grpinvnumtxt, jot_grpinvretchk, jot_grpinvrettxt" .
                       " FROM " . $table .
                       " WHERE jot_grpid = " . $group_id;
                    
                    
                    $groupinvite = $wpdb->get_row( $sql );
                    if (!$groupinvite) {
                        //group is not found
                        $error=2;            
                    }
                    
                    switch ( $error ) {
                             case 0;
			           $confirm_set = Joy_Of_Text_Plugin()->settings->get_groupmeta($group_id,'jot_grpinvconfirm');				  
				   $subhtml = $this->get_wrapped_jotform($group_id, $all_group_id, $groupinvite, $atts, $confirm_set);
                             break;
                             case 1;
                                  //Group ID is not an integer
                                  $subhtml = "<div>";
                                  $subhtml .= '<p>jotform shortcode error. Group ID field in shortcode is not valid.<p>';
                                  $subhtml .= '</div>';
                             break;
                             case 2:
                                  // ID not found
                                  $subhtml = "<div>";
                                  $subhtml .= '<p>jotform shortcode error. Group ID field in shortcode "' . $atts['id'] . '" is not found.<p>';
                                  $subhtml .= '</div>';
                             break;
                             default:
                             # code...
                             break;
                    }
                }
                    
               return apply_filters( 'jot_jotform_shortcode', $subhtml);
                
     }
     
     public function get_wrapped_jotform($group_id, $all_group_id, $groupinvite, $atts, $confirm_set) {
	      
	       $subhtml = '<div>';
	       $subhtml .= '<form id="jot-subscriber-form-' . $group_id . '" action="" method="post">';
	       if (count($all_group_id) == 1) {
		    $subhtml .= '<input type="hidden"  name="jot-group-id" value="' . $group_id . '">';
	       }
	       $subhtml .= '<input type="hidden"  name="jot_form_id" value="jot-subscriber-form">';
	       $subhtml .= '<input type="hidden"  name="jot-form-special"  id="jot-form-special" class="jot-special" value="">';
	       $subhtml .= '<input type="hidden"  name="jot-verified-number"  id="jot-verified-number"  value="">';
	       
	       if ($confirm_set == 1) {
		    $confirm_groupid = $group_id;				   
	       } else {
		    $confirm_groupid = "";
	       }
	      
	       $subhtml .= '<input type="hidden"  name="jot-confirm-groupid"  id="jot-confirm-groupid" value="' . $confirm_groupid . '">';
	       
	       $style = isset($atts['formstyle']) ?  $atts['formstyle'] : 'new';
	       if (strtolower($style) == 'old') {
		  $subhtml .= $this->get_old_jotform($group_id, $groupinvite, $atts, $confirm_set);
	       } else {
		  $subhtml .= $this->get_jotform($group_id, $all_group_id, $groupinvite, $atts, $confirm_set);
	       }
	       
	      
	       if ($confirm_set == 1) {
		 $subhtml .= '<fieldset class="jot-fieldset">';
		 $subhtml .= '<h3 id="jot-confirm-header">' . __("Enter your confirmation code","jot-plugin") . '</h3>';
		 $subhtml .= '<p>';
		 $subhtml .= '<label for="jot-subscribe-confirmcode">' . __("Confirmation code","jot-plugin") . '</label>';
		 $subhtml .= '<input id="jot-subscribe-confirmcode" name="jot-subscribe-confirmcode" maxlength="4" size="40" type="text"/>';
		 $subhtml .= '<p>';				    
		 $subhtml .= '<input type="button" id="jot-confirm-button" class="button" value="Subscribe"/>';
		 $subhtml .= '<p>';				     
		 $subhtml .= '</fieldset>';
	       }
	      
	       if (strtolower($style) == 'old') {
		  //
	       } else {
		    $subhtml .= '<fieldset class="jot-fieldset">';
		    $subhtml .= '<div id="jot-subscribemessage"></div>';
		    $subhtml .= '</fieldset>';
	       }
	            
	      
	       $subhtml .= '</form>';
	       $subhtml .= '</div>';
	      
	       return $subhtml;

     }
     
     public function get_jotform($group_id, $all_group_id, $groupinvite, $atts, $confirm_set) {	  
	  
	  if ($groupinvite) {
	       $jot_grpinvdesc    = $groupinvite->jot_grpinvdesc;
	       $jot_grpinvnametxt = $groupinvite->jot_grpinvnametxt;
	       $jot_grpinvnumtxt  = $groupinvite->jot_grpinvnumtxt;
	  } else {
	       $jot_grpinvdesc    = isset($atts['jot_grpinvdesc'])    ? $atts['jot_grpinvdesc']    : "";
               $jot_grpinvnametxt = isset($atts['jot_grpinvnametxt']) ? $atts['jot_grpinvnametxt'] : "";
               $jot_grpinvnumtxt  = isset($atts['jot_grpinvnumtxt'])  ? $atts['jot_grpinvnumtxt']  : "";
	  }
	  
	  
	  $subhtml = '<fieldset class="jot-fieldset">';
	  $subhtml .= '<h3 id="jot-confirm-header">' . $jot_grpinvdesc . '</h3>';
	  $subhtml .= '<p>';
	    
	  // Name field
	  $name = isset($atts['name']) ?  $atts['name'] : 'yes';
	  if (strtolower($name) == 'no') {
	    $subhtml .= '<input id="jot-subscribe-name" name="jot-subscribe-name" maxlength="40" size="40" type="hidden" value="No name given"/>';  
	  } else {
	    $subhtml .= '<label for="jot-subscribe-name">' . $jot_grpinvnametxt . '</label>';
	    $subhtml .= '<input id="jot-subscribe-name" name="jot-subscribe-name" maxlength="40" size="40" type="text"/>';
	    $subhtml .= '<p>';
	  }  
	  
	  // Number field
	  $subhtml .= '<label for="jot-subscribe-num">' . $jot_grpinvnumtxt . '</label>';
	  $subhtml .= '<input id="jot-subscribe-num" name="jot-subscribe-num" maxlength="200" size="40" type="text"/>';
	  $subhtml .= '<p>';
	  
	 
	  // Option group select
	  if (count($all_group_id) > 1) {
	  
	       switch ( strtolower($atts['multitype']) ) {
                        case 'checkbox';
			      $subhtml .= '<label for="jot-group-checkboxes">' . __("Select groups","jot-plugin") . '</label>';
			      
			      foreach ($all_group_id as $grpid) {
				   $groupdetails = Joy_Of_Text_Plugin()->settings->get_group_details($grpid);				   
				   $group_desc = isset($groupdetails->jot_groupdesc) ? $groupdetails->jot_groupdesc : "";
				   if ($group_desc == "") {
					$group_desc = sprintf(__("Group %d","jot-plugin"), $grpid );
				   }
				   $subhtml .= "<div class='jot-multi-item'>";
				   $subhtml .= '<label for="jot-group-checkbox-' . $grpid . '" class="jot-group-checkbox">' ;
				   $subhtml .= '<input id="jot-group-checkbox-' . $grpid . '" name="jot-group-id[]" type="checkbox" value="' . $grpid . '"/>'  . $group_desc;
				   $subhtml .= '</label>';
				   $subhtml .= "</div>";
				   $subhtml .= "<br>";
			      }
			      $subhtml .= "</p>";
                        break;
		        case 'select';
		        case 'multiselect';
                              $subhtml .= '<label for="jot-group-select">' . __("Select group","jot-plugin") . '</label>'  . '<br>';
			      
			      if (strtolower($atts['multitype']) == "multiselect") {
				   $multiple = " multiple ";
				   $multiarray = "[]";
			      } else {
				   $multiple = "";
				   $multiarray = "";
			      }
			      
			      $subhtml .= '<select id="jot-group-id" name="jot-group-id' . $multiarray . '"' . $multiple . '>';
			      foreach ($all_group_id as $grpid) {
				   $groupdetails = Joy_Of_Text_Plugin()->settings->get_group_details($grpid);				   
				   $group_desc = isset($groupdetails->jot_groupdesc) ? $groupdetails->jot_groupdesc : "";
				   if ($group_desc == "") {
					$group_desc = sprintf(__("Group %d","jot-plugin"), $grpid );
				   }
				   $subhtml .= '<option value="'  . $grpid . '">' . $group_desc . '</option>';
			      }
			      $subhtml .= "</select>";
			      $subhtml .= "</p>";
                        break;		        
                        default:
                              $subhtml .= "JOTFORM type not known"; 
                        break;
	       }        
	       
	
	
	  }     
	  // Button
	  if ($confirm_set == 1) {
	      $button_label = __("Get confirmation code","jot-plugin");
	  } else {
	      $button_label = __("Subscribe","jot-plugin");
	  }
	  $subhtml .= '<input type="button" id="jot-subscribegroup-button" class="button" value="' . $button_label . '"/>';
	  	  
	  $subhtml .= '</fieldset>';
                                  
	  return $subhtml;
	  
	  
     }
     
     public function get_old_jotform($group_id, $groupinvite, $atts, $confirm_set) {
	  
	  
	  $subhtml = '<table>';
	  $subhtml .= '<tr><th colspan=2 class="jot-td-c">' . $groupinvite->jot_grpinvdesc . '</th></tr>';
	    
	  if (strtolower($atts['name']) == 'no') {
	    $subhtml .= '<tr><th></th><td><input id="jot-subscribe-name" name="jot-subscribe-name" maxlength="40" size="40" type="hidden" value="No name given"/></td></tr>';  
	  } else {
	    $subhtml .= '<tr><th>' . $groupinvite->jot_grpinvnametxt . '</th><td><input id="jot-subscribe-name" name="jot-subscribe-name" maxlength="40" size="40" type="text"/></td></tr>';
	  }  
	  
	  $subhtml .= '<tr><th>' . $groupinvite->jot_grpinvnumtxt . '</th><td><input id="jot-subscribe-num" name="jot-subscribe-num" maxlength="200" size="40" type="text"/></td></tr>';
	  
	  if ($confirm_set == 1) {
	      $button_label = __("Get confirmation code","jot-plugin");
	  } else {
	      $button_label = __("Subscribe","jot-plugin");
	  }
	  $subhtml .= '<tr>';
          $subhtml .= '<td><input type="button" id="jot-subscribegroup-button" class="button" value="' . $button_label . '"/></td>';
	  $subhtml .= '<td><div id="jot-subscribemessage"></div></td>';
          $subhtml .= '</tr>';
	  $subhtml .= '</table>';
                                  
	  return $subhtml; 
	  
     }
     
     function process_jotadmin_shortcode() {
	  //ob_start();
          //echo "<iframe>";
	  //Joy_Of_Text_Plugin()->admin->settings_screen();  		     
	  //echo "</iframe>";
          //return ob_get_clean();
     }
    
     function process_jottextus_shortcode($atts) {

          $subhtml = "";
          //<div id="dialog" title="Dialog Title">I'm in a dialog</div>

          $atts = shortcode_atts(
		array(
                        'group_id'           => "",
                        'open_button_text'   => __('Text Us',"jot-plugin"),
                        'title_text'         => __('Enter your message',"jot-plugin"),
                        'name_text'          => __('Your Name',"jot-plugin"),
			'number_text'        => __('Your Number',"jot-plugin"),
                        'message_text'       => __('Message',"jot-plugin"),
                        'send_button_text'   => __('Send',"jot-plugin"),
			
		), $atts, 'jottextus' );
      
          $subhtml .= "<button id='jot-textus-opener'>" . $atts['open_button_text'] . "</button>";
          $subhtml .= "<div id='jot-textus-dialog' title='" . $atts['title_text']  . "' style='display:none;'>";
          
          // Text Us Form
          $subhtml .= '<div>';
          $subhtml .= '<form id="jot-textus-form" action="" method="post">';
          
          if ($atts['group_id'] != "") {
              $subhtml .= '<input type="hidden"  name="jot-textus-groupid" id="jot-textus-groupid" value="' . $atts['group_id'] . '">';
          }
          $subhtml .= '<input type="hidden"  name="jot-textus-special" id="jot-textus-special" class="jot-special" value="">';
          
          $subhtml .= '<table id="jot-textus-table">';
          
          // Message label and input
          $subhtml .= '<tr><th id="jot-textus-message-label">' . $atts['message_text'] . '</th></tr>';    
          $subhtml .= '<tr><td><textarea id="jot-textus-message" name="jot-textus-message" maxlength="160" rows="3"></textarea></td></tr>';
          
          // Name label and input
          $subhtml .= '<tr><th id="jot-textus-name-label">' . $atts['name_text'] . '</th></tr>';
          $subhtml .= '<tr><td><input id="jot-textus-name" name="jot-textus-name" maxlength="40" type="text"/></td></tr>';
          
          // Number label and input
          $subhtml .= '<tr><th id="jot-textus-num-label">' . $atts['number_text'] . '</th></tr>';
          $subhtml .= '<tr><td><input id="jot-textus-num" name="jot-textus-num" maxlength="200"  type="text"/></td></tr>';
          
          // Buttons    
          $subhtml .= '<tr><td>';
          $subhtml .= '<input type="button" id="jot-textus-send" class="button" value="' .  trim($atts['send_button_text']) .  '"/>';
          $subhtml .= '<input type="button" id="jot-textus-close" class="button"  value="' .  __("Close","jot-plugin") .  '"/>';
          $subhtml .= '</td></tr>';
          $subhtml .= '<tr><td><div id="jot-textusmessage"></div></td></tr>';
          $subhtml .= '</table>';
          
          $subhtml .= '</form>';
          $subhtml .= '</div>';  
          
          $subhtml .= "</div>";          
        
          return $subhtml;
               
     }
     
         
     function process_jotgroupsend_shortcode($atts) {

          $subhtml = "";
          $error = 0;
         
          $atts = shortcode_atts(
		array(
                        'group_id'           => "",
                        'member_select'      => 'no',
                        'send_button_text'   => __('Send',"jot-plugin"),
                        'message_text_title' => __("Enter your message","jot-plugin"),
                        'send_response'      => __("Messages will be sent shortly.","jot-plugin"),
                        'send_type'          => "immediate",
                        'message_type'       => "sms,mms,call"
			
		), $atts, 'jotgroupsend' );
          
          
          $sections = "";
          $subhtml .= "";
          //Joy_Of_Text_Plugin()->settings->render_message_panel ($sections, 'messages');
          
                    
          $group_id = apply_filters('jot_filter_jotgroupsend_groupid', $atts['group_id']);
                   
          if ($error == 0) {
               $can_manage_group = true;
               $can_manage_group = apply_filters('jot_can_manage_group', $can_manage_group,$group_id); 
               if (!$can_manage_group) {
                    return __("You do no have authority to manage this group.","jot-plugin");
               }
          }
         
          switch ( $error ) {
                        case 0;
                              
                              
                              // Group Send Form
                              $subhtml .= '<div>';
                              
                              $subhtml .= '<form id="jot-groupsend-form" action="" method="post">';
                              
                              if (strtolower($atts['member_select']) == 'yes') {
                                   // Render group member selector.
                                   $memselhtml = $this->get_groupsend_recipients($group_id);
                                   $subhtml .= '<input type="hidden"  name="jot-groupsend-member-select"  id="jot-groupsend-groupid" value="yes">';
                              } else {
                                   $memselhtml = "";
                                   $subhtml .= '<input type="hidden"  name="jot-groupsend-member-select"  id="jot-groupsend-groupid" value="no">';
                              }
                             
                              $subhtml .= '<input type="hidden"  name="jot-groupsend-groupid"  id="jot-groupsend-groupid" value="' . $atts['group_id'] . '">';
                              $subhtml .= '<input type="hidden"  name="jot-groupsend-special"  id="jot-groupsend-special" class="jot-special" value="">';
                              $subhtml .= '<input type="hidden"  name="jot-groupsend-response" id="jot-groupsend-response"  value="' . $atts['send_response'] . '">';
                              $subhtml .= '<input type="hidden"  name="jot-groupsend-sendtype" id="jot-groupsend-sendtype"  value="' . $atts['send_type'] . '">';
                              
                              
                              
                              $subhtml .= '<table id="jot-groupsend-table">';
                              
                              if ($memselhtml != "") {
                                   $subhtml .= "<tr>";
                                   $subhtml .= "<td>";
                                   $subhtml .= $memselhtml;
                                   $subhtml .= "</td>";
                                   $subhtml .= "</tr>";
                              }
                              
                              // Message label and input
                              $subhtml .= '<tr><th id="jot-groupsend-message-label">' . $atts['message_text_title'] . '</th></tr>';    
                              $subhtml .= '<tr><td><textarea id="jot-groupsend-message" name="jot-groupsend-message" maxlength="160" cols="70" rows="3"></textarea></td></tr>';
                                                                 
                              // Buttons    
                              $subhtml .= '<tr><td>';
                              $subhtml .= '<input type="button" id="jot-groupsend-send" class="button" style="display: inline-block;" value="' .  $atts['send_button_text'] .  '"/>';
                              $subhtml .= '</td></tr>';
                             
                              $subhtml .= '</table>';
                               
                              // Status lines
                              $subhtml .= "<div id='jot-groupsend-status'></div>";
                              $subhtml .= "<div id='jot-messagestatus'></div>";
                              $subhtml .= "<div id='jot-sendstatus-div' style='display:none'></div>";                           
                              
                              // Log line
                              //$subhtml .= '<tr><td>';                              
                              $subhtml .= '<div id="jot-groupsend-log"></div>';
                              //$subhtml .= '</td></tr>';
                                                          
                              
                              $subhtml .= '</form>';
                              //$subhtml .= '</div>';  
                              
                              $subhtml .= "</div>";          
    
                        break;
                       
                        default:
                            //Group ID is not an integer
                             $subhtml = "<div>";
                             $subhtml .= '<p>jotgroupsend shortcode - an error occurred.<p>';
                             $subhtml .= '</div>';
                        break;
          }
          
           
        
          return $subhtml;
               
     }
     
     public function process_jotoptout_shortcode($atts) {
          
          $subhtml = "";
          $error = 0;
         
          $atts = shortcode_atts(
	       array(                        
                        'enter_number_text'   => __('Enter your number',"jot-plugin"),
                        'enter_number_button' => __("Get groups","jot-plugin"),
                        'current_groups_text' => __("Select the groups you want to unsubscribe from.","jot-plugin"),
                        'unsubscribe_button'  => __("Unsubscribe","jot-plugin"),
			
		), $atts, 'jotoptout' );
          
          
          // Group Send Form
          $subhtml .= '<div>';
          $subhtml .= '<form id="jot-groupoptout-form" action="" method="post">';
                  
          $subhtml .= '<input type="hidden"  name="jot-groupoptout-special"  id="jot-groupoptout-special" class="jot-special" value="">';
                   
          
          $subhtml .= '<table id="jot-groupoptout-table">';
          
          // Message label and input
          $subhtml .= '<tr><th id="jot-groupoptout-message-label">' . $atts['enter_number_text'] . '</th></tr>';    
          $subhtml .= '<tr><td><input id="jot-groupoptout-num" name="jot-groupoptout-num" maxlength="50"  type="text"/></td></tr>';
          
                                             
          // Get groups button 
          $subhtml .= '<tr><td>';
          $subhtml .= '<input type="button" id="jot-groupout-get-groups-button" class="button" style="display: inline-block;" value="' .  $atts['enter_number_button'] .  '"/>';
          $subhtml .= '<div id="jot-groupoptout-spinner" style="display: inline-block;visibility: hidden;">';
          $subhtml .= '<img src="' . plugins_url( 'images/ajax-loader.gif', dirname(__FILE__) ) .  '">';
          $subhtml .= '</div>';
          $subhtml .= '</td></tr>';
          
          // Groups for given number  
          $subhtml .= '<tr><td>';
          $subhtml .= "<div id='jot-groupoptout-groups-display' style='display:none'>";
          $subhtml .= "<span id='jot-groupoptout-groups-heading'>";
          $subhtml .= $atts['current_groups_text'];
          $subhtml .= "</span>";
          $subhtml .= "<div id='jot-groupoptout-groups'></div>";
          $subhtml .= '<input type="button" id="jot-groupout-get-unsubscribe-button" class="button" style="display: inline-block;" value="' .  $atts['unsubscribe_button'] .  '"/>';
          $subhtml .= "</div>";          
          $subhtml .= '</td></tr>';
         
          $subhtml .= '</table>';          
          
          $subhtml .= '</form>';
        
          $subhtml .= "</div>";    
          
          return $subhtml;
          
          
     }

     public function get_groupsend_recipients($grpid) {                       
           
            $allmembers = Joy_Of_Text_Plugin()->settings->get_all_groups_and_members($grpid);
	                        
            // Show extended fields
            $extfield = 'true';
            
            
            // Remove duplicate numbers?
            $removedupes = 'true';
                      
            // Recipients div
            $html = "<div id='jottabgroupsend'>"; 
            
            // Recipients parent table
            $html .= "<table id='jot-groupsend-recip-parent-table'>"; 
            $html .= "<tr><td>";
                                     
            // Search filter controls
            $html .= "<table id='jot-groupsend-recip-groups-table-top' width='100%'>";
            $html .= "<tr>";
            $html .= "<td class='jot-td-l' width='100%'>";            
            $html .= "<input type='text' size='40' id='jot-groupsend-recip-search' value='' placeholder='" . __("Search","jot-plugin"). "'>";
            $html .= "</td>";            
            $html .= "</tr>";
            $html .= "</table>";
            
            $html .= "</td></tr>";
            $html .= "<tr><td>";
            
            $html .= "<div id='jot-groupsend-recip-div'>";            
            $html .= "<table id='jot-groupsend-recip-tab' >"; 
            $header = "";
            $fillerstyle = "";
            $style = "";
            
            foreach ($allmembers as $member) {
              
                if ($header <>  $member->jot_groupname) {
                    
                    $header = $member->jot_groupname;
                    
                    $html .= "<tr class='jot-recip-group-header'>";
                    $html .= "<td colspan=8><input type='checkbox' id='jot-recip-group-select-" . $member->jot_grpid . "' value='true'>";
                    $html .=  stripslashes($member->jot_groupname) . "</td>";                  
                    $html .= "</tr>";
                                       
                    $html .=  "<tr class='jot-mem-table-headers'>" .
                      "<td><div class='divider'></div></td>" .
                      "<td>" . __("Member Name","jot-plugin") . "</td>" .
                      "<td>" . __("Phone Number","jot-plugin") . "</td>";
                      
                    if ($extfield == 'false') {
                        $style = " style='display:none'";
                        $fillerstyle = "";
                    } else {
                        $style = "";
                        $filler = " style='display:none'";
                    }  

                    $html .= "<td class='jot-showextended'". $style . ">"  . __("Email","jot-plugin")   . "</td>" .
                             "<td class='jot-showextended'". $style . ">" . __("Address","jot-plugin")  . "</td>" .
                             "<td class='jot-showextended'". $style . ">"  . __("City","jot-plugin")    . "</td>" .
                             "<td class='jot-showextended'". $style . ">"  . __("State","jot-plugin")   . "</td>" .
                             "<td class='jot-showextended'". $style . ">"  . __("Zipcode","jot-plugin") . "</td>" .                            
                             "</tr>";
                }                     
                     
                $html .= "<tr class='jot-member'>";
                $html .= "<td width='5%' class='jot-td-c'><input type='checkbox' id='jot-groupsend-recip-mem-select-" . $member->jot_grpid . "-" . $member->jot_grpmemid . "' value='" . $member->jot_grpid . "-" . $member->jot_grpmemid . "'></td>";
                $html .= "<td width='10%' title='" . __("Member ID : ","jot-plugin") . $member->jot_grpmemid . ",\n" . __("Date added to group : ","jot-plugin") . $member->jot_grpxrefts . "'>" . $member->jot_grpmemname . "</td>";
                 
               
                $html .= "<td width='10%'>" . $member->jot_grpmemnum . "</td>";
                $html .= "<td width='13%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmememail)   . "</td>";
                $html .= "<td width='25%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmemaddress) . "</td>";
                $html .= "<td width='15%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmemcity)    . "</td>";
                $html .= "<td width='15%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmemstate )  . "</td>";
                $html .= "<td width='7%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmemzip)     . "</td>";               
                $html .= "</tr>";            
            }
            
            $html .= "</table>"; // end of jot-recip-tab
            $html .= "</div>";
          
            $html .= "</td></tr>";
            $html .= "</table>"; // end of jot-recip-parent-table
           
            $html .= "</div>";
            
            return $html;
        
        }
        
         function process_jotinbox_shortcode($atts) {

          $subhtml = "";
          $error = 0;
	  
	  // normalise attribute keys, lowercase
	  $atts = array_change_key_case((array)$atts, CASE_LOWER);
         
          $atts = shortcode_atts(
		array(
                        'group_id'           => ""
		), $atts, 'jotgroupsend' );
          
          
          $sections = "";
          $subhtml .= "";
          //Joy_Of_Text_Plugin()->settings->render_message_panel ($sections, 'messages');
          
                    
          $group_id = apply_filters('jot_filter_jotinbox_groupid', $atts['group_id']);
                   
          if ($error == 0) {
               $can_manage_group = true;
               $can_manage_group = apply_filters('jot_can_manage_group', $can_manage_group,$group_id); 
               if (!$can_manage_group) {
                    return __("You do no have authority to manage this group.","jot-plugin");
               }
          }
         
          switch ( $error ) {
                        case 0;
			 
			      // Toolbar
			      $subhtml .= "<p>";
			      $subhtml .= "<a href='#' id='jot-refresh-inbox'>" . __("Refresh","jot-plugin") . "</a>";
			      $subhtml .= "<p>";
		    
			      // Search filter controls
			      $subhtml .= "<table id='jot-groupinbox-search-tab width='100%'>";
			      $subhtml .= "<tr>";
			      $subhtml .= "<td class='jot-td-l' width='100%'>";            
			      $subhtml .= "<input type='text' size='40' id='jot-groupinbox-search' value='' placeholder='" . __("Search","jot-plugin"). "'>";
			      $subhtml .= "</td>";            
			      $subhtml .= "</tr>";
			      $subhtml .= "</table>";
			      $subhtml .= "<p>";
	                                                 
                              // Group Send Form
                              $subhtml .= "<div id='jot-groupinbox-div'>";
			      
                              
                              $all_history = $this->get_groupinbox($group_id);
                              
                              $subhtml .= '<table id="jot-groupinbox-tab">';
                              
                              $subhtml .=  "<tr class='jot-mem-table-headers'>" .
                                         "<td><div class='divider'></div></td>" .
                                         "<td>" . __("From","jot-plugin") . "</td>" .
                                         "<td>" . __("To","jot-plugin") . "</td>" .
                                         "<td>" . __("Message","jot-plugin") . "</td>" .
					 "<td>" . __("Status","jot-plugin") . "</td>" .
                                         "<td>" . __("Timestamp","jot-plugin") . "</td>" .
                                         "</tr>";
					 
			      $namelist = Joy_Of_Text_Plugin()->messenger->get_all_names();
			                                                                  
                              foreach ($all_history as $history) {
				   
				   $messagestatus = Joy_Of_Text_Plugin()->currentsmsprovider->checkstatus($history->jot_histmesstype,$history->jot_histsid,$history->jot_histstatus);

				   if ($hist->jot_histerrcode != 0 ) {
					// Twilio error codes start at 10000
					if ($messagestatus >= 10000) {
					    $messagestatus = "<a href='https://www.twilio.com/docs/errors/" . $messagestatus . "' target='_BLANK'>" . __("Twilio Error :","jot-plugin") . $messagestatus . "</a>";
					} 
				   }
                                        
                                   $subhtml .= "<tr class='jot-member'>";
                                   $subhtml .= "<td width='5%' class='jot-td-c'><input type='checkbox' id='jot-groupinbox-" . $history->jot_histid . "' value='" . $history->jot_histid . "'></td>";
                                   $subhtml .= "<td width='10%'>" . stripslashes(esc_attr( Joy_Of_Text_Plugin()->settings->get_name($history->jot_histfrom, $namelist) )) . "</td>";  
                                   $subhtml .= "<td width='10%'>" . stripslashes(esc_attr( Joy_Of_Text_Plugin()->settings->get_name($history->jot_histto, $namelist) )) . "</td>";
                                   $message_body = apply_filters('jot_filter_inbox_message_body',$history->jot_histmesscontent);
				   $subhtml .= "<td title='" . esc_attr($message_body)  . "'>" . $message_body . "</td>";
				   $subhtml .= "<td >" . $messagestatus . "</td>";
                                   $subhtml .= "<td >" . $history->jot_histts . "</td>";
                                   $subhtml .= "</tr>";            
                              }
                              
                              /*
                               $sql = " SELECT a.jot_histid, a.jot_histsid, a.jot_histfrom, a.jot_histto, a.jot_histprovider, a.jot_histmesscontent, a.jot_histmesstype, " .
              " a.jot_histstatus,DATE_FORMAT(a.jot_histts,'%m-%d-%Y %T' ) as jot_histts,a.jot_histmedia, a.jot_histprice, " . 
              " jot_histts as sort_histts, a.jot_histerrcode, a.jot_histmesssubtype " .
                              */
                               
                               
                              
                              $subhtml .= '</table>';
                               
                              
                              $subhtml .= "</div>";          
    
                        break;
                       
                        default:
                            //Group ID is not an integer
                             $subhtml = "<div>";
                             $subhtml .= '<p>jotinbox shortcode - an error occurred.<p>';
                             $subhtml .= '</div>';
                        break;
          }
          
           
        
          return $subhtml;
               
     }
     
     public function get_groupinbox($group_id = ""){           
        
          global $wpdb;
          
	  if ($group_id == "") {
	       $tablehist = $wpdb->prefix."jot_history";
	       $tablemems = $wpdb->prefix."jot_groupmembers";
	       $sql = " SELECT a.jot_histid, a.jot_histsid, a.jot_histfrom, a.jot_histto, a.jot_histprovider, a.jot_histmesscontent, a.jot_histmesstype, " .
		   " a.jot_histstatus,DATE_FORMAT(a.jot_histts,'%m-%d-%Y %T' ) as jot_histts,a.jot_histmedia, a.jot_histprice, " . 
		   " jot_histts as sort_histts, a.jot_histerrcode, a.jot_histmesssubtype " .
		   " FROM " . $tablehist . " a " .
		   " ORDER BY 9 DESC";
	  } else {
	       
	       $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
	       $tablexref = $wpdb->prefix."jot_groupmemxref";   // b
	       $tablegrps = $wpdb->prefix."jot_groups"; //c
	       $tablehist = $wpdb->prefix."jot_history";
	       $tablemems = $wpdb->prefix."jot_groupmembers";
	       
	       
	       if (!is_array($group_id) && $group_id !="") {
		    $group_id = array($group_id);
	       }
	       
	       
	       $group_list = apply_filters('jot_groupinbox_filter_grouplist', $group_id);
	       $group_list = implode( ", ", $group_id );
	    
	   
	    	       
	       $numbers_list = "SELECT  a.jot_grpmemnum  " . 
		    " FROM " . $tablegrpmem .  " a," . $tablexref . " b " . 
		    " WHERE a.jot_grpmemid = b.jot_grpmemid " .
		    " AND b.jot_grpid IN (" . $group_list . ")";	       
	      
	       $numbers_list = apply_filters('jot_groupinbox_filter_numberslist', $numbers_list, $group_id);
	        
	       if (!empty($group_id) && !empty($numbers_list)) {		   
		    $grpclause = " AND (a.jot_histfrom IN (" .
	            $numbers_list .
		    " ) OR" .
		    " a.jot_histto IN (" .
		    $numbers_list . 
		    " ) )" ;
	       } else {
		    $grpclause = "";
	       }
	       
	       $sql = " SELECT a.jot_histid, a.jot_histsid, a.jot_histfrom, a.jot_histto, a.jot_histprovider, a.jot_histmesscontent, a.jot_histmesstype, " .
		   " a.jot_histstatus,DATE_FORMAT(a.jot_histts,'%m-%d-%Y %T' ) as jot_histts,a.jot_histmedia, a.jot_histprice, " . 
		   " jot_histts as sort_histts, a.jot_histerrcode, a.jot_histmesssubtype " .
		   " FROM " . $tablehist . " a " .
		   " WHERE 1=1 " .
		   $grpclause .
		   " ORDER BY 9 DESC";
	       
	  }
          $result =  $wpdb->get_results( $sql );
          //Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Inbox SQL " . $sql . " >" . print_r($result,true));
          return apply_filters('jot_get_inbox',$result);
            
        }
     
} // end class