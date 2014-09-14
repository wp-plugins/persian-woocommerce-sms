<?php

/**
 * Persian Woocommerce SMS pro
 *
 * @author Mohammad Majidi
 */

class Woocommerceir_Setting_Options {

    private $settings_api;

    function __construct() {

        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') ); 
    }

	
    function admin_init() {


	
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );


		
        $this->settings_api->admin_init();
    }

	
    function admin_menu() {
        add_menu_page( 'تنظیمات پیامک','پیامک ووکامرس', 'manage_options', 'persian-woocommerce-sms-pro', array( $this, 'plugin_page' ), 'dashicons-email-alt' );
        add_submenu_page( 'persian-woocommerce-sms-pro', 'ارسال پیامک', 'ارسال پیامک', 'manage_options', 'persian-woocommerce-sms-pro-send-sms', array( $this, 'send_sms_to_any' ) );
    }


	
    function send_sms_to_any() {
        ?>
        <div class="wrap">
            <h4>ارسال پیامک به یک شماره</h4>
            <div class="postbox send_sms_to_any_notice"> 
                <p>قبل از ارسال پیامک ، لطفا تنظیمات آن را انجام دهید</p>
            </div>
            <?php if( isset( $_GET['message'] ) && $_GET['message'] == 'error' ): ?>
                <div class="error">
                    <p><strong>خطا:</strong> وارد کردن شماره دریافت کننده الزامی است!</p>
                </div>
            <?php endif; ?>

            <?php if( isset( $_GET['message'] ) && $_GET['message'] == 'gateway_problem' ): ?>
                <div class="error">
                    <p><strong>خطا:</strong> تنظیمات درگاه پیامک انجام نشده است</p>
                </div>
            <?php endif; ?>

            <?php if( isset( $_GET['message'] ) && $_GET['message'] == 'sending_failed' ): ?>
                <div class="error">
                    <p><strong>خطا:</strong> ارسال پیامک با مشکل مواجه گردید. لطفا شماره دریافت کننده یا تنظیمات سیستم پیامک را بررسی کنید</p>
                </div>
            <?php endif; ?>

            <?php if( isset( $_GET['message'] ) && $_GET['message'] == 'success' ): ?>
                <div class="updated">
                    <p>پیامک با موفقیت به دریافت کننده ارسال گردید!</p>
                </div>
            <?php endif; ?>

            <div class="postbox " id="persianwoosms_send_sms_any">
                <h3 class="hndle">ارسال پیامک</h3>
                <div class="inside">
                    <form class="initial-form" id="persianwoosms-send-sms-any-form" method="post" action="" name="post">
                        <p>
                            <label for="persianwoosms_receiver_number">شماره دریافت کننده</label><br>
                            <input type="text" name="persianwoosms_receiver_number" id="persianwoosms_receiver_number">
                            <span>شماره موبایل دریافت کننده پیامک را وارد کنید</span>
                        </p>

                        <p>
                            <label for="persianwoosms_sms_body">متن پیامک</label><br>
                            <textarea name="persianwoosms_sms_body" id="persianwoosms_sms_body" cols="50" rows="6"></textarea>
                            <span>متن دلخواهی که میخواهید به دریافت کننده ارسال کنید را وارد کنید</span>
                        </p>

                        <p>
                            <?php wp_nonce_field( 'send_sms_to_any_action','send_sms_to_any_nonce' ); ?>
                            <input type="submit" class="button button-primary" name="persianwoosms_send_sms" value="ارسال پیامک">
                        </p>         

                    </form>
                </div>
            </div>
           
        </div>
        <?php
    }

	
    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'persianwoosms_general',
                'title' => 'تنظیمات عمومی'
            ),
            array(
                'id' => 'persianwoosms_gateway',
                'title' => 'تنظیمات درگاه پیامک'
            ),

            array(
                'id' => 'persianwoosms_message',
                'title' => 'تنظیمات پیامک'
            )
        );
        return apply_filters( 'persianwoosms_settings_sections' , $sections );
    }


    function get_settings_fields() {

        
        $buyer_message = "از سفارش شما سپاسگذاریم\nسفارش [order_id] هم اکنون در وضعیت [order_status] می باشد\nبا احترام"; 
        $admin_message = "یک سفارش جدید ثبت شده است\nسفارش [order_id] هم اکنون در حالت [order_status] است\n";    
        $settings_fields = array(

            'persianwoosms_general' => apply_filters( 'persianwoosms_general_settings', array(
                array(
                    'name' => 'enable_notification',
                    'label' => 'فعال سازی ارسال پیامک',
                    'desc' => 'در صورت انتخاب این گزینه ، در هنگام ثبت سفارش جدید پیامک ارسال می گردد',
                    'type' => 'checkbox',
                ),

                array(
                    'name' => 'admin_notification',
                    'label' => 'ارسال پیامک به مدیر',
                    'desc' => 'با انتخاب این گزینه ، در هنگام ثبت سفارش جدید ، برای مدیر پیامک ارسال می گردد',
                    'type' => 'checkbox',
                    'default' => 'on'
                ),

                array(
                    'name' => 'buyer_notification',
                    'label' => 'ارسال پیامک به مشتری',
                    'desc' => 'با انتخاب این گزینه ، در هنگام ثبت سفارش جدید ، برای مشتری پیامک ارسال می گردد',
                    'type' => 'checkbox',
                ),

                array(
                    'name' => 'force_buyer_notification',
                    'label' => 'الزامی بودن گزینه دریافت پیامک',
                    'desc' => 'با فعال سازی این گزینه ، کاربر می بایست تیک گزینه "میخواهم از وضعیت سفارش از طریق پیامک آگاه شوم" را انتخاب کند',
                    'type' => 'select',
                    'default' => 'no',
                    'options' => array(
                        'yes' => 'بله',
                        'no'   => 'خیر'
                    )
                ),

                array(
                    'name' => 'buyer_notification_text',
                    'label' => 'متن قابل نمایش به مشتری',
                    'desc' => 'متن قابل نمایش به مشتری برای گزینه دریافت پیامک',
                    'type' => 'textarea',
                    'default' => 'میخواهم از وضعیت سفارش از طریق پیامک آگاه شوم.'
                ),
                array(
                    'name' => 'order_status',
                    'label' => 'ارسال پیامک در وضعیت سفارش',
                    'desc' => 'می توانید مشخص کنید سفارشات در چه وضعیتی می توانند پیامک دریافت کنند.',
                    'type' => 'multicheck',
                    'options' => array(
                        'on-hold' => 'در انتظار',
                        'pending'  => 'معلق',
                        'processing'  => 'در حال انجام',
                        'completed'  => 'تکمیل شده',
                    )
                )
            ) ),

            'persianwoosms_gateway' => apply_filters( 'persianwoosms_gateway_settings',  array(
                array(
                    'name' => 'sms_gateway',
                    'label' => 'انتخاب درگاه پیامک',
                    'desc' => 'درگاه پیامک (سرویس دهنده) خود را انتخاب کنید',
                    'type' => 'select',
                    'default' => '-1',
                    'options' => $this->get_sms_gateway()
                ),
            ) ),

            'persianwoosms_message' => apply_filters( 'persianwoosms_message_settings',  array(
                array(
                    'name' => 'sms_admin_phone',
                    'label' => 'شماره موبایل مدیر را وارد کنید',
                    'desc' => '<br/>شماره موبایل مدیر را برای دریافت پیامک وارد نمایید.',
                    'type' => 'text'
                ),
                array(
                    'name' => 'admin_sms_body',
                    'label' => 'متن پیامک به مدیر',
                    'desc' => 'شما می توانید متنی دلخواه برای ارسال پیامک به مدیر وارد کنید. همچنین می توانید از کد میانبر <code>[order_id]</code> برای نمایش شماره سفارش ، از کد <code>[order_status]</code> برای نمایش وضعیت سفارش ، از کد <code>[order_items]</code> برای دریافت تعداد سفارش ها و از کد <code>[order_amount]</code> برای نمایش مبلغ سفارش استفاده کنید.',
                    'type' => 'textarea',
                    'default' => $admin_message
                ),

                array(
                    'name' => 'sms_body',
                    'label' =>'متن پیامک به مشتری',
                    'desc' => 'شما می توانید متنی دلخواه برای ارسال پیامک به مشتری وارد کنید. همچنین می توانید از کد میانبر <code>[order_id]</code> برای نمایش شماره سفارش ، از کد <code>[order_status]</code> برای نمایش وضعیت سفارش ، از کد <code>[order_items]</code> برای دریافت تعداد سفارش ها و از کد <code>[order_amount]</code> برای نمایش مبلغ سفارش استفاده کنید.',
                    'type' => 'textarea',
                    'default' => $buyer_message
                ),
            ) ),
        );

        return apply_filters( 'persianwoosms_settings_
            section_content', $settings_fields );
    }


	
    function plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }


	
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

	
    function get_sms_gateway() {
        $gateway = array( 
            'none'      => 'انتخاب کنید',
            'panizsms' => 'پانیز پیامک',
            'parandsms' => 'پرند پیامک',
            'gamapayamak' => 'گاما پیامک',
            'limoosms' => 'لیمو اس ام اس',
            'maxsms' => 'مکس اس ام اس'
        );

        return apply_filters( 'persianwoosms_sms_gateway', $gateway );
    }

} 

function persianwoosms_settings_field_gateway() {

    $persian_woo_sms_username	= persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway', '' ); 
    $persian_woo_sms_password	= persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway', '' );
    $persian_woo_sms_sender		= persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway', '' ); 

    ?>
    
    <?php do_action( 'persianwoosms_gateway_settings_options_before' ); ?>

    

    <div class="smsglobal_wrapper">
        <hr>
        <p style="margin-top:15px; margin-bottom:0px; padding-right: 20px; font-size: 11px;">
           ووکامرس پارسی ، هیچ مسئولیتی در قبال هر یک از پنل های پیامک ندارد. تمامی مسئولیت های هزینه ها ، پاسخگویی و پشتیبانی بر عهده ارائه دهنده خدمات پیامک می باشد
       </p>
        <table class="form-table">
            <tr valign="top">
                <th scrope="row">نام کاربری پنل پیامک</th>
                <td>
                    <input type="text" name="persianwoosms_gateway[persian_woo_sms_username]" id="persianwoosms_gateway[persian_woo_sms_username]" value="<?php echo $persian_woo_sms_username; ?>">
                    <span>نام کاربری پنل پیامک خود را وارد کنید</span>
                </td>
            </tr>

            <tr valign="top">
                <th scrope="row">رمز عبور پنل پیامک</th>
                <td>
                    <input type="text" name="persianwoosms_gateway[persian_woo_sms_password]" id="persianwoosms_gateway[persian_woo_sms_password]" value="<?php echo $persian_woo_sms_password; ?>">
                    <span>رمز عبور پنل پیامک خود را وارد کنید</span> 
                </td>
            </tr>
            <tr valign="top">
                <th scrope="row">شماره ارسال کننده پیامک</th>
                <td>
                    <input type="text" name="persianwoosms_gateway[persian_woo_sms_sender]" id="persianwoosms_gateway[persian_woo_sms_sender]" value="<?php echo $persian_woo_sms_sender; ?>">
                    <span>شماره ارسال کننده پیامک را وارد کنید</span>
                </td>
            </tr>
        </table>
    </div>
    <?php do_action( 'persianwoosms_gateway_settings_options_after' ) ?>
    <?php
}


add_action( 'wsa_form_bottom_persianwoosms_gateway', 'persianwoosms_settings_field_gateway' );

