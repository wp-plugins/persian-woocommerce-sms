<?php
class WoocommerceIR_Order_SMS {
    public function __construct() {
		
		add_action(	'woocommerce_after_order_notes', array( $this, 'add_sms_field_in_checkout') );
        add_action( 'woocommerce_checkout_process', array( $this, 'add_sms_field_in_checkout_process' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_sms_field_in_order_meta' ) );
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'show_sms_field_in_admin_order_meta' ) , 10, 1 );
        add_action( 'woocommerce_order_status_changed', array( $this, 'send_sms_when_order_status_changed' ), 10, 3 );
		add_filter(	'woocommerce_form_field_persian_woo_sms_multiselect',    'add_multi_select_checkbox_to_checkout_ps_sms' , 11, 4 );
		add_filter( 'woocommerce_form_field_persian_woo_sms_multicheckbox',  'add_multi_select_checkbox_to_checkout_ps_sms' , 11, 4 );
		
		if ( ps_sms_options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' )
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts_css_frontend' ) );
		
	}
	
	public function scripts_css_frontend() {
		wp_register_script( 'persian-woo-sms-frontend', PS_WOO_SMS_PLUGIN_PATH.'/assets/js/status_selector_front_end.js', array( 'jquery' ), PS_WOO_SMS_VERSION, true );
		wp_localize_script( 'persian-woo-sms-frontend', 'persian_woo_sms', 
			array(
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'chosen_placeholder_single' => __( 'گزینه مورد نظر را انتخاب نمایید', 'persianwoosms' ),
				'chosen_placeholder_multi'  => __( 'گزینه های مورد نظر را انتخاب نمایید', 'persianwoosms' ),
				'chosen_no_results_text'    => __( 'هیچ گزینه ای وجود ندارد .', 'persianwoosms' ),
			)
		);
		wp_enqueue_script( 'persian-woo-sms-frontend' );
		if ( ps_sms_options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' ) {
			wc_enqueue_js( "
				jQuery( '#buyer_sms_status_field' ).hide();
				jQuery( 'input[name=buyer_sms_notify]' ).change( function () {
					if ( jQuery( this ).is( ':checked' ) )
						jQuery( '#buyer_sms_status_field' ).show();
					else
						jQuery( '#buyer_sms_status_field' ).hide();
				} ).change();
			" );
		}
    }
	
	function add_sms_field_in_checkout( $checkout ) {
		if( ps_sms_options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'off' || count( (array) get_allowed_woo_status_ps_sms() ) < 0 )
            return;
		echo '<div id="add_sms_field_in_checkout">';
		$checkbox_text = ps_sms_options( 'buyer_checkbox_text', 'sms_buyer_settings', 'مرا با ارسال پیامک از وضعیت سفارش آگاه کن' );
		$required = ( ps_sms_options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' ) ? true : false;
		if ( !$required ) {
			woocommerce_form_field( 'buyer_sms_notify', 
				array(
					'type'          => 'checkbox',
					'class'         => array('buyer-sms-notify form-row-wide'),
					'label'         => __( $checkbox_text, 'persianwoosms' ) ? __( $checkbox_text, 'persianwoosms' ) : '',
					'label_class' => '',
					'required'      => $required,
				), $checkout->get_value( 'buyer_sms_notify' )
			);
		}
		
		if ( ps_sms_options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' ) {
			$multiselect_text = ps_sms_options( 'buyer_select_status_text_top', 'sms_buyer_settings', '' );
			$multiselect_text_bellow = ps_sms_options( 'buyer_select_status_text_bellow', 'sms_buyer_settings', '' );
			$required = ( ps_sms_options( 'force_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' ) ? true : false;
			$mode = ( ps_sms_options( 'buyer_status_mode', 'sms_buyer_settings', 'selector' ) == 'selector' ) ? 'persian_woo_sms_multiselect' : 'persian_woo_sms_multicheckbox' ;
			woocommerce_form_field( 'buyer_sms_status', array(
				'type'          => $mode ? $mode : '',
				'class'         => array('buyer-sms-status form-row-wide wc-enhanced-select'),
				'label'         => $multiselect_text ? __( $multiselect_text, 'persianwoosms' ) : '',
				'options'       => get_allowed_woo_status_ps_sms(),
				'required'      => $required,
				'description' 	=>  $multiselect_text_bellow ? __( $multiselect_text_bellow, 'persianwoosms' ) : '',
				), $checkout->get_value( 'buyer_sms_status' )
			);
		}
		echo '</div>';
	}
	
	
	function add_sms_field_in_checkout_process() {
		
		if( ps_sms_options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'off' || count( (array) get_allowed_woo_status_ps_sms() ) < 0 )
            return;
		
		if( ps_sms_options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' && ! empty($_POST['buyer_sms_notify']) && ! $_POST['billing_phone'] )
			wc_add_notice( __( 'برای دریافت پیامک می بایست فیلد شماره تلفن را پر نمایید .' ), 'error' );
		
		if( ps_sms_options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes'
		&& ps_sms_options( 'force_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes'
		&& ( ( ps_sms_options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) != 'yes' && ! empty($_POST['buyer_sms_notify']) ) || ps_sms_options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' )
		&& ! $_POST['buyer_sms_status'] )
			wc_add_notice( __( 'انتخاب حداقل یکی از وضعیت ها الزامی است .' ), 'error' );
		
    }

    function save_sms_field_in_order_meta( $order_id ) {
		if( ps_sms_options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'off' || count( (array) get_allowed_woo_status_ps_sms() ) < 0 )
            return;
		
        if ( ! empty( $_POST['buyer_sms_notify'] ) )
            update_post_meta( $order_id, '_buyer_sms_notify', sanitize_text_field( $_POST['buyer_sms_notify'] ) );
		
		if ( ps_sms_options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' )
            update_post_meta( $order_id, '_buyer_sms_notify', '1' );
		
		if ( ! empty( $_POST['buyer_sms_status'] ) )
            update_post_meta( $order_id, '_buyer_sms_status', $_POST['buyer_sms_status'] );
		
    }
			
    function show_sms_field_in_admin_order_meta( $order ) {
        if( ps_sms_options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'off' || count( (array) get_allowed_woo_status_ps_sms() ) < 0 )
            return;
		$want_notification =  get_post_meta( $order->id, '_buyer_sms_notify', true );
        $display_info = (  isset( $want_notification ) && !empty( $want_notification ) ) ? 'بله' : 'خیر'; 
		
		if ( ps_sms_options( 'force_enable_buyer', 'sms_buyer_settings', 'no' ) == 'yes' )
			echo '<p>مشتری باید پیامک دریافت کند .</p>';	
		else 
			echo '<p>مشتری می خواهد پیامک دریافت کند؟ ' . $display_info . '</p>';
			
		if ( ps_sms_options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' ) {	
			$buyer_sms_status =  get_post_meta( $order->id, '_buyer_sms_status', true );
			$display_statuses = (  isset( $buyer_sms_status ) && !empty( $buyer_sms_status ) ) ? $buyer_sms_status : array(); 
			
			echo '<p>وضعیت های انتخابی برای دریافت پیامک توسط مشتری : ';
			if ( count($display_statuses) >=0 &&  !empty($display_statuses) ) {
				$statuses = '';
				foreach ( (array) $display_statuses as $status )
					$statuses .= wc_get_order_status_name($status).' - ';
				echo substr( $statuses , 0 , -3 );
			}
			else
				echo 'وضعیتی انتخاب نشده است';
			
		}
		else {
			echo '<p>وضعیت های اجباری برای دریافت پیامک توسط مشتری : ';
			echo 'تمام وضعیت های مجاز';
			echo '</p>';
		}
    }

    public function send_sms_when_order_status_changed( $order_id, $old_status, $new_status ) {
        
		if( !$order_id )
            return;
		
		$active_gateway = ps_sms_options( 'sms_gateway', 'sms_main_settings', '' );
        if( empty( $active_gateway ) ) {
            return;
        }
		
        $order = new WC_Order( $order_id );
		$admin_sms_data = $buyer_sms_data = array();

        $product_list	= get_product_list_ps_sms( $order );
		$all_items	= $product_list['names'];
		
		 
		$order_status_settings  = ps_sms_options( 'order_status', 'sms_buyer_settings', array() );
		$buyer_sms_status =  get_post_meta( $order_id, '_buyer_sms_status', true ) ? get_post_meta( $order_id, '_buyer_sms_status', true ) : array();
		
		
        if( in_array( $new_status, $order_status_settings ) && count( $order_status_settings ) > 0 && count( (array) get_allowed_woo_status_ps_sms() ) > 0 ) {
			
			if( ( ps_sms_options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'on' ) && get_post_meta( $order_id, '_buyer_sms_notify', true ) && strlen( get_post_meta( $order_id, '_billing_phone', true )) > 5 ) {
					
				if ( ( ps_sms_options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'no' )
					|| ( ps_sms_options( 'allow_buyer_select_status', 'sms_buyer_settings', 'no' ) == 'yes' && in_array( $new_status, $buyer_sms_status ) )  ) {
					
					
					$buyer_sms_data['number']   = explode( ',', get_post_meta( $order_id, '_billing_phone', true ) );
					
					$buyer_sms_body = ps_sms_options( 'sms_body', 'sms_buyer_settings', '' );
					$buyer_sms_data['sms_body'] = str_replace_tags_order( $buyer_sms_body, $new_status, $order_id, $order , $all_items , '' );
					
					$buyer_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $buyer_sms_data );
					if( $buyer_response ) {
						$order->add_order_note( sprintf('پیامک با موفقیت به مشتری با شماره %s ارسال گردید' , get_post_meta( $order_id, '_billing_phone', true )));
					} else {
						$order->add_order_note( sprintf('پیامک بخاطر خطا به مشتری با شماره %s ارسال نشد' , get_post_meta( $order_id, '_billing_phone', true )) );
					}
				}   
			}
		}
		
		if( ps_sms_options( 'enable_super_admin_sms', 'sms_admin_settings', 'on' ) == 'on' &&  strlen(ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' )) > 5 ) {
			
			$super_admin_order_status = ps_sms_options( 'super_admin_order_status', 'sms_admin_settings', array() );
			if ( in_array( $new_status, $super_admin_order_status ) ) {
			
				$super_admin_sms_data['number']   = explode( ',', ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' ) );
				
				$super_admin_sms_body         = ps_sms_options( 'super_admin_sms_body', 'sms_admin_settings', '' );
				$super_admin_sms_data['sms_body'] = str_replace_tags_order( $super_admin_sms_body, $new_status, $order_id, $order, $all_items , '' );
				
				$super_admin_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $super_admin_sms_data );
				if( $super_admin_response ) {
					$order->add_order_note( sprintf('پیامک با موفقیت به مدیر کل با شماره %s ارسال گردید' , ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' )));
				} else {
					$order->add_order_note( sprintf('پیامک بخاطر خطا به مدیر کل با شماره %s ارسال نشد' , ps_sms_options( 'super_admin_phone', 'sms_admin_settings', '' )));
				}
				
				/*
				if ( ps_sms_options( 'enable_super_admin_telegram', 'sms_admin_settings', 'on' ) == 'on' ) {
					
					$api_key = ps_sms_options( 'super_admin_telegram_API_Key', 'sms_admin_settings', '' );
					$api_token = ps_sms_options( 'super_admin_telegram_API_Token', 'sms_admin_settings', '' );

					$project_name = get_bloginfo( 'name' );
					$message = $super_admin_sms_data['sms_body'];
					
					if ( ! class_exists( 'WoocommerceIR_Notifygram' ) ) {
						require_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.notifygram.php';
						$telegram = new WoocommerceIR_Notifygram();
					}
					
					$telegram->Notifygram($api_key, $api_token, $project_name);
					$telegram->notify($message);
					
				}
				*/
				
			}
		}
		
		if( ps_sms_options( 'enable_product_admin_sms', 'sms_admin_settings', 'on' ) == 'on' ) {
		
			$product_ids = $product_list['ids'];
			$product_ids = explode( ',' , $product_ids);
			
			unset($admin_number_lists);
			$admin_number_lists = array();
			
			foreach ( (array) $product_ids as $product_id ) {
				$admin_datas = maybe_unserialize( get_post_meta( $product_id, '_hannanstd_woo_products_tabs', true ) );
				foreach ( (array) $admin_datas as $admin_data ) {
					$admin_statuses = explode( '-sv-' , $admin_data['content']);
					if( in_array( $new_status, $admin_statuses ) ) {
						if ( empty($admin_number_lists[$admin_data['title']]) )
							$admin_number_lists[$admin_data['title']] = get_the_title($product_id);
						else
							$admin_number_lists[$admin_data['title']] = $admin_number_lists[$admin_data['title']].'-'.get_the_title($product_id);
					}
				}
			}
			
			if ( !empty($admin_number_lists) && count($admin_number_lists) > 0 ) {
					
				foreach ( (array) $admin_number_lists as $number => $vendor_items ) {
					if ( strlen( $number ) > 5 ) {
						$admin_sms_data['number']   = explode( ',' , $number);
						
						$product_admin_sms_body         = ps_sms_options( 'product_admin_sms_body', 'sms_admin_settings', '' ); 
						$admin_sms_data['sms_body'] = str_replace_tags_order( $product_admin_sms_body, $new_status, $order_id, $order , $all_items , $vendor_items );
					
						$admin_response = WoocommerceIR_Gateways_SMS::init()->$active_gateway( $admin_sms_data );
						if( $admin_response ) {
							$order->add_order_note( sprintf('پیامک با موفقیت به مدیر محصول با شماره %s ارسال گردید' , $number));
						} else {
							$order->add_order_note( sprintf('پیامک بخاطر خطا به مدیر محصول با شماره %s ارسال نشد' , $number) );
						}
					}
				}
			}
			
		}
		
	
		
    }

}