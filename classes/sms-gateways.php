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

        $panizsms_response = file_get_contents( 'http://87.107.121.54/post/sendSMS.ashx?' . $content );
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
			$messageId = $client->send(rawurlencode( $phone ),$sms_data['sms_body']);
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
	
	
	
	/**
     * Sends SMS via smsfa
     */
    function smsfa( $sms_data ) {
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

        $smsfa_response = file_get_contents( 'http://smsfa.net/API/SendSms.ashx?' . $content );
        if ( ($smsfa_response != '-1') || ($smsfa_response != '-2') || ($smsfa_response != '-3') || ($smsfa_response != '-4') ) {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via tjp.ir
     */
    function tjp( $sms_data ) {
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
		$client = new SoapClient('http://sms-login.tjp.ir/webservice/?WSDL', $mxoptions);
		try
		{
			$messageId = $client->send(rawurlencode( $phone ),$sms_data['sms_body']);
			sleep(3);
		}
		
		catch (SoapFault $sf)
		{
			$tjp_sms_response = $sf->faultcode;
		}
        if ($tjp_sms_response == '') {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via arad-sms.ir
     */
    function aradsms( $sms_data ) {
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

        $aradsms_response = file_get_contents( 'http://arad-sms.ir/post/sendSMS.ashx?' . $content );
        if ($aradsms_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via FaraPayamak.ir
     */
    function farapayamak( $sms_data ) {
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

        $farapayamak_response = file_get_contents( 'http://87.107.121.54/post/sendSMS.ashx?' . $content );
        if ($farapayamak_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	
	
	/**
     * Sends SMS via niazpardaz.com
     */
    function niazpardaz( $sms_data ) {
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

        $niazpardaz_response = file_get_contents( 'http://login.niazpardaz.com/SMSInOutBox/SendSms?' . $content );
        if ($niazpardaz_response == 'SendWasSuccessful') {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via payamafraz.com
     */
    function payamafraz( $sms_data ) {
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

        $payamafraz_response = file_get_contents( 'http://payamafraz.ir/post/sendSMS.ashx?' . $content );
        if ($payamafraz_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via yektasms.com
     */
    function yektasms( $sms_data ) {
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

        $yektasms_response = file_get_contents( 'http://87.107.121.54/post/sendSMS.ashx?' . $content );
        if ($yektasms_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	/**
     * Sends SMS via smsbefrest.ir
     */
	function smsbefrest( $sms_data ) {
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

        $smsbefrest_response = file_get_contents( 'http://87.107.121.52/post/send.asmx?' . $content );
        if ($smsbefrest_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via Relax.ir
     */
	function relax( $sms_data ) {
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

        $relax_response = file_get_contents( 'http://onlinepanel.ir/post/send.ashx?' . $content );
        if ($relax_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via sms.paaz.ir
     */
	function paaz( $sms_data ) {
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

        $paaz_response = file_get_contents( 'http://sms.paaz.ir/post/send.ashx?' . $content );
        if ($paaz_response == '1-0') {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via postgah
     */
    function postgah( $sms_data ) {
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

        $postgah_response = file_get_contents( 'http://postgah.net/API/SendSms.ashx?' . $content );
        if ( ($postgah_response != '-1') || ($postgah_response != '-2') || ($postgah_response != '-3') || ($postgah_response != '-4') ) {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via idehpayam
     */
    function idehpayam( $sms_data ) {
        $response = false;

        $username = persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway' );
        $password = persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway' );
        $from = persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway' );
        $phone = $sms_data['number'];

        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$content = 'uname=' . rawurlencode( $username ) .
                '&pass=' . rawurlencode( $password ) .
                '&to=' . rawurlencode( $phone ) .
                '&from=' . rawurlencode( $from ) .
                '&msg=' . rawurlencode( $sms_data['sms_body'] );

        $idehpayam_response = file_get_contents( 'http://92.50.2.3/class/sms/webservice/send_url.php?' . $content );
            $response = true;

        return $response;
    }
	
	/**
     * Sends SMS via azaranpayamak
     */
    function azaranpayamak( $sms_data ) {
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

        $azaranpayamak_response = file_get_contents( 'http://azaranpayamak.ir/API/SendSms.ashx?' . $content );
        if ( ($azaranpayamak_response != '0') || ($azaranpayamak_response != '1') || ($azaranpayamak_response != '2') || ($azaranpayamak_response != '9') || ($azaranpayamak_response != '10') ) {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via sms.ir
     */
    function smsir( $sms_data ) {
        $response = false;

        $username = persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway' );
        $password = persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway' );
        $from = persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway' );
        $phone = $sms_data['number'];

        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$content = 'user=' . rawurlencode( $username ) .
                '&pass=' . rawurlencode( $password ) .
                '&to=' . rawurlencode( $phone ) .
                '&lineNo=' . rawurlencode( $from ) .
                '&text=' . rawurlencode( $sms_data['sms_body'] );

        $smsir_response = file_get_contents( 'http://panelesms.net/SendMessage.ashx?' . $content );
        if ($smsir_response == 'ok') {
            $response = true;
        }

        return $response;
    }
	
	/**
     * Sends SMS via manirani.ir
     */
    function manirani( $sms_data ) {
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

        $manirani_response = file_get_contents( 'http://sms.manirani.ir/API/SendSms.ashx?' . $content );
        if ( ($manirani_response != '-1') || ($manirani_response != '-2') || ($manirani_response != '-3') || ($manirani_response != '-4') ) {
            $response = true;
        }

        return $response;
    }
	
	
	/**
     * Sends SMS via websms.ir
     */
    function websms( $sms_data ) {
        $response = false;

        $username = persianwoosms_get_option( 'persian_woo_sms_username', 'persianwoosms_gateway' );
        $password = persianwoosms_get_option( 'persian_woo_sms_password', 'persianwoosms_gateway' );
        $from = persianwoosms_get_option( 'persian_woo_sms_sender', 'persianwoosms_gateway' );
        $phone = $sms_data['number'];

        if ( empty( $username ) || empty( $password ) ) {
            return $response;
        }
				
		$content = 'cusername=' . rawurlencode( $username ) .
                '&cpassword=' . rawurlencode( $password ) .
                '&cmobileno=' . rawurlencode( $phone ) .
                '&csender=' . rawurlencode( $from ) .
                '&cbody=' . rawurlencode( $sms_data['sms_body'] );

        $websms_response = file_get_contents( 'http://s1.websms.ir/wservice.php?' . $content );
        if ( ($websms_response != '112') || ($websms_response != '116')) {
            $response = true;
        }

        return $response;
    }



}
