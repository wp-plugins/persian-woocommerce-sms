<?php
/*
Plugin Name: Persian Woocommerce SMS
Version: 1.5
Plugin URI: http://www.woocommerce.ir/plugins.html
Description: این افزونه شما را قادر می سازد تا براحتی قابلیت ارسال پیامک را در سیستم ووکامرس پارسی فراهم کنید. تمامی حقوق این افزونه متعلق به تیم ووکامرس پارسی می باشد و هر گونه کپی برداری ،  فروش آن غیر مجاز می باشد.
Author URI: http://www.woocommerce.ir/
Author: ووکامرس فارسی

*/


$woo_sms = array(	'plugin' => 'افزونه پیامک ووکامرس فارسی', 
					'plugin_uri' => 'persian-woocommerce-sms', 
					'plugin_url' => 'http://woocommerce.ir/plugins.html', 
					'ajustes' => 'admin.php?page=woo_sms', 
					'puntuacion' => 'http://woocommerce.ir/plugins.html');



function woo_sms_farsi($enlaces, $archivo) {
	global $woo_sms;
	
	$plugin = plugin_basename(__FILE__);
	return $enlaces;
}
add_filter('plugin_row_meta', 'woo_sms_farsi', 10, 2);


function woo_sms_enlace_de_ajustes($enlaces) { 
	global $woo_sms;
	
	$enlace_de_ajustes = '<a href="' . $woo_sms['ajustes'] . '" title="تنظیمات افزونه">تنظیمات</a>'; 
	array_unshift($enlaces, $enlace_de_ajustes); 
	
	return $enlaces; 
}
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'woo_sms_enlace_de_ajustes');

function woo_sms_tab() {
	wp_enqueue_style('woo_sms_style'); 
	include('woocommerce-ir.php');
}


function woo_sms_admin_menu() {
	add_submenu_page('woocommerce', __('افزونه پیامک ووکامرس', 'woo_sms'),  __('پیامک ووکامرس', 'woo_sms') , 'manage_woocommerce', 'woo_sms', 'woo_sms_tab');
}
add_action('admin_menu', 'woo_sms_admin_menu', 15);


function woo_sms_screen_id($woocommerce_screen_ids) {
	global $woocommerce;

	$woocommerce_screen_ids[] = 'woocommerce_page_woo_sms';

	return $woocommerce_screen_ids;
}
add_filter('woocommerce_screen_ids', 'woo_sms_screen_id');


function woo_sms_registra_opciones() {
	register_setting('persianscript_sms_woocommerce_group', 'persianscript_sms_woocommerce');
}
add_action('admin_init', 'woo_sms_registra_opciones');


function woo_sms_procesa_estados($pedido) {
	global $woocommerce;

	$pedido = new WC_Order($pedido);
	$estado = $pedido->status;
	$nombres_de_estado = array('on-hold' => 'Recibido', 'processing' => __('Processing', 'woo_sms'), 'completed' => __('Completed', 'woo_sms'));
	foreach ($nombres_de_estado as $nombre_de_estado => $traduccion) if ($estado == $nombre_de_estado) $estado = $traduccion;

	$configuracion = get_option('persianscript_sms_woocommerce'); 
	
	$internacional = false;
	$userphone = woo_sms_procesa_el_userphone($pedido, $pedido->billing_phone, $configuracion['smswebservice']);
	if ($pedido->billing_country && ($woocommerce->countries->get_base_country() != $pedido->billing_country)) $internacional = true;
	
	if ($estado == 'Recibido')
	{
		if (isset($configuracion['notificacion']) && $configuracion['notificacion'] == 1) woo_sms_envia_sms($configuracion, $configuracion['userphone'], woo_sms_procesa_variables($configuracion['mensaje_pedido'], $pedido, $configuracion['variables'])); //Mensaje para el propietario
		$mensaje = woo_sms_procesa_variables($configuracion['mensaje_recibido'], $pedido, $configuracion['variables']);
	}
	else if ($estado == __('Processing', 'woo_sms')) $mensaje = woo_sms_procesa_variables($configuracion['mensaje_procesando'], $pedido, $configuracion['variables']);
	else if ($estado == __('Completed', 'woo_sms')) $mensaje = woo_sms_procesa_variables($configuracion['mensaje_completado'], $pedido, $configuracion['variables']);

	if (!$internacional || (isset($configuracion['internacional']) && $configuracion['internacional'] == 1)) woo_sms_envia_sms($configuracion, $userphone, $mensaje);
}
		add_action('woocommerce_order_status_completed', 'woo_sms_procesa_estados', 10);
		add_action('woocommerce_order_status_processing', 'woo_sms_procesa_estados', 10);


		add_action('woocommerce_order_status_pending_to_processing_notification', 'woo_sms_procesa_estados', 10);
		add_action('woocommerce_order_status_pending_to_on-hold_notification', 'woo_sms_procesa_estados', 10);
		add_action('woocommerce_order_status_pending_to_completed_notification', 'woo_sms_procesa_estados', 10);

function woo_sms_procesa_notas($datos) {
	global $woocommerce;
	
	$pedido = new WC_Order($datos['order_id']);
	
	$configuracion = get_option('persianscript_sms_woocommerce');
	
	$internacional = false;
	$userphone = woo_sms_procesa_el_userphone($pedido, $pedido->billing_phone, $configuracion['smswebservice']);
	if ($pedido->billing_country && ($woocommerce->countries->get_base_country() != $pedido->billing_country)) $internacional = true;
	
	if (!$internacional || (isset($configuracion['internacional']) && $configuracion['internacional'] == 1)) woo_sms_envia_sms($configuracion, $userphone, woo_sms_procesa_variables($configuracion['mensaje_nota'], $pedido, $configuracion['variables'], wptexturize($datos['customer_note'])));
}
add_action('woocommerce_new_customer_note', 'woo_sms_procesa_notas', 10);


function woo_sms_envia_sms($configuracion, $userphone, $mensaje) {
	
	if ($configuracion['smswebservice'] == "panizsms") 
	{
	$url = "http://87.107.121.54/post/sendSMS.ashx?from=" . $configuracion['smspanelsender'] . "&to=" . $userphone . "&text=" . woocommerce_ir_sms_check(woocommerce_ir_sms_normal($mensaje)) . "&username=" . $configuracion['smspanelusername'] . "&password=" . $configuracion['smspanelpassword'];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data = curl_exec($curl);
	curl_close($curl);
	}

	elseif ($configuracion['smswebservice'] == "sabapayamak") 
	{
	$url = "http://sabapayamak.com/post/sendSMS.ashx?from=" . $configuracion['smspanelsender'] . "&to=" . $userphone . "&text=" . woocommerce_ir_sms_check(woocommerce_ir_sms_normal($mensaje)) . "&username=" . $configuracion['smspanelusername'] . "&password=" . $configuracion['smspanelpassword'];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data = curl_exec($curl);
	curl_close($curl);
	}
	
	
	
	elseif ($configuracion['smswebservice'] == "mtbsms") 
	{
	$url = "http://mtbsms.ir/httpService/SendSMS.aspx?from=" . $configuracion['smspanelsender'] . "&to=" . $userphone . "&text=" . woocommerce_ir_sms_check(woocommerce_ir_sms_normal($mensaje)) . "&user=" . $configuracion['smspanelusername'] . "&pass=" . $configuracion['smspanelpassword'];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data = curl_exec($curl);
	curl_close($curl);
	}
	
	elseif ($configuracion['smswebservice'] == "parandsms") 
	{
	$url = "http://parandsms.ir/post/sendSMS.ashx?from=" . $configuracion['smspanelsender'] . "&to=" . $userphone . "&text=" . woocommerce_ir_sms_check(woocommerce_ir_sms_normal($mensaje)) . "&username=" . $configuracion['smspanelusername'] . "&password=" . $configuracion['smspanelpassword'];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data = curl_exec($curl);
	curl_close($curl);
	}
	
	elseif ($configuracion['smswebservice'] == "persiapanel") 
	{
	$url = "http://persiapanel.ir/API/sendSMS.ashx?from=" . $configuracion['smspanelsender'] . "&to=" . $userphone . "&text=" . woocommerce_ir_sms_check(woocommerce_ir_sms_normal($mensaje)) . "&username=" . $configuracion['smspanelusername'] . "&password=" . $configuracion['smspanelpassword'];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data = curl_exec($curl);
	curl_close($curl);
	}
	
	elseif ($configuracion['smswebservice'] == "sabzpayamak") 
	{
	$url = "http://sabzpayamak.ir/API/sendSMS.ashx?from=" . $configuracion['smspanelsender'] . "&to=" . $userphone . "&text=" . woocommerce_ir_sms_check(woocommerce_ir_sms_normal($mensaje)) . "&username=" . $configuracion['smspanelusername'] . "&password=" . $configuracion['smspanelpassword'];
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$data = curl_exec($curl);
	curl_close($curl);
	}
	
	
	
	
	
}





function woocommerce_ir_sms_normal($mensaje)
{
	$reemplazo = array('Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e',  'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y',  'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', "`" => "'", "´" => "'", "„" => ",", "`" => "'", "´" => "'", "“" => "\"", "”" => "\"", "´" => "'", "&acirc;€™" => "'", "{" => "", "~" => "", "–" => "-", "’" => "'", "!" => ".", "¡" => "", "?" => ".", "¿" => "");
 
	$mensaje = str_replace(array_keys($reemplazo), array_values($reemplazo), htmlentities($mensaje, ENT_QUOTES, "UTF-8"));
 
	return $mensaje;
}

function woocommerce_ir_sms_check($mensaje) {
	return urlencode(htmlentities($mensaje, ENT_QUOTES, "UTF-8"));
}

function woo_sms_prefijo($smswebservice) {
	if ($smswebservice == "clockwork" || $smswebservice == "clickatell" || $smswebservice == "bulksms" || $smswebservice = "msg91") return true;
	
	return false;
}

function woo_sms_procesa_el_userphone($pedido, $userphone, $smswebservice) {
	global $woocommerce;
	
	$prefijo = woo_sms_prefijo($smswebservice);
	
	$userphone = str_replace(array('+','-'), '', filter_var($userphone, FILTER_SANITIZE_NUMBER_INT));
	if ($pedido->billing_country && ($woocommerce->countries->get_base_country() != $pedido->billing_country || $prefijo))
	{
		$prefijo_internacional = dame_prefijo_pais($pedido->billing_country);
		preg_match("/(\d{1,4})[0-9.\- ]+/", $userphone, $prefijo);
		if (strpos($prefijo[1], $prefijo_internacional) === false) $userphone = $prefijo_internacional . $userphone;
	}

	return $userphone;
}

function woo_sms_procesa_variables($mensaje, $pedido, $variables, $nota = '') {
	$woo_sms = array("id", "order_key", "billing_first_name", "billing_last_name", "billing_company", "billing_address_1", "billing_address_2", "billing_city", "billing_postcode", "billing_country", "billing_state", "billing_email", "billing_phone", "shipping_first_name", "shipping_last_name", "shipping_company", "shipping_address_1", "shipping_address_2", "shipping_city", "shipping_postcode", "shipping_country", "shipping_state", "shipping_method", "shipping_method_title", "payment_method", "payment_method_title", "order_subtotal", "order_discount", "cart_discount", "order_tax", "order_shipping", "order_shipping_tax", "order_total", "status", "shop_name", "note"); 

	$variables = str_replace(array("\r\n", "\r"), "\n", $variables);
	$variables = explode("\n", $variables);

	preg_match_all("/%(.*?)%/", $mensaje, $busqueda);

	foreach ($busqueda[1] as $variable) 
	{ 
    	$variable = strtolower($variable);

    	if (!in_array($variable, $woo_sms) && !in_array($variable, $variables)) continue;

    	if ($variable != "shop_name" && $variable != "note") 
		{
			if (in_array($variable, $woo_sms)) $mensaje = str_replace("%" . $variable . "%", $pedido->$variable, $mensaje); 
			else $mensaje = str_replace("%" . $variable . "%", $pedido->order_custom_fields[$variable][0], $mensaje); 
		}
		else if ($variable == "shop_name") $mensaje = str_replace("%" . $variable . "%", get_bloginfo('name'), $mensaje);
		else if ($variable == "note") $mensaje = str_replace("%" . $variable . "%", $nota, $mensaje);
	}
	
	return $mensaje;
}

function dame_prefijo_pais($pais = '') {
	$paises = array('IR' => '0');

	return ($pais == '') ? $paises : (isset($paises[$pais]) ? $paises[$pais] : '');
}

function woo_sms_plugin($nombre) {
	$argumentos = (object) array('slug' => $nombre);
	$consulta = array('action' => 'plugin_information', 'timeout' => 15, 'request' => serialize($argumentos));
	$plugin = unserialize($respuesta['body']);
	return get_object_vars($plugin);
}

function woo_sms_muestra_mensaje() {
	wp_register_style('woo_sms_style', plugins_url('style.css', __FILE__)); 
	$configuracion = get_option('persianscript_sms_woocommerce');
	if (!isset($configuracion['mensaje_pedido']) || !isset($configuracion['mensaje_nota'])) add_action('admin_notices', 'woo_sms_actualizacion');
}
add_action('admin_init', 'woo_sms_muestra_mensaje');

function woo_sms_actualizacion() {
	global $woo_sms;
	
    echo '<div class="error fade" id="message"><h3>' . $woo_sms['plugin'] . '</h3><h4>لطفا تنظیمات افزونه پیامک را انجام دهید. اینجا <a href="' . $woo_sms['ajustes'] . '" title="تنظیمات">کلیک کنید</a></h4></div>';
}

?>
