<?php global $woo_sms; ?>

<div class="wrap woocommerce">
  <h2>
   افزونه فارسی پیامک ووکامرس
  </h2>
  <?php 
		settings_errors(); 
		$tab = 1;
		$configuracion = get_option('persianscript_sms_woocommerce');
  ?>
  <h3><a href="<?php echo $woo_sms['plugin_url']; ?>" title="Woocommerce SMS Plugin"><?php echo $woo_sms['plugin']; ?></a></h3>
  <p>
	این افزونه به صورت رایگان عرضه شده است. در صورتی که قصد حمایت از ما را دارید می توانید <a href="http://shop.persianscript.ir/products/woocommerce-sms/" target="_blank"> اینجا کلیک کنید</a>.
  </p>
  
  <form method="post" action="options.php">
    <?php settings_fields('persianscript_sms_woocommerce_group'); ?>
    <div class="cabecera"> <a href="http://woocommerce.ir/plugins.html" title="افزونه ارسال پیامک ووکامرس" target="_blank">
	<span class="imagen"></span></a> </div>
    <table class="form-table">
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[smswebservice]">
            سرویس دهنده پیامک:
          </label>
          </th>
        <td class="forminp forminp-number"><select id="persianscript_sms_woocommerce[smswebservice]" name="persianscript_sms_woocommerce[smswebservice]" tabindex="<?php echo $tab++; ?>">
            <?php
            $proveedores = array("panizsms" => "PANIZSMS.COM","hi-sms" => "Hi-SMS.ir","mtbsms" => "MTBSMS.ir","parandsms" => "ParandSMS.com","persiapanel" => "PersiaPanel.ir","farapayamak" => "FaraPayamak.ir","sabapayamak" => "SabaPayamak.info");
            foreach ($proveedores as $valor => $proveedor) 
            {
				$chequea = '';
				if (isset($configuracion['smswebservice']) && $configuracion['smswebservice'] == $valor) $chequea = ' selected="selected"';
				echo '<option value="' . $valor . '"' . $chequea . '>' . $proveedor . '</option>' . PHP_EOL;
            }
            ?>
          </select></td>
      </tr>
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[smspanelsender]">
            شماره ارسال کننده:
          </label>
          </th>
        <td class="forminp forminp-number"><input type="text" id="persianscript_sms_woocommerce[smspanelsender]" name="persianscript_sms_woocommerce[smspanelsender]" size="50" value="<?php echo (isset($configuracion['smspanelsender']) ? $configuracion['smspanelsender'] : ''); ?>" tabindex="<?php echo $tab++; ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[smspanelusername]">
            نام کاربری:
          </label>
          </th>
        <td class="forminp forminp-number"><input type="text" id="persianscript_sms_woocommerce[smspanelusername]" name="persianscript_sms_woocommerce[smspanelusername]" size="50" value="<?php echo (isset($configuracion['smspanelusername']) ? $configuracion['smspanelusername'] : ''); ?>" tabindex="<?php echo $tab++; ?>" /></td>
      </tr>
	  
	  <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[smspanelpassword]">
            رمز عبور:
          </label>
          </th>
        <td class="forminp forminp-number"><input type="password" id="persianscript_sms_woocommerce[smspanelpassword]" name="persianscript_sms_woocommerce[smspanelpassword]" size="50" value="<?php echo (isset($configuracion['smspanelpassword']) ? $configuracion['smspanelpassword'] : ''); ?>" tabindex="<?php echo $tab++; ?>" /></td>
      </tr>
	  
	  
      
      
      
     
      
     
      
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[userphone]">
            شماره موبایل مدیر:
          </label>
</th>
        <td class="forminp forminp-number"><input type="number" id="persianscript_sms_woocommerce[userphone]" name="persianscript_sms_woocommerce[userphone]" size="50" value="<?php echo (isset($configuracion['userphone']) ? $configuracion['userphone'] : ''); ?>" tabindex="<?php echo $tab++; ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[notificacion]">
            اطلاع رسانی سفارشات جدید:
          </label>
</th>
        <td class="forminp forminp-number"><input id="persianscript_sms_woocommerce[notificacion]" name="persianscript_sms_woocommerce[notificacion]" type="checkbox" value="1" <?php echo (isset($configuracion['notificacion']) && $configuracion['notificacion'] == "1" ? 'checked="checked"' : ''); ?> tabindex="<?php echo $tab++; ?>" /></td>
      </tr>
      
     
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[mensaje_pedido]">
           متن پیامک به مسئول سایت:
          </label>
          </th>
        <td class="forminp forminp-number"><textarea id="persianscript_sms_woocommerce[mensaje_pedido]" name="persianscript_sms_woocommerce[mensaje_pedido]" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo stripcslashes(isset($configuracion['mensaje_pedido']) ? $configuracion['mensaje_pedido'] : sprintf(__("یک سفارش جدید به شماره %s در ", 'woo_sms'), "%id%") . "%shop_name%" . " افزوده شد."); ?></textarea></td>
      </tr>
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[mensaje_recibido]">
            متن پیامک به کاربر در هنگام ارسال سفارش جدید:
          </label>
		</th>
        <td class="forminp forminp-number"><textarea id="persianscript_sms_woocommerce[mensaje_recibido]" name="persianscript_sms_woocommerce[mensaje_recibido]" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo stripcslashes(isset($configuracion['mensaje_recibido']) ? $configuracion['mensaje_recibido'] : sprintf(__('سفارش شما با شناسه %s با موفقیت در %s ثبت شد. از خرید شما سپاسگذاریم', 'woo_sms'), "%id%", "%shop_name%")); ?></textarea></td>
      </tr>
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[mensaje_procesando]">
           متن پیامک در هنگام تغییر وضعیت سفارش:
          </label>
				</th>
				<td class="forminp forminp-number"><textarea id="persianscript_sms_woocommerce[mensaje_procesando]" name="persianscript_sms_woocommerce[mensaje_procesando]" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo stripcslashes(isset($configuracion['mensaje_procesando']) ? $configuracion['mensaje_procesando'] : sprintf(__('از سفارش شما سپاسگذاریم!. سفارش شما به شماره %s هم اکنون در وضعیت پردازش قرار دارد ', 'woo_sms'), "%id%")); ?></textarea></td>
      </tr>
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[mensaje_completado]">
            متن پیامک در هنگام تکمیل سفارش:
          </label>
		</th>      
		<td class="forminp forminp-number"><textarea id="persianscript_sms_woocommerce[mensaje_completado]" name="persianscript_sms_woocommerce[mensaje_completado]" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo stripcslashes(isset($configuracion['mensaje_completado']) ? $configuracion['mensaje_completado'] : sprintf(__('از سفارش شما سپاسگذریم. سفارش شما به شماره %s هم اکنون در وضعیت تکمیل شده قرار دارد ', 'woo_sms'), "%id%")); ?></textarea></td>
      </tr>
      <tr valign="top">
        <th scope="row" class="titledesc"> <label for="persianscript_sms_woocommerce[mensaje_nota]">
            متن پیامک در هنگام نوشتن پیغام دلخواه در سفارش:
          </label>
</th>
        <td class="forminp forminp-number"><textarea id="persianscript_sms_woocommerce[mensaje_nota]" name="persianscript_sms_woocommerce[mensaje_nota]" cols="50" rows="5" tabindex="<?php echo $tab++; ?>"><?php echo stripcslashes(isset($configuracion['mensaje_nota']) ? $configuracion['mensaje_nota'] : sprintf(__('یک پیغام جدید در سفارش شماره %s ارسال شده است. متن پیغام: ', 'woo_sms'), "%id%") . "%note%"); ?></textarea></td>
      </tr>
    </table>
    <p class="submit">
      <input class="button-primary" type="submit" value="ذخیره تغییرات"  name="submit" id="submit" tabindex="<?php echo $tab++; ?>" />
    </p>
  </form>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {	
	$('select').on('change', function () {
		control($(this).val());
	});

	var control = function(capa) {
		var proveedores= new Array();
		<?php foreach($proveedores as $indice => $valor) echo "proveedores['$indice'] = '$valor';" . PHP_EOL; ?>
		
		for (var valor in proveedores) {
    		if (valor == capa) $('.' + capa).show();
			else $('.' + valor).hide();
		}
	};
	control($('select').val());
});
</script> 


