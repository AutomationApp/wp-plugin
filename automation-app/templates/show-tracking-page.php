<div class="automation_addon_container yellow">
	<div class="container_inner">
    	<div class="ttl">Install plugin for referrer tracking</div>
        <div class="descrption">Automation.app Referrer Tracker adds the referrer (website, Google, Facebook etc.) to the order meta data without using cookies. So that you can evaluate marketing efforts and create segmentation for groups of users with no added data privacy complexity.</div>
        <div class="action_btns">
        	<?php if(is_plugin_active('woo-doc-referrer/woo-doc-referrer.php')){?>
             <div class="alreadyInstalled">Active</div>
            <?php }else{?>
        		<a href="<?php echo admin_url('plugin-install.php?s=Automation.app Referrer Tracking&tab=search&type=term');?>"><button>Install Tracking</button></a> <span class="rMore"><a target="_blank" href="https://automation.app/blog/automationapp-plugin-for-referrer-tracking">Read More</a></span>
            <?php }?>
        </div>
    </div>
</div>