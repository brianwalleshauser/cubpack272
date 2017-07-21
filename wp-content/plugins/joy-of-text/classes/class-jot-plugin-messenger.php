<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Joy_Of_Text_Plugin_Messenger Class
*
*/


final class Joy_Of_Text_Plugin_Messenger {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
    function __construct() {
                 add_action( 'wp_ajax_send_message', array( &$this, 'send_message_callback' ) );
                 add_action( 'wp_ajax_nopriv_send_message', array( &$this, 'send_message_callback' ) );
                 add_action( 'wp_ajax_queue_message', array( &$this, 'queue_message' ) );
                 add_action( 'wp_ajax_process_quicksend', array( &$this, 'process_quicksend' ) );
                 add_action( 'wp_ajax_process_queue', array( &$this, 'process_queue' ) );
                 add_action( 'wp_ajax_process_messagefields_reset', array( &$this, 'process_messagefields_reset' ) );                 
                 add_action( 'wp_ajax_process_bulkadds', array( &$this, 'process_bulkadds' ) );
                 
                 add_action( 'wp_ajax_nopriv_process_queue', array( $this, 'process_queue' ) );
                 add_action( 'wp_ajax_process_queue', array( $this, 'process_queue' ) );
                
                
                
    } // end constructor
 
    private static $_instance = null;
       
    public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
    } // End instance()
 
 
    /**
     * Queue messages in table before being processed
     */
    public function process_bulkadds() {
    
             global $wpdb;
             $error = 0;
                         
             $formdata = $_POST['formdata'];
             $jot_grpid = $formdata['jot-grpid'];
             $bulkaddlist = $formdata['jot-bulkaddlist'];
                                       
             if (empty($bulkaddlist)) {
                // Empty message
                $error = 3;       
             }
                       
                       
            if ($error == 0) {
             
                                             
                          // Batch id for this set of bulk adds
                          //$batchid = uniqid(rand(), false);                    
                         
                          $bulkadd_json = stripslashes($bulkaddlist);
                          $bulkadd = json_decode($bulkadd_json,true);
                          $counter = 0;
                          
                          foreach ($bulkadd as $newmember ) {
                                       $bulkerror = 0;                                     
                                       
                                       if (!empty($newmember)) {
                                                    $singlemember = explode(',', $newmember);
                                                    
                                                    //echo sizeof($singlemember) == 7;
                                                    //echo print_r($singlemember);
                                                    
                                                    if (sizeof($singlemember) == 7) {
                                                    
                                                                 $membername = substr($singlemember[0],0,40);                                                                
                                                                 $membernum  = $singlemember[1];
                                                                 
                                                                 // Extended member info fields                                                    
                                                                 $param_extargs['jot_grpmememail']   = substr($singlemember[2],0,90);
                                                                 $param_extargs['jot_grpmemaddress'] = substr($singlemember[3],0,240);
                                                                 $param_extargs['jot_grpmemcity']    = substr($singlemember[4],0,40);
                                                                 $param_extargs['jot_grpmemstate']   = substr($singlemember[5],0,40);
                                                                 $param_extargs['jot_grpmemzip']     = substr($singlemember[6],0,40);
                                                                 
                                                    } else {
                                                       $bulkerror = 1000;
                                                       $msg = __("Data is missing. You should specify 7 columns of data - record not added.","jot-plugin");
                                                    }
                                                    
                                                    if ($bulkerror == 0 ) {
                                                                 // Check name is entered
                                                                 if (str_replace(' ', '',$membername) == '' || sanitize_text_field ($membername) == '' ) {
                                                                        $bulkerror = 1001;
                                                                        $msg = __("Please enter a name for this member","jot-plugin");
                                                                 }
                                                                
                                                                 // Check number is entered
                                                                 if (str_replace(' ', '',$membernum) == '' || sanitize_text_field ($membernum) == '' ) {
                                                                        $bulkerror = 1002;
                                                                        $msg = __("Please enter a number for this member","jot-plugin");
                                                                 }
                                                    }
                                                    
                                                    if ($bulkerror == 0) {
                                                                 $bulkerrorresp = Joy_Of_Text_Plugin()->options->process_add_member($membername, $membernum, $jot_grpid, $param_extargs);
                                                                 // Check if welcome message should be send            
                                                                 if (isset($bulkerrorresp['errorcode'])) {
                                                                      if ($bulkerrorresp['errorcode'] == 0) {
                                                                              // Send welcome message after successful add
                                                                              if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"CHECKING welcome to be added >>" . Joy_Of_Text_Plugin()->options->check_welcome_to_be_sent($jot_grpid,'jot_grpinvwelchk_jot_add') . "<<");
                                                                              if (Joy_Of_Text_Plugin()->options->check_welcome_to_be_sent($jot_grpid,'jot_grpinvwelchk_jot_add')) {
                                                                                  Joy_Of_Text_Plugin()->options->send_welcome_message($jot_grpid,
                                                                                                                                      $bulkerrorresp['verified_number'],
                                                                                                                                      $bulkerrorresp['lastid'],
                                                                                                                                      true);
                                                                                  if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"ADDING sending welcome to " . $jot_grpid . " " . $bulkerrorresp['verified_number'] . " " . $bulkerrorresp['lastid']);
                                                                              }
                                                                      }
                                                                 }                                                    
                                                    } else {
                                                                $bulkerrorresp = array('errormsg'=> $msg, 'errorcode' => $bulkerror, 'errorfield' => "",'url'=> "", 'sqlerr' => "", 'lastid'=> ""  );
                                                    }        
                                                   $allbulkerrors[] = $bulkerrorresp;
                                       }
                          } // foreach
             }
            
            
             switch ( $error ) {
                case 0; // All fine
                       $msg = "";
                break;                
                case 3; // Bulk add list is empty
                       $msg = __("Please enter members to add to this group.", "jot-plugin");         
                break;               
                default;
                       $msg = "";
                break;
             }
     
             
            $response = array('errormsg'=> $msg, 'errorcode' => $error, 'bulkerrors' => $allbulkerrors);
            echo json_encode($response);        
            
            die(); // this is required to terminate immediately and return a proper response            
             
    }
    
    /**
     * Process a request to send messages using the Messages-Quick Send form
     */
    public function process_quicksend() {
             
             $default_group = 99999999; //Quick send numbers will not be associated with a group
             
             $formdata = $_POST['formdata'];             
                                     
             parse_str($formdata['jot-allform'], $output);            
             $message              = isset($output['jot-plugin-messages']['jot-message'])             ? $output['jot-plugin-messages']['jot-message'] : "";
             $mess_type            = isset($output['jot-plugin-messages']['jot-message-type'])        ? $output['jot-plugin-messages']['jot-message-type'] : "jot-sms";
             $mess_usenumber       = isset($output['jot-plugin-messages']['jot-message-usenumber'])   ? $output['jot-plugin-messages']['jot-message-usenumber'] : "default";
             $mess_suffix          = isset($output['jot-plugin-messages']['jot-message-suffix'])      ? $output['jot-plugin-messages']['jot-message-suffix'] : "";
             $mess_audioid         = isset($output['jot-plugin-messages']['jot-message-audioid'])     ? $output['jot-plugin-messages']['jot-message-audioid'] : "";
             $mess_mmsimageid      = isset($output['jot-plugin-messages']['jot-message-mms-image'])   ? $output['jot-plugin-messages']['jot-message-mms-image'] : "";
             $mess_senderid        = isset($output['jot-plugin-messages']['jot-message-senderid'])    ? $output['jot-plugin-messages']['jot-message-senderid'] :"";
             $mess_removedupes     = isset($output['jot-plugin-messages']['jot-message-removedupes']) ? true : false ;
             $mess_sendmethod      = isset($output['jot-plugin-messages']['jot-message-sendmethod'])  ? $output['jot-plugin-messages']['jot-message-sendmethod'] : "jottabgroupsend";
             $schedule_description = isset($output['jot-plugin-messages']['jot-scheddesc'])           ? $output['jot-plugin-messages']['jot-scheddesc'] : "";
             
             // Split entered numbers by newlines and remove blanks
             $mess_numbers = array_filter(array_map('trim',$this->splitNewLine($formdata['jot-message-numberlist'])));
              
             
             /*
             echo " >> " . $message ;           
             echo " >> " . $mess_type   ;        
             echo " >> " . $mess_suffix  ;     
             echo " >> " . $mess_audioid   ;   
             echo " >> " . $mess_mmsimageid   ;
             echo " >> " . $mess_senderid     ;
             echo " >> " . $mess_removedupes  ;
             echo " >>B mess_numbers " .   print_r($mess_numbers,true);
             */
             
             // Verify Quicksend numbers and create member lists
             $rejected_numbers = array();
             $members_list = array();
             foreach ($mess_numbers as $quicksend_key => $quicksend_number){                          
                    $verified_number = Joy_Of_Text_Plugin()->currentsmsprovider->verify_number($quicksend_number);
                    if ( $verified_number == "") {
                          
                        // Add to rejected array  
                        $rejected_numbers[] = $quicksend_number;
                        
                        //Remove from number array
                        unset($mess_numbers[$quicksend_key]);
                    } else {                          
                         // Check if member already exists
                         $current_member = $this->get_member_from_num($verified_number);
                         //print_r($current_member);
                         if (isset($current_member['jot_grpmemid'])){
                             $members_list[] = $default_group . "-" . $current_member['jot_grpmemid'];
                         } else {
                             // Add this number as a new member.
                             $new_member = Joy_Of_Text_Plugin()->options->add_to_member_table($verified_number,$verified_number);
                             if ($new_member['jot_grpmemid'] != "") {
                                 $members_list[] =  $default_group . "-" . $new_member['jot_grpmemid'];
                             }
                         }
                    }
                    
             }
             
             //echo " >>A mess_numbers " .   print_r($mess_numbers,true);
             //echo " >> rej_numbers " .   print_r($rejected_numbers,true);
             //echo " >> mem list " .   print_r($members_list,true);
         
             
             $queue_args = array();
             $queue_args['jot-message']                   = $message;
             $queue_args['jot-message-usenumber']         = $mess_usenumber;
             $queue_args['jot-message-type']              = $mess_type;
             $queue_args['jot-message-suffix']            = $mess_suffix ;
             $queue_args['jot-message-audioid']           = $mess_audioid ;
             $queue_args['jot-message-mms-image']         = $mess_mmsimageid ;
             $queue_args['jot-message-senderid']          = $mess_senderid;
             $queue_args['jot-message-removedupes']       = $mess_removedupes ;
             $queue_args['jot-message-sendmethod']        = $mess_sendmethod;                          
             $queue_args['jot-scheddesc']                 = $schedule_description;
             
             $queue_args['jot-message-quicksend-numbers'] = $members_list;
             $rejected_numbers_string = implode(", ", $rejected_numbers);
             $queue_args['jot-message-quicksend-rejected_numbers'] = $rejected_numbers_string;
             
             $queue_args['jot-schedtimestamp'] = array('jot-scheddate' => isset($output['jot-scheddate']) ? $output['jot-scheddate'] : "",
                                                       'jot-schedtime' => isset($output['jot-schedtime']) ? $output['jot-schedtime'] : ""
                                                    );
             
             $queue_args['jot-schedrepeat'] = array('jot-sched-repeats-interval' => isset($output['jot-sched-repeats-interval']) ? $output['jot-sched-repeats-interval'] : "",
                                                    'jot-sched-repeats-unit' => isset($output['jot-sched-repeats-unit']) ? $output['jot-sched-repeats-unit'] : ""
                                                    );
             
             if (empty($members_list)) {
                      $msg = __("No valid numbers entered. Please try again.","jot-plugin");
                      $error = 9; // No valid numbers entered
                      $response = array('errormsg'=> $msg, 'errorcode' => $error, 'batchid' => 0, 'fullbatchsize' => 0, 'scheduled' => false, 'rejected_numbers' => $rejected_numbers_string);
                      echo json_encode($response);        
                      die();                           
             } else {
                    // Send Quicksend numbers (now members) to be queued
                    $this->queue_message($queue_args);
             }
             
    }
    
    function splitNewLine($text) {
         $code=preg_replace('/\n$/','',preg_replace('/^\n/','',preg_replace('/[\r\n]+/',"\n",$text)));
         return explode("\n",$code);
    }
    
    /**
     * Queue messages in table before being processed
     */
    public function queue_message($queue_args = null) {
    
             global $wpdb;
             $error = 0;
             $scheduled = false;
             
                         
             if (!$queue_args) {
                   $formdata = $_POST['formdata'];
                   parse_str($formdata['jot-allform'], $output);                   
                   $message              = isset($output['jot-plugin-messages']['jot-message'])             ? $output['jot-plugin-messages']['jot-message'] : "";
                   $mess_type            = isset($output['jot-plugin-messages']['jot-message-type'])        ? $output['jot-plugin-messages']['jot-message-type'] : "jot-sms";
                   $mess_usenumber       = isset($output['jot-plugin-messages']['jot-message-usenumber'])   ? $output['jot-plugin-messages']['jot-message-usenumber'] : "";
                   $mess_suffix          = isset($output['jot-plugin-messages']['jot-message-suffix'])      ? $output['jot-plugin-messages']['jot-message-suffix'] : "";
                   $mess_audioid         = isset($output['jot-plugin-messages']['jot-message-audioid'])     ? $output['jot-plugin-messages']['jot-message-audioid'] : "";
                   $mess_mmsimageid      = isset($output['jot-plugin-messages']['jot-message-mms-image'])   ? $output['jot-plugin-messages']['jot-message-mms-image'] : "";
                   $mess_senderid        = isset($output['jot-plugin-messages']['jot-message-senderid'])    ? $output['jot-plugin-messages']['jot-message-senderid'] :"";
                   $mess_removedupes     = isset($output['jot-plugin-messages']['jot-message-removedupes']) ? true : false ;
                   $mess_sendmethod      = isset($output['jot-plugin-messages']['jot-message-sendmethod'])  ? $output['jot-plugin-messages']['jot-message-sendmethod'] : "jottabgroupsend";       
                   $schedule_description = isset($output['jot-plugin-messages']['jot-scheddesc']) ? $output['jot-plugin-messages']['jot-scheddesc'] : "";
                   $process_type = 'P'; // Send immediately
                   
                   // Get selected schedule time and date                   
                   $schedule_input_timestamp = array('jot-scheddate' => isset($output['jot-scheddate']) ? $output['jot-scheddate'] : "",
                                                     'jot-schedtime' => isset($output['jot-schedtime']) ? $output['jot-schedtime'] : ""
                                                    );
                   
                   // Get schedule repeats                   
                   $schedule_input_repeat = array('jot-sched-repeats-interval' => isset($output['jot-sched-repeats-interval']) ? $output['jot-sched-repeats-interval'] : "",
                                                  'jot-sched-repeats-unit' => isset($output['jot-sched-repeats-unit']) ? $output['jot-sched-repeats-unit'] : ""
                                                  );
             
                   // Get selected members             
                   $mess_memsel_json = stripslashes($formdata['jot-message-grouplist']);
                   $mess_memsel = json_decode($mess_memsel_json,true);
                   
                   $rejected_numbers_string = "";
                   
             } else {
                   $message              = isset($queue_args['jot-message'])             ? $queue_args['jot-message'] : "";
                   $mess_type            = isset($queue_args['jot-message-type'])        ? $queue_args['jot-message-type'] : "jot-sms";
                   $mess_usenumber       = isset($queue_args['jot-message-usenumber'])   ? $queue_args['jot-message-usenumber'] : "";
                   $mess_suffix          = isset($queue_args['jot-message-suffix'])      ? $queue_args['jot-message-suffix'] : "";
                   $mess_audioid         = isset($queue_args['jot-message-audioid'])     ? $queue_args['jot-message-audioid'] : "";
                   $mess_mmsimageid      = isset($queue_args['jot-message-mms-image'])   ? $queue_args['jot-message-mms-image'] : "";
                   $mess_senderid        = isset($queue_args['jot-message-senderid'])    ? $queue_args['jot-message-senderid'] :"";
                   $mess_removedupes     = isset($queue_args['jot-message-removedupes']) ? true : false ;
                   $mess_sendmethod      = isset($queue_args['jot-message-sendmethod'])  ? $queue_args['jot-message-sendmethod'] : "jottabgroupsend";       
                   $schedule_description = isset($queue_args['jot-scheddesc']) ? $queue_args['jot-scheddesc'] : "";
                   $process_type         = isset($queue_args['jot-process-type']) ? $queue_args['jot-process-type'] : "P";
                   
                   // Get selected schedule time and date                   
                   $schedule_input_timestamp = isset($queue_args['jot-schedtimestamp']) ? $queue_args['jot-schedtimestamp'] : array() ;
             
                   // Get schedule repeats                   
                   $schedule_input_repeat =  isset($queue_args['jot-schedrepeat']) ? $queue_args['jot-schedrepeat'] : array() ;             
                                      
                   // Get selected members             
                   $mess_memsel = isset($queue_args['jot-message-quicksend-numbers']) ? $queue_args['jot-message-quicksend-numbers'] : array();
                   
                   // Get rejected numbers
                   $rejected_numbers_string = isset($queue_args['jot-message-quicksend-rejected_numbers']) ? $queue_args['jot-message-quicksend-rejected_numbers'] : "";
             }

             // If no number has been selected 
             if ($mess_usenumber == "default") {
                          $mess_usenumber = "";                                       
             }
             
             
             //echo ">>>" . $message . " >>" . $mess_type . " >>" . $mess_suffix. " >>" . $mess_audioid . " >>" . $mess_mmsimageid . " >>" . $mess_senderid . " >>Msel " . print_r($mess_memsel,true);
                          
             if (strlen($mess_senderid) > 11) {
                $mess_senderid = substr($formdata['jot-message-senderid'],0,11);
             }
             
             if (is_numeric($mess_senderid)) {
                 // Not alphanumeric
                 $error=8;
             }
             
             if ($mess_type=='jot-sms' && empty($message)) {
                // Empty message
                $error = 3;       
             }
             
                        
             if ($mess_type=='jot-call' && empty($message) && (empty($mess_audioid) || $mess_audioid == 'default' )) {
                // Empty audio message
                $error = 6;       
             }
                      
             $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;            
                        
             if ($selected_provider == 'default' || empty($selected_provider)) {
                 $error = 1;
             }
            
             if ($mess_type == 'jot-mms' && empty($mess_mmsimageid)) {
                 $error = 7;
                       
             }
             if ($error == 0) {
             
                          
                          // Truncate message if over 640 characters
                          if (strlen($message) > 640) {
                             $message = substr($message,0,640);
                          }
                          
                          // Truncate senderid if over 11 characters
                          $mess_senderid = trim($mess_senderid);
                          if (strlen($mess_senderid) > 11) {
                             $mess_senderid = substr($mess_senderid,0,11);
                          }
             
                          // Save message type
                          $smsmessage =  get_option('jot-plugin-messages');
                          $smsmessage['jot-message-type'] = $mess_type;
                             
                          // Save message suffix
                          $smsmessage['jot-message-suffix'] = $mess_suffix;
                            
                          // Save message content
                          $smsmessage['jot-message'] = $message;
                          
                          // Save audio file ID
                          $smsmessage['jot-message-audioid'] = $mess_audioid;
                          
                          // Save MMS message id
                          $smsmessage['jot-message-mms-image'] = $mess_mmsimageid;
                          
                          // Save remove dupes
                          $smsmessage['jot-message-removedupes'] = $mess_removedupes;   

                          // Save Sender ID
                          $smsmessage['jot-message-senderid'] = trim($mess_senderid);                          
                          
                          // Save Send Method
                          $smsmessage['jot-message-sendmethod'] = $mess_sendmethod;
                          
                          update_option('jot-plugin-messages',$smsmessage);
                          
                          // Save settings from extensions
                          do_action("jot_saving_extension_settings",$formdata);
                            
                          // Append Message suffix
                          if (!empty($mess_suffix)) {
                                $fullmessage = $message . " " . $mess_suffix ;                     
                          } else {
                                $fullmessage = $message;    
                          }                                                
                                             
                          // Batch id for this set of messages
                          $batchid = uniqid(rand(), false) ;      
                          
                          // Set schedule timestamp
                          $temp_schedule_timestamp = '2000-01-01 00:00:01';
                          $temp_schedule_timestamp = apply_filters('jot_queue_message_schedule',$temp_schedule_timestamp,$schedule_input_timestamp);
                             
                          if ($temp_schedule_timestamp == '2000-01-01 00:00:01') {
                                       // Not scheduled
                                       $schedule_timestamp = $temp_schedule_timestamp;
                                       $message_status = $process_type;
                                       $scheduled = false;
                          } else {
                                       // Scheduled
                                       $schedule_timestamp = $temp_schedule_timestamp;
                                       $message_status = "S";
                                       $scheduled = true;                                       
                          }
                          
                          $num_queued = 0;
                          foreach ($mess_memsel as $memsel ) {
                                       $queue_member = true;
                                       
                                       list($jotgrpid,$jotmemid) = explode("-", $memsel, 2);
                                                                             
                                       if($mess_removedupes) {
                                           $queue_member = $this->member_already_queued($batchid,$jotmemid);                                                    
                                       } else {
                                           $queue_member = true;
                                       }
                                       
                                       $member = $this->get_member($jotmemid);

                                       // Replace tags in message
                                       $finalmessage = "";
                                       $finalmessage = $this->get_replace_tags($fullmessage,$member,$jotgrpid);
                                       $finalmessage = apply_filters('jot-queue-message',$finalmessage);
                                             
                                       // Truncate message if over 640 characters
                                       if (strlen($finalmessage) > 640) {
                                          $finalmessage = substr($finalmessage,0,640);
                                       }                                 
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
                                       
                                       $data = array(
                                                  'jot_messqbatchid'    => $batchid,
                                                  'jot_messqgrpid'      => (int) $jotgrpid,
                                                  'jot_messqmemid'      => (int) $jotmemid,
                                                  'jot_messqcontent'    => $finalmessage,
                                                  'jot_messqtype'       => $message_type,
                                                  'jot_messqfromnumber' => $mess_usenumber,
                                                  'jot_messqaudio'      => $media_id,
                                                  'jot_messqstatus'     => $message_status,
                                                  'jot_messsenderid'    => $mess_senderid,
                                                  'jot_messqschedts'    => $schedule_timestamp,
                                                  'jot_messqts'         => current_time('mysql', 0)
                                                  
                                       );
                                       
                                       //echo "==" .print_r($data,true);
                                       
                                       // If removedupes is set and member is not already queued then insert.   
                                       if ($queue_member) {                         
                                           $table = $wpdb->prefix."jot_messagequeue";
                                           $success=$wpdb->insert( $table, $data );                                          
                                           $num_queued++;     
                                       } else {
                                           //Ignore dupe                                           
                                       }
                          }
                          
                          // if this is a scheduled queue then add record to schedule history.
                          if ($scheduled == true) {
                               
                               // Write schedule history header.
                               do_action("jot_sched_add_schedule", $batchid, $schedule_timestamp, $message_status, $message, $num_queued, $schedule_description, $message_type);
                             
                               //  If this is a repeated schedule, then schedule the repeats.       
                               do_action("jot_sched_add_repeats", $batchid, $mess_memsel, $data, $schedule_input_timestamp, $schedule_input_repeat, $mess_removedupes,$fullmessage, $schedule_description);
                                           
                          }
             }
            
            
             switch ( $error ) {
                case 0; // All fine
                       if($mess_removedupes) {
                         $dupes_removed = count($mess_memsel) - $num_queued;
                         $msg =  sprintf( __("Messages Queued. %d duplicate numbers removed. Sending..."), $dupes_removed );
                       } else {
                         $msg = "Messages Queued...Sending...";
                       }
                break;
                case 1; // No SMS provider set
                       $msg = __("Please select and configure an SMS provider.", "jot-plugin");         
                break;
                case 3; // Message is empty
                       $msg = __("Please enter a message.", "jot-plugin");         
                break;
                case 6; // No audio file selected.
                       $msg = __("Please enter a message or select an audio file for a call message", "jot-plugin");
                break;
                case 7; // No image has been selected for MMS message types.
                       $msg = __("No image has been selected for an MMS message type", "jot-plugin");
                break;
                case 8; // Sender ID must be alphanumeric.
                       $msg = __("The Sender ID must contain at least 1 letter and no more than 11 alphanumeric characters may be used.", "jot-plugin");
                break;                
                default;
                       $msg = "";
                break;
             }
     
             
            $response = array('errormsg'=> $msg, 'errorcode' => $error, 'batchid' => $batchid, 'fullbatchsize' => $num_queued, 'scheduled' => $scheduled, 'rejected_numbers' => $rejected_numbers_string);
            echo json_encode($response);        
            
            die(); // this is required to terminate immediately and return a proper response            
             
    }
    
    /*
     *
     * Check whether this memberid is already queued in this batch.
     *
     */
    public function member_already_queued($batchid,$jotmemid) {
         
            global $wpdb;
                                                
            $table = $wpdb->prefix."jot_messagequeue";
            $sql = " SELECT  jot_messqmemid" .
                   " FROM " . $table  .
                   " WHERE  jot_messqbatchid = %s " .
                   " AND    jot_messqmemid = %d ";   
           
            $sqlprep = $wpdb->prepare($sql, $batchid, $jotmemid);           
            $memlist = $wpdb->get_results( $sqlprep );
            
            if ($memlist) {
               // Already queued, so don't queue again
               return false;
            } else {
               // Not queued, so queue
               return true;
            }
             
    }
 
    /**
     *
     * Process queued messages
     *
     * */
    public function process_queue($process_args = null) {
             
             /*
              *
              * Process type :
              *
              *   D : Dripfeed - messages are read off the queue in batches by a scheduled task
              *   P : Pending  - messages are sent immediately, managed by the admin or jotgroupsend screens.
              *   
              */
             
             $batchid       = isset($_POST['jot_batchid'])         ? $_POST['jot_batchid']       : "";
             $engines       = isset($_POST['jot_engines'])         ? $_POST['jot_engines']       : 1 ;
             $process_type  = isset($process_args['jot-process-type']) ? $process_args['jot-process-type'] : 'P';
             $maxbatchsize  = isset($process_args['jot-process-maxbatchsize']) ? $process_args['jot-process-maxbatchsize'] : 5;
             $fullbatchsize = isset($_POST['jot_fullbatchsize'])   ? $_POST['jot_fullbatchsize'] : $maxbatchsize; //Use when queue sweeping 
             $process_batchsize = $maxbatchsize;
             $error = 0;
             $all_send_errors = null;
                   
             //echo "in process queue >" . $batchid . "<>" . $engines . "<>" . $maxbatchsize . "<>" . $fullbatchsize  ;      
            
             if (($engines * $maxbatchsize) > $fullbatchsize) {
                          $process_batchsize = round ($fullbatchsize / $engines); 
             }
             
             if ($process_batchsize == 0 ) {
                          $process_batchsize = 1;
             }
             
             $queued_messages = $this->get_queue_batch($batchid, $process_batchsize, $process_type);
             
            
             if (count($queued_messages) > 0) { 
                  //$current_user = wp_get_current_user();
                  $this->log_to_file(__METHOD__,"Process type :" . $process_type . " Sending message batch : " . $batchid ." Process_batchsize : " . $process_batchsize . " Number of messages : " . count($queued_messages));
             }
             
             foreach ($queued_messages as $message) {
                          
                   $error=0;
                   $member = $this->get_member($message->jot_messqmemid);
                   
                   // Is Sender ID set? If not use the number
                   if (!empty($message->jot_messsenderid)) {
                       $senderid = $message->jot_messsenderid;
                   } else {
                       $senderid = "";
                   }
                   
                   if (!empty($member)) {                    
                                       switch ( $message->jot_messqtype ) {
                                          case 'S';
                                             $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($member['jot_grpmemnum'],$message->jot_messqcontent,$senderid,$message->jot_messqfromnumber);
                                          break;
                                          case 'M';
                                             $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_mmsmessage($member['jot_grpmemnum'],$message->jot_messqcontent, $message->jot_messqaudio,$senderid,$message->jot_messqfromnumber);
                                          break;
                                          case 'c';
                                             $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_callmessage($member['jot_grpmemnum'],$message->jot_messqcontent, $message->jot_messqaudio,$message->jot_messqfromnumber);                                                   
                                          break;
                                       }
                   }
                   
                   $all_send_errors[] = array("send_message_number" => $message_error['send_message_number'] ,"send_message_errorcode" => $message_error['send_message_errorcode'] ,"send_message_msg" => $message_error['send_message_msg']); 
                                  
                   //$this->log_to_file(__METHOD__,">>error : " . $error . " Member : " . print_r($member,true) . " Message_error : " . print_r($message_error,true));     
                      
                                                      
                   
                   $collate_args = array('jot_batchid' => $message->jot_messqbatchid,  
                                         'jot_messsubtype' => 'QM'
                   );
                   
                   // Log messages in history table
                   if ($message_error['send_message_type'] == 'SMS') {
                          $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",$member['jot_grpmemid'],$message_error,$collate_args);
                   }
                   if ($message_error['send_message_type'] == 'MMS') {
                          $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",$member['jot_grpmemid'],$message_error,$collate_args);
                   }   
                   if ($message_error['send_message_type'] == 'call') {
                          // Has audio file call be sent
                          $messagecontent = isset($message->jot_messqcontent) ? $message->jot_messqcontent : "";
                          if (empty($message->jot_messqcontent) && $mess_audioid != 'default' ) {
                              $messagecontent = __("(Audio file sent)","jot-plugin");
                          }                               
                          $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_call("o",$member['jot_grpmemid'],$messagecontent,$message_error,$collate_args);
                   }   
     
             }
             
             $remaining_messages = $this->count_queue_batch($batchid, "X");
             
             $response = array("batchid" => $batchid, "batchsize" => count($queued_messages), 'remaining_messages' => $remaining_messages, 'process_batchsize' => $process_batchsize, 'batcherrors' => $all_send_errors);
            
             // If called from another function return response else return ajax response
             if ($process_args != null) {
                 return $response;
             } else {
                 echo json_encode($response);        
                 die();
             }
    }
    
    /*
     *    
     * Update status of processed message 
     *
     */
    public function update_queue_status($queueid, $status) {
             
            global $wpdb;
            $response = "";                          
            $table = $wpdb->prefix."jot_messagequeue";
            
            $data = array(
                      'jot_messqstatus'   => $status                             
                      );
            $wpdb->update( $table, $data, array( 'jot_messqid' =>  $queueid ) );
          
            
    }
    
    /*
     *    
     * Delete processed row 
     *
     */
    public function delete_from_queue($queueid) {
             
            global $wpdb;
                                    
            $table = $wpdb->prefix."jot_messagequeue";
            $wpdb->delete( $table, array( 'jot_messqid' =>  $queueid ) );
            $wpdb->query('COMMIT'); 
            
    }
    
    /*
     *
     *  Count number of messages still to be processed in the given batch
     *
     */
    public function count_queue_batch($batchid, $status) {             
                         
             global $wpdb;           
                          
             $table = $wpdb->prefix."jot_messagequeue";            
             
             $sql = " SELECT count(*) as messcount" .
                   " FROM " . $table  .
                   " WHERE jot_messqstatus <> %s " . 
                   " AND jot_messqbatchid = %s";
             
             
             $sqlprep = $wpdb->prepare($sql, $status, $batchid);
             
             $batchcount = $wpdb->get_row( $sqlprep );  
                     
             $remaining_count = isset($batchcount->messcount) ? $batchcount->messcount : -1;
                          
             return apply_filters('jot_count_queue_batch',$remaining_count);  
   
    }
    
    
    /*
     *
     *  Get next batch of messages to process
     *
     */
    public function get_queue_batch($batchid,$batchsize, $process_type) {             
                         
             global $wpdb;
             $response = "";
             
             if ($batchid != "") {
               $clause = " AND jot_messqbatchid = '" . $batchid . "'" ;             
             } else {
               $clause = "";            
             }
                          
             $table = $wpdb->prefix."jot_messagequeue";
            
             $wpdb->query('START TRANSACTION');
             $sql = " SELECT  jot_messqid, jot_messqbatchid, jot_messqgrpid, jot_messqmemid, jot_messqcontent,jot_messqtype,jot_messqstatus,jot_messqaudio,jot_messsenderid, jot_messqfromnumber " .
                   " FROM " . $table  .
                   " WHERE jot_messqstatus = '%s' " . 
                   $clause .
                   " ORDER BY jot_messqts, jot_messqid" .
                   " LIMIT  %d ";
             
            
             
             // SQLite doesn't support FOR UPDATE
             if (USING_SQLITE == 'false') {    
                $sql .=  " FOR UPDATE ";      
             }                              
                     
             $sqlprep = $wpdb->prepare($sql, $process_type, $batchsize);            
             $batchlist = $wpdb->get_results( $sqlprep );  
                  
             foreach ($batchlist as $queueitem) {      
                   $this->update_queue_status($queueitem->jot_messqid,'X');
             }
            
             $wpdb->query('COMMIT');
            
             return apply_filters('jot_get_queue_batch',$batchlist);  
   
    }
    
    /*
     *
     * Runs as a scheduled task to processs any message left on the queue in Pending status
     *
     */
    public function queue_sweeper() {            
            
            // Process Queue looking for "Drip feed" messages
            $process_args = array('jot-process-type' => "D",
                                  'jot-process-maxbatchsize' => jot_dripfeed_batchsize);
            
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,print_r($process_args,true));
            
            $sweep_response = $this->process_queue($process_args);
            $chkbatchsize = isset($sweep_response["batchsize"]) ? $sweep_response["batchsize"] : 0;
	    if ($chkbatchsize > 0 ) {
                $this->log_to_file(__METHOD__,"Running queue sweeper : " . print_r($sweep_response,true));
            }
            
    }
 
     
    function get_replace_tags($message,$member,$groupid = null) {
             
             if (!$member) {
                return $message;
             }
           
             $message = str_replace('%name%',$member['jot_grpmemname'], $message);
             $message = str_replace('%fullname%',$member['jot_grpmemname'], $message);
             
             $nameparts = $this->split_name($member['jot_grpmemname']);
             $message = str_replace('%firstname%',$nameparts['firstname'], $message);
             $message = str_replace('%lastname%',$nameparts['lastname'], $message);
             
             $message = str_replace('%number%',$member['jot_grpmemnum'], $message);
             $message = str_replace('%jot_number%',$member['jot_grpmemnum'], $message);
             $message = str_replace('%lastpost%',$this->get_last_post(), $message);
                 
             // Merge tags for extended member info
             $message = str_replace('%jot_id%',$member['jot_grpmemid'], $message);
             $message = str_replace('%jot_email%',$member['jot_grpmememail'], $message);
             $message = str_replace('%jot_address%',$member['jot_grpmemaddress'], $message);
             $message = str_replace('%jot_city%',$member['jot_grpmemcity'], $message);
             $message = str_replace('%jot_state%',$member['jot_grpmemstate'], $message);
             $message = str_replace('%jot_zip%',$member['jot_grpmemzip'], $message);
             $message = str_replace('%jot_timestamp%',$member['jot_grpmemts'], $message);
             
             // Date tags
             $message = str_replace('%day%',date("l"), $message);
             
             // Strip non-UTF tags
             $message = $this->strip_non_utf8($message);
                           
             // Replace subscription manager merge tag
             $subcommand = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbmanagesubs');
             $message = str_replace('%submgr%',$subcommand, $message);
             $message = str_replace('%jot_submgr%',$subcommand, $message);
                 
             // If groupid is set then replace group details
             if ($groupid != null) {
                          // Merge tags for group info.
                          $groupdetails = Joy_Of_Text_Plugin()->settings->get_group_details($groupid);
                          if ($groupdetails) {
                             $message = str_replace('%jot_groupid%',$groupid, $message);
                             $message = str_replace('%jot_groupname%',$groupdetails->jot_groupname, $message);
                             $message = str_replace('%jot_groupdesc%',$groupdetails->jot_groupdesc, $message);
                          }
                          
                          // Replace optout
                          $groupinvite = Joy_Of_Text_Plugin()->options->get_groupinvite($groupid);
                          $jot_groupoptout = isset($groupinvite->jot_groupoptout) ? $groupinvite->jot_groupoptout : "";
                          $message = str_replace('%optout%',$jot_groupoptout, $message);
                          $message = str_replace('%jot_optout%',$jot_groupoptout, $message);             
             }
                         
              
             return apply_filters('jot_get_replace_tags',$message);    
    }
    
    function split_name($name)  {
          $name = trim($name);
          if (strpos($name, ' ') === false) {
                return array('firstname' => $name, 'lastname' => '');             
                     
          } else {
             
                 $parts     = explode(" ", $name);
                 $firstname = reset($parts);                 
                 array_shift($parts);
                 $lastname = implode(" ", $parts);
                
                 return array('firstname' => $firstname, 'lastname' => $lastname);
          }    
    }
    
    function get_last_post() {
           $args = array( 'numberposts' => '1' );
           $recent_posts = wp_get_recent_posts( $args );
           foreach( $recent_posts as $recent ){
               return get_permalink($recent["ID"]);
           }     
    }
    
    
    
    public function save_call_message($messageid, $tonumber, $fullmessage, $audioid) {
          
             global $wpdb;          
             
             $table = $wpdb->prefix."jot_messages";
             $data = array(
                    'jot_messageid'      => sanitize_text_field ($messageid),
                    'jot_messagenum'     => $tonumber,
                    'jot_messagecontent' => sanitize_text_field ($fullmessage),
                    'jot_messageaudio'   => sanitize_text_field ($audioid)
             );
             $success=$wpdb->insert( $table, $data ); 
             if ($wpdb->last_error !=null) {
                 $this->log_to_file(__METHOD__,"*** In save_call_message *** " . $messageid . " SQL error : " . $wpdb->last_error);           
                 return 5;
             } else {
                 return 0;     
             }
                                  
    }
    
    public function get_saved_message($messageid) {
        
            //Get message which will be played as a voice call
            global $wpdb;
            
            $table = $wpdb->prefix."jot_messages";
            $sql = " SELECT  jot_messagecontent, jot_messageaudio, jot_messagenum " .
                   " FROM " . $table  .
                   " WHERE jot_messageid = '" . $messageid . "'";
        
            $message = $wpdb->get_row( $sql );
            
            $messagecontent['messagecontent'] = $message->jot_messagecontent;
            $messagecontent['messageaudio'] = $message->jot_messageaudio;
            $messagecontent['messagenum'] = $message->jot_messagenum;
                          
            return apply_filters('jot_saved_message',$messagecontent);
    }
    
    public function delete_saved_message($messageid) {
        
            //Delete saved message after text-to-voice call
            global $wpdb;
            $table = $wpdb->prefix."jot_messages";
            $success=$wpdb->delete( $table, array( 'jot_messageid' => $messageid ) );
            if ($wpdb->last_error != 0) {
               $this->log_to_file(__METHOD__,"Error deleting saved messageid:" . $messageid . " " . $wpdb->last_error);
            }
    }
    
    public function log_to_file($method, $text = "") {
        
        $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
        $file = WP_PLUGIN_DIR. "/joy-of-text/log/jot-" . $selected_provider . "-calls.log";
       
        // log message info to a file
        if(!file_exists(dirname($file))) {
            mkdir(dirname($file), 0755, true);            
        } else {
            file_put_contents($file, "==" . date('m/d/Y h:i:s a', time()) . "||" . $method . "||" . $text . "\r\n"  ,FILE_APPEND);
        }        
    }
    
    public function log_to_history($data) {
        
        global $wpdb;
                
        $table = $wpdb->prefix."jot_history";
        //$this->log_to_file(__METHOD__,">> Inserting" . print_r($data,true) );
       
        $setutf  = $wpdb->query('set names utf8;');        
        $success = $wpdb->insert( $table, $data );
       
        if ($wpdb->last_error != 0) {
             $this->log_to_file(__METHOD__,"log_to_history error " . $wpdb->last_error);
             $this->log_to_file(print_r($data,true));
        }
             
        if ($wpdb->last_error != 0) {
             return 5; //Error inserting into the database
        } else {
            return 0;
        }
        
               
    }
    
    public function update_status($jot_histsid,$messagestatus,$messageprice,$messagefrom,$error_code) {
        
        global $wpdb;
        
        $table = $wpdb->prefix."jot_history";        
        $data = array(
                      'jot_histstatus'   => sanitize_text_field ($messagestatus),
                      'jot_histprice'    => $messageprice,
                      'jot_histfrom'     => $messagefrom
                      );
        
        // If there was an error, then update the history
        // This is important when using Messaging Services, as errorcode is always zero initially.
        if ($error_code != 0) {
             $data['jot_histstatus']  = $error_code;
             $data['jot_histerrcode'] = $error_code;
        }
        
        $wpdb->update( $table, $data, array( 'jot_histsid' =>  $jot_histsid ) );
                 
    }
    
    function call_curl($url,$data, $request_type) {
            if ($this->is_curl_installed()) {
                   
                //$TwilioAuth = get_option('jot-plugin-smsprovider');
                $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            
                //$sid = isset($TwilioAuth['jot-accountsid-' . $selected_provider]) ? $TwilioAuth['jot-accountsid-' . $selected_provider] : ""; 
                //$token = isset($TwilioAuth['jot-authsid-' . $selected_provider]) ? $TwilioAuth['jot-authsid-' . $selected_provider] : "";               
                $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
                $token = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-authsid-' . $selected_provider);
                
                $jot_curl = curl_init($url );
                // Was there an error?
                if ($jot_curl === false) {
                          if (null !== curl_errno($jot_curl)) {
                             $curl_errno = curl_errno($jot_curl);
                             $curl_message = curl_strerror($curl_errno);          
                          } else {
                             $curl_message = "";
                          }
                          echo "CURL_init error : " . curl_error($jot_curl) . "<>" . curl_errno($jot_curl) . "<>" . $curl_message;            
                }
                
                // Send data for post requests
                if (strcasecmp($request_type,"post") == 0) {
                    //$post = http_build_query($data);
                    $post_string = http_build_query($data, null, '&');
                    $post = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $post_string);
                    
                    curl_setopt($jot_curl, CURLOPT_POST, true);
                    curl_setopt($jot_curl, CURLOPT_POSTFIELDS, $post);
                }
                curl_setopt($jot_curl, CURLOPT_REFERER, $_SERVER['SERVER_NAME'] . "?version=" . Joy_Of_Text_Plugin()->version);
                curl_setopt($jot_curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($jot_curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($jot_curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($jot_curl, CURLOPT_USERPWD, "$sid:$token");
                
                $jot_curl_output = curl_exec($jot_curl);
                
                // Was there an error?
                if ($jot_curl_output === false) {                                            
                          if (null !== curl_errno($jot_curl)) {
                             $curl_errno = curl_errno($jot_curl);
                             $curl_message = curl_strerror($curl_errno);          
                          } else {
                             $curl_message = "";
                          }
                          echo "CURL_exec error : " . curl_error($jot_curl) . "<>" . curl_errno($jot_curl) . "<>" . $curl_message . "<>" . $url;            
              }
                
                
                
                curl_close($jot_curl);
                
           } else {
                echo "CURL is NOT installed on this server";
                wp_die();
           }           
           return $jot_curl_output;
    }

    // Check if curl is installed
    function is_curl_installed() {             
             return function_exists('curl_version');
    }
    
    // Check if inbound number is a member of the selected admin group
    public function check_admin_group($inboundnumber) {
             $admin_groupid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-admingroup');
             
             if ($admin_groupid == "") {
                 if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Admin group set to blank.");
                 return false;
             }
             
             if ($admin_groupid ==  -1) {
                 if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Admin group set to -1.");
                 return false;
             }
             
             if (Joy_Of_Text_Plugin()->options->number_exists($inboundnumber, $admin_groupid) ) {
                 if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Number " . $inboundnumber . " FOUND in admin group " . $admin_groupid);
                 return true;
             } else {
                 if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Number " . $inboundnumber . " NOT FOUND in admin group " . $admin_groupid);
                 return false;
             }
    }
 
    // Does the inbound SMS number in e.164 format match the admin number
    public function match_incoming($inboundnumber, $adminnumber) {
        
        if ($inboundnumber == $adminnumber) {
            return true;
        } else {
             if (!empty($adminnumber)){
                          // remove leading zero if present
                          $adminnumber = ltrim($adminnumber,'0');
                          
                          // See if the admin number is contained in the e.164 inbound number
                          if (strpos($inboundnumber,$adminnumber)) {
                              return true;
                          } else {
                              return false;
                          }
            } else {
               return false;
            }
        }
        
    }
    
    public function process_inbound_command($inboundsms) {             
         
        $response = "";
        $response_msgsent = 0;
        $response_totalmsg = 0;
        
        //preg_match_all("/\[.*?\]/",$_REQUEST['Body'], $command);
        preg_match_all("/[\/\[].*?[\/\]]/", $_REQUEST['Body'], $command);
                 
        // Must be a @ or # command
        if (count($command[0]) == 0) {
             preg_match_all("/^[@#].*/", $_REQUEST['Body'], $command);
             $comm_list = explode(" ", $command[0][0],2);
             $fullcommand = isset($comm_list[0]) ? $comm_list[0] : "" ;
             $command = substr($fullcommand, 0, 1);
             
             switch ($command) {
                     case '#';  // format is #{number}  
                          $number = str_replace('#','',$fullcommand);                                   
                     break;
                     case '@';  // format is @{member id}
                          $memid = str_replace('@','',$fullcommand);
                          $number = $this->get_membernum($memid);         
                     break;
                     default;
                            
                     break;
             }             
             $messagebody = $comm_list[1]; //str_replace($fullcommand,'',$_REQUEST['Body']);
                         
        } else {
             // must be a // or [ ] command          
             $fullcommand = $command[0][0];
             $commstr = str_replace( array('[',']','/') , ''  , $command[0][0]);
             $comm_list = explode(" ", $commstr);
             $command = isset($comm_list[0]) ? $comm_list[0] : "";
             $object  = isset($comm_list[1]) ? $comm_list[1] : "";
             $param1  = isset($comm_list[2]) ? $comm_list[2] : "";
             $param2  = isset($comm_list[3]) ? $comm_list[3] : "";
             $messagebody = str_replace($fullcommand,"",$_REQUEST['Body']);
        }
         
        if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Admin command >" . $command . "<#". $object . "#". $param1 . "#" . $param2 . "#". $number . "#". $messagebody .'#');
        
        if (is_numeric($command)) {
             //command is the memberid
             //object is the message
             $memnum = "";
             $memnum = $this->get_membernum($command);
             
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"In is_numeric >" . $memnum . "<>" . $command . "<");
        
                        
             if (!empty($memnum)) {
                 $msgerr = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($memnum, $messagebody);
                 $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                       'jot_messsubtype' => 'CM'
                                       );
                 Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o","",$msgerr,$collate_args);
                 if ($msgerr['send_message_errorcode'] == 0) {                                       
                          $response_msgsent = 1;                                       
                 }
                 $response_totalmsg = 1;
                  
             }
        } else {
             switch ( strtoupper($command) ) {
                     case 'GET': { 
                               switch ( strtoupper($object)) { 
                                   case 'GROUPS': {
                                       //if (!empty($param1)) {
                                           // $param1 will be the optional name search string
                                           $response = $this->get_groups($param1);
                                       //}
                                   break; 
                                   }
                                   case 'GROUP': {
                                       if (!empty($param1)) {
                                          // $param1 will be the groupid
                                          // $param2 optional member search string
                                          $response = $this->get_groupmembers($param1, $param2);
                                       }
                                   break; 
                                   }
                               }    
                     break;
                     }
                     case 'SEND':
                               switch ( strtoupper($object)) {                                   
                                   case 'GROUP': {
                                       if (!empty($param1)) {
                                          // $param1 will be the groupid
                                          // $param2 will OPTIONALLY be a batchid
                                          if ($param2 != "") {
                                              // Batch ID passed through command
                                              $batchid = $param2;
                                          } else {                                         
                                              $batchid = uniqid(rand(), false);
                                          }
                                          // Subtype = G - group send.
                                          $collate_args = array('jot_batchid' => $batchid,  
                                                                  'jot_messsubtype' => 'CG'
                                                                 );
                                          
                                          $cmd_response = $this->send_to_group($param1,$messagebody,$collate_args);
                                          $response_message = isset($cmd_response['message']) ? $cmd_response['message'] : "" ;
                                          $response_msgsent = isset($cmd_response['msgsent']) ? $cmd_response['msgsent'] : 0 ;
                                          $response_totalmsg = isset($cmd_response['totalmsg']) ? $cmd_response['totalmsg'] : 0;
                                          $response = $response_message;
                                       }
                                   break; 
                                   }
                               }
                     case '#' :
                     case '@' : {
                          // Send a message to the number given in or derived from the # or @ commands
                          $verified_number = Joy_Of_Text_Plugin()->currentsmsprovider->verify_number($number);
                          if ( $verified_number != "") {                                                   
                              
                              // See if this number is for an existing member
                              $member = $this->get_member_from_num($verified_number);
                              if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Get Member Details>>" . print_r($member,true));
                      
                              if (isset($member['jot_grpmemid'])) {
                                  $messagebody = $this->get_replace_tags($messagebody,$member);
                              }
                              
                              $msgerr = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($verified_number, $messagebody);
                              $response_totalmsg = 1;
                              $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                                    'jot_messsubtype' => 'CS'
                                       );
                              Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o","",$msgerr,$collate_args);
                              if ($msgerr['send_message_errorcode'] == 0) {                                       
                                       $response_msgsent = 1;                                       
                              }
                          }
                     }
                     break;     
                     default;
                            
                     break;
              }
         
        }
         
        return array('message' =>$response, 'msgsent' => $response_msgsent, 'totalmsg' => $response_totalmsg );
    }
    
  
    /*
     *
     * Get groups for remote command requests
     *
     */
     public function get_groups($grpname = '') {
        
            //Get group id and group name for all or given group
            global $wpdb;
            $response = "";
            $truncated = false;
            
            // If no parameters then retreive all groups.
            if(!empty($grpname)) {
               $clause = " WHERE jot_groupname like '%$grpname%'" ;                                    
            } else {
               $clause = "";
            }
            
            $table = $wpdb->prefix."jot_groups";
            $sql = " SELECT  jot_groupid, jot_groupname " .
                   " FROM " . $table  .
                   $clause .
                   " ORDER BY 1";
           
            $grouplist = $wpdb->get_results( $sql );             
            foreach ( $grouplist as $group ) 
            {
                $nextgrp = $group->jot_groupid . " " . $group->jot_groupname . "\n";
                
                // Limit to 2x160 messages
                if (strlen($nextgrp) + strlen($response) < 305) {
                   $response .= $nextgrp;
                } else {
                   $truncated = true;                 
                }
            }
            // Limit response to two messages
            // Providers seem to hold back longer messages
            if ($truncated) {
               $response .=  __("(Msg Truncated)","jot-plugin");                                       
            }
           
            return apply_filters('jot_get_groups',$response);
            
    }
    
    /*
     *
     * Get groups for display in drop downs
     *
     */
    public function get_display_groups() {
        
        
             global $wpdb;
                                                 
             $table = $wpdb->prefix."jot_groups";
             $sql = " SELECT  jot_groupid, jot_groupname, jot_groupoptout, jot_groupopttxt, jot_groupautosub" .
                    " FROM " . $table  .
                    " ORDER BY 2" ;   
            
             $grplist = $wpdb->get_results( $sql );
             
                  
             return apply_filters('jot_get_jot_groups',$grplist);
		
    }
    
    /*
     *
     * Get oldest - used in Get Started examples
     *
     */
    public function get_oldest_groups() {
			
			global $wpdb;
							    
			$table = $wpdb->prefix."jot_groups";
			$sql = " SELECT  min(jot_groupid) as jot_groupid" .
			       " FROM " . $table  ;   
		       
			$grp = $wpdb->get_row( $sql );         
			
			return apply_filters('jot_get_oldest_group',$grp->jot_groupid);
		
    }
    
    /*
     *
     * Used for [get group] command
     *
     */
    public function get_groupmembers($groupid,$namesearch='') {
       
            //Get members from the given group and name search string
            global $wpdb;
            $response = "";
            
            // If no parameters then retreive all members.
            if(!empty($namesearch)) {
               $clause = " AND jot_grpmemname like '%" .  sanitize_text_field($namesearch) ."%'" ;                                    
            } else {
               $clause = "";
            }
                                    
            $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
            $tablexref = $wpdb->prefix."jot_groupmemxref";   // b

            $sql = " SELECT  a.jot_grpmemname, a.jot_grpmemnum, b.jot_grpmemid " .
                   " FROM " . $tablegrpmem .  " a," . $tablexref . " b " . 
		   " WHERE b.jot_grpid = " . $groupid .
                   " AND a.jot_grpmemid = b.jot_grpmemid " .
                   $clause .
                   " ORDER BY 1";
                   
            if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__, $sql . " " . $groupid); 
            
            $memlist = $wpdb->get_results( $sql );             
            foreach ( $memlist as $member ) 
            {             
                $nextmem = $member->jot_grpmemid . " " . $member->jot_grpmemname . " " . substr($member->jot_grpmemnum,0,20) . "\n"; 
                
                // Limit to 2x160 messages
                if (strlen($nextmem) + strlen($response) < 305) {
                   $response .= $nextmem;
                } else {
                   $truncated = true;                 
                }
            }
            // Limit response to two messages
            // Providers seem to hold back longer messages
            if ($truncated) {
               $response .=  __("(Msg Truncated)","jot-plugin");                                       
            }
            
            return apply_filters('jot_get_groupmembers',$response);
            
    }
    
    /*
     *
     *  Get member details from id
     *
     */
    public function get_member($jotmemid) {
        
            //Get member details for given memberid
            global $wpdb;
            
            
            $table_members = $wpdb->prefix."jot_groupmembers";
            $sql = " SELECT jot_grpmemid, jot_grpmemname, jot_grpmemnum, jot_grpmemstatus , " .
                   " jot_grpmememail, jot_grpmemaddress, jot_grpmemcity, jot_grpmemstate, " .    
	           " jot_grpmemzip, jot_grpmemts     " .
                   " FROM " . $table_members  .
                   " WHERE jot_grpmemid =" . $jotmemid;
                       
            $member = $wpdb->get_row( $sql );
           
            $memarr = array("jot_grpmemid"      => $jotmemid,
                            "jot_grpmemname"    => isset($member->jot_grpmemname)    ? $member->jot_grpmemname    : "",
                            "jot_grpmemnum"     => isset($member->jot_grpmemnum)     ? $member->jot_grpmemnum     : "",
                            "jot_grpmemstatus"  => isset($member->jot_grpmemstatus)  ? $member->jot_grpmemstatus  : "",
                            "jot_grpmememail"   => isset($member->jot_grpmememail)   ? $member->jot_grpmememail   : "",
			    "jot_grpmemaddress" => isset($member->jot_grpmemaddress) ? $member->jot_grpmemaddress : "",
			    "jot_grpmemcity"    => isset($member->jot_grpmemcity)    ? $member->jot_grpmemcity    : "",
			    "jot_grpmemstate"   => isset($member->jot_grpmemstate)   ? $member->jot_grpmemstate   : "",
			    "jot_grpmemzip"     => isset($member->jot_grpmemzip)     ? $member->jot_grpmemzip     : "",
			    "jot_grpmemts"      => isset($member->jot_grpmemts)      ? $member->jot_grpmemts      : ""
                            );
            
            return apply_filters('jot_get_member',$memarr,$jotmemid);
    }
    
     /*
      *
      * Get member id for the given phone number
      * This is used in this routed SMS notification
      *
      */
      public function get_member_from_num($number) {
             
             global $wpdb;
             $memarr = array();
             
             $table = $wpdb->prefix."jot_groupmembers";
             $sql = " SELECT jot_grpmemid, jot_grpmemname, jot_grpmemnum, jot_grpmemts " .
                   " FROM " . $table  .
                   " WHERE jot_grpmemnum = '" . $number . "'" .
                   " ORDER BY jot_grpmemts DESC " . 
                   " LIMIT 1";
             
             
             $member = $wpdb->get_row( $sql );
             

             if ($member) {
                $memarr = array("jot_grpmemid" => $member->jot_grpmemid, "jot_grpmemname" => $member->jot_grpmemname, "jot_grpmemnum" => $member->jot_grpmemnum );
             } 
             return apply_filters('jot_get_member_from_num',$memarr);
     }
     
     /*
      *
      * Get number for given member id      
      *
      */
      public function get_membernum($memid){
             
             global $wpdb;
             $memnum = "";
                          
             if (!empty($memid)) {
                          $table = $wpdb->prefix."jot_groupmembers";
                          $sql = " SELECT  jot_grpmemnum  " .
                                " FROM " . $table  .
                                " WHERE jot_grpmemid = " . $memid ;
                          
                          $member = $wpdb->get_row( $sql );
                          $memnum = isset($member->jot_grpmemnum) ? $member->jot_grpmemnum : "";
             }
             
             return $memnum;
     }    
    
    
     public function get_groupmembers_only($groupid) {
        
             //Get members from the given group
             global $wpdb;
                
             $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
             $tablexref = $wpdb->prefix."jot_groupmemxref";   // b

             $sql = " SELECT  jot_grpmemname, jot_grpmemnum, a.jot_grpmemid " .
                   " FROM " . $tablegrpmem .  " a," . $tablexref . " b " . 
		   " WHERE b.jot_grpid = %d " . 
                   " AND a.jot_grpmemid = b.jot_grpmemid " .
                   " ORDER BY 1";
            
             $sqlprep = $wpdb->prepare($sql, $groupid);
             $memlist = $wpdb->get_results( $sqlprep );             
             
             return apply_filters('jot_get_groupmembers_only',$memlist);
            
    }
    
    public function member_in_group($jot_grpid, $jot_grpmemid) {
        
             //Get members from the given group
             global $wpdb;
                        
             $tablexref = $wpdb->prefix."jot_groupmemxref";   // a

             $sql = " SELECT  1 " .
                   " FROM "  . $tablexref . " a " . 
		   " WHERE a.jot_grpid = %d " . 
                   " AND a.jot_grpmemid = %d " .
                   " ORDER BY 1";
            
             $sqlprep = $wpdb->prepare($sql, $jot_grpid, $jot_grpmemid  );
             
             $memlist = $wpdb->get_row( $sqlprep );             
             
             if ($memlist) {
                return true;
             } else {
                return false;
             }
             
            
    }
    
    public function get_all_names() {
             global $wpdb;
             
             $tablegrpmem = $wpdb->prefix."jot_groupmembers";            

             $sql = " SELECT DISTINCT  jot_grpmemnum, jot_grpmemname" .
                    " FROM " . $tablegrpmem .
                    " WHERE jot_grpmemnum <> ''";
                        
             $memlist = $wpdb->get_results( $sql );
             
             foreach ( $memlist as $member ) {
                  $nameslist[$member->jot_grpmemnum] = isset($member->jot_grpmemname) ? $member->jot_grpmemname : "";          
             }
             
             return apply_filters('jot_get_all_names',$nameslist);
             
    }
    
    public function get_jot_groupname($jot_groupid) {
			
			//Get group name
			global $wpdb;
							    
			$table = $wpdb->prefix."jot_groups";
			$sql = " SELECT  jot_groupname" .
			       " FROM " . $table  .
			       " WHERE jot_groupid = %d";
			        
		       
		        $sqlprep = $wpdb->prepare($sql,$jot_groupid);
			$grp = $wpdb->get_row( $sqlprep );         
			
			if (!empty($grp->jot_groupname)) {
			    $grpname = $grp->jot_groupname;
			} else {
			    $grpname = "";
			}
			
			return apply_filters('jot_get_jot_groupname',$grpname);
			
    }
    
    
    /*
     *
     * Get group by name 
     *
     */
    public function get_group_byname($jot_groupname) {
			
             //Get group name
             global $wpdb;
                                                 
             $table = $wpdb->prefix."jot_groups";
             $sql = " SELECT  jot_groupid " .
                    " FROM " . $table  .
                    " WHERE jot_groupname = %s " .
                    " LIMIT 1 ";
                     
            
             $sqlprep = $wpdb->prepare($sql,$jot_groupname);
             $grp = $wpdb->get_row( $sqlprep );         
             
             if (isset($grp->jot_groupid)) {
                 $groupid = $grp->jot_groupid;
             } else {
                 $groupid = "";
             }
             
             return apply_filters('jot_get_group_byname',$groupid);
			
    }
    
    
    
    public function get_groups_by_number($number) {
             
             global $wpdb;
             
             if (!is_array($number)) {
		$number = array($number);
	     }
	    
             if (!empty($number)) {		
                $grpclause = " AND b.jot_grpmemnum IN ('" . implode( "','", $number ) . "')";
             } else {
                $grpclause = "";
             }
             
             $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
             $tablexref   = $wpdb->prefix."jot_groupmemxref"; // b
             $tablegroups = $wpdb->prefix."jot_groups";       //c 
             
             $sql = "SELECT c.jot_groupid,c.jot_groupname,c.jot_groupdesc,c.jot_groupoptout,c.jot_groupopttxt,c.jot_groupallowdups,c.jot_groupautosub " . 
                    " FROM " . $tablexref .   " a, " . 
                               $tablegrpmem . " b, " .
                               $tablegroups . " c  " .                                                        
                    " WHERE a.jot_grpmemid = b.jot_grpmemid " .
                    " AND a.jot_grpid = c.jot_groupid " .
                    $grpclause;
             
             //$sqlprep = $wpdb->prepare($sql,$number);
             $grps = $wpdb->get_results( $sql);
                         
             return apply_filters('jot_get_groups_by_number',$grps);
             
    }
    
    public function send_to_group($groupid, $messagebody,$sendgroup_args = null) {
             
             
             // Set processing limit to 1000 seconds
	     // Which should be enough for approximately 1000 messages
	     // assuming there are no other limits imposed by the web server.
	     set_time_limit(1000);
             
             //Send a message to the given group
             global $wpdb;
             $response_msgsent = 0;
             $response_totalmsg = 0;
             $senderid = "";
            
             $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
             $tablexref = $wpdb->prefix."jot_groupmemxref";   // b

             $sql = " SELECT  a.jot_grpmemid,jot_grpmemname, jot_grpmemnum " .
                   " FROM " . $tablegrpmem .  " a," . $tablexref . " b " . 
		   " WHERE b.jot_grpid = %d " . 
                   " AND a.jot_grpmemid = b.jot_grpmemid " .
                   " ORDER BY 1";
            
             $sqlprep = $wpdb->prepare($sql, $groupid);
             $memlist = $wpdb->get_results( $sqlprep );
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Admin function - send to group :" .  $groupid . " " . $messagebody . " " . $sqlprep ); 
            
            
             $memlist = apply_filters('jot_send_to_group_memberlist',$memlist, $groupid);
            
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Memlist : " . print_r($memlist,true));
                        
             $response_totalmsg = sizeof($memlist);
             foreach ( $memlist as $member ) 
             {
                 $memberobj = $this->get_member($member->jot_grpmemid);
                
                 $detagged_message = $this->get_replace_tags($messagebody,$memberobj,$groupid);
                 
                 if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Get member : " . $member->jot_grpmemid . " >>" . print_r($memberobj,true)  ); 
                 
                 // Check if group number has been set
                 $group_fromnumber = Joy_Of_Text_Plugin()->options->get_group_fromnumber($groupid);
                 
                 // Use default Sender ID if set
                 $senderid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-smssenderid');   
                                                  
                 $message_error=Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($member->jot_grpmemnum,$detagged_message,$senderid,$group_fromnumber);
                 
                 if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Message sent to : " . $member->jot_grpmemid . " >>" . print_r($message_error,true)  ); 
                                 
                 Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",$member->jot_grpmemid,$message_error,$sendgroup_args);
                 if ($message_error['send_message_errorcode'] == 0) {
                          $response_msgsent++;
                 }
             
             }
             
             // If sender ID has been set, then can't reply to the sending number
             if ($senderid != "") {
                 $message_reply = "";
             } else {
                 $message_reply = sprintf(__("Message sent to members of group %s", "jot-plugin"), $groupid);
             }
             
             $response = array ('message'  => $message_reply,
                                'msgsent'  => $response_msgsent,
                                'totalmsg' => $response_totalmsg
                              );
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Admin function - response :" .  print_r($response,true) ); 
             return apply_filters('jot_sent_to_group',$response);
            
            
    }
    
        
    /*
     *
     * Check if the message contains the subscriptions manager message.
     * If so send a message containing all of the groups this number is a member of.
     *
     * INPUT :
     *      Phone number                            - string
     *      Keyword contained in message            - string
     *      
     */
    public function subscription_manager_message($number, $keyword) {

             $subcommand = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbmanagesubs');
             
             if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"subcommand >" . $subcommand . " keyword>" . $keyword);
             
             
             if (trim(strtolower($subcommand)) != trim(strtolower($keyword)) || $subcommand == "") {
                 $submgr = false;
                 return $submgr;
             } else {
                 $submgr = true;
             }
             
             // Get all groups for the given number
             $groups = $this->get_groups_by_number($number);
             
             // Construct response.
             if (!$groups) {
                 $response = __("You don't appear to have subscribed to any groups.", "jot-plugin");
             } else {
                $response = __("You are subscribed to the follow groups : ","jot-plugin")  . "\n";
                foreach ($groups as $group) {
                    $groupinvite = Joy_Of_Text_Plugin()->settings->get_group_invite($group->jot_groupid);
                    $opt_in = isset($groupinvite->jot_grpinvaddkeyw) ? $groupinvite->jot_grpinvaddkeyw : "";
                    
                    
                    $next_group  = __("Name: ", "jot-plugin") .  $group->jot_groupdesc . "\n";
                    $next_group .= __("Opt-in: ", "jot-plugin") . $opt_in  . "\n";
                    $next_group .= __("Opt-out: ", "jot-plugin") . $group->jot_groupoptout  . "\n";
                    $next_group .= "\n";
                    
                    // Limit to 2x160 messages
                   if (strlen($next_group) + strlen($response) < 625) {
                          $response .= $next_group;
                   } else {
                         $truncated = true;                 
                   }
                }
             }
            
             // Limit response to two messages
             // Providers seem to hold back longer messages
             if ($truncated) {
               $response .=  __("(Msg Truncated)","jot-plugin");                                       
             }
                          
             // Send message containing all groups.
             $member = $this->get_member_from_num($number);
             $jot_grpmemid = isset($member['jot_grpmemid']) ? $member['jot_grpmemid'] : "";    
                    
             $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($number, $response);                 
             $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                   'jot_messsubtype' => 'SC'
                                       );
             Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",$jot_grpmemid,$message_error,$collate_args);
             
             return $submgr;
    }
    
    
    public function group_opt_out($number, $keyword) {
              
             $optout = false;
             $keyword = strtolower ($keyword);
             
             //Get group list from database.
             global $wpdb;
             $table = $wpdb->prefix."jot_groups";
             
             if (strcasecmp($keyword, "all") == 0 || strcasecmp($keyword, "leaveall") == 0) {
                 $sql = " SELECT jot_groupid, jot_groupopttxt, jot_groupname, jot_groupdesc " .
                        " FROM   " . $table    ;
                 $groups =$wpdb->get_results( $sql );
             } else {
                 $sql = " SELECT jot_groupid, jot_groupopttxt, jot_groupname, jot_groupdesc " .
                        " FROM   " . $table . 
                        " WHERE  LOWER(REPLACE(jot_groupoptout, ' ', '')) = %s ";
                 $sqlprep = $wpdb->prepare( $sql, $keyword  );   
                 $groups =$wpdb->get_results( $sqlprep );                          
             }
             
             if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"group_opt_out : " . $number . " >" . $keyword . "< " . $sqlprep);
             
             // Remove member from group   
             foreach ($groups as $group) {
            
                               
                $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
                $tablexref = $wpdb->prefix."jot_groupmemxref";   // b

                 $sql = "DELETE a FROM " . $tablexref .  " AS a " . 
                        " JOIN " . $tablegrpmem . " AS b " .
                        " ON a.jot_grpmemid = b.jot_grpmemid " .
                        " WHERE a.jot_grpid =  %d " .
                        " AND b.jot_grpmemnum = %s ";                        
                        
                
                $sqlprep = $wpdb->prepare( $sql, $group->jot_groupid, $number);     
                $rowsdeleted = $wpdb->query($sqlprep);
                
                // Opt out found
                if ($rowsdeleted > 0) {
                    $optout = true;
                    
                    // Action hook for deleted user                                     
                    $member = $this->get_member_from_num($number);
                    $group_arr = (array) $group;                    
                    do_action('jot_after_group_optout',$member, $group_arr);
                    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"After do_action");
                    $this->send_unsubscribe_notifications($member, $group_arr);
                }
                    
                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Opt Out : " . $keyword .  " Deleting number : " . $number . "/" . $stripnumber . " for group : " . $group->jot_groupid . ". Rows deleted : " . $rowsdeleted. " >" . $group->jot_groupopttxt . "<");
                if (!empty($group->jot_groupopttxt) && $rowsdeleted > 0) {
                    
                    // Use default Sender ID for welcome SMS if set
                    $senderid = "";               
                    $smsdetails = get_option('jot-plugin-smsprovider');
                    if (!empty($smsdetails['jot-smssenderid'])) {
                       $senderid = isset($smsdetails['jot-smssenderid']) ? $smsdetails['jot-smssenderid'] : "";
                    }            
                       
                    $member = $this->get_member_from_num($number);
                    if (isset($member['jot_grpmemid'])) {

                    	  $detagged_message = $this->get_replace_tags($group->jot_groupopttxt,$member,$group->jot_groupid);
                    }	else {
                          $detagged_message = $group->jot_groupopttxt;      
                    }
                    
                    $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($number, $detagged_message,$senderid);                 
                    $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                         'jot_messsubtype' => 'GO'
                                       );
                    $jot_grpmemid = isset($member['jot_grpmemid']) ? $member['jot_grpmemid'] : "";    
                    Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o",$jot_grpmemid,$message_error,$collate_args);
                }
             
             }
              
             return $optout;
    }
    
    public function get_autogroups() {
        
            //Get all groups that have been set as auto subscribe
            global $wpdb;
                                                
            $table = $wpdb->prefix."jot_groups";
            $sql = " SELECT  jot_groupid" .
                   " FROM " . $table  .
                   " WHERE jot_groupautosub = 1" ;   
           
            $grplist = $wpdb->get_results( $sql );         
            
            
            return apply_filters('jot_get_autogroups',$grplist);
            
    }
    
    public function process_messagefields_reset() {
             
             $smsmessage =  get_option('jot-plugin-messages');
             $smsmessage['jot-message-type'] = "jot-sms";
                             
             $smsmessage['jot-message-suffix'] = "";
             $smsmessage['jot-message'] = "";
             $smsmessage['jot-message-audioid'] = "default";
             $smsmessage['jot-message-mms-image'] = "";
             $smsmessage['jot-message-senderid'] = "";                          
                                                 
             update_option('jot-plugin-messages',$smsmessage);
    }
    
    /*
     *  Add number to auto-add groups
     *
     */
    public function add_to_inbound_group($inboundnum, $inboundname = "", $previous_sent_welcome = false) {
              
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__, "******* add_to_inbound_group : " . $inboundnum . " previous welcome > " . $previous_sent_welcome );
                                                 
             global $wpdb;             
             $inbound_groupid = "";
             $sent_welcome = false;
             $detagged_message = "";
             $settings = get_option('jot-plugin-smsprovider');
             
             
             //Get all inbound groups
             $allgroups = $this->get_autogroups();
             
                          
             foreach ($allgroups as $group) {                         
             
                          // Check if this number is already in the group, if not add to group                         
                          if (Joy_Of_Text_Plugin()->options->number_exists($inboundnum, $group->jot_groupid)) {
                                  //Number already in this group.
                                  
                                  if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__, "IN this group " . $group->jot_groupid . "<>" . $inboundnum);
                          } else {
                                       // Has Inboundname been passed in from textus form
                                       if ($inboundname == "") {                                       
                                                    $allnames = Joy_Of_Text_Plugin()->options->process_get_names_for_number($inboundnum);
                                                    
                                                    if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__, "NOT IN this group " . $group->jot_groupid . "<>" . $inboundnum . " allnames " . print_r($allnames, true));
                                                    
                                                    // Use first name returned
                                                    if(count($allnames) > 0) {
                                                      $firstname = $allnames[0];
                                                      $name = isset($firstname->jot_grpmemname) ? $firstname->jot_grpmemname : "";
                                                    } else {
                                                      $name = $inboundnum; 
                                                    }
                                       } else {
                                            $name = $inboundname;
                                       }
                                       
                                       // Add member to inbound group
                                       $addmember = Joy_Of_Text_Plugin()->options->process_add_member($name, $inboundnum, $group->jot_groupid);
                                       if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Added member to group " . print_r($addmember,true));
                                       
                                       if ($addmember['errorcode'] == 0) {
                                            // Send group welcome message if selected and member id is set
                                            if (!$previous_sent_welcome) {
                                                    if (isset($addmember['lastid'])) {
                                                            $msgerr = Joy_Of_Text_Plugin()->options->send_welcome_message($group->jot_groupid,
                                                                                                                $inboundnum,
                                                                                                                $addmember['lastid'],
                                                                                                                false);
                                                            if ($msgerr['send_message_errorcode'] == 0) {
                                                                         $sent_welcome = true;
                                                            }            
                                                            if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Auto Add Send Welcome. Msgerr return >>" . print_r($msgerr,true) . " >> Sent welcome " . $sent_welcome);
                                                    }
                                            }
                                       } else {
                                          $this->log_to_file(__METHOD__, "Auto add failed >> Errorcode>>" . $addmember['errorcode'] . "<>".  $name . "<>" . $inboundnum . "<>" . $group->jot_groupid . "<<");
                                       }
                                       
                                       if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"End of add_to_inbound_group >> " .$name . "<>" . $inboundnum . "<>" .  $inbound_groupid . "<<>>" . $detagged_message . "<<>>" . $group->jot_groupid . "<<");
                                       
                                                                           
                          }
             } // foreach
             return $sent_welcome;             
    }
    
  
    
    public function update_number($enumber) {

             global $wpdb;             ;
           
             //  Strip + and country code
             $stripnumber = "%" . substr($enumber,3);
             $table = $wpdb->prefix."jot_groupmembers";
             $sql = " UPDATE ". $table .
                    " SET jot_grpmemnum = %s" .
                    " WHERE jot_grpmemnum LIKE %s " ;
                
             $sqlprep = $wpdb->prepare( $sql, $enumber, $stripnumber);
             $rowsdeleted = $wpdb->query($sqlprep);
    }
    
    public function keyword_subscribe_to_group($fromnumber,$inmessage,$previous_sent_welcome = false)  {
             
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"******* keyword subscribe >" . $fromnumber . " Sent Welome >" . $previous_sent_welcome );
            
             global $wpdb;
             $sent_welcome = false;
             $groupinvite = "";
              
             // Strip spaces 
             $message = strtolower (preg_replace('/\s+/', '', trim($inmessage)));   
             
             // Single word - message could be a keyword
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__, "single > " . $message);
             $table = $wpdb->prefix."jot_groupinvites";
             $sql = " SELECT jot_grpid, jot_grpinvretchk, jot_grpinvaddkeyw" .
                    " FROM " . $table .
                    " WHERE LOWER(REPLACE(jot_grpinvaddkeyw,' ','')) = %s ";
             
             $sqlprep = $wpdb->prepare($sql, $message);
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"SQLprep " . $sqlprep);
             $groupinvites = $wpdb->get_results( $sqlprep );
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Group invites " . print_r($groupinvites,true));
             
             foreach ($groupinvites as $invite) {
                      
                $allnames = Joy_Of_Text_Plugin()->options->process_get_names_for_number($fromnumber);
                
                if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Names for given number : " . print_r($allnames,true));
                                   
                // Use first name returned
                if(count($allnames) > 0) {                    
                      $firstname = $allnames[0];
                      $name = $firstname->jot_grpmemname;
                } else {
                      $name = $fromnumber;                                         
                }
                
                // Add member to inbound group
                $addmember = Joy_Of_Text_Plugin()->options->process_add_member($name, $fromnumber, $invite->jot_grpid);
                if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Add member >>" . print_r($addmember,true));
                
                // Send welcome message if required
                // If welcome sent already by auto-adds, then don't send again.
                if (!$previous_sent_welcome) {
                      if ($invite->jot_grpinvretchk && $addmember['errorcode'] == 0) {
                         $member = $this->get_member_from_num($fromnumber);
                         if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Get Member Details>>" . print_r($member,true));
                        
                         if (isset($member['jot_grpmemid'])) {
                            $msgerr = Joy_Of_Text_Plugin()->options->send_welcome_message($invite->jot_grpid, $fromnumber, $member['jot_grpmemid']);
                            if ($msgerr['send_message_errorcode'] == 0) {
                               $sent_welcome = true;
                            }
                            if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Keyword Subscribe to group. Msgerr return >>" . print_r($msgerr,true)  . " >> Sent welcome " . $sent_welcome);
                         }
                      }
                }
                // Already a member of the group?
                if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"Addmember code >>" . $addmember['errorcode']);
                if ($addmember['errorcode'] == 3) {
                      $groupinvite_alreadysub_message = Joy_Of_Text_Plugin()->settings->get_groupmeta($invite->jot_grpid,'jot_grpinvalreadysub');
                      if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"message >>" . $groupinvite_alreadysub_message);
                      if ($groupinvite_alreadysub_message != "") {
                          
                          // Get member details         
                          $member = $this->get_member_from_num($fromnumber);
                          
                          $groupinvite_alreadysub_message = $this->get_replace_tags($groupinvite_alreadysub_message,$member,$invite->jot_grpid);
                          
                          // Send already subscribed message.
                          $msgerr = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($fromnumber, $groupinvite_alreadysub_message);
                          $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                                'jot_messsubtype' => 'AS'
                                   );
                          Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o","",$msgerr,$collate_args);
                      }
                      
                }
             }
             
                 
             
             if (Joy_Of_Text_Plugin()->debug) $this->log_to_file(__METHOD__,"*** END *** sent_welcome > " . $sent_welcome);
             return $sent_welcome;             
    }
    
    public function strip_non_utf8($message) {
             
          //Remove non-UTF8 characters
          //reject overly long 2 byte sequences, as well as characters above U+10000 and replace with no-string
          $message = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
              '|[\x00-\x7F][\x80-\xBF]+'.
              '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
              '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
              '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
              '', $message );
              
          //reject overly long 3 byte sequences and UTF-16 surrogates and replace with no-string
          $message = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
              '|\xED[\xA0-\xBF][\x80-\xBF]/S','', $message );
             
          return $message;   
    }
    
    public function send_unsubscribe_notifications($member, $group) {
             
             if (!$member) {
                 return ;
             }
             
             if (!$group) {
                 return;      
             } else {
                $jot_groupid = isset($group['jot_groupid']) ? $group['jot_groupid'] : "";
             }
             
             $jot_inbunsubchk           = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbunsubchk');
             $jot_inbunsub_emailsubject = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbunsub-emailsubject');   
             $jot_inbunsubmsg           = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbunsubmsg');
             $jot_inbsmschk             = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbsmschk');
             $jot_grpmemnum             = isset($member['jot_grpmemnum']) ? $member['jot_grpmemnum'] : "";

             
             if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Subchk " .  $jot_inbunsubchk) ;
             if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Subject " .  $jot_inbunsub_emailsubject);
             if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Message " .  $jot_inbunsubmsg);
             if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Number " . $jot_grpmemnum);
             
             if ($jot_grpmemnum != "") {
                          if ($jot_inbunsubchk == 'true') {
                                       // Send email notification of unsubscription
                                       
                                       // Get admin email address
                                       $jot_inbemail = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbemail');
                                     
                                       if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Email notification check :" . $jot_inbemail); 
                                       
                                       // Get notification message
                                       if ($jot_inbunsubmsg != "") {
                                                    $notification_message = $jot_inbunsubmsg;
                                                    $notification_message = $this->get_replace_tags($notification_message,$member,$jot_groupid);
                                           } else {
                                                    $notification_message = sprintf(__("%s has unsubscribed","jot-plugin"),$jot_grpmemnum);
                                       }
                                       
                                       // Has an email address by entered
                                       if ( !empty($jot_inbemail) ) {
                                           
                                           if ($jot_inbunsub_emailsubject != "") {
                                               $subject = $jot_inbunsub_emailsubject;     
                                           } else {
                                               $subject = __('%number% has unsubscribed.',"jot-plugin");
                                           }
                                           $subject = $this->get_replace_tags($subject,$member,$jot_groupid);
                                          
                                           $this->send_unsubscription_email_notification($subject, $notification_message);
                                           
                                       }
                                       
                                       // Has an admin group been selected                                   
                                       $jot_inbnotgroup = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbnotgroup');
                                       if ( !is_null($jot_inbnotgroup) ) {                                           
                                           // Subtype = US- group send.
                                           $collate_args = array('jot_batchid' => $batchid,  
                                                                     'jot_messsubtype' => 'US'
                                                                    );                                          
                                           $notif_msgerr = Joy_Of_Text_Plugin()->messenger->send_to_group($jot_inbnotgroup,$notification_message,$collate_args);                                        
                                           if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>> Sending unsub notification " . print_r($notif_msgerr,true));
                                       }
                                       
                          } else {
                               // Unsubscription notifications not selected   
                          }
             }             
             
    }    
       
    public function send_unsubscription_email_notification($subject, $notification_message) {
            
            $to = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbemail');
            
            if ($notification_message == "") {
                return;
            }            
            
            
            // From header
            $fromtext = "";
            $fromtext = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-inbsmssuffix');
            if (empty($fromtext)) {
                $fromtext = __("JOT plugin","jot-plugin");
            }
            $headers[] = 'From: "' . $fromtext . '" <' . get_option('admin_email') . '>';            
          
            Joy_Of_Text_Plugin()->currentsmsprovider->send_email( $to, $subject, $notification_message, $headers );
            
    }
    
    
       
} // end class
 
