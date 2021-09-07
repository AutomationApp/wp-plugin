<?php
$settings=json_decode(get_option('WAE_settings',''),true);
$frequency=(!empty($settings['cron_frequency']))?$settings['cron_frequency']:WAE_CRON_FRQ;
$limit=(!empty($settings['export_limit']))?$settings['export_limit']:WAE_QUEUE_LIMIT;
$frequencyOpt=['15minute'=>__('Every 15 Minutes','woocommerce'),'30minute'=>__('Every 30 Minutes','woocommerce'),'hourly'=>__('Every Hour','woocommerce'),'twicedaily'=>__('Twice Daily','woocommerce'),'daily'=>__('Daily','woocommerce')];

$quedOrders=wooAtExportAdmin::count_queue_orders();
?>
<div class="container-fluid WAE_admin_container">
  <h2 class="WAE_title"><?php _e('Automation.app Import','woocommerce');?></h2>
  <?php if($quedOrders>0){?>
  <div class="notice notice-success" style=" margin-left:0px; margin-bottom:15px">
	<?php echo sprintf(__('<p>There are <strong>(%s)</strong> orders are in queue to export.</p>','woocommerce'),$quedOrders );?>
  </div>
  <?php }?>
  <div class="row">
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><?php _e('Import Settings','woocommerce');?></h3>
        </div>
        <div class="panel-body">
          <form id="WAE_settings_form" class="WAE_form">
            <div class="form-group">
              <label for="cron_frequency"><?php _e('Frequency','woocommerce');?></label>
              <select class="form-control" name="cron_frequency" required>
                <?php
					foreach($frequencyOpt as $k=>$v)
					{
						$selected=($k==$frequency)?'selected="selected"':'';
						echo '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
					}
				?>
              </select>
            </div>
            <div class="form-group">
              <label for="export_limit"><?php _e('Export Limit (Per Cron Job)','woocommerce');?></label>
              <input type="number" class="form-control" name="export_limit" value="<?php echo $limit;?>" required>
            </div>
            <button type="submit" class="btn btn-primary"><?php _e('Save Settings','woocommerce');?></button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><?php _e('Import Orders','woocommerce');?></h3>
        </div>
        <div class="panel-body">
          <ul class="form_steps">
            <li data-id="step_search_orders" class="active"><span>1. </span><?php _e('Continue to import','woocommerce');?></li>
            <li data-id="step_export_orders"><span>2. </span><?php _e('Import Orders','woocommerce');?></li>
          </ul>
          <div class="WAE-spacer h_20"></div>
          <div class="form_steps_data active" id="step_search_orders">
            <form id="WAE_search_order_form" class="WAE_form">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="from_date"><?php _e('From Date','woocommerce');?></label>
                    <input type="date" class="form-control" name="from_date" placeholder="<?php _e('mm/dd/yyyy','woocommerce');?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="from_date"><?php _e('To Date','woocommerce');?></label>
                    <input type="date" class="form-control" name="to_date" placeholder="<?php _e('mm/dd/yyyy','woocommerce');?>">
                  </div>
                </div>
              </div>
              <p class="allOrderWarning"><?php _e('Keep dates empty for search from all orders.','woocommerce');?></p>
              <button type="submit" class="btn btn-primary"><?php _e('Search Orders','woocommerce');?></button>
            </form>
          </div>
          <div class="form_steps_data" id="step_export_orders">
            <form id="WAE_export_order_form" class="WAE_form">
              <button type="button" class="btn btn-danger" onclick="WAE_switch_step(1)"><?php _e('Back To Search','woocommerce');?></button>
              <button type="submit" class="btn btn-primary"><?php _e('Submit','woocommerce');?></button>
              <div class="WAE-spacer h_15"></div>
              <ul class="list-group" id="WAE_export_order_list">
                <li class="list-group-item selectAllOption">
                  <label class="cb-container">
                    <input type="checkbox" value="yes">
                    <span class="checkmark"></span> <?php echo sprintf(__('Select All Orders (%s)','woocommerce'),'<span id="WAE_search_count">0</span> Orders');?></label>
                </li>
              </ul>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>