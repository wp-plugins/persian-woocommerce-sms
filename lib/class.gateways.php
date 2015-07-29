<?php
class WoocommerceIR_Gateways_SMS {
    
	private static $_instance;
    
	public static function init() {
        if ( !self::$_instance )
            self::$_instance = new WoocommerceIR_Gateways_SMS();
        return self::$_instance;
    }
	
    function get_sms_gateway() {
        $gateway = array( 
            'none'      => 'انتخاب کنید',
            'sepehritc' => 'Sepehr-ITC.com',
            'parandsms' => 'ParandSMS.com',
            'gamapayamak' => 'GAMAPayamak.com',
            'limoosms' => 'LimooSMS.com',
            'maxsms' => 'S1.Max-SMS.ir',
            'maxsms2' => 'S2.Max-SMS.ir',
            'smsfa' => 'SMSFa.ir',
            'aradsms' => 'Arad-SMS.ir',
            'farapayamak' => 'FaraPayamak.ir',
			'payamafraz' => 'PayamAfraz.com',
			'niazpardaz' => 'SMS.NiazPardaz.com',
			'yektasms' => 'Yektatech.ir',
			'smsbefrest' => 'SmsBefrest.ir',
			'relax' => 'Relax.ir',
			'paaz' => 'Paaz.ir',
			'postgah' => 'Postgah.info',
			'idehpayam' => 'IdehPayam.com',
			'azaranpayamak' => 'azaranpayamak.ir',
			'smsir' => 'SMS.ir',
			'manirani' => 'manirani.ir',
			'tjp' => 'TJP.ir'
        );
        return apply_filters( 'persianwoosms_sms_gateway', $gateway );
    }
	

	/**
	 * وب سرویس باید توانایی ارسال پیامک دسته جمعی را داشته باشد . ارسال به چندین شماره در یک بار درخواست وب سرویس
    */
	 
	
	/**
     * Sends SMS via tjp.ir
     */
    function tjp( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient('http://sms-login.tjp.ir/webservice/?WSDL', array('login' => $username,'password' => $password) );
		try
		{
			$status = $client->sendToMany($to , $massage);
		}
		
		catch (SoapFault $sf)
		{
			$sms_response = $sf->faultcode;
		}
        if ($sms_response == '') {
            $response = true;
        }
        return $response;
    }
	
	
	/**
     * Sends SMS via Max-SMS.ir - S1
     */
    function maxsms( $sms_data ) {
        $response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
			
		$client = new SoapClient('http://login.max-sms.ir/webservice/?WSDL', array('login' => $username,'password' => $password) );
		try
		{
			$status = $client->sendToMany($to , $massage);
		}
		
		catch (SoapFault $sf)
		{
			$sms_response = $sf->faultcode;
		}
        if ($sms_response == '') {
            $response = true;
        }

        return $response;
    }
	
		
	/**
     * Sends SMS via Max-SMS.ir S2
     */
	function maxsms2( $sms_data ) {
        $response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://panel.max-sms.ir/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via ParandSMS.ir
     */
	function parandsms( $sms_data ) {
        $response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://parandsms.ir/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' &&  $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via arad-sms.ir
     */
    function aradsms( $sms_data ) {
	    $response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://arad-sms.ir/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' &&  $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via smsbefrest.ir
     */
	function smsbefrest( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://87.107.121.52/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }

		
	/**
     * Sends SMS via Relax.ir
     */
	function relax( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://onlinepanel.ir/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }

	
	
	/**
     * Sends SMS via sms.paaz.ir
     */
	function paaz( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://sms.paaz.ir/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via FaraPayamak.ir
     */
    function farapayamak( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://87.107.121.54/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via niazpardaz.com
     */
    function niazpardaz( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://87.107.121.54/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
		
	/**
     * Sends SMS via Sepehr-ITC.ir
     */
    function sepehritc( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://87.107.121.54/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via payamafraz.com
     */
    function payamafraz( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://payamafraz.ir/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via yektasms.com
     */
    function yektasms( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://87.107.121.54/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	
	
	/**
     * Sends SMS via gamapayamak.com
     */
    function gamapayamak( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://87.107.121.54/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	

	
	/**
     * Sends SMS via limoosms.com
     */
    function limoosms( $sms_data ) {	
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$client = new SoapClient("http://87.107.121.54/post/send.asmx?wsdl");
		try
		{	
			$encoding = "UTF-8";     
			$parameters = array(
				'username' => $username,
				'password' => $password,
				'from' => $from,
				'to' => $to,
				'text' => iconv($encoding, 'UTF-8//TRANSLIT', $massage ),
				'isflash' => false,
				'udh' => "",
				'recId' => array(0),
				'status' => 0
			);
			$status = $client->SendSms($parameters)->SendSmsResult;
		}
		catch (SoapFault $ex) {
			$sms_response = $ex->faultstring;
		}
		
        if ($sms_response == '' && $status == 1 ) {
            $response = true;
        }

        return $response;
    }
	
	
	
	/**
     * Sends SMS via smsfa
     */
    function smsfa( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
		
		$client = new SoapClient('http://smsfa.net/API/Send.asmx?WSDL');
		try
		{
			$status= $client->SendSms(
				array(
					'username'	=> $username,
					'password'	=> $password,
					'from'		=> $from,
					'to'		=> $to,
					'text'		=> $massage,
					'flash'		=> false,
					'udh'		=> ''
				)
			)->SendSmsResult;
		}
		
		catch (SoapFault $sf)
		{
			$sms_response = $sf->faultcode;
		}
        if ($sms_response == '' && $status>0 ) {
            $response = true;
        }
        return $response;
    }
	
	
	
	
	/**
     * Sends SMS via postgah
     */
    function postgah( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
		
		$client = new SoapClient('http://postgah.net/API/Send.asmx?WSDL');
		try
		{
			$status= $client->SendSms(
				array(
					'username'	=> $username,
					'password'	=> $password,
					'from'		=> $from,
					'to'		=> $to,
					'text'		=> $massage,
					'flash'		=> false,
					'udh'		=> ''
				)
			)->SendSmsResult;
		}
		
		catch (SoapFault $sf)
		{
			$sms_response = $sf->faultcode;
		}
        if ($sms_response == ''  && $status>0  ) {
            $response = true;
        }
        return $response;
    }
	
	
	
	/**
     * Sends SMS via azaranpayamak
     */
    function azaranpayamak( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
		
		$client = new SoapClient('http://azaranpayamak.ir/API/Send.asmx?WSDL');
		try
		{
			$status= $client->SendSms(
				array(
					'username'	=> $username,
					'password'	=> $password,
					'from'		=> $from,
					'to'		=> $to,
					'text'		=> $massage,
					'flash'		=> false,
					'udh'		=> ''
				)
			)->SendSmsResult;
		}
		
		catch (SoapFault $sf)
		{
			$sms_response = $sf->faultcode;
		}
        if ($sms_response == '' && $status>0 ) {
            $response = true;
        }
        return $response;
    }
	
	
	/**
     * Sends SMS via manirani.ir
     */
    function manirani( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
		
		$client = new SoapClient('http://sms.manirani.ir/API/Send.asmx?WSDL');
		try
		{
			$status= $client->SendSms(
				array(
					'username'	=> $username,
					'password'	=> $password,
					'from'		=> $from,
					'to'		=> $to,
					'text'		=> $massage,
					'flash'		=> false,
					'udh'		=> ''
				)
			)->SendSmsResult;
		}
		
		catch (SoapFault $sf)
		{
			$sms_response = $sf->faultcode;
		}
        if ($sms_response == '' && $status>0 ) {
            $response = true;
        }
        return $response;
    }
	
	
	/**
     * Sends SMS via sms.ir
     */
    function smsir( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $massage = $sms_data['sms_body'];
		if ( empty( $username ) || empty( $password ) ) {
			return $response;
		}
		
		date_default_timezone_set('Asia/Tehran');
		$client= new SoapClient('http://ip.sms.ir/ws/SendReceive.asmx?wsdl');
		foreach ( (array) $sms_data['number'] as $to ) {
			$parameters['userName'] = $username;
			$parameters['password'] = $password;
			$parameters['mobileNos'] = array(doubleval($to));
			$parameters['messages'] = array($massage);
			$parameters['lineNumber'] = $from;
			$parameters['sendDateTime'] = date("Y-m-d")."T".date("H:i:s");
			$status= $client->SendMessageWithLineNumber($parameters)->message;
			if ( empty($status) )
				$status = true;
			else
				$status = false;
			$response = $status || $response;
		}
        return $response;
	}
	
	
	
	/**
     * Sends SMS via idehpayam
     */
    function idehpayam( $sms_data ) {
		$response = false;
        $username = ps_sms_options( 'persian_woo_sms_username', 'sms_main_settings' );
        $password = ps_sms_options( 'persian_woo_sms_password', 'sms_main_settings' );
        $from = ps_sms_options( 'persian_woo_sms_sender', 'sms_main_settings' );
        $to = $sms_data['number'];
		$massage = $sms_data['sms_body'];
        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }

		if ( !class_exists( 'nusoap_client' ) ) 
			require_once PS_WOO_SMS_PLUGIN_LIB_PATH. '/nusoap.php';
		
        $username = get_sms_options( 'us' );
        $password = get_sms_options( 'ps' );
		$from = get_sms_options( 'nu' );
		$to = $sms_data['to'];
		$message = $sms_data['text'];
		
		if ( empty( $username ) || empty( $password ) )
            return $result;
   
		$client = new nusoap_client("http://92.50.2.3/class/sms/wssimple/server.php?wsdl");
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8 = true;
		$send = $client->call("SendSMS", array('Username' => $username, 'Password' => $password, 'SenderNumber' => $from, 'RecipientNumbers' => $to, 'Message' => $message, 'Type' => 'normal'));
		$send = explode(';', $send);
		$status = $send[0];
		if ( $status == 'success' )
			$response = true;
        return $response;
	}
			
	
}