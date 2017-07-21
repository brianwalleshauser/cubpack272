<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Joy_Of_Text_Plugin_Admin Class
*

*/


final class Joy_Of_Text_Plugin_Admin {
        /**
        * Joy_Of_Text_Plugin_Admin The single instance of Joy_Of_Text_Plugin_Admin.
        * @var object
        * @access private
        * @since 1.0.0
        */
        
        private static $_instance = null;
        
        
        /**
        * Constructor function.
        */
        public function __construct () {
            // Register the settings with WordPress.
            add_action( 'admin_init', array( $this, 'register_settings' ) );
            // Register the settings screen within WordPress.
            add_action( 'admin_menu', array( $this, 'register_settings_screen' ) );
            
            // Add admin notices
            add_action( 'admin_notices', array($this,'check_senderid_valid') );
            add_action( 'admin_notices', array($this,'check_licence_activation') );            
            
        } // End __construct()
        
        /**
        * Main Joy_Of_Text_Plugin_Admin Instance
        *
        * Ensures only one instance of Joy_Of_Text_Plugin_Admin is loaded or can be loaded.
        *
        */
        public static function instance () {
            if ( is_null( self::$_instance ) )
            self::$_instance = new self();
            return self::$_instance;
        } // End instance()
        
        /**
        * Register the admin screen.
        */
        public function register_settings_screen () {
           
            add_menu_page(__('Messaging', 'jot-plugin'), __('Messaging', 'jot-plugin'), 'jot_user_capability', 'jot-plugin', array( $this, 'settings_screen' ),
'dashicons-phone');
                     
        } // End register_settings_screen()
        
        
        function check_licence_activation() {
            
            if ( !current_user_can( 'jot_admin_capability' ) ) {
                // Suppress notifications for non-admins
                return;
            }
            
            $licence_status = trim( Joy_Of_Text_Plugin()->settings->get_network_smsprovider_settings('jot-eddlicencestatus') );
            $add_notice = false;
            
            if ($licence_status == 'expired') {
                $notice_type = 'notice-error is-dismissable';
                $notice_message = __('Your Joy Of Text licence has expired. Please refer to your renewal reminder email.', 'jot-plugin');
                $add_notice = true;
            } elseif ($licence_status != 'valid' && $licence_status != 'Active') {
                $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=licencekeys'>" . __("Messaging-Settings-Licence Keys","jot-plugin") .  "</a>";
                $notice_type = 'notice-warning is-dismissable';
                $notice_message = sprintf (__('Your Joy Of Text licence is not activated. Add your key into the %s page', 'jot-plugin'), $url);
                $add_notice = true;
            }
            
            if ($add_notice) {
                echo "<div class='notice $notice_type'>";
                echo  "<p>" . $notice_message  . "</p>";
                echo "</div>";
            }
    
        }
        
        function check_senderid_valid() {
            
            // Sender IDs are not supported by Twilio in the US. This is a common cause of errors.
            $senderid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-smssenderid');
            $country_code = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-smscountrycode');            
                       
            if ($senderid != '' && $country_code == 'US') {
                $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=general'>" . __("Messaging-Settings-General Settings","jot-plugin") .  "</a>";
                echo "<div class='notice notice-error is-dismissable'>";
                echo  "<p>" . sprintf(__('Sender IDs are not supported by Twilio in the USA. Please remove it from %s', 'jot-plugin'),$url)  . "</p>";
                echo "</div>";
            }
    
        }

        
        
        /**
        * Output the markup for the settings screen.
        */
        public function settings_screen () {
            global $title;
            $sections = Joy_Of_Text_Plugin()->settings->get_settings_sections();
            $tab = $this->_get_current_tab( $sections );
            
            
            $subform = $this->get_subform();
            $tabform = $tab . "-" . $subform;

            // Check if logo exists and display if found
            if (file_exists( plugins_url( 'logo/logo.png', dirname(__FILE__) )  )) {
                $file = plugins_url( 'logo/logo.png', dirname(__FILE__) );
                if (@getimagesize($file)) {
                    echo "<img src='" . $file . "'>";
                }
            }
            
            
            echo $this->get_admin_header_html( $sections, $title );
            switch ( $tabform ) {
                case 'smsprovider-main';
                    if ( current_user_can( 'jot_admin_capability' ) ) {
                        $this->write_smsprovider_fields($sections, $tab);
                    }
                break;
                case 'messages-main':
                        $this->write_message_fields($sections, $tab);                    
                break;
                case 'group-list-main':
                    
                        $this->write_group_list_fields($sections, $tab);
                    
                break;
                case 'group-list-add':
                    
                        $this->write_group_add_fields($sections, $tab);
                    
                break;
                case 'group-list-bulk':
                    
                        $this->write_member_bulk_add_fields($sections, $tab);
                    
                break;
                case 'message-history-main':
                    
                        $this->write_message_history_fields($sections, $tab);
                    
                break;
                //case 'scheduler-manager-main':
                
                //    $this->write_scheduler_fields($sections, $tab);
                
                //break;
                default:
                    do_action("jot_render_extension_tab",$tabform);
                break;
            }
                    
        } // End settings_screen()
            
            
        /**
        * Write out Sms_provider (now Settings) tab screen
        */    
        public function write_smsprovider_fields($sections,$tab) {
            
                
            $auth = get_option('jot-plugin-smsprovider');
            echo "<form id=\"smsprovider-fields-form\" action=\"options.php\" method=\"post\">";
                        
            settings_fields( 'jot-plugin-settings-' . $tab );
            //do_settings_sections( 'jot-plugin-' . $tab );
            
            $pagehtml = Joy_Of_Text_Plugin()->settings->render_smsprovider_settings($sections,$tab);
            echo $pagehtml['html'];
            
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            if (isset($_GET['section']))  {
               // Don't display Save button on Get Started or system info tabs
               if ($_GET['section'] != 'getstarted' && $_GET['section'] != 'systeminfo')  {
                   submit_button( __(  $sections[$tab]['buttontext'], 'jot-plugin' ) );
               }
            }
            echo "</form>";
            echo "<br>";
            
            // Display a guidance messages
            $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
            //$sid = isset($auth['jot-accountsid-' . $selected_provider]) ? $auth['jot-accountsid-' . $selected_provider] : null;
            $sid = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-accountsid-' . $selected_provider);
            $guidance = "";
            $cssclass = "";
            
                       
            $this->write_page_footer();
            
        }
        
        /**
        * Write out message_fields tab screen
        */
        public function write_message_fields($sections,$tab) {
            
            $smsmessage =  get_option('jot-plugin-messages');            
            $mms_image = isset($smsmessage['jot-message-mms-image']) ? $smsmessage['jot-message-mms-image'] : "";
            
            // Send default send method as group send, rather than quick send.
            $send_method = isset($smsmessage['jot-message-sendmethod']) ? $smsmessage['jot-message-sendmethod'] : "jottabgroupsend";
            
            echo "<form id=\"jot-message-field-form\" action=\"\" method=\"post\">";
            echo "<input type=\"hidden\"  id=\"jot-plugin-messages[jot-message-mms-image]\" name=\"jot-plugin-messages[jot-message-mms-image]\" value=\"" . $mms_image ."\">";
            echo "<input type=\"hidden\"  id=\"jot-plugin-messages[jot-message-sendmethod]\" name=\"jot-plugin-messages[jot-message-sendmethod]\" value=\"" . $send_method ."\">";
            
            // Current batch being processed
            echo "<input type=\"hidden\"  id=\"jot-plugin-messages[jot-message-batchid]\" name=\"jot-plugin-messages[jot-message-batchid]\" value=\"\">";
            echo "<input type=\"hidden\"  id=\"jot-plugin-messages[jot-message-fullbatchsize]\" name=\"jot-plugin-messages[jot-message-fullbatchsize]\" value=\"\">";
            
            
            settings_fields( 'jot-plugin-settings-' . $tab );
            echo Joy_Of_Text_Plugin()->settings->render_message_panel($sections,$tab);            
            
            
            echo "<div class='jot-message-send-buttons'>";
            echo "<a href=\"#\" class=\"button button-primary\" id=\"jot-sendmessage\">"  . __("Send your message","jot-plugin") . "</a>"; 
            echo "<div class=\"divider\"></div>";
            echo "<a href=\"#\" class=\"button button-primary\" id=\"jot-sendreset\">" . __("Reset","jot-plugin") ."</a>"; 
            echo "</div>";
            
            echo "</form>";
            echo "<br>";
            echo "<div id=\"jot-messagestatus\"></div>";
            echo "<div id=\"jot-messagewarningstatus\"></div><p>";
            echo "<div id=\"jot-sendstatus-div\">";
            echo "</div>";
            echo "<div id=\"jot-restartbatch-div\" style=\"display:none\"><a href=\"#\" id=\"jot-restartbatch\" title=\"" . __("Restart message processing.","jot-plugin")  . "\">" . __("Restart","jot-plugin") . "</a></div>";
           
            $this->write_page_footer();            
           
        }
        
        public function write_group_list_fields($sections,$tab) {
            
            // Add new group button
            echo "<p class=\"submit\">";
            echo '<a href="' . admin_url( 'admin.php?page=jot-plugin&tab=group-list&subform=add') . '" class="button button-primary" >' . $sections[$tab]['buttontext'] . '</a>';
            echo "</p>";
            
            // Jump-to group
            echo "<table id='jot-group-list-controls-tab'>\n";
            echo "<tr>";
            echo "<td class='jot-td-r'>";
            echo Joy_Of_Text_Plugin()->settings->render_jumptogroup($sections,$tab);
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            
            echo "<form id=\"group-list-fields-form\" action=\"\" method=\"post\">";
            echo "<input type=\"hidden\"  name=\"jot-form-id\" value=\"jot-group-add\">";
            settings_fields( 'jot-plugin-settings-' . $tab );            
            echo Joy_Of_Text_Plugin()->settings->render_grouplist();
            echo "</form>";
            
            echo "<br>";
            echo "<br>";
            echo "<br>";
            
            if ( isset($_GET['lastid'])) {
                $lastid = $_GET['lastid'];
                wp_localize_script( 'jot-js', 'jot_lastgroup',
			       array( 'id' => $lastid ) );
                $groupdetails = Joy_Of_Text_Plugin()->settings->get_saved_group_details($lastid);
                
                echo Joy_Of_Text_Plugin()->settings->render_grouplisttabs($lastid);
                echo Joy_Of_Text_Plugin()->settings->render_groupdetails($sections, $tab, $lastid);
                if (!isset($groupdetails->jot_virtualgroup)) {
                    echo Joy_Of_Text_Plugin()->settings->render_groupmembers($sections, $tab, $lastid);
                    echo Joy_Of_Text_Plugin()->settings->render_groupinvites($sections, $tab, $lastid);
                }
                do_action("jot_render_extension_subtab",$sections, $tab, $lastid);               
               
               
            }
            
            $this->write_page_footer();
        }
        
        public function write_group_add_fields($sections,$tab) {
            
            if( isset($_GET['settings-updated']) ) { 
                echo "<div id=\"message\" class=\"updated\">";
                echo "<p><strong>" . _e('Settings saved.') . "</strong></p>";
                echo "</div>";
            }
            //echo "<form id=\"group-add-fields-form\" action=\"" . plugins_url( 'jot-options.php\"', __FILE__ ) . " method=\"post\">";
            echo "<form id=\"jot-group-add-fields-form\" action=\"\" method=\"post\">";
            echo "<input type=\"hidden\"  name=\"jot_form_id\" value=\"jot-group-add\">";
            echo "<input type=\"hidden\"  name=\"jot_form_target\" value=\"main\">";
            settings_fields( 'jot-plugin-settings-' . $tab );
            
            echo Joy_Of_Text_Plugin()->settings->render_groupadd($sections, $tab);
            echo "<div class='jot-group-add-buttons'>";
            echo "<a href=\"#\" class=\"button button-primary\" id=\"jot-addgroup\">" . __("Add new group","jot-plugin"). "</a>";
            echo "<div class=\"divider\"></div>";
            echo "<a href=\"#\" class=\"button button-primary\" id=\"jot-addgroupcancel\">" . __("Cancel","jot-plugin") ."</a>"; 
            echo "</div>";
            echo "</form>";
            echo "<br>";
            echo "<div id=\"jot-messagestatus\"></div>";
            
            $this->write_page_footer();
        }
        
        public function write_member_bulk_add_fields($sections,$tab) {
          
            echo Joy_Of_Text_Plugin()->settings->render_memberbulkadd($sections, $tab, $_GET['lastid'], $_GET['paged']);
            $this->write_page_footer();
        
        }
        
        
        public function write_message_history_fields($sections,$tab) {
            
            echo "<form id=\"group-list-fields-form\" action=\"options.php\" method=\"post\">";
            echo "<input type=\"hidden\"  name=\"jot-form-id\" value=\"jot-group-add\">";
            settings_fields( 'jot-plugin-settings-' . $tab );            
            echo Joy_Of_Text_Plugin()->settings->render_messagehistory();
            echo "</form>";
            
            $this->write_page_footer();
      
        }
        
        public function write_scheduler_fields($sections,$tabform) {
            
             
             if (is_plugin_active('jot-scheduler/jot-scheduler.php')) {
                do_action("jot_render_extension_tab",$tabform);
             } else {
                echo "<h3>";
                echo __("The JOT Scheduler extension allows you to schedule a batch of messages to be sent at a future date and time and seemlessly integrates into the existing JOT Pro and Lite screens.","jot-plugin");
                echo "</h3>";
                echo "<h3>";
                echo "To find out more and to purchase the JOT Scheduler, please follow this " . "<a href='http://www.getcloudsms.com/downloads/joy-text-message-scheduler-extension/' target='_blank'>link</a>";
                echo "</h3>";
                echo "<p>";
                
                echo "<p>";    
             }
        }
        
        /**
        * Register the settings within the Settings API.
        */
        public function register_settings () {
                    
                    register_setting( 'jot-plugin-settings-smsprovider', 'jot-plugin-smsprovider', array($this,'sanitise_settings'));
                    register_setting( 'jot-plugin-settings-network-smsprovider', 'jot-plugin-network-smsprovider'); 
                    register_setting( 'jot-plugin-settings-messages', 'jot-plugin-messages');
                    register_setting( 'jot-plugin-settings-group-list', 'jot-plugin-group-list');
                    register_setting( 'jot-plugin-settings-message-history', 'jot-plugin-message-history');
                    register_setting( 'jot-plugin-settings-woocommerce', 'jot-plugin-woo-manager');
                    
        } // End register_settings()
        
        
        public function sanitise_settings($input) {
                       
            //var_dump($input);
            //exit;
            
            if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
            }
            parse_str( $_POST['_wp_http_referer'], $referrer );
            
            if (isset($referrer['tab'])) {                 
                   $tab = $referrer['tab'];
            } else {
                   return $input;
            }
            
                        
            if (isset($referrer['section'])) {                 
                   $sectiontab = $referrer['section'];
            } else {
                   return $input;
            }
                        
            $input = $input ? $input : array();
            
            // Get existing settings array
            $smsdetails       = get_option('jot-plugin-smsprovider') ? get_option('jot-plugin-smsprovider') : array() ;
            $sms_site_details = get_site_option('jot-plugin-network-smsprovider') ? get_option('jot-plugin-network-smsprovider') : array() ;
            
            // Save Twilio details to network wide site options
            if (function_exists('is_multisite') && is_multisite() && is_main_site()) {
                $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
                if (isset($input['jot-eddlicence'])) {
                    $sms_site_details['jot-eddlicence'] = $input['jot-eddlicence'];
                }
                /*
                if (isset($input['jot-accountsid-' . $selected_provider])) {
                    $sms_site_details['jot-accountsid-' . $selected_provider] = $input['jot-accountsid-' . $selected_provider];
                }
                if (isset($input['jot-authsid-' . $selected_provider])) {
                    $sms_site_details['jot-authsid-' . $selected_provider] = $input['jot-authsid-' . $selected_provider];
                }
                if (isset($input['jot-messservsid-' . $selected_provider])) {
                    $sms_site_details['jot-messservsid-' . $selected_provider] = $input['jot-messservsid-' . $selected_provider];
                }
                if (isset($input['jot-messservchk-' . $selected_provider])) {
                    $sms_site_details['jot-messservchk-' . $selected_provider] = $input['jot-messservchk-' . $selected_provider];
                }                
                if (isset($input['jot-phonenumbers-' . $selected_provider])) {
                    $sms_site_details['jot-phonenumbers-' . $selected_provider] = $input['jot-phonenumbers-' . $selected_provider];
                }
                if (isset($input['jot-smsuseacrossnetwork'])) {
                    $sms_site_details['jot-smsuseacrossnetwork'] = $input['jot-smsuseacrossnetwork'];
                }
                */
                update_site_option('jot-plugin-network-smsprovider',$sms_site_details);
            }     
            
            
            // If there are fields of type checkbox for this tab, that are not in input then set them to false            
            $fields = Joy_Of_Text_Plugin()->settings->get_settings_fields($tab);
         
                    
            foreach ($fields as $key => $value) {
                if (isset($value['sectiontab'])) {                    
                    if ($value['type'] == 'checkbox' && $value['sectiontab'] == $sectiontab) {
                        if (array_key_exists($key, $input)) {//
                            // Key found in input array
                        } else {
                            // Key not found so add it into the input array
                            $input[$key] = false;
                        }
                    }
                }
            }
                        
            // Merge new settings with existing settings (priority goes to left hand array)
            $smsdetails_merge = $input + $smsdetails;
                        
            return $smsdetails_merge;
        }
        
        /**
        * Validate the settings.
        */
        public function validate_settings ( $input ) {
            $sections = Joy_Of_Text_Plugin()->settings->get_settings_sections();
            $tab = $this->_get_current_tab( $sections );
            return Joy_Of_Text_Plugin()->settings->validate_settings( $input, $tab );
        } // End validate_settings()

        /**
        * Return marked up HTML for the header tag on the settings screen.
        */
        public function get_admin_header_html ( $sections, $title ) {
            $response = '';
            $defaults = array(
            'tag' => 'h2',
            'atts' => array( 'class' => 'jot-plugin-wrapper' ),
            'content' => $title
            );
            $args = $this->_get_admin_header_data( $sections, $title );
            $args = wp_parse_args( $args, $defaults );
            $atts = '';
            if ( 0 < count ( $args['atts'] ) ) {
                foreach ( $args['atts'] as $k => $v ) {
                    $atts .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
                }
            }
            $response = '<' . esc_attr( $args['tag'] ) . $atts . '>' . $args['content'] . '</' . esc_attr( $args['tag'] ) . '>' . "\n";
            return $response;
        } // End get_admin_header_html()
       
        /**
        * Return the current tab key.
        */
        public function _get_current_tab ( $sections = array() ) {
            $response = "";
            if ( isset ( $_GET['tab'] ) ) {
                $response = sanitize_title_with_dashes( $_GET['tab'] );
            } else {
                if ( is_array( $sections ) && ! empty( $sections ) ) {
                    // Find the default tab to open
                    $defaulttab = Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-defaulttab');
                    if (empty($defaulttab)) {
                        $response = $this->get_default_tab($sections);
                    } else {
                        $response = $defaulttab;
                    }
                } else {
                    $response = '';
                }
            }
          
            return $response;
        } // End _get_current_tab()
        
        /**
        * Return the current tab key.
        */
        public function get_default_tab ( $sections ) {
           
            $response = "";
            
            foreach ( $sections as $key => $argsarray ) {                       
                if (array_key_exists('default', $argsarray)) {
                    if ($argsarray['default'] == 'true') {
                        $response = $key;
                    }
                } 
            }
            
            return $response;
        } // End _get_current_tab()
        
        /**
        * Return the current subform key.
        */
        
        private function get_subform () {
            if ( isset ( $_GET['subform'] ) ) {
                $response = sanitize_title_with_dashes( $_GET['subform'] );
            } else {
                $response = 'main';               
            }
            return $response;
        } // End _get_current_tab()
       
        
        /**
        * Return an array of data, used to construct the header tag.
        */
        private function _get_admin_header_data ( $sections, $title ) {
            $response = array( 'tag' => 'h2', 'atts' => array( 'class' => 'jot-plugin-wrapper' ), 'content' => $title );
            $tabexclude = array("smsprovider");
            if ( is_array( $sections ) && 1 < count( $sections ) ) {
                
                $docurl = 'http://www.getcloudsms.com/documentation/';
		$docurl = apply_filters('jot_whitelabel_helpurl',$docurl);
		
                $response['content'] = '<a href="' . $docurl . '" target="_blank" class="nav-tab" id="jot-help" title="Help"><img src="' . plugins_url( 'images/help.png', dirname(__FILE__) ) .  '" title="Help" id="jot-help-image"></a>';
              
                $response['atts']['class'] = 'nav-tab-wrapper';
                $tab = $this->_get_current_tab( $sections );
                foreach ( $sections as $key => $value ) {
                    $class = 'nav-tab';
                    if ( $tab == $key ) {
                        $class .= ' nav-tab-active';
                    }
                    
                
                    if (in_array($key, $tabexclude)) {
                        if ( current_user_can( 'jot_admin_capability' )) {               
                            $response['content'] .= '<a href="' . admin_url( 'admin.php?page=jot-plugin&tab=' . sanitize_title_with_dashes( $key ) ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $value['tabname']) . '</a>';
                        }
                    } else {
                        $response['content'] .= '<a href="' . admin_url( 'admin.php?page=jot-plugin&tab=' . sanitize_title_with_dashes( $key ) ) . '" class="' . esc_attr( $class ) . '">' . esc_html( $value['tabname']) . '</a>';
                    }
                }
            }
            return (array)apply_filters( 'jot-plugin-get-admin-header-data', $response );
        } // End _get_admin_header_data()

        public function write_page_footer() {
            
            $product_display_name = Joy_Of_Text_Plugin()->product_display_name;
            $product_display_name = apply_filters('jot_whitelabel_product_display_name', $product_display_name);
            
            $support_email = Joy_Of_Text_Plugin()->support_email;
            $support_email = apply_filters('jot_whitelabel_support_email', $support_email);
            
            echo "<br>";
            echo "<br>";
            echo "<br>";
            echo "<br>";
            echo "<br>";
            
            $footer_logo = "";
            $footer_logo = apply_filters('jot_whitelabel_footer_logo', $footer_logo);
            echo "<div class='jot-page-footer-logo'>" . $footer_logo . "</div>";
            
            
            echo "<div class='jot-page-footer'>" . $product_display_name  .
                                   " " . __("Version ","jot-plugin") .
                                   Joy_Of_Text_Plugin()->version .
                                   " (". Joy_Of_Text_Plugin()->product .")" .
                                   "<br>" . _("For feedback and support, please send an email to") .
                                   " " .
                                   "<a href=\"mailto:" . $support_email . "\">" . $support_email . "</a></div>";
            
            
        }
        
} // End Class