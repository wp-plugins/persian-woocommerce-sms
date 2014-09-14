<?php

/**
 * @author woocommerce.ir
 */
class Woocommerceir_SMS_Gateways {

    private static $_instance;

    public static function init() {
        if ( !self::$_instance ) {
            self::$_instance = new Woocommerceir_SMS_Gateways();
        }

        return self::$_instance;
    }


    /**
     * Sends SMS via PanizSMS.com
     */
    function panizsms( $sms_data ) {
        $response = false;

        $username = persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway' );
        $password = persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway' );
        $from = persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway' );
        $phone = $sms_data['number'];

        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }

        $content = 'username=' . rawurlencode( $username ) .
                '&password=' . rawurlencode( $password ) .
                '&to=' . rawurlencode( $phone ) .
                '&from=' . rawurlencode( $from ) .
                '&text=' . rawurlencode( $sms_data['sms_body'] );

        $panizsms_response = file_get_contents( 'http://www.panizsms.ir/post/sendSMS.ashx?' . $content );
        if ($panizsms_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via ParandSMS.com
     */
    function parandsms( $sms_data ) {
        $response = false;

        $username = persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway' );
        $password = persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway' );
        $from = persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway' );
        $phone = $sms_data['number'];

        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }

        $content = 'username=' . rawurlencode( $username ) .
                '&password=' . rawurlencode( $password ) .
                '&to=' . rawurlencode( $phone ) .
                '&from=' . rawurlencode( $from ) .
                '&text=' . rawurlencode( $sms_data['sms_body'] );

        $parandsms_response = file_get_contents( 'http://www.parandsms.ir/post/sendSMS.ashx?' . $content );
        if ($parandsms_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via gamapayamak.com
     */
    function gamapayamak( $sms_data ) {
        $response = false;

        $username = persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway' );
        $password = persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway' );
        $from = persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway' );
        $phone = $sms_data['number'];

        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }

        $content = 'username=' . rawurlencode( $username ) .
                '&password=' . rawurlencode( $password ) .
                '&to=' . rawurlencode( $phone ) .
                '&from=' . rawurlencode( $from ) .
                '&text=' . rawurlencode( $sms_data['sms_body'] );

        $gamapayamak_response = file_get_contents( 'http://p.gamapayamak.com/post/sendSMS.ashx?' . $content );
        if ($gamapayamak_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via limoosms.com
     */
    function limoosms( $sms_data ) {
        $response = false;

        $username = persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway' );
        $password = persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway' );
        $from = persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway' );
        $phone = $sms_data['number'];

        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }

        $content = 'username=' . rawurlencode( $username ) .
                '&password=' . rawurlencode( $password ) .
                '&to=' . rawurlencode( $phone ) .
                '&from=' . rawurlencode( $from ) .
                '&text=' . rawurlencode( $sms_data['sms_body'] );

        $limoosms_response = file_get_contents( 'http://panel.limoosms.com/post/sendSMS.ashx?' . $content );
        if ($limoosms_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via max-sms.ir
     */
    function maxsms( $sms_data ) {
        $response = false;

        $username = persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway' );
        $password = persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway' );
        $from = persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway' );
        $phone = $sms_data['number'];

        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$mxoptions = array(
		'login' => rawurlencode($username),
		'password' => rawurlencode($password)
		);
		$client = new SoapClient('http://login.max-sms.ir/webservice/?WSDL', $mxoptions);
		try
		{
			$messageId = $client->send(rawurlencode( $phone ), rawurlencode( $sms_data['sms_body'] ));
			sleep(3);
		}
		
		catch (SoapFault $sf)
		{
			$max_sms_response = $sf->faultcode;
		}
        if ($max_sms_response == '') {
            $response = true;
        }

        return $response;
    }



}
