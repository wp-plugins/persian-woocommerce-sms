<?php
/*
Plugin Name: ارسال پیامک ووکامرس
Version: 2.0.3
Plugin URI: http://www.woocommerce.ir/plugins.html
Description: این افزونه شما را قادر می سازد تا براحتی قابلیت ارسال پیامک را در سیستم ووکامرس پارسی فراهم کنید. تمامی حقوق این افزونه متعلق به تیم ووکامرس پارسی می باشد و هر گونه کپی برداری ،  فروش آن غیر مجاز می باشد.
Author URI: http://www.woocommerce.ir/
Author: ووکامرس فارسی
*/

if ( !defined( 'ABSPATH' ) ) exit;

define( 'PLUGIN_LIB_PATH', dirname(__FILE__). '/lib' );

require_once PLUGIN_LIB_PATH. '/class.settings-api.php';


function sat_sms_autoload( $class ) {

    if ( stripos( $class, 'Woocommerceir_' ) !== false ) {

        $class_name = str_replace( array('Woocommerceir_', '_'), array('', '-'), $class );
        $filename = dirname( __FILE__ ) . '/classes/' . strtolower( $class_name ) . '.php';

        if ( file_exists( $filename ) ) {
            require_once $filename;
        }
    }
}

spl_autoload_register( 'sat_sms_autoload' );


function persianwoosms_get_option( $option, $section, $default = '' ) {

    $options = get_option( $section );

    if ( isset( $options[$option] ) ) {
        return $options[$option];
    }

    return $default;
}


class Sat_WC_Order_SMS {


    public function __construct() {

       
        $this->instantiate();

        add_action( 'init', array( $this, 'localization_setup' ) );
        add_action( 'admin_init', array( $this, 'send_sms_to_any_receiver' ), 11 );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        if( persianwoosms_get_option( 'enable_notification', 'persianwoosms_general', 'off' ) == 'off' ) {
            return;
        }
        
        add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'add_buyer_notification_field' ) );
        add_action( 'woocommerce_checkout_process', array( $this, 'add_buyer_notification_field_process' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'buyer_notification_update_order_meta' ) );
        add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'buyer_sms_notify_display_admin_order_meta' ) , 10, 1 );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box_order_page' ) ); 
        add_action( 'wp_ajax_persianwoosms_send_sms_to_buyer', array( $this, 'send_sms_from_order_page' ) );
        add_action( 'woocommerce_order_status_changed', array( $this, 'trigger_after_order_place' ), 10, 3 );

    }


    function instantiate() {
        new Woocommerceir_Setting_Options();
        new Woocommerceir_SMS_Gateways();
    }


    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Sat_WC_Order_SMS();
        }

        return $instance;
    }




    public function enqueue_scripts() {

        // wp_enqueue_style( 'persianwoosms-styles', plugins_url( 'css/style.css', __FILE__ ), false, date( 'Ymd' ) );
       // wp_enqueue_script( 'persianwoosms-scripts', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), false, true );
    }
     
    public function admin_enqueue_scripts() {

        wp_enqueue_style( 'admin-persianwoosms-styles', plugins_url( 'css/admin.css', __FILE__ ), false, date( 'Ymd' ) );
        wp_enqueue_script( 'admin-persianwoosms-scripts', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), false, true );

        wp_localize_script( 'admin-persianwoosms-scripts', 'persianwoosms', array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ) );   
    }


    function add_buyer_notification_field() {

        if( persianwoosms_get_option( 'buyer_notification', 'persianwoosms_general', 'off' ) == 'off' ) {
            return;
        }

        $required = ( persianwoosms_get_option( 'force_buyer_notification', 'persianwoosms_general', 'no' ) == 'yes' ) ? true : false;
        $checkbox_text = persianwoosms_get_option( 'buyer_notification_text', 'persianwoosms_general', 'مرا با ارسال پیامک از وضعیت سفارش آگاه کن' );
        woocommerce_form_field( 'buyer_sms_notify', array(
            'type'          => 'checkbox',
            'class'         => array('buyer-sms-notify form-row-wide'),
            'label'         => __( $checkbox_text, 'persianwoosms' ),
            'required'      => $required,
        ), 0);
    }


    function add_buyer_notification_field_process() {
        
        if( persianwoosms_get_option( 'force_buyer_notification', 'persianwoosms_general', 'no' ) == 'no' ) {
            return;
        }
        
        if ( ! $_POST['buyer_sms_notify'] ) {
                wc_add_notice( __( 'گزینه ارسال پیامک الزامی است' ), 'error' );
        }
    }


    function buyer_sms_notify_display_admin_order_meta( $order ) {
        $want_notification =  get_post_meta( $order->id, '_buyer_sms_notify', true );
        $display_info = (  isset( $want_notification ) && !empty( $want_notification ) ) ? 'بله' : 'خیر'; 
        echo '<p>مشتری می خواهد پیامک دریافت کند؟ ' . $display_info . '</p>';
    }


    function buyer_notification_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['buyer_sms_notify'] ) ) {
            update_post_meta( $order_id, '_buyer_sms_notify', sanitize_text_field( $_POST['buyer_sms_notify'] ) );
        }
    }

    public  function trigger_after_order_place( $order_id, $old_status, $new_status ) {
        
        $order = new WC_Order( $order_id );

        if( !$order_id ) {
            return;
        }

        $admin_sms_data = $buyer_sms_data = array();

        $default_admin_sms_body = 'یک سفارش جدید ثبت شده است.سفارش [order_id] هم اکنون در حالت [order_status] است';
        $default_buyer_sms_body = 'از سفارش شما سپاسگذاریم.سفارش [order_id] هم اکنون در وضعیت [order_status] می باشد.با احترام';
        $order_status_settings  = persianwoosms_get_option( 'order_status', 'persianwoosms_general', array() );
        $admin_phone_number     = persianwoosms_get_option( 'sms_admin_phone', 'persianwoosms_message', '' );
        $admin_sms_body         = persianwoosms_get_option( 'admin_sms_body', 'persianwoosms_message', $default_admin_sms_body ); 
        $buyer_sms_body         = persianwoosms_get_option( 'sms_body', 'persianwoosms_message', $default_buyer_sms_body ); 
        $active_gateway         = persianwoosms_get_option( 'sms_gateway', 'persianwoosms_gateway', '' );
        $want_to_notify_buyer   = get_post_meta( $order_id, '_buyer_sms_notify', true ); 
        $order_amount           = get_post_meta( $order_id, '_order_total', true );
        $product_list           = $this->get_product_list( $order );
        
        if( count( $order_status_settings ) < 0 || empty( $active_gateway ) ) {
            return;
        }  

        if( in_array( $new_status, $order_status_settings ) ) { 

            if( $want_to_notify_buyer ) {
                if(  persianwoosms_get_option( 'admin_notification', 'persianwoosms_general', 'on' ) == 'on' ) {
                    $admin_sms_data['number']   = $admin_phone_number;     
                    $admin_sms_data['sms_body'] = $this->pharse_sms_body( $admin_sms_body, $new_status, $order_id, $order_amount, $product_list );
                    $admin_response             = Woocommerceir_SMS_Gateways::init()->$active_gateway( $admin_sms_data );
                    
                    if( $admin_response ) {
                        $order->add_order_note('پیامک با موفقیت ارسال گردید');
                    } else {
                        $order->add_order_note('پیامک ارسال نشد. خطایی رخ داده است');
                    }
                }

                $buyer_sms_data['number']   = get_post_meta( $order_id, '_billing_phone', true );
                $buyer_sms_data['sms_body'] = $this->pharse_sms_body( $buyer_sms_body, $new_status, $order_id, $order_amount, $product_list );
                $buyer_response             = Woocommerceir_SMS_Gateways::init()->$active_gateway( $buyer_sms_data );

                if( $buyer_response ) {
                    $order->add_order_note('پیامک با موفقیت برای مشتری ارسال گردید');
                } else {
                    $order->add_order_note( 'پیامک به مشتری ارسال نشد. خطایی رخ داده است' );
                }  

            } else {

                if(  persianwoosms_get_option( 'admin_notification', 'persianwoosms_general', 'on' ) == 'on' ) {
                    $admin_sms_data['number']   = $admin_phone_number;     
                    $admin_sms_data['sms_body'] = $this->pharse_sms_body( $admin_sms_body, $new_status, $order_id, $order_amount, $product_list );
                    $admin_response             = Woocommerceir_SMS_Gateways::init()->$active_gateway( $admin_sms_data );

                    if( $admin_response ) {
                        $order->add_order_note('پیامک با موفقیت ارسال گردید.');
                    } else {
                        $order->add_order_note('پیامک ارسال نشد. خطایی رخ داده است');
                    }
                }
            }         
        }
    }


    public function pharse_sms_body( $content, $order_status, $order_id, $order_amount, $product_list ) {

        $order = $order_id;
        $order_total = $order_amount. ' '. get_post_meta( $order_id, '_order_currency', true );
        $find = array(
            '[order_id]',
            '[order_status]',
            '[order_amount]',
            '[order_items]'
        );
        $replace = array(
            $order,
            $order_status,
            $order_total,
            $product_list
        );

        $body = str_replace( $find, $replace, $content );
        
        return $body;
    }


    public function add_meta_box_order_page( $post_type ) {
        if( $post_type == 'shop_order' ) {
            add_meta_box( 'send_sms_to_buyer', 'ارسال پیامک به مشتری', array( $this, 'render_meta_box_content' ), 'shop_order', 'side', 'high' );
        }
    }


    public function render_meta_box_content( $post ) {
        ?>
        <div class="persianwoosms_send_sms" style="position:relative">
            <div class="persianwoosms_send_sms_result"></div>
            <h4>ارسال پیامک دلخواه به مشتری</h4>
            <p>تمامی پیامک های ارسال شده از طرف شما به شما ره <code><?php echo get_post_meta( $post->ID, '_billing_phone', 'true' ) ?></code> ارسال می گردد.</p>
            <p>
                <textarea rows="5" cols="20" class="input-text" id="persianwoosms_sms_to_buyer" name="persianwoosms_sms_to_buyer" style="width: 246px; height: 78px;"></textarea>
            </p>
            <p> 
                <?php wp_nonce_field('persianwoosms_send_sms_action','persianwoosms_send_sms_nonce'); ?>
                <input type="hidden" name="order_id" value="<?php echo $post->ID; ?>">
                <input type="submit" class="button" name="persianwoosms_send_sms" id="persianwoosms_send_sms_button" value="ارسال پیامک">
            </p>
            <div id="persianwoosms_send_sms_overlay_block"><img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__ ); ?>" alt=""></div>
        </div>

        <?php
    }


    function send_sms_from_order_page() {     
        $active_gateway = persianwoosms_get_option( 'sms_gateway', 'persianwoosms_gateway', '' );

        if( empty( $active_gateway ) ) {
            wp_send_json_error( array('message' => 'درگاه پیامک تنظیم نشده است') );
        }

        $buyer_sms_data['number']   = get_post_meta( $_POST['order_id'], '_billing_phone', true );
        $buyer_sms_data['sms_body'] = $_POST['textareavalue'];
        
        $buyer_response = Woocommerceir_SMS_Gateways::init()->$active_gateway( $buyer_sms_data );
        if( $buyer_response ) {
            wp_send_json_success( array('message' => 'پیامک با موفقیت ارسال شد') );
        } else {
            wp_send_json_error( array('message' => 'پیامک ارسال نشد. خطایی رخ داده است') );
        }  
    }


    function get_product_list( $order ) {
        
        $product_list = '';
        $order_item = $order->get_items();

        foreach( $order_item as $product ) {
            $prodct_name[] = $product['name']; 
        }

        $product_list = implode( ',', $prodct_name );

        return $product_list;
    }


    function send_sms_to_any_receiver() {
        if( isset( $_POST['persianwoosms_send_sms'] ) && wp_verify_nonce( $_POST['send_sms_to_any_nonce'], 'send_sms_to_any_action' ) ) {
            if( isset( $_POST['persianwoosms_receiver_number'] ) && empty( $_POST['persianwoosms_receiver_number'] ) ) {
                wp_redirect( add_query_arg( array( 'page'=> 'persian-woocommerce-sms-pro-send-sms', 'message' => 'error' ), admin_url( 'admin.php' ) ) );
            } else {
                $active_gateway = persianwoosms_get_option( 'sms_gateway', 'persianwoosms_gateway', '' );

                if( empty( $active_gateway ) || $active_gateway == 'none' ) {
                   
                    wp_redirect( add_query_arg( array( 'page'=> 'persian-woocommerce-sms-pro-send-sms', 'message' => 'gateway_problem' ), admin_url( 'admin.php' ) ) );    
                
                } else {

                    $receiver_sms_data['number']   = $_POST['persianwoosms_receiver_number'];
                    $receiver_sms_data['sms_body'] = $_POST['persianwoosms_sms_body'];
                    
                    $receiver_response = Woocommerceir_SMS_Gateways::init()->$active_gateway( $receiver_sms_data );

                    if( $receiver_response ) {
                        wp_redirect( add_query_arg( array( 'page'=> 'persian-woocommerce-sms-pro-send-sms', 'message' => 'success' ), admin_url( 'admin.php' ) ) );  
                    } else {
                        wp_redirect( add_query_arg( array( 'page'=> 'persian-woocommerce-sms-pro-send-sms', 'message' => 'sending_failed' ), admin_url( 'admin.php' ) ) );     
                    }
                }
            }
        }
    }

} 

add_action( 'plugins_loaded', 'load_sat_wc_order_sms' );

function load_sat_wc_order_sms() {
    $persianwoosms = Sat_WC_Order_SMS::init();
}