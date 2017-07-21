<?php
/**
* Joy_Of_Text Twilio. Class for Twilio API functions
*
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_Smsprovider {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
    function __construct() {
 
        add_filter( 'jot_get_settings_fields', array($this,'add_provider_fields'),10,2 );
           
    } // end constructor
 
    private static $_instance = null;
        
        public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
        } // End instance()

    /**
    * Get numbers from Twilio
    */
    public function getPhoneNumbers($one_number = null) {
            
         
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
                                   
            try {                     
                
                if (isset($one_number)) {
                   $data = array("PhoneNumber" => '"' . $one_number .'"'); 
                } else {
                   $data = array();
                }
                $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/IncomingPhoneNumbers.json";
                $jot_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'get');
               
                $allnumbers['default'] = __("Select a number","jot-plugin");
                $numbers_json = json_decode($jot_response);
                
                if (isset($numbers_json)) {
                    if (isset($numbers_json->code)) {
                        // Error occurred
                         $errormessage = sprintf( __('A Twilio error occurred. "%s %s". Check your Twilio credentials.', 'jot-plugin'), $numbers_json->code, $numbers_json->message );
                         return array('message_code'=>$numbers_json->code, 'message_text'=> $errormessage, 'all_numbers'=>$allnumbers);
                    } else {                    
                        foreach ($numbers_json->incoming_phone_numbers as $number) {
                            $allnumbers[$number->phone_number] = $number->phone_number;
                        }                        
                        return array('message_code'=>0, 'message_text'=> __('Success! You are connecting to Twilio.','jot-plugin'), 'all_numbers'=>$allnumbers, 'full_api_response' => $numbers_json);
                    }
                }
                 
            
            }  catch (Exception $e) {
                // Ignore error
            }
    }
    
    public function send_smsmessage($tonumber, $message, $senderid="", $alt_fromnumber = "") {

                         
            $smsprovider = get_option('jot-plugin-smsprovider');
                       
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            
            $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
            $messservsid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservsid-' . $selected_provider);
                       
            // Is Sender ID set? If not use the number
            if ($senderid != "") {
                $fromnumber = $senderid;
            } else {                
                //  SenderID overrides Group number and default Settings number
                if ($alt_fromnumber != "") {
                    // Use group specific number if set
                    $fromnumber = $alt_fromnumber;
                } else {
                    $fromnumber = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-phonenumbers-' . $selected_provider);
                }   
            }
            
            // If Messaging Service SID is set, then send the Messaging Service SID instead of the 'from' number.
            $jot_messservchk = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservchk');           
            
                        
            if ($messservsid != "" && $jot_messservchk) {
                $data = array (
                    'MessagingServiceSid' => $messservsid,            
                    'To' => $tonumber,
                    'Body' => stripcslashes($message)
                );
                $fromnumber = "Messaging Services";
            } else {
                $data = array (
                    'From' => $fromnumber,              
                    'To' => $tonumber,
                    'Body' => stripcslashes($message)
                );
            }
            
            $data = apply_filters('jot_filter_sms_message_data', $data);
            
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"data : " . print_r($data,true));
          
            $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
            $jot_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'post');
            
            // Process response
            $err_json = json_decode($jot_response);
            
            if (isset($err_json->code)) {                    
                if ($err_json->code > 0) {                        
                    $error = array('send_message_type'=>'SMS','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>'','send_message_errorcode'=>$err_json->code, 'send_message_msg'=> $err_json->message,'send_details'=>$jot_response);
                }
            } else {                    
                if ($err_json->error_code != null ){                        
                   $error = array('send_message_type'=>'SMS','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>'','send_message_errorcode'=>$err_json->code, 'send_message_msg'=> $err_json->message,'send_details'=>$jot_response);
                } else {                       
                   $error = array('send_message_type'=>'SMS','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>'','send_message_errorcode'=>'0', 'send_message_msg'=> __('SMS message sent successfully','jot-plugin'), 'send_details'=>$jot_response);
                }
            }
       
        //do_action('jot_after_sms_send',$tonumber, $message, $senderid, $alt_fromnumber, $data, $jot_response, $error);
        
        return $error; 
            
    }
    
    public function send_mmsmessage($tonumber, $message, $mmsimageid, $senderid="", $alt_fromnumber = "") {

                         
            $smsprovider = get_option('jot-plugin-smsprovider');
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            
            $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);            
            $messservsid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservsid-' . $selected_provider);
            
            $attachment_mine = get_post_mime_type($mmsimageid);
            $minearr = explode('/', $attachment_mine);
            $attachment_type = $minearr[0];
            $mediaurl = "";
             
            if ($attachment_type == 'image') {            
                $image_attributes = wp_get_attachment_image_src( $mmsimageid, 'full' ); // returns an array
                $mediaurl =  $image_attributes[0];
            } else {
                $mediaurl = wp_get_attachment_url($mmsimageid);
                                        
            }
            
              
            // Is Sender ID set? If not use the number
            if ($senderid != '') {
                $fromnumber = $senderid;
            } else {
                //  SenderID overrides Group number and default Settings number
                if ($alt_fromnumber != "") {
                    // Use group specific number if set
                    $fromnumber = $alt_fromnumber;
                } else {
                    $fromnumber = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-phonenumbers-' . $selected_provider);
                }   
            }
            
            // If Messaging Service SID is set, then send the Messaging Service SID instead of the 'from' number.
            //$jot_messservchk = isset($smsprovider['jot-messservchk']) ? $smsprovider['jot-messservchk'] : false;            
            $jot_messservchk = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservchk');         

                        
            if ($messservsid != "" && $jot_messservchk) {               
                $data = array (
                    'MessagingServiceSid' => $messservsid, 
                    'To' => $tonumber,
                    'Body' => stripcslashes($message),
                    'MediaUrl' => $mediaurl
                );
                $fromnumber = "Messaging Services";
            } else {
                $data = array (
                    'From' => $fromnumber,
                    'To' => $tonumber,
                    'Body' => stripcslashes($message),
                    'MediaUrl' => $mediaurl
                );
            }
            
            $data = apply_filters('jot_filter_mms_message_data', $data);          
                        
            // log message info to a file
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"== MMS Message :  From:" . $fromnumber. " To:" . $tonumber . " MediaUrl:" . $mediaurl );
                 
            $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
            $jot_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'post');
                
            // Process response
            $err_json = json_decode($jot_response);
                
            if (isset($err_json->code)) {                    
               if ($err_json->code > 0) {                        
                    $error = array('send_message_type'=>'MMS','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>$mmsimageid,'send_message_errorcode'=>$err_json->code, 'send_message_msg'=> $err_json->message,'send_details'=>$jot_response);
                }
            } else {                    
                if ($err_json->error_code != null ){                        
                    $error = array('send_message_type'=>'MMS','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>$mmsimageid,'send_message_errorcode'=>$err_json->code, 'send_message_msg'=> $err_json->message,'send_details'=>$jot_response);
                } else {                       
                    $error = array('send_message_type'=>'MMS','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>$mmsimageid,'send_message_errorcode'=>'0', 'send_message_msg'=> __('MMS message sent successfully','jot-plugin'), 'send_details'=>$jot_response);
                }
            }
            
            //do_action('jot_after_mms_send',$tonumber, $message, $mmsimageid, $senderid, $alt_fromnumber, $data, $jot_response, $error);
           
            return $error; 
            
    }
    
      public function send_callmessage($tonumber, $message, $audioid, $alt_fromnumber = "") {

            $error = 0;
            
            // Save message content for call type
            $messageid = uniqid(rand(), false);
            $error = Joy_Of_Text_Plugin()->messenger->save_call_message($messageid, $tonumber, $message, $audioid);
            
            // If no save error 
            if ($error == 0) {             
                $smsprovider = get_option('jot-plugin-smsprovider');
                $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
                
                $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
                $fromnumber = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-phonenumbers-' . $selected_provider);
                $call_url = get_site_url() . "?messageid=" . $messageid;
                $tonumber = apply_filters('jot_change_call_destination',$tonumber);
                
                // Use group specific number if set
                if ($alt_fromnumber != "") {
                    $fromnumber = $alt_fromnumber;
                }
                
                $data = array (
                       'From' => $fromnumber,
                       'To' => $tonumber,
                       'Url' => $call_url
                );                
                
                /*
                 *
                 * Added if Messaging Services are extended to call message types
                 *
                 *
                // If Messaging Service SID is set, then send the Messaging Service SID instead of the 'from' number.
                $jot_messservchk = isset($smsprovider['jot-messservchk']) ? $smsprovider['jot-messservchk'] : false;
                $messservsid = isset($smsprovider['jot-messservsid-' . $selected_provider]) ? $smsprovider['jot-messservsid-' . $selected_provider] : "";
                
                if ($messservsid != "" && $jot_messservchk) {               
                    $data = array (
                       'MessagingServiceSid' => $messservsid,
                       'To' => $tonumber,
                       'Url' => $call_url
                    );  
                } else {
                    $data = array (
                       'From' => $fromnumber,
                       'To' => $tonumber,
                       'Url' => $call_url
                    );
                }
                */
           
                $data = apply_filters('jot_filter_call_message_data', $data);
                 
                // log message info to a file
                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"== Call Message start :  Messageid:" . $messageid . " Before call. From:" . $fromnumber. " To:" . $tonumber . " Callurl:" . $call_url );
                            
                
                $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Calls.json";
                $jot_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'post');
                                
                // Process response
                $err_json = json_decode($jot_response);
                if (isset($err_json->code)) {                    
                    if ($err_json->code > 0) {                        
                        $error = array('send_message_type'=>'call','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>$audioid,'send_message_errorcode'=>$err_json->code, 'send_message_msg'=> $err_json->message);
                        // log error message to file
                        Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Messageid " . $messageid . " " . print_r($error,true));
                       
                    }
                } else {                    
                    if ($err_json->error_code != null ){                        
                       $error = array('send_message_type'=>'call','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>$audioid,'send_message_errorcode'=>$err_json->code, 'send_message_msg'=> $err_json->message);
                       // log error message to file
                       Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"*** Call Error  - Messageid " . $messageid . " " . print_r($error,true));                  
                    } else {                       
                       $error = array('send_message_type'=>'call','send_message_from_number'=>$fromnumber,'send_message_number'=>$tonumber,'send_message_content' => stripcslashes($message),'send_message_media'=>$audioid,'send_message_errorcode'=>'0', 'send_message_msg'=> __('Voice call message sent successfully','jot-plugin'), 'send_details'=>$jot_response);
                      
                    }
                }
            }
            
            //do_action('jot_after_call_send',$tonumber, $message, $audioid, $alt_fromnumber, $data, $jot_response, $error);
            
            return $error;  
    }
    
    
    public function get_callmessage() {
        global $wpdb;
        if (isset($_GET['messageid'])) {
            $messagecontent = Joy_Of_Text_Plugin()->messenger->get_saved_message($_GET['messageid']);            
        }                     
        
        if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"== Message end :  Messageid:" . $_GET['messageid'] . " Audio:" . $messagecontent['messageaudio']);
        
        $voicesettings = get_option('jot-plugin-smsprovider');
        $voicegender = $voicesettings['jot-voice-gender'];
        $voiceaccent = $voicesettings['jot-voice-accent'];
        
        if (!isset($voicegender)) {
            Joy_Of_Text_Plugin()->settings->set_voice_preference('alice');          
            $voicesettings = get_option('jot-plugin-smsprovider');
            $voicegender = $voicesettings['jot-voice-gender'];           
        }
        
        if (!isset($voiceaccent)) {           
            Joy_Of_Text_Plugin()->settings->set_voiceaccent_preference('en-GB');
            $voicesettings = get_option('jot-plugin-smsprovider');          
            $voiceaccent = $voicesettings['jot-voice-accent'];
        }              
        
        if (!empty($messagecontent)) {                
           $xml  =  '<?xml version="1.0" encoding="UTF-8"?>';
           $xml .=  '<Response>';
           $xml .=  '<Say voice="' . $voicegender . '" language="'. $voiceaccent . '">' .
                    apply_filters('jot_filter_call_message_content',stripslashes($messagecontent['messagecontent'])) .
                    '</Say>';
           if ($messagecontent['messageaudio'] != 'default') {
               $xml .= '<Play>' . wp_get_attachment_url($messagecontent['messageaudio']) . '</Play>';               
           }
           $xml .= '<Pause length="3"></Pause>';
           $xml .= '</Response>';
           $xml = apply_filters('jot_filter_call_xml',$xml,$messagecontent['messagecontent'], $messagecontent['messageaudio'], $messagecontent['messagenum']);
           if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"XML >>>" . $xml . "<<<");   
        
           //header('Content-type: text/xml'); 
           echo $xml;
        }        
        
        Joy_Of_Text_Plugin()->messenger->delete_saved_message($_GET['messageid']);
        die();
    }
    
       
    public function add_provider_fields( $settings_fields,$section ) {
        
        switch ( $section ) {
                case 'smsprovider':                    
                    $settings_fields['jot-accountsid'] = array(
                        'name' => __( 'Twilio Account SID', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',
                        'optional' => true,
                        'description' => __( 'Enter your Account SID number that you received from Twilio.', 'jot-plugin' )
                    );
                    $settings_fields['jot-authsid'] = array(
                        'name' => __( 'Twilio Auth Token', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',
                        'optional' => true,
                        'description' => __( 'Enter your Auth token that you received from Twilio.', 'jot-plugin' )
                    );
                    $settings_fields['jot-messservsid'] = array(
                        'name' => __( 'Messaging Service SID', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',
                        'optional' => true,
                        'description' => __( 'Enter your Twilio Messaging Service SID. (Optional)', 'jot-plugin' )
                    );                    
                    $settings_fields['jot-phonenumbers'] = array(
                        'name' => __( 'Phone Numbers', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',                       
                        'description' => __( 'Select the Twilio number you wish to send your SMS messages from.', 'jot-plugin' )
                    );
                    $settings_fields['jot-messservchk'] = array(
                        'name' => __( 'Use Messaging Service?', 'jot-plugin' ),
                        'label' => __( 'Use Messaging Service?', 'jot-plugin' ),
                        'type' => 'checkbox',
                        'default' => 'false',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',
                        'description' => __( 'If clicked, the number pool you have configured your Twilio Messaging Service will be used.', 'jot-plugin' )
                    );
                    $settings_fields['jot-smsurl'] = array(
                        'name' => __( 'Twilio SMS URL', 'jot-plugin' ),
                        'type' => 'textvalue',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',                        
                        'description' => __( 'Received SMS messages are sent to this URL by Twilio.', 'jot-plugin' )
                    );      
                   
                break;               
        } 
        return $settings_fields;  
    }
    
    public function process_inbound_sms() {
        
               
        $iscommand = false;
        $response = "";
        $response_smsbody = "";
        
        if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Received a message - " . print_r($_REQUEST,true));
        
        // Make sure that Twilio has sent a Message SID
        // Ensures that the inbound URL hasn't been called from a browser
        if (!empty($_REQUEST['MessageSid'])) {
            
            // Add to history            
            $this->collate_inbound_SMS('i');  
                      
            $smsprovider = get_option('jot-plugin-smsprovider');
            $curr_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            
            
            // Handle inbound commands
            // Check if the message is from the admin number or from the selected provider number
            //$providernum = $smsprovider['jot-phonenumbers-' . $curr_provider];
            $providernum = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-phonenumbers-' . $curr_provider);
            
            
            if (Joy_Of_Text_Plugin()->messenger->check_admin_group($_REQUEST['From']) || $_REQUEST['From'] == $providernum) {
               
                //preg_match_all("/\[.*?\]/", $_REQUEST['Body'], $command);
                preg_match_all("/[\/\[].*[\/\]]/", $_REQUEST['Body'], $command);
                $command = array_filter($command);
               
               if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"In command " . print_r($command,true));
               
                // Check for @ commands
                if (empty($command)) {
                   preg_match_all("/^[@#].*/", $_REQUEST['Body'], $command);
                   $command = array_filter($command);
                }
                                
                if (!empty($command)) {
                    $response= Joy_Of_Text_Plugin()->messenger->process_inbound_command($_REQUEST['Body']);
                    $iscommand = true;
                    if (isset($response['message']))
                    {
                        $response_from_command =  $response['message'] ;
                        $response_smsbody = "<Message>" . $response['message'] . "</Message>";
                    }
                };
            }            
             
            //Is this an opt-out SMS?
            $optout = false;
            
            // Take spaces out of message body
            $optout_command = strtolower (preg_replace('/\s+/', '', trim($_REQUEST['Body'])));            
                      
            // Check if this is an opt command
            
            if ($optout_command != "") {
                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"2 Opt out received : >" . $_REQUEST['Body'] . "<>" . $_REQUEST['From'] . "<" );
                $optout = Joy_Of_Text_Plugin()->messenger->group_opt_out($_REQUEST['From'],$optout_command);
                
                // Is this a subscription manager command message?
                if ($optout == false) {
                    $submgr = Joy_Of_Text_Plugin()->messenger->subscription_manager_message($_REQUEST['From'],$optout_command);
                }
                
            }
             
            // If not an opt-out or a sub manager command and not sent by the notifier then
            // Add inbound number to the auto add groups 'Inbound' group.
            // Check if message is a subscription keyword and add to the appropriate group.
            if ($optout == false && $submgr == false) {
                if (!Joy_Of_Text_Plugin()->messenger->match_incoming($_REQUEST['From'], $providernum)) {
                    Joy_Of_Text_Plugin()->messenger->keyword_subscribe_to_group($_REQUEST['From'],$_REQUEST['Body'],false);
                    Joy_Of_Text_Plugin()->messenger->add_to_inbound_group($_REQUEST['From'], "", false);
                    
                    
                }
            }
            
            
            // Route inbound message to phone
            $jot_inbsmschk = isset($smsprovider['jot-inbsmschk']) ? $smsprovider['jot-inbsmschk'] : false;
            $jot_inbnotgroup = isset($smsprovider['jot-inbnotgroup']) ? $smsprovider['jot-inbnotgroup'] : false;
            if ( $jot_inbsmschk == 'true' && !is_null($jot_inbnotgroup) && empty($command) ) {
                $defaultsms = __("You have received a message from ","jot-plugin") . $_REQUEST['From'] . ". " . __("The message was","jot-plugin") . " '" . $_REQUEST['Body'] . "'. " . $smsprovider['jot-inbsmssuffix'];
                $routedmessage = $this->get_routing_message($_REQUEST['From'],$_REQUEST['Body'],$defaultsms);
                            
                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>> Routing SMS to SMS. From " . $_REQUEST['From'] . " to group " . $jot_inbnotgroup  . " Message: " . $routedmessage );
                
                // Subtype = NG - group send.
                $collate_args = array('jot_batchid' => $batchid,  
                                          'jot_messsubtype' => 'NG'
                                         );                                          
                $notif_msgerr = Joy_Of_Text_Plugin()->messenger->send_to_group($jot_inbnotgroup,$routedmessage,$collate_args);                                        
                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>> Sending notification " . print_r($notif_msgerr,true));
                                             
                
            }
            
            // Route inbound message to email
            $jot_inbemail = isset($smsprovider['jot-inbemail']) ? $smsprovider['jot-inbemail'] : "";
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"jot_inbsmschk " . $jot_inbsmschk . "<>" . $jot_inbemail . "<>"); 
            if ( $jot_inbsmschk == 'true' && !empty($jot_inbemail) && empty($command) ) {
                $subject = $this->get_email_subject($_REQUEST['From'],'jot-inbemailsubject',"SMS message received from %number%!");
                $defaultemail = __("You have received a message from ","jot-plugin") . $_REQUEST['From'] . ". " . __("The message was","jot-plugin") . " '" . $_REQUEST['Body'] . "'. " . $smsprovider['jot-inbsmssuffix'];
                $this->send_subscription_email_notification($subject, $_REQUEST['From'],$_REQUEST['Body'], $defaultemail);
                
            }
            
                       
            // Send command response.
            if ($_REQUEST['From'] != $providernum && $response_from_command != "") {
                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Final response - " . $response_smsbody);
                //Send <response> to Twilio
                header("content-type: text/xml");
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                echo "<Response>";
                echo $response_smsbody;
                echo "</Response>";                
            } else {
                if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Final response - Empty response");
                header("content-type: text/xml");
                echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
                echo "<Response/>";     
            }
            
            die();
        
        } else {
            echo __("Joy Of Text Pro Plugin - Think you've taken a wrong turn.", "jot-plugin");           
            die();
        }
    }
    
    
    public function collate_inbound_SMS($direction) {
           
           $bodytext = sanitize_text_field ($_REQUEST['Body']);
           
           if (!empty($bodytext)) {
            
                // Remove command strings
                //$strippedbodytext = Joy_Of_Text_Plugin()->messenger->removecommands($bodytext);
                $jot_SmsStatus   = isset($_REQUEST['SmsStatus'])  ? $_REQUEST['SmsStatus']  : "";
                $jot_MessageSid  = isset($_REQUEST['MessageSid']) ? $_REQUEST['MessageSid'] : "";
                $jot_From        = isset($_REQUEST['From'])       ? $_REQUEST['From']       : "";
                $jot_To          = isset($_REQUEST['To'])         ? $_REQUEST['To']         : "";
                
                $histdata = array(
                             'jot_histsid'           => sanitize_text_field ($jot_MessageSid),
                             'jot_histdir'           => sanitize_text_field ($direction),
                             'jot_histmemid'         => 9999999,
                             'jot_histfrom'          => sanitize_text_field ($jot_From),
                             'jot_histto'            => sanitize_text_field ($jot_To),
                             'jot_histprovider'      => sanitize_text_field (Joy_Of_Text_Plugin()->currentsmsprovidername),
                             'jot_histmesscontent'   => $bodytext,
                             'jot_histmesstype'      => sanitize_text_field ('S'),
                             'jot_histmesssubtype'   => 'RC',
                             'jot_histstatus'        => sanitize_text_field ($jot_SmsStatus),
                             'jot_histprice'         => 0,
                             'jot_histmedia'         => '',                                                
                             'jot_histts'            => current_time('mysql', 0)                                 
                             );
                
	        $histdata = apply_filters('jot_filter_inbound_message_history_before_save',$histdata);
                	    
            } else {
                Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Inbound SMS from " . $jot_From . " had empty message content after sanitising.");
           }
           
           // If not a command sent by the notifier
           if ($jot_From != $jot_To) {
              $error = Joy_Of_Text_Plugin()->messenger->log_to_history($histdata);
           }
           
           return $error;
           
    }
    
    
   
    
    public function collate_outbound_SMS($direction, $jotmemid, $message_error,$collation_args = null) {
        
           /*
            * Message subtype codes:
            * 
            *	AA - Auto group
            *	AS - Already subscribed
            *	CA - Call message
            *   CC - Confirmation Code
            *	CG - Command group
            *	CM - Command member
            *	CS - Command single
            *	GO - Group opt-out
            *	KM - Kiosk message
            *	NG - Notification Group
            *	QM - Queue message
            *	RC - Received Message
            *	RM - Routed message
            *	SC - Subscription Command
            *	SM - Scheduled message
            *	SN - Subscriber	Notification
            *	TU - Text Us
            *	US - Unsubscription Notification
            *	WA - Woocommerce Admin
            *	WC - Woocommerce Customer
            *	WM - Welcome message
            *
            */
           
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Collate Outbound SMS>>" . print_r($message_error,true) . " args>>" . print_r($collation_args,true));
           
            $provider_return = json_decode($message_error['send_details'],true);
            $messtype = substr($message_error['send_message_type'],0,1);
            
            $messmedia       = isset($message_error['send_message_media']) ? $message_error['send_message_media'] : "";
            $sid             = isset($provider_return['sid'])            ? $provider_return['sid']                : "";
            $from            = isset($provider_return['from'])           ? $provider_return['from']               : "";
            $to              = isset($provider_return['to'])             ? $provider_return['to']                 : "";
            $body            = isset($provider_return['body'])           ? $provider_return['body']               : "";
            $price           = isset($provider_return['price'])          ? $provider_return['price']              : 0;
            $jot_MessServSID = isset($provider_return['messaging_service_sid']) ? $provider_return['messaging_service_sid'] : "";
            
            $batchid     = isset($collation_args['jot_batchid']) ? $collation_args['jot_batchid'] : "";
            $messsubtype = isset($collation_args['jot_messsubtype']) ? $collation_args['jot_messsubtype'] : "";
            $errorcode   = isset($message_error['send_message_errorcode']) ? $message_error['send_message_errorcode'] : -1;
               
            // Is the Messaging Service number pool being used?
            //$jot_messservchk = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservchk');
            $jot_messservchk = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservchk');            

              
            if ($jot_messservchk == true) {
                    if ($jot_MessServSID != "") {                     
                       $from = __("Number Pool","jot-plugin");
                    } else {                    
                       $from = __("Not found.","jot-plugin"); 
                    }
            } else {
                    if ($from == "") {
                       $from = isset($message_error['send_message_from_number']) ? $message_error['send_message_from_number'] : __("Not found..","jot-plugin");
                    }
                    if ($to == "") {
                        $to = isset($message_error['send_message_number']) ? $message_error['send_message_number'] : __("Not found...","jot-plugin");
                    } 
                    if ($body == "") {
                        $body = isset($message_error['send_message_content']) ? $message_error['send_message_content'] : "";
                    }
            }               
            
            if ($message_error['send_message_errorcode'] != 0) {
                $status = $message_error['send_message_errorcode'];
            } else { 
                $status = isset($provider_return['status'])        ? $provider_return['status']        : "";
            }
            
            $histdata  = array(
                        'jot_histsid'           => sanitize_text_field ($sid),
                        'jot_histdir'           => sanitize_text_field ($direction),
                        'jot_histmemid'         => $jotmemid,
                        'jot_histfrom'          => sanitize_text_field ($from),
                        'jot_histto'            => sanitize_text_field ($to),
                        'jot_histprovider'      => sanitize_text_field (Joy_Of_Text_Plugin()->currentsmsprovidername),
                        'jot_histmesscontent'   => sanitize_text_field ($body),
			'jot_histmesstype'      => sanitize_text_field ($messtype),                        
			'jot_histstatus'        => sanitize_text_field ($status),
                        'jot_histprice'         => $price,
                        'jot_histmedia'         => $messmedia,
                        'jot_histbatchid'       => $batchid,
                        'jot_histmesssubtype'   => $messsubtype,
                        'jot_histerrcode'       => $errorcode,
                        'jot_histts'            => current_time('mysql', 0)    
                        );
           
            $histdata = apply_filters('jot_filter_outbound_message_history_before_save',$histdata,$jotmemid, $message_error, $collation_args);
            
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Hist data >" . print_r($histdata,true));
           
            $error = Joy_Of_Text_Plugin()->messenger->log_to_history($histdata);
            return $error;
    }
    
    public function collate_outbound_call($direction, $jotmemid, $message_body, $message_error, $collation_args = null) {
           
            $provider_return = json_decode($message_error['send_details'],true);
            $messtype = substr($message_error['send_message_type'],0,1);
           
           
            $messmedia = isset($message_error['send_message_media']) ? $message_error['send_message_media'] : "";
            $sid       = isset($provider_return['sid'])            ? $provider_return['sid']            : "";
            $from      = isset($provider_return['from'])           ? $provider_return['from']           : "";
            $to        = isset($provider_return['to'])             ? $provider_return['to']             : "";          
            $price     = isset($provider_return['price'])          ? $provider_return['price']          : 0;
            $jot_MessServSID = isset($provider_return['messaging_service_sid']) ? $provider_return['messaging_service_sid'] : "";
           
            $batchid     = isset($collation_args['jot_batchid']) ? $collation_args['jot_batchid'] : "";
            $messsubtype = isset($collation_args['jot_messsubtype']) ? $collation_args['jot_messsubtype'] : "";
            $errorcode   = isset($message_error['send_message_errorcode']) ? $message_error['send_message_errorcode'] : -1;  
                       
            if ($from == "") {
               $from = isset($message_error['send_message_from_number']) ? $message_error['send_message_from_number'] : __("Not found.","jot-plugin");
            } 
            if ($to == "") {
                $to = isset($message_error['send_message_number']) ? $message_error['send_message_number'] : __("Not found.","jot-plugin");
            }              
           
            if ($message_error['send_message_errorcode'] != 0) {
                $status = $message_error['send_message_errorcode'];
            } else { 
                $status = isset($provider_return['status'])        ? $provider_return['status']  : "";
            }
           
            $histdata = array(
                        'jot_histsid'           => sanitize_text_field ($sid),
                        'jot_histdir'           => sanitize_text_field ($direction),
                        'jot_histmemid'         => $jotmemid,
                        'jot_histfrom'          => sanitize_text_field ($from),
                        'jot_histto'            => sanitize_text_field ($to),
                        'jot_histprovider'      => sanitize_text_field (Joy_Of_Text_Plugin()->currentsmsprovidername),
                        'jot_histmesscontent'   => sanitize_text_field ($message_body),
			'jot_histmesstype'      => sanitize_text_field ($messtype),
			'jot_histstatus'        => sanitize_text_field ($status),
                        'jot_histprice'         => $price,
                        'jot_histmedia'         => $messmedia,
                        'jot_histbatchid'       => $batchid,
                        'jot_histmesssubtype'   => $messsubtype,
                        'jot_histerrcode'       => $errorcode,
                        'jot_histts'            => current_time('mysql', 0)    
                        );
           
           $histdata = apply_filters('jot_filter_outbound_call_history_before_save',$histdata, $jotmemid, $message_body, $message_error, $collation_args);
           
           $error = Joy_Of_Text_Plugin()->messenger->log_to_history($histdata,$jotmemid, $message_body, $message_error);
           return $error;
    }
    
    public function checkstatus($jot_histmesstype,$jot_histsid,$saved_status) {            
           
             
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
        
            if ($jot_histmesstype == "S" || $jot_histmesstype == "M") {
                $transient_states = array("accepted","queued", "sending", "sent", "receiving");
                $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages/$jot_histsid.json";
            } else { // get call status
                $transient_states = array("queued", "ringing", "in-progress");
                $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Calls/$jot_histsid.json";
            }
             
            if (in_array($saved_status, $transient_states)) {
                                                
                try {                     
                    
                    $data = array();                    
                    $jot_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'get');
                    
                    $messagestatus_json = json_decode($jot_response);                   
                                        
                    // Update status in history table
                    if ($messagestatus_json->status != $saved_status) {
                        $price = isset($messagestatus_json->price) ? $messagestatus_json->price : 0;
                        $from  = isset($messagestatus_json->from)  ? $messagestatus_json->from  : "No from number";
                        $error_code = isset($messagestatus_json->error_code) ? $messagestatus_json->error_code : 0;
                        Joy_Of_Text_Plugin()->messenger->update_status($jot_histsid,$messagestatus_json->status,$price,$from,$error_code); 
                    }
                    
                    return $messagestatus_json->status;
                
                }  catch (Exception $e) {
                    // Ignore error
                }
            }  else {
                return $saved_status;
            }  
            
    }
    
    public function get_url_config_instructions() {
      $helptext = "";
      if (Joy_Of_Text_Plugin()->currentsmsprovidername != 'default') {
        $helptext = "<br>" . __("You will need to configure your Twilio number with this URL","jot-plugin");
        $helptext .=  "<br>" . __("For details on how to configure this URL in Twilio. Click ", "jot-plugin") ;
        $helptext .= "<a href='https://www.twilio.com/blog/2012/04/get-started-with-twilio-sms-receiving-incoming-sms-quickstart.html' target='_blank'>" . __("here", "jot-plugin") . "</a>.<br>";
      }
      return $helptext;
    }
    
    public function get_routing_message($from, $body, $defaultmsg){
        
        $smsprovider = get_option('jot-plugin-smsprovider');
        
        
        $routing_message = isset($smsprovider['jot-inbsmsrtmsg']) ? $smsprovider['jot-inbsmsrtmsg'] : "";
        
        
        if (empty($routing_message)) {
           $detagged_message = $defaultmsg;
        } else {
           $routing_message = $this->get_member_replace_tags($from, $routing_message);
           $detagged_message = str_replace('%message%', $body, $routing_message) . " " . $smsprovider['jot-inbsmssuffix'];  
        }
        return $detagged_message;
       
    }
    
    public function get_member_replace_tags($from, $message) {
        
        $memarr = Joy_Of_Text_Plugin()->messenger->get_member_from_num($from);
                              
        if (!empty($memarr['jot_grpmemid'])) {
           $message = str_replace('%id%',$memarr['jot_grpmemid'], $message);
        } else {
           $message = str_replace('%id%','%id not found%', $message);
        }
        if (!empty($memarr['jot_grpmemname'])) {
           $message = str_replace('%name%',$memarr['jot_grpmemname'], $message);
           
           $nameparts = Joy_Of_Text_Plugin()->messenger->split_name($memarr['jot_grpmemname']);
           $message = str_replace('%firstname%',$nameparts['firstname'], $message);
           $message = str_replace('%lastname%',$nameparts['lastname'], $message);  
           
        } else {
           $message = str_replace('%name%','%name not found%', $message);
        }
        $message = str_replace('%number%',$from, $message);
        return $message;
        
    }
    
    public function get_email_subject($from,$subject_variable,$default) {
        
        $smsprovider = get_option('jot-plugin-smsprovider');
        $jot_inbemailsubject = isset($smsprovider[$subject_variable]) ? $smsprovider[$subject_variable] : "";
        if ( $jot_inbemailsubject == "" ) {
            $template = __($default,"jot-plugin");
        } else {
            $template = $jot_inbemailsubject;    
        }
        $subject_line = $this->get_member_replace_tags($from, $template);
        
        return $subject_line;
        
    }
    
    public function send_subscription_email_notification($subject, $from, $body, $defaultmsg) {
            $smsprovider = get_option('jot-plugin-smsprovider');
            $to = $smsprovider['jot-inbemail'];
                        
            $message = $this->get_routing_message($from, $body, $defaultmsg);
            
            // from header
            $fromtext = "";
            $fromtext = $smsprovider['jot-inbsmssuffix'];
            if (empty($fromtext)) {
                $fromtext = __("JOT plugin","jot-plugin");
            }
            $headers[] = 'From: "' . $fromtext . '" <' . get_option('admin_email') . '>';
            
            $this->send_email( $to, $subject, $message, $headers );
            
    }
    
    public function send_email($to, $subject, $message, $headers) {           
                                                  
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>> Sending notification. From " . $from . "##Headers:" . $headers . "##Subject : " . $subject . "##to " . $to . "##Message: " . $message);
            
            $send_email = wp_mail( $to, $subject, $message, $headers );
            if (Joy_Of_Text_Plugin()->debug) {
                if ($send_email) {
                    Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"EMAIL SENT>>> Sending notification. From " . $from . "##Headers:" . $headers . "##Subject : " . $subject . "##to " . $to . "##Message: " . $message);                    
                } else {
                    Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"EMAIL NOT SENT>>> Sending notification. From " . $from . "##Headers:" . $headers . "##Subject : " . $subject . "##to " . $to . "##Message: " . $message);                 
                }
            }
    }
    
    /*
    *
    * Confirm that the given number is valid by calling Twilio's lookup function.
    *
    */
    public function verify_number($number, $countrycode = "") {
      
       if (empty($number)) {
         return "";
       }
       
       $intnumber = "";
             
       if ( $countrycode == "") {
            $currcc = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-smscountrycode');
            if ($currcc != "") {
                $countrycode = $currcc;
            } else {
                $countrycode = "US";
            }
       }        
    
       $data = array();
       
       $url = "https://lookups.twilio.com/v1/PhoneNumbers/" . $number . "?CountryCode=" . $countrycode;
       
       $twilio_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'get');
       
       $twilio_json = json_decode($twilio_response);
       //echo ">>" . print_r($twilio_json);
       if (!empty($twilio_json->phone_number)) {
            $intnumber = $twilio_json->phone_number;
         
       }
       return $intnumber;
       //return $twilio_json;        
    }
    
    /**
    * Get account details from Twilio
    */
    public function getAccountDetails() {
            
            
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
                                   
            try {                     
                
                $data = array();
                $url = "https://api.twilio.com/2010-04-01/Accounts/$sid.json";
                $jot_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'get');                
               
                return $jot_response;
                 
            
            }  catch (Exception $e) {
                // Ignore error
            }
    }
    
    /*
     *
     * Get the Voice URL setting for the given Twilio number
     *
     */ 
    public function getSmsUrl($twilio_number) {        
              
        $sms_url_not_set = __("<<Not Set>>","jot-voice");
          
        $twilio_response = $this->getPhoneNumbers();  
          
        $all_numbers_api_response = isset($twilio_response['full_api_response']) ? $twilio_response['full_api_response'] : array();        
        $sms_url = $this->getNumberAttribute($all_numbers_api_response,"sms_url", $sms_url_not_set, $twilio_number);
                 
        return $sms_url;
    }
    
    /*
     *
     * Set the Voice URL setting for the given Twilio number
     *
     */ 
    public function setSmsUrl($twilio_number,$voice_url) {
        
        $return = "";
        $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
        $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
            
        if ($sid != "") {
            $twilio_response = $this->getPhoneNumbers();         
            $twilio_upd_response = array();
              
            $all_numbers_api_response = isset($twilio_response['full_api_response']) ? $twilio_response['full_api_response'] : array();         
            $number_sid = $this->getNumberAttribute($all_numbers_api_response,"sid", "", $twilio_number);
           
           
            // Set Voice URL for this number.
            if ($number_sid != "") {
                $data = array("SmsUrl" => $voice_url);
                
                $api_url = "https://api.twilio.com/2010-04-01/Accounts/$sid/IncomingPhoneNumbers/$number_sid.json";
                $twilio_upd_response = Joy_Of_Text_Plugin()->messenger->call_curl($api_url,$data,'post');
                $twilio_upd_response_json = json_decode($twilio_upd_response);
                $return = isset($twilio_upd_response_json->sms_url) ? $twilio_upd_response_json->sms_url : "";
            }
        }
        
        return $return ;
    }
    
    /*
     *
     * Get the given attribute from the given Twilio number
     *
     */
    public function getNumberAttribute($full_api_response, $attr, $default = "", $twilio_number = "") {
        
        $return_attr = "";
        
        if(isset($full_api_response->incoming_phone_numbers)) {
            foreach ($full_api_response->incoming_phone_numbers as $number) {
                if ($number->phone_number == $twilio_number) {                   
                    $return_attr = isset($number->$attr) ? $number->$attr : $default;
                }
            }
        } else {
            $return_attr = $default;
        }
        
        if ($return_attr == "") {
            $return_attr = $default;
        }
        
        return $return_attr;
    }
    
    
    
    
    
} // end class