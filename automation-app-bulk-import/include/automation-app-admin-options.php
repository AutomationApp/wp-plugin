<?php
class wooAtExportAdmin{
	
	function __construct()
	{
		 add_action('admin_enqueue_scripts',array($this,'register_assets'),10);
		 add_filter('automation_import_page_callback',function(){ return [$this,'WAE_export_page']; },100);
		 
		 add_action("wp_ajax_WAE_settings",array($this,'ajax_WAE_settings_handle'));
		 add_action("wp_ajax_nopriv_WAE_settings",array($this,'ajax_WAE_settings_handle'));
		 
		 add_action("wp_ajax_WAE_search_orders",array($this,'ajax_WAE_search_orders_handle'));
		 add_action("wp_ajax_nopriv_WAE_search_orders",array($this,'ajax_WAE_search_orders_handle'));
		 
		 add_action("wp_ajax_WAE_export_order",array($this,'ajax_WAE_export_order_handle'));
		 add_action("wp_ajax_nopriv_WAE_export_order",array($this,'ajax_WAE_export_order_handle'));
		 
		 add_action('woocommerce_webhook_delivery',array($this,'after_webhook_called'),99,5);
	}
	
	public function register_assets()
	{
		$check=(!empty($_GET['page']))?$_GET['page']:'';
		if(strpos($check,'automation-export')!==false)
		{
			$vesrionQ='?ver='.WAE_VESION;
			
			wp_enqueue_style('bootstrap.min.css',WAE_URL.'assets/css/bootstrap.min.css');
			wp_enqueue_style('WAE.css',WAE_URL.'assets/css/style.css'.$vesrionQ,array(),null);
			
			wp_enqueue_script('WAE.js',WAE_URL.'assets/js/script.js'.$vesrionQ,array(),null,true);
			wp_localize_script('WAE.js','WAE_jsOBJ', array('ajaxUrl' =>admin_url('admin-ajax.php'),'wrongMsg'=>__('Something went wrong.','woocommerce')));
		}
	}
	
	public function WAE_export_page()
	{
		require_once('admin-templates/export_page.php');
	}
	
	public function ajax_WAE_settings_handle()
	{
		parse_str($_POST['formData'],$postData);
		$return=['status'=>'0'];
		if(current_user_can('administrator') && !empty($postData))
		{
			update_option('WAE_settings',json_encode($postData));
			wooAtExportCrons::set_crons();
			$return=['status'=>'1','msg'=>__('Settings Saved.','woocommerce')];
		}
		
		wp_send_json($return);
	}
	
	public function ajax_WAE_search_orders_handle()
	{
		parse_str($_POST['formData'],$postData);
		$return=['status'=>'0'];
		if(current_user_can('administrator'))
		{
			global $wpdb;
			
			/*For Already In Queue*/
			$alreadyInQueue=$wpdb->get_results('SELECT DISTINCT orderId FROM '.$wpdb->prefix.WAE_QUEUE_TABLE,ARRAY_A);
			$alreadyInQueue=(!empty($alreadyInQueue))?array_column($alreadyInQueue,'orderId'):[];
			
			/*For Already Exported*/
			$alreadyExported=$wpdb->get_results('SELECT DISTINCT post_id FROM '.$wpdb->postmeta.' WHERE meta_key="WAE_order_exported" AND meta_value="1"',ARRAY_A);
			$alreadyExported=(!empty($alreadyExported))?array_column($alreadyExported,'post_id'):[];
			
			$ingnorOrders=array_unique(array_merge($alreadyInQueue,$alreadyExported));
			
			$where=['post_type="shop_order"'];
			
			if(!empty($postData['from_date']))
			$where[]='post_date>="'.$postData['from_date'].' 00:00:00"';
			
			if(!empty($postData['to_date']))
			$where[]='post_date<="'.$postData['to_date'].' 23:59:59"';
			
			if(!empty($ingnorOrders))
			$where[]='ID NOT IN ("'.implode('","',$ingnorOrders).'")';
			
			$whereQ='WHERE '.(implode(' AND ',$where));
			
			$orders=$wpdb->get_results('SELECT ID,post_date FROM '.$wpdb->posts.' '.$whereQ.' ORDER BY ID DESC',ARRAY_A);
			$return=['status'=>'1','orders'=>$orders];
		}
		wp_send_json($return);
	}	
	
	public function ajax_WAE_export_order_handle()
	{
		parse_str($_POST['formData'],$postData);
		$return=['status'=>'0'];
		if(current_user_can('administrator') && !empty($postData['o']))
		{
			global $wpdb;
			$userID=get_current_user_id();
			foreach($postData['o'] as $order)
			{
				$wpdb->insert($wpdb->prefix.WAE_QUEUE_TABLE,['orderId'=>$order,'exportedBy'=>$userID]);
			}
			
			$return=['status'=>'1','msg'=>__('Orders successfully added in queue to export.','woocommerce'),'redirect'=>'reload'];
		}
		wp_send_json($return);
	}
	
	public static function count_queue_orders()
	{
		global $wpdb;
		$queue=$wpdb->get_row('SELECT count(id) as total FROM '.$wpdb->prefix.WAE_QUEUE_TABLE,ARRAY_A);
		return $queue['total'];
	}
	
	public function after_webhook_called($http_args,$response,$duration,$arg,$webhook_id)
        {
            $orderData = json_decode($http_args['body']);
			if(!empty($orderData->id))
			{
				$webhookBaseId = get_option('automation_app_order_created_wh','');
			 	
				if(!empty($webhookBaseId) && $webhookBaseId==$webhook_id)
				update_post_meta($orderData->id,'WAE_order_exported','1');
            }
        }
}

new wooAtExportAdmin();
?>
