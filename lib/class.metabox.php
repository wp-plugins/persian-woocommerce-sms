<?php
class WoocommerceIR_Metabox_SMS {
	
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box_woocommerce' ) ); 
		add_action( 'wp_ajax_persianwoosms_send_sms_to_buyer', array( $this, 'send_sms_from_woocommerce_pages' ) );	
		add_action( 'wp_ajax_nopriv_persianwoosms_send_sms_to_buyer', array( $this, 'send_sms_from_woocommerce_pages' ) );	
	}
	
    public function add_meta_box_woocommerce( $post_type ) {
		global $post;
        if( $post_type == 'shop_order' && ps_sms_options( 'enable_metabox', 'sms_buyer_settings', 'off' ) == 'on' ) 
            add_meta_box( 'send_sms_to_buyer', 'ارسال پيامک به مشتري', array( $this, 'metabox_in_shop_order' ), 'shop_order', 'side', 'high' );
        
		if( $post->ID && $post_type == 'product' && ps_sms_options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' )
            add_meta_box( 'send_sms_to_buyer', 'ارسال پيامک به مشترکين اين محصول', array( $this, 'metabox_in_product' ), 'product', 'side', 'low' );
    }
	
    public function metabox_in_shop_order( $post ) {
		if ( get_post_meta( $post->ID, '_billing_phone', 'true' ) ) { 
			?>
			<div class="persianwoosms_send_sms" style="position:relative">
				<div class="persianwoosms_send_sms_result"></div>
				<h4>ارسال پيامک دلخواه به مشتري</h4>
				<p>تمامي پيامک هاي ارسال شده از طرف شما به شماره<code><?php echo get_post_meta( $post->ID, '_billing_phone', 'true' ) ?></code> ارسال مي گردد.</p>
				<p>
					<textarea rows="5" cols="20" class="input-text" id="persianwoosms_sms_to_buyer" name="persianwoosms_sms_to_buyer" style="width: 246px; height: 78px;"></textarea>
				</p>
				<p> 
					<?php wp_nonce_field('persianwoosms_send_sms_action','persianwoosms_send_sms_nonce'); ?>
					<input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
					<input type="hidden" name="post_type" value="shop_order">
					<input type="submit" class="button" name="persianwoosms_send_sms" id="persianwoosms_send_sms_button" value="ارسال پيامک">
				</p>
				<div id="persianwoosms_send_sms_overlay_block"><img src="<?php echo PS_WOO_SMS_PLUGIN_PATH.'/assets/images/ajax-loader.gif'; ?>" alt=""></div>
			</div>
			<?php
		}
		else { ?>	
			<div class="persianwoosms_send_sms" style="position:relative">
				<div class="persianwoosms_send_sms_result"></div>
				<h4>ارسال پيامک دلخواه به مشتري</h4>
				<p>شماره اي براي ارسال پيامک وجود ندارد</p>
			</div>
		<?php
		}
    }
	
	
    public function metabox_in_product( $post ) {
		$thepostid = $post->ID;
        ?>
        <div class="persianwoosms_send_sms" style="position:relative">
            <div class="persianwoosms_send_sms_result"></div>
            <h4>ارسال پيامک دلخواه به مشترکين اين محصول</h4>
            <p>
				<select name="select_group" id="select_group">
					<?php
					$options = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_options', true ) :  ps_sms_options( 'notif_options', 'sms_notif_settings', '' );
					$options = !empty($options) ? $options : array();
					$options = explode ( PHP_EOL , $options);
					foreach ( ( array ) $options as $option )  {
						list( $code , $text) = explode ( ":", $option);
						if ( strlen($text) > 1) {
						?>
						<option id="sms_qroup_check_<?php echo $code; ?>" value="<?php echo $code; ?>"><?php echo $text;?></option>
						<?php
						}
					}

						$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_onsale_text', true ) : ps_sms_options( 'notif_onsale_text', 'sms_notif_settings', '' );
						$code = '_onsale';
						?>
						<option id="sms_qroup_check_<?php echo $code; ?>" value="<?php echo $code; ?>"><?php echo $text;?></option>
						<?php
					

					
				
						$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_low_stock_text', true ) : ps_sms_options( 'notif_low_stock_text', 'sms_notif_settings', '' );
						$code = '_low';
						?>
						<option id="sms_qroup_check_<?php echo $code; ?>" value="<?php echo $code; ?>"><?php echo $text;?></option>
						<?php
					
					
						$text = get_post_meta( $thepostid, '_is_sms_set', true ) ? get_post_meta( $thepostid, '_notif_no_stock_text', true ) : ps_sms_options( 'notif_no_stock_text', 'sms_notif_settings', '' );
						$code = '_in';
						?>
						<option id="sms_qroup_check_<?php echo $code; ?>" value="<?php echo $code; ?>"><?php echo $text;?></option>
					
				</select>
			</p>
			<p>
                <textarea rows="5" cols="20" class="input-text" id="persianwoosms_sms_to_buyer" name="persianwoosms_sms_to_buyer" style="width: 246px; height: 78px;"></textarea>
            </p>
            
			<p> 
                <?php wp_nonce_field('persianwoosms_send_sms_action','persianwoosms_send_sms_nonce'); ?>
                <input type="hidden" name="post_id" value="<?php echo $post->ID; ?>">
				<input type="hidden" name="post_type" value="product">
                <input type="submit" class="button" name="persianwoosms_send_sms" id="persianwoosms_send_sms_button" value="ارسال پيامک">
            </p>
			
			
            <div id="persianwoosms_send_sms_overlay_block"><img src="<?php echo PS_WOO_SMS_PLUGIN_PATH.'/assets/images/ajax-loader.gif'; ?>" alt=""></div>
        </div>
        <?php
    }
	
	
    function send_sms_from_woocommerce_pages() {

		$active_gateway = ps_sms_options( 'sms_gateway', 'sms_main_settings', '' );

		if( empty( $active_gateway ) ) {
			wp_send_json_error( array('message' => 'درگاه پيامک تنظيم نشده است') );
		}
		
		if ( $_POST['post_type'] == 'shop_order' ) {
			
			$order = new WC_Order( $_POST['post_id'] );
			$phone = get_post_meta( $_POST['post_id'], '_billing_phone', true );
			$buyer_sms_data['number']   = explode( ',', $phone);
			$buyer_sms_data['sms_body'] = $_POST['textareavalue'];
			
			if ( !$buyer_sms_data['number'] || empty($buyer_sms_data['number']) ) {
				wp_send_json_error( array('message' => 'شماره اي براي دريافت وجود ندارد') );
			}
			elseif ( !$buyer_sms_data['sms_body'] || empty($buyer_sms_data['sms_body']) ) {
				wp_send_json_error( array('message' => 'متن پيامک خالي است') );
			}
			else {
				$buyer_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $buyer_sms_data );
				if( $buyer_response ) {
					$order->add_order_note( sprintf('پیامک با موفقیت به مشتری با شماره موبایل %s ارسال شد . <br/>متن پیامک : %s' , $phone , $buyer_sms_data['sms_body'] ));
					wp_send_json_success( array('message' => 'پيامک با موفقيت ارسال شد') );
				} else {
					$order->add_order_note('پيامک ارسال نشد. خطايي رخ داده است');
					$order->add_order_note( sprintf('پیامک به مشتری با شماره موبایل %s ارسال نشد . خطایی رخ داده است .<br/>متن پیامک : %s' , $phone , $buyer_sms_data['sms_body'] ));
				}
			}
		}
		
		
		if ( $_POST['post_type'] == 'product' ) {
			
			$buyer_sms_data['sms_body'] = $_POST['textareavalue'];
			if ( !$buyer_sms_data['sms_body'] || empty($buyer_sms_data['sms_body']) ) {
				wp_send_json_error( array('message' => 'متن پيامک خالي است') );
			}
			
			$product_id = $_POST['post_id'];
			$group = isset($_POST['group']) ? $_POST['group'] : '';
			if ( $group ) {
				$product_metas = get_post_meta( $product_id, '_hannanstd_sms_notification',  true) ? get_post_meta( $product_id, '_hannanstd_sms_notification',  true) : '';
				$contacts = explode ( '***', $product_metas );
				$numbers_list = array();
				foreach ( (array) $contacts as $contact ) {
					list( $number , $groups ) = explode ( '|', $contact);
					$groups = explode ( ',' , $groups);
		
					if ( in_array( $group, $groups ) ) {
						$numbers_list[] = $number;
					}
				}
				$numbers_list = array_unique( explode( ',', implode( ',', $numbers_list )) );
		
				$buyer_sms_data['number']   = $numbers_list;
				$count = count( $numbers_list );
				if ( !$buyer_sms_data['number'] || empty($buyer_sms_data['number']) || $count<1 ) {
					wp_send_json_error( array('message' => 'شماره اي براي دريافت وجود ندارد') );
				}
				$buyer_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $buyer_sms_data );
				if( $buyer_response ) {
					wp_send_json_success( array('message' => sprintf('پيامک به %s شماره موبايل ارسال شد' , $count) ) );
				} else { 
					wp_send_json_error( array('message' => 'پيامک ارسال نشد. خطايي رخ داده است' ) );
				}
			}
		}
		
    }
}	