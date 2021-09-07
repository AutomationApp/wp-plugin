<div class="automation_addon_container green">
	<div class="container_inner">
    	<div class="ttl">Import Orders to Automation.app from woo</div>
        <div class="descrption">Use this plugin to get more of your WooCommerce orders and clients sent to Automation.app. This plugin allows you to send all orders to automation if you need all your historic data inside automation.app The plugin can also be used if you've had some downtime with the main sync plugin.</div>
        <div class="action_btns">
        	<?php if(is_plugin_active('automation-app-bulk-import/automation-app-bulk-import.php')){?>
             <div class="alreadyInstalled">Active</div>
            <?php }else{?>
        		<a href="<?php echo admin_url('plugin-install.php?s=Automation.app Bulk Order Import&tab=search&type=term');?>"><button>Install Import Plugin</button></a> <span class="rMore"><a target="_blank" href="https://automation.app/blog/order-import-plugin-bulk-import-woocommerce-orders-to-automationapp">Read More</a></span>
            <?php }?>
        </div>
    </div>
</div>