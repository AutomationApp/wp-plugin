var WAE_wrong_msg=WAE_jsOBJ.wrongMsg;
var WAE_time=3000;

function WAE_model($id,$text)
{
	var $html='<div id="WAE_model_'+$id+'" class="WAE_model"><div class="WAE_model_container"><div class="WAE_model_inner"><div class="WAE_model_close">X</div><div class="WAE_model_inner_holder">'+$text+'</div></div></div></div>';
	jQuery('body').prepend($html);
}

jQuery(document).on('click','.WAE_model_close,.WAE_close_model',function(event){
	var $this=jQuery(this);
	$this.parents('.WAE_model').remove();
});


function WAE_alert($text)
{
	WAE_model('alert','<div class="WAE_alert" align="center">'+$text+' <div class="mlm-spacer h_15"></div><button type="button" class="btn btn-primary WAE_close_model">OK</button></div>');	
}

function WAE_confirm(text,btn,callback){
	
	WAE_model('confirm','<div class="WAE_confirm" align="center">'+text+'<div class="mlm-spacer h_15"></div><button type="button" class="btn btn-success" id="WAE_confirm_click">'+btn+'</button> <button type="button" class="btn btn-danger WAE_close_model">Cancel</button></div>');
	
	jQuery('#WAE_confirm_click').click(function() {
		jQuery('.WAE_close_model').click();
        callback();
    });
}

function WAE_add_body_lock()
{
	jQuery('body').prepend('<div id="WAE_body_lock"></div>');
}

function WAE_remove_body_lock()
{
	jQuery('#WAE_body_lock').remove();
}

function WAE_show_form_loader($this)
{
	var $btn=$this.find('button[type="submit"]');
	$btn.attr('disabled');
	$this.find('#WAE_form_opt').remove();
	jQuery("<div id='WAE_form_loader'><div class='WAE_loader'></div> Please wait...</div>").insertAfter($btn);
}

function WAE_show_form_opt($form,$status,$msg,redirect,isRefresh)
{
	var $btn=$form.find('button[type="submit"]');
	jQuery('#WAE_form_loader').remove();
	
	if(typeof $msg=='undefined' && $status=='0')
 	$msg=WAE_wrong_msg;
 
	jQuery("<div id='WAE_form_opt' class='opt_"+$status+"'>"+$msg+"</div>").insertAfter($btn);
	
	redirect=redirect || '';
	
	var $t=(isRefresh)?0:WAE_time;
	if(redirect=='')
	{
		WAE_remove_body_lock();
		$btn.removeAttr('disabled');
		setTimeout(function(){ $form.find('#WAE_form_opt').remove(); },$t);
	}
	else
	setTimeout(function(){ if(redirect=='reload'){location.reload();}else{window.location.href = redirect;} },$t);
}

jQuery(document).on('submit','#WAE_settings_form',function(event){
	event.preventDefault();
	
	var $this=jQuery(this);
	
	WAE_add_body_lock();
	WAE_show_form_loader($this);
		
	jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url:WAE_jsOBJ.ajaxUrl,
			data:{
				'action':'WAE_settings',
				'formData':$this.serialize()
			},
			error: function (jqXHR, exception) {
				WAE_show_form_opt($this,0,WAE_wrong_msg);
			},success: function(data)
			{
				WAE_show_form_opt($this,data.status,data.msg,data.redirect,data.refresh);
			}
	});	
});

jQuery(document).on('submit','#WAE_search_order_form',function(event){
	event.preventDefault();
	
	var $this=jQuery(this);
	var $count=0;
	
	WAE_add_body_lock();
	WAE_show_form_loader($this);
	jQuery('#WAE_export_order_list li:not(.selectAllOption)').remove();
		
	jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url:WAE_jsOBJ.ajaxUrl,
			data:{
				'action':'WAE_search_orders',
				'formData':$this.serialize()
			},
			error: function (jqXHR, exception) {
				WAE_show_form_opt($this,0,WAE_wrong_msg);
			},success: function(data)
			{
				if(data.status=='1')
				{
					var orders=data.orders;
					if(orders.length>0)
					{
						$count=orders.length;
						var $html='';
						Object.keys(orders).forEach(function(key) {
							$html+='<li class="list-group-item"><label class="cb-container"><input name="o[]" type="checkbox" value="'+orders[key].ID+'"> <span class="checkmark"></span> Order #'+orders[key].ID+'</label> <span class="badge">'+orders[key].post_date+'</span></li>';
						});
						jQuery('#WAE_export_order_list').append($html);
						WAE_show_form_opt($this,1,'');
						WAE_switch_step(2);
					}
					else
					WAE_show_form_opt($this,0,'No any order found to export.');
				}
				else
				WAE_show_form_opt($this,data.status,WAE_wrong_msg);	
				
				jQuery('#WAE_search_count').html($count);
			}
	});	
});

jQuery(document).on('submit','#WAE_export_order_form',function(event){
	event.preventDefault();
	
	var $this=jQuery(this);
	
	WAE_add_body_lock();
	WAE_show_form_loader($this);
		
	jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url:WAE_jsOBJ.ajaxUrl,
			data:{
				'action':'WAE_export_order',
				'formData':$this.serialize()
			},
			error: function (jqXHR, exception) {
				WAE_show_form_opt($this,0,WAE_wrong_msg);
			},success: function(data)
			{
				WAE_show_form_opt($this,data.status,data.msg,data.redirect,data.refresh);
			}
	});	
});

function WAE_switch_step(step)
{
	jQuery('.form_steps li,.form_steps_data').removeClass('active');
	var $s=(step==1)?':first':':last';
	var $sel=jQuery('.form_steps li'+$s);
	$sel.addClass('active');
	jQuery('#'+$sel.data('id')).addClass('active');
}

jQuery(document).on('change','li.selectAllOption input[type="checkbox"]',function(event){
	var $ac=(jQuery(this).is(":checked"))?true:false;
	jQuery('#WAE_export_order_list li:not(.selectAllOption) input[type="checkbox"]').prop('checked',$ac);
});