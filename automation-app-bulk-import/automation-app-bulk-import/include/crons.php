<?php
class wooAtExportCrons{
    function __construct() {
		add_filter( 'cron_schedules',array($this,'cron_add_schedules')); 
		add_action('WAE_export_queue',array($this,'WAE_export_queue_cron'));
		
		add_filter('WAE_export_cron_frequancy',array($this,'WAE_export_cron_frequancy_handle'),10,1); 
		add_filter('WAE_export_queue_limit',array($this,'WAE_export_queue_limit_handle'),10,1); 
	}
		
	public function cron_add_schedules($schedules)
	{
		$schedules['15minute'] = array(
		'interval' => 900,
		'display' => __('Every 15 Mins','woocommerce')
		);
		
		$schedules['30minute'] = array(
		'interval' => 1800,
		'display' => __('Every 30 Mins','woocommerce')
		);
	
		return $schedules;	
	}
	
	public static function set_crons()
	{
		self::unset_crons();
		$cronTime=apply_filters('WAE_export_cron_frequancy',WAE_CRON_FRQ);
		
		if(!wp_next_scheduled('WAE_export_queue'))
		wp_schedule_event(strtotime(date("Y-m-d 00:00:00",time())),$cronTime,'WAE_export_queue');
	}
	
	public static function unset_crons()
	{
		wp_clear_scheduled_hook('WAE_export_queue');
	}
	
	public function WAE_export_cron_frequancy_handle($arg)
	{
        $settings=json_decode(get_option('WAE_settings',''),true);
		return (!empty($settings['cron_frequency']))?$settings['cron_frequency']:$arg;
	}
	
	public function WAE_export_queue_limit_handle($args)
	{
		$settings=json_decode(get_option('WAE_settings',[]),true);
		return (!empty($settings['export_limit']))?$settings['export_limit']:$args;
	}
	
	public function WAE_export_queue_cron()
	{
		$webhookId = get_option('automation_app_order_created_wh','');
		if(!$webhookId) return;
		
		$limit=apply_filters('WAE_export_queue_limit',WAE_QUEUE_LIMIT);
		
		global $wpdb;
		$queue=$wpdb->get_results('SELECT id,orderId FROM '.$wpdb->prefix.WAE_QUEUE_TABLE.' ORDER BY id ASC LIMIT '.$limit,ARRAY_A);
		if(!empty($queue))
		{
			try
			{
				$webhook = wc_get_webhook($webhookId);
				if($webhook)
				{
					$called=[];
					foreach($queue as $s)
					{
						$webhook->deliver($s['orderId']);
						$called[]=$s['id'];
					}
						
					$calledIds = implode(',',array_map('absint',$called));
					$wpdb->query('DELETE FROM '.$wpdb->prefix.WAE_QUEUE_TABLE.' WHERE id IN('.$calledIds.')');
				}
			}
			catch(Exception $exception){
				error_log($exception->getMessage());
			}
		}
	}
}
new wooAtExportCrons();
?>