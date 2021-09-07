<div class="app_setting_wrapper">
  <h2>Automation App settings</h2>
  <div class="card app_setting_container">
    <div class="card-body">
      <form method="post" action="options.php">
        <?php settings_fields( 'automation_app_options_group' ); ?>
        <h3>Api settings</h3>
        <p>Add your api key and secret should they change.</p>
        <div class="form-group">
          <label class="form-label" for="Api key">Api key</label>
          <input class="form-control" id="automation_app_api_key" name="automation_app_api_key" type="text" placeholder="Api key" value="<?php echo get_option('automation_app_api_key'); ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="Secret key">Secret key</label>
          <input class="form-control" id="automation_app_secret_key" name="automation_app_secret_key" type="text" placeholder="Api key" value="<?php echo get_option('automation_app_secret_key'); ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="Api domain">Api domain</label>
          <input class="form-control" id="automation_app_api_domain" name="automation_app_api_domain" type="text" placeholder="Api key" value="<?php echo get_option('automation_app_api_domain'); ?>">
        </div>
        <?php  submit_button(); ?>
      </form>
    </div>
  </div>
</div>