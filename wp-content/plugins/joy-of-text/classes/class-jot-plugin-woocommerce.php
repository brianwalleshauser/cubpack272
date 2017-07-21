<?php
/**
* Joy_Of_Text Woocommerce. Synchronises Woocommerce order details with JOT groups.
*
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



final class Joy_Of_Text_Plugin_Woocommerce {
 
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/
 
    /**
     * Initializes the plugin 
     */
               function __construct() {
                    add_filter('jot-plugin-settings-sections', array($this,'add_woo_tab'),10,1 );		   
                    add_action('jot_render_extension_tab',array($this,'render_woo_tab'),10,1);
                    add_filter('jot_get_settings_fields',array($this, 'get_settings_fields'),10,2);
                    
                    add_action('wp_ajax_process_sync_woo_jot', array( $this, 'process_sync_woo_jot' ) );
                    add_action('woocommerce_payment_complete', array($this,'process_new_wooorder'));
                    add_action('woocommerce_order_status_changed', array( $this, 'check_woo_notifications' ), 10, 3 );
                   
                                    
               } // end constructor
            
               private static $_instance = null;
                   
               public static function instance () {
                   if ( is_null( self::$_instance ) )
                       self::$_instance = new self();
                   return self::$_instance;
               } // End instance()

               public function add_woo_tab($settings_sections) {
                   
                    $settings_sections['woo-manager'] = array(
                         'tabname'    => __( 'Woocommerce', 'jot-plugin' )                    
                    );
                    
                    return $settings_sections;
               }
               
               /**
		* Retrieve the settings fields details
		*/
		public function get_settings_fields ( $settings_fields, $section ) {
                    
                  		    
		    switch ( $section ) {
			case 'woo-manager':
                              $settings_fields['jot-woo-syncgrp'] = array(
                                   'name' => __( 'Select a JOT group:', 'jot-plugin' ),                                   
                                   'type' => 'select',
                                   'default' => 'false',
                                   'section' => 'woo-manager',
                                   'subform' => 'main',
                                   'description' => __( 'Select a JOT group to copy your WooCommerce customers into.', 'jot-plugin' )
                              );
                              $settings_fields['jot-woo-merge'] = array(
                                   'name' => __( 'Merge during sync?', 'jot-plugin' ),
                                   'label' => __( 'Merge during sync?', 'jot-plugin' ),
                                   'type' => 'checkbox',
                                   'default' => 'false',
                                   'section' => 'woo-manager',
                                   'subform' => 'main',
                                   'description' => __( 'Merge Woo customers into your selected JOT group? If not checked, the existing members of the JOT group will be deleted before the sync.', 'jot-plugin' )
                              );
                              $settings_fields['jot-woo-notif-type'] = array(
                                   'name' => __( 'Send notifications for:', 'jot-plugin' ),
                                   'type' => 'checkbox',
                                   'default' => 'jot-woo-completed',
                                   'section' => 'woo-manager',
                                   'options' => array('on-hold' => __('On Hold','jot-plugin'),
                                                      'pending' => __('Pending','jot-plugin'),
                                                      'processing' => __('Processing','jot-plugin'),
                                                      'completed' => __('Completed','jot-plugin')),
                                   'subform' => 'main',
                                   'description' => __( 'Select the orders statuses you want to send notifcations for.', 'jot-plugin' )
                              );                      
			      $tagurl = "<a href='http://www.getcloudsms.com/documentation/joy-text-supported-merge-tags/' target='_blank'>" . __("merge tags","jot-plugin") . "</a>";
                              $settings_fields['jot-woo-message'] = array(
                                  'name' => __( 'Enter your notification message', 'jot-plugin' ),
                                  'type' => 'textarea',
                                  'default' => '',
                                  'maxlength' => 640,
                                  'rows' =>5,
                                  'cols' =>100,
                                  'placeholder' => __("Enter your WooCommerce notification message","jot-plugin"),
                                  'section' => 'woo-manager',
                                  'subform' => 'main',
                                  'description' => sprintf( __("This message will be sent to your customer. You can include %s like %%woo_cust_firstname%% and %%woo_order_status%% into your message.","jot-plugin"),$tagurl)                                  
                              );
                              $settings_fields['jot-woo-admin-message'] = array(
                                  'name' => __( 'Enter your group notification message', 'jot-plugin' ),
                                  'type' => 'textarea',
                                  'default' => '',
                                  'maxlength' => 640,
                                  'rows' =>5,
                                  'cols' =>100,
                                  'placeholder' => __("Enter your WooCommerce group notification message","jot-plugin"),
                                  'section' => 'woo-manager',
                                  'subform' => 'main',
                                  'description' => sprintf( __("This message will be sent to the selected group. You can include %s like %%woo_cust_firstname%% and %%woo_order_status%% into your message. (optional).","jot-plugin"),$tagurl)                                  
                              );
                              
                              if (Joy_Of_Text_Plugin()->product == "JOT Pro") {
                                   $url = "<a href='" . admin_url() .   "admin.php?page=jot-plugin&tab=smsprovider&section=general'>" . __("Messaging-Settings-General Settings","jot-plugin") .  "</a>";
                                   
				   /*
				   $settings_fields['jot-woo-adminnum'] = array(
                                        'name' => __( 'Admin Number:', 'jot-plugin' ),
                                        'type' => 'text',
                                        'default' => '',
                                        'readonly' => true,
                                        'section' => 'woo-manager',
                                        'subform' => 'main',
                                        'description' => sprintf (__("Go to %s to amend your admin phone number.","jot-plugin"), $url),
                                   );
                                   */
				   
				   // Send group notification
				   $settings_fields['jot-woo-groupnotify'] = array(
                                        'name' => __( 'Select group:', 'jot-plugin' ),
                                        'type' => 'select',
                                        'default' => '',
                                        'readonly' => true,
                                        'section' => 'woo-manager',
                                        'subform' => 'main',
                                        'description' => sprintf (__("Select the group you wish to notify when the order status changes. For example, to a group of admin users (optional)","jot-plugin"), $url),
                                   );
				   $settings_fields['jot-woo-group-message'] = array(
					'name' => __( 'Enter your group notification message', 'jot-plugin' ),
					'type' => 'textarea',
					'default' => '',
					'maxlength' => 640,
					'rows' =>5,
					'cols' =>100,
					'placeholder' => __("Enter your WooCommerce group notification message.","jot-plugin"),
					'section' => 'woo-manager',
					'subform' => 'main',
					'description' => sprintf( __("This notification message will be sent to the selected group. (optional)","jot-plugin"),$tagurl)                                  
				   );
                              } elseif (Joy_Of_Text_Plugin()->product == "JOT Lite") {
                                   $settings_fields['jot-woo-adminnum'] = array(
                                        'name' => __( 'Admin Number:', 'jot-plugin' ),
                                        'type' => 'text',
                                        'default' => '',                                        
                                        'section' => 'woo-manager',
                                        'subform' => 'main',
                                        'description' => __( 'The admin number which will receive the notifications.', 'jot-plugin' ),
                                   );
                                   
                              }
			break;
		        
			default:
			    
			break;
		    }
		  		    
		    return (array) apply_filters( 'jot_woo_get_settings_fields', $settings_fields );
	       } // End get_settings_fields()
		
	       public function render_woo_tab($tabform) {
		    
		   if ($tabform == 'woo-manager-main' )	{
			
                        if (Joy_Of_Text_Plugin()->product == "JOT Lite") {
                              if (jot_woo()->jot_lite_required_version >  Joy_Of_Text_Plugin()->version) {
                                   echo "<h2>" . sprintf(__("You need the Joy Of Text Lite Version %s or above to run this extension","jot-plugin"),jot_woo()->jot_lite_required_version ) . "</h2>";
                                   echo "<h2>" . sprintf(__("You are currently using Joy Of Text Lite Version %s.","jot-plugin"), Joy_Of_Text_Plugin()->version ) . "</h2>";
                                   return;
                              }
                        } 
                        
			if( isset($_GET['settings-updated']) ) { 
				echo "<div id=\"message\" class=\"updated\">";
				echo "<p><strong>" . _e('Settings saved.') . "</strong></p>";
				echo "</div>";
			}
			
			echo $html = "<form id=\"jot-woo-fields-form\" action=\"options.php\" method=\"post\">";
			echo "<input type=\"hidden\"  name=\"settings_updated\" value=\"yes\">";
			settings_fields( 'jot-plugin-settings-woocommerce');                        
			echo $this->render_woo_settings("woo-manager");
			
			submit_button( __( "Save settings", "jot-plugin" ) );
			
			echo "</form>";			
			echo "<p><p>";
		        echo "<h3>" .  __("Synchronize WooCommerce to the Joy Of Text.","jot-plugin") ."</h3>";		       
                        echo "<p>";
			echo "<table>";
		        echo "<tr><td><input type=\"button\" id=\"jot-plugin-woo-manager[jot-woo-sync]\" class=\"button\" value=\"Sync WooCommerce to JOT now\">";
                        echo "<td>";
			echo "</td><td><div id=\"jot-woo-syncstatus-div\"></div></td></tr>";
			echo "</table>";
			echo "<p>";
			echo "<textarea rows='10' cols='100' id=\"jot-woo-syncstatus-textarea\">";
                        $woodetails = get_option('jot-plugin-woo-manager');
                        $log_entry = isset($woodetails['jot-woo-sync-log']) ? $woodetails['jot-woo-sync-log'] : "";
                        if ($log_entry != "") {
                          echo $log_entry;
                        }
                        echo "</textarea>";
		   }
	       }
               
               /**
		* Renders page for displaying Woo plugin settings
		*
		* @return string HTML markup for the field.
		*/
		public function render_woo_settings ($tab) {
			
		    $woodetails = get_option('jot-plugin-woo-manager');
                    
                    // If no group has been selected then set merge as true
                    if (!isset($woodetails['jot-woo-syncgrp'])) {                        
                         $woodetails['jot-woo-merge'] = 'true';
                         update_option('jot-plugin-woo-manager',$woodetails);
			 $woodetails = get_option('jot-plugin-woo-manager');			
                    }
                    
                    $html = "<h3>" .  __("WooCommerce Order Notifications.","jot-plugin") ."</h3>";
                    $currselections = array();
                    
                    if (isset($woodetails['jot-woo-notif-type'])) {
                         $currselections = $woodetails['jot-woo-notif-type'];
                    }
                                       
                    $html .= "<table class=\"jot-formtab form-table\">\n";
                                        
                    $html .= Joy_Of_Text_Plugin()->settings->render_row_options('jot-woo-notif-type', $currselections, $tab);
                    $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-woo-message','',isset($woodetails['jot-woo-message']) ? $woodetails['jot-woo-message'] : "",$tab);     
		    
		    // All groups
		    $jotgroups = Joy_Of_Text_Plugin()->messenger->get_display_groups();
                    foreach ($jotgroups as $jotgroup) {
                         $allgroups[$jotgroup->jot_groupid] = $jotgroup->jot_groupname;		    		    
                    }
		    
                    // Get admin number
                    if (Joy_Of_Text_Plugin()->product == "JOT Pro") {
                         			 
			 // Render group notification settings
			 $html .= Joy_Of_Text_Plugin()->settings->render_section_header(__("Send admin notifications","jot-plugin"));
			 $allgroups[99999999] = __("Do not notify group","jot-woo-plugin");
			 $currval = isset($woodetails['jot-woo-groupnotify']) ? $woodetails['jot-woo-groupnotify'] : 99999999;
                         $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-woo-groupnotify','',$allgroups,$currval,$tab);  
			
			 $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-woo-admin-message','',isset($woodetails['jot-woo-admin-message']) ? $woodetails['jot-woo-admin-message'] : "",$tab);     
		    	   
		    			 
                    } elseif (Joy_Of_Text_Plugin()->product == "JOT Lite") {
                         $jot_admin_num = isset($woodetails['jot-woo-adminnum']) ? $woodetails['jot-woo-adminnum'] : "";
			 $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-woo-adminnum','',$jot_admin_num,$tab);
			 $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-woo-admin-message','',isset($woodetails['jot-woo-admin-message']) ? $woodetails['jot-woo-admin-message'] : "",$tab);     
		    	
                    } 
                    
                    
                                       
                    $html .= "</table>";
                    
		    $html .= "<h3>" .  __("WooCommerce to JOT mapping.","jot-plugin") ."</h3>";
		    $html .= "<p class='description'>";
                    $html .= __("Choose which JOT Group you want WooCommerce customers added to.","jot-plugin");                    
                    $html .= "</p>";
                    $html .= "<p class='description'>";
                    $html .= __("WooCommerce customers will be added to the group when an order is completed or when you run the sync process.","jot-plugin");
		    $html .= "</p><p>";
                    $html .= "<table class=\"jot-formtab form-table\">\n";
		  
		    
		    $allgroups[99999999] = __("Do not sync","jot-woo-plugin");
                    $currval = isset($woodetails['jot-woo-syncgrp']) ? $woodetails['jot-woo-syncgrp'] : 99999999;
                    $html .= Joy_Of_Text_Plugin()->settings->render_row_multi('jot-woo-syncgrp','',$allgroups,$currval,$tab);  
                    $html .= Joy_Of_Text_Plugin()->settings->render_row('jot-woo-merge','',isset($woodetails['jot-woo-merge']) ? $woodetails['jot-woo-merge'] : true,$tab);     
		    	    
		    $html .= "</table>";
		     
		    $html =  apply_filters( 'jot_render_woo_settings', $html);
		    return $html;
               } // End render_woo_settings()
               
               
               public function process_sync_woo_jot() {
			
		    $woodetails = get_option('jot-plugin-woo-manager');
		    
		    $allvals = "";
		    $log_entry = $this->write_to_log(__("Sync started.","jot-plugin"));
		    
		    if (isset($woodetails['jot-woo-syncgrp']))  {			
                        $mapping = $woodetails['jot-woo-syncgrp'];
			
			if ($mapping != '99999999') {				   
			   $log_entry .= $this->sync_woo_group($mapping);
			}
			$log_entry .= $this->write_to_log("--------------------------------------------------------------------------------------");
		    } else {
			$log_entry .= $this->write_to_log(__("No JOT group selected","jot-plugin"));
		    }
		    $log_entry .= $this->write_to_log(__("Sync finished.","jot-plugin"));
		    
                    // Write log to option variable
                    $woodetails['jot-woo-sync-log'] = $log_entry;
                    update_option('jot-plugin-woo-manager',$woodetails);
                    
                    $response = array("log" => $log_entry);
		    echo json_encode($response);
		    wp_die();
			
		}
		
		public function sync_woo_group($woo_groupid) {
			
			$alladds = null;
			 		
			$memlist = $this->get_woo_members();			
			//echo ">>" . print_r($memlist);
			
			//Clear down JOT group first.
			$woodetails = get_option('jot-plugin-woo-manager');
                        
                        if (isset($woodetails['jot-woo-merge'])) {
                           $mergegrp = $woodetails['jot-woo-merge'];                         
                        } else {
                           $mergegrp = false;
                        }
                        
                        $log_entry = $this->write_to_log("--------------------------------------------------------------------------------------");
                        if ($mergegrp == false) {
                            $rowsdeleted = $this->clear_jotgroup($woo_groupid);
                            $log_entry .= $this->write_to_log("Deleting members from JOT group : " . Joy_Of_Text_Plugin()->messenger->get_jot_groupname($woo_groupid). " No. rows deleted : " . $rowsdeleted);
                        }
                       
                       	$log_entry .= $this->write_to_log("Sync'ing WooCommerce with JOT group " . Joy_Of_Text_Plugin()->messenger->get_jot_groupname($woo_groupid) );
			
			foreach ($memlist as $member) {
	                
                        	$addmember = Joy_Of_Text_Plugin()->options->process_add_member($member->first_name . " " . $member->last_name,
											       $member->phone,
											       $woo_groupid);					
				$log_entry .= $this->write_to_log($addmember['errormsg'] . " (" . $member->phone . ")" );
		
			}
                        return $log_entry;
		}
                
                
		
		public function get_woo_members($wooorder_id = null) {
			 global $wpdb;
                         
                         $woocustlist = array();
                         $sqlprep = "";
                  	                         
			 $table_woo_order_items  =  $wpdb->prefix."woocommerce_order_items";
                         
                         if ($wooorder_id == null) {
                              $sql = " SELECT DISTINCT order_id FROM " . $table_woo_order_items;
                              $orderlist = $wpdb->get_results( $sql );
                         } else {                              
                              $sql =   " SELECT DISTINCT order_id FROM " . $table_woo_order_items .
                                       " WHERE  order_id = %d";
                              $sqlprep   = $wpdb->prepare($sql, $wooorder_id);
                              $orderlist = $wpdb->get_results( $sqlprep );  
                         }
                         
			                                                 
                         foreach ($orderlist as $order ) {
                              $orderid     = $order->order_id;
                              $firstname   = get_post_meta( $orderid, '_billing_first_name' ); // Array
                              $lastname    = get_post_meta( $orderid, '_billing_last_name' );  // Array
                              
                              // Get Woo phone number and send to Twilio for verification
                              $phone       = get_post_meta( $orderid, '_billing_phone' );      // Array
                              $verified_number = Joy_Of_Text_Plugin()->currentsmsprovider->verify_number($phone[0]);                             
                              
                              // Construct array
                              $cust = new stdClass;    
                              $cust->first_name = $firstname[0];
                              $cust->last_name  = $lastname[0];
                              $cust->phone = $verified_number;
                              
                              $woocustlist[] = $cust;
                         }
			
			 return apply_filters('jot_get_woo_members', $woocustlist);			
			
	       }
		
	       public function clear_jotgroup($jot_groupid) {
			
		    global $wpdb;			
			
			
		    switch ( Joy_Of_Text_Plugin()->product ) {
			 case "JOT Lite":
			      // Delete all from groupmembers tables
			      $tablemem = $wpdb->prefix."jot_groupmembers";   // a
			      
			      $sql = "DELETE FROM " . $tablemem .  
			   	  " WHERE jot_grpid =  %d ";
			      $sqlprep = $wpdb->prepare( $sql, $jot_groupid);			
			      $rowsdeleted = $wpdb->query($sqlprep);
			      return $rowsdeleted;			                          
			 break;
		         case "JOT Pro":
			      // Delete all from group xref
			      $tablexref = $wpdb->prefix."jot_groupmemxref";   // a
			      
			      $sql = "DELETE FROM " . $tablexref .  
			   	  " WHERE jot_grpid =  %d ";
			      $sqlprep = $wpdb->prepare( $sql, $jot_groupid);			
			      $rowsdeleted = $wpdb->query($sqlprep);
			      return $rowsdeleted;
		         break;
			 default:
			      return 0;
			 break;
		    }			
		    
	       }
                
               public function write_to_log($text) {
        			
			$log_line = date('m/d/Y h:i:s a', time()) ." " . $text . "\r\n" ;
                        return $log_line;
			       
		}
                
               public function process_new_wooorder($order_id) {
                    
                   
                    $order = new WC_Order($order_id);
		    
                    //Should only be one order
                    $memlist = $this->get_woo_members($order_id);
                   
                    // Get group selected for Woocommerce folks 
                    $woodetails = get_option('jot-plugin-woo-manager');
		  
		    $allvals = "";
		    if ($woodetails)  {			
                        $woo_groupid = isset($woodetails['jot-woo-syncgrp']) ?  $woodetails['jot-woo-syncgrp'] : 99999999;
                    
                         if ($woo_groupid != 99999999) {
                              foreach ($memlist as $member) {
                                        $masked_number = substr_replace($member->phone, str_repeat("X", 4), 4, 4);
                                        $addmember = Joy_Of_Text_Plugin()->options->process_add_member($member->first_name . " " . $member->last_name,
                                                                                                       $member->phone,
                                                                                                       $woo_groupid);	
                              }
                         }
                    }
                    
                    
               }
	       
	       public function process_jotwoo_edd_activate_license() {
            
			$formdata   = $_POST['formdata'];    
			$licence    = isset($formdata['jot-eddlicence']) ? $formdata['jot-eddlicence'] : "";
			$product    = isset($formdata['jot-eddproduct']) ? $formdata['jot-eddproduct'] : EDD_SL_ITEM_NAME_JOTWOO;
			$statuskey  = 'jot-woo-eddlicencestatus';
			$licencekey = 'jot-woo-eddlicence';
			
			Joy_Of_Text_Plugin()->options->process_edd_activate_license($licence,$product,$statuskey,$licencekey);
		
               }
               
               
               public function check_woo_notifications($order_id, $old_status, $new_status ) {
                    
                    $order = new WC_Order($order_id);
                     
                    if (!$order) {
                         return;
                    }
                    
                    $woodetails = get_option('jot-plugin-woo-manager');
                    $notify_order_statuses = $woodetails['jot-woo-notif-type'];                    
                    
                    if (!is_array($notify_order_statuses)) {
                         return;
                    }
                                
		     if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__, "Orderid " .  $order_id  ." New status " . $new_status . " " . print_r($notify_order_statuses,true). " Check " . array_key_exists($new_status, $notify_order_statuses));
	       
                    if (array_key_exists($new_status, $notify_order_statuses) !== false) {
                         if ($notify_order_statuses[$new_status] == true) {
                              $this->send_customer_woo_notifications($order_id,$new_status);
                              $this->send_admin_woo_notifications($order_id, $new_status );
                         }             
                    }
               }
               
                
               public function send_customer_woo_notifications($order_id, $new_status ) {
                    
		    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"In send customer woo. Orderid:" . $order_id);
		    
                    $order = new WC_Order($order_id);                   
                       
                    if (!$order) {
                         return;
                    }
                    
                    $woodetails = get_option('jot-plugin-woo-manager');
                   
                    // Get message template.
                    $woodetails = get_option('jot-plugin-woo-manager');
                    $woo_message = isset($woodetails['jot-woo-message']) ? $woodetails['jot-woo-message'] : "";                    
                    
                    if ($woo_message != "") {
                         
                         // Get customer details for this order
                         $customer = $this->get_woo_members($order_id);
                         
                         // Replace woocommerce merge tags                         
                         $detagged_message_final = $this->get_replace_tags($woo_message, $customer[0], $order, $new_status, $order_id);
                                                    
                         // Use default Sender ID for welcome SMS if set
                         $senderid = "";               
                         $smsdetails = get_option('jot-plugin-smsprovider');
                         if (!empty($smsdetails['jot-smssenderid'])) {
                              $senderid = isset($smsdetails['jot-smssenderid']) ? $smsdetails['jot-smssenderid'] : "";
                         }    
                        
			 // Send message
                         $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($customer[0]->phone, $detagged_message_final,$senderid);
			 
			 $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                               'jot_messsubtype' => 'WC'
                                              );
                         $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o","",$message_error,$collate_args);
			 
                         if ($message_error['send_message_errorcode'] != 0) {
                              $masked_number = substr_replace($message_error['send_message_number'], str_repeat("X", 4), 4, 4);
                              Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>>> JOTWoo Cust Notification error : "
                                                                           . $message_error['send_message_msg']
                                                                           . " Order ID: " . $order_id
                                                                           . " Num: " . $masked_number
                                                                           );
                         }			 
                    }
                              
               }
               
               
               public function send_admin_woo_notifications($order_id, $new_status ) {
                    
		    
		    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"In send_admin_woo_notif orderid: " . $order_id);
                                     
                    // Send admin number for Lite,
		    // Send admin group for Pro
                    if (Joy_Of_Text_Plugin()->product == "JOT Pro") {
                         $this->send_notifications_to_group($order_id, $new_status );        
                    } elseif (Joy_Of_Text_Plugin()->product == "JOT Lite") {
                         $this->send_notifications_to_admin($order_id, $new_status ); 
                    } else {
                         return;
                    }
                              
               }
               
	       public function send_notifications_to_admin($order_id, $new_status ) {
                    
		    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,"In send_not_to_admin orderid:" . $order_id);
		    
                    $order = new WC_Order($order_id);                   
                       
                    if (!$order) {
                         return;
                    }
                    
                    $woodetails = get_option('jot-plugin-woo-manager');
                   
                    // Get message template.
                    $woodetails = get_option('jot-plugin-woo-manager');
                    $woo_message = isset($woodetails['jot-woo-admin-message']) ? $woodetails['jot-woo-admin-message'] : "";
                                     
                    // Get admin number for Lite
                    $jot_admin_num = isset($woodetails['jot-woo-adminnum']) ? $woodetails['jot-woo-adminnum'] : ""; 
                    
                    if ($woo_message != "" && $jot_admin_num != "") {                                                                       
                        
                         // Get customer details for this order
                         $customer = $this->get_woo_members($order_id);
                         
                         // Replace woocommerce merge tags                         
                         $detagged_message_final = $this->get_replace_tags($woo_message, $customer[0], $order, $new_status, $order_id);
                                                    
                         // Use default Sender ID for admin SMS if set
                         $senderid = "";               
                         $smsdetails = get_option('jot-plugin-smsprovider');
                         if (!empty($smsdetails['jot-smssenderid'])) {
                              $senderid = isset($smsdetails['jot-smssenderid']) ? $smsdetails['jot-smssenderid'] : "";
                         }    
                        
                         // Send message
                         $message_error = Joy_Of_Text_Plugin()->currentsmsprovider->send_smsmessage($jot_admin_num, $detagged_message_final,$senderid);
			 
			 $collate_args = array('jot_batchid' => uniqid(rand(), false),  
                                               'jot_messsubtype' => 'WA'
                                              );
                         $error = Joy_Of_Text_Plugin()->currentsmsprovider->collate_outbound_SMS("o","",$message_error,$collate_args);
			 
                         if ($message_error['send_message_errorcode'] != 0) {
                              $masked_number = substr_replace($jot_admin_num, str_repeat("X", 4), 4, 4);
                              Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>>> JOTWoo Admin Notification error : "
                                                                           . $message_error['send_message_msg']
                                                                           . " Order ID: " . $order_id
                                                                           . " Num: " . $masked_number
                                                                           );
                         }
                    }
                              
               }
	       
	       public function send_notifications_to_group($order_id, $new_status ) {
                    
		    if (Joy_Of_Text_Plugin()->debug) Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__, "In send_not_to_group. Orderid:" . $order_id );
		    
                    $order = new WC_Order($order_id);                   
                       
                    if (!$order) {
                         return;
                    }
                    
                    $woodetails = get_option('jot-plugin-woo-manager');
                   
                    // Get message template.
                    $woodetails = get_option('jot-plugin-woo-manager');
                    $woo_message = isset($woodetails['jot-woo-admin-message']) ? $woodetails['jot-woo-admin-message'] : 99999999;
                                     
                    // Get admin group for Pro
                    $jot_admin_numgrp = isset($woodetails['jot-woo-groupnotify']) ? $woodetails['jot-woo-groupnotify'] : "";
		    
		    
                    if ($woo_message != "" && $jot_admin_numgrp != 99999999) {                                                                       
                        
                         // Get customer details for this order
                         $customer = $this->get_woo_members($order_id);
                         
                         // Replace woocommerce merge tags
			 if ($customer) {
			   $detagged_message_final = $this->get_replace_tags($woo_message, $customer[0], $order, $new_status, $order_id);                                                    
			 } else {
			      Joy_Of_Text_Plugin()->messenger->log_to_file(__METHOD__,">>>> JOTWoo Group Notification error. Cannot find customer "
                                                                           . $message_error['send_message_msg']
                                                                           . " Order ID: " . $order_id                                                                           
                                                                           );
			      return;
			 }
                    
		         // Send message to group
                         $send_response = Joy_Of_Text_Plugin()->messenger->send_to_group($jot_admin_numgrp, $detagged_message_final);
                         
                    }
                              
               }
	       
	       
               public function get_replace_tags($message, $customer, $order, $new_status, $order_id) {

                    // Customer details
                    $message = str_replace('%woo_cust_firstname%',$customer->first_name, $message);
                    $message = str_replace('%woo_cust_lastname%',$customer->last_name, $message);
                    $message = str_replace('%woo_cust_number%',$customer->phone, $message);
                    
                    // Order details
                    $order_price = $order->get_total();
                    $order_number = $order->get_order_number();
                    $message = str_replace('%woo_order_price%',$order_price, $message);
                    $message = str_replace('%woo_order_number%',$order_number, $message);
                    $message = str_replace('%woo_order_status%',$new_status, $message);
               
		    // Order product items
		    $message = str_replace('%woo_order_products%',$this->get_order_items($order_id), $message);
               
	            // Woo custom fields		    	   
		    if (strpos($message, '%woometa_')) {
			// Get all merge tags for metakeys
			preg_match_all('/%woometa_(.*?)\%/s', $message, $matches);
			
			$i = 0;
			foreach ($matches[0] as $metamatch) {			   
			    $post_metaval = get_post_meta( $order_id, $matches[1][$i]);			   
			    $metaval = isset($post_metaval) ? $post_metaval : array();
			    $flat_array = $this->flatten($metaval);
			    $message = str_replace($metamatch, implode(" " , $flat_array), $message);
			    $i++;
			}						
		    }
		    
	       
                    return apply_filters('jot_get_replace_tags',$message);    
                    
               }
	       	       	       
	       public function get_order_items( $order_id ) {
		    global $woocommerce;
		    
		    if (!$order_id) {
			 return;
		    }
		    
		    $order = new WC_Order( $order_id );
	    
		    $product_list = '';
		    $product_name = array();
		    $order_item = $order->get_items();
	    
		    foreach( $order_item as $product ) {
			$product_name[] = '\n-- ' . $product['name'] . " x " . $product['qty'];
		    }
	    
		    $product_list = implode( '' , $product_name );
		    
		    return $product_list;
	       }

	       public function flatten($array) {
		    if (!is_array($array)) {
			 // nothing to do if it's not an array
			 return array($array);
		    }
		    
		    $result = array();
		    foreach ($array as $value) {
		        // explode the sub-array, and add the parts
		        $result = array_merge($result, $this->flatten($value));
		    }
		    
		    return $result;
	       }  

    
} // end class
 