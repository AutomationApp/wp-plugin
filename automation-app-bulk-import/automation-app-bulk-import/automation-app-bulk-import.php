<?php
/**
 * Plugin Name: Automation.app Bulk Order Import
 * Plugin URI: http://automation.app
 * Description: Use this plugin to get more of your WooCommerce orders and clients sent to Automation.app. This plugin allows you to send all orders to automation if you need all your historic data inside automation.app The plugin can also be used if you've had some downtime with the main sync plugin.
 * Version: 1.0.0
 * Author: automationApp
 * Author URI: http://automation.app
*/
error_reporting(E_ERROR | E_PARSE);
class wooAtExport{

    function __construct() {
		
		define('WAE_VESION','1.1.0');
		define('WAE_PATH',plugin_dir_path(__FILE__));
		define('WAE_URL',plugin_dir_url(__FILE__));
		define('WAE_QUEUE_TABLE','automation_export_queue');
		
		define('WAE_CRON_FRQ','15minute');
		define('WAE_QUEUE_LIMIT',50);
	
		$this->include_functions();
	
		register_activation_hook( __FILE__,array($this,'plugin_activation'));
		register_deactivation_hook( __FILE__,array($this,'plugin_deactivation'));
		
		add_action('admin_notices',array($this,'check_main_plugin_installed'));
    }
	
	public function include_functions() 
	{
		require_once(WAE_PATH.'include/automation-app-admin-options.php');
		require_once(WAE_PATH.'include/crons.php');
	}
	
	public function plugin_activation()
	{
		/*Create Tables*/
		global $wpdb;
		$createLogTable="CREATE TABLE IF NOT EXISTS `".$wpdb->prefix.WAE_QUEUE_TABLE."` (
				  `id` int(10) AUTO_INCREMENT,
				  `orderId` int(10) NOT NULL,
				  `exportedBy` int(10) NOT NULL,
				  `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`) , UNIQUE KEY `orderId` (`orderId`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;";
		$wpdb->query($createLogTable);
		
		/*Register Crons*/
		wooAtExportCrons::set_crons();
	}
	
	public function plugin_deactivation()
	{
		wooAtExportCrons::unset_crons();
		delete_option('WAE_settings');
	}
	
	public function check_main_plugin_installed()
	{
		if(!in_array('automation-app/automation-app.php',apply_filters('active_plugins', get_option('active_plugins'))))
		{
			?>
            <div class="notice notice-error">
         		<p><?php _e('Please install and configure <strong>Automation.app WooCommerce Extension</strong>.','woocommerce');?></p>
            </div>
            <?php 
		}
	}
}
new wooAtExport();