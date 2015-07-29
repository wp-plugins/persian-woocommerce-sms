<?php
class WoocommerceIR_Settings_SMS {

    private $settings_api;

    function __construct() {
        
		$this->settings_api = new WoocommerceIR_Settings_Fields_SMS;
        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') , 60 );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts_css_admin'));
		add_action( 'admin_init', array($this, 'redirect_to_woo_sms_about_page'));
		add_action( 'ps_woo_sms_form_submit_sms_main_settings',  function() {submit_button();});
		add_action( 'ps_woo_sms_form_submit_sms_buyer_settings',  function() {submit_button();});
		add_action( 'ps_woo_sms_form_submit_sms_admin_settings',  function() {submit_button();});	
		add_action( 'ps_woo_sms_form_submit_sms_notif_settings',  function() {submit_button();});
        add_action( 'admin_init',  array ( 'WoocommerceIR_Bulk_SMS', 'send_sms_to_bulk_receiver') , 11 );
		add_action( 'ps_woo_sms_form_bottom_persianwoosms_send', array ( 'WoocommerceIR_Bulk_SMS', 'send_sms_to_bulk') );
		add_action( 'admin_footer', array ( 'WoocommerceIR_Bulk_SMS', 'bulk_admin_footer_ps_sms'), 10 );
		add_action( 'load-edit.php', array ( 'WoocommerceIR_Bulk_SMS', 'bulk_action_ps_sms')  );
		
		if (class_exists('WoocommerceIR_Gateways_SMS'))
			new WoocommerceIR_Gateways_SMS();
		
        if( ps_sms_options( 'enable_admin_bar', 'sms_main_settings', 'off' ) == 'on' )
			add_action('wp_before_admin_bar_render',  array( $this,'persianwoo_adminbar') );
		
        if( ps_sms_options( 'enable_plugins', 'sms_main_settings', 'off' ) == 'off' )
            return;
		
		if ( ps_sms_options( 'enable_buyer', 'sms_buyer_settings', 'off' ) == 'on' 
		|| ps_sms_options( 'enable_super_admin_sms', 'sms_admin_settings', 'off' ) == 'on' 
		|| ps_sms_options( 'enable_product_admin_sms', 'sms_admin_settings', 'off' ) == 'on' ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.order.php';
			if (class_exists('WoocommerceIR_Order_SMS'))
				new WoocommerceIR_Order_SMS();
		}
		
		if ( ps_sms_options( 'enable_metabox', 'sms_buyer_settings', 'off' ) == 'on' || ps_sms_options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on'  ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.metabox.php';
			if ( class_exists('WoocommerceIR_Metabox_SMS') )
				new WoocommerceIR_Metabox_SMS();
		}
		
		if ( ps_sms_options( 'enable_product_admin_sms', 'sms_admin_settings', 'off' ) == 'on' || ps_sms_options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on'  ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.products.tab.php';
			if ( class_exists('WoocommerceIR_Tab_SMS') )
				new WoocommerceIR_Tab_SMS();
		}
		
		if( ps_sms_options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on'
		|| ps_sms_options( 'enable_super_admin_sms', 'sms_admin_settings', 'off' ) == 'on' 
		|| ps_sms_options( 'enable_product_admin_sms', 'sms_admin_settings', 'off' ) == 'on' ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.notifications.php';
			if (class_exists('WoocommerceIR_Notification_SMS'))
				new WoocommerceIR_Notification_SMS();
		}
		
		if( ps_sms_options( 'enable_notif_sms_main', 'sms_notif_settings', 'off' ) == 'on' ) {
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/class.widget.php';
		}
		
		
    }

    public static function init() {
        static $instance = false;
        return $instance = ( ! $instance ? new WoocommerceIR_Settings_SMS() : $instance );
    }
	 
    public function scripts_css_admin() {
		global $post;
		if ( $post->post_type == 'shop_order' || $post->post_type == 'product' ) {
			wp_enqueue_style( 'admin-persianwoosms-styles', PS_WOO_SMS_PLUGIN_PATH.'/assets/css/admin.css', false, date( 'Ymd' ) );
			wp_enqueue_script( 'admin-persianwoosms-scripts', PS_WOO_SMS_PLUGIN_PATH.'/assets/js/admin.js', array( 'jquery' ), false, true );
			wp_localize_script( 'admin-persianwoosms-scripts', 'persianwoosms', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )) );
		}
		if ( ($post->post_type == 'product') and ( ps_sms_options( 'enable_plugins', 'sms_main_settings', 'off' ) != 'off') ) {
			wp_register_script( 'repeatable-sms-tabs' , PS_WOO_SMS_PLUGIN_PATH.'/assets/js/repeatable-sms-tabs.min.js' , array('jquery') , 'all' );
			wp_enqueue_script( 'repeatable-sms-tabs' );
			wp_register_style( 'repeatable-sms-tabs-styles' , PS_WOO_SMS_PLUGIN_PATH.'/assets/css/repeatable-sms-tabs.min.css' , '' , 'all' );
			wp_enqueue_style( 'repeatable-sms-tabs-styles' );
		}
    }

    function admin_init() {
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        $this->settings_api->admin_init();
    }
	
    function admin_menu() {
        add_submenu_page( 'woocommerce', 'تنظیمات پیامک', 'تنظیمات پیامک', 'manage_woocommerce', 'persian-woocommerce-sms-pro', array( $this, 'setting_page' ) );
		if ( get_option( 'redirect_to_woo_sms_about_page' ) != 'yes' )
			add_submenu_page( 'index.php', 'درباره پیامک ووکامرس', 'پیامک ووکامرس', 'read', 'about-persian-woocommerce-sms-pro', array( $this, 'about_page' ) );
    }
	
	function redirect_to_woo_sms_about_page() {
		if ( get_option( 'redirect_to_woo_sms_about_page_check' ) != 'yes') {
			ob_start();
			if (!headers_sent()) {
				wp_redirect( admin_url( 'index.php?page=about-persian-woocommerce-sms-pro' ) );
			}
			else {
				update_option( 'redirect_to_woo_sms_about_page_check', 'yes' );
				update_option( 'redirect_to_woo_sms_about_page', 'yes' );
			}
		}
		else {
			update_option( 'redirect_to_woo_sms_about_page', 'yes' );
		}
	}

    function about_page() {
		update_option( 'redirect_to_woo_sms_about_page_check', 'yes' );
		include PS_WOO_SMS_PLUGIN_LIB_PATH. '/about.php';
    }
	
    function setting_page() {
        echo '<div class="wrap">';
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();
        echo '</div>';
    }
	
	function persianwoo_adminbar() {
		global $wp_admin_bar;		
		if( current_user_can( 'manage_woocommerce' ) && is_admin_bar_showing()) {
			$wp_admin_bar->add_menu(array(
				'id'		=>	'persianwoo_adminbar_send',
				'title'		=>	'<span class="ab-icon"></span>ارسال پیامک ووکامرس',
				'href'		=>	admin_url( 'admin.php?page=persian-woocommerce-sms-pro&send=true' ),
			));
		}
	}

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'sms_main_settings',
                'title' => 'تنظیمات عمومی'
            ),
            array(
                'id' => 'sms_buyer_settings',
                'title' => 'تنظیمات پیامک مشتری'
            ),
            array(
                'id' => 'sms_admin_settings',
                'title' => 'تنظیمات پیامک مدیر'
            ),
            array(
                'id' => 'sms_notif_settings',
                'title' => 'تنظیمات اطلاع رسانی محصولات'
            ),
            array(
                'id' => 'persianwoosms_send',
                'title' => 'ارسال پیامک به شماره دلخواه'
            )
        );
        return apply_filters( 'persianwoosms_settings_sections' , $sections );
    }

    function get_settings_fields() {
        $settings_fields = array(
		
            'sms_main_settings' => apply_filters( 'sms_main_settings_settings', array(
                array(
                    'name' => 'enable_plugins',
                    'label' => 'فعال سازی کلی افزونه پیامک',
                    'desc' => 'در صورت فعالسازی این گزینه قابلیت ارسال پیامک به ووکامرس اضافه خواهد شد .',
                    'type' => 'checkbox',
                ),
				array(
                    'name' => 'enable_admin_bar',
                    'label' => 'لینک ارسال پیامک در ادمین بار',
                    'desc' => 'در صورت فعالسازی این گزینه لینک ارسال پیامک جهت دسترسی سریع تر به ادمین بار اضافه خواهد شد .',
                    'type' => 'checkbox',
                ),
				array(
                    'name' => 'header_1',
                    'label' => 'تنظیمات درگاه پیامک',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
				array(
                    'name' => 'note',
                    'label' => 'تذکر',
                    'desc' => 'ووکامرس پارسی ، مسئولیتی در قبال هیچ یک از پنل های پیامک ندارد. تمامی مسئولیت های هزینه ها ، پاسخگویی و پشتیبانی بر عهده ارائه دهنده خدمات پیامک می باشد .',
                    'type' => 'html',
                ),
				array(
                    'name' => 'sms_gateway',
                    'label' => 'انتخاب درگاه پیامک',
                    'desc' => 'درگاه پیامک (سرویس دهنده) خود را انتخاب کنید',
                    'type' => 'select',
                    'default' => '-1',
                    'options' => class_exists('WoocommerceIR_Gateways_SMS') ? WoocommerceIR_Gateways_SMS::get_sms_gateway() : array(),
                ),
                array(
                    'name' => 'persian_woo_sms_username',
                    'label' => 'نام کاربری پنل پیامک',
                    'type' => 'text',
                ),
                array(
                    'name' => 'persian_woo_sms_password',
                    'label' => 'کلمه عبور پنل پیامک',
                    'type' => 'text',
                ),
                array(
                    'name' => 'persian_woo_sms_sender',
                    'label' => 'شماره ارسال کننده پیامک',
                    'type' => 'text',
                ),
            ) ),
			
            'sms_buyer_settings' => apply_filters( 'sms_buyer_settings_settings',  array(				
                array(
                    'name' => 'enable_buyer',
                    'label' => 'ارسال پیامک به مشتری',
                    'desc' => 'با انتخاب این گزینه ، در هنگام ثبت و یا تغییر وضعیت سفارش ، برای مشتری پیامک ارسال می گردد .',
                    'type' => 'checkbox',
                ),
				array(
                    'name' => 'enable_metabox',
                    'label' => 'متاباکس ارسال پیامک',
                    'desc' => 'با انتخاب این گزینه ، در صفحه سفارشات متاباکس ارسال پیامک به مشتریان اضافه میشود .',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'force_enable_buyer',
                    'label' => 'اختیاری بودن دریافت پیامک',
                    'desc' => 'فقط در صورت فعال سازی این قسمت ، گزینه "میخواهم از وضعیت سفارش از طریق پیامک آگاه شوم" در صفحه تسویه حساب نمایش داده خواهد شد و در غیر این صورت پیامک همواره ارسال خواهد شد .',
                    'type' => 'select',
                    'default' => 'yes',
                    'options' => array(
                        'yes' => 'خیر',
                        'no'   => 'بله' // inja no mishe bale , yes mishe kheyr :D ... doroste . moshkeli nis .
                    )
                ),
                array(
                    'name' => 'buyer_checkbox_text',
                    'label' => 'متن انتخاب دریافت پیامک',
                    'desc' => 'این متن بالای چک باکس انتخاب دریافت پیامک در صفحه تسویه حساب نمایش داده خواهد شد .',
                    'type' => 'text',
                    'default' => 'میخواهم از وضعیت سفارش از طریق پیامک آگاه شوم .'
                ),
				array(
                    'name' => 'header_2',
                    'label' => 'وضعیت های دریافت پیامک',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
                array(
                    'name' => 'order_status',
                    'label' => 'وضعیت های سفارش مجاز',
                    'desc' => 'می توانید مشخص کنید مشتری در چه وضعیتی می توانند پیامک دریافت کنند.',
                    'type' => 'multicheck',
                    'options' => function_exists('get_all_woo_status_ps_sms') ? get_all_woo_status_ps_sms() : array(),
                ),
				array(
                    'name' => 'allow_buyer_select_status',
                    'label' => 'اجازه به انتخاب وضعیت ها توسط مشتری',
                    'desc' => 'با فعالسازی این گزینه ، مشتری میتواند در صفحه تسویه حساب وضعیت های دلخواه خود را از میان وضعیت های مجاز برای دریافت پیامک را انتخاب نماید . در صورت عدم فعالسازی این قسمت ، در تمام وضعیت های تیک خورده بالا پیامک ارسال میشود .',
                    'type' => 'select',
                    'default' => 'no',
                    'options' => array(
                        'yes' => 'بله',
                        'no'   => 'خیر'
                    )
                ),
				array(
                    'name' => 'buyer_status_mode',
                    'label' => 'نحوه انتخاب وضعیت ها',
                    'desc' => 'این قسمت ملزم به "بله" بودن تنظیمات "اجازه به انتخاب وضعیت ها توسط مشتری" است .',
                    'type' => 'select',
                    'default' => 'selector',
                    'options' => array(
                        'selector' => 'چند انتخابی',
                        'checkbox'   => 'چک باکس'
                    )
                ),
				 array(
                    'name' => 'force_buyer_select_status',
                    'label' => 'الزامی بودن انتخاب حداقل یک وضعیت',
                    'desc' => 'با فعال سازی این گزینه ، کاربر می بایست حداقل یک وضعیت سفارش را از بین وضعیت های مجاز انتخاب کند . این قسمت نیز ملزم به "بله" بودن تنظیمات "انتخاب وضعیت ها توسط مشتری" است .',
                    'type' => 'select',
                    'default' => 'no',
                    'options' => array(
                        'yes' => 'بله',
                        'no'   => 'خیر'
                    )
                ),
				array(
                    'name' => 'buyer_select_status_text_top',
                    'label' => 'متن بالای انتخاب وضعیت ها',
                    'desc' => 'این متن بالای لیست وضعیت ها در صفحه تسویه حساب برای انتخاب مشتری قرار میگیرد .',
                    'type' => 'text',
                    'default' => 'وضعیت هایی که مایل به دریافت پیامک هستید را انتخاب نمایید'
                ),
				array(
                    'name' => 'buyer_select_status_text_bellow',
                    'label' => 'متن پایین انتخاب وضعیت ها',
                    'desc' => 'این متن پایین لیست وضعیت ها در صفحه تسویه حساب برای انتخاب مشتری قرار میگیرد .',
                    'type' => 'text',
                    'default' => ''
                ),
				array(
                    'name' => 'header_3',
                    'label' => 'متن پیامک مشتری',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
                array(
                    'name' => 'sms_body',
                    'label' =>'متن پیامک به مشتری',
                    'desc' => "شما می توانید متنی دلخواه برای ارسال پیامک به مشتری وارد کنید .<br/>همچنین می توانید از کدهای میانبر زیر نیز استفاده نمایید :<br/><code>{first_name}</code> : نام خریدار ، <code>{last_name}</code> : نام خانوادگی خریدار ، <code>{phone}</code> شماره موبایل خریدار ، <code>{email}</code> : ایمیل خریدار<br/><code>{order_id}</code> : شماره سفارش ، <code>{status}</code> : وضعیت سفارش ، <code>{price}</code> : مبلغ سفارش ، <code>{all_items}</code> : آیتم های سفارش  ، <code>{count_items}</code> : تعداد آیتم های سفارش  ، <code>{transaction_id}</code> : شماره تراکنش",
                    'type' => 'textarea',
                    'default' => "سلام {last_name} {first_name}\nسفارش {order_id} دریافت شد و هم اکنون در وضعیت {status} می باشد\nآیتم های سفارش : {all_items}\nمبلغ سفارش : {price}\nشماره تراکنش : {transaction_id}"
                ),
            )),
			
			'sms_admin_settings' => apply_filters( 'sms_admin_settings_settings',  array(
				array(
                    'name' => 'header_1',
                    'label' => 'پیامک مدیر اصلی',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
				array(
                    'name' => 'enable_super_admin_sms',
                    'label' => 'ارسال پیامک به مدیران اصلی',
                    'desc' => 'با انتخاب این گزینه ، در هنگام ثبت و یا تغییر سفارش ، برای مدیران اصلی سایت پیامک ارسال می گردد .',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
                array(
                    'name' => 'super_admin_phone',
                    'label' => 'شماره موبایل مدیران اصلی',
                    'desc' => '<br/>شماره ها را با کاما (,) جدا نمایید',
                    'type' => 'text'
                ),/*
				array(
                    'name' => 'enable_super_admin_telegram',
                    'label' => 'ارسال پیام به مدیران اصلی از طریق تلگرام',
                    'desc' => 'با انتخاب این گزینه ، در هنگام ثبت و یا تغییر سفارش ، برای مدیران اصلی سایت از طریق تلگرام پیام ارسال می گردد .',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
                array(
                    'name' => 'super_admin_telegram_API_Key',
                    'label' => 'API Key',
                    'desc' => '',
                    'type' => 'text',
                    'default' => ''
                ),
                array(
                    'name' => 'super_admin_telegram_API_Token',
                    'label' => 'API Token',
                    'desc' => '',
                    'type' => 'text',
                    'default' => ''
                ),*/
                array(
                    'name' => 'super_admin_order_status',
                    'label' => 'وضعیت های سفارش دریافت پیامک مدیران اصلی',
                    'desc' => 'می توانید مشخص کنید مدیران اصلی سایت در چه وضعیت هایی پیامک دریافت کنند ',
                    'type' => 'multicheck',
                    'options' => function_exists('get_all_woo_status_ps_sms_for_admin') ? get_all_woo_status_ps_sms_for_admin() : array(),
                ),
                array(
                    'name' => 'super_admin_sms_body',
                    'label' => 'متن پیامک سفارش به مدیران اصلی',
                    'desc' => "شما می توانید متنی دلخواه برای ارسال پیامک به مدیران اصلی را وارد کنید .<br/>همچنین می توانید از کدهای میانبر زیر نیز استفاده نمایید :<br/><code>{first_name}</code> : نام خریدار ، <code>{last_name}</code> : نام خانوادگی خریدار ، <code>{phone}</code> شماره موبایل خریدار ، <code>{email}</code> : ایمیل خریدار<br/><code>{order_id}</code> : شماره سفارش ، <code>{status}</code> : وضعیت سفارش ، <code>{price}</code> : مبلغ سفارش ، <code>{all_items}</code> : آیتم های سفارش  ، <code>{count_items}</code> : تعداد آیتم های سفارش  ، <code>{transaction_id}</code> : شماره تراکنش",
                    'type' => 'textarea',
                    'default' => "سلام مدیر\nسفارش {order_id} ثبت شده است و هم اکنون در وضعیت {status} می باشد\nآیتم های سفارش : {all_items}\nمبلغ سفارش : {price}"
                ),
				array(
                    'name' => 'header_2',
                    'label' => 'پیامک مدیران هر محصول',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
				array(
                    'name' => 'enable_product_admin_sms',
                    'label' => 'ارسال پیامک به مدیران محصول',
                    'desc' => 'با انتخاب این گزینه ، در هنگام ثبت و یا تغییر سفارش ، برای مدیران هر محصول پیامک ارسال می گردد .',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
                array(
                    'name' => 'product_admin_sms_body',
                    'label' => 'متن پیامک به مدیران محصول',
                    'desc' => "شما می توانید متنی دلخواه برای ارسال پیامک به مدیران محصولات را وارد کنید .<br/>برای وارد کردن شماره موبایل و وضعیت دریافت پیامک مدیر هر محصول هم از تب پیامک موجود در محصول اقدام نمایید .<br/>همچنین علاوه بر کد های میانبر بالا می توانید از کد میانبر زیر نیز استفاده نمایید :<br/><code>{vendor_items}</code> : آیتم های سفارش اختصاص یافته به هر شماره ",
                    'type' => 'textarea',
                    'default' => "سلام\nسفارش {order_id} ثبت شده است و هم اکنون در وضعیت {status} می باشد\nآیتم های سفارش اختصاص یافته به شماره شما : {vendor_items}"
                ),
				array(
                    'name' => 'header_3',
                    'label' => 'متن پیامک های موجودی انبار',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
                array(
                    'name' => 'admin_out_stock',
                    'label' => 'اتمام موجودی انبار',
                    'desc' => "متن پیامک زمانیکه موجودی انبار تمام شد",
                    'type' => 'textarea',
                    'default' => "سلام\nموجودی انبار محصول {product_title} به اتمام رسیده است ."
                ),
                array(
                    'name' => 'admin_low_stock',
                    'label' => 'کاهش موجودی انبار',
                    'desc' => "متن پیامک زمانیکه موجودی انبار کم است",
                    'type' => 'textarea',
                    'default' => "سلام\nموجودی انبار محصول {product_title} رو به اتمام است ."
                ),
				array(
                    'name' => 'header_4',
                    'label' => 'شورت کد های قابل استفاده',
                    'desc' => "شورت کد های قابل استفاده در متن پیامک های مرتبط با موجوی انبار :<br/><br/><code>{product_id}</code> : آیدی محصول ، <code>{sku}</code> : شناسه محصول ، <code>{product_title}</code> : عنوان محصول ، <code>{stock}</code> : موجودی انبار",
                    'type' => 'html',
                ),
				
				
			) ),
						
			'sms_notif_settings' => apply_filters( 'sms_notif_settings_settings',  array(
				array(
                    'name' => 'enable_notif_sms_main',
                    'label' => 'فعال سازی اطلاع رسانی',
                    'desc' => 'با فعالسازی این گزینه قابلیت اطلاع رسانی پیامکی محصولات به ووکامرس اضافه خواهد شد . و در صورت غیرفعالسازی کلیه قسمت های زیر بی تاثیر خواهند شد .',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
				array(
                    'name' => 'notif_old_pr',
                    'label' => 'اعمال محصولات قدیمی',
                    'desc' => 'منظور از محصولات قدیمی محصولاتی هستند که قبل از نسخه جدید افزونه پیامک ایجاد شده اند و تنظیم نشده اند .',
                    'type' => 'select',
                    'default' => 'no',
                    'options' => array(
                        'yes' => 'اعمال تنظیمات پیشفرض بر روی محصولات قدیمی',
                        'no'   => 'اطلاع رسانی پیامکی رو برای محصولات قدیمی نادیده بگیر'
                    )
                ),
				array(
                    'name' => 'header_1',
                    'label' => 'تذکر',
                    'desc' => 'کلیه قسمت های زیر تنظیمات پیشفرض بوده و برای هر محصول قابل تنظیم جدا گانه می باشد .<br/><br/>منظور از اطلاع رسانی محصولات ، آگاه سازی کاربران از وضعیت های هر محصول دلخواه شان نظیر ، فروش حراج ، اتمام محصول . ... می باشد . ',
                    'type' => 'html',
                ),
				array(
                    'name' => 'header_2',
                    'label' => 'نمایش در صفحه محصول',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
                array(
                    'name' => 'enable_notif_sms',
                    'label' => 'نمایش خودکار',
                    'desc' => 'با فعالسازی این قسمت گزینه "میخواهم از وضعیت محصول توسط پیامک با خبر شوم" در صفحه محصولات اضافه خواهد شد .<br/>
						میتوانید این قسمت "نمایش خودکار" را غیرفعال نمایید و بجای آن از شورت کد [woo_ps_sms] یا ابزارک "اطلاع رسانی پیامکی ووکامرس" در صفحه محصول استفاده نمایید .<br/><br/>
						تذکر : برای جلوگیری از مشکل تداخل  جیکوئری ، در صفحه هر محصول فقط از یکی از حالت های "نمایش خودکار" ، "ابزارک" یا "شورت کد" استفاده نمایید .',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
				array(
                    'name' => 'notif_title',
                    'label' => 'متن سر تیتر گزینه ها',
                    'desc' => '<br/>این متن در صفحه محصول به صورت چک باکس ظاهر خواهد شد و مشتری با فعال کردن آن میتواند شماره خود را برای دریافت اطلاعیه آن محصول وارد نماید .',
                    'type' => 'text',
					'default' => "به من از طریق پیامک اطلاع بده"
                ),
				array(
                    'name' => 'header_3',
                    'label' => 'گزینه های اصلی',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
				array(
                    'name' => 'header_4',
                    'label' => 'شورت کد های قابل استفاده',
                    'desc' => "شورت کد های قابل استفاده در متن پیامک ها :<br/><br/><code>{product_id}</code> : آیدی محصول ، <code>{sku}</code> : شناسه محصول ، <code>{product_title}</code> : عنوان محصول ، <code>{regular_price}</code> قیمت اصلی ، <code>{onsale_price}</code> : قیمت فروش فوق العاده<br/><code>{onsale_from}</code> : تاریخ شروع فروش فوق العاده ، <code>{onsale_to}</code> : تاریخ اتمام فروش فوق العاده ، <code>{stock}</code> : موجودی انبار",
                    'type' => 'html',
                ),
				
                array(
                    'name' => 'enable_onsale',
                    'label' => 'زمانیکه محصول حراج شد',
                    'desc' => 'هنگامی که این گزینه فعال باشد در صورت حراج نبودن محصول گزینه "زمانیکه که محصول حراج شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
                array(
                    'name' => 'notif_onsale_text',
                    'label' => 'متن گزینه "زمانیکه محصول حراج شد"',
                    'desc' => '<br/>میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه محصول حراج شد" نمایید .',
                    'type' => 'text',
					'default' => "زمانیکه محصول حراج شد"
                ),
                array(
                    'name' => 'notif_onsale_sms',
                    'label' =>'متن پیامک "زمانیکه محصول حراج شد"',
                    'desc' => '',
                    'type' => 'textarea',
                    'default' => "سلام\nمحصول {product_title} از قیمت {regular_price} به قیمت {onsale_price} کاهش یافت ."
                ),
                array(
                    'name' => 'enable_notif_no_stock',
                    'label' => 'زمانیکه که محصول موجود شد',
                    'desc' => 'هنگامی که این گزینه فعال باشد در صورت ناموجود شدن محصول گزینه "زمانیکه که محصول موجود شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
                array(
                    'name' => 'notif_no_stock_text',
                    'label' => 'متن گزینه "زمانیکه محصول موجود شد"',
                    'desc' => '<br/>میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه محصول موجود شد" نمایید .',
                    'type' => 'text',
					'default' => "زمانیکه محصول موجود شد"
                ),
				array(
                    'name' => 'notif_no_stock_sms',
                    'label' =>'متن پیامک "زمانیکه محصول موجود شد"',
                    'desc' => '',
                    'type' => 'textarea',
                    'default' => "سلام\nمحصول {product_title} هم اکنون موجود و قابل خرید می باشد ."
                ),
				array(
                    'name' => 'enable_notif_low_stock',
                    'label' => 'زمانیکه موجودی انبار محصول کم شد',
                    'desc' => 'هنگامی که این گزینه فعال باشد ، گزینه "زمانیکه که موجودی انبار محصول کم شد" نیز به لیست گزینه های اطلاع رسانی اضافه خواهد شد .',
                    'type' => 'checkbox',
                    'default' => 'no'
                ),
                array(
                    'name' => 'notif_low_stock_text',
                    'label' => 'متن گزینه "زمانیکه موجودی انبار محصول کم شد"',
                    'desc' => '<br/>میتوانید متن دلخواه خود را جایگزین جمله "زمانیکه موجودی انبار محصول کم شد" نمایید .',
                    'type' => 'text',
					'default' => "زمانیکه موجودی انبار محصول کم شد"
                ),
				array(
                    'name' => 'notif_low_stock_sms',
                    'label' =>'متن پیامک "زمانیکه محصول موجودی انبار کم شد"',
                    'desc' => '',
                    'type' => 'textarea',
                    'default' => "سلام\nموجودی محصول {product_title} کم می باشد . لطفا در صورت تمایل به خرید سریعتر اقدام نمایید ."
                ),
				array(
                    'name' => 'header_5',
                    'label' => 'تذکر',
                    'desc' => 'توجه داشته باشید که عملکرد گزینه های مربوط به "موجودی و انبار" وابسته به <a href="'.admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ).'" target="_blank">تنظیمات ووکامرس</a> خواهد بود .',
                    'type' => 'html',
                ),				
				array(
                    'name' => 'header_6',
                    'label' => 'گزینه های اضافی',
                    'desc' => '<hr/>',
                    'type' => 'html',
                ),
				array(
                    'name' => 'notif_options',
                    'label' =>'گزینه های دلخواه',
                    'desc' => 'شما میتوانید گزینه های دلخواه خود را برای نمایش در صفحه محصولات ایجاد نمایید و به صورت دستی به مشتریانی که در گزینه های بالا عضو شده اند پیامک ارسال کنید .<br/>
						برای اضافه کردن گزینه ها ، همانند نمونه بالا ابتدا یک کد عددی دلخواه تعریف کنید سپس بعد از قرار دادن عبارت ":" متن مورد نظر را بنویسید .<br/>
						دقت کنید که کد عددی هر گزینه بسیار مهم بوده و از تغییر کد مربوط به هر گزینه بعد از ذخیره تنظیمات خود داری نمایید .',
                    'type' => 'textarea',
                    'default' => "1:زمانیکه محصول توقف فروش شد\n2:زمانیکه نسخه جدید محصول منتشر شد\n"
                ),
				array(
                    'name' => 'header_7',
                    'label' => 'تذکر',
                    'desc' => 'متن پیامک مربوط به گزینه های اضافی را در میتوانید در صفحه هر محصول در باکس سمت چپ آن نوشته و پیامک را ارسال نمایید .',
                    'type' => 'html',
                ),
			) ),
        );
        return apply_filters( 'persianwoosms_settings_section_content', $settings_fields );
    }

}