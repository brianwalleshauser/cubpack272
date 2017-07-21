<?php
/**
* Joy_Of_Text System Info. Show useful configuration info, used for support queries
*
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_SystemInfo {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
     function __construct() {
        
     } // end constructor
 
     private static $_instance = null;
        
        public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
        } // End instance()

     
     public function render_system_info($sections, $tab) {
       
       $html = "";
       $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-systeminfo','',$this->get_system_info(),$tab);
       return $html;
     
     }
        
     public function get_system_info() {
          global $wpdb;
          
          $html = "";
          
          if ( get_bloginfo( 'version' ) < '3.4' ) {
		$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
          } else {
		$theme_data = wp_get_theme();
		$theme      = $theme_data->Name . ' ' . $theme_data->Version;
          }
     
          $html = "** Site Settings **\n";
          $curl_support = function_exists( 'curl_init' )  ? 'Supported.' : 'Your server does not support cURL.';
          $html .= "cURL:                      " . $curl_support . "\n"; 
          $multi = is_multisite() ? 'Yes'  : 'No';
          $html .= "Multisite:                 " . $multi . "\n" ;
          $html .= "SITE_URL:                  " . site_url() . "\n" ;
          $html .= "HOME_URL:                  " . home_url() . "\n" ;
          $html .= "WordPress Version:         " . get_bloginfo( 'version' ) . "\n";
          $html .= "Permalink Structure:       " . get_option( 'permalink_structure' ) . "\n"; 
          $html .= "Active Theme:              " . $theme . "\n";
          $debug_status = defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : '<<Not set>>' . "\n";
          $html .= "WP_DEBUG:                  " . $debug_status;
          $cron_status = defined( 'DISABLE_WP_CRON' ) ? WP_CRON ? 'true' . "\n" : 'false' . "\n" : '<<Not set>>' . "\n";
          $html .= "DISABLE_WP_CRON:           " . $cron_status;
          $html .= "WP Table Prefix:           " . $wpdb->prefix . " Length: ". strlen( $wpdb->prefix ) . " Status: ";
          if ( strlen( $wpdb->prefix )>16 ) {
               $html .= " ERROR: Too Long";
          } else {
               $html .= " Acceptable";
          }
          $html .= "\n\n";
          
          $html .= "** PHP Settings **\n";
          $html .= "PHP Version:               " . PHP_VERSION . "\n";          
          $html .= "Web Server Info:           " . $_SERVER['SERVER_SOFTWARE'] . "\n"; 
          $html .= "WordPress Memory Limit:    " . WP_MEMORY_LIMIT . "\n";
          $html .= "PHP Memory Limit:          " . ini_get( 'memory_limit' ) . "\n"; 
          $html .= "PHP Upload Max Size:       " . ini_get( 'upload_max_filesize' ) . "\n";
          $html .= "PHP Post Max Size:         " . ini_get( 'post_max_size' ) . "\n";
          $html .= "PHP Upload Max Filesize:   " . ini_get( 'upload_max_filesize' ) . "\n";
          $html .= "PHP Time Limit:            " . ini_get( 'max_execution_time' ) . "\n"; 
          $html .= "PHP Max Input Vars:        " . ini_get( 'max_input_vars' ) . "\n";
          $html .= "PHP Arg Separator:         " . ini_get( 'arg_separator.output' ) . "\n";
          $allowurlfileopen = ini_get( 'allow_url_fopen' ) ? "Yes\n" : "No\n";
          $html .= "PHP Allow URL File Open:   " . $allowurlfileopen ;
                    
          $html .= "\n\n";
          
          $html .= "** Active plugins **\n";
          $plugins = get_plugins();
          $active_plugins = get_option( 'active_plugins', array() );
          
          $html .= "Active Plugins:" . "\n";
          foreach ( $plugins as $plugin_path => $plugin ) {
               // If the plugin isn't active, don't show it.
               if ( ! in_array( $plugin_path, $active_plugins ) ) {
                    	continue;
               }
               $html .= "                           ". $plugin['Name'] . ', Version : ' . $plugin['Version'] ."\n";
          }
          $html .= "\n\n";
          
	  $product_display_name = Joy_Of_Text_Plugin()->product_display_name;
          $product_display_name = apply_filters('jot_whitelabel_product_display_name', $product_display_name);
          $html .= "** " . $product_display_name . " Settings **\n";
          $html .= "Product                " . $product_display_name . "\n";
          $html .= "Version                " . Joy_Of_Text_Plugin()->version . "\n\n";
          
          // Twilio Settings
          $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
          $html .= "** Twilio Settings **\n";
          
          //Check connection to Twilio
          $url="https://pricing.twilio.com/v1/PhoneNumbers/Countries/US";
          $data = array();
          $jot_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'get');
          $err_json = json_decode($jot_response);
          
          if ($err_json->country != "" ) {
               $twilio_connection = "Connecting successfully";
          } else {
               $twilio_connection = "Not connecting";
          }
          
          $messsrv_sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservsid-' . $selected_provider);
          $twilio_acc_details_json = Joy_Of_Text_Plugin()->currentsmsprovider->getAccountDetails();
          $twilio_acc_details = json_decode($twilio_acc_details_json);
          $twilio_acc_status = isset($twilio_acc_details->status) ? $twilio_acc_details->status : "";
          $twilio_acc_type   = isset($twilio_acc_details->type) ? $twilio_acc_details->type : "";
         
          $html .= "Twilio Connection          " . $twilio_connection . "\n";
          $html .= "Twilo Account SID:         " . Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider) . "\n";
          $html .= "Twilio Account Status      " . (($twilio_acc_status != "") ? $twilio_acc_status . "\n" : "<<Not set>>" . "\n");
          $html .= "Twilio Account Type        " . (($twilio_acc_type != "") ? $twilio_acc_type . "\n" : "<<Not set>>" . "\n");
         
          $auth_token = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-authsid-' . $selected_provider) . "\n";
          $html .= "Twilo Auth Token Length:   " . strlen($auth_token) . "\n";
         
          $html .= "Messaging Service SID:     " . (($messsrv_sid != "") ? $messsrv_sid . "\n" : "<<Not set>>" . "\n");
          $messsrv_selected = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservchk') == true ? "Yes" : "No";
          $html .= "Message Services enabled:  " . $messsrv_selected . "\n";
          $selected_number = Joy_Of_Text_Plugin()->settings->get_current_smsprovider_number();
          $html .= "Selected Twilio number:    " . $selected_number . "\n";
          
          $all_numbers = Joy_Of_Text_Plugin()->currentsmsprovider->getPhoneNumbers($selected_number);
          $all_numbers_api_response = isset($all_numbers['full_api_response']) ? $all_numbers['full_api_response'] : array();
          $sms_url = Joy_Of_Text_Plugin()->currentsmsprovider->getNumberAttribute($all_numbers_api_response,"sms_url", "<<Not set>>", $selected_number);
          $sms_fallback_url = Joy_Of_Text_Plugin()->currentsmsprovider->getNumberAttribute($all_numbers_api_response,"sms_fallback_url", "<<Not set>>", $selected_number);
          $sms_method = Joy_Of_Text_Plugin()->currentsmsprovider->getNumberAttribute($all_numbers_api_response,"sms_method", "<<Not set>>", $selected_number);
          $incoming_numbers = $all_numbers_api_response->incoming_phone_numbers;
          $html .= "SMS URL                    " . $sms_url . "\n"; 
          $html .= "SMS Fallback URL           " . $sms_fallback_url . "\n"; 
          $html .= "SMS Method                 " . $sms_method . "\n"; 
        
          
          $allnumbers = Joy_Of_Text_Plugin()->currentsmsprovider->getPhoneNumbers();
          unset($allnumbers['all_numbers']['default']);
          $allnumbers_string = implode(",\n                           ",$allnumbers['all_numbers']);
          $html .= "All Twilio numbers:        " . $allnumbers_string . "\n";
          
          
          $html .= "Country Code:              " . Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-smscountrycode') . "\n\n";
          
          // Licence Settings       
          $html .= "** Licence Settings **\n";
          $html .= "Licence Key:               " . Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-eddlicence') . "\n";
          $html .= "Licence Status:            " . Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-eddlicencestatus')  . "\n\n";
         
          // Inbound Settings       
          $html .= "** Inbound Message Settings **\n";
          $html .= "SMS Request URL:           " .  get_site_url() . "?inbound" . "\n\n";
          
          // Notification Settings       
          $html .= "** Notification Settings **\n";          
          $jot_inbsmschk = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbsmschk') == true ? "Yes" : "No";
          $jot_inbsubchk = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbsubchk') == true ? "Yes" : "No";
          $html .= "SMS notification:          " .  $jot_inbsmschk . "\n";
          $html .= "Subscription notification: " .  $jot_inbsubchk . "\n";
          $html .= "Notifications sent to:     " .  Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbsmsnum') . "\n";
          $html .= "Email address:             " .  Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbemail') . "\n\n";
           
          // General Settings       
          $html .= "** General Settings **\n";          
          $def_senderid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-smssenderid');
          $html .= "Default SenderID:          " .  (($def_senderid != "") ? $def_senderid . "\n" : "<<Not set>>" . "\n");
          $html .= "Default open tab:          " .  Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-defaulttab') . "\n";
	  $admin_groupid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-admingroup');
	  if ($admin_groupid != "" && $admin_groupid != -1) {
	    $admin_groupid_display = " (" . $admin_groupid . ")";
	  } else {
	    $admin_groupid_display = "";
	  }
          $html .= "Admin number group:        " .  Joy_Of_Text_Plugin()->messenger->get_jot_groupname($admin_groupid) . $admin_groupid_display . "\n";
	  $html .= "Voice gender:              " .  Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-voice-gender') . "\n";
          $html .= "Voice Accent:              " .  Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-voice-accent') . "\n\n";
          
          // Message tab Settings       
          $html .= "** Message Tab Settings **\n";
          $jot_removedupes = Joy_Of_Text_Plugin()->settings->get_message_settings('jot-message-removedupes');
          $senderid = Joy_Of_Text_Plugin()->settings->get_message_settings('jot-message-senderid');
          
          $html .= "SenderID:                  " .  (($senderid != "") ? $senderid . "\n" : "<<Not set>>" . "\n");
          $html .= "Remove duplicates:         " .  ($jot_removedupes  != "" ? "Yes" : "No");
          $html .= "\n\n";
          
          // Group details.
          $html .= "** Group Settings **\n";
          $table_groups = $wpdb->prefix."jot_groups"; //a
          $table_memxref = $wpdb->prefix."jot_groupmemxref"; //b
          $table_grpmem =  $wpdb->prefix."jot_groupmembers"; //c
          $sql = " SELECT a.jot_groupid as jot_groupid, jot_groupname ,jot_groupdesc, jot_groupoptout, jot_groupautosub, jot_ts, count(*) as jot_memcount " .
                   " FROM " . $table_groups . " a, " .$table_memxref  . " b, " . $table_grpmem . " c " .
                   " WHERE a.jot_groupid = b.jot_grpid " .
                   " AND b.jot_grpmemid = c.jot_grpmemid " .
                   " GROUP BY a.jot_groupid, jot_groupname ,jot_groupdesc, jot_groupoptout, jot_groupautosub, jot_ts " .
                   " UNION " .
                   " SELECT jot_groupid, jot_groupname ,jot_groupdesc, jot_groupoptout, jot_groupautosub, jot_ts, 0 as jot_memcount " .
                   " FROM " . $table_groups  .
                   " WHERE NOT EXISTS (SELECT 1 FROM " . $table_memxref . " WHERE jot_groupid = jot_grpid) " .
                   " ORDER BY 6 DESC";
            
          $grouplist = $wpdb->get_results( $sql );
                    
          $allgroups = "";
          foreach ($grouplist as $group) {
               $group_invite = Joy_Of_Text_Plugin()->settings->get_group_invite($group->jot_groupid);
               $jot_redirect = Joy_Of_Text_Plugin()->settings->get_groupmeta($group->jot_groupid, 'jot_grpinvredirect');
               $jot_grpinvwelchk_jot_add  = Joy_Of_Text_Plugin()->settings->get_groupmeta($group->jot_groupid, 'jot_grpinvwelchk_jot_add');
               $jot_grpinvwelchk_jot_copy = Joy_Of_Text_Plugin()->settings->get_groupmeta($group->jot_groupid, 'jot_grpinvwelchk_jot_copy');
               $jot_grpinvwelchk_jot_move = Joy_Of_Text_Plugin()->settings->get_groupmeta($group->jot_groupid, 'jot_grpinvwelchk_jot_move');
               
               // Get Gravity Mappings
               $gf_mappings_json =  Joy_Of_Text_Plugin()->settings->get_groupmeta($group->jot_groupid, 'gf_mappings');
	       $gf_mappings = json_decode($gf_mappings_json,true);
		    
               // Is this metadata for the current selected group?
               if (isset($gf_mappings['jot-gravityforms'])) {
                    $gf_form = "                           Gravity Form ID:           " . ($gf_mappings['jot-gravityforms']) . "\n" ;
               } else {
                    $gf_form = "";
                        
               }
               
               $allgroups .= "\n" . 
                             "                           ID:                        " . $group->jot_groupid . "\n" .
                             "                           Name:                      " . $group->jot_groupname . "\n" .
                             "                           Auto Group:                " . ($group->jot_groupautosub != 0 ? "Yes" : "No") . "\n" .
                             "                           Opt out:                   " . ($group->jot_groupoptout != "" ? $group->jot_groupoptout  : "<<Not set>>") . "\n" .
                             "                           Members:                   " . $group->jot_memcount . "\n" .
                             "                           Redirect to:               " . ($jot_redirect != "" ? $jot_redirect : "<<Not set>>")  . "\n" . 
                             "                           Subscription Keyword:      " . ($group_invite->jot_grpinvaddkeyw != "" ? $group_invite->jot_grpinvaddkeyw : "<<Not set>>"). "\n" .
                             "                           Welcome message enabled:   " . ($group_invite->jot_grpinvretchk == 1 ? "Yes" : "No" ) . "\n" .
                             "                           Welcome message type:      " . ($group_invite->jot_grpinvmesstype) . "\n" .
                             "                           Welcome message on ADD:    " . ($jot_grpinvwelchk_jot_add != "" ? "Yes" : "No" ) . "\n" .
                             "                           Welcome message on COPY:   " . ($jot_grpinvwelchk_jot_copy != "" ? "Yes" : "No" ) . "\n" .
                             "                           Welcome message on MOVE:   " . ($jot_grpinvwelchk_jot_move != "" ? "Yes" : "No" ) . "\n" .
                             "                           Mapped to a Gravity Form:  " . (isset($gf_mappings['jot-gravityforms']) ? "Yes" : "No")  . "\n" .
                             $gf_form .
                             "\n";
                             
                
               ;                 
          }
          $html .= "Groups:               " . $allgroups;
          return $html;
     }
     
     
     
    
} // end class
 