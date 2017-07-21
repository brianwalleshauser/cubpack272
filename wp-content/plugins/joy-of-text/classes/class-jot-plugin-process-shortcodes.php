<?php
/**
*
* Joy_Of_Text Process Shortcodes. Processess requests from the front end shortcodesadmin pages
*
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_Process_Shortcodes {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
    public function __construct() {                
                
                // Process jotform shortcode
                add_action( 'wp_ajax_process_subscriber_form', array( $this, 'process_subscriber_form' ) );
                add_action( 'wp_ajax_nopriv_process_subscriber_form', array( $this, 'process_subscriber_form' ) );
                
                // Process confirmed jotform shortcode
                add_action( 'wp_ajax_process_confirmed_subscriber_form', array( $this, 'process_confirmed_subscriber_form' ) );
                add_action( 'wp_ajax_nopriv_process_confirmed_subscriber_form', array( $this, 'process_confirmed_subscriber_form' ) );
                
                
                // Process textus form
                add_action( 'wp_ajax_process_textus_send', array( $this, 'process_textus_send' ) );
                add_action( 'wp_ajax_nopriv_process_textus_send', array( $this, 'process_textus_send' ) );
                
                // Process jotgroupsend form
                add_action( 'wp_ajax_process_jotgroupsend', array( $this, 'process_jotgroupsend' ) );
                add_action( 'wp_ajax_nopriv_process_jotgroupsend', array( $this, 'process_jotgroupsend' ) );
                
                // Process jotoptout form
                add_action( 'wp_ajax_process_jotoptout_getgroups', array( $this, 'process_jotoptout_getgroups' ) );
                add_action( 'wp_ajax_nopriv_process_jotoptout_getgroups', array( $this, 'process_jotoptout_getgroups' ) );
                add_action( 'wp_ajax_process_jotoptout_unsubscribe', array( $this, 'process_jotoptout_unsubscribe' ) );
                add_action( 'wp_ajax_nopriv_process_jotoptout_unsubscribe', array( $this, 'process_jotoptout_unsubscribe' ) );
                
                // Process jotinbox refresh shortcode
                add_action( 'wp_ajax_process_refresh_inbox', array( $this, 'process_refresh_inbox' ) );
                add_action( 'wp_ajax_nopriv_process_refresh_inbox', array( $this, 'process_refresh_inbox' ) );
                
                // Generate HTML for Group Invite
                add_action( 'wp_ajax_process_generate_invite_html', array( $this, 'process_generate_invite_html' ) );
                                            
        
    } // end constructor
 
    private static $_instance = null;
        
    public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
    } // End instance()

     
   public function process_subscriber_form() {
            
            
            $formdata = $_POST['formdata'];
            parse_str($formdata, $output);
               
            if (is_array($output['jot-group-id'])) {
                $allgroups = $output['jot-group-id'];
            } else {
                $allgroups = array();
                $allgroups[] = $output['jot-group-id'];
            }
                      
            $jot_grpmemnum = wp_strip_all_tags($output['jot-subscribe-num']);
            $jot_grpmemname = wp_strip_all_tags($output['jot-subscribe-name']);
            
            // Get extended member info if present
            $jot_grpmememail   = isset($output['jot_grpmememail'])   ? wp_strip_all_tags($output['jot_grpmememail'])   : "";
            $jot_grpmemaddress = isset($output['jot_grpmemaddress']) ? wp_strip_all_tags($output['jot_grpmemaddress']) : "";
            $jot_grpmemcity    = isset($output['jot_grpmemcity'])    ? wp_strip_all_tags($output['jot_grpmemcity'])    : "";
            $jot_grpmemstate   = isset($output['jot_grpmemstate'])   ? wp_strip_all_tags($output['jot_grpmemstate'])   : "";
            $jot_grpmemzip     = isset($output['jot_grpmemzip'])     ? wp_strip_all_tags($output['jot_grpmemzip'])     : "";
                 
            // Spam bot check
            $jot_form_special = isset($output['jot-form-special']) ? wp_strip_all_tags($output['jot-form-special']) : "";
            
            
            // Has confirmation been set for any of the selected groups?            
            $confirm_groupid     = isset($output['jot-confirm-groupid']) ? $output['jot-confirm-groupid']   : -1;
            
            if ($confirm_groupid == "") {
                $confirm_groupid = -1;
            }            
            
            $add_args = array(
                                    'jot-group-id'        => $allgroups,
                                    'jot-confirm-groupid' => $confirm_groupid,
                                    'jot-subscribe-name'  => $jot_grpmemname,
                                    'jot-subscribe-num'   => $jot_grpmemnum,
                                    'jot_grpmememail'     => $jot_grpmememail,
                                    'jot_grpmemaddress'   => $jot_grpmemaddress,
                                    'jot_grpmemcity'      => $jot_grpmemcity,
                                    'jot_grpmemstate'     => $jot_grpmemstate,
                                    'jot_grpmemzip'       => $jot_grpmemzip,
                                    'jot-form-special'    => $jot_form_special
                                    
                             );
            
            $parse_return = $this->parse_jotform_input($add_args);
                    
            if ($parse_return['errorcode'] !=0) {
                $response = array('errormsg'=> $parse_return['errormsg'], 'errorcode' => $parse_return['errorcode'], 'sqlerr' => '' );
                echo json_encode($response);
                die();
            }
            
            if ($confirm_groupid == -1) {
                        // Add/Update subscriber records
                        $this->add_subscriber_details($parse_return);
            } else {
                        // Write confirmation form
                        $this->write_confirmation_code($parse_return);      
            }
        
   }
   
   public function process_confirmed_subscriber_form() {
            
            $errorcode = 0;
            $formdata = $_POST['formdata'];
            parse_str($formdata, $output);
                        
            if (is_array($output['jot-group-id'])) {
                $allgroups = $output['jot-group-id'];
            } else {
                $allgroups = array();
                $allgroups[] = $output['jot-group-id'];
            }
                            
            $jot_grpmemnum = wp_strip_all_tags($output['jot-subscribe-num']);
            $jot_grpmemname = wp_strip_all_tags($output['jot-subscribe-name']);
            
            // Get extended member info if present
            $jot_grpmememail   = isset($output['jot_grpmememail'])   ? wp_strip_all_tags($output['jot_grpmememail'])   : "";
            $jot_grpmemaddress = isset($output['jot_grpmemaddress']) ? wp_strip_all_tags($output['jot_grpmemaddress']) : "";
            $jot_grpmemcity    = isset($output['jot_grpmemcity'])    ? wp_strip_all_tags($output['jot_grpmemcity'])    : "";
            $jot_grpmemstate   = isset($output['jot_grpmemstate'])   ? wp_strip_all_tags($output['jot_grpmemstate'])   : "";
            $jot_grpmemzip     = isset($output['jot_grpmemzip'])     ? wp_strip_all_tags($output['jot_grpmemzip'])     : "";
                 
            $verified_number     = isset($output['jot-verified-number'])         ? wp_strip_all_tags($output['jot-verified-number'])   : "";
            $confirm_groupid     = isset($output['jot-confirm-groupid'])         ? wp_strip_all_tags($output['jot-confirm-groupid'])   : "";
            $entered_confirmcode = isset($output['jot-subscribe-confirmcode'])   ? wp_strip_all_tags($output['jot-subscribe-confirmcode'])   : "";
            
            if ($entered_confirmcode == "") {
                // Blank code entered
                $errorcode = 3;
            }
            
            if ($errorcode == 0) {
                        if ($verified_number == "" || $confirm_groupid == "" ) {
                           $errorcode = 1;
                        } else {
                             $confirmcode = Joy_Of_Text_Plugin()->settings->get_groupmeta($confirm_groupid,'jot_grpconfcode_' . $verified_number);
                             $confirmcode_arr = json_decode($confirmcode,true);
                            
                             
                             if (isset($confirmcode_arr['cc']) ) {                        
                                    if ($confirmcode_arr['cc'] ==  $entered_confirmcode) {
                                        
                                         $add_args = array(
                                                'jot-group-id'        => $allgroups,
                                                'jot-subscribe-name'  => $jot_grpmemname,
                                                'jot-subscribe-num'   => $jot_grpmemnum,
                                                'jot-verified-num'    => $verified_number,
                                                'jot_grpmememail'     => $jot_grpmememail,
                                                'jot_grpmemaddress'   => $jot_grpmemaddress,
                                                'jot_grpmemcity'      => $jot_grpmemcity,
                                                'jot_grpmemstate'     => $jot_grpmemstate,
                                                'jot_grpmemzip'       => $jot_grpmemzip                                    
                                         );
                                         
                                         // Write subscription timestamp
                                         $confirmcode_arr['st'] = current_time('mysql', 0);                            
                                         $return =Joy_Of_Text_Plugin()->settings->save_groupmeta($confirm_groupid,'jot_grpconfcode_' . $verified_number, json_encode($confirmcode_arr));
                                                                     
                                         // Add subscriber - processing will end after this.
                                         $this->add_subscriber_details($add_args);
                                         
                                    } else {
                                         $errorcode = 3;
                                    }
                             } else {
                                 $errorcode = 2;
                             }
                        }
            }
            
            switch ( $errorcode ) {
                        case 0; // All fine
                                // Subscriber will be added
                        break;
                        case 1; // 
                               $errormsg = __("A problem occurred. Please try again.","jot-plugin");      
                        break;
                        case 2; //                            
                               $errormsg = __("Confirmation code cannot be validated.","jot-plugin");
                        break;
                        case 3; // Error sending message                   
                               $errormsg = __("Confirmation code is incorrect.<br><br>Please try again.","jot-plugin");
                        break;                        
                        default:
                               $errormsg = __("There's been a problem validating the confirmation code.","jot-plugin");
                        break;
            }         
                        
            
            $response = array('errormsg'=> $errormsg, 'errorcode' => $errorcode, 'url'=> '', 'sqlerr' => '', 'lastid' => '', 'message_error' => '', 'number' => $verified_number, 'confirmgroup' => $confirm_args['jot-confirm-groupid'] );
            echo json_encode($response);           
            die();
            
   }
   
   public function write_confirmation_code($confirm_args) {
            
            $confirm_code_written = false;
            $errorcode = 0;
            $errormsg  = '';
            
            // Write confirmation code
            $verified_number = $confirm_args['jot-verified-num'];
            $confirm_code = rand ( 1000 , 9999 );
            $confirm_key  = 'jot_grpconfcode_' . $verified_number;
            $confirm_meta = array( 'cc' => $confirm_code,
                                    'ts' => current_time('mysql', 0)
                                  );
            $confirm_meta_json = json_encode($confirm_meta);
            $return =Joy_Of_Text_Plugin()->settings->save_groupmeta($confirm_args['jot-confirm-groupid'], $confirm_key, $confirm_meta_json);
           
            if (isset($return['success'])) {
                if ($return['success'] > 0) {
                        $confirm_code_written = true;
                } else {
                        $confirm_code_written = false;
                        $errorcode = 1;                        
                }
            } else {
               // Confirmation code wasn't written
               $confirm_code_written = false;
               $errorcode = 2;
               
            }
            
            if ($confirm_code_written === true) {
                 // Get HTML for confirmation form
                 
                 // Send confirmation code to subscriber
                 $confirm_msg = sprintf(__("Your subscription code is : %s","jot-plugin"),$confirm_code);
                 $msgerr = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($verified_number, $confirm_msg );                                    
                 $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                   'jot_messsubtype' => 'CC'
                                 );
                 $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",9999998,$msgerr,$collate_args);
                 if ($msgerr['send_message_errorcode'] != 0) {
                        $confirm_code_written = false;
                        $errorcode = 3;
                 }
            } 
                     
            switch ( $errorcode ) {
                        case 0; // All fine
                               $errormsg = __("Confirmation code sent. Please enter code.","jot-plugin");                        
                        break;
                        case 1; // insert failed
                               $errormsg = __("There's been a problem writing the confirmation code.","jot-plugin");      
                        break;
                        case 2; //                            
                               $errormsg = __("There's been a problem writing the confirmation code..","jot-plugin");
                        break;
                        case 3; // Error sending message                   
                               $errormsg = __("There's been a problem sending the confirmation code...","jot-plugin");
                        break;                        
                        default:
                               $errormsg = __("There's been a problem writing the confirmation code....","jot-plugin");
                        break;
            }         
                        
            
            $response = array('errormsg'=> $errormsg, 'errorcode' => $errorcode, 'url'=> '', 'sqlerr' => '', 'lastid' => '', 'message_error' => '', 'number' => $verified_number, 'confirmgroup' => $confirm_args['jot-confirm-groupid'] );
            echo json_encode($response); 
            
            die();
   }
   
   public function parse_jotform_input($add_args) {
      
            $msg = "";
            $msgerr = "";
            
            // Check group(s) selected         
            $allgroups = $add_args['jot-group-id'];
            
            if (empty($allgroups[0])) {
                unset($allgroups[0]);
                $error = 8;                
            }
            
            $jot_grpmemname = $add_args['jot-subscribe-name'];
            
            // Get extended member info if present
            $jot_grpmememail   = $add_args['jot_grpmememail'];
            $jot_grpmemaddress = $add_args['jot_grpmemaddress'];
            $jot_grpmemcity    = $add_args['jot_grpmemcity'];
            $jot_grpmemstate   = $add_args['jot_grpmemstate'];
            $jot_grpmemzip     = $add_args['jot_grpmemzip'];
            
            // Spam bot check
            $jot_form_special = $add_args['jot-form-special'];
                                            
            if ($jot_form_special != "") {
                // Filled in by a bot so end
                $error = 7;
                $msg = __("Form completed by spam bot.","jot-plugin");  
            }   
       
            // Check name is entered
            if (!isset($jot_grpmemname) || str_replace(' ', '',$jot_grpmemname) == '') {
                $error = 4;         
            }     
               
        
            //Strip spaces out of number
            $phone_num = Joy_Of_Text_Plugin()->options->parse_phone_number($add_args['jot-subscribe-num']);
            
            if ($error == 0) {
                        foreach ($allgroups as $jot_grpid) {
                                
                                $error = 0;
                                $verified_number = 0;
                                
                                // Does phone number start with a plus
                                $removed_plus = false;
                                if (preg_match('/^\+/', $phone_num)) {
                                    $phone_num = substr($phone_num,1);
                                    $removed_plus = true;
                                }
                                
                                if (!is_numeric($phone_num)) {
                                    $error = 2;
                                    break;
                                }
                                
                                if ($removed_plus) {
                                    $phone_num = "+" . $phone_num;
                                }
                                 
                                if ($error == 0) {                
                                    $verified_number = Joy_Of_Text_Plugin()->options->get_verified_number_for_group($jot_grpid,$phone_num);
                                    if ( $verified_number == "") {
                                        $error = 5;
                                    } else {
                                        $add_args['jot-verified-num'] = $verified_number;
                                    }
                                }
                            
                                // Does this number already exist in the group?
                                if (Joy_Of_Text_Plugin()->options->number_exists($verified_number, $jot_grpid) && $error == 0) {
                                    $error = 3;
                                    break;
                                }
                                
                                // Check email address
                                if (isset($output['jot_grpmememail'])) {
                                        if (!is_email($jot_grpmememail)) {
                                                $error=6;
                                                break;
                                        }
                                }                            
                        } // for All groups
            }
            
            switch ( $error ) {
                        case 0; // All fine
                               $msg = __("No parse errors", "jot-plugin");                               
                        break;
                        case 1; // insert failed
                               $msg = __("An error occurred when subscribing.", "jot-plugin");         
                        break;
                        case 2; // None numeric phone number                           
                               $msg = sprintf( __( "The phone number '%s' is not numeric. Please try again", "jot-plugin" ), esc_html($phone_num) );
                        break;
                        case 3; // Number already exists in this group.
                                $groupinvite_alreadysub_message = Joy_Of_Text_Plugin()->settings->get_groupmeta($jot_grpid,'jot_grpinvalreadysub');
                                
                                if ($groupinvite_alreadysub_message != "") {
                                        // Already subbed message set for this group
                              
                                        // Get member details         
                                        $member = Joy_Of_Text_Plugin()->messenger->get_member_from_num($verified_number);                              
                                        $msg = Joy_Of_Text_Plugin()->messenger->get_replace_tags($groupinvite_alreadysub_message,$member,$jot_grpid);
                                } else {
                                        $groupdetails = Joy_Of_Text_Plugin()->settings->get_group_details($jot_grpid);				   
                                        $group_desc = isset($groupdetails->jot_groupdesc) ? $groupdetails->jot_groupdesc : "";
                                        if ($group_desc == "") {
                                                   $group_desc = sprintf(__("Group %d","jot-plugin"), $grpid );
                                        }
                                        $msg = sprintf( __( "The phone number '%s' is already subscribed to the group - '%s' (%s)", "jot-plugin" ), esc_html($phone_num),$group_desc,$jot_grpid );
                                }
                        break;
                        case 4; // Member name not set set
                               $msg = __("Please enter your name.", "jot-plugin");         
                        break;
                        case 5; // Not a valid number
                            $msg = esc_html($phone_num) . __(" - number is not valid. Try again by adding your area code/country code.","jot-plugin");                      
                        break;
                        case 6; // Not a valid email
                            $msg =  __("Email address is invalid. Please try again.","jot-plugin");                    
                        break;
                        case 7; // Spam Bot detected
                            $msg .= __("Form completed by spam bot.","jot-plugin");                       
                        break;
                        case 8; // No groups selected
                            $msg .= __("Please select a group.","jot-plugin");                       
                        break; 
                        default:
                        # code...
                        break;
            }         
            
            $add_args['errormsg']  = $msg;
            $add_args['errorcode'] = $error;
        
            return $add_args;
        
   }
   
   
   public function add_subscriber_details($add_args) {
      
            $redirectURL = '';
            $lastmemid='';
            $msg = "";
            $msgerr = "";
            global $wpdb;            
                      
            $allgroups = $add_args['jot-group-id'];
            $verified_number = $add_args['jot-verified-num'];
            $jot_grpmemname = $add_args['jot-subscribe-name'];            
          
            // Get extended member info if present
            $jot_grpmememail   = isset($add_args['jot_grpmememail'])   ? wp_strip_all_tags($add_args['jot_grpmememail'])   : "";
            $jot_grpmemaddress = isset($add_args['jot_grpmemaddress']) ? wp_strip_all_tags($add_args['jot_grpmemaddress']) : "";
            $jot_grpmemcity    = isset($add_args['jot_grpmemcity'])    ? wp_strip_all_tags($add_args['jot_grpmemcity'])    : "";
            $jot_grpmemstate   = isset($add_args['jot_grpmemstate'])   ? wp_strip_all_tags($add_args['jot_grpmemstate'])   : "";
            $jot_grpmemzip     = isset($add_args['jot_grpmemzip'])     ? wp_strip_all_tags($add_args['jot_grpmemzip'])     : "";     
           
            foreach ($allgroups as $jot_grpid) {
               
                    $error = 0;
                    
                    if (Joy_Of_Text_Plugin()->options->number_exists($phone_num, $jot_grpid)) {
                         $error = 3;
                    }
                        
                    if ( $error===0)  {
                                                
                            if ($memdetails = Joy_Of_Text_Plugin()->options->number_exists_in_db($verified_number)) {
                                // Update existing record - adding existing member to new group
                                
                                // Insert into xref table
                                $table = $wpdb->prefix."jot_groupmemxref";
                                $data = array(
                                   'jot_grpid'       => $jot_grpid,
                                   'jot_grpmemid'    => $memdetails->jot_grpmemid,
                                   'jot_grpxrefts'   => current_time('mysql', 0)
                                   );
                                $success=$wpdb->insert( $table, $data );
            
                                // Update values in the existing member record
                                $table = $wpdb->prefix."jot_groupmembers";
                                $data = array(
                                    'jot_grpmemname' => sanitize_text_field (substr($jot_grpmemname,0,40)),                        
                                    'jot_grpmemts'   => current_time('mysql', 0),
                                    'jot_grpmememail'   => substr($jot_grpmememail,0,90),
                                    'jot_grpmemaddress' => substr($jot_grpmemaddress,0,240),
                                    'jot_grpmemcity'    => substr($jot_grpmemcity,0,40),
                                    'jot_grpmemstate'   => substr($jot_grpmemstate,0,40),
                                    'jot_grpmemzip'     => substr($jot_grpmemzip,0,40)
                                );
                                $success=$wpdb->update( $table, $data, array ('jot_grpmemid' => $memdetails->jot_grpmemid ) );
                                
                                if ($success === false) {
                                   // Insert failed
                                   $error=1;
                                } else {
                                  // Send welcome message to the group message if required
                                  $msgerr = Joy_Of_Text_Plugin()->options->send_welcome_message($jot_grpid, $verified_number,$memdetails->jot_grpmemid);              
                                }
                                $lastmemid = $memdetails->jot_grpmemid;                                           
                            } else {                           
                                $data = array(
                                   'jot_grpmemname'    => sanitize_text_field (substr($jot_grpmemname,0,40)),
                                   'jot_grpmemnum'     => $verified_number,
                                   'jot_grpmemstatus'  => 1,
                                   'jot_grpmememail'   => substr($jot_grpmememail,0,90),
                                   'jot_grpmemaddress' => substr($jot_grpmemaddress,0,240),
                                   'jot_grpmemcity'    => substr($jot_grpmemcity,0,40),
                                   'jot_grpmemstate'   => substr($jot_grpmemstate,0,40),
                                   'jot_grpmemzip'     => substr($jot_grpmemzip,0,40),
                                   'jot_grpmemts'      => current_time('mysql', 0)
                                    );    
                                
                                // Insert into members table
                                $table = $wpdb->prefix."jot_groupmembers";
                                $success=$wpdb->insert( $table, $data );
                                $lastmemid = $wpdb->insert_id;
                                                                   
                                // Insert into xref table
                                $table = $wpdb->prefix."jot_groupmemxref";
                                $data = array(
                                   'jot_grpid'         => $jot_grpid,
                                   'jot_grpmemid'      => $lastmemid,
                                   'jot_grpxrefts'     => current_time('mysql', 0)
                                   );
                                $success=$wpdb->insert( $table, $data );
                                if ($success === false) {
                                   // Insert failed
                                   $error=1;
                                } else {
                                  // Send welcome message to the group message if required
                                  $msgerr = Joy_Of_Text_Plugin()->options->send_welcome_message($jot_grpid, $verified_number,$lastmemid);
                                }
                             }
                             
                             // Member inserted/updated OK, then send notifications
                             if ($success === false) {
                                ///
                             } else {
                                $smsprovider = get_option('jot-plugin-smsprovider');
                               
                                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin("SMS provider settings " . print_r($smsprovider,true));
                                
                                // Send email notification
                                $jot_inbsubchk = isset($smsprovider['jot-inbsubchk']) ? $smsprovider['jot-inbsubchk'] : "";
                                $jot_inbemail = isset($smsprovider['jot-inbemail']) ? $smsprovider['jot-inbemail'] : "";
                                if ( $jot_inbsubchk == 'true' && !empty($jot_inbemail) ) {
                                     $subject = __("New Subscriber!","jot-plugin");
                                     $defaultemail = __("You have a new subscriber.","jot-plugin") . __(" Their name and number is ","jot-plugin") . " " . $jot_grpmemname . " - " . $verified_number ;
                                     Joy_Of_Text_Plugin()->currentsmsprovider->send_email($subject, $verified_number, "", $defaultemail);
                                }
                                
                                // Send SMS notification
                                $jot_inbnotgroup = isset($smsprovider['jot-inbnotgroup']) ? $smsprovider['jot-inbnotgroup'] : "";
                                
                                if ( $jot_inbsubchk == 'true' && $jot_inbnotgroup != "" ) {
                                        $defaultsms = __("You have a new subscriber.","jot-plugin") . __(" Their name and number is ","jot-plugin") . " " . $jot_grpmemname . " - " . $verified_number ;
                                        $routedmessage = Joy_Of_Text_Plugin()->currentsmsprovider->get_routing_message($verified_number,"",$defaultsms);
                                        
                                        // Subtype = NG - group send.
                                        $collate_args = array('jot_batchid' => $batchid,  
                                                                  'jot_messsubtype' => 'NG'
                                                                 );                                          
                                        $notif_msgerr = Joy_Of_Text_Plugin()->messenger->send_to_group($jot_inbnotgroup,$routedmessage,$collate_args);                                        
                                        if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>> Sending notification " . print_r($notif_msgerr,true));
                                                       
                                    
                                }
                                
                            }
                            
                    } // if error
                    
                    switch ( $error ) {
                            case 0; // All fine
                                   $msg .= __("Thank you for subscribing. ", "jot-plugin") . "<br>";
                                   $redirectURL = Joy_Of_Text_Plugin()->settings->get_groupmeta($jot_grpid,'jot_grpinvredirect');
                                   if (!empty($redirectURL)) {
                                        $redirectURL .= "?message=" . urlencode($msg);
                                   }
                                   
                                   // Add new member to auto-add groups
                                   $auto_group_return = Joy_Of_Text_Plugin()->messenger->add_to_inbound_group($verified_number, sanitize_text_field (substr($jot_grpmemname,0,40)));                                   
                            break;
                            case 1; // insert failed
                                   $msg .= __("An error occurred when subscribing.", "jot-plugin")  . "<br>";         
                            break 2;
                            case 3; // Number already exists in this group. Ignore if multi numbers                          
                                   $msg .= sprintf( __( "The phone number '%s' is already subscribed to this group.", "jot-plugin" ), esc_html($phone_num) )  . "<br>";
                            break;
                            default:
                                   $msg .= __("A problem occurred during the subscription.","jot-plugin");
                            break;
                    }         
                
                
            } // for All groups
        
        
            // Action hook fired if subscriber is added successfully.
            if ($error == 0) {
                $subscriber_args = array('jot_grpid'         => $allgroups,
                                         'jot_grpmemid'      => $lastmemid,
                                         'jot_grpmemname'    => sanitize_text_field (substr($jot_grpmemname,0,40)),
                                         'jot_grpmemnum'     => $verified_number,                               
                                         'jot_grpmememail'   => substr($jot_grpmememail,0,90),
                                         'jot_grpmemaddress' => substr($jot_grpmemaddress,0,240),
                                         'jot_grpmemcity'    => substr($jot_grpmemcity,0,40),
                                         'jot_grpmemstate'   => substr($jot_grpmemstate,0,40),
                                         'jot_grpmemzip'     => substr($jot_grpmemzip,0,40),
                                         'jot_grpmemts'      => current_time('mysql', 0)
                                         );
                do_action('jot_after_subscriber_added',$subscriber_args);
            }
            
            $response = array('errormsg'=> $msg, 'errorcode' => $error, 'url'=> $redirectURL, 'sqlerr' => $wpdb->last_error, 'lastid' => $lastmemid, 'message_error' => $msgerr,'number' => $verified_number, 'confirmgroup' => '' );
            echo json_encode($response);
            die();
        
   }
    
   public function process_textus_send() {		    
	    
            
            $errorcode = 0;
            $phone_num = "";
            $verified_number = "";
            
            $formdata = $_POST['formdata'];
	    parse_str($formdata, $output);
	    
                       
            // Spam bot check
            $jot_textus_special = isset($output['jot-textus-special']) ? wp_strip_all_tags($output['jot-textus-special']) : "";
            
            if ($jot_textus_special != "") {
                // Filled in by a bot so end
                $errorcode = 4;
            }
            
            $jot_textus_groupid = isset($output['jot-textus-groupid']) ? $output['jot-textus-groupid'] : -1;
            $jot_textus_name    = isset($output['jot-textus-name'])    ? wp_strip_all_tags($output['jot-textus-name']) : "";
            $jot_textus_num     = isset($output['jot-textus-num'])     ? wp_strip_all_tags($output['jot-textus-num']) : "";
            $jot_textus_message = isset($output['jot-textus-message']) ? wp_strip_all_tags($output['jot-textus-message']) : "";
	    
            if (!is_numeric($jot_textus_groupid)) {
                $errorcode = 2;
            }
            
            if ($jot_textus_message == "") {
                $errorcode = 3;
            }
                       
            // Verify Number           
            if ($errorcode == 0) {
                $phone_num = Joy_Of_Text_Plugin()->options->parse_phone_number($jot_textus_num);
                $verified_number = Joy_Of_Text_Plugin()->currentsmsprovider->verify_number($phone_num);
                if ( $verified_number == "") {
                       $errorcode = 5;
                }
            }
            
         
            if ($errorcode == 0) {
                        // Add number to group if group id provided
                        if ($jot_textus_groupid != -1) {
                            if ($jot_textus_name == "") {
                               $jot_textus_name = $verified_number;
                            }
                            $add_return = Joy_Of_Text_Plugin()->options->process_add_member($jot_textus_name, $verified_number, $jot_textus_groupid);
                            
                        }
            
                        // Add number to auto-add group if one is set                        
                        $auto_group_return = Joy_Of_Text_Plugin()->messenger->add_to_inbound_group($verified_number, $jot_textus_name);
            
                        // Send SMS message to the inbound notification group
                        $smsprovider = get_option('jot-plugin-smsprovider');
                        $jot_inbsmschk = isset($smsprovider['jot-inbsmschk']) ? $smsprovider['jot-inbsmschk'] : 'false';
                        $jot_inbnotgroup = isset($smsprovider['jot-inbnotgroup']) ? $smsprovider['jot-inbnotgroup'] : "";
                        $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
                        $selected_number = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-phonenumbers-' . $selected_provider);;
                        
                        if ( $jot_inbsmschk == 'true' && $jot_inbnotgroup != "") {
                                 
                                $defaultsms = __("You have received a message from ","jot-plugin") . $verified_number . ". " . __("The message was","jot-plugin") . " '" . $jot_textus_message . "'. " . $smsprovider['jot-inbsmssuffix'];
                                $routedmessage = Joy_Of_Text_Plugin()->currentsmsprovider->get_routing_message($verified_number,$jot_textus_message,$defaultsms);
                                // Subtype = NG - notification group send.
                                $collate_args = array('jot_batchid' => $batchid,  
                                                          'jot_messsubtype' => 'NG'
                                                         );
                                          
                                $notif_msgerr = Joy_Of_Text_Plugin()->messenger->send_to_group($jot_inbnotgroup,$routedmessage,$collate_args);    
                                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>> Sending notification " . print_r($notif_msgerr,true));
                                                   
                        }
                        // Add message to Message History
                        $histdata = array(
                                    'jot_histsid'           => sanitize_text_field ('Textus_inbound'),
                                    'jot_histdir'           => sanitize_text_field ('i'),
                                    'jot_histmemid'         => 9999999,
                                    'jot_histfrom'          => sanitize_text_field ($verified_number),
                                    'jot_histto'            => sanitize_text_field ($selected_number),
                                    'jot_histprovider'      => sanitize_text_field (Joy_Of_Text_Plugin()->currentsmsprovidername),
                                    'jot_histmesscontent'   => $jot_textus_message,
                                    'jot_histmesstype'      => sanitize_text_field ('S'),
                                    'jot_histstatus'        => sanitize_text_field ('Received (Text Us)'),
                                    'jot_histprice'         => 0,
                                    'jot_histmedia'         => '',                                                
                                    'jot_histts'            => current_time('mysql', 0)                                 
                        );
                        $hist_error = Joy_Of_Text_Plugin()->messenger->log_to_history($histdata);
                        
                        // Send email to admin
                        $jot_inbemail = isset($smsprovider['jot-inbemail']) ? $smsprovider['jot-inbemail'] : "";
                        if ( $jot_inbsmschk == 'true' && $jot_inbemail != "" ) {
                            $subject = __("Webform Message received!","jot-plugin");
                            $defaultemail = __("You have received a message from ","jot-plugin") . $verified_number . ". " . __("The message was","jot-plugin") . " '" . $jot_textus_message . "'. " . $smsprovider['jot-inbsmssuffix'];
                            Joy_Of_Text_Plugin()->currentsmsprovider->send_email($subject, $verified_number, $jot_textus_message, $defaultemail);
                          
                        }
            }
            
            switch ( $errorcode ) {
                case 0; // All fine
                        $msg = __( 'Message sent. Thank you.', 'jot-plugin' );
                break;
                case 2; // Group ID is not numeric
                       $msg = __("The group id provided is not numeric.","jot-plugin");                       
                break;
                case 3; // Empty message
                       $msg = __("Please enter your message.","jot-plugin");                       
                break;
                case 4; // Spam Bot detected
                       $msg = __("Form completed by spam bot.","jot-plugin");                       
                break; 
                case 5; // Not a valid number
                       $msg = esc_html($jot_textus_num) . __(" - number is not valid. Try again by adding your area code/country code.","jot-plugin");                       
                break;               
                default:
                       $msg= "";
                break;
            }         
            
            echo json_encode(array("errorcode" => $errorcode, "errormsg" => $msg));
            wp_die();
   }
   
     
   public function process_jotgroupsend() {		    
	    
            
            $errorcode = 0;
            $phone_num = "";
            $verified_number = "";
            $jot_groupsend_messagetype = "jot-sms";
            $jot_groupsend_mediaid = "";
            
            $selected_recipients_json = isset($_POST['jot-recipients']) ? stripslashes($_POST['jot-recipients']) : "";
            $selected_recipients = json_decode($selected_recipients_json,true);
            
                       
            $formdata = $_POST['formdata'];
	    parse_str($formdata, $output);
	    
                        
            $jot_member_select_attr = isset($output['jot-groupsend-member-select']) ? $output['jot-groupsend-member-select'] : "";
            
            
                                                       
            if ($jot_member_select_attr == "") {
                    // If member select dialog not specified, then make sure a group_id has been provided
                    if (!is_numeric($jot_groupsend_groupid)) {
                        $errorcode = 2;
                    }
            }
            
            
            if ($jot_member_select_attr == "yes" && empty($selected_recipients)) {                     
                    echo json_encode(array("errorcode" => 9,
                                                            "errormsg"  => __("No members selected. Please try again.","jot-plugin"),
                                                            "batchid"   => 0
                                                         ));
                    wp_die();    
            }
            
            // Spam bot check
            $jot_groupsend_special = isset($output['jot-groupsend-special']) ? wp_strip_all_tags($output['jot-groupsend-special']) : "";
            
            if ($jot_groupsend_special != "") {
                // Filled in by a bot so end
                $errorcode = 4;
            }
            
            $jot_groupsend_groupid  = isset($output['jot-groupsend-groupid'])  ? $output['jot-groupsend-groupid'] : -1;
            $jot_groupsend_message  = isset($output['jot-groupsend-message'])  ? wp_strip_all_tags($output['jot-groupsend-message']) : "";
	    $jot_groupsend_response = isset($output['jot-groupsend-response']) ? wp_strip_all_tags($output['jot-groupsend-response']) : "";
            $jot_groupsend_sendtype = isset($output['jot-groupsend-sendtype']) ? trim(wp_strip_all_tags($output['jot-groupsend-sendtype'])) : "immediate";
            
            switch ( $jot_groupsend_sendtype ) {
                        case 'immediate'; // Send messages immediately
                             $process_type = "P";     
                        break;
                        case 'dripfeed'; // Dripfeed messages
                             $process_type = "D";
                        break;                             
                        default:
                             $process_type = "P";
                        break;
            }   
                        
            
            if ($jot_groupsend_message == "") {
                $errorcode = 3;
            }          
         
            
            $batchid = -1;
            $queue_args = array();
            if ($errorcode == 0) {
            
                        // If member_select attribute has been used and members have been selected, then use the selected members
                        if ($selected_recipients) {
                                    $group_members_for_queue = $selected_recipients;      
                        } else {
                        
                                    $group_members = Joy_Of_Text_Plugin()->messenger->get_groupmembers_only($jot_groupsend_groupid);
                                   
                                    if (!$group_members) {
                                        echo json_encode(array("errorcode" => 7,
                                                               "errormsg"  => __("Group members not found","jot-plugin"),
                                                               "batchid"   => 0
                                                         ));
                                         wp_die();
                                    }
                                    // Create array of member ids
                                    $group_members_for_queue = array();
                                    foreach ($group_members as $member) {
                                        $group_members_for_queue[] = $jot_groupsend_groupid . "-" . $member->jot_grpmemid;                                     
                                    }
                        } 
                        
                                                
                        $queue_args['jot-message']                   = $jot_groupsend_message;
                        $queue_args['jot-message-type']              = apply_filters('jot_filter_jotgroupsend_messagetype',$jot_groupsend_messagetype);
                        $queue_args['jot-message-mms-image']         = apply_filters('jot_filter_jotgroupsend_mediaid',$jot_groupsend_mediaid);
                        $queue_args['jot-message-removedupes']       = true ;
                        $queue_args['jot-message-sendmethod']        = "jottabgroupsend";                        
                        $queue_args['jot-message-quicksend-numbers'] = $group_members_for_queue;
                        $queue_args['jot-process-type']              = $process_type; //Drip feed or send immediately message                               
                        
                        $queue_response = Joy_Of_Text_Plugin()->messenger->queue_message($queue_args);
                           
            }         
                       
            switch ( $errorcode ) {
                case 0; // All fine
                        $msg = $jot_groupsend_response;
                break;
                case 2; // Group ID is not numeric
                        $msg = __("The group id provided is not numeric.","jot-plugin");                       
                break;
                case 3; // Empty message
                        $msg = __("Please enter your message.","jot-plugin");                       
                break;
                case 4; // Spam Bot detected
                        $msg = __("Form completed by spam bot.","jot-plugin");                       
                break; 
                case 5; // Send error - no message sent
                        $msg = __("An error occurred. No messages were sent.","jot-plugin");                                        
                break;
                case 6; // Send error - completed with errors
                        $msg = __("Completed with some errors.","jot-plugin");
                        $errorcode = 0; // Reset to 0 so the colour is shown in green
                break; 
                default:
                       $msg= "";
                break;
            }         
           
            echo json_encode(array("errorcode" => $errorcode,
                                   "errormsg"  => $msg,
                                   "batchid"   => $batchid,                                   
                                   "fullbatchsize" => isset($queue_response['fullbatchsize']) ? $queue_response['fullbatchsize'] : 0
                                   ));
            wp_die();
   }
   
   public function process_jotoptout_getgroups() {
            
            $allgroups = array();
            $errorcode = 0;
            $formdata = $_POST['formdata'];
	    parse_str($formdata, $output);
	    
                       
            // Spam bot check
            $jot_groupoptout_special = isset($output['jot-groupoptout-special']) ? wp_strip_all_tags($output['jot-groupoptout-special']) : "";
            
            if ($jot_groupoptout_special != "") {
                // Filled in by a bot so end
                $errorcode = 4;
            }
                      
            $jot_groupoptout_num  = isset($output['jot-groupoptout-num'])  ? $output['jot-groupoptout-num'] : -1;
            
            if ($errorcode == 0) {
                $phone_num = Joy_Of_Text_Plugin()->options->parse_phone_number($jot_groupoptout_num );
                $verified_number = Joy_Of_Text_Plugin()->currentsmsprovider->verify_number($phone_num);
                if ( $verified_number == "") {
                       $errorcode = 5;
                }
            }
            
            if ($errorcode == 0) {
               // Get all groups for this number
               $allgroups = Joy_Of_Text_Plugin()->messenger->get_groups_by_number($verified_number);
               
               // if no groups returned
               if (!$allgroups) {
                  $errorcode = 1;
               }               
            }
            
                        
            switch ( $errorcode ) {
                case 0; // All fine
                        $msg = "";
                break;
                case 1:
                        $msg = __("No groups found for this number","jot-plugin"); 
                break; 
                case 4; // Spam Bot detected
                        $msg = __("Form completed by spam bot.","jot-plugin");                       
                break;                
                default:
                       $msg= "";
                break;
            }         
           
            echo json_encode(array("errorcode" => $errorcode,
                                   "errormsg"  => $msg,
                                   "verified_number" => $verified_number,
                                   "allgroups" => $allgroups,                                  
                                   ));
            wp_die();
            
   }
   
   public function process_jotoptout_unsubscribe() {
           
            global $wpdb;
            
            $errorcode = 1;
            $formdata = $_POST['formdata'];
            parse_str($formdata, $output);
            $rowsdeleted = 0;
	    
                       
            $jot_groupoptout_groups = isset($output['jot-groupoptout-groups']) ? $output['jot-groupoptout-groups'] : array();
            $jot_groupoptout_verifiednum = isset($output['jot-groupoptout-verifiednum']) ? $output['jot-groupoptout-verifiednum'] : "";
            
            // Spam bot check
            $jot_groupoptout_special = isset($output['jot-groupoptout-special']) ? wp_strip_all_tags($output['jot-groupoptout-special']) : "";
            
            if ($jot_groupoptout_special != "") {
                // Filled in by a bot so end
                $errorcode = 4;
            } elseif ($jot_groupoptout_verifiednum != "" ) {
                        if ($jot_groupoptout_groups) {
                              foreach ($jot_groupoptout_groups as $groupid => $value) {
                                    $rows_for_group = 0;
                                    if (isset($groupid)) {
                                          // Delete group subscription for the given number                        
                                          $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
                                          $tablexref = $wpdb->prefix."jot_groupmemxref";   // b
                          
                                          $sql = "DELETE a FROM " . $tablexref .  " AS a " . 
                                                 " JOIN " . $tablegrpmem . " AS b " .
                                                 " ON a.jot_grpmemid = b.jot_grpmemid " .
                                                 " WHERE a.jot_grpid =  %d " .
                                                 " AND b.jot_grpmemnum = %s ";                        
                                               
                                          
                                          $sqlprep = $wpdb->prepare( $sql, $groupid, $jot_groupoptout_verifiednum);     
                                          $rows_for_group = $wpdb->query($sqlprep);
                                          
                                          $rowsdeleted += $rows_for_group;
                                          
                                          // Opt out found
                                          if ($rows_for_group > 0) {
                                                // Action hook for deleted user                                     
                                                $member = Joy_Of_Text_Plugin()->messenger->get_member_from_num($jot_groupoptout_verifiednum);
                                                $group_arr = (array) $groupid;                    
                                                do_action('jot_after_group_optout',$member, $group_arr);
                                                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"After do_action");
                                                Joy_Of_Text_Plugin()->messenger->send_unsubscribe_notifications($member, $group_arr);
                                          }
                                          
                                    }
                               }
                        } else {
                             $errorcode = 3;             
                        }
            } else {
                $errorcode = 2;
            }
            
            if ($rowsdeleted > 0) {
                // All fine                
                $errorcode = 0;
            } 
            
            switch ( $errorcode ) {
                case 0; // All fine
                        $msg = __("Unsubscription successful","jot-plugin");
                break;
                case 1:
                        $msg = sprintf(__("A problem occurred - %s","jot-plugin"),$sqlprep);
                break;
                case 2:
                        $msg = __("Number could not be verified.","jot-plugin");
                break;
                case 3:
                        $msg = __("No groups selected.","jot-plugin");
                break; 
                case 4; // Spam Bot detected
                        $msg = __("Form completed by spam bot.","jot-plugin");                       
                break;                
                default:
                       $msg= "";
                break;
            }     
           
            
            echo json_encode(array("errorcode" => $errorcode,
                                   "errormsg"  => $msg,
                                   "rowsdeleted" => $rowsdeleted                             
                                   ));
            wp_die();
            
                                           
   }
   
   public function process_refresh_inbox() {
                        
            $shortcode_return = do_shortcode('[jotinbox]');
            
            echo json_encode(array("html" => $shortcode_return));            
            die();
   }
   
   public function process_generate_invite_html() {
           
            $formdata = $_POST['formdata'];
            parse_str($formdata, $output);
                      
            $jot_groupid = isset($formdata['jot_grpinvdesc'])    ? $formdata['jot_grpinvdesc']    : "";
            
            if ($jot_groupid == "") {
                 echo json_encode(array("html" => "Group ID not set. Could not build HTML"));            
                 die();
            }
            
            $group_id = isset($formdata['jot_groupid'])    ? $formdata['jot_groupid']    : "0000";
            $atts = array(
                        'jot_grpinvdesc'    => isset($formdata['jot_grpinvdesc'])    ? $formdata['jot_grpinvdesc']    : "",
                        'jot_grpinvnametxt' => isset($formdata['jot_grpinvnametxt']) ? $formdata['jot_grpinvnametxt'] : "",
                        'jot_grpinvnumtxt'  => isset($formdata['jot_grpinvnumtxt'])  ? $formdata['jot_grpinvnumtxt']  : ""
                        );
            
            $confirm_set = Joy_Of_Text_Plugin()->settings->get_groupmeta($group_id,'jot_grpinvconfirm');
            
            $all_group_id = array($group_id);
            $subhtml = Joy_Of_Text_Plugin()->shortcodes->get_wrapped_jotform($group_id, $all_group_id, array(), $atts, $confirm_set);
            
            echo json_encode(array("html" => $subhtml));            
            die();
           
   }
   
} // end class
 