<?php
class WoocommerceIR_Notification_SMS {
    public function __construct() {
		
		if( ps_sms_options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' ) {
			add_action( 'widgets_init', 'woo_ps_sms_load_widget' );
			add_shortcode( 'woo_ps_sms',  array( $this, 'woo_sms_short_code') );
			add_action ( 'woocommerce_single_product_summary' , array ( $this , 'add_notif_input_after_summary' ) , 39) ;
			add_action( 'wp_ajax_save_numbers_to_product_meta', array( $this, 'save_numbers_to_product_meta') );
			add_action( 'wp_ajax_nopriv_save_numbers_to_product_meta', array( $this, 'save_numbers_to_product_meta') );
			add_action( 'woocommerce_product_sms',     array( $this, 'product_page_hannanstd_custom_tabs_panel' ) );
			add_action( 'woocommerce_product_set_stock_status', array( $this, 'send_sms_when_is_in_stock') );
			add_action( 'send_sms_onsale_event',  array( $this, 'send_sms_when_is_onsale') );
		}
	
		add_action( 'woocommerce_process_product_meta',     array( $this, 'product_save_data' ), 9999, 2 );
		add_action( 'woocommerce_low_stock', array( $this, 'send_sms_when_is_low_stock' ) );
		add_action( 'woocommerce_product_set_stock_status', array( $this, 'send_admin_sms_when_is_out_stock')  );		
	}
	
	
	public function product_page_hannanstd_custom_tabs_panel( $thepostid ) {
		woocommerce_wp_checkbox( array( 'id' => 'enable_notif_sms', 'wrapper_class' => 'enable_notif_sms', 'label' => __( 'فعالسازی (نمایش خودکار در محصول)', 'woocommerce' ),'cbvalue' => 'on','desc_tip' => true,
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_enable_notif_sms', true ) : ps_sms_options( 'enable_notif_sms', 'sms_notif_settings', 'no' )),
		'description' => __( 'با فعالسازی این قسمت گزینه "میخواهم از وضعیت محصول توسط پیامک با خبر شوم" در صفحه محصولات اضافه خواهد شد .<br/>میتوانید این قسمت "نمایش خودکار" را غیرفعال نمایید و بجای آن از شورت کد "[woo_ps_sms]" یا ابزارک "اطلاع رسانی پیامکی ووکامرس" در صفحه محصول استفاده نمایید .', 'woocommerce' ) ) );
		echo '<p>تذکر : برای جلوگیری از مشکل تداخل  جیکوئری ، در صفحه هر محصول فقط از یکی از حالت های "نمایش خودکار" ، "ابزارک" یا "شورت کد" استفاده نمایید .</p>'; 
		echo '<hr/>'; 
		woocommerce_wp_text_input( array( 'id' => 'notif_title' ,'class' => '', 'label' => __( 'متن سر تیتر گزینه ها', 'persianwoosms' ), 'desc_tip' => true, 'placeholder' => '' ,
		'description' => 'این متن در صفحه محصول به صورت چک باکس ظاهر خواهد شد و مشتری با فعال کردن آن میتواند شماره خود را برای دریافت اطلاعیه آن محصول وارد نماید .',
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_title', true ) : ps_sms_options( 'notif_title', 'sms_notif_settings', '' )),
		 ) );
		echo '<hr/>'; 
		echo '<div class="hannanstd-woo-tabs-hidden-how-to-info" style="display: none;">
				<h3 style="padding-top:0;padding-bottom:0;">شورت کد های قابل استفاده در متن پیامک ها :</h3>
				<p style="margin:0;padding-left:13px;"><code>{sku}</code> : شناسه محصول ، <code>{product_title}</code> : عنوان محصول ، <code>{regular_price}</code> قیمت اصلی ، <code>{onsale_price}</code> : قیمت فروش فوق العاده<br/><code>{onsale_from}</code> : تاریخ شروع فروش فوق العاده ، <code>{onsale_to}</code> : تاریخ اتمام فروش فوق العاده ، <code>{stock}</code> : موجودی انبار</p>
			</div>
			<div class="dashicons dashicons-editor-help hannanstd-tabs-how-to-toggle" title="راهنمایی"></div>';
	
		woocommerce_wp_checkbox( array( 'id' => 'enable_onsale', 'wrapper_class' => 'enable_onsale', 'label' => __( 'زمانیکه که محصول حراج شد', 'woocommerce' ),'cbvalue' => 'on','desc_tip' => true,
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_enable_onsale', true ) : ps_sms_options( 'enable_onsale', 'sms_notif_settings', 'no' )),
		'description' => __( 'هنگامی که این گزینه فعال باشد در صورت حراج نبودن محصول گزینه "زمانیکه که محصول حراج شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .', 'woocommerce' ) ) );
		woocommerce_wp_text_input( array( 'id' => 'notif_onsale_text' ,'class' => '', 'label' => __( 'متن گزینه "زمانیکه محصول حراج شد"', 'persianwoosms' ), 'desc_tip' => true, 'placeholder' => '' ,
		'description' => 'میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه محصول حراج شد" نمایید .',
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_onsale_text', true ) : ps_sms_options( 'notif_onsale_text', 'sms_notif_settings', '' )),
		 ) );
		woocommerce_wp_textarea_input(  array( 'id' => 'notif_onsale_sms','class' => 'short', 'label' => __( 'متن پیامک "زمانیکه محصول حراج شد"', 'woocommerce' ), 'desc_tip' => true,
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_onsale_sms', true ) : ps_sms_options( 'notif_onsale_sms', 'sms_notif_settings', '' )),
		'description' => __( '', 'woocommerce' ) ) );
		echo '<hr/>'; 
		woocommerce_wp_checkbox( array( 'id' => 'enable_notif_no_stock', 'wrapper_class' => 'enable_notif_no_stock', 'label' => __( 'زمانیکه که محصول موجود شد', 'woocommerce' ),'cbvalue' => 'on','desc_tip' => true,
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_enable_notif_no_stock', true ) : ps_sms_options( 'enable_notif_no_stock', 'sms_notif_settings', 'no' )),
		'description' => __( 'هنگامی که این گزینه فعال باشد در صورت ناموجود شدن محصول گزینه "زمانیکه که محصول موجود شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .', 'woocommerce' ) ) );
		woocommerce_wp_text_input( array( 'id' => 'notif_no_stock_text' ,'class' => '', 'label' => __( 'متن گزینه "زمانیکه محصول موجود شد"', 'persianwoosms' ), 'desc_tip' => true, 'placeholder' => '' ,
		'description' => 'میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه محصول موجود شد" نمایید .',
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_no_stock_text', true ) : ps_sms_options( 'notif_no_stock_text', 'sms_notif_settings', '' )),
		 ) );
		woocommerce_wp_textarea_input(  array( 'id' => 'notif_no_stock_sms','class' => 'short', 'label' => __( 'متن پیامک "زمانیکه محصول موجود شد"', 'woocommerce' ), 'desc_tip' => true,
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_no_stock_sms', true ) : ps_sms_options( 'notif_no_stock_sms', 'sms_notif_settings', '' )),
		'description' => __( '', 'woocommerce' ) ) );
		echo '<hr/>'; 
		woocommerce_wp_checkbox( array( 'id' => 'enable_notif_low_stock', 'wrapper_class' => 'enable_notif_low_stock', 'label' => __( 'زمانیکه موجودی انبار محصول کم شد', 'woocommerce' ),'cbvalue' => 'on','desc_tip' => true,
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_enable_notif_low_stock', true ) : ps_sms_options( 'enable_notif_low_stock', 'sms_notif_settings', 'no' )),
		'description' => __( 'هنگامی که این گزینه فعال باشد ، گزینه "زمانیکه که موجودی انبار محصول کم شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .', 'woocommerce' ) ) );
		woocommerce_wp_text_input( array( 'id' => 'notif_low_stock_text' ,'class' => '', 'label' => __( 'متن گزینه "زمانیکه موجودی انبار محصول کم شد"', 'persianwoosms' ), 'desc_tip' => true, 'placeholder' => '' ,
		'description' => 'میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه موجودی انبار محصول کم شد" نمایید .',
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_low_stock_text', true ) : ps_sms_options( 'notif_low_stock_text', 'sms_notif_settings', '' )),
		 ) );
		woocommerce_wp_textarea_input(  array( 'id' => 'notif_low_stock_sms','class' => 'short', 'label' => __( 'متن پیامک "زمانیکه محصول موجودی انبار کم شد"', 'woocommerce' ), 'desc_tip' => true,
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_low_stock_sms', true ) : ps_sms_options( 'notif_low_stock_sms', 'sms_notif_settings', '' )),
		'description' => __( '', 'woocommerce' ) ) );
		echo '<hr/>';
		woocommerce_wp_textarea_input(  array( 'id' => 'notif_options','class' => 'short', 'label' => __( 'گزینه های دلخواه', 'woocommerce' ), 'desc_tip' => true,'style'=>'height:100px;',
		'value' => ( get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_options', true ) : ps_sms_options( 'notif_options', 'sms_notif_settings', '' )),
		'description' => __( 'شما میتوانید گزینه های دلخواه خود را برای نمایش در صفحه محصولات ایجاد نمایید و به صورت دستی به مشتریانی که در گزینه های بالا عضو شده اند پیامک ارسال کنید .<br/>
		برای اضافه کردن گزینه ها ، همانند نمونه بالا ابتدا یک کد عددی دلخواه تعریف کنید سپس بعد از قرار دادن عبارت ":" متن مورد نظر را بنویسید .<br/>
		دقت کنید که کد عددی هر گزینه بسیار مهم بوده و از تغییر کد مربوط به هر گزینه بعد از ذخیره تنظیمات خود داری نمایید .', 'woocommerce' ) ) );
		echo '<hr/>'; 
	}
	
	public function product_save_data( $post_id, $post ) {
		
		$product = wc_get_product( $post_id );
		
		if( ps_sms_options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' ) {
			update_post_meta( $post_id, '_is_sms_set', 'yes' );
			update_post_meta( $post_id, '_enable_notif_sms', ( isset($_POST['enable_notif_sms']) ? $_POST['enable_notif_sms'] : 'no' ) );
			update_post_meta( $post_id, '_notif_title', ( isset($_POST['notif_title']) ? $_POST['notif_title'] : '' ) );
			update_post_meta( $post_id, '_enable_onsale', ( isset($_POST['enable_onsale']) ? $_POST['enable_onsale'] : 'no' ) );
			update_post_meta( $post_id, '_notif_onsale_text', ( isset($_POST['notif_onsale_text']) ? $_POST['notif_onsale_text'] : '' ) );
			update_post_meta( $post_id, '_notif_onsale_sms', ( isset($_POST['notif_onsale_sms']) ? $_POST['notif_onsale_sms'] : '' ) );
			update_post_meta( $post_id, '_enable_notif_low_stock', ( isset($_POST['enable_notif_low_stock']) ? $_POST['enable_notif_low_stock'] : 'no' ) );
			update_post_meta( $post_id, '_notif_low_stock_text', ( isset($_POST['notif_low_stock_text']) ? $_POST['notif_low_stock_text'] : '' ) );
			update_post_meta( $post_id, '_notif_low_stock_sms', ( isset($_POST['notif_low_stock_sms']) ? $_POST['notif_low_stock_sms'] : '' ) );
			update_post_meta( $post_id, '_enable_notif_no_stock', ( isset($_POST['enable_notif_no_stock']) ? $_POST['enable_notif_no_stock'] : 'no' ) );
			update_post_meta( $post_id, '_notif_no_stock_text', ( isset($_POST['notif_no_stock_text']) ? $_POST['notif_no_stock_text'] : '' ) );
			update_post_meta( $post_id, '_notif_no_stock_sms', ( isset($_POST['notif_no_stock_sms']) ? $_POST['notif_no_stock_sms'] : '' ) );
			update_post_meta( $post_id, '_notif_options', ( isset($_POST['notif_options']) ? $_POST['notif_options'] : '' ) );
			if ( get_post_meta( $post_id, '_onsale_send', true) !='yes' && ( !empty($_POST['_sale_price']) && $_POST['_sale_price'] < $_POST['_regular_price'] ) ) {
				$date_from = isset( $_POST['_sale_price_dates_from'] ) ? wc_clean( $_POST['_sale_price_dates_from'] ) : '';
				$date_from = strtotime( $date_from );
				if ( ! $date_from || !is_numeric( $date_from ) || $date_from <= strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
					$this->send_sms_when_is_onsale($product);
				}
				else {
					wp_schedule_single_event( $date_from , 'send_sms_onsale_event' , array($product) );
				}	
			}
			elseif ( get_post_meta( $post_id, '_onsale_send', true) =='yes' && ( empty($_POST['_sale_price']) || $_POST['_sale_price'] >= $_POST['_regular_price'] ) ) {
				update_post_meta( $post_id, '_onsale_send', 'no'  );
			}
		}
	
		if ( get_post_meta( $post_id, '_low_stock_send', true) !='yes' && isset($_REQUEST['_manage_stock']) && ( isset($_POST['_stock_status']) 
			&& $_POST['_stock_status'] == 'instock' ) && isset($_POST['_stock']) && 
			$_POST['_stock'] < get_option( 'woocommerce_notify_low_stock_amount' ) && $_POST['_stock'] >= get_option( 'woocommerce_notify_no_stock_amount' ) ) {
			$this->send_sms_when_is_low_stock($product);
		}
		else if ( get_post_meta( $post_id, '_low_stock_send', true) =='yes' && ((isset($_POST['_stock']) && $_POST['_stock'] >= get_option( 'woocommerce_notify_low_stock_amount' )) || !isset($_POST['_stock'])|| !isset($_POST['_manage_stock']))  ) {
			update_post_meta( $post_id, '_low_stock_send', 'no'  );
		}
		
		
	}
	
	
	public function add_notif_input_after_summary() {
		global $product, $woocommerce_loop;
		$thepostid = $product->id;
		$is_old = get_post_meta( $thepostid, '_is_sms_set', true ) ? false : true;
		
		if ( $is_old ) {
			if ( ps_sms_options( 'notif_old_pr', 'sms_notif_settings', 'no' ) == 'yes' ) {
			  if ( ps_sms_options( 'enable_notif_sms', 'sms_notif_settings', 'no' ) !='on' )
				  return;
			}
			else
				return;
		}
		else if ( get_post_meta( $thepostid, '_enable_notif_sms', true ) != 'on' )
			return;
		
		global $woo_notif;
		if ( $woo_notif == 'yes' || $GLOBALS['woo_notif'] == 'yes' )
			return;
		else 
			$GLOBALS['woo_notif'] = $woo_notif = 'yes';
			
		$this->notif_function( $product );
	}
	
	public function woo_sms_short_code() {
		if ( !is_product() ) return;
		
		global $woo_notif;
		if ( $woo_notif == 'yes' || $GLOBALS['woo_notif'] == 'yes' )
			return;
		else 
			$GLOBALS['woo_notif'] = $woo_notif = 'yes';
			
		$product_id = get_the_ID();
		$product = wc_get_product( $product_id );
		return is_object( $product ) ? $this->notif_function( $product ) : false;
	}
	
	public function notif_function( $product ) {
		$thepostid = $product->id;
		$this->scripts_product_frontend( $thepostid );
		?>
		<form class="cart" style="max-width:293px" enctype="multipart/form-data" method="post">
			<img src="<?php echo PS_WOO_SMS_PLUGIN_PATH; ?>/assets/images/tick.png" style="display:none !important" />
			<img src="<?php echo PS_WOO_SMS_PLUGIN_PATH; ?>/assets/images/false.png" style="display:none !important" />
			<img src="<?php echo PS_WOO_SMS_PLUGIN_PATH; ?>/assets/images/ajax-loader.gif" style="display:none !important" />
			<input type="checkbox" id="sms_news_check" name="sms_news_check" value="1"/>
			<label id="sms_news_check_label"  for="sms_news_check" >
			<?php echo get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_title', true ) : ps_sms_options( 'notif_title', 'sms_notif_settings', '' ); ?>
			</label><br class="sms_qroup_check"  />
			<?php
			if ( ( (get_post_meta( $thepostid, '_is_sms_set', true ) && get_post_meta( $thepostid, '_enable_onsale', true ) == 'on') ||
			( ! get_post_meta( $thepostid, '_is_sms_set', true ) && ps_sms_options( 'enable_onsale', 'sms_notif_settings', '' ) == 'on' ) ) && !$product->is_on_sale()   ) {
				$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_onsale_text', true ) : ps_sms_options( 'notif_onsale_text', 'sms_notif_settings', '' );
				$code = '_onsale';
			?>
				<input style="margin-right:15px;" type="checkbox" id="sms_qroup_check_<?php echo $code; ?>" class="sms_qroup_check" name="sms_qroup[]" value="<?php echo $code;?>"/>
				<label class="sms_qroup_check_label"  for="sms_qroup_check_<?php echo $code; ?>" ><?php echo $text;?></label><br class="sms_qroup_check"  />
			<?php
			}
			$options = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_options', true ) :  ps_sms_options( 'notif_options', 'sms_notif_settings', '' );
			$options = !empty($options) ? $options : array();
			$options = explode ( PHP_EOL , $options);
			foreach ( ( array ) $options as $option )  {
				list( $code , $text) = explode ( ":", $option);
				if ( strlen($text) > 1) {
			?>
				<input style="margin-right:15px;" type="checkbox" id="sms_qroup_check_<?php echo $code; ?>" class="sms_qroup_check" name="sms_qroup[]" value="<?php echo $code;?>"/>
				<label class="sms_qroup_check_label"  for="sms_qroup_check_<?php echo $code; ?>" ><?php echo $text;?></label><br class="sms_qroup_check"  />
			<?php
				}
			}
			if ( ((get_post_meta( $thepostid, '_is_sms_set', true ) && get_post_meta( $thepostid, '_enable_notif_low_stock', true ) == 'on') ||
			( ! get_post_meta( $thepostid, '_is_sms_set', true ) && ps_sms_options( 'enable_notif_low_stock', 'sms_notif_settings', '' ) == 'on' )) 
			&& $product->get_total_stock() >= get_option( 'woocommerce_notify_low_stock_amount' ) && $product->is_in_stock() ) {
				$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_low_stock_text', true ) : ps_sms_options( 'notif_low_stock_text', 'sms_notif_settings', '' );
				$code = '_low';
			?>
				<input style="margin-right:15px;" type="checkbox" id="sms_qroup_check_<?php echo $code; ?>" class="sms_qroup_check" name="sms_qroup[]" value="<?php echo $code;?>"/>
				<label class="sms_qroup_check_label"  for="sms_qroup_check_<?php echo $code; ?>" ><?php echo $text;?></label><br class="sms_qroup_check"  />
			<?php
			}
			if ( ( (get_post_meta( $thepostid, '_is_sms_set', true ) && get_post_meta( $thepostid, '_enable_notif_no_stock', true ) == 'on') ||
			( ! get_post_meta( $thepostid, '_is_sms_set', true ) && ps_sms_options( 'enable_notif_no_stock', 'sms_notif_settings', '' ) == 'on' ) )  && !$product->is_in_stock() ) {
				$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_no_stock_text', true ) : ps_sms_options( 'notif_no_stock_text', 'sms_notif_settings', '' );
				$code = '_in';
			?>
				<input style="margin-right:15px;" type="checkbox" id="sms_qroup_check_<?php echo $code; ?>" class="sms_qroup_check" name="sms_qroup[]" value="<?php echo $code;?>"/>
				<label class="sms_qroup_check_label"  for="sms_qroup_check_<?php echo $code; ?>" ><?php echo $text;?></label><br class="sms_qroup_check"  />
			<?php
			}
			?>
			<input type="text" id="sms_news_text" name="sms_news_text" style="width:auto;direction:ltr;text-align:left;"/>	
			<button id="sms_news_btn" class="single_add_to_cart_button button alt" type="submit">ثبت</button>
			<span id="submit_result"></span>
		</form>
	<?php
	}
	
	public function scripts_product_frontend( $product_id ){
		wc_enqueue_js('
			var HANNANStd_Woo_SMS = jQuery.noConflict();
			HANNANStd_Woo_SMS(document).ready(function(){
				HANNANStd_Woo_SMS("#submit_result").hide();
				HANNANStd_Woo_SMS("#sms_news_text").hide();
				HANNANStd_Woo_SMS(".sms_qroup_check_label").hide();
				HANNANStd_Woo_SMS(".sms_qroup_check").hide();
				HANNANStd_Woo_SMS("#sms_news_btn").hide();
				HANNANStd_Woo_SMS("#sms_news_check").change(function(){
					if( HANNANStd_Woo_SMS("#sms_news_check:checked").val() ) {
						HANNANStd_Woo_SMS("#sms_news_text").fadeIn(); 
						HANNANStd_Woo_SMS("#sms_news_btn").fadeIn(); 	
						HANNANStd_Woo_SMS("#submit_result").fadeIn(); 	
						HANNANStd_Woo_SMS(".sms_qroup_check_label").fadeIn(); 	
						HANNANStd_Woo_SMS(".sms_qroup_check").fadeIn(); 	
					}						
					else{
						HANNANStd_Woo_SMS("#sms_news_text").fadeOut();
						HANNANStd_Woo_SMS("#sms_news_btn").fadeOut();
						HANNANStd_Woo_SMS("#submit_result").fadeOut();
						HANNANStd_Woo_SMS(".sms_qroup_check_label").fadeOut();
						HANNANStd_Woo_SMS(".sms_qroup_check").fadeOut();
					}
				});
			});
			HANNANStd_Woo_SMS( document ).on( "click", "#sms_news_btn", function() {
				HANNANStd_Woo_SMS("#submit_result").html( "<img src=\"'.PS_WOO_SMS_PLUGIN_PATH.'/assets/images/ajax-loader.gif\" />" );
				var sms_number = HANNANStd_Woo_SMS("#sms_news_text").val();
				var sms_group = [];
				HANNANStd_Woo_SMS(".sms_qroup_check:checked").each(function(i){
					sms_group[i] = HANNANStd_Woo_SMS(this).val();
				});
				HANNANStd_Woo_SMS.ajax({
					url : "'.admin_url( "admin-ajax.php" ).'",
					type : "post",
					data : {
						action : "save_numbers_to_product_meta",
						security: "'.wp_create_nonce( "save-numbers-to-product-meta" ).'",
						sms_number : sms_number,
						sms_group : sms_group,
						product_id : "'.$product_id.'",
					},
					success : function( response ) {
						HANNANStd_Woo_SMS("#submit_result").html( response );
					}
				});
				return false;
			});
		' );
	}
	
	function save_numbers_to_product_meta() {
		check_ajax_referer( 'save-numbers-to-product-meta', 'security' );
		$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : false;
		$sms_number = isset($_POST['sms_number']) ? $_POST['sms_number'] : false;
		$group = isset($_POST['sms_group']) ? $_POST['sms_group'] : false;
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) { 
			if ( !$sms_number || strlen( $sms_number) < 1 ) {
				echo '<img src="'.PS_WOO_SMS_PLUGIN_PATH.'/assets/images/false.png">&nbsp;';
				echo 'شماره تلفن را وارد نمایید';
				die();
			}
			else if ( !is_numeric($sms_number) ) {
				echo '<img src="'.PS_WOO_SMS_PLUGIN_PATH.'/assets/images/false.png">&nbsp;';
				echo 'شماره تلفن معتبر نیست';
				die();
			}
			else if ( !$group || empty( $group ) ) {
				echo '<img src="'.PS_WOO_SMS_PLUGIN_PATH.'/assets/images/false.png">&nbsp;';
				echo 'انتخاب یکی از گزینه ها الزامیست';
				die();
			}
			else {
				$old_meta = get_post_meta( $product_id, '_hannanstd_sms_notification',  true) ? get_post_meta( $product_id, '_hannanstd_sms_notification',  true) : '';
				$new_meta = $sms_number.'|'.implode(',',$group).'***';
				if ( strpos($new_meta, $old_meta) === false) {
					$meta = $old_meta.$new_meta;
					update_post_meta( $product_id, '_hannanstd_sms_notification',  $meta);
					echo '<img src="'.PS_WOO_SMS_PLUGIN_PATH.'/assets/images/tick.png">&nbsp;';
					echo 'شماره شما ثبت شد';
					die();
				}
				else {
					echo '<img src="'.PS_WOO_SMS_PLUGIN_PATH.'/assets/images/false.png">&nbsp;';
					echo 'این شماره پیش تر ثبت شده است .';
					die();
				}
			}
		}
		else {
			echo 'خطایی در ثبت اطلاعات رخ داده است .';
			die();
		}
	}
	
	function send_sms_when_is_onsale($product) {
		$product_id = $product->id;
		if ( (get_post_meta( $product_id, '_is_sms_set', true ) && get_post_meta( $product_id, '_enable_onsale', true ) == 'on') ||
		( ! get_post_meta( $product_id, '_is_sms_set', true ) && ps_sms_options( 'enable_onsale', 'sms_notif_settings', '' ) == 'on' ) ) {
			if ( get_post_meta( $product_id, '_onsale_send', true) =='yes' ) return;
			$product_metas = get_post_meta( $product_id, '_hannanstd_sms_notification',  true) ? get_post_meta( $product_id, '_hannanstd_sms_notification',  true) : '';
			$contacts = explode ( '***', $product_metas );
			$numbers_list = array();
			foreach ( (array) $contacts as $contact ) {
				list( $number , $groups ) = explode ( '|', $contact);
				$groups = explode ( ',' , $groups);
			
				if ( in_array( '_onsale', $groups ) ) {
					$numbers_list[] = $number;
				}
			}
			$numbers_list = array_unique( explode( ',', implode( ',', $numbers_list )) );
			$active_gateway = ps_sms_options( 'sms_gateway', 'sms_main_settings', '' );
			$receiver_sms_data['number']   = $numbers_list;
			$receiver_sms_data['sms_body'] = get_post_meta( $product_id, '_is_sms_set', true ) ? get_post_meta( $product_id, '_notif_onsale_sms', true ) : ps_sms_options( 'notif_onsale_sms', 'sms_notif_settings', '' );
			$receiver_sms_data['sms_body'] = str_replace_tags_product( $receiver_sms_data['sms_body'] , $product_id );
			
			$receiver_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $receiver_sms_data );
			if( $receiver_response ) {
				update_post_meta( $product_id, '_onsale_send', 'yes'  );
			}
		}
	}
	
	function send_sms_when_is_low_stock($product) {
		$product_id = $product->id;
		if ( get_post_meta( $product_id, '_low_stock_send', true) =='yes' ) return;
		if ( (get_post_meta( $product_id, '_is_sms_set', true ) && get_post_meta( $product_id, '_enable_notif_low_stock', true ) == 'on') ||
		( ! get_post_meta( $product_id, '_is_sms_set', true ) && ps_sms_options( 'enable_notif_low_stock', 'sms_notif_settings', '' ) == 'on' )  ) {
			
			$product_metas = get_post_meta( $product_id, '_hannanstd_sms_notification',  true) ? get_post_meta( $product_id, '_hannanstd_sms_notification',  true) : '';
			$contacts = explode ( '***', $product_metas );
			$numbers_list = array();
			foreach ( (array) $contacts as $contact ) {
				list( $number , $groups ) = explode ( '|', $contact);
				$groups = explode ( ',' , $groups);
			
				if ( in_array( '_low', $groups ) ) {
					$numbers_list[] = $number;
				}
			}
			$numbers_list = array_unique( explode( ',', implode( ',', $numbers_list )) );
			$active_gateway = ps_sms_options( 'sms_gateway', 'sms_main_settings', '' );
			$receiver_sms_data['number']   = $numbers_list;
			$receiver_sms_data['sms_body'] = get_post_meta( $product_id, '_is_sms_set', true ) ? get_post_meta( $product_id, '_notif_low_stock_sms', true ) : ps_sms_options( 'notif_low_stock_sms', 'sms_notif_settings', '' );
			$receiver_sms_data['sms_body'] = str_replace_tags_product( $receiver_sms_data['sms_body'] , $product_id );
			$receiver_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $receiver_sms_data );
			if ( $receiver_response )
				$user_sent = 'yes';
			else
				$user_sent = 'no';
		}
		else 
			$user_sent = 'yes';
		$numbers_list = '';
		if( ps_sms_options( 'enable_super_admin_sms', 'sms_admin_settings', 'on' ) == 'on' &&  strlen(ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' )) > 5 ) {	
			if ( in_array( 'low', ps_sms_options( 'super_admin_order_status', 'sms_admin_settings', array() ) ) ) {
				if ( $numbers_list == '' )
					$numbers_list = ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' );
				else
					$numbers_list = $numbers_list.','.ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' );
			}
		}
		if( ps_sms_options( 'enable_product_admin_sms', 'sms_admin_settings', 'on' ) == 'on' ) {
			$admin_datas = maybe_unserialize( get_post_meta( $product_id, '_hannanstd_woo_products_tabs', true ) );
			foreach ( (array) $admin_datas as $admin_data ) {
				if( in_array( 'low' , explode( '-sv-' , $admin_data['content']) ) ) {
					if ( $numbers_list == '' )
						$numbers_list = $admin_data['title'];
					else
						$numbers_list = $numbers_list.','.$admin_data['title'];
				}
			}
		}
		$numbers = array();
		$numbers = array_unique( explode( ',', $numbers_list ) );
		$active_gateway = ps_sms_options( 'sms_gateway', 'sms_main_settings', '' );
		$receiver_sms_data['number']   = $numbers;
		$receiver_sms_data['sms_body'] = ps_sms_options( 'admin_low_stock', 'sms_admin_settings', '' );
		$receiver_sms_data['sms_body'] = str_replace_tags_product( $receiver_sms_data['sms_body'] , $product_id );
		$receiver_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $receiver_sms_data );
		if ( $receiver_response )
			$admin_send = 'yes';
		else
			$admin_send = 'no';
		
		if ( $admin_send == 'yes' &&  $user_sent == 'yes'  )
			update_post_meta( $product_id, '_low_stock_send', 'yes'  );
	}
	
	
	function send_sms_when_is_in_stock($product_id) {
		$product = wc_get_product( $product_id );		
		if ( (get_post_meta( $product_id, '_is_sms_set', true ) && get_post_meta( $product_id, '_enable_notif_no_stock', true ) == 'on') ||
		( ! get_post_meta( $product_id, '_is_sms_set', true ) && ps_sms_options( 'enable_notif_no_stock', 'sms_notif_settings', '' ) == 'on' )  ) {
			if ( !$product->is_in_stock() ) {
				update_post_meta( $product_id, '_in_stock_send', 'no'  );		
				return;
			}
			if ( get_post_meta( $product_id, '_in_stock_send', true) =='yes' ) return;
			$product_metas = get_post_meta( $product_id, '_hannanstd_sms_notification',  true) ? get_post_meta( $product_id, '_hannanstd_sms_notification',  true) : '';
			$contacts = explode ( '***', $product_metas );
			$numbers_list = array();
			foreach ( (array) $contacts as $contact ) {
				list( $number , $groups ) = explode ( '|', $contact);
				$groups = explode ( ',' , $groups);
				if ( in_array( '_in', $groups ) ) {
					$numbers_list[] = $number;
				}
			}
			$numbers_list = array_unique( explode( ',', implode( ',', $numbers_list )) );
			$active_gateway = ps_sms_options( 'sms_gateway', 'sms_main_settings', '' );
			$receiver_sms_data['number']   = $numbers_list;
			$receiver_sms_data['sms_body'] = get_post_meta( $product_id, '_is_sms_set', true ) ? get_post_meta( $product_id, '_notif_no_stock_sms', true ) : ps_sms_options( 'notif_no_stock_sms', 'sms_notif_settings', '' );
			$receiver_sms_data['sms_body'] = str_replace_tags_product( $receiver_sms_data['sms_body'] , $product_id );
			$receiver_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $receiver_sms_data );
			if( $receiver_response )
				update_post_meta( $product_id, '_in_stock_send', 'yes'  );	
		}
	}
	
	function send_admin_sms_when_is_out_stock($product_id) {
		if( ps_sms_options( 'enable_super_admin_sms', 'sms_admin_settings', 'on' ) != 'on' && ps_sms_options( 'enable_product_admin_sms', 'sms_admin_settings', 'on' ) != 'on' )
			return;
		
		if ( ! metadata_exists( 'post', $product_id, '_stock' )  ) {
			return;
		}
		
		$product = wc_get_product( $product_id );		
			
		if ( $product->is_in_stock() ) {
			update_post_meta( $product_id, '_out_stock_send', 'no'  );		
			return;
		}
		if ( get_post_meta( $product_id, '_out_stock_send', true) =='yes' ) return;
		$numbers_list = '';
		if( ps_sms_options( 'enable_super_admin_sms', 'sms_admin_settings', 'on' ) == 'on' &&  strlen(ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' )) > 5 ) {	
			if ( in_array( 'out', ps_sms_options( 'super_admin_order_status', 'sms_admin_settings', array() ) ) ) {
				if ( $numbers_list == '' )
					$numbers_list = ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' );
				else
					$numbers_list = $numbers_list.','.ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' );
			}
		}
		if( ps_sms_options( 'enable_product_admin_sms', 'sms_admin_settings', 'on' ) == 'on' ) {
			$admin_datas = maybe_unserialize( get_post_meta( $product_id, '_hannanstd_woo_products_tabs', true ) );
			foreach ( (array) $admin_datas as $admin_data ) {
				if( in_array( 'out' , explode( '-sv-' , $admin_data['content']) ) ) {
					if ( $numbers_list == '' )
						$numbers_list = $admin_data['title'];
					else
						$numbers_list = $numbers_list.','.$admin_data['title'];
				}
			}
		}
		$numbers = array();
		$numbers = array_unique( explode( ',', $numbers_list) );
		$active_gateway = ps_sms_options( 'sms_gateway', 'sms_main_settings', '' );
		$receiver_sms_data['number']   = $numbers;
		$receiver_sms_data['sms_body'] = ps_sms_options( 'admin_out_stock', 'sms_admin_settings', '' );
		$receiver_sms_data['sms_body'] = str_replace_tags_product( $receiver_sms_data['sms_body'] , $product_id );
		$receiver_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $receiver_sms_data );
		if( $receiver_response )
			update_post_meta( $product_id, '_out_stock_send', 'yes'  );	
	}
	
}