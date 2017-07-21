<?php
/**
*
* Joy_Of_Text options. Processess requests from the admin pages
*
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_Options {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
    public function __construct() {
                                    
                add_action( 'wp_ajax_process_forms',        array( $this, 'process_forms' ) );
                add_action( 'wp_ajax_nopriv_process_forms', array( $this, 'process_forms' ) );
                    
                add_action( 'wp_ajax_process_savemem', array( $this, 'process_save_member' ) );
                add_action( 'wp_ajax_process_deletemem', array( $this, 'process_delete_member' ) );                
                add_action( 'wp_ajax_process_deletegroup', array( $this, 'process_delete_group' ) );
               
                // Process delete member from all groups
                add_action( 'wp_ajax_process_deleteallmem', array( $this, 'process_delete_member_allgroups' ) );  
                
                // Process add member called from admin screens
                add_action( 'wp_ajax_process_addmem', array( $this, 'process_frontend_add_member' ) );
                
                //add_action( 'wp_ajax_process_downloadgroup', array( $this, 'process_download_group' ) );
                add_action( 'admin_post_process_downloadgroup', array( $this, 'process_download_group' ) );
               
                add_action( 'wp_ajax_process_deletehistitem', array( $this, 'process_delete_histitem' ) );
                add_action( 'wp_ajax_process_getchathistory', array( $this, 'process_getchathistory' ) );
                add_action( 'wp_ajax_process_sendchatmessage', array( $this, 'send_chat_message' ) );
                add_action( 'wp_ajax_process_getnewchatmessages', array( $this, 'process_get_new_chat_messages' ) );
                add_action( 'wp_ajax_process_getnamesfornumber', array( $this, 'process_get_names_for_number' ) );
                add_action( 'wp_ajax_process_set_extendmemfields', array( $this, 'process_set_extendmemfields' ) );
                add_action( 'wp_ajax_process_set_abridgehistory', array( $this, 'process_set_abridgehistory' ) );
                add_action( 'wp_ajax_process_set_removedupes', array( $this, 'process_set_removedupes' ) );
                add_action( 'wp_ajax_process_memberlist_bulk_actions', array( $this, 'process_memberlist_bulk_actions' ) );
		add_action('user_register',array($this,'new_from_reg'),10,1);

                add_action( 'wp_ajax_process_bulkcommitoff', array( $this, 'process_bulk_commit_off' ) );
                add_action( 'wp_ajax_process_bulkcommiton', array( $this, 'process_bulk_commit_on' ) );
	
                
                //EDD licence activation
                add_action( 'wp_ajax_process_jot_edd_activate_license', array( $this, 'process_jot_edd_activate_license' ) );
                
                // Ajax call from voice url setting button
                add_action( 'wp_ajax_process_configure_smsurl', array( $this, 'process_configure_smsurl' ) );   
                
                                            
        
    } // end constructor
 
    private static $_instance = null;
        
    public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
    } // End instance()

     
    public function process_forms () {
        
        
        if (!empty($_POST)) {
            $formdata =  $_POST['formdata'] ;
            parse_str($formdata, $output);
            $jot_form_id = $output['jot_form_id'];
           
            switch ( $jot_form_id ) {
                case 'jot-group-add':
                   $this->process_add_group();
                break;
                case 'jot-group-invite-form':
                   $this->process_group_invite_form();
                break;
                case 'jot-subscriber-form':
                   //$this->process_subscriber_form(); - moved to class-jot-plugin-process-shortcodes.php
                break;
                case 'jot-group-details-form':
                   $this->process_group_details_form();
                break;
                            
                default:
                # code...
                break;
            }
           
        }
    }
        
    public function process_add_group($param_groupname = "", $param_groupdesc = ""  ) {
                
        //if ( !current_user_can('manage_options') ) {
        //      $error=4;  
        //}
        
        global $wpdb;
        $error = 0;
        $lastid = 0;
        $url = "";
        
        if ($param_groupname != ""  ) {
            $jot_groupname = $param_groupname;
            $jot_groupdesc = $param_groupdesc;
        } else {
            $formdata = $_POST['formdata'];
            parse_str($formdata, $output);
            $groupfields = $output['jot-plugin-group-list'];
            $jot_groupname = isset($groupfields['jot_groupname']) ? $groupfields['jot_groupname'] : "";
            $jot_groupdesc = isset($groupfields['jot_groupdesc']) ? $groupfields['jot_groupdesc'] : "";
        
        }        
            
        if ($jot_groupname == "" || str_replace(' ', '',$jot_groupname) == "") {
                $error = 1;
        }
        
        if ($jot_groupdesc == "" || str_replace(' ', '',$jot_groupdesc) == "") {
                $error = 2;
        }
        
        $table = $wpdb->prefix."jot_groups";
        
        $group_exists = $wpdb->get_col( $wpdb->prepare( 
                "
                SELECT    jot_groupid 
                FROM        " . $table . "
                WHERE       jot_groupname = %s                 
                ",
                $jot_groupname                
        ) ); 
        
        if ($group_exists) {
                $error=3;
        }
        
        
        if ($error===0) {                
                $data = array(
                    'jot_groupname' => sanitize_text_field ($jot_groupname),
                    'jot_groupdesc' => sanitize_text_field ($jot_groupdesc),
                    'jot_groupoptout' => "",
		    'jot_groupopttxt' => "",
		    'jot_groupallowdups' => 0,
		    'jot_groupautosub' => 0,
                    'jot_ts' => current_time('mysql', 0)
                );
                $sqlerr=$wpdb->insert( $table, $data );
                $lastid = $wpdb->insert_id;                
                
        }
        
                      
        switch ( $error ) {
                case 0; // All fine
                       $msg = "";
                break;
                case 1; // Group name not set
                       $msg = __("No group name was entered. Please try again.", "jot-plugin");         
                break;
                case 2; // Group description not set
                       $msg = __("No group description was entered. Please try again.", "jot-plugin");         
                break;
                case 3; // Group with same name already exists
                       $msg = __("A group with this name already exists. Please try again.", "jot-plugin");         
                break;
                case 4; // Not an admin
                       $msg = __("You are not an Admin user.", "jot-plugin");     
                break;
                default:
                # code...
                break;
        }         
              
        if ($error===0) {
                // Alter URL to set the correct target form       
                $target = remove_query_arg('subform', wp_get_referer() );
                if (isset($_POST['jot-form-target'])) {
                    $target .= add_query_arg( array( 'subform' => $_POST['jot_form_target'] ),  $target );
                }
                // Alter URL to add status and last row ID
                $url = add_query_arg( array( 'settings-updated' => 'true', 'lastid' => $lastid ),  $target );
         }
        
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'url'=> $url, 'sqlerr' => $wpdb->last_error, 'lastid' => $lastid  );
        
        // If called from frontend
        if ($param_groupname == ""  ) {
           echo json_encode($response);        
           wp_die();
        } else {
           return $response;     
        }
        
    }
    
    public function process_group_details_form() {
                
        //if ( !current_user_can('manage_options') ) {
        //      $error=4;  
        //}
        
        global $wpdb;
        $error = 0;
        
        $formdata = $_POST['formdata'];
        parse_str($formdata, $output);
        $jot_groupid = $output['jot_grpid'];
       
        $groupfields = $output['jot-plugin-group-list'];
        $jot_groupnameupd = isset($groupfields['jot_groupnameupd']) ? $groupfields['jot_groupnameupd'] : "";
        $jot_groupdescupd = isset($groupfields['jot_groupdescupd']) ? $groupfields['jot_groupdescupd'] : "";
        $jot_groupoptout  = isset($groupfields['jot_groupoptout'])  ? $groupfields['jot_groupoptout']  : "";
        $jot_groupopttxt  = isset($groupfields['jot_groupopttxt'])  ? $groupfields['jot_groupopttxt']  : "";
        $jot_groupautosub = isset($groupfields['jot_groupautosub']) ? $groupfields['jot_groupautosub'] : "";
        
        
        if (!isset($groupfields['jot_groupdescupd']) || str_replace(' ', '',$groupfields['jot_groupdescupd']) == '') {
                $error = 2;
        }
        
        if (!isset($groupfields['jot_groupnameupd']) || str_replace(' ', '',$groupfields['jot_groupnameupd']) == '') {
                $error = 1;
        }
        
        $table = $wpdb->prefix."jot_groups";     
               
        if ($error===0) {                
                $data = array(
                    'jot_groupname'    => sanitize_text_field ($jot_groupnameupd),
                    'jot_groupdesc'    => sanitize_text_field ($jot_groupdescupd),
                    'jot_groupoptout'  => sanitize_text_field ($jot_groupoptout),
                    'jot_groupopttxt'  => sanitize_text_field ($jot_groupopttxt),
                    'jot_groupautosub' => sanitize_text_field ($jot_groupautosub === 'true' ? 1:0)
                );                
                $sqlerr=$wpdb->update( $table, $data, array( 'jot_groupid' => $jot_groupid ));
                
        }        
                      
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Group details saved successfully.", "jot-plugin");
                       
                       $reserved = array('STOP', 'STOPALL', 'UNSUBSCRIBE', 'CANCEL', 'END', 'QUIT');
                       $jot_groupoptout = strtoupper($jot_groupoptout);
                       if (in_array($jot_groupoptout,$reserved)) {
                         $twilio_url = "<a href=\"https://www.twilio.com/help/faq/sms/does-twilio-support-stop-block-and-cancel-aka-sms-filtering\" target=\"_blank\">here</a>";
                         $msg .= "<br><br>" . $jot_groupoptout . __(" is a telecoms industry reserved word, which will stop ALL messages being received from your Twilio number. See ","jot-plugin") 
                                 . $twilio_url . __(" for details.", "jot-plugin");
                       }
                       
                break;
                case 1; // Group name not set
                       $msg = __("No group name was entered. Please try again.", "jot-plugin");         
                break;
                case 2; // Group description not set
                       $msg = __("No group description was entered. Please try again.", "jot-plugin");         
                break;
                case 3; // Group with same name already exists
                       $msg = __("A group with this name already exists. Please try again.", "jot-plugin");         
                break;
                case 4; // Not an admin
                       $msg = __("You are not an Admin user.", "jot-plugin");     
                break;
                default:
                # code...
                break;
        }         
        
        
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'url'=> "", 'sqlerr' => $wpdb->last_error, 'lastid' => ""  );
        echo json_encode($response);
        
        wp_die();
        
    }
    
    public function process_group_invite_form() {
        
        $error = 0;
        
        //if ( !current_user_can('manage_options') ) {
        //      $error=4;  
        //}
        
        global $wpdb;
        
        if ($error==0) {
                $formdata = $_POST['formdata'];
                parse_str($formdata, $output);
                
                //echo ">> " . print_r($output,true);
                      
                $groupfields = $output['jot-plugin-group-list'];
                $table = $wpdb->prefix."jot_groupinvites";
                
                $invite_exists =$wpdb->get_col( $wpdb->prepare( 
                        "
                        SELECT    jot_grpid  
                        FROM        " . $table . "
                        WHERE       jot_grpid = %s                 
                        ",
                        $groupfields['jot_grpid']                
                ) ); 
               
                if (isset($groupfields['jot_grpinvretchk'])) {
                        $retchk = sanitize_text_field ($groupfields['jot_grpinvretchk'] === 'true' ? 1:0);
                } else {
                        $retchk = 0;
                }
                
                $mess_type            = $groupfields['jot_grpinvmesstype'];
                $mess_audioid         = $groupfields['jot_grpinvaudioid'];
                $mess_mmsimageid      = $groupfields['jot-message-mms-image'];
                switch ( $mess_type ) {
                        case 'jot-sms';
                              $message_type = "S";
                              $media_id = "";
                        break;
                        case 'jot-call';
                              $message_type = "c";
                              $media_id = $mess_audioid;
                        break;
                        case 'jot-mms';
                              $message_type = "M";
                              $media_id = $mess_mmsimageid;
                        break;
                }
                
                // Get welcome message text and strip non-UTF8 characters
                $finalmessage = isset($groupfields['jot_grpinvrettxt'])  ?  $groupfields['jot_grpinvrettxt']  : "";
                
                // Retain new lines
                $finalmessage = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $finalmessage ) ) );
                
                if (!empty($finalmessage)) {
                        $finalmessage = Joy_Of_Text_Plugin()->messenger->strip_non_utf8($finalmessage);
                }
                
                if ( $invite_exists ) {
                        $data = array(
                        'jot_grpinvdesc'     => isset($groupfields['jot_grpinvdesc'])    ? sanitize_text_field ($groupfields['jot_grpinvdesc'])           : "",
                        'jot_grpinvnametxt'  => isset($groupfields['jot_grpinvnametxt']) ? sanitize_text_field ($groupfields['jot_grpinvnametxt'])        : "",
                        'jot_grpinvnumtxt'   => isset($groupfields['jot_grpinvnumtxt'])  ? sanitize_text_field ($groupfields['jot_grpinvnumtxt'])         : "",
                        'jot_grpinvretchk'   => $retchk ,
                        'jot_grpinvrettxt'   => $finalmessage,
                        'jot_grpinvaddkeyw'  => isset($groupfields['jot_grpinvaddkeyw']) ? sanitize_text_field (trim($groupfields['jot_grpinvaddkeyw']))  : "",
                        'jot_grpinvaudioid'  => $media_id,
                        'jot_grpinvmesstype' => $message_type
                        );
                        
                        $success=$wpdb->update( $table, $data, array( 'jot_grpid' =>  $groupfields['jot_grpid'] ) );
                
                        //Save redirect URL using groupmeta since v2.0.19
                        $redirectURL = isset($groupfields['jot_grpinvredirect']) ? sanitize_text_field ($groupfields['jot_grpinvredirect']) : "";
                        Joy_Of_Text_Plugin()->settings->save_groupmeta($groupfields['jot_grpid'], 'jot_grpinvredirect', $redirectURL);

                        //Save welcome message options
                        $jot_grpinvwelchk_jot_add = isset($groupfields['jot_grpinvwelchk']['jot_add']) ? true : false;
                        Joy_Of_Text_Plugin()->settings->save_groupmeta($groupfields['jot_grpid'], 'jot_grpinvwelchk_jot_add', $jot_grpinvwelchk_jot_add);

                        //$jot_grpinvwelchk_jot_copy = isset($groupfields['jot_grpinvwelchk']['jot_copy']) ? sanitize_text_field ($groupfields['jot_grpinvwelchk']['jot_copy']) : "";
                        $jot_grpinvwelchk_jot_copy = isset($groupfields['jot_grpinvwelchk']['jot_copy']) ? true : false;
                        Joy_Of_Text_Plugin()->settings->save_groupmeta($groupfields['jot_grpid'], 'jot_grpinvwelchk_jot_copy', $jot_grpinvwelchk_jot_copy);
                     
                        $jot_grpinvwelchk_jot_move = isset($groupfields['jot_grpinvwelchk']['jot_move']) ? true : false;
                        Joy_Of_Text_Plugin()->settings->save_groupmeta($groupfields['jot_grpid'], 'jot_grpinvwelchk_jot_move', $jot_grpinvwelchk_jot_move);
                     
                        // Save group phone number
                        $jot_grpinvphonenumber = isset($groupfields['jot_grpinvphonenumber']) ? $groupfields['jot_grpinvphonenumber'] : "default";
                        Joy_Of_Text_Plugin()->settings->save_groupmeta($groupfields['jot_grpid'], 'jot_grpinvphonenumber', $jot_grpinvphonenumber );
                     
                        // Save group phone number country code
                        $jot_grpinvcountrycode = isset($groupfields['jot_grpinvcountrycode']) ? $groupfields['jot_grpinvcountrycode'] : "nocc";
                        Joy_Of_Text_Plugin()->settings->save_groupmeta($groupfields['jot_grpid'], 'jot_grpinvcountrycode', $jot_grpinvcountrycode );
                        
                        //Save confirmation code option
                        $jot_grpinvconfirm = isset($groupfields['jot_grpinvconfirm']) ? true : false;
                        Joy_Of_Text_Plugin()->settings->save_groupmeta($groupfields['jot_grpid'], 'jot_grpinvconfirm', $jot_grpinvconfirm);
                        
                        // Save already subscribed message
                        $jot_grpinvalreadysub = isset($groupfields['jot_grpinvalreadysub']) ? $groupfields['jot_grpinvalreadysub'] : "";
                        Joy_Of_Text_Plugin()->settings->save_groupmeta($groupfields['jot_grpid'], 'jot_grpinvalreadysub', $jot_grpinvalreadysub );
                       
                        
                } else {                        
                        $data = array(
                        'jot_grpid' => (int) $groupfields['jot_grpid'],
                        'jot_grpinvdesc'    => isset($groupfields['jot_grpinvdesc'])    ? sanitize_text_field ($groupfields['jot_grpinvdesc'])           : "",
                        'jot_grpinvnametxt' => isset($groupfields['jot_grpinvnametxt']) ? sanitize_text_field ($groupfields['jot_grpinvnametxt'])        : "",
                        'jot_grpinvnumtxt'  => isset($groupfields['jot_grpinvnumtxt'])  ? sanitize_text_field ($groupfields['jot_grpinvnumtxt'])         : "",
                        'jot_grpinvretchk'  => $retchk ,
                        'jot_grpinvrettxt'  => $finalmessage,
                        'jot_grpinvaddkeyw' => isset($groupfields['jot_grpinvaddkeyw']) ? sanitize_text_field (trim($groupfields['jot_grpinvaddkeyw']))  : "",
                        'jot_grpinvaudioid'  => $media_id,
                        'jot_grpinvmesstype' => $message_type                                  
                        );
                        $success=$wpdb->insert( $table, $data );
                                                
                }
        }
        if ($wpdb->last_error !=null) {
            $error = 1;
        }
        if ($success === false) {
            $error = 1;
        }
        
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Group details saved successfully", "jot-plugin");
                break;
                case 1; // Database error
                       $msg = __("Could not save. A database error occurred", "jot-plugin");
                break;
                case 4; // Not an admin
                       $msg = __("You are not an Admin user.", "jot-plugin");     
                break;
                default:
                # code...
                break;
        }
                        
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'url'=> "", 'sqlerr' => $wpdb->last_error, 'lastid' => ""  );
        echo json_encode($response);
        
        
        wp_die();
        
    }
    
    
    
    public function add_to_member_table($member_name,$member_number, $member_args = null) {
            global $wpdb;
            
            $data = array(                       
                        'jot_grpmemname'    => sanitize_text_field ($member_name),
                        'jot_grpmemnum'     => $member_number,
                        'jot_grpmemstatus'  => 1,
                        'jot_grpmememail'   => isset($member_args['jot_grpmememail'])   ? sanitize_email($member_args['$jot_grpmememail'])         : "",
                        'jot_grpmemaddress' => isset($member_args['jot_grpmemaddress']) ? sanitize_text_field ($member_args['$jot_grpmemaddress']) : "",
                        'jot_grpmemcity'    => isset($member_args['jot_grpmememail'])   ? sanitize_text_field ($member_args['$jot_grpmememail'])   : "",
                        'jot_grpmemstate'   => isset($member_args['jot_grpmemstate'])   ? sanitize_text_field ($member_args['jot_grpmemstate'])    : "",
                        'jot_grpmemzip'     => isset($member_args['jot_grpmemzip'])     ? sanitize_text_field ($member_args['jot_grpmemzip'])      : "",
                        'jot_grpmemts'      => current_time('mysql', 0)
                        );
                                    
                    
            // Insert into members table
            $table = $wpdb->prefix."jot_groupmembers";
            $success=$wpdb->insert( $table, $data );
                        
            return array('jot_grpmemid' => $wpdb->insert_id, 'jot_grpmemnum' => $member_name, 'jot_grpmemnum' => $member_number);
            
    }
    
    public function process_frontend_add_member () {
            
            $formdata = $_POST['formdata'];
            $jot_grpmemname = $formdata['jot_grpmemname'];
            $jot_grpmemnum = $formdata['jot_grpmemnum'];
            $jot_grpid = $formdata['jot_grpid'];           
        
            
            // Extended info fields
            $param_extargs = array();
            $param_extargs['jot_grpmememail']   = isset($formdata['jot_grpmememail'])   ? $formdata['jot_grpmememail']   : "";
	    $param_extargs['jot_grpmemaddress'] = isset($formdata['jot_grpmemaddress']) ? $formdata['jot_grpmemaddress'] : "";
	    $param_extargs['jot_grpmemcity']    = isset($formdata['jot_grpmemcity'])    ? $formdata['jot_grpmemcity']    : "";
	    $param_extargs['jot_grpmemstate']   = isset($formdata['jot_grpmemstate'])   ? $formdata['jot_grpmemstate']   : "";
	    $param_extargs['jot_grpmemzip']     = isset($formdata['jot_grpmemzip'])     ? $formdata['jot_grpmemzip']     : "";
            
            //echo ">> " . $jot_grpmemname . " " . $jot_grpmemnum . " " . $jot_grpid . " " . print_r($param_extargs,true);
            
            // Add new member
            $add_new_member = $this->process_add_member($jot_grpmemname, $jot_grpmemnum, $jot_grpid, $param_extargs);
            
            // Check if welcome message should be sent           
            if (isset($add_new_member['errorcode'])) {
                 if ($add_new_member['errorcode'] == 0) {
                       // Send welcome message after successful add
                       if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"CHECKING welcome to be added >>" . $this->check_welcome_to_be_sent($jot_grpid,'jot_grpinvwelchk_jot_add') . "<<");
                       if ($this->check_welcome_to_be_sent($jot_grpid,'jot_grpinvwelchk_jot_add')) {
                           $this->send_welcome_message($jot_grpid,
                                                       $add_new_member['verified_number'],
                                                       $add_new_member['lastid'],
                                                       true);
                           if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"ADDING sending welcome to " . $jot_grpid . " " . $add_new_member['verified_number'] . " " . $add_new_member['lastid']);
                       }
                 }
            }
            echo json_encode($add_new_member);        
            wp_die();
            
            
    }	

    public function new_from_reg($user_id){
	
//Joy_Of_Text_Plugin()->messenger->log_to_file(print_r($_POST,true));
		$this->process_add_member($_POST["first_name-33"].' '.$_POST["last_name-33"],$_POST["mobile_number-33"],1);
	}
    public function process_add_member($param_memname = "", $param_memnum = "", $param_grpid = "", $param_extargs = null) {
     
        $url ='';
        $errorfield = '';
        $lastmemid = 0;
        $verified_number = '';
        $success = "";
       
        global $wpdb;
        $error=0;
        
        if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__  , $param_memname . "<>" . $param_memnum . "<>" . $param_grpid);
        
        
        // From POST or from Bulk adds?
        if ($param_grpid == ""  ) {            
            $formdata = $_POST['formdata'];
            $jot_grpmemname = $formdata['jot_grpmemname'];
            $jot_grpmemnum = $formdata['jot_grpmemnum'];
            $jot_grpid = $formdata['jot_grpid'];           
        
            
            // Extended info fields
            $jot_grpmememail   = isset($formdata['jot_grpmememail'])   ? $formdata['jot_grpmememail']   : "";
	    $jot_grpmemaddress = isset($formdata['jot_grpmemaddress']) ? $formdata['jot_grpmemaddress'] : "";
	    $jot_grpmemcity    = isset($formdata['jot_grpmemcity'])    ? $formdata['jot_grpmemcity']    : "";
	    $jot_grpmemstate   = isset($formdata['jot_grpmemstate'])   ? $formdata['jot_grpmemstate']   : "";
	    $jot_grpmemzip     = isset($formdata['jot_grpmemzip'])     ? $formdata['jot_grpmemzip']     : "";
        } else {            
            $jot_grpmemname = $param_memname;
            $jot_grpmemnum  = $param_memnum;
            $jot_grpid = $param_grpid;
            
            // Extended info fields
            $jot_grpmememail   = isset($param_extargs['jot_grpmememail'])   ? $param_extargs['jot_grpmememail']   : "" ;
	    $jot_grpmemaddress = isset($param_extargs['jot_grpmemaddress']) ? $param_extargs['jot_grpmemaddress'] : "";
	    $jot_grpmemcity    = isset($param_extargs['jot_grpmemcity'])    ? $param_extargs['jot_grpmemcity']    : "";
	    $jot_grpmemstate   = isset($param_extargs['jot_grpmemstate'])   ? $param_extargs['jot_grpmemstate']   : "";
	    $jot_grpmemzip     = isset($param_extargs['jot_grpmemzip'])     ? $param_extargs['jot_grpmemzip']     : "";
        }
        
                               
        // Check name is entered
         if (!isset($jot_grpmemname) || str_replace(' ', '',$jot_grpmemname) == '' || sanitize_text_field ($jot_grpmemname) == '' ) {
                $error = 1;         
        }
        
        // Check phone number
        $removed_plus = false;
        
        $phone_num = $this->parse_phone_number( $jot_grpmemnum );
        
        // Does phone number start with a plus
        if (preg_match('/^\+/', $phone_num)) {
            $phone_num = substr($phone_num,1);
            $removed_plus = true;
        } 
        if (!is_numeric($phone_num)) {
             $error = 2;
        }
        
        if ($removed_plus) {
            $phone_num = "+" . $phone_num;
        }
        
        if ($error == 0) {
            $verified_number = $this->get_verified_number_for_group($jot_grpid,$phone_num);
            if ( $verified_number == "") {
                $error = 5;
            }
        }
        
        // Does this number already exist in the group?   
        if ($this->number_exists($verified_number, $jot_grpid) && $error == 0) {
             $error = 3;
        }       
         
                   
        if ( $error==0 ) {
                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Verified number : " . $verified_number);
                
                if ($memdetails = $this->number_exists_in_db($verified_number)) {
                    // Update existing record - adding existing member to new group
                    
                    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"In add_member update" . print_r($memdetails,true));
                    
                    // Insert into xref table
                    $table = $wpdb->prefix."jot_groupmemxref";
                    $data = array(
                       'jot_grpid'       => $jot_grpid,
                       'jot_grpmemid'    => $memdetails->jot_grpmemid,
                       'jot_grpxrefts'   => current_time('mysql', 0)
                       );
                    $success=$wpdb->insert( $table, $data );

                    // Update the name field of the existing member record
                    $table = $wpdb->prefix."jot_groupmembers";
                    $data = array(
                        'jot_grpmemname' => sanitize_text_field ($jot_grpmemname),                        
                        'jot_grpmemts'   => current_time('mysql', 0),
                        'jot_grpmememail'   => sanitize_email ($jot_grpmememail) ,
                        'jot_grpmemaddress' => sanitize_text_field ($jot_grpmemaddress),
                        'jot_grpmemcity'    => sanitize_text_field ($jot_grpmemcity),
                        'jot_grpmemstate'   => sanitize_text_field ($jot_grpmemstate),
                        'jot_grpmemzip'     => sanitize_text_field ($jot_grpmemzip),                       
                        'jot_grpmemts'      => current_time('mysql', 0)
                    );
                   
                    $lastmemid = $memdetails->jot_grpmemid;                   
                    $success=$wpdb->update( $table, $data, array ('jot_grpmemid' => $memdetails->jot_grpmemid ) );
                   
                } else {
                    
                    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"In add_member insert >>" . print_r($memdetails,true));
                                        
                    $data = array(                       
                        'jot_grpmemname'    => sanitize_text_field ($jot_grpmemname),
                        'jot_grpmemnum'     => $verified_number,
                        'jot_grpmemstatus'  => 1,
                        'jot_grpmememail'   => sanitize_email ($jot_grpmememail) ,
                        'jot_grpmemaddress' => sanitize_text_field ($jot_grpmemaddress),
                        'jot_grpmemcity'    => sanitize_text_field ($jot_grpmemcity),
                        'jot_grpmemstate'   => sanitize_text_field ($jot_grpmemstate),
                        'jot_grpmemzip'     => sanitize_text_field ($jot_grpmemzip),
                        'jot_grpmemts'      => current_time('mysql', 0)
                        );
                                    
                    
                    // Insert into members table
                    $table = $wpdb->prefix."jot_groupmembers";
                    $success=$wpdb->insert( $table, $data );
                    $lastmemid = $wpdb->insert_id;
                                                       
                    // Insert into xref table
                    $table = $wpdb->prefix."jot_groupmemxref";
                    $data = array(
                       'jot_grpid'       => $jot_grpid,
                       'jot_grpmemid'    => $lastmemid,
                       'jot_grpxrefts'   => current_time('mysql', 0)
                       );
                    $success=$wpdb->insert( $table, $data );
                                   
                }
        } // if error
        
        if ($success === false) {
            $error = 6;
        }
       
        switch ( $error ) {
                case 0; // All fine
                       $msg = sprintf( __( 'New member "%s" added successfully.', 'jot-plugin' ), esc_html($jot_grpmemname) );                       
                break;
                case 1; // insert failed
                       $msg = __("Name field is blank. Please enter a name", "jot-plugin");                       
                break;
                case 2; // None numeric phone number
                       $msg = "'" . esc_html($phone_num) . "' " .  __("The phone number is not numeric.", "jot-plugin");                                              
                break;
                case 3; // Number already exists in this group
                        $msg = "'" . esc_html($phone_num) . "' " . __("Phone number already exists in this group", "jot-plugin");                          
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.","jot-plugin");                       
                break;
                case 5; // Not a valid number
                       $msg = esc_html($phone_num) . __(" - number is not valid. Try again by adding your area code/country code.","jot-plugin");                       
                break;
                case 6; // Database error
                       $msg = __("Could not save. A database error occurred", "jot-plugin");
                break;
                default:
                       $msg= "";
                break;
        }         
        
        
        // Action hook fired if subscriber is added successfully.
        if ($error == 0) {
            $allgroups = array();
            $allgroups[] = $jot_grpid;
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
            do_action('jot_after_member_added',$subscriber_args);
        }       
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => $errorfield,'url'=> $url, 'sqlerr' => $wpdb->last_error, 'lastid'=> $lastmemid, 'verified_number' => $verified_number );
        
        // If called from frontend
        if ($param_grpid == ""  ) {
           echo json_encode($response);        
           wp_die();
        } else {
            // If called from bulkadd
           return $response;     
        }
        
    }
    
    public function process_save_member() {
global $wpdb;
        //if ( !current_user_can('manage_options') ) {
        //      $error=4;  
        //}
      
        
        $error=0;
        $existing_member_name = "";
        
        $formdata = $_POST['formdata'];
        $jot_grpmemid      = isset($formdata['jot_grpmemid'])       ? $formdata['jot_grpmemid']       : "";
        $jot_grpmemname    = isset($formdata['jot_grpmemname'])     ? $formdata['jot_grpmemname']     : "";
        $jot_grpmemnum     = isset($formdata['jot_grpmemnum'])      ? $formdata['jot_grpmemnum']      : "";
        $jot_grpmememail   = isset($formdata['jot_grpmememail'])    ? $formdata['jot_grpmememail']    : "";
	$jot_grpmemaddress = isset($formdata['jot_grpmemaddress'])  ? $formdata['jot_grpmemaddress']  : "";
	$jot_grpmemcity    = isset($formdata['jot_grpmemcity'])     ? $formdata['jot_grpmemcity']     : "";
	$jot_grpmemstate   = isset($formdata['jot_grpmemstate'])    ? $formdata['jot_grpmemstate']    : "";
	$jot_grpmemzip     = isset($formdata['jot_grpmemzip'])      ? $formdata['jot_grpmemzip']      : "";
        $jot_grpid         = isset($formdata['jot_grpid'])          ? $formdata['jot_grpid']          : "";
        
        $table = $wpdb->prefix."jot_groupmembers";        
             
        // Check name is entered
        if ($jot_grpmemname == "" || str_replace(' ', '',$jot_grpmemname) == '') {
                $error = 1;         
        }
        
        if ($jot_grpmemid == "") {
            $error = 7;
        }
        
        /*
        // Check phone number
        $removed_plus = false;
        $phone_num = $this->parse_phone_number( $jot_grpmemnum );
        
        // Does phone number start with a plus
        if (preg_match('/^\+/', $phone_num)) {
            $phone_num = substr($phone_num,1);
            $removed_plus = true;
        } 
        if (!is_numeric($phone_num)) {
             $error = 2;
        }
        
        if ($removed_plus) {
            $phone_num = "+" . $phone_num;
        }
        */
        
        if ($error == 0) {
            $verified_number = $this->get_verified_number_for_group($jot_grpid,$jot_grpmemnum);
            if ( $verified_number == "") {
                $error = 5;
            }
        }
        
        if ($error == 0) {
            $numexists = $this->number_exists_for_member($verified_number,  $jot_grpmemid );
            if (!empty($numexists)) {
                 $existing_member_name = isset($numexists->jot_grpmemname) ? $numexists->jot_grpmemname : "";
                 $error = 3;
            }
        }
        
        $success = true;    
        if ( $error==0 ) {
                
                $data = array(
                        'jot_grpmemname'    => sanitize_text_field ($jot_grpmemname),
                        'jot_grpmemnum'     => sanitize_text_field ($verified_number),
                        'jot_grpmememail'   => sanitize_email ($jot_grpmememail) ,
                        'jot_grpmemaddress' => sanitize_text_field ($jot_grpmemaddress),
                        'jot_grpmemcity'    => sanitize_text_field ($jot_grpmemcity),
                        'jot_grpmemstate'   => sanitize_text_field ($jot_grpmemstate),
                        'jot_grpmemzip'     => sanitize_text_field ($jot_grpmemzip),
                        'jot_grpmemts'      => current_time('mysql', 0)
                );
                    
                
                $success=$wpdb->update( $table, $data, array( 'jot_grpmemid' => $jot_grpmemid ) );
                
        }
        
        if ($success === false) {
            $error = 6;
        }
        
        $errorfield = "";
        $url = "";
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Details updated successfully.", "jot-plugin");
                break;
                case 1; // insert failed
                       $msg = __("Name field is blank. Please enter a name", "jot-plugin");
                       $errorfield = isset($formdata['jot_namefield_id']) ? $formdata['jot_namefield_id'] : "";
                break;
                case 2; // None numeric phone number
                       $msg = __("The phone number is not numeric.", "jot-plugin");
                       $errorfield =  isset($formdata['jot_numfield_id']) ? $formdata['jot_numfield_id'] : "" ;                       
                break;
                case 3; // Number already exists in this group
                       $msg = sprintf(__("Phone number already exists for another member - '%s'", "jot-plugin"),$existing_member_name);
                       $errorfield = isset($formdata['jot_numfield_id']) ? $formdata['jot_numfield_id'] : "";
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.", "jot-plugin");                       
                break;
                case 5; // Not a valid number
                       $msg = __("That number is not valid. Try again by adding your area code/country code.","jot-plugin");
                       $errorfield =  isset($formdata['jot_numfield_id']) ? $formdata['jot_numfield_id'] : "" ;     
                break;
                case 6; // Database error
                       $msg = __("Could not save. A database error occurred", "jot-plugin");
                break;
                case 7; // jotgrpmemid not found
                       $msg = __("Member ID not found.", "jot-plugin");
                default:
                       $msg= "";
                break;
        }         
        
        // Action hook fired if subscriber is added successfully.
        if ($error == 0) {
            $allgroups = array();
            $allgroups[] = $jot_grpid;
            $subscriber_args = array('jot_grpid'         => $allgroups,
                                     'jot_grpmemid'      => $jot_grpmemid,
                                     'jot_grpmemname'    => sanitize_text_field (substr($jot_grpmemname,0,40)),
                                     'jot_grpmemnum'     => $verified_number,                               
                                     'jot_grpmememail'   => substr($jot_grpmememail,0,90),
                                     'jot_grpmemaddress' => substr($jot_grpmemaddress,0,240),
                                     'jot_grpmemcity'    => substr($jot_grpmemcity,0,40),
                                     'jot_grpmemstate'   => substr($jot_grpmemstate,0,40),
                                     'jot_grpmemzip'     => substr($jot_grpmemzip,0,40),
                                     'jot_grpmemts'      => current_time('mysql', 0)
                                     );
            do_action('jot_after_member_saved',$subscriber_args);
        }
                        
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => $errorfield,'url'=> $url, 'sqlerr' => $wpdb->last_error  );
        echo json_encode($response);
        
        wp_die();
                
    }
    
    public function process_delete_member($param_grpid = "", $param_grpmemid = "") {
        

        global $wpdb;
        $error=0;
        
        $formdata = $_POST['formdata'];
        $table = $wpdb->prefix."jot_groupmemxref";
        
      
        if ($param_grpid == "" ) {           
            $formdata = $_POST['formdata'];
            $jot_grpid = $formdata['jot_grpid'];
            $jot_grpmemid = $formdata['jot_grpmemid'];                        
        } else {           
            $jot_grpid = $param_grpid;
            $jot_grpmemid = $param_grpmemid; 
        }
                
        if ($jot_grpmemid == "") {
            $error = 1;            
        }
        
        
        if ( $error==0 ) {               
                $success=$wpdb->delete( $table, array( 'jot_grpid' =>  $jot_grpid,'jot_grpmemid' => $jot_grpmemid ) );
                
                if ($success > 0) {
                    // Action hook for deleted user                    
                    $member = Joy_Of_Text_Plugin()->settings->get_member($jot_grpmemid);
                    $group = (array) Joy_Of_Text_Plugin()->settings->get_group_details($jot_grpid);
                    do_action('jot_after_member_deletion_from_group',$member, $group);
                }
        }
        
        //echo ">>>> " . $success . "<<>>" . $jot_grpid . "<>" . $jot_grpmemid . "<>";
        
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Member removed from this group successfully.", "jot-plugin");
                break;
                case 1; // Member ID not found
                       $msg = __("Member ID not found.", "jot-plugin");
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.", "jot-plugin");                       
                break; 
                default:
                       $msg= "";
                break;
        }         
        
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => "",'url'=> "", 'sqlerr' => $wpdb->last_error  );
        echo json_encode($response);
        
        wp_die();
                
    }
    
     public function process_delete_member_allgroups($param_grpid = "", $param_grpmemid = "") {
        

        global $wpdb;
        $error=0;
        
        $formdata = $_POST['formdata'];
        $table = $wpdb->prefix."jot_groupmemxref";
        
      
        if ($param_grpid == "" ) {           
            $formdata = $_POST['formdata'];
            $jot_grpid = $formdata['jot_grpid'];
            $jot_grpmemid = $formdata['jot_grpmemid'];                        
        } else {           
            $jot_grpid = $param_grpid;
            $jot_grpmemid = $param_grpmemid; 
        }
                
        if ($jot_grpmemid == "") {
            $error = 1;            
        }
        
        
        if ( $error==0 ) {               
                $success=$wpdb->delete( $table, array( 'jot_grpmemid' => $jot_grpmemid ) );
                
                if ($success > 0) {
                    // Action hook for deleted user                    
                    $member = Joy_Of_Text_Plugin()->settings->get_member($jot_grpmemid);
                    $group = (array) Joy_Of_Text_Plugin()->settings->get_group_details($jot_grpid);
                    do_action('jot_after_member_deletion_from_group',$member, $group);
                }
        }
        
        //echo ">>>> " . $success . "<<>>" . $jot_grpid . "<>" . $jot_grpmemid . "<>";
        
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Member removed from all groups successfully.", "jot-plugin");
                break;
                case 1; // Member ID not found
                       $msg = __("Member ID not found.", "jot-plugin");
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.", "jot-plugin");                       
                break; 
                default:
                       $msg= "";
                break;
        }         
        
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => "",'url'=> "", 'sqlerr' => $wpdb->last_error, 'rows_deleted' => $success  );
        echo json_encode($response);
        
        wp_die();
                
     }
    
     public function process_delete_group() {
                
        //if ( !current_user_can('manage_options') ) {
        //      $error=4;  
        //}

        global $wpdb;
        $error=0;
        
        $formdata = $_POST['formdata'];
            
        if ( $error==0 ) {
            
                $table = $wpdb->prefix."jot_groups";
                $success=$wpdb->delete( $table, array( 'jot_groupid' => $formdata['jot_grpid'] ) );
                
                $table = $wpdb->prefix."jot_groupinvites";
                $success=$wpdb->delete( $table, array( 'jot_grpid' =>  $formdata['jot_grpid'] ) );
                                  
                $table = $wpdb->prefix."jot_groupmemxref";
                $success=$wpdb->delete( $table, array( 'jot_grpid' =>  $formdata['jot_grpid'] ) );
                
                $table = $wpdb->prefix."jot_groupmeta";
                $success=$wpdb->delete( $table, array( 'jot_groupid' =>  $formdata['jot_grpid'] ) );
                
        }
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("Group deleted successfully.", "jot-plugin");
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.", "jot-plugin");                       
                break; 
                default:
                       $msg= "";
                break;
        }         
        
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => "",'url'=> "", 'sqlerr' => $wpdb->last_error  );
        echo json_encode($response);
        
        wp_die();
                
    }
    
    /*
     *
     * Download group members into a CSV file.
     *
     */
     public function process_download_group() {
                   
            $jot_grpid =  $_GET['grpid'];
            if (!empty($jot_grpid)) {
               //$groupmembers = Joy_Of_Text_Plugin()->messenger->get_groupmembers_only($jot_grpid);
               $groupmembers = Joy_Of_Text_Plugin()->settings->get_all_groups_and_members($jot_grpid);
               $data = "";
            
               foreach ( $groupmembers as $member ) {
                   $group_name = $member->jot_groupname;
                   //$data .= '"' . $member->jot_grpmemname . '","' . $member->jot_grpmemnum . '"' . "\n";
                   $data .= $this->addQuotes($member->jot_grpmemname) . "," .
                           $this->addQuotes($member->jot_grpmemnum) . "," .
                           $this->addQuotes($member->jot_grpmememail) . "," .
                           $this->addQuotes($member->jot_grpmemaddress) . "," .
                           $this->addQuotes($member->jot_grpmemcity) . "," .
                           $this->addQuotes($member->jot_grpmemstate) . "," .
                           $this->addQuotes($member->jot_grpmemzip) . "\r\n";
               }
               
               $group_name = str_replace(" ", "", $group_name);
               $filename = "jot-" . $group_name . "-memberlist.csv";
               
               header('Content-Type: application/csv');
               header('Content-Disposition: attachement; filename="' . $filename . '";');
               echo $data;
            }
            exit();
    }
        
    
    
    function addQuotes($str){
            $str = isset($str) ? $str : "";
            return '"' . $str . '"';
    }
    
    //c.jot_groupname, b.jot_grpid, b.jot_grpmemid, a.jot_grpmemname, a.jot_grpmemnum, jot_grpmememail, jot_grpmemaddress, jot_grpmemcity, jot_grpmemstate, jot_grpmemzip, b.jot_grpxrefts
    
    public function process_delete_histitem() {
                
        if ( !current_user_can('manage_options') ) {
              $error=4;  
        }

        global $wpdb;
        $error=0;
        
        $formdata = $_POST['formdata'];
            
        if ( $error==0 ) {
            
                $table = $wpdb->prefix."jot_history";
                $success=$wpdb->delete( $table, array( 'jot_histid' =>  $formdata['jot_histid'] ) );                
                
        }
        switch ( $error ) {
                case 0; // All fine
                       $msg = __("History item deleted successfully.", "jot-plugin");
                break;
                case 4; // Not an Admin
                       $msg = __("You are not an Admin user.", "jot-plugin");                       
                break; 
                default:
                       $msg= "";
                break;
        }         
        
                
        $response = array('errormsg'=> $msg, 'errorcode' => $error, 'errorfield' => $errorfield,'url'=> $url, 'sqlerr' => $wpdb->last_error  );
        echo json_encode($response);
        
        wp_die();
                
    }
    
    public function parse_phone_number($number) {

        $number = str_replace(' ', '', $number);
        $number = str_replace('(', '', $number);
        $number = str_replace(')', '', $number);
        $number = str_replace('-', '', $number);
        $number = str_replace('.', '', $number);
        return sanitize_text_field($number);

    }
    
    public function group_exists($id) {

         global $wpdb;
         $table = $wpdb->prefix."jot_groups";
         $sql = " SELECT jot_groupid  " .
                " FROM " . $table .
                " WHERE jot_groupid = " . $id ;                
            
         $grpexists = $wpdb->get_results( $sql );
         return $grpexists;

    }
    
    public function number_exists($number, $id="") {

         global $wpdb;
    
         if (empty($id)) {
            $grpidclause = "";
         } else {
            $grpidclause = " AND b.jot_grpid = " . $id ;
         }
    
         $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
         $tablexref = $wpdb->prefix."jot_groupmemxref";   // b
             
         
         $sql = "SELECT b.jot_grpid, a.jot_grpmemid " . 
		" FROM " . $tablegrpmem .  " a, " . $tablexref . " b " . 
		" WHERE a.jot_grpmemid = b.jot_grpmemid " .
		" AND a.jot_grpmemnum = %s " .		
                $grpidclause .
                " LIMIT 1 "
                ;
         
         $sqlprep = $wpdb->prepare( $sql, $number, $number);
         $numexists = $wpdb->get_row( $sqlprep );
         
         if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"***** In number_exists : " .$sqlprep . ">>" . print_r($numexists,true) . "<<");
         
         return $numexists;

    }
    
    public function number_exists_in_db($number) {
            
         global $wpdb;
         
         $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
        	 
         $sql = "SELECT a.jot_grpmemid, a.jot_grpmemts " . 
	 	" FROM " . $tablegrpmem .  " a "  . 
	 	" WHERE a.jot_grpmemnum = %s ".
                " ORDER BY 2 DESC " .
                " LIMIT 1" ;
	 
         
         
                               
         $sqlprep = $wpdb->prepare( $sql, $number);
        
         $numexists = $wpdb->get_row( $sqlprep );
         return $numexists;

    }
    
    // Check whether this number being added for a different member 
    // In which case, that's an error
    public function number_exists_for_member($number,  $memid) {

         global $wpdb;
         
         $numexists = "";
         $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
         $tablexref = $wpdb->prefix."jot_groupmemxref";   // b
         
         $sql = "SELECT  a.jot_grpmemnum, a.jot_grpmemid, a.jot_grpmemname " . 
		" FROM " . $tablegrpmem .  " a " . 
		" WHERE a.jot_grpmemnum = %s " .
                " AND   a.jot_grpmemid != %d " .
                " LIMIT 1";
         
         $sqlprep = $wpdb->prepare( $sql, $number,$memid);
         
         $numexists = $wpdb->get_row( $sqlprep );
         
         return $numexists; 
    }
    
    public function send_welcome_message($jot_groupid, $number,$jotmemid, $welcome_already_checked = false) {
            
         $msgerr = "";
         $message_error = "";
         
         if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"In Send_welcome_message >> " . $jot_groupid . "<>" . $number . "<>" . $jotmemid . "<>" . $welcome_already_checked );
         
         $welchkbox = $this->get_groupinvite($jot_groupid);
               
         if ($welchkbox) {
            if ($welchkbox->jot_grpinvretchk || $welcome_already_checked) {
               
                   $member = Joy_Of_Text_Plugin()->messenger->get_member($jotmemid);
                   
                   $detagged_message_final = Joy_Of_Text_Plugin()->messenger->get_replace_tags($welchkbox->jot_grpinvrettxt,$member,$jot_groupid);                   
                                      
                   // Use default Sender ID for welcome SMS if set
                   $senderid = "";               
                   $smsdetails = get_option('jot-plugin-smsprovider');
                   if (!empty($smsdetails['jot-smssenderid'])) {
                       $senderid = isset($smsdetails['jot-smssenderid']) ? $smsdetails['jot-smssenderid'] : "";
                   }       
                   
                   // Check if group number has been set
                   $group_fromnumber = $this->get_group_fromnumber($jot_groupid);
                   
                   if (!empty($member)) {
                           
                           switch ($welchkbox->jot_grpinvmesstype) {
                                       case 'S';
                                            $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($number, $detagged_message_final,$senderid,$group_fromnumber);
                                       break;
                                       case 'M';
                                            $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_mmsmessage($number, $detagged_message_final,$welchkbox->jot_grpinvaudioid,$senderid,$group_fromnumber);
                                       break;
                                       case 'c';
                                            $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_callmessage($number, $detagged_message_final,$welchkbox->jot_grpinvaudioid,$group_fromnumber);                                                   
                                       break;
                           }
                           
                          if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Send_welcome_message return >> " . print_r($message_error,true) ); 
                           
                          $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                                'jot_messsubtype' => 'WM'
                                          );
                           // Log successful messages in history table
                           if ($message_error['send_message_type'] == 'SMS') {
                                  $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",$member['jot_grpmemid'],$message_error,$collate_args);
                           }
                           if ($message_error['send_message_type'] == 'MMS') {
                                  $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",$member['jot_grpmemid'],$message_error,$collate_args);
                           }   
                           if ($message_error['send_message_type'] == 'call') {
                                  // Has audio file call be send
                                  $messagecontent = $message->jot_messqcontent;
                                  if (empty($message->jot_messqcontent) && $mess_audioid != 'default' ) {
                                      $messagecontent = __("(Audio file sent)","jot-plugin");
                                  }                               
                                  $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_call("o",$member['jot_grpmemid'],$messagecontent,$message_error,$collate_args);
                           }
                          
                   }
                   
                   
            }
         } else {
            Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"A problem occurred getting group info > " . $sql );  
         }
         
         return $message_error;     

    }
    
    public function get_groupinvite($jot_groupid) {
            
            global $wpdb;
            
            $table  = $wpdb->prefix."jot_groupinvites";
            $table1 = $wpdb->prefix."jot_groups";
            
            $sql = " SELECT jot_grpinvretchk, jot_grpinvrettxt, jot_groupoptout,jot_grpinvmesstype, jot_grpinvaudioid  " .
                   " FROM " . $table . ", " . $table1 . 
                   " WHERE jot_grpid = %d " . 
                   " AND jot_groupid = jot_grpid ";               
            
            $sqlprep = $wpdb->prepare($sql,$jot_groupid);
            //Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Getting group invite > " . $sqlprep );  
            $groupinvite = $wpdb->get_row( $sqlprep );
            
            return $groupinvite;      
            
    }
   
    public function process_getchathistory() {
            global $wpdb;
        
            $formdata = $_POST['formdata'];    
            //$jot_histfrom = $formdata['jot_histfrom'];
            //$jot_histto   = $formdata['jot_histto'];
            $jot_histid       = $formdata['jot_histid'];
            $jot_histmesstype = $formdata['jot_histtype'];
            $jot_histstatus   = $formdata['jot_histstatus'];
            
            // Update the status of this message
            $messagestatus = Joy_Of_Text_Plugin()->currentsmsprovider->checkstatus($jot_histmesstype,$jot_histid,$jot_histstatus);
            
            // Get the from and to numbers for this message.
            $histitem = $this->get_message_history($jot_histid);
            
            // Set from and to numbers from history or passed in from the screen. 
            $jot_histfrom      = isset($histitem->jot_histfrom) ? $histitem->jot_histfrom : $formdata['jot_histfrom'];
            $jot_histto        = isset($histitem->jot_histto)   ? $histitem->jot_histto   : $formdata['jot_histto'];
            $message_direction = isset($histitem->jot_histdir)  ? $histitem->jot_histdir  : "";
                                    
            $table = $wpdb->prefix."jot_history";
            $sql = " SELECT jot_histid, jot_histfrom,jot_histto,jot_histmesscontent,jot_histts, DATE_FORMAT(jot_histts,'%%d %%b %%Y ')  as jot_date, DATE_FORMAT(jot_histts,'%%H:%%i') as jot_time, jot_histmesstype , jot_histdir" .
                   " FROM " . $table .
                   " WHERE ((jot_histfrom = %s " .
                   " AND jot_histto = %s ) " .
                   " OR (jot_histto = %s " .
                   " AND jot_histfrom = %s)) " .
                   //" AND jot_histmesstype = 'S' " .
                   " ORDER BY 5";
            
            $sqlprep = $wpdb->prepare( $sql, $jot_histfrom, $jot_histto, $jot_histfrom, $jot_histto);
            $histlist = $wpdb->get_results( $sqlprep );
            
            
            // Work out ournumber and their number
            $smsprovider_numbers = Joy_Of_Text_Plugin()->currentsmsprovider->getPhoneNumbers();
	    $all_numbers = isset($smsprovider_numbers['all_numbers']) ? $smsprovider_numbers['all_numbers'] : array();            
            
            if (array_key_exists($jot_histfrom, $all_numbers)) {
                        $ournumber = $jot_histfrom;
                        $theirnumber = $jot_histto;
            } elseif (array_key_exists($jot_histto, $all_numbers)) {
                      $ournumber = $jot_histto;
                      $theirnumber = $jot_histfrom;  
            } else {
                   // If number isn't a current Twilio number
                   if ($message_direction == "o") {
                        $ournumber = $jot_histfrom;
                        $theirnumber = $jot_histto;
                   } else {
                        $ournumber = $jot_histto;
                        $theirnumber = $jot_histfrom;
                   }
            }
            
            // Get member's name from number
            $memarr = Joy_Of_Text_Plugin()->messenger->get_member_from_num($theirnumber);
            if (isset($memarr['jot_grpmemname'])) {
                $mem_name = $memarr['jot_grpmemname'];
            } else {
                $mem_name = $theirnumber;
            }
            
            // Send data back to frontend.
            $response = array('ournumber' => $ournumber, 'theirnumber' => $theirnumber, 'theirname' => $mem_name, 'histlist' => $histlist);
            echo json_encode($response);
            wp_die();
            
    }
    
    public function get_message_history($jot_histid) {
           
            global $wpdb;
           
            $table = $wpdb->prefix."jot_history";
            $sql = " SELECT jot_histid, jot_histfrom,jot_histto,jot_histmesscontent,jot_histts, DATE_FORMAT(jot_histts,'%%d %%b %%Y ')  as jot_date, DATE_FORMAT(jot_histts,'%%H:%%i') as jot_time, jot_histmesstype, jot_histdir " .
                   " FROM " . $table .
                   " WHERE jot_histid = %d ";
            
            $sqlprep = $wpdb->prepare( $sql, $jot_histid);
            $histitem = $wpdb->get_row( $sqlprep );
            
            return $histitem;
    }
    
    public function process_get_new_chat_messages() {
            global $wpdb;
        
            $formdata = $_POST['formdata'];    
            $jot_histfrom = $formdata['jot_theirnumber'];
            $jot_histto = $formdata['jot_ournumber'];
            $jot_histid   = $formdata['jot_lasthistid'];
            
            
            $table = $wpdb->prefix."jot_history";
            $sql = " SELECT jot_histid, jot_histfrom,jot_histto,jot_histmesscontent,jot_histts, DATE_FORMAT(jot_histts,'%%d %%b %%Y ')  as jot_date, DATE_FORMAT(jot_histts,'%%H:%%i') as jot_time " .
                   " FROM " . $table .
                   " WHERE jot_histfrom = %s " .
                   " AND jot_histto = %s " .
                   " AND jot_histid > %d " .
                   " AND jot_histmesstype = 'S' " .
                   " ORDER BY 5";
            
            $sqlprep = $wpdb->prepare( $sql, $jot_histfrom, $jot_histto, $jot_histid);
            $histlist = $wpdb->get_results( $sqlprep );
            
            echo json_encode($histlist);
            wp_die();
            
    }
    
    public function send_chat_message() {

            $formdata = $_POST['formdata'];    
           
            $jot_histto   = $formdata['jot_theirnumber'];
            $jot_message  = $formdata['jot_chatmessage'];
            $jot_grpmemid = $formdata['jot_grpmemid'];
            
            $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($jot_histto, $jot_message);
            $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                   'jot_messsubtype' => 'VM'
                                  );
            $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",$jot_grpmemid,$message_error,$collate_args);
            
            echo json_encode($message_error);
            wp_die();
    }
    
    public function process_get_names_for_number($number="") {
            global $wpdb;
            $namelist = "";
                        
            if (empty($number)) {
               $formdata = $_POST['formdata'];    
               $jot_grpmemnum   = $formdata['jot_theirnumber'];
            } else {
               $jot_grpmemnum = $number;
            }
                        
            if (!empty($jot_grpmemnum)) {
            
                        $jot_grpmemnum = "%" . substr($jot_grpmemnum,3);
                        
                        $table = $wpdb->prefix."jot_groupmembers";
                        $sql = " SELECT jot_grpmemname,jot_grpmemts " .
                               " FROM " . $table .
                               " WHERE jot_grpmemnum LIKE %s " .                   
                               " ORDER BY 2 DESC";
                        
                        $sqlprep = $wpdb->prepare( $sql, $jot_grpmemnum);            
                        $namelist = $wpdb->get_results( $sqlprep );
            }
            
            if (!empty($number)) {
                return $namelist;
            } else {
                echo json_encode($namelist);
                wp_die();
            }
    }
   
   public function process_bulk_commit_on() {
           global $wpdb;
           $wpdb->query( 'SET autocommit = 0;' );
           //Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"== Commit off ==");
       
   }
   
   public function process_bulk_commit_off() {
           global $wpdb;
           $wpdb->query( 'COMMIT;' );
           $wpdb->query( 'SET autocommit = 1;' );
           //Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"== Commit on ==");
   }
   
   public function process_set_extendmemfields() {
            $formdata = $_POST['formdata']; 
            $value   = isset($formdata['jot-mem-extfields']) ? $formdata['jot-mem-extfields'] : "";
            
            $settings =  get_option('jot-plugin-group-list');
            $settings['jot-mem-extfields'] = $value ;   
            update_option('jot-plugin-group-list',$settings);
            
            echo json_encode(array("field" => "jot-mem-extfields", "value" => $value));
            wp_die();     
   }
   
   public function process_set_removedupes() {
            $formdata = $_POST['formdata'];    
            $value   = isset($formdata['jot-mem-removedupes']) ? $formdata['jot-mem-removedupes'] : "";
            
            $settings =  get_option('jot-plugin-group-list');
            $settings['jot-mem-removedupes'] = $value ;   
            update_option('jot-plugin-group-list',$settings);
            
            echo json_encode(array("field" => "jot-mem-removedupes", "value" => $value));
            wp_die();     
   }
   
   public function  process_set_abridgehistory() {
            $formdata = $_POST['formdata'];            
            $value   = isset($formdata['jot-hist-abridge']) ? $formdata['jot-hist-abridge'] : "";
            
            $settings =  get_option('jot-plugin-message-history');
            $settings['jot-hist-abridge'] = $value ;   
            update_option('jot-plugin-message-history',$settings);
            
            echo json_encode(array("field" => "jot-hist-abridge", "value" => $value));
            wp_die();     
   }
   
   public function process_jot_edd_activate_license() {
            
            $formdata   = $_POST['formdata'];    
            $licence    = isset($formdata['jot-eddlicence']) ? $formdata['jot-eddlicence'] : "";
            $product    = isset($formdata['jot-eddproduct']) ? $formdata['jot-eddproduct'] : EDD_SL_ITEM_NAME_JOTPRO;
            $statuskey  = 'jot-eddlicencestatus';
            $licencekey = 'jot-eddlicence';
            
            $this->process_edd_activate_license($licence,$product,$statuskey,$licencekey);
            
   }
   
   public function process_edd_activate_license($licence,$product,$statuskey,$licencekey) {
            
            // data to send in our API request
            $api_params = array( 
                    'edd_action'=> 'activate_license', 
                    'license' 	=> $licence, 
                    'item_name' => urlencode( $product ),
                    'url'       => home_url()
            );
            
            
            // Call the custom API.
            $response = wp_remote_post( EDD_SL_STORE_URL_JOTPRO, array(
                    'timeout'   => 15,
                    'sslverify' => false,
                    'body'      => $api_params
            ) );
          
            // make sure the response came back okay
            if ( is_wp_error( $response ) )
                    return false;

            // decode the licence data
            $licence_data = json_decode( wp_remote_retrieve_body( $response ) );
            
            // $licence_data->license will be either "active" or "inactive"
            
            // If multisite then save licence as a network option
            if (function_exists('is_multisite') && is_multisite() && is_main_site()) {
                        Joy_Of_Text_Plugin()->settings->set_network_smsprovider_settings($statuskey,$licence_data->license);
                        Joy_Of_Text_Plugin()->settings->set_network_smsprovider_settings($licencekey,$licence);
            } else {
                        Joy_Of_Text_Plugin()->settings->set_smsprovider_settings($statuskey,$licence_data->license);
                        Joy_Of_Text_Plugin()->settings->set_smsprovider_settings($licencekey,$licence);                        
            }
            
        
            echo json_encode(array("activationstatus" => $licence_data->license, "response" => $licence_data));
            wp_die();
   }
   
   public function check_edd_license() {
            $store_url = EDD_SL_STORE_URL_JOTPRO;
            $item_name = EDD_SL_ITEM_NAME_JOTPRO;
            $license = Joy_Of_Text_Plugin()->settings->get_network_smsprovider_settings('jot-eddlicence');
            
            $api_params = array(
                    'edd_action' => 'check_license',
                    'license' => $license,
                    'item_name' => urlencode( $item_name )
            );
            $response = wp_remote_get( add_query_arg( $api_params, $store_url ), array( 'timeout' => 15, 'sslverify' => false ) );
            if ( is_wp_error( $response ) )
                    return false;
            $licence_data = json_decode( wp_remote_retrieve_body( $response ) );
           
            // Set field licence status
            if ($licence_data) {
                Joy_Of_Text_Plugin()->settings->set_network_smsprovider_settings('jot-eddlicencestatus',$licence_data->license);                        
            }
            
   }
   
   
   public function process_memberlist_bulk_actions() {
            $formdata = $_POST['formdata'];
            
            $errorcode = 999;            
            
            $action = isset($formdata['jot_action']) ? $formdata['jot_action'] : 'noaction';
            $jot_source_grpid = isset($formdata['jot_source_grpid']) ? $formdata['jot_source_grpid'] : 0;
            $jot_target_grpid = isset($formdata['jot_target_grpid']) ? $formdata['jot_target_grpid'] : 0;
            
            $mess_memsel_json = stripslashes($formdata['jot_memberlist']);
            $mess_memsel = json_decode($mess_memsel_json,true);

            // Remove 'all' from the member selected array
            if(($key = array_search('all', $mess_memsel)) !== false) {
               unset($mess_memsel[$key]);
            }
            
            
            // This shouldn't happen and jot_grpid is hidden in the form
            if ($jot_source_grpid == 0) {
                echo json_encode(array("errorcode" => 999, "errormsg" => __("No source group has been selected. Please try again.","jot-plugin")));
                wp_die();        
            }
            
            if ($jot_target_grpid == 0 && $action != "delete") {
                echo json_encode(array("errorcode" => 999, "errormsg" => __("No target group has been selected. Please try again.","jot-plugin")));
                wp_die();        
            }
            
            if (empty($mess_memsel)) {
                echo json_encode(array("errorcode" => 999, "errormsg" => __("No members have been selected. Please try again.","jot-plugin")));
                wp_die();        
            }
            
                 
            switch ( $action ) {
                case 'move';
                        $send_welcome = $this->check_welcome_to_be_sent($jot_target_grpid,'jot_grpinvwelchk_jot_move');
                        $errorcode = $this->move_selected_members($jot_source_grpid, $jot_target_grpid, $mess_memsel,$send_welcome); 
                break;
                case 'copy';
                        $send_welcome = $this->check_welcome_to_be_sent($jot_target_grpid,'jot_grpinvwelchk_jot_copy');
                        $errorcode = $this->copy_selected_members($jot_target_grpid, $mess_memsel, $send_welcome );
                                          
                break;
                case 'delete';
                        $errorcode = $this->delete_selected_members($jot_source_grpid, $mess_memsel );                      
                break;
                case 'noaction';
                                              
                break; 
                default:
                       $errorcode = 999;                         
                break;
            }
            
             switch ( $errorcode ) {                
                case 1;
                       $msg = __("Member copy successful. Refreshing...", "jot-plugin");                       
                break;
                case 11;
                       $msg = __("Some members already exist in target group. Refreshing...", "jot-plugin");                       
                break;
                case 2;
                       $msg = __("Member delete successful. Refreshing...", "jot-plugin");                       
                break;
                case 3;
                       $msg = __("Member move successful. Refreshing...", "jot-plugin");     
                break;
                case 4;
                       $msg = __("A database problem occurred when moving members. Refreshing...", "jot-plugin");     
                break;
                case 5;
                       $msg = __("One or more members already exist in the target group. Refreshing...", "jot-plugin");     
                break;
                case 999;
                       $msg = __("No action selected. Try again", "jot-plugin");                      
                break;
                
                default:
                       $msg = __("A problem occurred ","jot-plugin") . $errorcode ;
                break;
            }
                        
            echo json_encode(array("errorcode" => $errorcode, "errormsg" => $msg));
            wp_die();
   }
   
   public function copy_selected_members($jot_target_grpid, $selected_members, $send_welcome) {
          
            global $wpdb;
           
            $errorcode = 1;
           
            foreach($selected_members as $member) {
                  // Insert into xref table
                  if (!Joy_Of_Text_Plugin()->messenger->member_in_group($jot_target_grpid, $member)) {
                        
                        $table = $wpdb->prefix."jot_groupmemxref";
                        $data = array(
                           'jot_grpid'       => $jot_target_grpid,
                           'jot_grpmemid'    => $member,
                           'jot_grpxrefts'   => current_time('mysql', 0)
                           );
                        $success=$wpdb->insert( $table, $data );
                        
                        //Send Welcome message
                        if ($send_welcome) {
                           $number = Joy_Of_Text_Plugin()->messenger->get_membernum($member);
                           $this->send_welcome_message($jot_target_grpid, $number,$member,true);
                           if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"COPY sending welcome to " . $jot_target_grpid . " " . $member  . " " . $number);
                        }
                  } else {
                        // Some members already exist in the target group
                        $errorcode = 11;
                  }
            }
          
           return $errorcode;
   }
   
   
   public function delete_selected_members($jot_source_grpid, $selected_members) {
            global $wpdb;
            $table = $wpdb->prefix."jot_groupmemxref";
           
            //Successful deletion
            $errorcode = 2;
           
            foreach($selected_members as $member) {
                 $success=$wpdb->delete( $table, array( 'jot_grpid' => $jot_source_grpid ,'jot_grpmemid' => $member ) );                   
            }
           
           return $errorcode;  
            
   }
   
   public function move_selected_members($jot_source_grpid, $jot_target_grpid, $selected_members, $send_welcome) {
            global $wpdb;
            $table = $wpdb->prefix."jot_groupmemxref";
           
            //Successful deletion
            $errorcode = 3;
           
           
            foreach($selected_members as $member) {
                   // Check if member already in target group     
                   if (!Joy_Of_Text_Plugin()->messenger->member_in_group($jot_target_grpid, $member)) {
                        $wpdb->query('START TRANSACTION');
                        
                        // Delete from source 
                        $del_success=$wpdb->delete( $table, array( 'jot_grpid' => $jot_source_grpid ,'jot_grpmemid' => $member ) ); 
                        
                        // Insert into target
                        $data = array(
                             'jot_grpid'     => $jot_target_grpid,
                             'jot_grpmemid'  => $member,
                             'jot_grpxrefts' => current_time('mysql', 0)
                         );
                        $insert_success=$wpdb->insert( $table, $data );
                        
                                               
                        if($del_success && $insert_success) {
                             $wpdb->query('COMMIT'); // if you come here then well done
                             
                             //Send Welcome message
                             if ($send_welcome) {
                                  $number = Joy_Of_Text_Plugin()->messenger->get_membernum($member);
                                  $this->send_welcome_message($jot_target_grpid, $number,$member,true);
                                  if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"MOVE sending welcome to " . $jot_target_grpid . " " . $member  . " " . $number);
                             }
                        } else {
                             $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
                             Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Member move rollback : " . $jot_source_grpid .  " " . $jot_target_grpid . " " . $member . " del >" . $del_success .  "<>" . $insert_success);
                             $errorcode = 4;
                        }
                   } else {
                        
                        // Member already exists in target group, so delete member from source group
                        $del_success=$wpdb->delete( $table, array( 'jot_grpid' => $jot_source_grpid ,'jot_grpmemid' => $member ) ); 
                        $errorcode = 5;
                  }
            }          
           return $errorcode;  
            
   }
   
   public function check_welcome_to_be_sent($jot_target_grpid, $metafield) {
            
            $currval = Joy_Of_Text_Plugin()->settings->get_groupmeta($jot_target_grpid,$metafield);
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"check_welcome_to_be_sent : " . $jot_target_grpid . " " . $metafield . " >" . $currval . "<");
            if (isset($currval)) {
               if ($currval == true) {
                   $ret = true;            
               } else {
                   $ret = false;               }
            } else {
               $ret = false;
            }
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"check_welcome_to_be_sent returning >" . $ret . "<");
            return $ret;
   }
   
   
   /*
    * Check if this group is configured with a country code
    * If so, verify the provided number using the group country code
    */
   public function get_verified_number_for_group($jot_grpid,$phone_num) {
            
            $group_cc = Joy_Of_Text_Plugin()->settings->get_groupmeta($jot_grpid,'jot_grpinvcountrycode');
            
            if ($group_cc == "nocc" || $group_cc == "") {
                  return Joy_Of_Text_Plugin()->currentsmsprovider->verify_number($phone_num);                    
            } else {
                  return Joy_Of_Text_Plugin()->currentsmsprovider->verify_number($phone_num, $group_cc);      
            }
   }
   
   /*
    * Check if this group is configured with a group number
    * If so then, welcome messages will be send using this number
    *
    */
   public function get_group_fromnumber($jot_grpid) {
            $group_phone = Joy_Of_Text_Plugin()->settings->get_groupmeta($jot_grpid,'jot_grpinvphonenumber');
           
            if ($group_phone == "default" || $group_phone == "") {
                  return "";                    
            } else {
                  return $group_phone;      
            }            
   }
   
   
   
   
   public function process_configure_smsurl() {
            
            $errorcode = 0;
            $twilio_set_voice_url = array();        
                        
	    $twilio_phonenumber  = Joy_Of_Text_Plugin()->settings->get_current_smsprovider_number();
                      
            if ($twilio_phonenumber == "") {
                $errorcode = 2;
            }
                        
            if ($errorcode == 0) {
                $new_voice_url =  get_site_url() . "?inbound";
                $twilio_set_voice_url = Joy_Of_Text_Plugin()->currentsmsprovider->setSmsUrl($twilio_phonenumber,$new_voice_url);
            }
            
            if ($twilio_set_voice_url != 0) {
                $msg = 'SMS URL NOT Set';
                $errorcode = 3;
            } else {
                $msg = 'SMS URL Set';
                $errorcode = 0;
            }
            
            $response = array('errormsg'  => $msg,
                              'errorcode' => $errorcode,
                              'voice_url' => $twilio_set_voice_url);
        
            echo json_encode($response);
            die();
           
            
    }
   
   
} // end class
 
