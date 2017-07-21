<?php
/**
 * The plugin bootstrap file
 *
 * 
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              
 * @since             1.0.0
 * @package           Joy_Of_Text
 *
 * @wordpress-plugin
 * Plugin Name:       Joy Of Text Pro
 * Plugin URI:        http://www.getcloudsms.com/
 * Description:       SMS, MMS and text-to-voice messaging. Connect with your customers, subscribers, followers, members and friends.
 * Version:           2.25.0
 * Author:            Stuart Wilson
 * Author URI:        http://www.getcloudsms.com/
 * Text Domain:       jot-plugin
 * Domain Path:       /languages
 *
 * Version 2.25.0
 *     - NEW: Added "Select number" drop down on Messages tab, allowing Twilio number to be selected, rather than using the default Twilio number.
 *     - NEW: Added "Jump to Group" on the Group Manager page
 *     - NEW: Changed keyword for opting-out of all groups to "leave all"
 *     - NEW: Added support for multi-word opt outs
 *     - NEW: Added support for multi-word text subscriptions
 *     - NEW: Allow admin group to be specified, replacing single Admin number
 *     - NEW: Allow notifications to be sent to a group, rather than to an individual number.
 *     - NEW: Merge tags, such as %firstname% added to admin send message commands.
 *     - NEW: Added an "already subscribed" message for members subscribing by text message.
 *     - NEW: Added delete member from all groups buttons
 *     - NEW: Added a new notification, sent to admins when a member unsubscribes.
 *     - FIX: Added sender ID for send_to_group messages 
 *     - FIX: Fixed a bug in the Member List update option, which allowed two members to have the same phone number.
 *     - FIX: Improved handling of phone numbers on the virtual phone when using Messaging Services
 *
 *
 * Version 2.24.0 
 *     - NEW: Enhancing [jotgroupsend] shortcode. Added member_select=yes parameter
 *     - NEW: Redesigned [jotform] shortcode form.
 *     - NEW: Added option for a subscription confirmation code when using form to subscribe.
 *     - NEW: Added filter hooks before messages are sent.
 *     - NEW: Added filter hook triggered after a member has been deleted.
 *     - NEW: Added the additional member details fields to the group CSV download file.
 *     - NEW: New members subscribing through the webform, are added to 'auto-add' groups.
 *     - NEW: Removed the auto-add groups reply message, instead the auto-add groups's welcome message is used.
 *     - NEW: Add subscription manager command. Allowing members to text in keyword which will show them their groups subscription details.
 *     - NEW: Added subscription manager merge tag, %jot_submgr%.
 *     - NEW: Increased maximum message size to 1600 characters in database tables.
 *     - FIX: Improved processing of messages on the Message Tab to reduce the impact of server time-outs
 *     - FIX: Fixed bug where group metadata was not deleted, when a group was deleted from Group Manager.
 *     - FIX: Fixed bug where the selected Twilio number was being added to 'auto add' groups by the Notifier extension.
 *     - FIX: Removed 'audio not found' message on Message History tab, when no audio file has been selected.
 *     
 * Version 2.23.1
 *     - NEW: Support for repeatable schedules.
 *     - FIX: Changed format of the version numbers.
 *     - FIX: History tab not being created on new installs, due to bug in CREATE TABLE SQL.
 *
 * Version 2.0.23
 *     - NEW: Added System Info page in Settings to help support requests.
 *     - NEW: Quick Send tab, allowing numbers to be entered without first adding them to groups
 *     - NEW: Group send short code, providing a means of sending a message to a whole group.
 *     - NEW: Group opt out short code.
 *     - NEW: Added capability to drip feed messages, rather than send them all in one go. (To be fully enabled in V2.0.24)
 *     - NEW: Captured more data in the message history as a precursor to better reporting in a v2.0.24
 *     - NEW: Added action hooks which are called when members subscriber or are added.
 *     - NEW: Added 3 second pause before hanging up a text-to-voice or audio file call.
 *     - NEW: Added button to configure SMS URL, allowing inbound messages to be received by the plugin.
 *     - FIX: Fixed bug where welcome messages were being sent twice.
 *     - FIX: Fixed bug where a parameter was missing from some get_replace_tags function calls
 *     - FIX: Fixed bug when sending welcome messages after submitting a mapped Gravity Form.
 *     - FIX: Fixed bug when displaying group details.
 *
 * Version 2.0.22
 *     - NEW: Added configurable email subject line for inbound message notification.
 *     - NEW: Added option to select a number for each group, which will be used to send 'welcome messages' from. 
 *     - NEW: Added Woocommerce order notifications, sent to customers and admins.
 *     - NEW: Added new jottextus shortcode. The shortcode adds a front end contact form. Completed forms can be texted to site admins.
 *     - NEW: Added sound notifications to the Message History 'virtual phone' for send and received messages.
 *     - NEW: Licence keys from the master site are now shared across all subsites in a multisite installation. 
 *     - FIX: Fixed bug in SMS subscription code.
 *     - FIX: Fixed bug in rendering of fields for extensions. (Ensured return type is array)
 *     - FIX: Added name back into virtual phone title.
 *     - FIX: Moved front-end strings into function and added filter.
 *     - FIX: Fixed problem with mapping Gravity Forms to JOT form fields.
 *     - FIX: Made the group invite redirect URL field up to 500 characters long.
 *     - FIX: Allowed "Welcome SMS" to be upto 640 characters.
 *     - FIX: Simplified message length counter.
 *     
 *
 * Version 2.0.21
 *     - NEW: Added support for Twilio's Messaging Services. (See http://www.getcloudsms.com/configuring-twilio-messaging-services/)
 *     - NEW: Support for creating schedule plans in the Scheduler Extension.
 *     - NEW: Added index on member table, to speed up history retrieval.
 *     - NEW: Hidden the Sender ID field when country code is US (Twilio do not support Sender IDs in the US).
 *     - NEW: Changed Woo sync log to option variable rather than file.
 *     - FIX: Fixed bug in Message History sort sequence.
 *     - FIX: Fixed bug in the remove duplicates processing.
 *     - FIX: Simplified code for the 'virtual phone' on the Message History tab.
 *     - FIX: Changed the way %firstname% and %lastname% tags are split for multipart names.
 *     - FIX: Ensure message length is restricted to 640 characters.
 *
 * Version 2.0.20
 *     - NEW: Added ability to move, copy and delete members between/from groups, as a bulk action.
 *     - NEW: Added option to choose if welcome messages are sent when members manually added, moved or copied into groups.
 *     - NEW: Added warning notice if licence key isn't activated
 *     - NEW: Added licence check when opening the Settings tab.
 *     - NEW: Changes to the Get Started settings tab, providing guidance about how to setup the plugin.
 *     - FIX: Fixed a bug which hid the message type options for the Group Invite welcome messages.
 *     - FIX: Fixed bug in Message History filtered searches
 
 *
 * Version 2.0.19
 *     - Redesigned the layout of the settings pages, separating settings into tabs.
 *     - Added option to redirect to another page after successful subscription.
 *     - Added new merge tags for jot_groupid, jot_groupname and jot_groupdesc.
 *     - Changed the opt-out processing, so the 'remove' keyword is no longer needed.
 *
 * Verion 2.0.18
 *     - Integration with Gravity Forms.
 *     - Allowed multiple group subscriptions from input form.
 *     - Provide merge tags for the extended info field in messages.
 *     - Simplified the query behind the Message History tab.
 *
 *     
 * Version 2.0.17
 *     - Fix multisite bug.
 *     - Changed time zone to Wordpress local default rather than GMT
 *     - Fixed bug in variables within the subscription welcome message.
 *     - Fixed bug in Welcome message when over 160 characters entered.
 *     - Fixed bugs in the 'select members' dialog on the Messages tab.
 *     - Changed the way duplicate numbers are removed. Now removed when messages are being queued.
 *
 * Version 2.0.16
 *     - Scheduler bug fix
 *
 *
 * Version 2.0.15
 *     - Add Woocommerce integration. Sync'ing Woo customers to be added to JOT groups.
 *     - Enabled selection of all media types for MMS messages.
 *     - Show message price when hovering over the status field on the History tab.
 *     - Added subscriber notifications. SMS or email send to admin when new member subscribes to a group.
 *     - Added the additional member info fields as option subscription form fields.
 * 
 * Version 2.0.14
 *     - Bug fix. Tables not being created during lite to pro upgrade
 *     - Added %firstname% and %lastname% tags for routed email and SMS messages 
 *
 * Version 2.0.13
 *     - Adding support for the JOT Scheduler extension plugin
 *     - Adding SQLite support
 *     - Added option to select which tab is opened when the plugin is started.
 *     - Various bug fixes
 *
 * Version 2.0.12
 *     - Changed to the bulk upload, slicing uploads into batches and providing upload status.
 *     - Fixed problem with email forwarding.
 *
 * Version 2.0.11
 *     - Consolidated major release
 *
 * Version 2.0.10g
 *     - Pre 2.0.11 release Beta release to selected customers
 *
 *
 * Version 2.0.10f
 *     - Allow group subscriptions by texting in a specified keyword.
 *     - Allowed either number or email to be used for message routing.
 *     - Added spinner to 'virtual phone' on message history tab.
 *     - Fixed bug where duplicate numbers were being added.
 *     - Changed the select recipients dialog to allow searches.
 *     - Changed character set and collation on the JOT tables to support unicode characters.
 *
 * Version 2.0.10e
 *     - Added option to simplify message history.
 *     - Added member name (if known) on message history.
 * 
 * Version 2.0.10d
 *     - Included default sender ID used for welcome and opt-out messages
 *     - Normalised the data by separating members into their own table.
 *     - Added Twilio number verification and country codes.
 *
 * Version 2.0.10c
 *     - Added option to automatically add inbound SMS numbers to chosen groups.
 *     - Added welcome message for new inbound SMS numbers.
 *     - Made the routed SMS message configurable by an admin.
 *     - Added new commands for sending an SMS remotely @number #id /memid/
 *     
 * Version 2.0.10b
 *     - Updated remote command syntax to allow forward slashes "/" or square brackets "[]" to be used for commands
 *     - Added a link to sent media in Message History
 *     - Added field reset button on Messages tab
 *     - Changed date format to MM-DD-YYYY on Message History
 *     - Added extended member information.
 *
 * Version 2.0.10a
 *     - Added email notification for inbound messages.
 *
 * Version 2.0.10
 *     - Included new user role and capabilities for Multisite
 *     - Change Network Activation, creating custom tables for each subsite
 *     - Included MMS messaging
 *     - Included logo option
 *     - Sender ID on SMS and MMS
 *
 * Version 2.0.9
 *     - Added %firstname%, %lastname% and %fullname% variables for inclusion in messages *
 * 
 * Version 2.0.8
 *     - Included voice gender and language options for text-to-voice messages
 *     - Included group member download button
 * 
 * Version 2.0.7
 *     - Included filtering on Message History and a history retention option
 *
 * Version 2.0.6
 *     - Included ability to enter multiple numbers separated by commas on the input form
 *   
 * Version 2.0.5
 *     - Included Group opt-out keywords
 *  
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	//********************************************
	// Set this constant to true if using SQLite
	//********************************************
	
	define( 'USING_SQLITE', 'false' );
        
        //********************************************
	// Set this constant for the dripfeed batch size
	//********************************************
	
	define( 'jot_dripfeed_batchsize', 100 );	

        //********************************************
	// Easy Digital Downloads constants
	//********************************************
	
        // this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
	if (!defined('EDD_SL_STORE_URL_JOTPRO')) define( 'EDD_SL_STORE_URL_JOTPRO', 'http://www.getcloudsms.com' );
	// the name of your product. This is the title of your product in EDD and should match the download title in EDD exactly
	if (!defined('EDD_SL_ITEM_NAME_JOTPRO')) define( 'EDD_SL_ITEM_NAME_JOTPRO', 'Joy Of Text Pro Version - Wordpress SMS' );
        if (!defined('EDD_SL_ITEM_ID_JOTPRO')) define( 'EDD_SL_ITEM_ID_JOTPRO', 33 );
	
	/**
	* Returns the main instance of Joy_Of_Text_Plugin to prevent the need to use globals.
	*/
	function Joy_Of_Text_Plugin() {
		return Joy_Of_Text_Plugin::instance();
	} // End Joy_Of_Text_Plugin()

	Joy_Of_Text_Plugin();


	/**
	* Main Joy_Of_Text_Plugin Class
	* @author SW
	*/
	final class Joy_Of_Text_Plugin {
		
		private static $_instance = null;
		public $debug;
		public $token;
		public $version;
		public $product;
                public $product_display_name;
		public $admin;
		public $settings;
                public $support_email;
		public $messenger;
		public $options;
		public $shortcodes;
                public $process_shortcodes;
                public $systeminfo;
		public $woocommerce;
		public $gravity;
		public $smsproviders;
		public $currentsmsprovider;     
		public $currentsmsprovidername;
		public $current_site;
		public $lastgrpid;
			
		public function __construct () {			
						
			$this->debug = true;
			
			
			$this->product = "JOT Pro";
                        $this->product_display_name = __("Joy Of Text Pro","jot-plugin");
                        $this->support_email = 'jotplugin@gmail.com';
			$this->token= 'jot-plugin';
			$this->version = '2.25.0';
									
			$this->plugin_url = plugin_dir_url( __FILE__ );
			$this->plugin_path = plugin_dir_path( __FILE__ );
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			require_once( 'classes/class-jot-plugin-settings.php' );
			$this->settings = Joy_Of_Text_Plugin_Settings::instance();
						
			require_once( 'classes/class-jot-plugin-messenger.php' );
			$this->messenger = Joy_Of_Text_Plugin_Messenger::instance();
			
			require_once( 'classes/class-jot-plugin-admin.php' );
			$this->admin = Joy_Of_Text_Plugin_Admin::instance();
				
			require_once( 'classes/class-jot-plugin-options.php' );
			$this->options = Joy_Of_Text_Plugin_Options::instance();
				
			require_once( 'classes/class-jot-plugin-shortcodes.php' );
			$this->shortcodes = Joy_Of_Text_Plugin_Shortcodes::instance();
                        
                        require_once( 'classes/class-jot-plugin-process-shortcodes.php' );
			$this->process_shortcodes = Joy_Of_Text_Plugin_Process_Shortcodes::instance();
                        
                        require_once( 'classes/class-jot-plugin-systeminfo.php' );
			$this->systeminfo = Joy_Of_Text_Plugin_SystemInfo::instance();
			
			// If Woocommerce is installed the show the JOT-Woo sync tab
			if (is_plugin_active('woocommerce/woocommerce.php')) {
			    require_once( 'classes/class-jot-plugin-woocommerce.php' );
			    $this->woocommerce = Joy_Of_Text_Plugin_Woocommerce::instance();
			}
			
			
			//register_activation_hook( __FILE__, array( $this, 'jot_activate' ) );
			register_activation_hook( 'joy-of-text/joy-of-text.php', array( $this, 'jot_activate' ) );
			
			add_action('init', array($this, 'initialise_plugin'));		
			add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
			add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));
			add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ));
			add_filter('query_vars', array($this,'messageid_query_vars'));
			add_action('parse_request', array($this,'parse_voicecall_request'));
			add_action('plugins_loaded', array($this,'check_classes') );
                        add_filter('cron_schedules', array($this,'new_interval'));
                        add_action('jot_table_maintenance', array($this,'jot_run_table_maintenance') );
                        add_action('jot_queue_sweeper', array($this,'jot_run_queue_sweeper') );
						
			//$this->smsproviders = $this->get_smsproviders();
			
			// Set currentprovider to Twilio. May add other providers at some point
			//$this->currentsmsprovidername = $this->settings->get_current_smsprovider();
			$this->currentsmsprovidername = 'twilio';
						
			if ($this->currentsmsprovidername != 'default' && !empty($this->currentsmsprovidername)) {
				require_once( 'classes/smsproviders/class-jot-provider-' . $this->currentsmsprovidername . '.php' );
				$this->currentsmsprovider = Joy_Of_Text_Plugin_Smsprovider::instance();
			} else {
				// Set the SMS provider to 'default'
				$smsprov =  get_option('jot-plugin-smsprovider');
				$smsprov['jot-smsproviders'] = 'default' ;   
				update_option('jot-plugin-smsprovider',$smsprov);
			}
		
			$this->lastgrpid = $this->jot_get_groupid();	
			
			//********************************************
			// Easy Digital Downloads plugin updater
			//********************************************
			
			if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
				// load our custom updater if it doesn't already exist 
				require_once( 'edd/EDD_SL_Plugin_Updater.php' );
			}
			
			// retrieve our license key from the DB
			$license_key = trim( $this->settings->get_network_smsprovider_settings('jot-eddlicence') ); 
			// setup the updater
			$edd_updater = new EDD_SL_Plugin_Updater( EDD_SL_STORE_URL_JOTPRO, __FILE__, array(
				'version' 	=> $this->version,	        // current version number
				'license' 	=> $license_key,	        // license key (used get_option above to retrieve from DB)
				'item_name'     => EDD_SL_ITEM_NAME_JOTPRO,	// name of this plugin
                                'item_id'       => EDD_SL_ITEM_ID_JOTPRO,	// id of this plugin
				'author' 	=> 'Stuart Wilson',	        // author of this plugin
				'url'           => home_url(),
                                'beta'          => false
			) );
			
			   
		} // End __construct()
		
		
		/**
		* Main Joy_Of_Text_Plugin Instance
		*
		* Ensures only one instance of Joy_Of_Text_Plugin is loaded or can be loaded.
		*
		*/
		public static function instance () {
			if ( is_null( self::$_instance ) )
			self::$_instance = new self();
			return self::$_instance;
		} // End instance()
		
		/**
		*
		* Initialise plugin
		*
		*/
		public function initialise_plugin() {
			
			// Load text domain.
			load_plugin_textdomain( 'jot-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
			// Apply version updates if applicable.
			$installed_version = get_option($this->token . '-version');
			if ($installed_version <> $this->version) {			   
			    $this->apply_version_updates();			    
			}
			
			
			
		
		} // Initialise plugin
	
		/*
		 *
		 * Check whether :
		 * 	Woocommerce
		 * 	Gravity Forms
		 *  are installed
		 *
		 */
		public function check_classes(){
			
			// If Woocommerce is installed then load the Woo class
			if (class_exists('WooCommerce')) {
			    require_once( 'classes/class-jot-plugin-woocommerce.php' );
			    $this->woocommerce = Joy_Of_Text_Plugin_Woocommerce::instance();
			}
			
			// If Gravity Forms is installed then load the GF class
			if (class_exists('GFForms')) {
			    require_once( 'classes/class-jot-plugin-gravity.php' );
			    $this->gravity = Joy_Of_Text_Plugin_Gravity::instance();			    
			}
			
		}	
	
		/**
		* Add settings link
		*/
		function plugin_action_links($links, $file) {
			
			static $this_plugin;
		     
			if (!$this_plugin) {
			    $this_plugin = plugin_basename(__FILE__);
			}
		     
			// check to make sure we are on the correct plugin
			if ($file == $this_plugin) {
			    // the anchor tag and href to the URL we want. For a "Settings" link, this needs to be the url of your settings page
			    $settings_link = '<a href="admin.php?page=jot-plugin&tab=smsprovider">Settings</a>';
			    // add the link to the list
			    array_unshift($links, $settings_link);
			}
		     
			return $links;
		}
	
		/**
		* Cloning is forbidden.
		*/
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?','jot-plugin' ), '1.0.0' );
		} // End __clone()
		
		/**
		* Unserializing instances of this class is forbidden.
		*/
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?','jot-plugin' ), '1.0.0' );
		} // End __wakeup()
		
		
		function jot_activate($networkwide) {
			global $wpdb;
				     
                        // Setup schedules
                        $this->setup_schedule();
                                     
			if (function_exists('is_multisite') && is_multisite()) {
			    $this->messenger->log_to_file(__METHOD__,"1 Activating JOT on a Multisite. Networkwide >>". $networkwide . "<<" );
			    // check if it is a network activation - if so, create the tables for each blog id
			    if ($networkwide) {
                                $old_blog = $wpdb->blogid;
                                // Get all blog ids
                                $sql = "SELECT blog_id FROM " . $wpdb->prefix."blogs";
                                $blogids = $wpdb->get_col($sql);
                                foreach ($blogids as $blog_id) {
                                    switch_to_blog($blog_id);
                                    $this->create_tables($blog_id);
                                    $this->apply_updates($blog_id);
                                    $this->alter_tables($blog_id);
                                }
                                switch_to_blog($old_blog);				
			    } else {
			       //Create single site tables in a multi site env
			       $this->messenger->log_to_file(__METHOD__,"1 Activating JOT on a single site in a multisite env. Networkwide >>" . "<<" );
                               $this->create_tables('Single Site');
			       $this->apply_updates('Single Site');
			       $this->alter_tables('Single Site');
			    }
			} else {
			   //Create single site tables
			   $this->messenger->log_to_file(__METHOD__,"1 Activating JOT on a Single Site. Networkwide >>" . "<<" );
                           $this->create_tables('Single Site');
			   $this->apply_updates('Single Site');
			   $this->alter_tables('Single Site');
			}
			
			// Log activation
			$data = array();
                        $url = "http://www.getcloudsms.com/log.php";
                        $jot_response = Joy_Of_Text_Plugin()->messenger->call_curl($url,$data,'get'); 
				
			// Add Capability and role
			$this->add_capability();
			
			// Set new version
			$this->_log_version_number();                        
                        $this->messenger->log_to_file(__METHOD__,"1 Completed activation of JOT to Version " . $this->version);
		}
		
		public function apply_version_updates() {
			global $wpdb;
				     
                        // Check if schedule already exists.                      
                        $this->setup_schedule();
                       
                        
                        // v2.0.22 - Copy licence key to network option
                        // So it can be used on all multisites
                                                
                        if (is_main_site()) {
                            $licence       = $this->settings->get_smsprovider_settings('jot-eddlicence');
                            $licencestatus = $this->settings->get_smsprovider_settings('jot-eddlicencestatus');                        
                            $this->settings->set_network_smsprovider_settings('jot-eddlicence',$licence);
                            $this->settings->set_network_smsprovider_settings('jot-eddlicencestatus',$licencestatus);
                            $this->messenger->log_to_file(__METHOD__,"Setting licence : " . $licence);
                            $this->messenger->log_to_file(__METHOD__,"Setting licence status : " , $licencestatus);
                        }
                        
                        // v2.24.1 - create an admin group if admin number had been set and add admin number to it.
                        $jot_adminnum  = $this->settings->get_smsprovider_settings('jot-adminnum');
                        if ($jot_adminnum != "") {
                            // Add new admin group
                            $admin_group_name = __("JOT Admin Group","jot-plugin");
                            $jot_groupid_arr = $this->options->process_add_group($admin_group_name, $admin_group_name);
                            $this->messenger->log_to_file(__METHOD__,"Creating admin group >" . print_r($jot_groupid_arr,true));
                            
                            $jot_groupid = isset($jot_groupid_arr['lastid']) ? $jot_groupid_arr['lastid'] : 0;
    
                            // Get member name
                            $memarr = $this->messenger->get_member_from_num($jot_adminnum);
                            if (isset($memarr['jot_grpmemname'])) {
                                $admin_name = $memarr['jot_grpmemname'];
                            } else {
                                $admin_name = $jot_adminnum;
                            }
                            
                            $this->messenger->log_to_file(__METHOD__,"Creating admin group >" . $jot_groupid . "<>" . $admin_name . "<>" . print_r($memarr,true));
                            
                            if ($jot_groupid != 0) {
                                $this->settings->set_smsprovider_settings('jot-admingroup',$jot_groupid);
                                $this->settings->set_smsprovider_settings('jot-adminnum',"");
                                $addmember = $this->options->process_add_member($admin_name,
                                                                                $jot_adminnum,
                                                                                $jot_groupid);
                                $this->messenger->log_to_file(__METHOD__,"Creating admin group >" . print_r($addmember,true));
                            }
                        }
                        
                        $jot_inbsmsnum  = $this->settings->get_smsprovider_settings('jot-inbsmsnum');
                        if ($jot_inbsmsnum != "") {
                            // Add new notification group
                            $notif_group_name = __("JOT Notification Group","jot-plugin");
                            $jot_groupid_arr = $this->options->process_add_group($notif_group_name, $notif_group_name);
                            $this->messenger->log_to_file(__METHOD__,"Creating notification group >" . print_r($jot_groupid_arr,true));
                            
                            $jot_groupid = isset($jot_groupid_arr['lastid']) ? $jot_groupid_arr['lastid'] : 0;
    
                            // Get member name
                            $memarr = $this->messenger->get_member_from_num($jot_inbsmsnum);
                            if (isset($memarr['jot_grpmemname'])) {
                                $admin_name = $memarr['jot_grpmemname'];
                            } else {
                                $admin_name = $jot_adminnum;
                            }
                            
                            $this->messenger->log_to_file(__METHOD__,"Creating notification group >" . $jot_groupid . "<>" . $admin_name . "<>" . print_r($memarr,true));
                            
                            if ($jot_groupid != 0) {
                                $this->settings->set_smsprovider_settings('jot-inbnotgroup',$jot_groupid);
                                $this->settings->set_smsprovider_settings('jot-inbsmsnum',"");
                                $addmember = $this->options->process_add_member($admin_name,
                                                                                $jot_inbsmsnum,
                                                                                $jot_groupid);
                                $this->messenger->log_to_file(__METHOD__,"Creating notification group >" . print_r($addmember,true));
                            }
                        }
                        
			if (function_exists('is_multisite') && is_multisite()) {
				// Update all multisite tables	
				$this->messenger->log_to_file(__METHOD__,"2 Updating JOT on a Multisite to Version " . $this->version);
				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$sql = "SELECT blog_id FROM " . $wpdb->prefix."blogs";
				$blogids = $wpdb->get_col($sql);
				foreach ($blogids as $blog_id) {
				    switch_to_blog($blog_id);
				    $this->create_tables($blog_id);
				    $this->alter_tables($blog_id);
				}
				switch_to_blog($old_blog);				
			    
			} else {
			   //Update single site tables
			   $this->messenger->log_to_file(__METHOD__,"2 Updating JOT on a single site" );
                           $this->create_tables('Single Site');
			   $this->alter_tables('Single Site');
			}
						
			// Set new version
			$this->_log_version_number();
			$this->messenger->log_to_file(__METHOD__,"2 Completed update of JOT to Version " . $this->version);
		}
		
		/**
		* Installation. Runs on activation.
		*/
		public function create_tables ($blog_id) {
			global $wpdb;
			
			$this->messenger->log_to_file(__METHOD__,"Creating/updating tables for Blog : " . $blog_id );
			
			if (USING_SQLITE == 'false') { 
				$updateclause = " ON UPDATE CURRENT_TIMESTAMP";
			} else {
				$updateclause = "";
			}
			// Create groups table
			$table = $wpdb->prefix."jot_groups";
			$structure = "CREATE TABLE $table (
			    jot_groupid        INT(9) NOT NULL AUTO_INCREMENT,
			    jot_groupname      VARCHAR(40) NOT NULL,
			    jot_groupdesc      VARCHAR(60) NOT NULL,
			    jot_groupoptout    VARCHAR(20) NOT NULL,
			    jot_groupopttxt    VARCHAR(160) NOT NULL,
			    jot_groupallowdups BOOLEAN DEFAULT 0,
			    jot_groupautosub   BOOLEAN DEFAULT 0,
			    jot_ts             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			    UNIQUE KEY jot_groupid (jot_groupid)
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
			$this->messenger->log_to_file(__METHOD__,"Run dbdelta for " . $table . " Return : " . print_r($return,true) );
			
			// Create group meta table
			$table = $wpdb->prefix."jot_groupmeta";
			$structure = "CREATE TABLE $table (
			    jot_groupmetaid    INT(9)       NOT NULL AUTO_INCREMENT,
			    jot_groupid        VARCHAR(40)  NOT NULL,
			    jot_groupmetakey   VARCHAR(255) NOT NULL,
			    jot_groupmetaval   LONGTEXT     NOT NULL,
			    UNIQUE KEY jot_groupmetaid (jot_groupmetaid)
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
			$this->messenger->log_to_file(__METHOD__,"Run dbdelta for " . $table . " Return : " . print_r($return,true) );
						
			// Create group members table
			$table = $wpdb->prefix."jot_groupmembers";
			$structure = "CREATE TABLE $table (
			    jot_grpmemid      INT(9) NOT NULL AUTO_INCREMENT,			    
			    jot_grpmemname    VARCHAR(40) NOT NULL,
			    jot_grpmemnum     VARCHAR(40) NOT NULL,
			    jot_grpmemstatus  INT(2) NOT NULL,
			    jot_grpmememail   VARCHAR(90) NOT NULL,
			    jot_grpmemaddress VARCHAR(240) NOT NULL,
			    jot_grpmemcity    VARCHAR(40) NOT NULL,
			    jot_grpmemstate   VARCHAR(40) NOT NULL,
			    jot_grpmemzip     VARCHAR(20) NOT NULL,                           
			    jot_grpmemts      TIMESTAMP DEFAULT CURRENT_TIMESTAMP$updateclause,
			    UNIQUE KEY jot_grpmemid (jot_grpmemid)
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
			$this->messenger->log_to_file(__METHOD__,"Run dbdelta for " . $table . " Return : " . print_r($return,true) );
			
                        // Create index on member phone number (added to speed up history retrieval)
                        
                        $table = $wpdb->prefix."jot_groupmembers";
                        $index_grpmemnum =  $table . "_indx_grpmemnum";
                        if ($wpdb->get_var("SHOW INDEX FROM " . $table . " WHERE KEY_NAME = '" . $index_grpmemnum .  "'") == $table) {
                            $this->messenger->log_to_file(__METHOD__, "Index " . $index_grpmemnum . " already exists. No action.");
                        } else {
                            $structure = "CREATE INDEX " . $index_grpmemnum  . " ON $table (jot_grpmemnum);";
                            $return = $wpdb->query($structure);
                            $this->messenger->log_to_file(__METHOD__,"Run query for " . $index_grpmemnum . " Index. Return : " . $return );                           
                        }
			
			// Create group to members crossref table
			$table = $wpdb->prefix."jot_groupmemxref";
			$structure = "CREATE TABLE $table (
			    jot_grpid         INT(9) NOT NULL,
			    jot_grpmemid      INT(9) NOT NULL,
			    jot_grpxrefts     TIMESTAMP DEFAULT CURRENT_TIMESTAMP$updateclause,
			    UNIQUE KEY jot_grpmemxref (jot_grpid, jot_grpmemid )
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
			$this->messenger->log_to_file(__METHOD__,"Run dbdelta for " . $table . " Return : " . print_r($return,true) );
			
						
			// Create group invite table
			$table = $wpdb->prefix."jot_groupinvites";
			$structure = "CREATE TABLE $table (
			    jot_grpid          INT(9) NOT NULL,
			    jot_grpinvdesc     VARCHAR(60) NOT NULL,
			    jot_grpinvnametxt  VARCHAR(40) NOT NULL,
			    jot_grpinvnumtxt   VARCHAR(40) NOT NULL,
			    jot_grpinvretchk   BOOLEAN DEFAULT 1,
			    jot_grpinvrettxt   VARCHAR(640) NOT NULL,
			    jot_grpinvaddkeyw  VARCHAR(20) NOT NULL,
			    jot_grpinvmesstype CHAR(1) NOT NULL,			  
			    jot_grpinvaudioid  VARCHAR(20) NOT NULL,
			    jot_grpinvts       TIMESTAMP DEFAULT CURRENT_TIMESTAMP$updateclause,
			    UNIQUE KEY jot_grpinvid (jot_grpid)
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
			$this->messenger->log_to_file(__METHOD__,"Run dbdelta for " . $table . " Return : " . print_r($return,true) );
			
			
			// Messages table added in V1.05
			$table = $wpdb->prefix."jot_messages";
			$structure = "CREATE TABLE $table (
			    jot_messautoid     INT(9) NOT NULL AUTO_INCREMENT,
			    jot_messageid      VARCHAR(30) NOT NULL,
                            jot_messagenum     VARCHAR(40) NOT NULL,
			    jot_messagecontent VARCHAR(1600) NOT NULL,
			    jot_messageaudio   VARCHAR(20) NOT NULL,
			    UNIQUE KEY jot_messautoid (jot_messautoid)			   
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
			$this->messenger->log_to_file(__METHOD__,"Run dbdelta for " . $table . " Return : " . print_r($return,true) );
			
			
			// Message History - premium version only
			$table = $wpdb->prefix."jot_history";
			$structure = "CREATE TABLE $table (
			    jot_histid          INT(9) NOT NULL AUTO_INCREMENT,
			    jot_histsid         VARCHAR(40) NOT NULL,
			    jot_histdir         CHAR(1),
			    jot_histmemid       INT(9) NOT NULL,
			    jot_histfrom        VARCHAR(40) NOT NULL,
			    jot_histto          VARCHAR(40) NOT NULL,
			    jot_histprovider    VARCHAR(20) NOT NULL,
			    jot_histmesscontent VARCHAR(1600) NOT NULL,
			    jot_histmesstype    CHAR(1),
                            jot_histmesssubtype CHAR(2),
                            jot_histerrcode     INT(9) NOT NULL,
			    jot_histstatus      VARCHAR(40) NOT NULL,
			    jot_histmedia       VARCHAR(20) NOT NULL,
			    jot_histprice       DECIMAL(12, 2) NOT NULL DEFAULT 0,
                            jot_histbatchid     VARCHAR(50) NOT NULL,
			    jot_histts          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			    UNIQUE KEY jot_histid (jot_histid)			   
			)
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
                        $this->messenger->log_to_file(__METHOD__,$structure);
			$this->messenger->log_to_file(__METHOD__,"Run dbdelta for " . $table . " Return : " . print_r($return,true) );
			
                        
			// Message queue table 
			$table = $wpdb->prefix."jot_messagequeue";
			$structure = "CREATE TABLE $table (
			    jot_messqid         INT(9) NOT NULL AUTO_INCREMENT,
			    jot_messqbatchid    VARCHAR(50) NOT NULL,
			    jot_messqgrpid      INT(9) NOT NULL,
			    jot_messqmemid      INT(9) NOT NULL,
			    jot_messqcontent    VARCHAR(1600) NOT NULL,
			    jot_messqtype       CHAR(1) NOT NULL,
                            jot_messqfromnumber VARCHAR(40) NOT NULL,
			    jot_messqstatus     CHAR(1) NOT NULL,
			    jot_messqaudio      VARCHAR(20) NOT NULL,
			    jot_messsenderid    VARCHAR(11) NOT NULL,
			    jot_messqschedts    TIMESTAMP NOT NULL,
			    jot_messqts         TIMESTAMP NOT NULL,
			    UNIQUE KEY jot_messqid (jot_messqid)
			)   ENGINE=InnoDB
			    CHARACTER SET utf8 
			    COLLATE utf8_unicode_ci
			;";
			$return = dbDelta($structure);
			$this->messenger->log_to_file(__METHOD__,"Run dbdelta for " . $table . " Return : " . print_r($return,true) );
			
						
			// If jot_groups table is empty then insert the default group
			$lastgrpid = $this->jot_get_groupid(); 
			if ($lastgrpid == 0) {
				$data = array(
				'jot_groupname' => __("My customer group","jot-plugin"),
				'jot_groupdesc' => __("My customer group","jot-plugin"),
				'jot_groupoptout' => "",
				'jot_groupopttxt' => "",
				'jot_groupallowdups' => 0,
				'jot_groupautosub' => 0,
				'jot_ts' => current_time('mysql', 0)
				);				
				
				$table = $wpdb->prefix."jot_groups";
				$sqlerr=$wpdb->insert( $table, $data );
				      
			} 		
						
			
		} // End install()
		
		public function apply_updates($blog_id) {
									
			// Apply any updates specific to this version
			$installed_version = get_option($this->token . '-version');
			
			$migrated = get_option($this->token. '-migrated');
			$this->messenger->log_to_file(__METHOD__,"Installed version : " . $installed_version . " This version : " . $this->version . " Migrated: " . $migrated);
			
			if ($migrated != 'yes') {
			  
				$this->messenger->log_to_file(__METHOD__,"-- Applying updates for Blog : " . $blog_id );
				// Check xref exists
				// check if xref empty - if so
				
				global $wpdb;			
				
			     
			       $table = $wpdb->prefix."jot_groupmemxref";
			       if($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
				  $this->messenger->log_to_file(__METHOD__,"-- xref table does exist : " . $blog_id . " " . $table );
				
				  // Get the most recently added record for each unique number
				  $tablexref   = $wpdb->prefix."jot_groupmemxref";
				  $tablegrpmem = $wpdb->prefix."jot_groupmembers";
				  $sql = "INSERT INTO " . $tablexref . " (jot_grpid, jot_grpmemid) " . 
				         " SELECT jot_grpid, jot_grpmemid " .
					 " FROM ( " .
					 " SELECT LEFT(REVERSE(jot_grpmemnum),10) AS choppednum, MAX(jot_grpmemts) AS maxts " .
                                         " FROM " . $tablegrpmem . 
					 " GROUP BY choppednum " .
                                         " ) a, " . $tablegrpmem  . " b " .
                                         " WHERE b.jot_grpmemts = a.maxts " .
                                         " AND a.choppednum = LEFT(REVERSE(b.jot_grpmemnum),10) " .
					 " AND b.jot_grpid != 0"; 
					 
				  $rowsinserted = $wpdb->query($sql);
				  $this->messenger->log_to_file(__METHOD__,"-- SQL 1 return ". $wpdb->last_error . " <<>> " . $sql . "<< Rows updated :" . $rowsinserted);
				
				  // For each number add in the remaining group entries
				  $tablexref = $wpdb->prefix."jot_groupmemxref";
				  $tablegrpmem = $wpdb->prefix."jot_groupmembers";
							
				  $sql = "INSERT INTO " . $tablexref . " (jot_grpid, jot_grpmemid) " . 
				         " SELECT c.jot_grpid, a.jot_grpmemid " . 
				         " FROM " . $tablegrpmem . " a," . $tablexref . " b," . $tablegrpmem . " c ".
				         " WHERE a.jot_grpid = b.jot_grpid " .
				         " AND a.jot_grpmemid = b.jot_grpmemid " .
					 " AND a.jot_grpid != 0 " .
				         " AND a.jot_grpmemnum LIKE " .
                                         "   CASE WHEN substr( c.jot_grpmemnum, 1 ) = '+' " .
                                         "    THEN CONCAT( '%', SUBSTR( c.jot_grpmemnum, 3 ) ) ".
                                         "    ELSE c.jot_grpmemnum " .
                                         "   END " .  
				         " AND b.jot_grpid <> c.jot_grpid ";
					 
				  $wpdb->query($sql);
				  $this->messenger->log_to_file(__METHOD__,"-- SQL 2 return ". $wpdb->last_error . " <<>> " . $sql . "<< Rows updated :" . $rowsinserted);
			          $this->_set_migration_flag('yes');
			       } else {
				  $this->messenger->log_to_file(__METHOD__,"-- xref table does NOT exist : " . $blog_id . " " . $table );
			       }
		       }			
		}
		
		public function alter_tables($blog_id) {
			
			global $wpdb;
			$this->run_alter_sql($blog_id,$wpdb->prefix."jot_groups");
			$this->run_alter_sql($blog_id,$wpdb->prefix."jot_groupmembers");
			$this->run_alter_sql($blog_id,$wpdb->prefix."jot_groupmemxref");
			$this->run_alter_sql($blog_id,$wpdb->prefix."jot_groupinvites");
			$this->run_alter_sql($blog_id,$wpdb->prefix."jot_messages");
			$this->run_alter_sql($blog_id,$wpdb->prefix."jot_history");
			$this->run_alter_sql($blog_id,$wpdb->prefix."jot_messagequeue");		
			
		}
		
		public function run_alter_sql($blog_id, $table) {
			global $wpdb;
			
			$sql = "ALTER TABLE " . $table . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci";
			$wpdb->query($sql);
			
			$this->messenger->log_to_file(__METHOD__,"-- Altering table for blog " . $blog_id . " " . $sql . " Last error :" .  $wpdb->last_error);
			
		}
		
		
		/**
		* Log the plugin version number.
		*/
		private function _log_version_number () {
			// Log the version number.
			update_option( $this->token . '-version', $this->version );
		} // End _log_version_number()
		
		/**
		* Set migration flag.
		*/
		private function _set_migration_flag ($value) {
			// Log the version number.
			update_option( $this->token . '-migrated', $value);
		} // End _log_version_number()
		
		
						
		/**
		* Registers and enqueues admin-specific minified JavaScript.
		*/
	       public function enqueue_scripts() {
				    
			
			if (!is_admin()) {
			    wp_enqueue_script('jquery');
			}

			// Include in admin_enqueue_scripts action hook
			wp_enqueue_media();
			wp_enqueue_script( 'custom-header' );

			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-ui-tooltip');
			wp_enqueue_script('jquery-ui-dialog');
                        $version_suffix = str_replace('.','-',$this->version);
                        
			wp_register_style('jot-css', plugins_url('css/jot-' . $version_suffix . '.css',__FILE__ ));
			wp_enqueue_style('jot-css');
			$version_suffix = str_replace('.','-',$this->version);
			wp_register_script( 'jot-js', plugins_url( 'joy-of-text/js/jot-pro-messenger-' . $version_suffix . '.js'),false,false,true );
			wp_enqueue_script( 'jot-js' );
			
			// Enqueue CSS and script for JQuery UI
			wp_register_style('jot-uitheme-css', plugins_url('css/jquery-ui-fresh.css?ver='. $this->version ,__FILE__ ));
			wp_enqueue_style('jot-uitheme-css');			
			
			// Enqueue backend buttons
			wp_enqueue_style('buttons');
			
			wp_localize_script( 'jot-js', 'ajax_object',
			       array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
			
			wp_localize_script( 'jot-js', 'wp_vars',
			       array( 'wp_admin_url' => admin_url() ) );
			
			wp_localize_script( 'jot-js', 'jot_db',
			       array( 'usingsqlite' => USING_SQLITE ) );			
			
			
			wp_localize_script( 'jot-js', 'jot_plugin',
			       array( 'referrer' => isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "" ) );
			
			wp_localize_script ( 'jot-js', 'jot_images',
				array( 'saveimg' => plugins_url( 'joy-of-text/images/save.png', dirname(__FILE__) ),
				       'addimg'  => plugins_url( 'joy-of-text/images/add.png', dirname(__FILE__) ),
				       'delimg'  => plugins_url( 'joy-of-text/images/delete.png', dirname(__FILE__) ),
                                       'trashimg'  => plugins_url( 'joy-of-text/images/trash.png', dirname(__FILE__) ),
				       'sendimg' => plugins_url( 'joy-of-text/images/send.png', dirname(__FILE__) ),
				       'spinner' => plugins_url( 'joy-of-text/images/ajax-loader.gif', dirname(__FILE__) )
				      ) );
                        
                        wp_localize_script ( 'jot-js', 'jot_sounds',
				array( 'ting' => plugins_url( 'joy-of-text/sounds/ting.wav', dirname(__FILE__) ),
                                       'open' => plugins_url( 'joy-of-text/sounds/open.wav', dirname(__FILE__) ),
                                       'send' => plugins_url( 'joy-of-text/sounds/send.wav', dirname(__FILE__) )
				      ) );
			
					
			
			//wp_localize_script ( 'jot-js', 'jot_help',
			//	array( 'helpurl' => plugins_url( 'joy-of-text/help/jot-help.html', dirname(__FILE__) )
			//	      ) );
			
			wp_localize_script ( 'jot-js', 'jot_number',
				array( 'number' => $this->get_current_fromnumber() ));
			
                        $frontend_strings = $this->get_frontend_strings();
                        $backend_strings  = $this->get_backend_strings();
                        $combined_strings = $frontend_strings + $backend_strings;
			wp_localize_script ( 'jot-js', 'jot_strings',$combined_strings);
						
			wp_localize_script ( 'jot-js', 'jot_product',
				array( 'item' => EDD_SL_ITEM_NAME_JOTPRO ));
			
			wp_localize_script( 'jot-js', 'jot_woo',
			       array( 'logfile' => plugins_url("/joy-of-text/log/jotwoosync.log") ) );
			
			if ( isset($_GET['lastid'])) {
				$id = $_GET['lastid'];
			} else {
				$id = 1;
			}
			
			wp_localize_script( 'jot-js', 'jot_lastgroup', array( 'id' => $id ));
	    
                } // end register_admin_scripts

	        function get_frontend_strings() {
		
                    $frontend_strings =  array(
                                       'grpsub' => __("Subscribing you to the group...","jot-plugin"),
                                       'confcode' => __("Retrieving your confirmation code...","jot-plugin"),
                                       'msgsent' => __("Messages sent :","jot-plugin"),
                                       'msgqueued' => __("Messages queued.","jot-plugin"),
                                       'msgsentshortly' => __("Messages will be sent shortly.","jot-plugin"),
                                       'viewlog'    => __("View Log","jot-plugin")
				    );
                    
                    return apply_filters('jot_get_frontend_strings',$frontend_strings);
                }
                
                function get_backend_strings() {
		
                    $twilio_spam_guidance = "<a href='https://support.twilio.com/hc/en-us/articles/223181848-How-Does-Carrier-Filtering-Work-' target='_BLANK'>" . __("Twilio's spam guidance","jot-plugin") . "</a>";
                    $backend_strings =  array( 'saveinv' => __("Saving invite details....","jot-plugin"),
				       'savegrp' => __("Saving group details","jot-plugin"),				      
				       'sendmsg' => __("Sending messages....","jot-plugin"),
				       'queuemsg' => __("Queuing messages....","jot-plugin"),
				       'sentmsg' => __("Message Sent at","jot-plugin"),
				       'sentallmsg' => __("Message processing complete.","jot-plugin"),
				       'addgrp' => __("Adding group....","jot-plugin"),
				       'selectrecip' => __("Select message recipients","jot-plugin"),
                                       'enterrecip' => __("Enter message recipients","jot-plugin"),
				       'number' => __("Number","jot-plugin"),
				       'status' => __("Status Message","jot-plugin"),
				       'groupdelete' => __("Are you sure you want to delete this group?","jot-plugin"),
                                       'confirmmemalldel' => __("Are you sure you want to delete this member from ALL groups?","jot-plugin"),
				       'addroutenum' => __("Please add a number to route SMS messages to","jot-plugin"),
				       'histitemdelete' => __("Are you sure you want to delete this history item?","jot-plugin"),
				       'numbernotavailable' => __("Number not available","jot-plugin"),
				       'helptitle' => __("Joy Of Text Help","jot-plugin"),
				       'proccomplete' => __("Processing complete.","jot-plugin"),
				       'messagesent' => __("Messages sent","jot-plugin"),
				       'sendwarning' => __("do not leave this page until all messages are sent.","jot-plugin"),
                                       'configuring' => __("Configuring...","jot-plugin"),
				       'refreshing' => __("Refreshing...","jot-plugin"),
				       'confirmmemrem' => __("Are you sure you want to remove this member from the group?","jot-plugin"),
				       'confirmmemdel' => __("Are you sure you want to delete this member?","jot-plugin"),
				       'confirmhistdel' => __("Are you sure you want to delete history over xxx days old?","jot-plugin"),
				       'confirmhistkeep' => __("Are you sure you want keep all history?","jot-plugin"),
                                       'scheduled' => __("Messages have been scheduled","jot-plugin"),
				       'woodelconfirm' => __("The existing members in the selected JOT group, will be deleted before the sync from Woocommerce. Are you sure?","jot-plugin"),
                                       'rejectednumbers' => __("These numbers were rejected : ","jot-plugin"),
                                       'messagelimitwarning' => sprintf(__("WARNING : Sending over 250 messages in one go, may cause your messages to be blocked as spam. See %s","jot-plugin"),$twilio_spam_guidance),
                                      
				    );
                   
                    return apply_filters('jot_get_backend_strings',$backend_strings);
                }

		       
	       
	       function messageid_query_vars($vars) {
		   $vars[] = 'messageid';
		   $vars[] = 'inbound';
		   return $vars;
		}
	 
	       function parse_voicecall_request($wp) {
			// only process requests with "messageid"
			if (array_key_exists('messageid', $wp->query_vars)) {		    
			    // process the request.
			    $this->currentsmsprovider->get_callmessage();
			}
			// only process requests with "inbound"
			if (array_key_exists('inbound', $wp->query_vars)) {		    
			    // process the request.
			    $this->currentsmsprovider->process_inbound_sms();
			}
			
			return $wp;
			
		}
		
		function parse_inbound_sms_request(){
			if( is_singular() && get_query_var( 'inbound' ) ) {
				$this->currentsmsprovider->process_inbound_sms();
			}	    		
		}
		
		
	       /**
		* Reads SMS provider details from an ini file
		*/
	       public function get_smsproviders() {
	           
		   return parse_ini_file( 'jot.ini',true);
		   
	       }
	       
	       function jot_get_groupid() {
		     global $wpdb;
		     
		     
		     $table = $wpdb->prefix."jot_groups";
		     if($wpdb->get_var("SHOW TABLES LIKE '$table'") == $table) {
			$result = $wpdb->get_row("select max(jot_groupid) as jot_groupid from ". $table );
			if(count($result) == 0) {
			   return 0;
			} else {
			   return $result->jot_groupid;
			}
		     } else {
			return 0;
		     }
	       }
	       
	       public function get_current_fromnumber() {
		   // Get current selected number
		   $smsprovider = get_option('jot-plugin-smsprovider');
                   $selected_provider = Joy_Of_Text_Plugin()->currentsmsprovidername;
		   if ($selected_provider != 'default') {           
			return Joy_Of_Text_Plugin()->settings->get_smsprovider_settings('jot-phonenumbers-' . $selected_provider);
		   } else {
			// No number selected yet
			return 0;
		   }
		}
		
		function add_capability() {
			
			$this->messenger->log_to_file(__METHOD__,"Adding role and assigning capabilities");
			// Add role for a basic JOT User
			remove_role('jot_user');
			$result = add_role(
				'jot_user_role',
				__( 'Joy Of Text User' ),
				array(
				    'read'                => true,				    
				    'jot_user_capability' => true
				)
			);
			
			// gets the admin
			$role = get_role( 'administrator' );
			
			// Add JOT admin capability
			$role->add_cap( 'jot_admin_capability' );
			
			// Add JOT user capability
			$role->add_cap( 'jot_user_capability' );
						
		}
                
                // add 10 minute interval to wp schedules
		function new_interval($interval) {
		    if (array_key_exists('minutes_10', $interval)) {
			unset($interval['minutes_10']);			
		    } 
		    $interval['minutes_10'] = array('interval' => 10*60, 'display' => '10 minute interval');
		    
		    return $interval;
		}
                
                
                public function setup_schedule() {
						
			wp_clear_scheduled_hook( 'jot_table_maintenance' );
			$maint_timestamp = wp_next_scheduled( 'jot_table_maintenance' );

			//If $maint_timestamp == false schedule hasn't been done previously
			if( $maint_timestamp == false ){			  
			  wp_schedule_event( time(), 'daily', 'jot_table_maintenance');
			}
                        
                        
                        wp_clear_scheduled_hook( 'jot_queue_sweeper' );
			$sweeper_timestamp = wp_next_scheduled( 'jot_queue_sweeper' );

			//If $timestamp == false schedule hasn't been done previously
			if( $sweeper_timestamp == false ){			  
			  wp_schedule_event( time(), 'minutes_10', 'jot_queue_sweeper');
			}
			
                        
		}
                
                public function jot_run_table_maintenance() {
                    // Tidy up message history and message queue tables            
                    $this->settings->delete_history(); 
                    $this->settings->delete_messagequeue();
                    
                    do_action('jot_run_table_maintenance');
                }
                
                
                public function jot_run_queue_sweeper() {
                    // Process any pending messages in the queue table            
                    $this->messenger->queue_sweeper();                               
                }
                              
                
                
		
	} // End Class

?>