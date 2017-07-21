<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    /**
    * Joy_Of_Text_Plugin_Settings Class
    */
    final class Joy_Of_Text_Plugin_Settings {
        
    
        private static $_instance = null;
        /**
        * Main Joy_Of_Text_Plugin_Settings Instance
        *
        * Ensures only one instance of Joy_Of_Text_Plugin_Settings is loaded or can be loaded.
        *
        * @since 1.0.0
        * @static
        * @return Main Joy_Of_Text_Plugin_Settings instance
        */
        public static function instance () {
            if ( is_null( self::$_instance ) )
                self::$_instance = new self();
            return self::$_instance;
        } // End instance()
        
        /**
        * Constructor function.
        */

        public function __construct () {
            
            add_action( 'wp_ajax_process_filter_history', array( $this, 'process_filter_history' ) );
            add_action( 'wp_ajax_process_reset_filters', array( $this, 'process_reset_filters' ) );
            add_action( 'wp_ajax_process_history_deletions', array( $this, 'process_history_deletions' ) );
            add_action( 'wp_ajax_process_refresh_languages', array( $this, 'process_refresh_languages' ) );
	    
	    add_filter( 'paginate_links', array($this,'filter_pagination_url'),10,1);
                                    
        } // End __construct()
        
        
        /**
        * Validate the settings.
        */
        public function validate_settings ( $input, $tab ) {
              
           return $input;
        } // End validate_settings()
        
        
        
        
        
        
        /**
        * Retrieve the settings fields details
        */
        public function get_settings_sections () {
            $settings_sections = array();
            
            // Define section tabs
            $settings_sections['smsprovider'] = array(
                    'tabname'    => __( 'Settings' , 'jot-plugin'),
                    'buttontext' => __( 'Save settings' , 'jot-plugin'),
                    'default'    => false  
            );
            $settings_sections['messages'] = array(
                    'tabname'    => __( 'Messages', 'jot-plugin' ),
                    'buttontext' => __( 'Send Messages', 'jot-plugin' ),
                    'default'    => false                                     
            );
            $settings_sections['group-list'] = array(
                    'tabname'    => __( 'Group Manager', 'jot-plugin' ),
                    'buttontext' => __( 'Add Group', 'jot-plugin' ),                    
                    'default'    => true                               
            );
            $settings_sections['message-history'] = array(
                    'tabname'    => __( 'Message History', 'jot-plugin' ),
                    'buttontext' => '',
                    'default'    => false  
            );
            //$settings_sections['scheduler-manager'] = array(
	    //	    'tabname'    => __( 'Schedule Manager', 'jot-plugin' )                    
	    //);        
            
            
                     
            // Don't forget to add fields for the section in the get_settings_fields() function below
            return (array)apply_filters( 'jot-plugin-settings-sections', $settings_sections );
        } // End get_settings_sections()
        
                
        /**
        * Retrieve the settings fields details
        */
        public function get_settings_fields ( $section ) {
            
                      
            if (!isset($subform)) {
                $subform = 'main';
            }
            $settings_fields = array();
            // Declare the default settings fields.
            switch ( $section ) {
                case 'smsprovider':
                    // Get started settings tab
                    $settings_fields['jot-smsproviders'] = array(
                        'name' => __('SMS Providers', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',                        
                        'description' => __( 'Select your SMS provider.', 'jot-plugin' )
                    );
                    $settings_fields['jot-smscountrycode'] = array(
                        'name' => __('Your country code', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',                        
                        'description' => __( 'Select the country you are in, so Twilio can convert your number into an international format.', 'jot-plugin' )
                    );
                    $settings_fields['jot-smsuseacrossnetwork'] = array(
                        'name' => __( 'Use across network?', 'jot-plugin' ),
                        'label' => __( 'Use these credentials across the whole Wordpress multisite network?', 'jot-plugin' ),
                        'type' => 'checkbox',
                        'default' => 'false',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'twiliosettings',
                        'subform' => 'main',
                        'description' => __( 'If clicked, these credentials will be used on all multisite sub-sites', 'jot-plugin' )
                    );                    
                    // Licenses settings tab
                    $settings_fields['jot-eddlicence'] = array(
                        'name' => __('Enter your licence key:', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'licencekeys',
                        'subform' => 'main',                        
                        'description' => __( 'Enter the JOT Pro licence key, this will enable automatic plugin updates.', 'jot-plugin' )
                    );
                    $settings_fields['jot-eddlicencestatus'] = array(
                        'name' => __('Licence Activation status :', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'readonly' => true,
                        'section' => 'smsprovider',
                        'sectiontab'  => 'licencekeys',
                        'subform' => 'main',                        
                        'description' => ''
                    );
                                                           
                    // Notification settings tab
                    $settings_fields['jot-inbsmschk'] = array(
                        'name' => __( 'Inbound SMS notification?', 'jot-plugin' ),
                        'label' => __( 'Inbound SMS notification?', 'jot-plugin' ),
                        'type' => 'checkbox',
                        'default' => 'false',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'subform' => 'main',
                        'description' => __( 'Do you want to be notfied when an SMS is received?', 'jot-plugin' )
                    );
                    $settings_fields['jot-inbsubchk'] = array(
                        'name' => __( 'Subscription notification?', 'jot-plugin' ),
                        'label' => __( 'Subscription notification?', 'jot-plugin' ),
                        'type' => 'checkbox',
                        'default' => 'false',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'subform' => 'main',
                        'description' => __( 'Do you want to be notified when someone subscribes to a group?', 'jot-plugin' )
                    );
		    $settings_fields['jot-inbnotgroup'] = array(
                        'name' => __( 'Group to send notifications to:', 'jot-plugin' ),
                        'type' => 'select',                        
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'subform' => 'main',
                        'description' => __( 'Select the group you want to send the notifications to. (Optional field)', 'jot-plugin' )
                    );
                    $settings_fields['jot-inbemail'] = array(
                        'name' => __( 'Email address to route SMS messages to:', 'jot-plugin' ),
                        'type' => 'text',                        
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'subform' => 'main',
                        'description' => __( 'Enter an email address to send notifications to. (Optional field)', 'jot-plugin' )
                    );
                    $settings_fields['jot-inbemailsubject'] = array(
                        'name' => __( 'Email address subject line:', 'jot-plugin' ),
                        'type' => 'text',                        
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'placeholder' => __("Message received from %firstname% (%number%)","jot-plugin"),
                        'subform' => 'main',
                        'maxlength' => 80,
                        'description' => __( 'Enter the subject line for notification emails. (Optional field)', 'jot-plugin' )
                    );
                    $settings_fields['jot-inbsmsrtmsg'] = array(
                        'name' => __( 'Notification message :', 'jot-plugin' ),
                        'type' => 'textarea',                        
                        'maxlength' => 640,
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'subform' => 'main',                        
                        'placeholder'=> __("e.g. You received a message from %number%. The message was %message%. Their member number is %id%", "jot-plugin"),
                        'description' => __( 'Enter the format of the notification message.(Optional field)', 'jot-plugin' ),
                        'markup' => "<span id='jot-message-count-inbound-notification'></span>"
                    );
                    $settings_fields['jot-inbsmssuffix'] = array(
                        'name' => __( 'Routed SMS suffix:', 'jot-plugin' ),
                        'type' => 'text',                        
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'subform' => 'main',
                        'placeholder'=> __("e.g. (From JOT Plugin)", "jot-plugin"),
                        'description' => __( 'Text to be appended to SMS messages when routed to email or SMS. (Optional field)', 'jot-plugin' )
                    );
		    
		    $settings_fields['jot-inbunsubchk'] = array(
                        'name' => __( 'Unsubscription notification?', 'jot-plugin' ),
                        'label' => __( 'Unsubscription notification?', 'jot-plugin' ),
                        'type' => 'checkbox',
                        'default' => 'false',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'subform' => 'main',
                        'description' => __( 'Do you want to be notified when someone unsubscribes from a group?', 'jot-plugin' )
                    );
		    $settings_fields['jot-inbunsub-emailsubject'] = array(
                        'name' => __( 'Email address subject line:', 'jot-plugin' ),
                        'type' => 'text',                        
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'placeholder' => __("%number% unsubscribed.","jot-plugin"),
                        'subform' => 'main',
                        'maxlength' => 80,
                        'description' => __( 'Enter the subject line for unsubscription emails. (Optional field)', 'jot-plugin' )
                    );
		    $settings_fields['jot-inbunsubmsg'] = array(
                        'name' => __( 'Unsubscription message :', 'jot-plugin' ),
                        'type' => 'textarea',                        
                        'maxlength' => 640,
                        'section' => 'smsprovider',
                        'sectiontab'  => 'notification',
                        'subform' => 'main',                        
                        'placeholder'=> __("e.g. %number% unsubscribed from group %jot_groupid% ", "jot-plugin"),
                        'description' => __( 'Enter the format of the unsubscription message.(Optional field)', 'jot-plugin' ),
                        'markup' => "<span id='jot-message-count-unsub-notification'></span>"
                    );
                    // Inbound settings tab
                    $settings_fields['jot-inbsmsurl'] = array(
                        'name' => __( 'SMS request URL', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'readonly' => true,
                        'section' => 'smsprovider',
                        'sectiontab'  => 'inbound',
                        'subform' => 'main',
                        'description' => __( 'Inbound SMS messages will be routed to this URL. ', 'jot-plugin' ) . (Joy_Of_Text_Plugin()->currentsmsprovidername != 'default'? Joy_Of_Text_Plugin()->currentsmsprovider->get_url_config_instructions() : "" ),
                    );
		    $settings_fields['jot-inbmanagesubs'] = array(
                        'name' => __( 'Manage subscriptions keyword:', 'jot-plugin' ),
                        'type' => 'text',                        
                        'section' => 'smsprovider',
                        'sectiontab'  => 'inbound',
                        'subform' => 'main',
                        'description' => __( 'When members send a text containing this keyword to your Twilio number,<br>they will be sent a list of their current group subscriptions. (Optional field)', 'jot-plugin' )
                    );
		                                                                                    
                    // General settings tab
                    $settings_fields['jot-smssenderid'] = array(
                        'name' => __( 'Enter default Sender ID', 'jot-plugin' ),
                        'type' => 'text',
                        'placeholder' => __("e.g. JOT Plugin","jot-plugin"),
                        'section' => 'smsprovider',
                        'sectiontab'  => 'general',
                        'subform' => 'main',
                        'maxlength' => '11',
                        'description' => __( 'Sender ID lets you send messages with the company or brand name as the sender, rather than your Twilio number.<br>If a default Sender ID is set, it will be used on all outgoing \'welcome\' and \'opt-out\' SMS messages. (Optional field)', 'jot-plugin' )
                    );
                    $settings_fields['jot-defaulttab'] = array(
                        'name' => __('Select default tab :', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',                      
                        'section' => 'smsprovider',
                        'sectiontab'  => 'general',
                        'subform' => 'main',                        
                        'description' => 'Select the tab to open when the plugin starts.'
                    );		    
		    $settings_fields['jot-admingroup'] = array(
			'name' => __( 'Select group:', 'jot-plugin' ),
			'type' => 'select',
			'default' => '',
			'section' => 'smsprovider',
			'sectiontab'  => 'general',
			'description' => __("Select an admin group.<br>Members of this group can issue commands, such as send a message to a group or you can receive notifications (optional)","jot-plugin")
                    );
                    $settings_fields['jot-voice-gender'] = array(
                        'name' => __( 'Select voice', 'jot-plugin' ),
                        'type' => 'radio',
                        'default' => 'woman',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'general',
                        'options' => array('man' => __('Male','jot-plugin'), 'alice' => __('Female','jot-plugin')),
                        'subform' => 'main',
                        'description' => __( 'Select the voice use for text-to-voice calls.', 'jot-plugin' )
                    );
                    $settings_fields['jot-voice-accent'] = array(
                        'name' => __( 'Select voice language', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'smsprovider',
                        'sectiontab'  => 'general',
                        'subform' => 'main',                       
                        'description' => __( 'Select the language for text-to-voice calls.', 'jot-plugin' )
                    );
                    // System Info
                    $settings_fields['jot-systeminfo'] = array(
                        'name' => __( 'System Info :', 'jot-plugin' ),
                        'type' => 'textarea',
                        'default' => '',
                        'readonly' => 'yes',
                        'rows' =>30,
                        'cols' =>100,
                        'wrap' => false,
                        'section' => 'smsprovider',
                        'sectiontab'  => 'systeminfo',
                        'subform' => 'main',                        
                        'description' => __( 'If you raise a support call, please send the information presented above, as this will help with the support query.', 'jot-plugin' )
                    );     
          
          
                break;
                case 'messages':
                    $settings_fields['jot-message-quicksend-number'] = array(
                        'name' => __( 'Enter numbers', 'jot-plugin' ),
                        'type' => 'textarea',
                        'rows' =>2,
                        'cols' =>30,
                        'section' => 'messages',
                        'subform' => 'main',                       
                        'description' => __( 'Enter the phone numbers you wish to send messages to. One number on each line.', 'jot-plugin' )
                    );
                    $tagurl = "<a href='http://www.getcloudsms.com/documentation/joy-text-supported-merge-tags/' target='_blank'>" . __("merge tags","jot-plugin") . "</a>";
                    $tagurl = apply_filters('jot_whitelabel_mergetag_url',$tagurl);
		    
		    $settings_fields['jot-message-usenumber'] = array(
                        'name' => __( 'Select number', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'messages',
                        'subform' => 'main',                        
                        'description' => __( 'Select the Twilio number you want to send the messages from.', 'jot-plugin' )
                    );
		    
		    $settings_fields['jot-message'] = array(
                        'name' => __( 'Enter your message', 'jot-plugin' ),
                        'type' => 'textarea',
                        'default' => '',
                        'maxlength' => 640,
                        'rows' =>5,
                        'cols' =>100,
                        'placeholder' => __("Enter your message","jot-plugin"),
                        'section' => 'messages',
                        'subform' => 'main',
                        'description' => sprintf( __("You can include %s like %%firstname%%, %%lastname%%, %%number%% into your message.","jot-plugin"),$tagurl),
                        'markup' => "<span id='jot-message-count-message'></span>"
                    );
                    $defsite = sprintf( '(from %s)',$_SERVER['SERVER_NAME'] );
                    $settings_fields['jot-message-suffix'] = array(
                        'name' => __( 'Message suffix', 'jot-plugin' ),
                        'type' => 'text',
                        'placeholder' => __("e.g.","jot-plugin") . " " . $defsite,
                        'section' => 'messages',
                        'subform' => 'main',
                        'description' => __( 'Suffix to append to the end of each message.', 'jot-plugin' )
                    );
		    $settings_fields['jot-message-audioid'] = array(
                        'name' => __( 'Audio file', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'messages',
                        'subform' => 'main',                        
                        'description' => __( 'You can choose to play an audit file from your media library, when a voice call is answered', 'jot-plugin' )
                    );
                    $settings_fields['jot-message-type'] = array(
                        'name' => __( 'Send message as', 'jot-plugin' ),
                        'type' => 'radio',
                        'default' => 'jot-sms',
                        'section' => 'messages',
                        'options' => array('jot-sms' => __('SMS','jot-plugin'), 'jot-call' => __('A text-to-voice call/audio file','jot-plugin'), 'jot-mms' => __('MMS','jot-plugin')),
                        'subform' => 'main',
                        'description' => __( 'Send message as an SMS, a text-to-voice call/audio file or MMS.', 'jot-plugin' )
                    );
                    $settings_fields['jot-message-senderid'] = array(
                        'name' => __( 'Enter a Sender ID', 'jot-plugin' ),
                        'type' => 'text',
                        'placeholder' => __("e.g. JOT Plugin","jot-plugin"),
                        'section' => 'messages',
                        'subform' => 'main',
                        'maxlength' => '11',
                        'description' => __( 'Sender ID lets you send messages with the company or brand name as the sender, rather than your Twilio number.', 'jot-plugin' )
                    );
                    $settings_fields['jot-message-removedupes'] = array(
                        'name' => __( 'Remove duplicates ? ', 'jot-plugin' ),
                        'label' => __( 'Remove duplicates ?', 'jot-plugin' ),
                        'default' => 'true',                        
                        'type' => 'checkbox',
                        'section' => 'messages',
                        'subform' => 'main',
                        'description' => __( 'Select if you do not want duplicate messages to be sent to the same number.', 'jot-plugin' )
                    );
                break;
                case 'group-list':
		    // Jump to Group
		    $settings_fields['jot-jumptogroup'] = array(
                        'name' => __( 'Jump to group :', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'group-list',                        
                        'subform' => 'main',                       
                        'description' => ''
                    );		    
                    // Group details fields
                    $settings_fields['jot_groupnameupd'] = array(
                        'name' => __( 'Group name', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 40,
                        'description' => __( 'Enter your group name.', 'jot-plugin' )
                    );
                    $settings_fields['jot_groupdescupd'] = array(
                        'name' => __( 'Group description', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 60,
                        'description' => __( 'Enter your group description.', 'jot-plugin' )
                    );
                    $settings_fields['jot_groupautosub'] = array(
                        'name' => __( '"Auto-add" Group?', 'jot-plugin' ),
			'label' => __( 'Make this an "auto-add" group?', 'jot-plugin' ),
                        'type' => 'checkbox',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'description' => __( 'Automatically add inbound SMS numbers and subscribers using the [jotform] form to this group?', 'jot-plugin' )
                    );
                    $settings_fields['jot_groupoptout'] = array(
                        'name' => __( 'Group Opt-out keyword', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 60,
                        'description' => __( 'Enter an opt-out keyword. Subscribers can send a text message containing your keyword, to opt-out of receiving messages. (Optional field)', 'jot-plugin' )
                    );
                    $tagurl = "<a href='http://www.getcloudsms.com/documentation/joy-text-supported-merge-tags/' target='_blank'>" . __("merge tags","jot-plugin") . "</a>";
                    $tagurl = apply_filters('jot_whitelabel_mergetag_url',$tagurl);
		    
		    $settings_fields['jot_groupopttxt'] = array(
                        'name' => __( 'Group Opt-out reply :', 'jot-plugin' ),
                        'type' => 'textarea',
                        'default' => '',
                        'placeholder' => __("e.g You have been removed from the group. Thank you.","jot-plugin"),
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 160,
                        'description' => sprintf(__( 'Enter the text to be sent when a subscriber opts-out. You can include %s (Optional field)', 'jot-plugin' ),$tagurl),
                        'markup' => "<span id='jot-message-count-optout'></span>"
                    );    
                    // Group invite fields
                    $settings_url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=twiliosettings' target='_blank'>" . __("Messaging-Settings-Twilio Settings","jot-plugin") .  "</a>";
                    $settings_fields['jot_grpinvphonenumber'] = array(
                        'name' => __( 'Phone Numbers', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'group-list',                        
                        'subform' => 'main',                       
                        'description' => sprintf(__( 'Select the number you want to send this group\'s "welcome messages" from.<br>If not selected, the default number set in the %s will be used. (Optional) ', 'jot-plugin' ),$settings_url),
                    );
                    $settings_fields['jot_grpinvcountrycode'] = array(
                        'name' => __('Your country code', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main',           
                        'description' => __( 'Select the country code for the group phone number. (Optional)', 'jot-plugin' )
                    );
                    $settings_fields['jot_grpinvdesc'] = array(
                        'name' => __( 'Text for description field', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => __('Please subscribe for SMS updates ', 'jot-plugin'),
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 60                        
                    );
                    $settings_fields['jot_grpinvnametxt'] = array(
                        'name' => __( 'Text for name field', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => __('Enter your name : ', 'jot-plugin'),
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 40                        
                    );
                    $settings_fields['jot_grpinvnumtxt'] = array(
                        'name' => __( 'Text for phone number field', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => __('Enter your phone number :', 'jot-plugin'),
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 40                        
                    );                   
                    $settings_fields['jot_grpinvretchk'] = array(
                        'name' => __( 'Send welcome message', 'jot-plugin' ),
                        'label' => __( 'Send welcome message when a new subscriber completes the invite form or subscribes using the subscription keyword ?', 'jot-plugin' ),
                        'default' => 'true',                        
                        'type' => 'checkbox',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'description' => __( 'Send a welcome message whenever a subscription request is received.', 'jot-plugin' )
                    );
                    $settings_fields['jot_grpinvwelchk'] = array(
                        'name' => __( 'Send welcome message when:', 'jot-plugin' ),
                        'type' => 'checkbox',
                        'default' => 'jot-add',
                        'section' => 'group-list',
                        'options' => array('jot_add' => __('A member is ADDED into this group','jot-plugin'), 'jot_copy' => __('A member is COPIED into this group','jot-plugin'), 'jot_move' => __('A member is MOVED into this group','jot-plugin')),
                        'subform' => 'main',
                        'description' => __( 'Send a welcome messages when an admin adds members to a group, copies or moves members between groups.', 'jot-plugin' )
                    );
                    $tagurl = "<a href='http://www.getcloudsms.com/documentation/joy-text-supported-merge-tags/' target='_blank'>" . __("merge tags","jot-plugin") . "</a>";
                    $tagurl = apply_filters('jot_whitelabel_mergetag_url',$tagurl);
		    
		    $settings_fields['jot_grpinvrettxt'] = array(
                        'name' => __( 'Enter welcome message', 'jot-plugin' ),
                        'type' => 'textarea',
                        'default' => __("Hello %firstname%. Thank you for subscribing to our group. Send '%jot_optout%' to unsubscribe.", "jot-plugin"),
                        'section' => 'group-list',
                        'placeholder' => __("Welcome to our group. Send '%jot_optout%' to unsubscribe. Check your subscriptions by texting in %jot_submgr%", "jot-plugin"),
                        'subform' => 'main',
                        'maxlength' => 640,                       
                        'description' => sprintf( __("You can include %s like %%firstname%%, %%lastname%%, %%number%% into your welcome message.","jot-plugin"),$tagurl),
                        'markup' => "<span id='jot-message-count-welcome'></span>"
                    );
                    $settings_fields['jot_grpinvformtxt'] = array(
                        'name' => __( 'HTML for your invite form', 'jot-plugin' ),
                        'type' => 'textarea',
                        'default' => '',
			'readonly' => 'yes',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'description' => __( 'Click to generate the HTML for the form. Place it on your site to display the invitation form.', 'jot-plugin' ),
			'markuplink' => "<p><a href='#' class='button' id='jot-generate-invite-html'>" . __("Generate HTML","jot-plugin"). "</a>"
                    );
                    $settings_fields['jot_grpinvredirect'] = array(
                        'name' => __( 'Redirect to:', 'jot-plugin' ),
                        'type' => 'text',
                        'maxlength' => 500,
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'description' => __( 'After a member has successfully subscribed, they can be redirected to another page. Enter the URL of the page to redirect to. (Optional)', 'jot-plugin' )
                    );
                    $settings_fields['jot_grpinvshortcode'] = array(
                        'name' => __( 'Invite form shortcode', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'size' => 60,
                        'section' => 'group-list',
                        'subform' => 'main',
                        'description' => __( 'Alternatively, use this shortcode to create the invitation form.', 'jot-plugin' )
                    );
                    $settings_fields['jot_grpid'] = array(
                        'name' => '',
                        'type' => 'hidden',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main' 
                    );
                    $settings_fields['jot_grpinvaddkeyw'] = array(
                        'name' => __( 'Subscription keyword or phrase', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 60,
                        'description' => __( 'Enter a keyword or phrase. Subscribers who send a message containing this keyword/phrase, will be added to this group.  (Optional field)', 'jot-plugin' )
                    );
		    $settings_fields['jot_grpinvalreadysub'] = array(
                        'name' => __( 'Already subscribed message', 'jot-plugin' ),
                        'type' => 'textarea',
                        'default' => '',
			'placeholder' => 'e.g. Sorry you are already subscribed to this group',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'maxlength' => 640,
                        'description' => __( 'If an existing member of this group attempts to subscribe again, this message will be sent to them.  (Optional field)', 'jot-plugin' ),
                        'markup' => "<span id='jot-message-count-already-subbed'></span>"
		    );
                    $settings_fields['jot_grpinvaudioid'] = array(
                        'name' => __( 'Audio file', 'jot-plugin' ),
                        'type' => 'select',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'main',                        
                        'description' => __( 'You can choose to play an audit file from your media library, when a voice call is answered', 'jot-plugin' )
                    );
                    $settings_fields['jot_grpinvmesstype'] = array(
                        'name' => __( 'Send message as', 'jot-plugin' ),
                        'type' => 'radio',
                        'default' => 'jot-sms',
                        'section' => 'group-list',
                        'options' => array('jot-sms' => __('SMS','jot-plugin'), 'jot-call' => __('A text-to-voice call/audio file','jot-plugin'), 'jot-mms' => __('MMS','jot-plugin')),
                        'subform' => 'main',
                        'description' => __( 'Send welcome message as an SMS, a text-to-voice call/audio file or MMS.', 'jot-plugin' )
                    );
		    $settings_fields['jot_grpinvconfirm'] = array(
                        'name' => __( 'Send confirmation code?', 'jot-plugin' ),
                        'label' => __( 'Send confirmation code when users subscribe using [jotform] form?', 'jot-plugin' ),
                        'default' => 'false',                        
                        'type' => 'checkbox',
                        'section' => 'group-list',
                        'subform' => 'main',
                        'description' => __( 'Select if you want a 4-digit code to be sent in an SMS to the subscriber. The code must be entered to complete the subscription.', 'jot-plugin' )
                    );
                    // Group add fields
                    $settings_fields['jot_groupname'] = array(
                        'name' => __( 'Enter the group name', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'add',
                        'maxlength' => 40,
                        'description' => __( 'Enter your group name.', 'jot-plugin' )
                    );
                    $settings_fields['jot_groupdesc'] = array(
                        'name' => __( 'Enter the group description', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'add',
                        'maxlength' => 60,
                        'description' => __( 'Enter your group description.', 'jot-plugin' )
                    );		    
                    // Bulk member add fields
                    $settings_fields['jot_bulkaddgrp'] = array(
                        'name' => __( 'Add members to group:', 'jot-plugin' ),
                        'type' => 'text',
                        'default' => '',
                        'readonly' => true,
                        'section' => 'group-list',
                        'subform' => 'bulk',
                        'maxlength' => 42                        
                    );
                    $settings_fields['jot_bulkadd'] = array(
                        'name' => __( 'Enter new member details.', 'jot-plugin' ),
                        'type' => 'textarea',
                        'default' => '',
                        'section' => 'group-list',
                        'subform' => 'bulk',                      
                        'rows' =>20,
                        'cols' =>90,
                        'placeholder'=>"Thomas Edison,07904334423,,,,,",
                        'description' => __( 'Enter one member per line, in the format {name},{number},{email},{address},{city},{state},{zipcode}. Only name and number are mandatory. ', 'jot-plugin' )
                    );
                break;
                     
                default:
                   
                    $settings_fields = (array) apply_filters("jot_render_get_extension_fields",$section);
                    
                break;
            }
           
            return (array)apply_filters( 'jot_get_settings_fields', $settings_fields, $section );
        } // End get_settings_fields()
        
       
       /*******************************************************************************************
        *******************************************************************************************
        **                                                                                       **
        **                      R E N D E R  S C R E E N  F U N C T I O N S                      **
        **                                                                                       ** 
        *******************************************************************************************
        *******************************************************************************************/
       
        
        /**
        * Renders page for displaying SMS provider (Since 2.0.19 now all settings)
        *
        * @return string HTML markup for the field.
        */
        public function render_smsprovider_settings ($sections, $tab) {
               
                                           
                // Check licence status
                Joy_Of_Text_Plugin()->options->check_edd_license();
            
                $return_array = array();
                $return_array['message_code'] = '';
                $return_array['message_text'] = '';
               
                if (isset($_GET['section'])) {                 
                   $sectiontab = $_GET['section'];
                } else {
                   $sectiontab = "getstarted";
                }
                
                $html = $this->write_settings_navbar($tab,$sectiontab);
		$html .= $this->render_saved_notice();
                           
                $html .= "<table class=\"jot-formtab form-table\">\n";
                
                switch ( $sectiontab ) {
                    case 'getstarted';
                           $html .= $this->render_getstarted($sections, $tab);
                    break;
                    case 'twiliosettings'; 
                           $ret = $this->render_twiliosettings($sections, $tab);
                           $html .= $ret['html'];
                           $return_array['message_code'] = $ret['message_code'];
                           $return_array['message_text'] = $ret['message_text'];
                    break;
                    case 'licencekeys';
                           $html .= $this->render_licences($sections, $tab);     
                    break;
                    case 'inbound';
                           $html .= $this->render_inbound($sections, $tab);                                
                    break;
                    case 'notification';
                           $html .= $this->render_notifications($sections, $tab);       
                    break;
                    case 'general';
                           $html .= $this->render_general($sections, $tab);     
                    break;
                    case 'systeminfo';
                           $html .= Joy_Of_Text_Plugin()->systeminfo->render_system_info($sections, $tab);     
                    break;
                    default;
                           $html = apply_filters('jot_render_settings_subsection',$html,$sections,$tab);
                    break;
                }  
                
                $html .= "</table>";
    
                $return_array['html'] = $html;
                                       
                return apply_filters( 'jot_render_smsprovider_settings',$return_array);

                    
        } // End render_smsprovider_settings()
        
        public function write_settings_navbar($tab,$insection) {
            
            $sectionurl = admin_url( 'admin.php?page=jot-plugin&tab=' . $tab . '&section=');
            
            $sectionarray = array (
                'getstarted' => __('Get Started','jot-plugin'),
                'twiliosettings' => __('Twilio Settings','jot-plugin'),
                'licencekeys' => __('Licence Keys','jot-plugin'),
		'inbound' => __('Inbound Message Settings','jot-plugin'),
                'notification' => __('Notification Settings','jot-plugin'),
                'general' => __('General Settings','jot-plugin'),
                'systeminfo' => __('System Info','jot-plugin')  
                
            );
            
	    $sectionarray = apply_filters('jot_add_to_settings_navbar',$sectionarray);
	    
            $html =  "<div id='jot-navcontainer'>";
            $html .= "<ul id='jot-navlist'>";
            
            $sectionarray_size = count( $sectionarray );
            $counter = 1;
            foreach ($sectionarray as $key => $value) {
                if ($insection == $key) {
                    $html .= "<li><b>" . $value ."</b></li>";
                } else {
                    $html .= "<li><a href='" . $sectionurl . $key .  "'>" . $value . "</a></li>";                            
                }
                if ($counter < $sectionarray_size) {
                    $html .= "|";
                }
                $counter++;
            }       
            
            $html .= "</ul>";
            $html .= "</div>";
            
            return $html;
            
        }
        
        /**
        * Renders page for displaying SMS providersettings
        *
        * @return string HTML markup for the field.
        */
        public function render_getstarted($sections, $tab) {
            
            $html = "<tr><td>";
            
            $html .= "<ul class='jot-getstarted'>";
            $twilio_url = "<a href='http://www.twilio.com' target='_blank'>" . __("Twilio.com","jot-plugin") . "</a>";
            
            $html .= "<li>" . sprintf( __("Step 1 - Get your account from %s","jot-plugin"), $twilio_url) ;
            
            $html .= "<ul class='jot-getstarted-nested'>";
            $html .= "<li>" . "<span class='getstarted-description'>" . __("To use this plugin you'll need a Twilio account.","jot-plugin") . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("Register an account at %s, purchase a <b>phone number</b> and get your unique <b>'Twilio Account SID'</b> and <b>'Twilio Auth Token'</b>.","jot-plugin"), $twilio_url) . "</span>" . "</li>";
            $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=twiliosettings' target='_blank'>" . __("Messaging-Settings-Twilio Settings","jot-plugin") .  "</a>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("Go to %s and enter your 'Twilio Account SID' and 'Twilio Auth Token' and press the 'Save Settings' button.","jot-plugin"), $url) . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("Your Twilio number(s) should then be displayed, so select the number you want to send your messages from.","jot-plugin") . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("Select the country you are in. The plugin and Twilio need to know this, to check that the member phone numbers you've entered are valid.","jot-plugin") . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("Press the 'Save Settings' button and move to Step 2.","jot-plugin") . "</span>";
            $html .= "</ul>";
            $html .= "</li>";
            
            
            $html .= "<li>" .  __("Step 2 - Activate your licence keys","jot-plugin") ;
            $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=licencekeys' target='_blank'>" . __("Messaging-Settings-Licence Keys","jot-plugin") .  "</a>";
            $html .= "<ul class='jot-getstarted-nested'>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("Go to %s to activate your licence key. This will enable automatic plugin updates through the Wordpress dashboard.","jot-plugin"), $url) . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("To activate the licences, enter your key(s) and press the 'Activate Licence' key.","jot-plugin") . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("Press the 'Save Settings' button and move to Step 3.","jot-plugin") . "</span>" . "</li>";
            $html .= "</ul>";
            $html .= "</li>";
            
            $html .= "<li>" .  __("Step 3 - Add some members to a group","jot-plugin");
            $get_oldest_group = Joy_Of_Text_Plugin()->messenger->get_oldest_groups();
            if (isset($get_oldest_group)) {
                $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=group-list&lastid=" . $get_oldest_group . "&subtab=jottabgroupmembers' target='_blank'>" . __("Group Manager-Member List","jot-plugin") .  "</a>" ;
            } else {
                $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=group-list' target='_blank'>" . __("Group Manager","jot-plugin") .  "</a>" ;
            }
            
            $html .= "<ul class='jot-getstarted-nested'>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("To add members, go to the Group Manager tab, click on a group name, then select the 'Member List' tab. Enter the new member's details and press the 'floppy disk' icon to save.","jot-plugin") . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("For example, click %s and add members to the group.","jot-plugin"), $url) . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("Once you've added some members, move to Step 4.","jot-plugin") . "</span>" . "</li>";
            $html .= "</ul>";
            $html .= "</li>";
            
            $html .= "<li>" .  __("Step 4 - Send some messages","jot-plugin") ;
            $html .= "<ul class='jot-getstarted-nested'>";
            $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=messages' target='_blank'>" . __("Messages","jot-plugin") .  "</a>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("Go to the %s tab, select one or more members, enter the message text and press 'Send your messages'.","jot-plugin"), $url) . "</span>" . "</li>";
            $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=message-history' target='_blank'>" . __("Message History","jot-plugin") .  "</a>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("Click on the %s tab, to see the history of your successful messages.","jot-plugin"), $url) . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("From the Message History tab, click on one of the history items to open the 'virtual phone', allowing you to send messages to the same recipient.","jot-plugin") . "</span>" . "</li>";
            $html .= "</ul>";
            $html .= "</li>";
            
            $html .= "<li>" .  __("Step 5 - Create a form.","jot-plugin") ;
            
            if (isset($get_oldest_group)) {
                $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=group-list&lastid=" . $get_oldest_group . "&subtab=jottabgroupinvite' target='_blank'>" . __("Group Manager-Group Invite","jot-plugin") .  "</a>" ;
            } else {
                $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=group-list' target='_blank'>" . __("Group Manager","jot-plugin") .  "</a>" ;
            }
            
            $html .= "<ul class='jot-getstarted-nested'>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("Go to the %s tab to tailor your member subscription form.","jot-plugin"), $url) . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("You can create a subscription form for each group you add.","jot-plugin") . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("In the Group Invite tab, you'll see the HTML to create the form or you can use the provided shortcode. ","jot-plugin") . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("Add the HTML or shortcode to the appropriate page on your website.","jot-plugin") . "</span>" . "</li>";
            $html .= "</ul>";
            $html .= "</li>";
            
            
            $html .= "<li>" .  __("Step 6 - Receive SMS messages (including Opt-outs).","jot-plugin") . "</li>";
            $url = "<a href='http://www.getcloudsms.com/documentation/pro-settings-tab/#jot-smsrequesturl' target='_blank'>" . __("'A MESSAGE COMES IN'","jot-plugin") . "</a>";
            $url = apply_filters('jot_whitelabel_message_comes_in_url',$url);
	    
	    $settings_url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=twiliosettings'>" . __("Messaging-Settings-Twilio Settings","jot-plugin") .  "</a>";
            $html .= "<ul class='jot-getstarted-nested'>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("To receive SMS messages and group subscription opt-outs into the plugin, you need to configure the %s field in your Twilio account.","jot-plugin"), $url) . "</span>" . "</li>";
	    $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("You can do this manually, or you can use the 'Configure' button or the %s page","jot-plugin"),$settings_url) . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("To configure manually, following the steps below.","jot-plugin"),$settings_url) . "</span>" . "</li>";
	    $html .= "<ol>";
	    $html .= "<li> " . "<span class='getstarted-description'>" . __("Login to your Twilio account, select your numbers and configure the 'Messaging' section.","jot-plugin") . "</span>" . "</li>";
            $divider = "<br><div class='divider'></div>";
            $insmsurl = get_site_url() . "?inbound";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("Configure the 'A MESSAGE COMES IN' field, to use Webhooks and enter the URL: %s<b>%s</b> ","jot-plugin"),$divider, $insmsurl) . "</span>" . "</li>";
            $html .= "<li> " . "<span class='getstarted-description'>" . __("Once the 'A MESSAGE COMES IN' is configured, Twilio will route SMS messages sent to your Twilio number, to the JOT plugin.","jot-plugin") . "</span>" . "</li>";
            $html .= "</ol>";
	    $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=message-history' target='_blank'>" . __("Message History","jot-plugin") .  "</a>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("You can see received SMS messages in the %s tab.","jot-plugin"), $url) . "</span>" . "</li>";
            $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=notification' target='_blank'>" . __("Messaging-Settings-Notification","jot-plugin") .  "</a>";
            $html .= "<li> " . "<span class='getstarted-description'>" . sprintf (__("Can you choose to be notified when an SMS is received, in the %s tab.","jot-plugin"), $url) . "</span>" . "</li>";
            $html .= "</ul>";
            $html .= "</li>";            
            
            $html .= "</ul><br><br><br>";
            $url = "<a href='http://www.getcloudsms.com/documentation/' target='_blank'>" . __("Pro Documentation","jot-plugin") .  "</a>";
	    $url = apply_filters('jot_whitelabel_documentation_url',$url);
            $html .= "<span class='getstarted-description'>" . sprintf( __("For detailed documentation please go to %s","jot-plugin"),$url) . "</span>";
            
            $html .= "</td></tr>";
            
            
            return $html;
        }
        
        public function render_twiliosettings($sections, $tab) {
            
            $fields = $this->get_settings_fields($tab);
            $smsdetails = get_option('jot-plugin-smsprovider');
                      
            $html = "";
            
            // List all SMS providers
            $html .= $this->render_section_header(__("Twilio Settings","jot-plugin"));
	    
	    // Render settings notifications
	    $html .= $this->render_settings_notifications();
                
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
                            
            // List all the SMS provider specific fields
            if ($selected_provider != 'default' && !empty($selected_provider)) {
                                        
                    foreach ($fields as $k=>$v) {
                        
                        if (isset($v['optional'])) {
                            if ($v['optional']) {                               
                                $currval = $this->get_smsprovider_settings($k . '-' . $selected_provider);
                                $html .= $this->render_row($k, $k. '-' . $selected_provider,$currval,$tab);                                
                            }
                        }
                    }
                   
                    $html .= $this->render_row('jot-messservchk','',$this->get_smsprovider_settings('jot-messservchk'),$tab);
                   
                    /*
                    // Use credentials on multisite?
                    if (function_exists('is_multisite') && is_multisite()) {
                       $sms_site_details = get_site_option('jot-plugin-network-smsprovider') ? get_site_option('jot-plugin-network-smsprovider') : array() ;
                       $currval = isset($sms_site_details['jot-smsuseacrossnetwork']) ? $sms_site_details['jot-smsuseacrossnetwork'] : false;
                       $html .= $this->render_row('jot-smsuseacrossnetwork','',$currval,$tab);
                    }
                    */
                    
		    // Render all phone numbers
                    $smsprovider_numbers = Joy_Of_Text_Plugin()->currentsmsprovider->getPhoneNumbers();   
                    $smsprovider_currnumber = $this->get_current_smsprovider_number();		                                              
                    $html .= $this->render_row_multi('jot-phonenumbers','jot-phonenumbers-' . $selected_provider ,$smsprovider_numbers['all_numbers'], $smsprovider_currnumber, $tab);
		    
		    // Render SMS URL for the selected number		  
		    if ($smsprovider_currnumber == "") {
			$twilio_sms_url = __("<<Not set>>","jot-plugin");
		    } else {
			$twilio_sms_url = Joy_Of_Text_Plugin()->currentsmsprovider->getSmsUrl($smsprovider_currnumber);
	    	    }		    
	            $html .= $this->render_row('jot-smsurl','',$twilio_sms_url,$tab);	
		    
		    
		    // Set country code to US if not already set. 
                    $allcountrycodes = $this->get_countrycodes();   
                    $currcc = $this->get_smsprovider_settings('jot-smscountrycode');
                       
                    if (empty($currcc)) {
                        $this->set_smsprovider_settings('jot-smscountrycode','US');
                        $currcc = $this->get_smsprovider_settings('jot-smscountrycode');
                     }
                    $html .= $this->render_row_multi('jot-smscountrycode','',$allcountrycodes,$currcc,$tab);
                      
                   
            }
            
            $message_code = isset($smsprovider_numbers['message_code']) ? $smsprovider_numbers['message_code'] : "";
            $message_text = isset($smsprovider_numbers['message_text']) ? $smsprovider_numbers['message_text'] : "";            
            
	    /*           
            if ($message_code == 0) {                
                if ( $this->get_current_smsprovider_number() == 'default') {
                    $message_code = -1;
                    $message_text = __( 'Please select your "from" number and save.', 'jot-plugin' );                   
                }
            }
            */
            
            $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=licencekeys'>" . __("Messaging-Settings-Licence Keys","jot-plugin") .  "</a>";
            $html .= "<tr><th colspan=2><p class='description'>";
            $html .= sprintf (__("Go to %s to activate your licence key.","jot-plugin"), $url);
            $html .= "</p></th></tr>";
                        
            $return_array = array("message_code"=> $message_code,
                         "message_text"=> $message_text,
                         'html'=> $html);
            
            return apply_filters( 'jot_render_smsprovider_settings', $return_array);
                
            
        }
	
	public function render_settings_notifications() {
	    
	    $html = "";
	    $correct_sms_url =  get_site_url() . "?inbound"; 
	    
	    $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
	    $twilio_accountsid   = $this->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
	    $twilio_authsid      = $this->get_smsprovider_settings('jot-authsid-' . $selected_provider);
	    $twilio_phonenumber  = $this->get_current_smsprovider_number();
	    $twilio_sms_url    = Joy_Of_Text_Plugin()->currentsmsprovider->getSmsUrl($twilio_phonenumber);
	       
	    // Check connectivity to Twilio
	    $smsprovider_numbers = Joy_Of_Text_Plugin()->currentsmsprovider->getPhoneNumbers();
	    $message_code = isset($smsprovider_numbers['message_code']) ? $smsprovider_numbers['message_code'] : "";
            $message_text = isset($smsprovider_numbers['message_text']) ? $smsprovider_numbers['message_text'] : "";      
	    
	    //**********************************/	    
	    // Indicate successful configuration.
	    if ($message_code == 0 ) {
		if ($twilio_phonenumber != 'default' || $twilio_sms_url == $correct_sms_url) {		
		    $html .=  "<div class='notice notice-success is-dismissible'>";
		    $html .=  "<p>";
		}
		
		if ($twilio_phonenumber != 'default') {
			// Render Voice URL help messages		   
			$html .=  __("You are successfully connecting to Twilio.",'jot-plugin');		  		    
		}
		
		if ($twilio_sms_url == $correct_sms_url) {
			// Render Voice URL help messages
			$html .=  "<p>";
			$html .=  __("You've successfully configured the Twilio SMS URL.",'jot-plugin');		  		    
		}
		
		if ($twilio_phonenumber != 'default' || $twilio_sms_url == $correct_sms_url) {
		    $html .=  "<p>";
		    $html .=  "</div>";
		}
	    } else {
		if ($twilio_accountsid  == "" && $twilio_authsid == "") {
		    // New install don't show connection error
		} else {
		    $html .= "<div class='notice notice-error is-dismissible'>";
		    $html .= "<p>";
		    $html .= __("Unable to connect to Twilio. The error message is:",'jot-plugin');
		    $html .= "<p>";
		    $html .= $message_text;
		    $html .= "<p>";
		    $html .= "<p>";
		    $html .= "</div>";
		}
	    }
	    
	    //**********************************/
	    // Indicate Twilio Auth info needs to be entered.	    
	    $twilio_dashboard_url =  "<a href='https://www.twilio.com/console' target='_blank'>" . __("Twilio Console Dashboard",'jot-plugin') . "</a>";
	    if ($twilio_accountsid == "" || $twilio_authsid == "") {
	        $html .= "<div class='notice notice-error is-dismissible'>";
		$html .= "<p>";
	    }
	    
	    if ($twilio_accountsid == "") {		     
		    $html .= __("Please enter your Twilio Account SID.",'jot-plugin');		  		    
	    }
	    
	    if ($twilio_authsid == "") {		   
		    $html .= "<p>";
		    $html .= __("Please enter your Twilio Authenication Token.");		  		    
	    }
	    
	    if ($twilio_accountsid == "" || $twilio_authsid == "") {
		$html .=  "<p>";
		$html .= sprintf(__("The Twilio Account SID and Auth Token can be obtained through the %s",'jot-plugin'),$twilio_dashboard_url);
		$html .= "<p>";
	        $html .= "</div>";		
	    }
	    
	    //**********************************/   
	    // Indicate that the phone number needs to be selected
	    if ($twilio_phonenumber == "default") {
	        $html .= "<div class='notice notice-error is-dismissible'>";
		$html .= "<p>";
		$html .= __("After you've entered your Twilio details and pressed 'Save', then select your number and save again.",'jot-plugin');
		$html .= "<p>";
	        $html .= "</div>";		
	    }
	    
	    /************************************/
	    // Add button to configure the selected number for inbound voice	    
	    if ($twilio_phonenumber != 'default' && $twilio_accountsid != "" && $twilio_authsid != "") {
		if ($twilio_sms_url != $correct_sms_url) {
			// Render Voice URL help messages		   
			$notice_html = "<div class='notice notice-info is-dismissible'>";
			$notice_html .= "<p>";
			$notice_html .= __("You need to configure your Twilio account to send messages you've received to this plugin.",'jot-plugin');
			$notice_html .= "<p>";
			$notice_html .= __("You can do this by logging into Twilio, selecting your number and configuring the 'A message comes in' field.",'jot-plugin');
			$notice_html .= "<p>";
			$notice_html .= __("Your number should be configured to use the URL : " ,'jot-plugin');
			$notice_html .= "<b>" .$correct_sms_url . "</b>";
			$notice_html .= "<p>";
			$notice_html .= __("Alternatively, you can use the button below to configure the URL for you.",'jot-plugin');
			$notice_html .= "<p>";
			$notice_html .= "<input type='button' name='jot-config-sms-url' id='jot-config-sms-url' class='button-secondary' value='" . __("Configure URL",'jot-plugin') . "'>";            
			$notice_html .= "<div id='jot-config-sms-url-messages'></div>";
			$notice_html .= "<p>";
			$notice_html .= "</div>";
			$html .=  $notice_html;
		}
	    }
	 
	    return $html;   
	}
        
	public function render_saved_notice() {
	    $html = "";
	    if( isset($_GET['settings-updated']) ) { 
                $html .=  "<div id=\"update_notice_message\" class=\"notice notice-success is-dismissible\">";
                $html .=  "<p>";
		$html .=  "<strong>" . __('Settings saved.','jot-plugin') . "</strong>";
		$html .=  "</p>";
                $html .=  "</div>";
		
	    }
	    return $html;
	    
	}
	
        public function render_licences($sections, $tab) {
            
                        
            $html = "<tr>";
            $html .= "<th colspan='2'>";
            $html .= "<p class='description'>";
            $html .= __("Enter your licence key(s) and press the 'Activate' button.","jot-plugin");
            $html .= "<br>";
            $html .= __("A response of 'valid' or 'Active' indicates that the licence has been successfully activated.","jot-plugin");
            $html .= "</p>";
            $html .= "</th>";
            $html .= "</tr>";
            
            $html .= $this->render_section_header(__("JOT Pro licence key.","jot-plugin"));
                            
            $html .= $this->render_row('jot-eddlicence','',$this->get_network_smsprovider_settings('jot-eddlicence'),$tab);
            $status = $this->get_network_smsprovider_settings('jot-eddlicencestatus');
            $html .= $this->render_row('jot-eddlicencestatus','',$status,$tab);
                
            // Licence activation button
            $html .= "<tr>";
            $html .= "<th>";
            $html .= "";
            $html .= "</th><td>";
            $html .= "<input type='button' name='jot-edd-licence-activate' id='jot-edd-licence-activate' class='button-secondary' value='" . __("Activate licence","jot-plugin") . "'>";            
            $html .= "</td>";
            $html .= "</tr>";                
            $html = apply_filters('jot_render_additional_licences',$html,$tab);
            return $html;
        }
        
        public function render_inbound($sections, $tab) {
        	
            $html = $this->render_section_header(__("Inbound SMS settings","jot-plugin"));
            $smsurl = get_site_url() . "?inbound";
            $html .= $this->render_row('jot-inbsmsurl','',$smsurl,$tab);
            //$html .= $this->render_row('jot-inbreply','',$this->get_smsprovider_settings('jot-inbreply'),$tab);
	    $html .= $this->render_row('jot-inbmanagesubs','',$this->get_smsprovider_settings('jot-inbmanagesubs'),$tab);
	    
                       
            return $html;
        }
        
        public function render_notifications($sections, $tab) {	    
	    
            $html = $this->render_section_header(__("Notification settings","jot-plugin"));
	    
	    // Get All groups	    
	    $jotgroups = Joy_Of_Text_Plugin()->messenger->get_display_groups();
	    foreach ($jotgroups as $jotgroup) {
		 $allgroups[$jotgroup->jot_groupid] = $jotgroup->jot_groupname;		    		    
	    }
	    $allgroups[-1] = __("Select notification group","jot-plugin");
	    $currval = $this->get_smsprovider_settings('jot-inbnotgroup');
	   
	    if ($currval == "") {
		$currval = -1;
	    }
	    
	    $html .= $this->render_row_multi('jot-inbnotgroup','',$allgroups,$currval,$tab);  
	    $html .= $this->render_row('jot-inbemail','',$this->get_smsprovider_settings('jot-inbemail'),$tab);
	    
	    // Inbound message/sub settings
	    $html .= $this->render_section_header(__("Inbound message and subscription notificatons.","jot-plugin"));	    
	    	    
            $html .= $this->render_row('jot-inbsmschk','',$this->get_smsprovider_settings('jot-inbsmschk'),$tab);
            $html .= $this->render_row('jot-inbsubchk','',$this->get_smsprovider_settings('jot-inbsubchk'),$tab);
	    
	   
            $html .= $this->render_row('jot-inbemailsubject','',$this->get_smsprovider_settings('jot-inbemailsubject'),$tab);            
            $html .= $this->render_row('jot-inbsmsrtmsg','',$this->get_smsprovider_settings('jot-inbsmsrtmsg'),$tab);

            $html .= $this->render_row('jot-inbsmssuffix','',$this->get_smsprovider_settings('jot-inbsmssuffix'),$tab);                 

	    // Unsubscription settings
	    $html .= $this->render_section_header(__("Unsubscription notifications.","jot-plugin"));
	    $html .= $this->render_row('jot-inbunsubchk','',$this->get_smsprovider_settings('jot-inbunsubchk'),$tab);
	    $html .= $this->render_row('jot-inbunsub-emailsubject','',$this->get_smsprovider_settings('jot-inbunsub-emailsubject'),$tab);   
	    $html .= $this->render_row('jot-inbunsubmsg','',$this->get_smsprovider_settings('jot-inbunsubmsg'),$tab);
	  
	    
	   

            return $html;
        }
        
        public function render_general($sections, $tab) {
            
            $html = "";
            
            // Hide Sender ID for US installations           
            $currcc = $this->get_smsprovider_settings('jot-smscountrycode');            
            if ($currcc != "US") {
               $html .= $this->render_section_header(__("Sender ID Settings (Not applicable in the USA)","jot-plugin"));
               $html .= $this->render_row('jot-smssenderid','',$this->get_smsprovider_settings('jot-smssenderid'),$tab);
            }
            
            // Voice Preferences
            $voicegender = $this->get_smsprovider_settings('jot-voice-gender');
            $voiceaccent = $this->get_smsprovider_settings('jot-voice-accent');
               
            if (empty($voicegender)) {
                $this->set_voice_preference('alice');          
                $voicesettings = get_option('jot-plugin-smsprovider');
                $voicegender = $voicesettings['jot-voice-gender'];           
            }
            if (empty($voiceaccent)) {           
                $this->set_voiceaccent_preference('en-GB');
                $voicesettings = get_option('jot-plugin-smsprovider');          
                $voiceaccent = $voicesettings['jot-voice-accent'];
            }
            $html .= $this->render_section_header(__("Voice preferences","jot-plugin")); 
            $html .= $this->render_row('jot-voice-gender','',$voicegender,$tab);
            $allaccents = $this->get_accents();
            $html .= $this->render_row_multi('jot-voice-accent','' ,$allaccents, $voiceaccent, $tab);
            
            // Select default open tab
            $html .= $this->render_section_header(__("Plugin Tabs","jot-plugin"));
            $alltabs = $this->get_settings_sections();
            $showtabs = array();
            foreach ($alltabs as $key => $values) {                   
                   $showtabs[$key] = $values['tabname']; 
            }
            $defaulttab = $this->get_smsprovider_settings('jot-defaulttab');
                
            if (empty($defaulttab)) {
                $defaulttab = Joy_Of_Text_Plugin()->admin->get_default_tab($alltabs);
                $this->set_smsprovider_settings('jot-defaulttab',$defaulttab);
            }
            $html .= $this->render_row_multi('jot-defaulttab','',$showtabs,$defaulttab,$tab);
            
           
	    
	    // All groups
	    $html .= $this->render_section_header(__("Admin settings","jot-plugin"));
	    $jotgroups = Joy_Of_Text_Plugin()->messenger->get_display_groups();
	    foreach ($jotgroups as $jotgroup) {
		 $allgroups[$jotgroup->jot_groupid] = $jotgroup->jot_groupname;		    		    
	    }
	    $allgroups[-1] = __("Select admin group","jot-plugin");
	    $currval = $this->get_smsprovider_settings('jot-admingroup');
	   
	    if ($currval == "") {
		$currval = -1;
	    }
	    
	    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-admingroup','',$allgroups,$currval,$tab);  
                       
            return $html;
                    
        }
        
                
        
        /**
        * Gets the current selected SMS provider
        *
        */
        public function get_current_smsprovider() {

            if (isset($_GET['smsprovider'])) {
                $this->set_current_smsprovider($_GET['smsprovider']);
            }
            $sms =  get_option('jot-plugin-smsprovider');
            return $sms['jot-smsproviders'];   
            
        }
        
        /**
        * Sets the current selected SMS provider if sent in URL
        *
        */
        public function set_current_smsprovider() {
            $smsprov =  get_option('jot-plugin-smsprovider');
            $smsprov['jot-smsproviders'] = $_GET['smsprovider'] ;   
            update_option('jot-plugin-smsprovider',$smsprov);
            
        }
        
                
        public function get_current_smsprovider_number() {
                    
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            $sms =  get_option('jot-plugin-smsprovider');
            if (isset($sms['jot-phonenumbers-' . $selected_provider])) {
                return $sms['jot-phonenumbers-' . $selected_provider];  
            } else {
                return "";
             
            }
            
        }
        
        
        /*
         *
         * Set and Get the voice preference
         *
         */
        public function set_voice_preference($value) {
            $smsdetails =  get_option('jot-plugin-smsprovider');
            $smsdetails['jot-voice-gender'] = $value ;   
            update_option('jot-plugin-smsprovider',$smsdetails);  
        }
        
        public function set_voiceaccent_preference($value) {            
            $smsdetails =  get_option('jot-plugin-smsprovider');
            $smsdetails['jot-voice-accent'] = $value ;   
            update_option('jot-plugin-smsprovider',$smsdetails);  
        }
        
       
        /*
         *
         * Get accents available for 'man' or 'alice' 
         *
         */
        public function get_accents() {
            
            $voicesettings = get_option('jot-plugin-smsprovider');          
            $voicegender = isset($voicesettings['jot-voice-gender']) ? $voicesettings['jot-voice-gender'] : "";
            
            switch ( $voicegender ) {
                case 'man'; 
                    $allaccents = array('en' => __('English','jot-plugin'),
                                        'en-GB' => __('English, UK','jot-plugin'),
                                        'es' => __('Spanish','jot-plugin'),
                                        'fr' => __('French','jot-plugin'),
                                        'de' => __('German','jot-plugin')
                                        //'it' => __('Italian','jot-plugin')
                                        );   
                break;
                case 'alice';
                    $allaccents = array('da-DK'=> __('Danish, Denmark','jot-plugin'),	
                                        'de-DE'=> __('German, Germany','jot-plugin'),	
                                        'en-AU'=> __('English, Australia','jot-plugin'),	
                                        'en-CA'=> __('English, Canada','jot-plugin'),	
                                        'en-GB'=> __('English, UK','jot-plugin'),	
                                        'en-IN'=> __('English, India','jot-plugin'),	
                                        'en-US'=> __('English, United States','jot-plugin'),	
                                        'ca-ES'=> __('Catalan, Spain','jot-plugin'),	
                                        'es-ES'=> __('Spanish, Spain','jot-plugin'),	
                                        'es-MX'=> __('Spanish, Mexico','jot-plugin'),	
                                        'fi-FI'=> __('Finnish, Finland','jot-plugin'),	
                                        'fr-CA'=> __('French, Canada','jot-plugin'),	
                                        'fr-FR'=> __('French, France','jot-plugin'),	
                                        'it-IT'=> __('Italian, Italy','jot-plugin'),	
                                        'ja-JP'=> __('Japanese, Japan','jot-plugin'),	
                                        'ko-KR'=> __('Korean, Korea','jot-plugin'),	
                                        'nb-NO'=> __('Norwegian, Norway','jot-plugin'),	
                                        'nl-NL'=> __('Dutch, Netherlands','jot-plugin'),	
                                        'pl-PL'=> __('Polish-Poland','jot-plugin'),	
                                        'pt-BR'=> __('Portuguese, Brazil','jot-plugin'),	
                                        'pt-PT'=> __('Portuguese, Portugal','jot-plugin'),	
                                        'ru-RU'=> __('Russian, Russia','jot-plugin'),	
                                        'sv-SE'=> __('Swedish, Sweden','jot-plugin'),	
                                        'zh-CN'=> __('Chinese (Mandarin)','jot-plugin'),	
                                        'zh-HK'=> __('Chinese (Cantonese)','jot-plugin'),	
                                        'zh-TW'=> __('Chinese (Taiwanese Mandarin)','jot-plugin')
                                       );
                            
                break;
                default;
                       $allaccents = array('en-GB' => 'English, UK');  
                break;
             }
             return $allaccents;
        }
        
                
        /*
         *
         * Get the accents for a frontend Ajax request
         *
         */
        public function  process_refresh_languages () {
            $formdata = $_POST['formdata'];    
            $jot_voice_gender = $formdata['jot_voice_gender'];
            $this->set_voice_preference($jot_voice_gender);
            $this->set_voiceaccent_preference('en-GB');
            
            $allaccents = $this->get_accents();
            echo json_encode($allaccents);
            wp_die();
            
        }        
       
        /*
         *
         * Get country codes
         *
         */
        public function get_countrycodes() {
            
            
                    $countrycodes = array(  'AF' => __('Afghanistan - (AF)','jot-plugin') ,
                                            'AX' => __('Aland Islands - (AX)','jot-plugin') ,
                                            'AL' => __('Albania - (AL)','jot-plugin') ,
                                            'DZ' => __('Algeria - (DZ)','jot-plugin') ,
                                            'AS' => __('American Samoa - (AS)','jot-plugin') ,
                                            'AD' => __('Andorra - (AD)','jot-plugin') ,
                                            'AO' => __('Angola - (AO)','jot-plugin') ,
                                            'AI' => __('Anguilla - (AI)','jot-plugin') ,
                                            'AQ' => __('Antarctica - (AQ)','jot-plugin') ,
                                            'AG' => __('Antigua and Barbuda - (AG)','jot-plugin') ,
                                            'AR' => __('Argentina - (AR)','jot-plugin') ,
                                            'AM' => __('Armenia - (AM)','jot-plugin') ,
                                            'AW' => __('Aruba - (AW)','jot-plugin') ,
                                            'AU' => __('Australia - (AU)','jot-plugin') ,
                                            'AT' => __('Austria - (AT)','jot-plugin') ,
                                            'AZ' => __('Azerbaijan - (AZ)','jot-plugin') ,
                                            'BS' => __('Bahamas - (BS)','jot-plugin') ,
                                            'BH' => __('Bahrain - (BH)','jot-plugin') ,
                                            'BD' => __('Bangladesh - (BD)','jot-plugin') ,
                                            'BB' => __('Barbados - (BB)','jot-plugin') ,
                                            'BY' => __('Belarus - (BY)','jot-plugin') ,
                                            'BE' => __('Belgium - (BE)','jot-plugin') ,
                                            'BZ' => __('Belize - (BZ)','jot-plugin') ,
                                            'BJ' => __('Benin - (BJ)','jot-plugin') ,
                                            'BM' => __('Bermuda - (BM)','jot-plugin') ,
                                            'BT' => __('Bhutan - (BT)','jot-plugin') ,
                                            'BO' => __('Bolivia (Plurinational State of) - (BO)','jot-plugin') ,
                                            'BQ' => __('Bonaire, Sint Eustatius and Saba - (BQ)','jot-plugin') ,
                                            'BA' => __('Bosnia and Herzegovina - (BA)','jot-plugin') ,
                                            'BW' => __('Botswana - (BW)','jot-plugin') ,
                                            'BV' => __('Bouvet Island - (BV)','jot-plugin') ,
                                            'BR' => __('Brazil - (BR)','jot-plugin') ,
                                            'IO' => __('British Indian Ocean Territory - (IO)','jot-plugin') ,
                                            'BN' => __('Brunei Darussalam - (BN)','jot-plugin') ,
                                            'BG' => __('Bulgaria - (BG)','jot-plugin') ,
                                            'BF' => __('Burkina Faso - (BF)','jot-plugin') ,
                                            'BI' => __('Burundi - (BI)','jot-plugin') ,
                                            'KH' => __('Cambodia - (KH)','jot-plugin') ,
                                            'CM' => __('Cameroon - (CM)','jot-plugin') ,
                                            'CA' => __('Canada - (CA)','jot-plugin') ,
                                            'CV' => __('Cabo Verde - (CV)','jot-plugin') ,
                                            'KY' => __('Cayman Islands - (KY)','jot-plugin') ,
                                            'CF' => __('Central African Republic - (CF)','jot-plugin') ,
                                            'TD' => __('Chad - (TD)','jot-plugin') ,
                                            'CL' => __('Chile - (CL)','jot-plugin') ,
                                            'CN' => __('China - (CN)','jot-plugin') ,
                                            'CX' => __('Christmas Island - (CX)','jot-plugin') ,
                                            'CC' => __('Cocos (Keeling) Islands - (CC)','jot-plugin') ,
                                            'CO' => __('Colombia - (CO)','jot-plugin') ,
                                            'KM' => __('Comoros - (KM)','jot-plugin') ,
                                            'CG' => __('Congo - (CG)','jot-plugin') ,
                                            'CD' => __('Congo (Democratic Republic of the) - (CD)','jot-plugin') ,
                                            'CK' => __('Cook Islands - (CK)','jot-plugin') ,
                                            'CR' => __('Costa Rica - (CR)','jot-plugin') ,
                                            'CI' => __('Cote d\'Ivoire - (CI)','jot-plugin') ,
                                            'HR' => __('Croatia - (HR)','jot-plugin') ,
                                            'CU' => __('Cuba - (CU)','jot-plugin') ,
                                            'CW' => __('Curacao - (CW)','jot-plugin') ,
                                            'CY' => __('Cyprus - (CY)','jot-plugin') ,
                                            'CZ' => __('Czech Republic - (CZ)','jot-plugin') ,
                                            'DK' => __('Denmark - (DK)','jot-plugin') ,
                                            'DJ' => __('Djibouti - (DJ)','jot-plugin') ,
                                            'DM' => __('Dominica - (DM)','jot-plugin') ,
                                            'DO' => __('Dominican Republic - (DO)','jot-plugin') ,
                                            'EC' => __('Ecuador - (EC)','jot-plugin') ,
                                            'EG' => __('Egypt - (EG)','jot-plugin') ,
                                            'SV' => __('El Salvador - (SV)','jot-plugin') ,
                                            'GQ' => __('Equatorial Guinea - (GQ)','jot-plugin') ,
                                            'ER' => __('Eritrea - (ER)','jot-plugin') ,
                                            'EE' => __('Estonia - (EE)','jot-plugin') ,
                                            'ET' => __('Ethiopia - (ET)','jot-plugin') ,
                                            'FK' => __('Falkland Islands (Malvinas) - (FK)','jot-plugin') ,
                                            'FO' => __('Faroe Islands - (FO)','jot-plugin') ,
                                            'FJ' => __('Fiji - (FJ)','jot-plugin') ,
                                            'FI' => __('Finland - (FI)','jot-plugin') ,
                                            'FR' => __('France - (FR)','jot-plugin') ,
                                            'GF' => __('French Guiana - (GF)','jot-plugin') ,
                                            'PF' => __('French Polynesia - (PF)','jot-plugin') ,
                                            'TF' => __('French Southern Territories - (TF)','jot-plugin') ,
                                            'GA' => __('Gabon - (GA)','jot-plugin') ,
                                            'GM' => __('Gambia - (GM)','jot-plugin') ,
                                            'GE' => __('Georgia - (GE)','jot-plugin') ,
                                            'DE' => __('Germany - (DE)','jot-plugin') ,
                                            'GH' => __('Ghana - (GH)','jot-plugin') ,
                                            'GI' => __('Gibraltar - (GI)','jot-plugin') ,
                                            'GR' => __('Greece - (GR)','jot-plugin') ,
                                            'GL' => __('Greenland - (GL)','jot-plugin') ,
                                            'GD' => __('Grenada - (GD)','jot-plugin') ,
                                            'GP' => __('Guadeloupe - (GP)','jot-plugin') ,
                                            'GU' => __('Guam - (GU)','jot-plugin') ,
                                            'GT' => __('Guatemala - (GT)','jot-plugin') ,
                                            'GG' => __('Guernsey - (GG)','jot-plugin') ,
                                            'GN' => __('Guinea - (GN)','jot-plugin') ,
                                            'GW' => __('Guinea-Bissau - (GW)','jot-plugin') ,
                                            'GY' => __('Guyana - (GY)','jot-plugin') ,
                                            'HT' => __('Haiti - (HT)','jot-plugin') ,
                                            'HM' => __('Heard Island and McDonald Islands - (HM)','jot-plugin') ,
                                            'VA' => __('Holy See - (VA)','jot-plugin') ,
                                            'HN' => __('Honduras - (HN)','jot-plugin') ,
                                            'HK' => __('Hong Kong - (HK)','jot-plugin') ,
                                            'HU' => __('Hungary - (HU)','jot-plugin') ,
                                            'IS' => __('Iceland - (IS)','jot-plugin') ,
                                            'IN' => __('India - (IN)','jot-plugin') ,
                                            'ID' => __('Indonesia - (ID)','jot-plugin') ,
                                            'IR' => __('Iran (Islamic Republic of) - (IR)','jot-plugin') ,
                                            'IQ' => __('Iraq - (IQ)','jot-plugin') ,
                                            'IE' => __('Ireland - (IE)','jot-plugin') ,
                                            'IM' => __('Isle of Man - (IM)','jot-plugin') ,
                                            'IL' => __('Israel - (IL)','jot-plugin') ,
                                            'IT' => __('Italy - (IT)','jot-plugin') ,
                                            'JM' => __('Jamaica - (JM)','jot-plugin') ,
                                            'JP' => __('Japan - (JP)','jot-plugin') ,
                                            'JE' => __('Jersey - (JE)','jot-plugin') ,
                                            'JO' => __('Jordan - (JO)','jot-plugin') ,
                                            'KZ' => __('Kazakhstan - (KZ)','jot-plugin') ,
                                            'KE' => __('Kenya - (KE)','jot-plugin') ,
                                            'KI' => __('Kiribati - (KI)','jot-plugin') ,
                                            'KP' => __('Korea (Democratic People\'s Republic of) - (KP)','jot-plugin') ,
                                            'KR' => __('Korea (Republic of) - (KR)','jot-plugin') ,
                                            'KW' => __('Kuwait - (KW)','jot-plugin') ,
                                            'KG' => __('Kyrgyzstan - (KG)','jot-plugin') ,
                                            'LA' => __('Lao People\'s Democratic Republic - (LA)','jot-plugin') ,
                                            'LV' => __('Latvia - (LV)','jot-plugin') ,
                                            'LB' => __('Lebanon - (LB)','jot-plugin') ,
                                            'LS' => __('Lesotho - (LS)','jot-plugin') ,
                                            'LR' => __('Liberia - (LR)','jot-plugin') ,
                                            'LY' => __('Libya - (LY)','jot-plugin') ,
                                            'LI' => __('Liechtenstein - (LI)','jot-plugin') ,
                                            'LT' => __('Lithuania - (LT)','jot-plugin') ,
                                            'LU' => __('Luxembourg - (LU)','jot-plugin') ,
                                            'MO' => __('Macao - (MO)','jot-plugin') ,
                                            'MK' => __('Macedonia (the former Yugoslav Republic of) - (MK)','jot-plugin') ,
                                            'MG' => __('Madagascar - (MG)','jot-plugin') ,
                                            'MW' => __('Malawi - (MW)','jot-plugin') ,
                                            'MY' => __('Malaysia - (MY)','jot-plugin') ,
                                            'MV' => __('Maldives - (MV)','jot-plugin') ,
                                            'ML' => __('Mali - (ML)','jot-plugin') ,
                                            'MT' => __('Malta - (MT)','jot-plugin') ,
                                            'MH' => __('Marshall Islands - (MH)','jot-plugin') ,
                                            'MQ' => __('Martinique - (MQ)','jot-plugin') ,
                                            'MR' => __('Mauritania - (MR)','jot-plugin') ,
                                            'MU' => __('Mauritius - (MU)','jot-plugin') ,
                                            'YT' => __('Mayotte - (YT)','jot-plugin') ,
                                            'MX' => __('Mexico - (MX)','jot-plugin') ,
                                            'FM' => __('Micronesia (Federated States of) - (FM)','jot-plugin') ,
                                            'MD' => __('Moldova (Republic of) - (MD)','jot-plugin') ,
                                            'MC' => __('Monaco - (MC)','jot-plugin') ,
                                            'MN' => __('Mongolia - (MN)','jot-plugin') ,
                                            'ME' => __('Montenegro - (ME)','jot-plugin') ,
                                            'MS' => __('Montserrat - (MS)','jot-plugin') ,
                                            'MA' => __('Morocco - (MA)','jot-plugin') ,
                                            'MZ' => __('Mozambique - (MZ)','jot-plugin') ,
                                            'MM' => __('Myanmar - (MM)','jot-plugin') ,
                                            'NA' => __('Namibia - (NA)','jot-plugin') ,
                                            'NR' => __('Nauru - (NR)','jot-plugin') ,
                                            'NP' => __('Nepal - (NP)','jot-plugin') ,
                                            'NL' => __('Netherlands - (NL)','jot-plugin') ,
                                            'NC' => __('New Caledonia - (NC)','jot-plugin') ,
                                            'NZ' => __('New Zealand - (NZ)','jot-plugin') ,
                                            'NI' => __('Nicaragua - (NI)','jot-plugin') ,
                                            'NE' => __('Niger - (NE)','jot-plugin') ,
                                            'NG' => __('Nigeria - (NG)','jot-plugin') ,
                                            'NU' => __('Niue - (NU)','jot-plugin') ,
                                            'NF' => __('Norfolk Island - (NF)','jot-plugin') ,
                                            'MP' => __('Northern Mariana Islands - (MP)','jot-plugin') ,
                                            'NO' => __('Norway - (NO)','jot-plugin') ,
                                            'OM' => __('Oman - (OM)','jot-plugin') ,
                                            'PK' => __('Pakistan - (PK)','jot-plugin') ,
                                            'PW' => __('Palau - (PW)','jot-plugin') ,
                                            'PS' => __('Palestine, State of - (PS)','jot-plugin') ,
                                            'PA' => __('Panama - (PA)','jot-plugin') ,
                                            'PG' => __('Papua New Guinea - (PG)','jot-plugin') ,
                                            'PY' => __('Paraguay - (PY)','jot-plugin') ,
                                            'PE' => __('Peru - (PE)','jot-plugin') ,
                                            'PH' => __('Philippines - (PH)','jot-plugin') ,
                                            'PN' => __('Pitcairn - (PN)','jot-plugin') ,
                                            'PL' => __('Poland - (PL)','jot-plugin') ,
                                            'PT' => __('Portugal - (PT)','jot-plugin') ,
                                            'PR' => __('Puerto Rico - (PR)','jot-plugin') ,
                                            'QA' => __('Qatar - (QA)','jot-plugin') ,
                                            'RE' => __('Reunion - (RE)','jot-plugin') ,
                                            'RO' => __('Romania - (RO)','jot-plugin') ,
                                            'RU' => __('Russian Federation - (RU)','jot-plugin') ,
                                            'RW' => __('Rwanda - (RW)','jot-plugin') ,
                                            'BL' => __('Saint Barthelemy - (BL)','jot-plugin') ,
                                            'SH' => __('Saint Helena, Ascension and Tristan da Cunha - (SH)','jot-plugin') ,
                                            'KN' => __('Saint Kitts and Nevis - (KN)','jot-plugin') ,
                                            'LC' => __('Saint Lucia - (LC)','jot-plugin') ,
                                            'MF' => __('Saint Martin (French part) - (MF)','jot-plugin') ,
                                            'PM' => __('Saint Pierre and Miquelon - (PM)','jot-plugin') ,
                                            'VC' => __('Saint Vincent and the Grenadines - (VC)','jot-plugin') ,
                                            'WS' => __('Samoa - (WS)','jot-plugin') ,
                                            'SM' => __('San Marino - (SM)','jot-plugin') ,
                                            'ST' => __('Sao Tome and Principe - (ST)','jot-plugin') ,
                                            'SA' => __('Saudi Arabia - (SA)','jot-plugin') ,
                                            'SN' => __('Senegal - (SN)','jot-plugin') ,
                                            'RS' => __('Serbia - (RS)','jot-plugin') ,
                                            'SC' => __('Seychelles - (SC)','jot-plugin') ,
                                            'SL' => __('Sierra Leone - (SL)','jot-plugin') ,
                                            'SG' => __('Singapore - (SG)','jot-plugin') ,
                                            'SX' => __('Sint Maarten (Dutch part) - (SX)','jot-plugin') ,
                                            'SK' => __('Slovakia - (SK)','jot-plugin') ,
                                            'SI' => __('Slovenia - (SI)','jot-plugin') ,
                                            'SB' => __('Solomon Islands - (SB)','jot-plugin') ,
                                            'SO' => __('Somalia - (SO)','jot-plugin') ,
                                            'ZA' => __('South Africa - (ZA)','jot-plugin') ,
                                            'GS' => __('South Georgia and the South Sandwich Islands - (GS)','jot-plugin') ,
                                            'SS' => __('South Sudan - (SS)','jot-plugin') ,
                                            'ES' => __('Spain - (ES)','jot-plugin') ,
                                            'LK' => __('Sri Lanka - (LK)','jot-plugin') ,
                                            'SD' => __('Sudan - (SD)','jot-plugin') ,
                                            'SR' => __('Suriname - (SR)','jot-plugin') ,
                                            'SJ' => __('Svalbard and Jan Mayen - (SJ)','jot-plugin') ,
                                            'SZ' => __('Swaziland - (SZ)','jot-plugin') ,
                                            'SE' => __('Sweden - (SE)','jot-plugin') ,
                                            'CH' => __('Switzerland - (CH)','jot-plugin') ,
                                            'SY' => __('Syrian Arab Republic - (SY)','jot-plugin') ,
                                            'TW' => __('Taiwan, Province of China - (TW)','jot-plugin') ,
                                            'TJ' => __('Tajikistan - (TJ)','jot-plugin') ,
                                            'TZ' => __('Tanzania, United Republic of - (TZ)','jot-plugin') ,
                                            'TH' => __('Thailand - (TH)','jot-plugin') ,
                                            'TL' => __('Timor-Leste - (TL)','jot-plugin') ,
                                            'TG' => __('Togo - (TG)','jot-plugin') ,
                                            'TK' => __('Tokelau - (TK)','jot-plugin') ,
                                            'TO' => __('Tonga - (TO)','jot-plugin') ,
                                            'TT' => __('Trinidad and Tobago - (TT)','jot-plugin') ,
                                            'TN' => __('Tunisia - (TN)','jot-plugin') ,
                                            'TR' => __('Turkey - (TR)','jot-plugin') ,
                                            'TM' => __('Turkmenistan - (TM)','jot-plugin') ,
                                            'TC' => __('Turks and Caicos Islands - (TC)','jot-plugin') ,
                                            'TV' => __('Tuvalu - (TV)','jot-plugin') ,
                                            'UG' => __('Uganda - (UG)','jot-plugin') ,
                                            'UA' => __('Ukraine - (UA)','jot-plugin') ,
                                            'AE' => __('United Arab Emirates - (AE)','jot-plugin') ,
                                            'GB' => __('United Kingdom of Great Britain and Northern Ireland - (GB)','jot-plugin') ,
                                            'US' => __('United States of America - (US)','jot-plugin') ,
                                            'UM' => __('United States Minor Outlying Islands - (UM)','jot-plugin') ,
                                            'UY' => __('Uruguay - (UY)','jot-plugin') ,
                                            'UZ' => __('Uzbekistan - (UZ)','jot-plugin') ,
                                            'VU' => __('Vanuatu - (VU)','jot-plugin') ,
                                            'VE' => __('Venezuela (Bolivarian Republic of) - (VE)','jot-plugin') ,
                                            'VN' => __('Viet Nam - (VN)','jot-plugin') ,
                                            'VG' => __('Virgin Islands (British) - (VG)','jot-plugin') ,
                                            'VI' => __('Virgin Islands (U.S.) - (VI)','jot-plugin') ,
                                            'WF' => __('Wallis and Futuna - (WF)','jot-plugin') ,
                                            'EH' => __('Western Sahara - (EH)','jot-plugin') ,
                                            'YE' => __('Yemen - (YE)','jot-plugin') ,
                                            'ZM' => __('Zambia - (ZM)','jot-plugin') ,
                                            'ZW' => __('Zimbabwe - (ZW)','jot-plugin') 
                                       );
                            
            
             return $countrycodes;
        }
        
        /**
        * Renders page for displaying Message panel
        *
        * @return string HTML markup for the field.
        */
        public function render_message_panel ($sections, $tab,$usage = null,$args= null) {
           
            $html = "";
            
            $smsmessage =  get_option('jot-plugin-messages');
                     
            if($args) {
                // Values passed into function through $args
                $message_body = $args['jot-message'];
                $message_suffix = $args['jot-message-suffix'];
                $message_type = $args['jot-message-type'];
                $message_removedupes = isset($args['jot-message-removedupes']) ? $args['jot-message-removedupes'] : "";
                $message_senderid = $args['jot-message-senderid'];
                // audioid is actually the media id file id
                $message_audioid = $args['jot-message-audioid'];
                $message_mediaid = $args['jot-message-audioid'];
            } else {
                // Values to be retrieved from stored options
                $message_body = $smsmessage['jot-message'];
                $message_suffix = $smsmessage['jot-message-suffix'];
                $message_type = '';
                $message_removedupes = isset($smsmessage['jot-message-removedupes']) ? 'true' : 'false';
                
                $message_senderid = "";
                if (empty($smsmessage['jot-message-senderid'] )) {
                   // Use default senderid
                   $smsdetails = get_option('jot-plugin-smsprovider');
                   $message_senderid = isset($smsdetails['jot-smssenderid']) ? $smsdetails['jot-smssenderid'] : "";
                } else {
                   $message_senderid = isset($smsmessage['jot-message-senderid']) ? $smsmessage['jot-message-senderid'] : "";
                }
                $message_audioid = $smsmessage['jot-message-audioid'];
                $message_mediaid = $smsmessage['jot-message-mms-image'];
                
            }
            
            if (isset($_GET['grpid'])) {
                $grpid = $_GET['grpid'];
            } else {
                $grpid = "";
            }
           
            if ($usage == 'schedplanner') {
                // Group list will be added by Sched Extension
            } else {
                $html .= $this->render_messageoptions();
                $html .= $this->get_recipients_list($grpid);
                $html .= $this->get_quick_send($tab);
            }
            
            $html .= "<table id='jot-message-tab-table' class=\"jot-formtab form-table\">\n";
          
            // Message body fields            
            $html .= $this->render_row('jot-message','',$message_body,$tab);            
            $html .= $this->render_row('jot-message-suffix','',$message_suffix,$tab);
            
            // Is the Twilio Message Service being used
            $jot_messservchk = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservchk');
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            $messservsid= Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-messservsid-' . $selected_provider);
            
            $html .= "<tr>";
            $html .= "<th>";
            $html .= __("Using Messaging Services?","jot-plugin");
            $html .= "</th><td>";
            if ($jot_messservchk == true && $messservsid != "") {
               $html .= __("Yes","jot-plugin");
            } else {
               $html .= __("No","jot-plugin");
            }                   
            $html .= "</td>";
            $html .= "</tr>";  
            
            // Show Twilio numbers
	    $current_selected_number = $this->get_current_smsprovider_number();
	    $smsprovider_numbers = Joy_Of_Text_Plugin()->currentsmsprovider->getPhoneNumbers();
	    $html .= $this->render_row_multi('jot-message-usenumber','',$smsprovider_numbers['all_numbers'], $current_selected_number, $tab);
            
            // Remove Dupes
            if ($usage != 'schedplanner') {
                $html .= $this->render_row('jot-message-removedupes','',$message_removedupes,$tab);
            }
            $html .= $this->render_row('jot-message-type','',$message_type,$tab);
            
            // Sender ID for SMS and MMS messages
            
            $currcc = $this->get_smsprovider_settings('jot-smscountrycode'); 
            if (!$jot_messservchk && $currcc != "US") {
               // Hide if Messaging Service is being used or country code is US
               $html .= $this->render_row('jot-message-senderid','',$message_senderid,$tab);
            }
                     
            // Text-to-Voice and Audio file
            $html .= $this->render_row_multi('jot-message-audioid','',$this->get_audio_media(),$smsmessage['jot-message-audioid'],$tab);
             
            
            // MMS image fields
            $html .= "<tr id='jot-message-mms-tr' style='display:none'>";
            $html .= "<th>";
            $html .= "Choose MMS media";
            $html .= "</th><td>";
            $html .= "<input type='button' name='jot-upload-btn' id='jot-upload-btn' class='button-secondary' value='Select MMS media'>";            
            $html .= "</td>";
            $html .= "</tr>";
            
            $html .= "<tr style='display:none'>";
            $html .= "<th>";
            $html .= "Selected MMS media";
            $html .= "</th><td>";
            
            $image_attributes = null;
            //echo "Media ID : " . $message_mediaid;
            if (!empty($message_mediaid)) {  
                $attachment_mine = get_post_mime_type($message_mediaid);
                $minearr = explode('/', $attachment_mine);
                $attachment_type = $minearr[0];
             
                if ($attachment_type == 'image') {
                    $image_attributes = wp_get_attachment_image_src( $message_mediaid, 'thumbnail' ); // returns an array
                    // Display image
                    if( $image_attributes ) {
                        $src = isset($image_attributes[0]) ? $image_attributes[0] : "";
                        $width = isset($image_attributes[1]) ? $image_attributes[1] : "";
                        $height = isset($image_attributes[2]) ? $image_attributes[2] : "";
                        $html .= "<img id='jot-image-selected' src='" . $src  . "' width='" . $width . "' height='" . $height . "'>";                    
                    } else {
                        $html .= "<img id='jot-image-selected' src='' style='display:none'>";
                        $html .= "<div id='jot-image-selected-status'>" . __("Image not found","jot-plugin") ."</div>";
                    }
                    $html .= "<input style='display:none' id='jot-media-selected' name='jot-media-selected' maxlength='40'  size='40' type='text' value='' placeholder='' readonly='readonly' />";
                } else {
                    // Display file name for other file types
                    //$attachment_meta = $this->wp_get_attachment($message_mediaid);
                    $filename_only = basename( get_attached_file( $message_mediaid ) ); // Just the file name
                    $html .= "<input id='jot-media-selected' name='jot-media-selected' maxlength='40'  size='40' type='text' value='" . $filename_only . "' placeholder='' readonly='readonly' />";
                    $html .= "<img id='jot-image-selected' src='' style='display:none'>";
                }
            } else {
                $html .= "<img id='jot-image-selected' src='' style='display:none'>";
                $html .= "<div id='jot-image-selected-status'>" . __("No image selected","jot-plugin") . $message_mediaid ."</div>";
            }
            
            
            $html .= "</td>";
            $html .= "</tr>";

            // Add additional fields on Message panel
            $html = apply_filters("jot_render_extension_message_fields",$html,$tab);
           
            $html .= "</table>";
	    
	    return apply_filters( 'jot_render_message_panel', $html);
        } // End render_message_panel()
        
       
        
        
        public function get_recipients_list($grpid) {
            
            
            if (isset($_GET['subtab'])) {
                if ($_GET['subtab'] == 'jottabgroupsend') {
                    $style = "style='display:block'";
                } else {
                    $style = "style='display:none'";
                }
            } else {
                $style = "style='display:block'";
            }
            	    
            
            $smsmessage =  get_option('jot-plugin-messages');            
            $send_method = isset($smsmessage['jot-message-sendmethod']) ? $smsmessage['jot-message-sendmethod'] : "jottabgroupsend";
            if ($send_method !="" && $send_method != 'jottabgroupsend' ) {
                $style = "style='display:none'";
            }
            
        
            $allmembers = $this->get_all_groups_and_members($grpid);
            
	    // Show extended fields?
            $extfield = "";
            $extfield = $this->get_memlist_settings('jot-mem-extfields');
	                
            if (empty($extfield)) {
                $extfield = 'true';
            }
            
            // Remove duplicate numbers?
            $removedupes = "";
            $removedupes = $this->get_memlist_settings('jot-mem-removedupes');
            
            if (empty($removedupes)) {
                $removedupes = 'true';
            }
          
            // Recipients div
            $html = "<div id='jottabgroupsend' $style>"; 
            
            // Recipients parent table
            $html .= "<table id='jot-recip-parent-table' width='100%'>"; 
            $html .= "<tr><td>";
                                     
            // Search filter controls
            $html .= "<table id='jot-recip-groups-table-top' width='100%'>";
            $html .= "<tr class='jot-recip-controls'>";
            $html .= "<th class='jot-td-l' width='50%'>";
            $html .= __("Search message recipients","jot-plugin");
            $html .= "<div class='divider'></div>";
            $html .= "<input type='text' size='40' id='jot-recip-search' value='' placeholder='" . __("Search","jot-plugin"). "'>";
            $html .= "</th>";            
            $html .= "<td class='jot-td-c' width='50%'>";           
            $html .= "<div id='jot-recip-numselected'>" . __("Selected : ","jot-plugin") . "</div>";            
            $html .= "</td>";   
            $html .= "</tr>";
            $html .= "</table>";
            
            $html .= "</td></tr>";
            $html .= "<tr><td>";
            
            $html .= "<div id='jot-recip-div'>";            
            $html .= "<table id='jot-recip-tab' width='100%' >"; 
            $header = "";
            $fillerstyle = "";
            $style = "";
            
            foreach ($allmembers as $member) {
              
                if ($header <>  $member->jot_groupname) {
                    
                    $header = $member->jot_groupname;
                    
                    $html .= "<tr class='jot-recip-group-header'>";
                    $html .= "<td colspan=8 ><input type='checkbox' id='jot-recip-group-select-" . $member->jot_grpid . "' value='true'>";
                    $html .= "<span title='" . sprintf(__("Group %d - %s", "jot-plugin"),$member->jot_grpid, stripslashes($member->jot_groupname))  . "'>" . stripslashes($member->jot_groupname) . "</span>";
		    $html .= "</td>";                  
                    $html .= "</tr>";
                                       
                    $html .=  "<tr class='jot-mem-table-headers' style='display:none;'>" .
                      "<td>" . __("Actions","jot-plugin") . "</td>" .
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
                             "<td class='jot-filler'" . $fillerstyle . "></td>" .
                             "</tr>";
                }                     
                     
                $html .= "<tr class='jot-member' style='display:none;'>";
                $html .= "<td width='5%' class='jot-td-c'>";
		
		// Actions
		$html .= "<input type='checkbox' id='jot-recip-mem-select-" . $member->jot_grpid . "-" . $member->jot_grpmemid . "' value='" . $member->jot_grpid . "-" . $member->jot_grpmemid . "'>";
		$html .= "<div class=\"divider\"></div>";
                $html .= "<a href='#' id='jot-recip-mem-delete-" . $member->jot_grpid . '-' . $member->jot_grpmemid . "'><img src='" . plugins_url( 'images/delete.png', dirname(__FILE__) ) .  "' title='" . __("Remove member from THIS group.","jot-plugin") ."'></a>";
                $html .= "<div class=\"divider\"></div>";
		$html .= "<a href='#' id='jot-recip-mem-deleteall-" . $member->jot_grpid . '-' . $member->jot_grpmemid . "'><img src='" . plugins_url( 'images/trash.png', dirname(__FILE__) ) .  "' title='" . __("Remove member from ALL groups.","jot-plugin") ."'></a>";
                		
		$html .= "</td>";
                $html .= "<td width='10%' title='" . __("Member ID : ","jot-plugin") . $member->jot_grpmemid . ",\n" . __("Date added to group : ","jot-plugin") . $member->jot_grpxrefts . "'>" . $member->jot_grpmemname . "</td>";
                 
               
                $html .= "<td width='10%'>" . $member->jot_grpmemnum . "</td>";
                $html .= "<td width='13%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmememail)   . "</td>";
                $html .= "<td width='25%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmemaddress) . "</td>";
                $html .= "<td width='15%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmemcity)    . "</td>";
                $html .= "<td width='15%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmemstate )  . "</td>";
                $html .= "<td width='7%' class='jot-showextended'". $style . ">" . stripslashes($member->jot_grpmemzip)     . "</td>";
		
		     
		
		$html .= "<td width='75%' class='jot-filler'" . $fillerstyle . "></td>";
                $html .= "</tr>";            
            }
            
            $html .= "</table>"; // end of jot-recip-tab
            $html .= "</div>";
          
            $html .= "<tr><td>";
                                     
            // Search filter controls
            $html .= "<table id='jot-recip-groups-table-bottom' width='100%'>";
           
            $html .= "<tr class='jot-recip-controls'>";            
            $html .= "<td width='33%'>";
            $html .= "<label>";
            $html .= "<input type='checkbox' id='jot-recip-selectall' value='true'>" ;
            $html .= __("Select/Unselect all","jot-plugin");
            $html .= "</label>";
            $html .= "</td>";
            $html .= "<td width='33%'>";
            $html .= "<label>";
            $html .= "<input type='checkbox' id='jot-recip-collapseall' value='true'>" ;
            $html .= __("Collapse/Uncollapse groups","jot-plugin");
            $html .= "</label>";
            $html .= "</td>";
            $html .= "<td width='33%'>";
            $html .= "<label>";
            $html .= "<input id='jot-plugin-group-list[jot-mem-extfields]' name='jot-plugin-group-list[jot-mem-extfield]' type='checkbox' value='' " . checked( $extfield, 'true', false ) . ">" . __("Show extended member information?","jot-plugin");
            $html .= "</label>";
            $html .= "</td>";
            $html .= "</tr>";
            $html .= "</table>";
            
            $html .= "</td></tr>";
          
            
            $html .= "</td></tr>";
            $html .= "</table>"; // end of jot-recip-parent-table
           
            $html .= "</div>";
            
            return $html;
        
        }
        
        function render_messageoptions ($current = 'jottabgroupsend') {
            
            if (isset($_GET['subtab'])) {
                $current = $_GET['subtab'];
            } else {
                $smsmessage =  get_option('jot-plugin-messages');            
                $send_method = isset($smsmessage['jot-message-sendmethod']) ? $smsmessage['jot-message-sendmethod'] : $current;
                if ($send_method != $current ) {
                    $current = $send_method;
                }
                
            }
            $tabs = array( 'jottabgroupsend' => __('Group Send','jot-plugin'), 'jottabquicksend' => __('Quick Send','jot-plugin') );
            $tabs = apply_filters('jot_render_messageoptions', $tabs);
           
            echo '<h2 class="nav-tab-wrapper">';
            foreach( $tabs as $tab => $name ){
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                echo "<a class='jot-subtab nav-tab$class' href='#$tab'>$name</a>";
        
            }
            echo '</h2>';
        }
        
        public function get_quick_send($tab) {
            
            if (isset($_GET['subtab'])) {
                if ($_GET['subtab'] == 'jottabquicksend') {
                    $style = "style='display:block'";
                } else {
                    $style = "style='display:none'";
                }
            } else {
                $style = "style='display:none'";
            }
            
                 
            // Quick Send Div
            $html =  "<div id='jottabquicksend' $style>";
            $html .= "<table id='jot-quicksend-tab' class='jot-formtab form-table'>";
            $html .= $this->render_row('jot-message-quicksend-number','','',$tab);            
            $html .= "</table>";
            $html .= "</div>";
            
            return $html;
        }
        
               
        /**
        * Renders the list of existing groups
        *
        * @return string HTML markup for the field.
        */
        public function render_grouplist () {
             
            //Get group list from database.
                 
            $rows_per_page = 5;
            $current = isset( $_GET['paged'] ) ? abs( (int) $_GET['paged'] ) : 1;
            
            // If passed in URL get grpid
            $jot_grpid = isset($_GET['lastid']) ? $_GET['lastid'] : 0;
                       
            global $wpdb;
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
            
            $grouplist = apply_filters('jot_get_grouplist',$wpdb->get_results( $sql ));
     
            $html = "<div>\n";
            $html .= "<form id='jot-group-list-form' action='' method='post'>";
            $html .= "<input type=\"hidden\"  id=\"jot_grppage\" name=\"jot_grppage\" value=\"" . $current ."\">";
            $html .= "<table id='jot-group-list-tab' class='wp-list-table widefat'>\n";
            $html .= "<thead>";
	    $html .= "<tr>";
	    $html .= "<th class='manage-column column-name'>" . __("Group Name","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Group Desc.","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Auto add.","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Group Opt-out keyword.","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Members","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Created","jot-plugin") . "</th>";
            $html .= "<th width='15%' class='manage-column column-name'>" . __("Actions","jot-plugin") . "</th>";
            $html .= "</tr>\n";
            $html .= "</thead>\n";
            $html .= "<tfoot>";
            $html .= "<tr>";
	    $html .= "<th class='manage-column column-name'>" . __("Group Name","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Group Desc.","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Auto add.","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Group Opt-out keyword.","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Members","jot-plugin") . "</th>";
            $html .= "<th class='manage-column column-name'>" . __("Created","jot-plugin") . "</th>";
            $html .= "<th width='15%' class='manage-column column-name'>" . __("Actions","jot-plugin") . "</th>";
            $html .= "</tr>\n";            
	    $html .= "</tfoot>";
            $html .= "<tbody>\n";
           
	    // Find page for selected group
	    if ($jot_grpid != 0 ) {
		$current = $this->get_page_for_group($jot_grpid,$rows_per_page,$grouplist);
	    }
	   
            $start = ($current - 1) * $rows_per_page;
            $pageend = $start + $rows_per_page;
            $end = (sizeof($grouplist) < $pageend) ? sizeof($grouplist) : $pageend; 
	                
            $pagination = array(               
               'base' => @add_query_arg('paged','%#%'),
               'format' => '?paged=%#%',               
               'show_all'     => False,
               'current'      => $current,
               'end_size'     => 2,
               'mid_size'     => 2,
               'prev_next'    => True,
               'prev_text'    => __('<< Previous','jot-plugin'),
               'next_text'    => __('Next >>','jot-plugin'),               
               'total'      => ceil(sizeof($grouplist)/$rows_per_page)  
            );
            
	    
            
            for ($i=$start;$i < $end ;++$i ) {
                $group = $grouplist[$i];
                if ($jot_grpid == $group->jot_groupid) {
                    $highlightclass = "jot-highlight";
                } else {
                    $highlightclass = "jot-group-list";
                }
		
		$groupdetails = Joy_Of_Text_Plugin()->settings->get_saved_group_details($group->jot_groupid);
		
                $html .= "<tr class='" . $highlightclass . "' id='" . esc_attr( $group->jot_groupid ) . "' title='Group ID : " . esc_attr( $group->jot_groupid ) .  "'>";
                $html .= "<td class='jot-td-l'>" . stripslashes(esc_attr( $group->jot_groupname )) . "</td>";
                $html .= "<td class='jot-td-l'>" . stripslashes(esc_attr( $group->jot_groupdesc )) . "</td>";
                $html .= "<td class='jot-td-l'>" . ($group->jot_groupautosub == 1 ? __('Yes','jot-plugin') : '' ) . "</td>";
                $html .= "<td class='jot-td-l'>" . (stripslashes(esc_attr( $group->jot_groupoptout )) != "" ? stripslashes(esc_attr( $group->jot_groupoptout )) . ",leave all" : "leave all"  ) . "</td>";
                $html .= "<td class='jot-td-l'>" . esc_attr( $group->jot_memcount ) . "</td>";
                $html .= "<td class='jot-td-l'>" . esc_attr( $group->jot_ts ) . "</td>";
                $html .= "<td class='jot-td-l'>" ;
		    if (!isset($groupdetails->jot_virtualgroup)) {
			$html .= "<div class=\"divider\"></div><a href='#' id='jot-grp-delete-" . $group->jot_groupid . "'><img src='" . plugins_url( 'images/delete.png', dirname(__FILE__) ) .  "' title='" . __("Delete Group","jot-plugin") . "'></a> " .
			 "<div class=\"divider\"></div><a href='#' id='jot-grp-mem-add-" . $group->jot_groupid . "'><img src='" . plugins_url( 'images/add.png', dirname(__FILE__) ) .  "' title='" . __("Add Members","jot-plugin") . "'></a>" .
			 "<div class=\"divider\"></div><a href='" .admin_url() . "admin-post.php?action=process_downloadgroup&grpid=" . $group->jot_groupid . "' id='jot-grp-mem-download-" . $group->jot_groupid . "'><img src='" . plugins_url( 'images/download.png', dirname(__FILE__) ) .  "' title='" . __("Download Members","jot-plugin") . "'></a>" .
			 "<div class=\"divider\"></div><a href='#' id='jot-grp-mem-send-" . $group->jot_groupid . "'><img src='" . plugins_url( 'images/send.png', dirname(__FILE__) ) .  "' title='" . __("Send Message To Group Members","jot-plugin") . "'></a>";     
		    }
		$html .= "</td>";               
                $html .= "</tr>\n";       	
            }
            
            $html .= "</tbody>\n";
            $html .= "</table>\n";            
            $html .= "<table class='jot-pagination-tab'>";
            $html .= "<tr><td><div class='jot-paginated-links'>" . paginate_links( $pagination ) . "</div></td></tr>";
            $html .= "</table>";
            $html .= "</form>";
	
            $html .= "</div>\n";
            
            return apply_filters('jot_render_grouplist',$html) ;
        } // End render_grouplist()
        
	
	/*
	 * Renders jump to group drop down on the Group Manager
	 *
	 *
	 */
	public function render_jumptogroup($sections,$tab) {
	    
	    $html = '';
	    
	    $grouparr = Joy_Of_Text_Plugin()->messenger->get_display_groups();	  
			  
	    $disp_grouparr = array(999999 => __("Select a group","jot-plugin"));
	    $jot_groupid = isset($_GET['lastid']) ? $_GET['lastid'] : 999999;
	    foreach ( $grouparr as $grp) {
		$groupid_str = sprintf(__("(Group ID : %s )","jot-plugin"), $grp->jot_groupid);
		$group_auto = ($grp->jot_groupautosub == 1 ? "(Auto)" : ""); 
		$disp_grouparr[$grp->jot_groupid] = $grp->jot_groupname . " " . $groupid_str . " " . $group_auto ;
	    }   
	   
	   
	    $html .= __("Jump to group: ","jot-plugin") . '<select id="jot-plugin-group-list[jot-jumptogroup]" name="jot-plugin-group-list[jot-jumptogroup]">';
            foreach ($disp_grouparr as $k => $v) {		
                $html .= '<option value="' . $k . '"' . selected( strval($k) ,$jot_groupid,false) . '>' . $v . '</option>';
            }
	    $html .= '</select>';
	    
	    return $html;
	    
	}
	
        
        /**
        * Renders page for displaying Add Group panel
        *        
        */
        public function render_groupadd($sections, $tab) {
        
            $html = "<table class=\"jot-formtab form-table\">\n";
            
            $html .= $this->render_row('jot_groupname','','',$tab);
            $html .= $this->render_row('jot_groupdesc','','',$tab);
                                    
            $html .= "</table>";
           
            
            return apply_filters('jot_render_groupadd',$html);
        } // End render_groupadd()
        
        /**
        * Renders page for displaying Add Group panel
        *        
        */
        public function render_memberbulkadd($sections, $tab, $lastid, $paged) {
            
            $filename = "";
            $filecontents = "";
            $error = "";
            $groupdetails = $this->get_group_details($lastid);
            
            $html = "<h3>" .  __("Bulk load new members","jot-plugin") ."</h3>";
            $html .= "<p class='description'>";
            $html .=  __("You can load new members from a text file, or enter their details straight into the text area below.","jot-plugin");
            $html .=  "</p>";
            $html .=  "<p class='description'>";
            $html .=  __("If you are uploading from a file, first upload the file, then press the 'Add Members' button.","jot-plugin");
            $html .=  "</p>";
            
           
            $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=group-list&lastid=" . $lastid . "&subtab=jottabgroupinvite' target='_blank'>" . __("Group Manager-Group Invite","jot-plugin") .  "</a>" ;
            $html .=  "<p class='description'>"; 
            $html .=  sprintf (__("Do you want to send a welcome message to members when they are added to this group? Check your %s settings.","jot-plugin"), $url) ;
            $html .=  "</p>";
            
            
            
            $html .= "<p>";
            $html .=  "<form id=\"jot-group-bulk-add-fields-form\" action=\"\" method=\"post\" enctype='multipart/form-data'>";
            $html .=  "<input type=\"hidden\"  id=\"jot_grpid\" name=\"jot_grpid\" value=\"" . $lastid ."\">";
            $html .=  "<input type=\"hidden\"  id=\"jot_grppage\" name=\"jot_paged\" value=\"" . $paged ."\">";
            settings_fields( 'jot-plugin-settings-' . $tab );
           
            $html .= "<table class=\"jot-formtab form-table\">\n";
            $html .= $this->render_row('jot_bulkaddgrp','',$groupdetails->jot_groupname,$tab);
            
            // Show value of welcome message on ADD
            $html .= "<tr>";
            $html .= "<th>";
            $html .= __("Send welcome message: ","jot-plugin");
            $html .= "</th><td>";
            if (Joy_Of_Text_Plugin()->options->check_welcome_to_be_sent($lastid,'jot_grpinvwelchk_jot_add')) {
               $html .= __("YES - a welcome message will be sent, when the member is added.","jot-plugin");
            } else {
               $html .= __("No","jot-plugin");
            }                   
            $html .= "</td>";
            $html .= "</tr>";  
             
                         
            if(isset($_FILES['jot-mem-bulk-file']['tmp_name'])) {
                
                $allowed =  array('txt','csv');
                $filename = $_FILES['jot-mem-bulk-file']['name'];
                $tmpfilename = $_FILES['jot-mem-bulk-file']['tmp_name'];               
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(!in_array($ext,$allowed) ) {
                    $error = "<div class=\"jot-messagered\"><h4>" . __("File can be type txt or csv only.","jot-plugin") . "</h4></div>";
                } elseif ($tmpfilename) {
                        if ($_FILES['jot-mem-bulk-file']['error'] == UPLOAD_ERR_OK  && is_uploaded_file($tmpfilename)) { 
                            $filecontents = file_get_contents($tmpfilename);
                            $error = "<div class=\"jot-messagegreen\"><h4>" . __("File can uploaded successful. Press 'Add Members' to process.","jot-plugin") . "</h4></div>";
                        }
                }
            }
            
                       
            $html .= "<tr>";
            $html .= "<th>";
            $html .= __("Select a file (optional)","jot-plugin");
            $html .= "</th>";
            $html .= "<td>";
            $html .= "<input type='file' name='jot-mem-bulk-file' id='jot-mem-bulk-file' value='" . $filename . "'>";
            $html .= "<input class='button' type='submit' value='Upload' name='submit'>";
            //$html .= "<a href='#' class='button button-primary' id='jot-mem-bulk-upload'>" . __("Upload","jot-plugin"). "</a>";
            $html .= "</td>";
            $html .= "</tr>";
            
            $html .= $this->render_row('jot_bulkadd','',$filecontents,$tab);
                                    
            $html .= "</table>";
            
            $html .= "<table id='jot-bulk-table'>";
            $html .= "<tr><td>";
            $html .= "<div class='jot-bulk-add-buttons'>";
            $html .= "<a href=\"#\" class=\"button button-primary\" id=\"jot-membulkadd\">" . __("Add members","jot-plugin"). "</a>";
            $html .= "<div class=\"divider\"></div>";
            $html .= "<a href=\"#\" class=\"button button-primary\" id=\"jot-membulkaddcancel\">" . __("Return","jot-plugin"). "</a>"; 
            $html .= "</td><td class='jot-bulk-td'>";
            $html .= "<div id=\"jot-bulk-status\" class='jot-messageblack'></div>";
            $html .= "</td></tr>";
            $html .= "</table>";
            $html .= "</form>";
            $html .= "<br>";
            $html .= "<div id=\"jot-bulkaddstatus-div\">" . $error. "</div>";
           
            
            return apply_filters('jot_render_memberbulkadd',$html);
        } // End render_memberbulkadd
        
        
        /**
        * Renders the list of existing groups
        *
        * @return string HTML markup for the field.
        */
        public function render_messagehistory () {
            //Get group list from database.
          
            
            $rows_per_page = $this->get_hist_settings('jot-memhistdisplay');
           
            if (empty($rows_per_page)){
                $rows_per_page = 50;    
            }
                       
            $current = isset( $_GET['paged'] ) ? abs( (int) $_GET['paged'] ) : 1; 
            
            $histlist = $this->get_history();
                        
            $html = "<div>\n";
            
            // Define Chat History dialog
            $html .= "<div class='jot-chathist' id='jot-chathist'>";
            $html .= "<div id='jot-chat-hist-div' class='jot-chat-hist-div'>";            
            $html .= "</div>";
              
            $html .= "<div class='jot-chat-send-container'>";     
            $html .= "<div class='jot-chat-send-div'>";
            $html .= "<textarea id='jot-chat-send-input' class='jot-chat-send-input'>";
            $html .= "</textarea>";
            $html .= "</div><!-- jot-chat-send-input -->";
            $html .= "<div class='jot-chat-send-button-parent'>";
            $html .= "<span class='jot-helper'></span><a href='#' class='jot-chat-send-button-anchor' id='jot-chat-send-msg'><img src='" . plugins_url( 'images/send32x32.png', dirname(__FILE__) ) .  "' title='Send'></a>";
            $html .= "</div><!-- jot-chat-send-button-parent -->";
            $html .= "</div><!-- jot-chat-send-container -->";
            $html .= "</div><!-- jot-chathist -->";
                        
            $html .= "<input type=\"hidden\"  id=\"jot_histpage\" name=\"jot_histpage\" value=\"" . $current ."\">";
            $html .= "<table id='jot-hist-list-tab' class='wp-list-table widefat'>\n";
            $html .= "<thead>";
            $html .= "<tr class='jot-ignore'>";
            $html .= "<td class='jot-ignore' colspan='2'>";
           
                        
            $html .= __("Display: ","jot-plugin") . '<select id="jot-plugin-message-history[jot-memhistdisplay]" name="jot-plugin-message-history[jot-memhistdisplay]">';
            for($i = 50; $i <= 400; $i += 50) {
                $html .= '<option value="' . $i . '"' . selected( strval($i) ,$rows_per_page,false) . '>' . $i . '</option>';
            }
            
            $html .= '</select>';
            $html .= "<input type='submit' name='submit' id='submit' class='button button-primary' value='" . __("Apply","jot-plugin") . "' />";
                        
            $html .= "</td>";
            
            // Show latest message in each message thread
            $abridgehist = $this->get_hist_settings('jot-hist-abridge');
            $html .= "<td>";
            $html .= "<label for='jot-plugin-message-history[jot-hist-abridge]'>";
            $html .= "<input id='jot-plugin-message-history[jot-hist-abridge]' name='jot-plugin-message-history[jot-hist-abridge]' type='checkbox' value='true'" . checked( $abridgehist, 'true', false ) . "/>";
            $html .= __("Only show most recent message in each thread.","jot-plugin");
            $html .= "</label>";
            $html .= "</td>";
            
            $html .= "<td class='jot-ignore jot-td-r' colspan='5'>";
	    
            if ( current_user_can( 'jot_admin_capability' )) {
		$delete_days = $this->get_hist_settings('jot-memhistdelete');
		if (empty($delete_days)) {
		    $delete_days = 99999999;
		    $this->set_hist_settings('jot-memhistdelete',$delete_days);
		}
		
		$histdeletearr = array("ever" => 99999999,
				       "1 day"=>1,
				       "2 days"=>2,
				       "3 days"=>3,
				       "4 days"=>4,
				       "5 days"=>5,
				       "6 days"=>6,
				       "7 days"=>7,
				       "30 days" => 30,
				       "60 days"=>60,
				       "90 days"=>90,
				       "180 days"=>180 );
		
		$html .= __("Keep history for : ","jot-plugin") . '<select id="jot-plugin-message-history[jot-memhistdelete]" name="jot-plugin-message-history[jot-memhistdelete]">';
		foreach ($histdeletearr as $key => $value) {
		   $html .= '<option value="' . $value  . '"' . selected( $value , $delete_days ,false) . '>' . $key . '</option>';
		}
		
		$html .= '</select>';
	    }
	    
            $html .= "</td>";    
            $html .= "</tr>";
            
            // Filter table
            $html .= "<tr class='jot-ignore'>";
            $html .= "<td class='jot-ignore' width='10%'><input id='jot-plugin-message-history[jot-filter-from]' class='jot-filter-td' name='jot-plugin-message-history[jot-filter-from]' maxlength='40' type='text' value='" . $this->get_filter('jot-filter-from') . "' placeholder='" . __("From filter","jot-plugin") ."'></td>";
            $html .= "<td class='jot-ignore' width='10%'><input id='jot-plugin-message-history[jot-filter-to]' class='jot-filter-td' name='jot-plugin-message-history[jot-filter-to]' maxlength='40' type='text' value='" . $this->get_filter('jot-filter-to') . "' placeholder='" . __("To filter","jot-plugin") ."'></td>";
            $html .= "<td class='jot-ignore' width='30%'><input id='jot-plugin-message-history[jot-filter-message]' class='jot-filter-td' name='jot-plugin-message-history[jot-filter-message]' maxlength='40' type='text' value='" . $this->get_filter('jot-filter-message') . "' placeholder='" . __("Message filter","jot-plugin") ."'></td>";
            $html .= "<td class='jot-ignore' width='10%'></td>";
            $html .= "<td class='jot-ignore' width='10%'>";
            $html .= "<select id='jot-plugin-message-history[jot-filter-type]' name='jot-plugin-message-history[jot-filter-type]'>";
            $messagetypes = $this->get_message_types();
            $html .= '<option value=""'. selected( '' ,$this->get_filter('jot-filter-type'),false) . '>' . __("All","jot-plugin")  . '</option>';
            foreach ($messagetypes as $type) {
                switch ( $type->jot_histmesstype ) {
                    case 'c'; // A call
                        $typestr = __('Call','jot-plugin');
                    break;
                    case 'S'; // An SMS
                        $typestr = __('SMS','jot-plugin');
                    break;
                    case 'M'; // An MMS
                        $typestr = __('MMS','jot-plugin');
                    break;
                    default:
                        
                    break;
                }       
                $html .= '<option value="' . $type->jot_histmesstype . '"' . selected( $type->jot_histmesstype ,$this->get_filter('jot-filter-type'),false) . '>' . $typestr  . '</option>';
            }
            $html .= "</select>";
        
            $html .= "</td>";
            $html .= "<td class='jot-ignore'><input id='jot-plugin-message-history[jot-filter-status]' class='jot-filter-td' name='jot-plugin-message-history[jot-filter-status]' maxlength='40' type='text' value='" . $this->get_filter('jot-filter-status') . "' placeholder='" . __("Status filter","jot-plugin") ."'></td>";
            $html .= "<td class='jot-ignore'><a href='#' class='button' id='jot-filter-clear'>" . __("Clear Filters","jot-plugin") . "</a></td>";
            $html .= "<td class='jot-ignore'><div id=\"jot-applyfilters-status\" class='jot-messageblack'></div></td>";
            $html .= "</tr>";
            
            
	    $html .= "<tr>";
	    $html .= "<th width='10%'>" . __("From","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("To","jot-plugin") . "</th>";
            //$html .= "<th class='manage-column column-name'>" . __("SMS Provider","jot-plugin") . "</th>";
            $html .= "<th width='30%'>" . __("Message","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Media","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Type","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Status","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Date/Time","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Action","jot-plugin") . "</th>";
            $html .= "</tr>\n";
            $html .= "</thead>\n";
            $html .= "<tfoot>";
            $html .= "<tr>";
	    $html .= "<th width='10%'>" . __("From","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("To","jot-plugin") . "</th>";
            //$html .= "<th class='manage-column column-name'>" . __("SMS Provider","jot-plugin") . "</th>";
            $html .= "<th width='30%'>" . __("Message","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Media","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Type","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Status","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Date/Time","jot-plugin") . "</th>";
            $html .= "<th width='10%'>" . __("Action","jot-plugin") . "</th>";
            $html .= "</tr>\n";            
	    $html .= "</tfoot>";
            $html .= "<tbody id='jot-hist-list-tab-body'>\n";
           
            //$current = max(1, get_query_var('page'));
            $start = ($current - 1) * $rows_per_page;
            $pageend = $start + $rows_per_page;
            $end = (sizeof($histlist) < $pageend) ? sizeof($histlist) : $pageend;
            
	    $currurl = admin_url( 'admin.php?page=jot-plugin&tab=message-history');
            $pagination = $this->get_pagination($current, $rows_per_page, $histlist, $currurl);
            
            $html .= $this->get_messagehistory_body($start, $end, $histlist);
                        
            $html .= "</tbody>\n";
            $html .= "</table>\n";            
            $html .= "<table class='jot-pagination-tab'>";
            $html .= "<tr><td><div class='jot-paginated-links' id='jot-hist-list-tab-pagination'>" . $pagination  . "</div></td></tr>";
            $html .= "</table>";
         
            $html .= "</div>\n";
            
            return apply_filters('jot_render_histlist',$html) ;
        } // End render_messagehistory()
        
        
        public function get_pagination($current, $rows_per_page, $histlist,$currurl = null) {
	    
	    if ($currurl == null) {
		$baseurl = $_SERVER["HTTP_REFERER"];
	    } else {
		$baseurl = $currurl;
	    }
	    
            return paginate_links( array(               
               'base' =>  add_query_arg('paged','%#%', $baseurl) ,
               'format' => '?paged=%#%',               
               'show_all'     => False,
               'current'      => $current,
               'end_size'     => 2,
               'mid_size'     => 2,
               'prev_next'    => True,
               'prev_text'    => __('<< Previous','jot-plugin'),
               'next_text'    => __('Next >>','jot-plugin'),               
               'total'      => ceil(sizeof($histlist)/$rows_per_page)  
            ));
        }
        
        public function get_history(){
            
          
            global $wpdb;
            $table = $wpdb->prefix."jot_history";

            $jot_from = $this->get_filter('jot-filter-from');
            if (!empty($jot_from)) {
                $jot_from_clause = " AND jot_histfrom LIKE '%" . $jot_from . "%' ";
                $jot_from_clause .= " OR c.jot_grpmemname LIKE '%" . $jot_from . "%' ";
            } else {
                $jot_from_clause = "";
            }
            
            $jot_to = $this->get_filter('jot-filter-to');
            if (!empty($jot_to)) {
                $jot_to_clause = " AND jot_histto LIKE '%" . $jot_to . "%' ";
                $jot_to_clause .= " OR b.jot_grpmemname LIKE '%" . $jot_to . "%' ";
            } else {
                $jot_to_clause = "";
            }
            
            $jot_message = $this->get_filter('jot-filter-message');
            if (!empty($jot_message)) {
                $jot_message_clause = " AND jot_histmesscontent LIKE '%" . $jot_message . "%' "; 
            } else {
                $jot_message_clause = "";
            }

            $jot_type = $this->get_filter('jot-filter-type');
            if (!empty($jot_type)) {
                // Message type is stored in DB as 'S', 'M' or 'c'
                $jot_type_clause = " AND jot_histmesstype LIKE '%" . $jot_type . "%' ";      
            } else {
                $jot_type_clause = "";
            }
            
            $jot_status = $this->get_filter('jot-filter-status');
            if (!empty($jot_status)) {
                $jot_status_clause = " AND jot_histstatus LIKE '%" . $jot_status . "%' "; 
            } else {
                $jot_status_clause = "";
            }
            
            
            $abridgeval = $this->get_hist_settings('jot-hist-abridge');
            
            if (!empty($abridgeval)) {
                $abridge = $abridgeval;
            } else {
                $abridge = 'false';
            }
                        
            
            if ($abridge == 'false') {
                $tablemems = $wpdb->prefix."jot_groupmembers";
                
                $sql = " SELECT a.jot_histid, a.jot_histsid, a.jot_histfrom, a.jot_histto, a.jot_histprovider, a.jot_histmesscontent, a.jot_histmesstype, " .
                   " a.jot_histstatus,DATE_FORMAT(a.jot_histts,'%m-%d-%Y %T' ) as jot_histts,a.jot_histmedia, b.jot_grpmemname, b.jot_grpmemname, a.jot_histprice, jot_histts as sort_histts, a.jot_histerrcode, a.jot_histmesssubtype " .
                   " FROM " . $table . " a LEFT JOIN " . $tablemems . " b " .
                   " ON a.jot_histto = b.jot_grpmemnum " .
                   " LEFT JOIN " . $tablemems . " c " .
                   " ON a.jot_histfrom = c.jot_grpmemnum " .
                   ' WHERE 1=1 ' .
                   $jot_from_clause .
                   $jot_to_clause .
                   $jot_message_clause .
                   $jot_type_clause .
                   $jot_status_clause .                  
                   " ORDER BY 1 DESC,14 DESC";
                
                /*
		 *    " AND a.jot_histerrcode = 0 " .
                   $sql = " SELECT a.jot_histid, a.jot_histsid, a.jot_histfrom, a.jot_histto, a.jot_histprovider, a.jot_histmesscontent, a.jot_histmesstype, " .
                   " a.jot_histstatus,DATE_FORMAT(a.jot_histts,'%m-%d-%Y %T' ) as jot_histts,a.jot_histmedia, a.jot_histprice, jot_histts as sort_histts " .
                   " FROM " . $table . " a  " . 
                   ' WHERE 1=1 ' .
                   $jot_from_clause .
                   $jot_to_clause .
                   $jot_message_clause .
                   $jot_type_clause .
                   $jot_status_clause .
                   " ORDER BY 12 DESC";
                */   
                   
            } else {
                $tablemems = $wpdb->prefix."jot_groupmembers";
               
                $sql = " SELECT concat(greatest(jot_histfrom, jot_histto) , least(jot_histfrom, jot_histto)) as signature, jot_histid, jot_histsid, jot_histfrom,jot_histto,jot_histprovider, jot_histmesscontent,jot_histmesstype,jot_histstatus,DATE_FORMAT(jot_histts,'%m-%d-%Y %T' ) as jot_histts,jot_histmedia, jot_histprice, jot_histts as sort_histts, jot_histerrcode, jot_histmesssubtype " .
                   " FROM " . $table . " a LEFT JOIN " . $tablemems . " b " .
                   " ON a.jot_histto = b.jot_grpmemnum " .
                   " LEFT JOIN " . $tablemems . " c " .
                   " ON a.jot_histfrom = c.jot_grpmemnum " .
                   ' WHERE 1=1 ' .
                   $jot_from_clause .
                   $jot_to_clause .
                   $jot_message_clause .
                   $jot_type_clause .
                   $jot_status_clause .                   
                   " ORDER BY sort_histts DESC ";
                   $sql = "select * from ($sql) d group by signature order by sort_histts desc";
               
            }            
           
            $result =  $wpdb->get_results( $sql );
            //Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"HISTORY SQL " . $sql . " >" . print_r($result,true));	    
            return $result;
            
        }
        
        public function get_message_types() {
            
            global $wpdb;
            
            $table = $wpdb->prefix."jot_history";
            $sql = " SELECT DISTINCT jot_histmesstype " .
                   " FROM " . $table .
                   " ORDER BY 1";
            //echo ">>" . $sql;
            return $wpdb->get_results( $sql );
            
        }
        
        public function get_messagehistory_body($start, $end, $histlist) {
                        
            $namelist = Joy_Of_Text_Plugin()->messenger->get_all_names();  
            $html = "";
	    
	    for ($i=$start;$i < $end ;++$i ) {
                $hist = $histlist[$i];
                $html .= "<tr class='jot-group-list' data-histid='" . $hist->jot_histid . "' data-histtype='" . $hist->jot_histmesstype . "' data-histstatus='" . $hist->jot_histstatus . "'>";
                $html .= "<td class='jot-td-l' id='" . stripslashes(esc_attr( $hist->jot_histfrom )) . "' title='" . stripslashes(esc_attr( $hist->jot_histfrom )) . "'>" . stripslashes(esc_attr( $this->get_name($hist->jot_histfrom, $namelist) )) . "</td>";
                $html .= "<td class='jot-td-l' id='" . stripslashes(esc_attr( $hist->jot_histto )) . "' title='" . stripslashes(esc_attr( $hist->jot_histto   )) . "'>" . stripslashes(esc_attr( $this->get_name($hist->jot_histto,   $namelist) )) . "</td>";
                if (strlen( $hist->jot_histmesscontent) <=90){
                   $html .= "<td class='jot-td-l' title='" . stripslashes(esc_attr( $hist->jot_histmesscontent )) . "'>" . stripslashes(esc_attr( $hist->jot_histmesscontent )) . "</td>";
                } else {
                   $html .= "<td class='jot-td-l' title='" . stripslashes(esc_attr( $hist->jot_histmesscontent )) . "'>" . substr(stripslashes(esc_attr( $hist->jot_histmesscontent )),0,90) . "...</td>";
                }                
               
                // Media column
                $mediaurl = "";
                $image_attributes = null;
                if (!empty($hist->jot_histmedia)) {
                    switch ( $hist->jot_histmesstype ) {
                    case 'S'; 
                        $mediaurl = "";
                    break;
                    case 'M';
                                                                        
                        $attachment_mine = get_post_mime_type($hist->jot_histmedia);
                        $minearr = explode('/', $attachment_mine);
                        $attachment_type = $minearr[0];
                        
                        if ($attachment_type == 'image') {                                 
                            $image_attributes = wp_get_attachment_image_src( $hist->jot_histmedia, 'thumbnail' ); // returns an array                       
                            $mediaurl = "<a class='jot-histimage' title='" . __("Loading...","jot-plugin") . "' href='" . $image_attributes[0] . "'>" . __("MMS image","jot-plugin") . "</a>";
                        } else {
                            $filename_only = basename( get_attached_file( $hist->jot_histmedia ) ); // Just the file name
                            if (!empty($filename_only)) {
                                $mediaurl =  $filename_only;
                            } else {
                                $mediaurl = __("Media not found","jot-plugin");
                            }
                        }
                        
                    break;
                    case 'c';                        
                        $audio_attributes = wp_get_attachment_url( $hist->jot_histmedia ); // returns URL
                       
                        if( $audio_attributes ) {
                            $filename = basename ( get_attached_file( $hist->jot_histmedia ) );
                            $mediaurl = "<a class='jot-histaudio' title='" . __("Audio file : ","jot-plugin") . $filename . "' href='" . $audio_attributes . "'>" . __("Audio file","jot-plugin") . "</a>";
                        } else {
			    if ($hist->jot_histmedia != 'default') {
				$mediaurl = "Audio not found";
			    } else {
				$mediarul = "";
			    }
                        }        
                    break;
                    default;
                        $jot_messtype = 'Unknown';   ;  
                    break;
                   }           
                    
                }
                
                $html .= "<td class='jot-ignore'>";
                $html .= $mediaurl;
                $html .= "</td>";
               
                // Message type
                switch ( $hist->jot_histmesstype ) {
                    case 'S'; 
                        $jot_messtype = 'SMS';
                    break;
                    case 'M';                        
                        $jot_messtype = 'MMS';        
                    break;
                    case 'c';                        
                        $jot_messtype = 'Call';        
                    break;
                    default;
                        $jot_messtype = 'Unknown';   ;  
                    break;
                }
                
	        $html .= "<td class='jot-td-l' title='" . $this->get_message_type($hist->jot_histmesssubtype)  . "'>" . stripslashes(esc_attr( $jot_messtype ))  . "</td>";
                if (Joy_Of_Text_Plugin()->currentsmsprovidername != 'default') {
                  $messagestatus = Joy_Of_Text_Plugin()->currentsmsprovider->checkstatus($hist->jot_histmesstype,$hist->jot_histsid,$hist->jot_histstatus);
                } else {
                  $messagestatus = "";  
                }
                if ($hist->jot_histprice <> 0) {
                   $messageprice = " title='" . $hist->jot_histprice . " USD'"; 
                } else {
                   $messageprice = ""; 
                }
		if ($hist->jot_histerrcode != 0 ) {
		    // Twilio error codes start at 10000
		    if ($messagestatus >= 10000) {
			$messagestatus = "<a href='https://www.twilio.com/docs/errors/" . $messagestatus . "' target='_BLANK'>" . __("Twilio Error :","jot-plugin") . $messagestatus . "</a>";
		    } 
		}
                $html .= "<td class='jot-td-l'" . $messageprice . ">" . $messagestatus  . "</td>";
                $html .= "<td class='jot-td-l'>" . stripslashes(esc_attr( $hist->jot_histts )) . "</td>";
		if ( current_user_can( 'jot_admin_capability' )) {    
		    $html .= "<td class='jot-td-l'><div class=\"divider\"></div><a href='#' id='jot-hist-delete-" . $hist->jot_histid . "'><img src='" . plugins_url( 'images/delete.png', dirname(__FILE__) ) .  "' title='Delete' class='jot-image'></a></td>";     
		}  else {
		    $html .= "<td><div class=\"divider\"></div></td>";
		}
		$html .= "</tr>\n";       	
            }
            
            if (count($histlist)==0) {
                $html .= "<tr class='jot-group-list'>";
                $html .= "<td colspan='8' class='jot-td-c'>" . __("No history found. Start sending messages!","jot-plugin") . "</td>";
                $html .= "</tr>";
            }
            
            return $html;            
            
        }
        
        
        
        function render_grouplisttabs( $jot_groupid ) {
            if (isset($_GET['subtab'])) {
                $current = $_GET['subtab'];
            } else {
		$current = 'jottabgroupdetails';
	    }
	    
	    $groupdetails = Joy_Of_Text_Plugin()->settings->get_saved_group_details($jot_groupid);
	    
	    if (!isset($groupdetails->jot_virtualgroup)) {
	        $tabs = array( 'jottabgroupdetails' => __('Group Details','jot-plugin'), 'jottabgroupmembers' => __('Member List','jot-plugin'), 'jottabgroupinvite' => __('Group Invite','jot-plugin') );
	    } else {
		$tabs = array( 'jottabgroupdetails' => __('Group Details','jot-plugin') );
	    }
            //if ( current_user_can( 'jot_admin_capability' ) ) {
            //  $tabs['jottabgroupinvite'] = 'Group Invite';
            //}
            
            $tabs = apply_filters('jot_render_grouplisttabs', $tabs);
            echo '<h2 class="nav-tab-wrapper">';
            foreach( $tabs as $tab => $name ){
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                echo "<a class='jot-subtab nav-tab$class' href='#$tab'>$name</a>";
        
            }
            echo '</h2>';
        }
        
                        
        

        /**
        * Renders the details of a selected group
        *
        * @return string HTML markup for the field.
        */
        public function render_groupdetails ($sections, $tab, $lastid) { 
            
	    $groupdetails = $this->get_saved_group_details($lastid);
	  
	    if (!$groupdetails) {
		return "1 Group not found " . $lastid;
	    }
            
            if (isset($_GET['subtab'])) {
                if ($_GET['subtab'] == 'jottabgroupdetails') {
                    $style = "style='display:block'";
                } else {
                    $style = "style='display:none'";
                }
            } else {
                $style = "style='display:block'";
            }
            
            $html = "<div id='jottabgroupdetails' $style>";            
            $html .= "<h3> Group Details - <span id='jot_grptitle'>" . stripslashes($groupdetails->jot_groupname) . " (" . __("Group ID : ","jot-plugin") . $groupdetails->jot_groupid .  ")</span></h3>";
            $html .= "<form id='jot-group-details-form' action='' method='post'>";
            $html .= "<input type=\"hidden\"  id=\"jot_form_id\" name=\"jot_form_id\" value=\"jot-group-details-form\">";
            $html .= "<input type=\"hidden\"  id=\"jot_grpid\" name=\"jot_grpid\" value=\"" . $lastid . "\">";
            $html .= "<table class=\"jot-formtab form-table\">\n";
            
            $html .= $this->render_row('jot_groupnameupd','',$groupdetails->jot_groupname,$tab);
            $html .= $this->render_row('jot_groupdescupd','',$groupdetails->jot_groupdesc,$tab);
	    
	    if (!isset($groupdetails->jot_virtualgroup)) {
		$html .= $this->render_row('jot_groupautosub','',$groupdetails->jot_groupautosub == 1 ? 'true' : 'false',$tab);            
		$html .= $this->render_row('jot_groupoptout','',$groupdetails->jot_groupoptout,$tab);
		$html .= $this->render_row('jot_groupopttxt','',$groupdetails->jot_groupopttxt,$tab);
	    }
            $html .= "</table>";
            $html .= "</form>";
            $html .= "<p>";
	    
	    if (!isset($groupdetails->jot_virtualgroup)) {
		$html .= "<input type=\"button\" id=\"jot-savegrpdetails\" class=\"button\" value=\"Save group details\">";
	    }
	    
	    $html .= "<div id=\"jot-grpdetails-message\"></div>";
            $html .= "</div>";
            
            return apply_filters('jot_render_groupdetails',$html);
        } // End render_groupdetails()

        
        
        /**
        * Renders member list
        *
        * @return string HTML markup for the field.
        */
        public function render_groupmembers($sections, $tab, $grpid) {
            
            $groupdetails = $this->get_saved_group_details($grpid);
	    
	    if (!$groupdetails) {
		return "2 Group not found " . $lastid;
	    }
            
            //Get group member list from database.
            global $wpdb;
            $tablea = $wpdb->prefix."jot_groupmembers";
            $tableb = $wpdb->prefix."jot_groupmemxref";
            $sql = " SELECT a.jot_grpmemid, b.jot_grpid, jot_grpmemname, jot_grpmemnum, jot_grpmememail, jot_grpmemaddress, jot_grpmemcity, jot_grpmemstate, jot_grpmemzip , b.jot_grpxrefts " .
                   " FROM " . $tablea . " a, " . $tableb . " b " .
                   " WHERE b.jot_grpid = " . $grpid .
                   " AND a.jot_grpmemid = b.jot_grpmemid " .                   
                   " ORDER BY 3 ASC";
            
            $groupmembers = $wpdb->get_results( $sql );
	    
	    $groupmembers = apply_filters('jot_render_groupmembers',$groupmembers,$grpid);
            
            if (isset($_GET['subtab'])) {
                if ($_GET['subtab'] == 'jottabgroupmembers') {
                    $style = "style='display:block'";
                } else {
                    $style = "style='display:none'";
                }
            } else {
                $style = "style='display:none'";
            }
            
            if (isset($_GET['paged'])) {
                $paged = $_GET['paged'];
            } else {
                $paged = 1;
            }
            
            // Write main member management form
            $html = "<div id='jottabgroupmembers' $style>";
            $html .= "<h3> Group Details - <span id='jot_grptitle'>" . stripslashes($groupdetails->jot_groupname) . " (" . __("Group ID : ","jot-plugin") . $groupdetails->jot_groupid .  ")</span></h3><br>";
            $html .= '<a href="' . admin_url( 'admin.php?page=jot-plugin&tab=group-list&subform=bulk&lastid='). $grpid . '&paged=' . $paged .'" class="button button-primary" >' .  __("Bulk add members","jot-plugin") . '</a>';
            
            $extfield = "";
            $extfield = $this->get_memlist_settings('jot-mem-extfields');
            
            if (empty($extfield)) {
                $extfield = 'true';
            }
             
            $html .= "<p>";
            $html .= "<table class='jot-tab-group-members'>";
            $html .= "<tr><td colspan=2>";
            $html .= ' <label><input id="jot-plugin-group-list[jot-mem-extfields]" name="jot-plugin-group-list[jot-mem-extfields]" type="checkbox" value="" ' . checked( $extfield, 'true', false ) . '>' . __("Show extended member information?","jot-plugin"). '</label>';
            $html .= "</td></tr>";
            $html .= "<tr><td>";
            $html .= "<p class='description'>";
            $html .= "Add new members or update existing member details.";
            $html .= "</p>";
            $html .= "</td><td>";
            $html .= "<div id=\"jot-messagestatus\"></div>";
            $html .= "</td></tr></table>";           
            
            $html .= "<p>";
            $html .= "<table>";
            $html .= "<tr><td>";
            $html .= __("Bulk Actions","jot-plugin");
            $html .= "</td>";
            
            //Bulk actions
            $html .= "<td>";
            $bulkactionarr = array (
                'noaction' => __("Select action","jot-plugin"),
                'move'     => __("Move","jot-plugin"),
                'copy'     => __("Copy","jot-plugin"),
                'delete'   => __("Delete","jot-plugin"),
                
            );
            $html .=  '<select id="jot-plugin-group-list[jot-bulk-action]" name="jot-plugin-group-list[jot-bulk-action]" class="jot-memlist-selects">';
            foreach ($bulkactionarr as $key => $value) {
               $html .= '<option value="' . $key . '">' . $value . '</option>';
            }            
            $html .= '</select>';            
            $html .= "</td>";
            
            //Divider
            $html .= "<td>";
            $html .= "<div class=\"divider\"></div>";
            $html .= "</td>";
            
            //Get groups
            $html .= "<td>";
            $html .= __(" Group","jot-plugin");
            $grouparr = Joy_Of_Text_Plugin()->messenger->get_display_groups();
            $disp_grouparr = array(0 => __("Select a group","jot-plugin"));
            foreach ( $grouparr as $grp) {
                $disp_grouparr[$grp->jot_groupid] = $grp->jot_groupname;
            }        
                
            $html .=  '<select id="jot-plugin-group-list[jot-target-grpid]" name="jot-plugin-group-list[jot-target-grpid]" class="jot-memlist-selects">';
            foreach ($disp_grouparr as $key => $value) {
               $html .= '<option value="' . $key . '">' . $value . '</option>';
            }            
            $html .= '</select>';            
            $html .= "</td>";
            
            //Apply button
            $html .= "<td>";
            $html .= "<input type=\"button\" id=\"jot-memberlist-bulkapply\" class=\"button\" value=\"Apply\">";
            $html .= "</td>";
            
            $html .= "</tr>";
            $html .= "</table>";                  
                                  
            $html .=  "<form id='jot-group-members-form' action='' method='post'>";
            
            $html .= "<input type=\"hidden\"  id=\"jot_grpid\" name=\"jot_grpid\" value=\"" . $grpid . "\">";
            $html .= "<table id=\"jot-groupmem-tab\" >\n";
            $html .=  "<tr class='jot-mem-table-headers'>";
            $args['value'] = 'false';
            $html .= "<th style='width:50px;' class='jot-td-c'>" . $this->render_field_checkbox("jot-mem-select-all",$args) . "</th>";
            $html .=  "<th>" . __("Member Name","jot-plugin") . "</th>" .
                      "<th>" . __("Phone Number","jot-plugin") . "</th>";
             
            if ($extfield == 'false') {
               $style = " style='display:none'";   
            } else {
               $style = "";
            }
            $html .= "<th" . $style . ">"  . __("Email","jot-plugin") . "</th>" .
                     "<th" . $style . ">" . __("Address","jot-plugin") . "</th>" .
                     "<th" . $style . ">"  . __("City","jot-plugin") . "</th>" .
                     "<th" . $style . ">"  . __("State","jot-plugin") . "</th>" .
                     "<th" . $style . ">"  . __("Zipcode","jot-plugin") . "</th>" .  
                     "<th style='width:82px;'>"  . __("Actions","jot-plugin") . "</th>" ;
                    
                  
            //Member add row
            $html .= "<tr class='jot-member-add'" . " id='" . esc_attr( $grpid ) . "'>";
            $html .= "<th style='width:50px;'>&nbsp;</th>";
            
            $args['value'] = '';
            $args['size'] = 18; // field width
            $html .= "<td class='jot-td-l'>" . $this->render_field_text('jot-mem-add-name', $args )  . "</td>";
            $args['value'] = '';
            $html .= "<td class='jot-td-r'>" . $this->render_field_text('jot-mem-add-num', $args )   . "</td>";
            $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-add-email', $args ) . "</td>";
            $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-add-addr', $args )  . "</td>";
            $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-add-city', $args )  . "</td>";
            $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-add-state', $args )  . "</td>";
            $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-add-zip', $args )  . "</td>";
        
            $html .= "<td class='jot-td-l jot-td-mem-actions'>";
            $html .= "<div class='divider'></div>";
            $html .= "<a href='#' id='jot-mem-new-" . $grpid . "'><img src='" . plugins_url( 'images/add.png', dirname(__FILE__) ) .  "' title='Add new member'></a>";
            $html .= "<div class=\"divider\"></div>";
            $html .= "<a href='#' id='jot-mem-refresh-" . $grpid. "'><img src='" . plugins_url( 'images/refresh.png', dirname(__FILE__) ) .  "' title='Refresh member list'></a>";
            $html .= "</td>";     
            $html .= "</tr>\n";
            
            
            foreach ( $groupmembers as $groupmember ) 
            {
                
                $html .= "<tr class='jot-member-list'>";
                $args['value'] = 'false';
                $html .= "<td  style='width:50px;' class='jot-td-c'>" . $this->render_field_checkbox("jot-mem-select-" . $groupmember->jot_grpmemid,$args) . "</td>";
                
                $args['value'] = $groupmember->jot_grpmemname;
                $html .= "<td class='jot-td-l' title='" . __("Member ID : ","jot-plugin") . $groupmember->jot_grpmemid . ",\n" . __("Date added to group : ","jot-plugin") . $groupmember->jot_grpxrefts .  "'>" . $this->render_field_text('jot-mem-upd-name-'. $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid, $args )  . "</td>";
                
                $args['value'] = $groupmember->jot_grpmemnum;
                $html .= "<td class='jot-td-r'>" . $this->render_field_text('jot-mem-upd-num-'. $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid, $args )  . "</td>";
                
                $args['value'] = $groupmember->jot_grpmememail;
                $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-upd-email-'. $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid, $args )  . "</td>";
                
                $args['value'] = $groupmember->jot_grpmemaddress;
                $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-upd-addr-'. $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid, $args )  . "</td>";
                
                $args['value'] = $groupmember->jot_grpmemcity;
                $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-upd-city-'. $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid, $args )  . "</td>";
                
                $args['value'] = $groupmember->jot_grpmemstate;
                $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-upd-state-'. $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid, $args )  . "</td>";
                                
                $args['value'] = $groupmember->jot_grpmemzip;
                $html .= "<td class='jot-td-r'" . $style . ">" . $this->render_field_text('jot-mem-upd-zip-'. $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid, $args )  . "</td>";
                                
                // Command links                
                $html .= "<td class='jot-td-l'>";
                $html .= "<div class=\"divider\"></div>";
                $html .= "<a href='#' id='jot-mem-save-" . $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid . "'><img src='" . plugins_url( 'images/save.png', dirname(__FILE__) ) .  "' title='" . __("Save","jot-plugin")  . "'></a>";
                $html .= "<div class=\"divider\"></div>";
                $html .= "<a href='#' id='jot-mem-delete-" . $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid . "'><img src='" . plugins_url( 'images/delete.png', dirname(__FILE__) ) .  "' title='" . __("Remove from group.","jot-plugin") ."'></a>";
                $html .= "<div class=\"divider\"></div>";
		$html .= "<a href='#' id='jot-mem-deleteall-" . $groupmember->jot_grpid . '-' . $groupmember->jot_grpmemid . "'><img src='" . plugins_url( 'images/trash.png', dirname(__FILE__) ) .  "' title='" . __("Remove member from ALL groups.","jot-plugin") ."'></a>";
                //$html .= "<div class=\"divider\"></div>";
                $html .= "</td>";     
                $html .= "</tr>\n";
        	
            }
                        
            $html .= "</table>\n";
            $html .= "</form>\n";
            $html .= "</div>\n";
           
            
            return apply_filters('jot_render_groupmembers_html',$html);
        } // End render_groupmembers()
        
        
        
        /**
        * Renders the admin form to construct invites to the groups
        *
        */
                        
        public function render_groupinvites($sections, $tab, $lastid) {
            
            $groupdetails = $this->get_saved_group_details($lastid);	  
	    
	    if (!$groupdetails) {
		return "<br>3 Group not found " . $lastid;
	    }
             
            //Get group invite details from database.
            global $wpdb;
            $groupinvite = "";
             
            $groupinvite = $this->get_group_invite($lastid);
            
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Get group invite " . print_r($groupinvite,true));
            
            if (isset($_GET['subtab'])) {
                if ($_GET['subtab'] == 'jottabgroupinvite') {
                    $style = "style='display:block'";
                } else {
                    $style = "style='display:none'";
                }
            } else {
                $style = "style='display:none'";
            }
	    
	         
            $html = "<div id='jottabgroupinvite' $style>"; 
            $html .= "<h3> Group Details - <span id='jot_grptitle'>" . stripslashes($groupdetails->jot_groupname) . " (" . __("Group ID : ","jot-plugin") . $groupdetails->jot_groupid .  ")</span></h3><br>";
            //$html .= "<h3>" .  __("Group Invite","jot-plugin") ."</h3>";
            $html .= "<p class='description'>";
            $html .= __("You can invite people to join your group using a form or they can send you a text containing a keyword.","jot-plugin");
            $html .= "</p>";
            $html .= "<form id='jot-group-invite-form' action='' method='post'>";
            $html .= "<input type=\"hidden\"  name=\"jot_form_id\" value=\"jot-group-invite-form\">";
            $jot_grpinvaudioid = isset($groupinvite->jot_grpinvaudioid) ? $groupinvite->jot_grpinvaudioid : "";
            $html .= "<input type=\"hidden\"  id=\"jot-plugin-group-list[jot-message-mms-image]\" name=\"jot-plugin-group-list[jot-message-mms-image]\" value=\"" . $jot_grpinvaudioid  . "\">";
            $html .= $this->render_row('jot_grpid','',$lastid,$tab);
            
            $html .= "<table id=\"jot-invite-tab-table\" class=\"jot-formtab form-table\">\n";
            
            $html .= $this->render_section_header(__("Specify a keyword.","jot-plugin"));            
            $html .= $this->render_row('jot_grpinvaddkeyw','',isset($groupinvite->jot_grpinvaddkeyw) ? $groupinvite->jot_grpinvaddkeyw : "" ,$tab);
            
	    $groupinvite_alreadysub = $this->get_groupmeta($lastid,'jot_grpinvalreadysub');
	    $html .= $this->render_row('jot_grpinvalreadysub','',$groupinvite_alreadysub ,$tab);
	    
            
            // Select number for group welcome message responses.
            $html .= $this->render_section_header(__("Select phone number for 'welcome messages' (Optional)","jot-plugin"));
            
            // Show active number
            $current_selected_number = $this->get_current_smsprovider_number();
            $html .= "<tr>";
            $html .= "<th>";
            $html .= __("Default Twilio number:","jot-plugin");
            $html .= "</th><td>";
            if ($current_selected_number == "default") {
               $html .= __("None.","jot-plugin");
            } else {
               $html .= $current_selected_number;
            }                   
            $html .= "</td>";
            $html .= "</tr>";                
            
            $groupinvite_currnumber = $this->get_groupmeta($lastid,'jot_grpinvphonenumber');
            
            if ($groupinvite_currnumber == "") {
                $groupinvite_currnumber = "default";
            }
            $smsprovider_numbers = Joy_Of_Text_Plugin()->currentsmsprovider->getPhoneNumbers();            
            $html .= $this->render_row_multi('jot_grpinvphonenumber','jot_grpinvphonenumber' ,$smsprovider_numbers['all_numbers'], $groupinvite_currnumber, $tab);
                                  
            
            // Render country code for group invites
            $allcountrycodes = array('nocc' => __("No Country Code Selected","jot-plugin")) + $this->get_countrycodes();           
            $currcc = $this->get_groupmeta($lastid,'jot_grpinvcountrycode');
                       
            if ($currcc == "") {
                $this->save_groupmeta($lastid,'jot_grpinvcountrycode','nocc');
                $currcc = $this->get_groupmeta($lastid,'jot_grpinvcountrycode');                
            }
            $html .= $this->render_row_multi('jot_grpinvcountrycode','',$allcountrycodes,$currcc,$tab);
            
	    
            // Tailor the invite form
            $html .= $this->render_section_header(__("Tailor the invitation form.","jot-plugin"));            
            $html .= $this->render_row('jot_grpinvdesc','',isset($groupinvite->jot_grpinvdesc) ? $groupinvite->jot_grpinvdesc : "" ,$tab);
            $html .= $this->render_row('jot_grpinvnametxt','',isset($groupinvite->jot_grpinvnametxt) ? $groupinvite->jot_grpinvnametxt : "",$tab);
            $html .= $this->render_row('jot_grpinvnumtxt','',isset($groupinvite->jot_grpinvnumtxt) ? $groupinvite->jot_grpinvnumtxt : "",$tab);
            
	    // Write form template HTML
	    $confirm_set = Joy_Of_Text_Plugin()->settings->get_groupmeta($lastid,'jot_grpinvconfirm');
	    $all_group_id  = array($lastid);
	    
	    $html .= $this->render_row('jot_grpinvformtxt',
				       '',
				       Joy_Of_Text_Plugin()->shortcodes->get_wrapped_jotform($lastid, $all_group_id, $groupinvite, array(), $confirm_set),
				       $tab);
            
	    // Redirect URL 
            $redirectURL = $this->get_groupmeta($lastid,'jot_grpinvredirect');
	    $html .= $this->render_row('jot_grpinvredirect','',$redirectURL,$tab);
	    
            
            $html .= $this->render_row('jot_grpinvshortcode','','[jotform group_id=' . $lastid . '] or [jotform group_id=' . $lastid . ' name=no]',$tab);           
            
	    
	    // Send confirmation code during subscription checkbox?
	    $html .= $this->render_section_header(__("Send confirmation code.","jot-plugin"));  
	    $confirm = $this->get_groupmeta($lastid,'jot_grpinvconfirm');
	    $html .= $this->render_row('jot_grpinvconfirm','',($confirm == 1 ? 'true' : 'false'),$tab); 	
	    
	    // Send welcome message header 
            $html .= $this->render_section_header(__("Send a welcome message.","jot-plugin"));       
            $grpinvchk = 0;
            if (isset($groupinvite->jot_grpinvretchk)) {
               $grpinvchk =  $groupinvite->jot_grpinvretchk;                
            }
	    
            // Send welcome message?
            $html .= $this->render_row('jot_grpinvretchk','', $grpinvchk == 1 ? 'true' : 'false',$tab);  
            
            // Render welcome message choices            
            // Get existing choices and load into an array of current selections
            $field_id = 'jot_grpinvwelchk';
            $fields = $this->get_settings_fields($tab);            
            $field_args = $fields[$field_id];
            $currselections = array();
            foreach ( $field_args['options'] as $k => $v ) {               
                if ($this->get_groupmeta($lastid,$field_id . '_' . $k) == true ) {
                    $currselections[$k] = true;     
                }
            }            
            $html .= $this->render_row_options('jot_grpinvwelchk', $currselections, $tab);
	                
            // Welcome message text
            $html .= $this->render_row('jot_grpinvrettxt','',isset($groupinvite->jot_grpinvrettxt) ? stripcslashes($groupinvite->jot_grpinvrettxt) : "",$tab);
             
            // Message type
            if (isset($groupinvite->jot_grpinvmesstype)) {
                switch ( $groupinvite->jot_grpinvmesstype ) {
                    case 'S'; 
                        $jot_messtype = 'jot-sms';
                    break;
                    case 'M';                        
                        $jot_messtype = 'jot-mms';        
                    break;
                    case 'c';                        
                        $jot_messtype = 'jot-call';        
                    break;
                    default;
                        $jot_messtype = 'jot-sms';   
                    break;
                }
            } else {
                $jot_messtype = 'jot-sms'; 
            }
           
            $html .= $this->render_row('jot_grpinvmesstype','',$jot_messtype,$tab);
            
            // Text-to-Voice and Audio file
            $html .= $this->render_row_multi('jot_grpinvaudioid','',$this->get_audio_media(),isset($groupinvite->jot_grpinvaudioid) ? $groupinvite->jot_grpinvaudioid : "",$tab);
           
            // MMS image fields
            $html .= "<tr id='jot-message-mms-tr' style='display:none'>";
            $html .= "<th>";
            $html .= "Choose MMS media";
            $html .= "</th><td>";
            $html .= "<input type='button' name='jot-upload-btn' id='jot-upload-btn' class='button-secondary' value='Select MMS media'>";            
            $html .= "</td>";
            $html .= "</tr>";
            
            $html .= "<tr style='display:none'>";
            $html .= "<th>";
            $html .= "Selected MMS media";
            $html .= "</th><td>";
            
            $image_attributes = null;
            //echo "Media ID : " . $groupinvite->jot_grpinvaudioid;
            if (!empty($groupinvite->jot_grpinvaudioid)) {                
               
                $attachment_mine = get_post_mime_type($groupinvite->jot_grpinvaudioid);
                $minearr = explode('/', $attachment_mine);
                $attachment_type = $minearr[0];
             
                if ($attachment_type == 'image') {
                    $image_attributes = wp_get_attachment_image_src( $groupinvite->jot_grpinvaudioid, 'thumbnail' ); // returns an array
                    // Display image
                    if( $image_attributes ) {
                        $src = isset($image_attributes[0]) ? $image_attributes[0] : "";
                        $width = isset($image_attributes[1]) ? $image_attributes[1] : "";
                        $height = isset($image_attributes[2]) ? $image_attributes[2] : "";
                        $html .= "<img id='jot-image-selected' src='" . $src  . "' width='" . $width . "' height='" . $height . "'>";                    
                    } else {
                        $html .= "<img id='jot-image-selected' src='' style='display:none'>";
                        $html .= "<div id='jot-image-selected-status'>" . __("Image not found","jot-plugin") ."</div>";
                    }
                    $html .= "<input style='display:none' id='jot-media-selected' name='jot-media-selected' maxlength='40'  size='40' type='text' value='' placeholder='' readonly='readonly' />";
                } else {
                    // Display file name for other file types                    
                    $filename_only = basename( get_attached_file( $groupinvite->jot_grpinvaudioid ) ); // Just the file name
                    $html .= "<input id='jot-media-selected' name='jot-media-selected' maxlength='40'  size='40' type='text' value='" . $filename_only . "' placeholder='' readonly='readonly' />";
                    $html .= "<img id='jot-image-selected' src='' style='display:none'>";
                }
            } else {
                $html .= "<img id='jot-image-selected' src='' style='display:none'>";
                $html .= "<div id='jot-image-selected-status'>" . __("No image selected","jot-plugin")  ."</div>";
            }
            
            
            $html .= "</td>";
            $html .= "</tr>";
            
            
            $html .= "</table>";           
            $html .= "</form>";
            $html .= "<p>";
            $html .= "<input type=\"button\" id=\"jot-saveinvite\" class=\"button\" value=\"Save invite details\">";
            $html .= "<div id=\"jot-invite-message\"></div>";
            $html .= "</div>";
            
            return apply_filters('jot_render_groupinvites',$html);
        } // End render_groupinvites()
        
        /******************************************************************************************
        *******************************************************************************************
        **                                                                                       **
        **                           H E L P E R   F U N C T I O N S                             **
        **                                                                                       ** 
        *******************************************************************************************
        *******************************************************************************************/
        
	public function  get_saved_group_details($jotgroupid) {
	    
	   // Get group details from transient if set
            if ( false === ( $groupdetails = get_transient( 'jot_groupdetails' ) ) ) {
                // data wasn't saved so regen and save the transient
                $groupdetails = $this->get_group_details($jotgroupid);
                set_transient( 'jot_groupdetails', $groupdetails, 20 );
            }
	    
	    // if saved details are for a different group, then fetch them again.
	    if ($groupdetails->jot_groupid != $jotgroupid) {
		$groupdetails = $this->get_group_details($jotgroupid);
                set_transient( 'jot_groupdetails', $groupdetails, 20 );
	    }
	    
	    return $groupdetails;
	    
	}
	
        public function wp_get_attachment( $attachment_id ) {
            $attachment = get_post( $attachment_id );
            return array(
                    'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
                    'caption' => $attachment->post_excerpt,
                    'description' => $attachment->post_content,
                    'href' => get_permalink( $attachment->ID ),
                    'src' => $attachment->guid,
                    'title' => $attachment->post_title
            );
        }
        
        public function get_name($number, $namelist) {
            
            if (isset($namelist[$number] )) {
                return $namelist[$number] ;
            } else {
                return $number;
            }
        }
        
        
        public function get_value ( $key, $default, $section ) {
            $response = false;
            $values = get_option( 'jot-plugin-' . $section, array() );
            if ( is_array( $values ) && isset( $values[$key] )  ) {
                $response = $values[$key];
            } else {
                $response = $default;
            }
            //if (empty($reponse)) {
            //    $response = $default;
            //}
             
            
            $response = stripslashes($response);
        return apply_filters('jot_get_value',$response);
        } // End get_value()
        
        
	//
	// No longer used
	//
	/*
	public function get_grouplist($grpid = "") {
        
            if (!empty($grpid)) {
                $grpclause = " AND b.jot_grpid = " . $grpid;
            } else {
                $grpclause = "";
            }
        
            //Get group list from database for groups with 1 or more member
            // Do not select opted-out members
            global $wpdb;
              
            $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
            $tablexref = $wpdb->prefix."jot_groupmemxref";   // b
            $tablegrps = $wpdb->prefix."jot_groups"; //c    
            $sql = "SELECT  c.jot_groupname, c.jot_groupid, b.jot_grpmemid, a.jot_grpmemname, a.jot_grpmemnum  " . 
		" FROM " . $tablegrpmem .  " a," . $tablexref . " b, " . $tablegrps . " c " . 
		" WHERE a.jot_grpmemid = b.jot_grpmemid " .
		" AND b.jot_grpid = c.jot_groupid " .
                $grpclause .
                " ORDER BY 1,4";
           
            $groups = $wpdb->get_results( $sql );
            $grouparr = array();
            $groupmemarr = array();
            
            $i=0;
            foreach ($groups as $group){
                
                if ($i==0) {
                    $currkey =  $group->jot_groupname;                    
                }
               
                if ($currkey != $group->jot_groupname ) {
                    
                                                                  
                    $grouparr[$currkey] = $groupmemarr;
                    unset($groupmemarr);
                    $groupmemarr = array();
                    $currkey = $group->jot_groupname;
                    $groupmemarr[] = array("id"=>$group->jot_groupid . "-" . $group->jot_grpmemid, "value"=> $group->jot_grpmemname . " (" . $group->jot_grpmemnum . ")"); 
                }  else {
                    $groupmemarr[] = array("id"=>$group->jot_groupid . "-" . $group->jot_grpmemid, "value"=> $group->jot_grpmemname . " (" . $group->jot_grpmemnum . ")"); 
                }                
                $i++;
                
            }
            // Catch last group
            $grouparr[$currkey] = $groupmemarr;
           
            if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Get groups output " . print_r($grouparr, true));
           
            return apply_filters('jot_get_grouplist',$grouparr);
        }
	*/
        
        
        public function get_audio_media() {
        
            $allaudio['default'] = __("No audio file selected","jot-plugin");            
            
            
            $args = array
            (
                'post_type' => 'attachment',
                'post_mime_type' => 'audio',
                'numberposts' => -1
            );
            $audiofiles = get_posts($args);
           
            foreach ($audiofiles as $file) {                 
                 $allaudio[$file->ID] = $file->post_title;
	    } 
            
            
            return apply_filters('jot_get_audio_media',$allaudio);
        }
        
        public function get_member($jotmemid) {
        
            //Get member details for given memberid
            global $wpdb;
            
            $table_members = $wpdb->prefix."jot_groupmembers";
            $sql = " SELECT jot_grpmemid, jot_grpmemname, jot_grpmemnum " .
                   " FROM " . $table_members  .
                   " WHERE jot_grpmemid =" . $jotmemid;
                       
            $member = $wpdb->get_row( $sql );
            $memarr = array("jot_grpmemid" => $member->jot_grpmemid, "jot_grpmemname" => $member->jot_grpmemname, "jot_grpmemnum" => $member->jot_grpmemnum );
                          
            return apply_filters('jot_get_jot_member',$memarr);
        }
        
        public function get_group_invite($jotgrpid) {
            
            global $wpdb;
            
            $table = $wpdb->prefix."jot_groupinvites";
            $sql = " SELECT jot_grpid, jot_grpinvdesc, jot_grpinvnametxt, jot_grpinvnumtxt, jot_grpinvretchk, jot_grpinvrettxt, jot_grpinvaddkeyw, jot_grpinvmesstype, jot_grpinvaudioid " .
                   " FROM " . $table .
                   " WHERE jot_grpid = %d ";
            
            $sqlprep = $wpdb->prepare($sql, $jotgrpid);           
            $groupinvite = $wpdb->get_row( $sqlprep );
            
            return $groupinvite;
            
        }
        
        public function get_group_details($jotgrpid) {
            
	    if (!$jotgrpid) {
		return;
	    }
	    
            //Get group list from database.
            global $wpdb;
            $table = $wpdb->prefix."jot_groups";
            $sql = " SELECT jot_groupid, jot_groupname,jot_groupdesc, jot_groupoptout, jot_groupopttxt, jot_groupautosub, jot_ts " . 
                   " FROM " . $table .
                   " WHERE jot_groupid = " . $jotgrpid;
            
            $sql = apply_filters('jot_get_group_details_sql',$sql, $jotgrpid);
            
            $groupdetails = $wpdb->get_row( $sql );    
	
            return apply_filters('jot_get_group_details',$groupdetails,$jotgrpid);
            
        }
        
        
        public function process_filter_history() {
            
            $formdata = $_POST['formdata'];    
            $jot_filter_from    = isset($formdata['jot-filter-from'])    ? $formdata['jot-filter-from'] : "" ;
            $jot_filter_to      = isset($formdata['jot-filter-to'])      ? $formdata['jot-filter-to'] : "";
            $jot_filter_message = isset($formdata['jot-filter-message']) ? $formdata['jot-filter-message'] : "";
            $jot_filter_type    = isset($formdata['jot-filter-type'])    ? $formdata['jot-filter-type'] : "";
            $jot_filter_status  = isset($formdata['jot-filter-status'])  ? $formdata['jot-filter-status'] : "";
            $jot_histpage = 1; //Always reset pagination back to first page when applying new filter
            
            
            // Remember all the filter values
            $this->set_filters($formdata);
            
            // Run the query to get the history with the filters 
            $histlist = $this->get_history();
            
            // Calculate pagination parameters
            $rows_per_page = $this->get_filter('jot-memhistdisplay');
            
            if (empty($rows_per_page)){
                $rows_per_page = 50;    
            }
             
            //$start = ($jot_histpage - 1) * $rows_per_page;
            $start = 0;
            $pageend = $start + $rows_per_page;
            $end = (sizeof($histlist) < $pageend) ? sizeof($histlist) : $pageend; 
            
            // Get the body of the history list
            $histlistbody = $this->get_messagehistory_body($start, $end, $histlist);
            //$histlistbody = json_encode($histlist);
                      
            // Sort out the pagination
	    $currurl = admin_url( 'admin.php?page=jot-plugin&tab=message-history');
            $pagination = $this->get_pagination($jot_histpage, $rows_per_page, $histlist.$currurl); 
            
            
            echo json_encode($histlistbody);
            die();
            
        }
     
        public function process_reset_filters() {
            
            $formdata = $_POST['formdata'];
            $this->set_filters($formdata);
            
        }
     
        /**
        * Sets the history tab filters
        *
        */
        public function set_filters($formdata) {
            $histfilters =  get_option('jot-plugin-message-history');
            $histfilters['jot-filter-from']    = isset($formdata['jot-filter-from'])    ? $formdata['jot-filter-from'] : "" ;
            $histfilters['jot-filter-to']      = isset($formdata['jot-filter-to'])      ? $formdata['jot-filter-to'] : "";
            $histfilters['jot-filter-message'] = isset($formdata['jot-filter-message']) ? $formdata['jot-filter-message'] : "";
            $histfilters['jot-filter-type']    = isset($formdata['jot-filter-type'])    ? $formdata['jot-filter-type'] : "";
            $histfilters['jot-filter-status']  = isset($formdata['jot-filter-status'])  ? $formdata['jot-filter-status'] : "";
            
            $jot_filter_from = isset($formdata['jot-filter-from']) ? $formdata['jot-filter-from'] : "";
            $jot_filter_to = isset($formdata['jot-filter-to']) ? $formdata['jot-filter-to'] : "";
            $jot_filter_message = isset($formdata['jot-filter-message']) ? $formdata['jot-filter-message'] : "";
            $jot_filter_type = isset($formdata['jot-filter-type']) ? $formdata['jot-filter-type'] : "";
            $jot_filter_status = isset($formdata['jot-filter-status']) ? $formdata['jot-filter-status'] : "";
            update_option('jot-plugin-message-history',$histfilters);
        }
        
        /**
        * Gets the given history tab filter
        *
        */
        public function get_filter($filtername) {
            
            $histfilter =  get_option('jot-plugin-message-history');
            $retval = isset($histfilter[$filtername]) ? $histfilter[$filtername] : "";   
            return $retval;
        }
        
        public function delete_history() {
            
            global $wpdb;
            
            // Get period for deletion
            $delete_days = $this->get_hist_settings('jot-memhistdelete');
            $rowsdeleted = 0;
        
            if (empty($delete_days)) {
                $delete_days = 99999999;
                $this->set_hist_settings('jot-memhistdelete',$delete_days);
            }
            
            if ($delete_days != 99999999) {
                //Get group list from database.
                
                $table = $wpdb->prefix."jot_history";
                $sql = " DELETE " . 
                       " FROM " . $table .
                       " WHERE jot_histts < DATE_SUB(NOW(), INTERVAL %d DAY)";
                
                $sqlprep = $wpdb->prepare( $sql,
                                $delete_days);
                
                
                $rowsdeleted = $wpdb->query( $sqlprep );
                Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Deleting history. Rows deleted :" . $rowsdeleted . " Delete days : " . $delete_days);
                
            }
            return $rowsdeleted;
        }
        
        public function delete_messagequeue() {
            
            global $wpdb;
            
            $delete_days = 14;
                
            $table = $wpdb->prefix."jot_messagequeue";
            $sql = " DELETE " . 
                   " FROM " . $table .
                   " WHERE jot_messqts < DATE_SUB(NOW(), INTERVAL %d DAY)";
                
            $sqlprep = $wpdb->prepare( $sql,$delete_days);
                
            $rowsdeleted = $wpdb->query( $sqlprep );
            Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"Deleting message queue. Rows deleted :" . $rowsdeleted . " Delete days : " . $delete_days);
         
            return $rowsdeleted;
        }
        
        public function process_history_deletions() {
            $formdata = $_POST['formdata'];    
            $jot_histdelete = $formdata['jot_histdelete'];
            $this->set_hist_settings('jot-memhistdelete',$jot_histdelete);
            
            $rowsdeleted = $this->delete_history();
            $response = array('status'=> 'saved history delete : ' . $jot_histdelete . " " . $rowsdeleted);
            echo json_encode($response);
            wp_die();
        }
        
         public function get_all_groups_and_members($grpid = "") {
	    
	    if (!is_array($grpid) && $grpid !="") {
		$grpid = array($grpid);
	    }
	    
            if (!empty($grpid)) {		
                $grpclause = " AND b.jot_grpid IN (" . implode( ", ", $grpid ) . ")";
            } else {
                $grpclause = "";
            }
        
            //Get group list from database for groups with 1 or more member
            // Do not select opted-out members
            global $wpdb;
              
            $tablegrpmem = $wpdb->prefix."jot_groupmembers"; // a
            $tablexref = $wpdb->prefix."jot_groupmemxref";   // b
            $tablegrps = $wpdb->prefix."jot_groups"; //c    
            $sql = "SELECT  c.jot_groupname, b.jot_grpid, b.jot_grpmemid, a.jot_grpmemname, a.jot_grpmemnum, jot_grpmememail, jot_grpmemaddress, jot_grpmemcity, jot_grpmemstate, jot_grpmemzip, b.jot_grpxrefts  " . 
		" FROM " . $tablegrpmem .  " a," . $tablexref . " b, " . $tablegrps . " c " . 
		" WHERE a.jot_grpmemid = b.jot_grpmemid " .
		" AND b.jot_grpid = c.jot_groupid " .               
                $grpclause .
                " ORDER BY 1,4";
		
            $groups = $wpdb->get_results( $sql );
            
            return apply_filters('jot_get_all_groups_and_members',$groups,$grpid);
         }
         
         public function save_groupmeta($jot_grpid, $key, $value) {
		    global $wpdb;
		    
		    // Check if key exists already for this group
		    $table = $wpdb->prefix."jot_groupmeta";
		    $sql = " SELECT jot_groupmetaid " .
		    " FROM " . $table .
		    " WHERE jot_groupid  = %d " .
		    " AND jot_groupmetakey = %s";
		    
		    $sqlprep = $wpdb->prepare($sql, $jot_grpid, $key );
                      
		    $key_exists =$wpdb->get_col($sqlprep); 
		     
		    if ( $key_exists ) {
			    $type = "update";
			     $data = array(
			    'jot_groupid'       => $jot_grpid,        
			    'jot_groupmetakey'  => $key,
			    'jot_groupmetaval'  => $value                             
			    );			    
			    
			    $success=$wpdb->update( $table, $data, array( 'jot_groupid' => $jot_grpid, 'jot_groupmetakey' => $key ) );
			    
		    } else {
			    $type = "insert";
			    $data = array(
			    'jot_groupid'       => $jot_grpid,        
			    'jot_groupmetakey'  => $key,
			    'jot_groupmetaval'  => $value                             
			    );
			    $success=$wpdb->insert( $table, $data );
			    
		    }
		    if ($success === false) {
			 $errorcode = 999;			 
		    } else {
			 $errorcode = 0;
		    }
	            $response = array('errorcode' => $errorcode, 'errormsg' => '', 'sqlerr' => $wpdb->last_error, 'success' => $success, 'type' => $type);
		    return $response;
		    
	    }
	       
	    public function get_groupmeta($jot_grpid, $key) {
                global $wpdb;
		    
		$table = $wpdb->prefix."jot_groupmeta";
		$sql = " SELECT jot_groupmetaval " .
		    " FROM " . $table .
		    " WHERE jot_groupid  = %d " .
		    " AND jot_groupmetakey = %s";
		    
                $sqlprep = $wpdb->prepare($sql, $jot_grpid, $key );
		$metaval = $wpdb->get_col($sqlprep);
		    
		$retval = isset($metaval[0]) ? $metaval[0] : "";
		   		
		return $retval;
		    
	    }
        
            public function check_save_error($savereturn,$errmdg) {
			
		if ($savereturn['errorcode'] != 0) {
		   $savereturn['errormsg'] = $errmsg;
		   echo json_encode($savereturn);
		   wp_die();		
		}
			
	    }
            
	    public function get_message_type($message_type_code) {
		
		/*	AA - Auto group
		*       AS - Already subscribed	
		*	CA - Call message
		*	CG - Command group
		*	CM - Command member
		*	CS - Command single
		*	GO - Group opt-out
		*	KM - Kiosk message
		*	NG - Notification Group
		*	QM - Queue message
		*	RM - Routed message
		*	SC - Subscription Command
		*	SM - Scheduled message
		*	SN - Subscriber	Notification
		*	SO - Shortcode opt-out
		*	TU - Text Us
		*	US - Unsubscription Notification
		*	WA - Woocommerce Admin
		*	WC - Woocommerce Customer
		*	WM - Welcome message
		*/
		switch ( $message_type_code ) {
                    case 'AA'; 
                        $return_message_type = 'Auto Add Group.';
                    break;
		    case 'AS';
			$return_message_type = 'Already subscribed to group';
		    break;
		    case 'CA'; 
                        $return_message_type = 'Call Message.';
                    break;
		    case 'CG'; 
                        $return_message_type = 'Command - Group Send.';
                    break;
		    case 'CM'; 
                        $return_message_type = 'Command - Send to member ID.';
                    break;
		    case 'CS'; 
                        $return_message_type = 'Command - Send to number.';
                    break;
		    case 'GO'; 
                        $return_message_type = 'Group Opt-Out.';
                    break;
		    case 'KM'; 
                        $return_message_type = 'Kiosk Message.';
                    break;
		    case 'NG'; 
                        $return_message_type = 'Group Post Notification.';
                    break;
		    case 'QM'; 
                        $return_message_type = 'Group Send/Quick Send Message.';
                    break;
		    case 'RC'; 
                        $return_message_type = 'Received Message.';
                    break;
		    case 'RM'; 
                        $return_message_type = 'Routed Inbound Message.';
                    break;
		    case 'SC'; 
                        $return_message_type = 'Subscription Manager Message';
                    break;
		    case 'SM'; 
                        $return_message_type = 'Scheduled Message';
                    break;
		    case 'SN'; 
                        $return_message_type = 'New Subscriber Notification Message';
                    break;
		    case 'TU'; 
                        $return_message_type = 'Textus Shortcode Message.';
                    break;
		    case 'US'; 
                        $return_message_type = 'Unsubscription Notification.';
                    break;
		    case 'WA'; 
                        $return_message_type = 'Woocommerce Admin Message.';
                    break;
		    case 'WC'; 
                        $return_message_type = 'Woocommerce Customer Message.';
                    break;
		    case 'WM'; 
                        $return_message_type = 'Welcome Message.';
                    break;
                    default;
                        return $message_type_code . " " . __("Not found.","jot-plugin");  
                    break;
                }
	    
	        return apply_filters('jot_get_message_type', $return_message_type);
	    }
	
	public function get_page_for_group($jot_grpid,$rows_per_page,$grouplist) {
	
	    $page = 1;
	    foreach ($grouplist as $key => $group) {
		if ($group->jot_groupid == $jot_grpid) {
		    $page = floor($key/$rows_per_page) + 1;		   
		    break;
		}
	    }
	    
	    return $page;
	    
	}
	
	public function filter_pagination_url($link) {	    
	    return  filter_input( INPUT_GET, 'lastid' ) ? remove_query_arg( 'lastid', $link ) : $link;
	}
	
	
        /******************************************************************************************
        *******************************************************************************************
        **                                                                                       **
        **                  F I E L D  R E N D E R I N G  F U N C T I O N S                      **
        **                                                                                       ** 
        *******************************************************************************************
        *******************************************************************************************/
        
        /**
        * Render a field of a given type.
        * @param array $args The field parameters.
        * @return void
        */
        public function render_field ( $args ) {
            $html = '';
                       
            
            // Make sure we have some kind of default, if the key isn't set.
            if ( ! isset( $args['default'] ) ) {
                $args['default'] = '';
            }
            
            // Default to text field if not set
            if (!isset($args['type']) ) {
                $html .= __("Field type not set","jot-plugin");
                return $html;
            }
            
            $method = 'render_field_' . $args['type'];
            if ( ! method_exists( $this, $method ) ) {
                $method = 'render_field_text';
            }
            // Construct the key.
            $key = Joy_Of_Text_Plugin()->token . '-' . $args['section'] . '[' . $args['id'] . ']';
            $method_output = $this->$method( $key, $args );
            if ( is_wp_error( $method_output ) ) {
                // if ( defined( 'WP_DEBUG' ) || true == constant( 'WP_DEBUG' ) ) print_r( $method_output ); // Add better error display.
            } else {
                $html .= $method_output;
            }
            // Output the description
            
            if ( isset( $args['description'] ) ) {
                $description  = '<p class="description">' . wp_kses_post( $args['description'] ) ;
                // Hack to allow extra markup to be added after description. 
                if ( isset( $args['markup'] ) ) {                
                    $description .=  '(' . $args['markup'] . ')' ;
                }
		if ( isset( $args['markuplink'] ) ) {                
                    $description .=   $args['markuplink'] ;
                }  
                $description .= '</p>' . "\n";
                               
                $html .= $description;
            }
            
            if (isset($args['display'])) {
                if ($args['display']=='echo' ) {
                    echo $html;
                } else {
                    return $html;
                }
            } else {
                return $html;
            }
        } // End render_field()
        
        /**
        * Render HTML markup for the "text" field type.
        *
        */
        protected function render_field_text ( $key, $args ) {
            if (isset($args['maxlength'])) {
                $maxlength = " maxlength='" . $args['maxlength']. "' ";
            }  else {
                $maxlength = " maxlength='40' ";
            }
            
            if (isset($args['size'])) {
                $size = $args['size'];
            }  else {
                $size = 40;
            }
            
            if (isset($args['placeholder'])) {
                    $placeholder = $args['placeholder'];
            } else {
                    $placeholder = "";
            }
            
            if (isset($args['readonly'])) {
                    $readonly = 'readonly="readonly" ';
            } else {
                    $readonly = "";
            }           
            
            //$html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" '  . $maxlength .' size="40" type="text" value="' . esc_attr( $this->get_value( $args['id'], $args['value'] , $args['section'] ) ) . '" />' . "\n";
              $html = '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" '  . $maxlength .' size="' . $size . '" type="text" value="' . esc_attr( stripslashes($args['value']) ) . '" placeholder="' . $placeholder . '" ' . $readonly . '/>' . "\n";
                 
            return apply_filters('jot_render_field_text',$html);
        } // End render_field_text()
        
        /**
        * Render HTML markup for the "text" field type.
        *
        */
        protected function render_field_textvalue ( $key, $args ) {
                                       
              $html =  "<span id='" . esc_attr( $key ) . "'>" . esc_attr( $args['value'] ) . "</span>";
                 
            return apply_filters('jot_render_field_textvalue',$html);
        } // End render_field_text()
        
        
        /**
        * Render HTML markup for the hidden field type.
        *
        */
        protected function render_field_hidden ( $key, $args ) {
            
            $html = '<input id="' . esc_attr( $key ) . '" type="hidden" ' .' name="' . esc_attr( $key ) . '" value="' . esc_attr( $args['value']  ) . '" />' . "\n"; 
                
        return apply_filters('jot_render_field_hidden',$html);
        } // End render_field_text()
        
        
        /**
        * Render HTML markup for the "radio" field type.
        *
        */
        protected function render_field_radio ( $key, $args ) {
          
            $html = '';
            if ( isset( $args['options'] ) && ( 0 < count( (array)$args['options'] ) ) ) {
                $html = '';
                $html .= '<div id="container-' . $key . '">';
                foreach ( $args['options'] as $k => $v ) {
                    $html .= '<input type="radio" name="' . esc_attr( $key ) . '" value="' . esc_attr( $k ) . '"' . checked( $args['value'], $k, false ) . ' /> ' . esc_html( $v ) . "<span class='divider'></span>";
                }
                $html .= '</div>';
            }
            return apply_filters('jot_render_field_radio',$html);
        } // End render_field_radio()
        
        
        /**
        * Render HTML markup for the "textarea" field type.
        *
        */
        protected function render_field_textarea ( $key, $args ) {
                
                if (isset($args['maxlength'])) {
                    $maxlength = " maxlength='" . $args['maxlength']. "' ";
                }  else {
                    $maxlength = " ";
                }
                if (isset($args['cols'])) {
                    $cols = $args['cols'];
                } else {
                    $cols = 40;
                }
                if (isset($args['rows'])) {
                    $rows = $args['rows'];
                } else {
                    $rows = 5;
                }
                
                if (isset($args['wrap'])) {
                    $wrap = " wrap='off' ";
                } else {
                    $wrap = "";
                }
        
                if (isset($args['placeholder'])) {
                    $placeholder = $args['placeholder'];
                } else {
                    $placeholder = "";
                }
                
                if (isset($args['readonly'])) {
                    $readonly = ' readonly ';
                } else {
                    $readonly = "";
                }       
                 
                //$html = '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" cols="' . $cols . '" rows="' . $rows. '" placeholder="' . $placeholder .'"' . $maxlength. '>' . esc_attr( $this->get_value( $args['id'], $args['value'], $args['section'] )) . '</textarea>' . "\n";
                $html = '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" cols="' . $cols . '" rows="' . $rows. '" placeholder="' . $placeholder .'"' . $maxlength. $readonly . $wrap . '>' . stripcslashes($args['value']) . '</textarea>' . "\n";
                
            
            return apply_filters('jot_render_field_textarea',$html);
        } // End render_field_textarea()
        
        
        /**
        * Render HTML markup for the "checkbox" field type.
        *
        */
        protected function render_field_checkbox ( $key, $args ) {
            
            $html = '';
            
            $has_description = false;
            if ( isset( $args['label'] ) ) {
               $has_description = true;
               $html .= '<label for="' . esc_attr( $key ) . '">';
            }
            if (isset($args['value'])) {
                $html .= '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="checkbox" value="true"' . checked( $args['value'], 'true', false ) . ' />' . "\n";
            }
            if ( $has_description ) {
                $html .= wp_kses_post( $args['label'] ) . '</label>';
            }            
            
        return apply_filters('jot_render_field_checkbox',$html);
        } // End render_field_checkbox()
        
        /**
        * Render HTML markup for the "select" field type.
        *         
        */
        protected function render_field_select ( $key, $args ) {
            
            $html = '';
            $size = '';
            $multiple = '';
            $arr = '';
            $currselections = array();
            if(isset($args['size'])) {
                $size = ' size="' . $args['size'] . '" ';
            }            
            if(isset($args['multiple'])) {
                $multiple = ' multiple ';
                $arr = '[]';
                if (is_array($args['currval'])) {
                   $currselections = $args['currval'];
                }
                
                //echo "<br>###### in multi " . $args['currval'] . " " . print_r($args['currval'],true);
                
            }
            
            //echo "<br>Arg " . $args['multiple'] . "<" . print_r($currselections,true)  . ">>" . $key . " " . $currselections . "<<>>" . is_array($currselections) . "<<";
            
            if ( isset( $args['options'] ) && ( 0 < count( (array)$args['options'] ) ) ) {
                $html .= '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . $arr .'"' . $size . $multiple . '>';
                foreach ( $args['options'] as $k => $v ) {
                    if(isset($args['multiple'])) {                       
                       if (array_search($k, $currselections) !== false && is_array($currselections) == 1) {                           
                          $sel = ' selected="selected" ';                         
                       } else {
                          $sel = ""; 
                       }                       
                       $html .= '<option value="' . esc_attr( $k ) . '"' . $sel . '>' . esc_html( $v ) . '</option>';                        
                    } else {
                       $html .= '<option value="' . esc_attr( $k ) . '"' . selected( esc_attr( $k ),$args['currval'],false) . '>' . esc_html( $v ) . '</option>';
                    }
                }
                $html .= '</select>';
            }
        return apply_filters('jot_render_field_select',$html);
        } // End render_field_select()
        
        
        /**
        * Render HTML markup for the "select" field type which contains optgroups.
        *         
        */
        protected function render_field_optgroupselect ( $key, $args ) {
            // $key is the optgroup name
            // $args['option'] contains an array with element called 'id' and 'value' 
            
            $html = '';
            if ( isset( $args['options'] ) && ( 0 < count( (array)$args['options'] ) ) ) {
                $html .= '<select class="jot-optgroup" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '[]" multiple="multiple">';
                foreach ( $args['options'] as $k => $v ) {
                    $html .= "<optgroup label='" . esc_attr($k) . "'>";
                    foreach ($v as $val) {
                        $html .= '<option value="' . esc_attr( $val['id'] ) . '">' . esc_html( $val['value'] ) . '</option>';
                    }
                }
                $html .= '</select>';
            }
        return apply_filters('jot_render_field_select',$html);
        } // End render_field_select()
        
        
        public function render_row_multi($field_name,$alt_field_name, $field_values, $currval, $tab) {
                      
            
            $fields = $this->get_settings_fields($tab);
                        
            $field_args = $fields[$field_name];
                       
            $html = "";
            $html .= "<tr><th>";
            $html .= $field_args['name'];
            $html .= "</th><td>";
            $field_args['options'] = $field_values;
            if (!empty($alt_field_name)) {
                    $field_args['id'] = $alt_field_name;
            }  else {
                    $field_args['id'] = $field_name;
            }    
            $field_args['currval'] = $currval;
            $html .=  $this->render_field($field_args);
            $html .= "</td></tr>";
            
            return apply_filters('jot_render_row_multi',$html);        
        }
        
        public function render_row_options($field_name, $currselections, $tab) {
            
                             
            $fields = $this->get_settings_fields($tab);
            $field_args = $fields[$field_name];
            
            $html = "";
            
            
            $html .= "<tr><th>";
            $html .= stripslashes($field_args['name']);
            $html .= "</th><td>";
            foreach ( $field_args['options'] as $k => $v ) {
                if (array_key_exists($k, $currselections) !== false) {
                   $currval = 'true';    
                } else {
                   $currval = 'false';
                }
                $key = Joy_Of_Text_Plugin()->token . '-' . $field_args['section'] . '[' . $field_name . '][' . $k . ']';
                $html .= '<label for="' . esc_attr( $k ) . '">';
                $html .= '<input id="' . esc_attr( $k ) . '" name="' . esc_attr( $key ) . '" type="checkbox" value="true"' . checked( $currval, 'true', false ) . ' />' . "\n";
                $html .= wp_kses_post( $v ) . '</label>' . "<p>";
            }
            $html .= '<p class="description">' . $field_args['description']. '</p>';
            $html .= "</td></tr>";
            
            return apply_filters('jot_render_row_options',$html);        
        }
        
        public function render_row($field_name, $alt_field_name, $field_value, $tab) {
        
            $fields = $this->get_settings_fields($tab);            
            $field_args = $fields[$field_name];
            
            
            $html = "";

            if (!isset($field_args['type'])) {
                return $html;                      
            }
            
            if (!isset($field_args['name'])) {
                return $html;                      
            }
            
            if (!empty($alt_field_name)) {
                    $field_args['id'] = $alt_field_name;
            }  else {
                    $field_args['id'] = $field_name;
            }                     
            
            if ($field_args['type']=='hidden') {
                $field_args['value'] = $field_value;
                $html .=  $this->render_field($field_args);
            } else {
                $html .= "<tr><th>";
                $html .= stripslashes($field_args['name']);
                $html .= "</th><td>";
                              
                if (!isset($field_value) || is_null($field_value) || $field_value == '') {                
                    if (isset($field_args['default'])) {
                       $val = $field_args['default'];
                    } else {
                        $val = '';
                    }
                } else {
                    $val = $field_value;
                }
                $field_args['value'] = $val;                
                $html .=  $this->render_field($field_args);
                $html .= "</td></tr>";
            }
            return apply_filters('jot_render_row',$html);        
        }
        
        public function render_section_header($field_text) {
            $html = "<tr><th class='jot-section-header' colspan=2>";
            $html .= $field_text;
            $html .= "</th></tr>";
            return apply_filters('jot_render_section_header',$html);        
        }
        
        
        /******************************************************************************************
        *******************************************************************************************
        **                                                                                       **
        **                    O P T I O N S  G E T T E R S  & S E T T E R S                      **
        **                                                                                       ** 
        *******************************************************************************************
        *******************************************************************************************/
        
        
               
        /**
        *
        * Gets the member list settings for the given key
        * 
        */
        public function get_memlist_settings($variable) {
          
            $settings =  get_option('jot-plugin-group-list');
            if (isset($settings[$variable])) {
                return $settings[$variable];            
            } else {
                return "";
            }
        }
        
        
        /**
        *
        * Gets the history settings for the given key
        * 
        */
        public function get_hist_settings($variable) {
          
            $settings =  get_option('jot-plugin-message-history');
            if (isset($settings[$variable])) {
                return $settings[$variable];            
            } else {
                return "";  
            }
        }
        
        
        /**
        *
        * Gets the message tab settings for the given key
        * 
        */
        public function get_message_settings($variable) {
          
            $settings =  get_option('jot-plugin-messages');
            if (isset($settings[$variable])) {
                return $settings[$variable];            
            } else {
                return "";  
            }
        }
        
        
        /**
        *    
        * Sets the history settings for the given key
        *
        */
        public function set_hist_settings($variable,$value) {
           
            $histsettings =  get_option('jot-plugin-message-history');
            $histsettings[$variable] = $value ;   
            update_option('jot-plugin-message-history',$histsettings);
            
        }
        
        
               
        public function get_smsprovider_settings($variable) {
          
            $sms_local_settings = get_option('jot-plugin-smsprovider');
            $sms_site_settings  = get_site_option('jot-plugin-network-smsprovider') ? get_site_option('jot-plugin-network-smsprovider') : array() ;
              
            // If selected use site wide settings  
            if (isset($sms_site_settings['jot-smsuseacrossnetwork'])) {
               // Use the site wide settings if selected
               if ($sms_site_settings['jot-smsuseacrossnetwork'] == true) {
                    if (isset($sms_site_settings[$variable])) {
                       return $sms_site_settings[$variable];                    
                    } 
               }
            }
                           
            // Otherwise use local settings (single site)
            if (isset($sms_local_settings[$variable])) {
                return $sms_local_settings[$variable];            
            } else {
                return "";
            }
        }
        
        /*
        *
        * Gets the SMS provider settings for the given key from networkwide option
        * 
        *
        */
        public function get_network_smsprovider_settings($variable) {
          
            $sms_local_settings = get_option('jot-plugin-smsprovider');
            $sms_site_settings  = get_site_option('jot-plugin-network-smsprovider') ;
            
            if (function_exists('is_multisite') && is_multisite()) {                  
                // Get from network settings
                if (isset($sms_site_settings[$variable])) {
                    return $sms_site_settings[$variable];            
                } 
            }
            
            // Otherwise use local settings (single site)
            if (isset($sms_local_settings[$variable])) {
                return $sms_local_settings[$variable];            
            } else {
                return "";
            }
            
        }
        

        /**
        *    
        * Sets the SMS provider settings for the given key
        *
        */
        public function set_smsprovider_settings($variable,$value) {
           
            $settings =  get_option('jot-plugin-smsprovider');
            $settings[$variable] = $value ;   
            update_option('jot-plugin-smsprovider',$settings);
            
        }
        
         /**
        *    
        * Sets the SMS provider settings for the given key.
        * Saves as a WP network wide option
        *
        */
        public function set_network_smsprovider_settings($variable,$value) {
           
            $settings =  get_site_option('jot-plugin-network-smsprovider');
            $settings[$variable] = $value ;   
            update_site_option('jot-plugin-network-smsprovider',$settings);
            
        }
        
} // End Class