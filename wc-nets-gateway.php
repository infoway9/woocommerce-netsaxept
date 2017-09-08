<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WC_Gateway_Payone_Gateway
 *
 * @author Pradipta
 */
class WC_Gateway_Nets_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $text_domain = 'ps_nets';
        // The global ID for this Payment method
        $this->id = "ps_nets_payment";

        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __("Netaxept Credit Card", $text_domain);

        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __("Netaxept Payment Gateway Plug-in for WooCommerce", $text_domain);

        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = __("Netaxept", $text_domain);

        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = NETAXEPT_PLUGIN_URL . 'images/nets_pay.jpg';


        $this->has_fields = false;

        // Supports the default credit card form
        //$this->supports = array('default_credit_card_form');
        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();

        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        $this->wsdl = 'https://epayment.nets.eu/netaxept.svc?wsdl';
        $this->terminal_url = 'https://epayment.nets.eu/terminal/default.aspx';
        $this->redirect_url = add_query_arg('wc-api', get_class($this), site_url());

        if ($this->test_environment == 'yes') {
            $this->wsdl = 'https://test.epayment.nets.eu/netaxept.svc?wsdl';
            $this->terminal_url = 'https://test.epayment.nets.eu/terminal/default.aspx';
        }

        /* PARAMETERS IN ENVIRONMENT */
        $this->Language = '';
        $this->OS = '';
        $this->WebServicePlatform = 'PHP5';

        /* PARAMETERS IN TERMINAL */

        $this->autoAuth = '';
        $this->paymentMethodList = '';
        $this->orderDescription = '';
        $this->redirectOnError = '';

        /* PARAMETERS IN REGISTER REQUEST */

        $this->AvtaleGiro = '';
        $this->CardInfo = '';
        $this->Customer = '';
        $this->Ndescription = '';
        $this->DnBNorDirectPayment = '';
        $this->Environment = '';
        $this->MicroPayment = '';
        $this->serviceType = 'B';
        $this->transactionId = '';
        $this->transactionReconRef = '';
        $this->Terminal = '';
        $this->Recurring = '';

        $this->redirectOnError = '';

        $this->NetaOrder = '';

        $this->RegisterRequest = '';

        /* $this->Language = '';
          $this->Language = ''; */


        // Lets check for SSL
        // add_action('admin_notices', array($this, 'do_ssl_check'));
        // Save settings
        if (is_admin()) {
            // Versions over 2.0
            // Save our administration options. Since we are not going to be doing anything special
            // we have not defined 'process_admin_options' in this class so the method in the parent
            // class will be used instead
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
        //add_action('init', array($this, 'check_payone_response'));
        add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'neta_response_handler'));
        //add_action('woocommerce_receipt_ps_payone_payment', array($this, 'payone_receipt_page'));
    }

//Netaxept
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable / Disable', 'ps_nets'),
                'label' => __('Enable this payment gateway', 'ps_nets'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', 'ps_nets'),
                'type' => 'text',
                'desc_tip' => __('Payment title the customer will see during the checkout process.', 'ps_nets'),
                'default' => __('Credit Card Payment', 'ps_nets'),
            ),
            'description' => array(
                'title' => __('Description', 'ps_nets'),
                'type' => 'textarea',
                'desc_tip' => __('Payment description the customer will see during the checkout process.', 'ps_nets'),
                'default' => __('Pay securely using your Netaxept Payment.', 'ps_nets'),
                'css' => 'max-width:350px;'
            ),
            'nets_merchant_id' => array(
                'title' => __('Merchant ID', 'ps_nets'),
                'type' => 'text',
                'desc_tip' => __('Merchant ID', 'ps_nets'),
            ),
            'nets_api_token' => array(
                'title' => __('Netaxept API Token', 'ps_nets'),
                'type' => 'password',
                'desc_tip' => __('This is Merchant API Token Key .', 'ps_nets'),
            ),
            'test_environment' => array(
                'title' => __('Test Mode', 'migs'),
                'label' => __('Enable Test Mode', 'ps_payone'),
                'type' => 'checkbox',
                'description' => __('Place the payment gateway in test mode.', 'ps_nets'),
                'default' => 'yes',
            )
        );
    }

    public function neta_response_handler() {
        global $woocommerce;


        $reg_response = $_REQUEST['responseCode'];
        $transactionId = $_REQUEST['transactionId'];
        $QueryResults = $this->callingQuery($transactionId);
        $wooOrder = new WC_Order($QueryResults->OrderInformation->OrderNumber);
        if ($reg_response == 'OK') {

            $AuthProcessRes = $this->callingAuth($transactionId, $QueryResults->OrderInformation->Amount);

            if (isset($AuthProcessRes->ResponseCode) && $AuthProcessRes->ResponseCode == 'OK') {

                $QueryResults = $this->callingQuery($transactionId);

                $FinalRes = $this->callingCapture($transactionId, $QueryResults->OrderInformation->Amount);

                if (isset($FinalRes->ResponseCode) && $FinalRes->ResponseCode == 'OK') {
                    $FinalQuery = $this->callingQuery($transactionId);



                    $wooOrder->reduce_order_stock();
                    $wooOrder->add_order_note(__('Netaxept Credit card payment completed.', 'ps_nets'));
                    $wooOrder->payment_complete();
                    $woocommerce->cart->empty_cart();
                    wp_redirect($this->get_return_url($wooOrder));
                    exit;
                } else {

                    wc_add_notice('ErrorCode: ' . $FinalRes->faultstring, 'error');

                    $wooOrder->add_order_note('Error: ' . $FinalRes->faultstring . '');
                    wp_redirect($woocommerce->cart->get_checkout_url());
                    exit;
                }
            } else {

                wc_add_notice('ErrorCode: ' . $AuthProcessRes->faultstring, 'error');

                $wooOrder->add_order_note('Error: ' . $AuthProcessRes->faultstring . '');
                wp_redirect($woocommerce->cart->get_checkout_url());
                exit;
            }
        } else {

            wc_add_notice('Error: Payment has been canceled, please try again.', 'error');

            $wooOrder->add_order_note('Error: Response Code-' . $QueryResults->Error->ResponseCode . ', Response-' . $QueryResults->Error->ResponseText);
            wp_redirect($woocommerce->cart->get_checkout_url());
            exit;
        }
    }

    public function process_payment($order_id) {
        global $woocommerce;

        $order = new WC_Order($order_id);
        /* $card_num = str_replace(array(' ', '-'), '', $_POST['ps_nets_payment-card-number']);
          $cardExp = trim($_POST['ps_nets_payment-card-expiry']);
          $expDateArr = explode('/', $cardExp);
          $formatted_card_exp_date = (trim($expDateArr[0]) . trim($expDateArr[1]));
          $card_cvc = ( isset($_POST['ps_nets_payment-card-cvc']) ) ? $_POST['ps_nets_payment-card-cvc'] : ''; */

        /* Configure the Netaxept Gateway */

        $this->setEnviornMent();
        $this->setTerminal();
        $this->setOrder($order_id, $order->order_total);
        $this->startRegisterReq();

        $RegisterResult = $this->callRegisterSoap();
        if (!isset($RegisterResult->TransactionId)) {
            wc_add_notice('ErrorCode: ' . $RegisterResult->faultstring, 'error');

            $order->add_order_note('Error: ' . $RegisterResult->faultstring . '');
            return false;
        }
        $prepare_the_url = $this->terminal_url . "?merchantId=" . $this->nets_merchant_id . "&transactionId=" . $RegisterResult->TransactionId . '&wooOrder=' . $order_id;
        /* Update the status On hold condition */
        //$order->update_status('on-hold');




        return array(
            'result' => 'success',
            'redirect' => $prepare_the_url
        );
    }

    // Validate fields
    public function validate_fields() {
        return true;
    }

    public function setEnviornMent() {
        $Language = '';
        $OS = '';
        $WebServicePlatform = 'PHP5';

        ####  ENVIRONMENT OBJECT  ####
        $this->Environment = new Environment(
                $Language, $OS, $WebServicePlatform
        );

        //return $Environment;
    }

    public function setTerminal() {

        ####  TERMINAL OBJECT  ####
        $this->Terminal = new Terminal(
                $this->autoAuth, $this->paymentMethodList, $language, $this->orderDescription, $this->redirectOnError, $this->redirect_url
        );
    }

    public function setOrder($orderNumber, $amount) {
        $currencyCode = 'NOK';
        $force3DSecure = '';
        $ArrayOfItem = '';
        $UpdateStoredPaymentInfo = '';

        $this->NetaOrder = new Order(
                $this->setNetaxeptAmount($amount), $currencyCode, $force3DSecure, $ArrayOfItem, $orderNumber, $UpdateStoredPaymentInfo
        );
    }

    public function setNetaxeptAmount($amount) {
        return ($amount * 100);
    }

    public function startRegisterReq() {

        $this->RegisterRequest = new RegisterRequest(
                $this->AvtaleGiro, $this->CardInfo, $this->Customer, $this->Ndescription, $this->DnBNorDirectPayment, $this->Environment, $this->MicroPayment, $this->NetaOrder, $this->Recurring, $this->serviceType, $this->Terminal, $this->transactionId, $this->transactionReconRef
        );
    }

    public function callRegisterSoap() {
        $InputParametersOfRegister = array
            (
            "token" => trim($this->nets_api_token),
            "merchantId" => trim($this->nets_merchant_id),
            "request" => $this->RegisterRequest
        );



        try {
            if (strpos($_SERVER["HTTP_HOST"], 'uapp') > 0) {
                // Creating new client having proxy
                $client = new SoapClient($this->wsdl, array('proxy_host' => "isa4", 'proxy_port' => 8080, 'trace' => true, 'exceptions' => true));
            } else {
                // Creating new client without proxy
                $client = new SoapClient($this->wsdl, array('trace' => true, 'exceptions' => true));
            }

            $OutputParametersOfRegister = $client->__call('Register', array("parameters" => $InputParametersOfRegister));

            // RegisterResult
            return $RegisterResult = $OutputParametersOfRegister->RegisterResult;
        } catch (SoapFault $fault) {
            return $fault;
        }
    }

    public function callingAuth($transactionId, $transactionAmount) {

        $description = "description of AUTH operation";
        $operation = "AUTH";

        $transactionReconRef = "";

        ####  PROCESS OBJECT  ####
        $ProcessRequest = new ProcessRequest(
                $description, $operation, $transactionAmount, $transactionId, $transactionReconRef
        );


        $InputParametersOfProcess = array
            (
            "token" => $this->nets_api_token,
            "merchantId" => $this->nets_merchant_id,
            "request" => $ProcessRequest
        );

        try {
            if (strpos($_SERVER["HTTP_HOST"], 'uapp') > 0) {
                // Creating new client having proxy
                $client = new SoapClient($this->wsdl, array('proxy_host' => "isa4", 'proxy_port' => 8080, 'trace' => true, 'exceptions' => true));
            } else {
                // Creating new client without proxy
                $client = new SoapClient($this->wsdl, array('trace' => true, 'exceptions' => true));
            }

            $OutputParametersOfProcess = $client->__call('Process', array("parameters" => $InputParametersOfProcess));

            return $ProcessResult = $OutputParametersOfProcess->ProcessResult;
        } // End try
        catch (SoapFault $fault) {
            return $fault;
        }
    }

    public function callingQuery($transactionId) {
        $QueryRequest = new QueryRequest(
                $transactionId
        );

####  ARRAY WITH QUERY PARAMETERS  ####
        $InputParametersOfQuery = array
            (
            "token" => $this->nets_api_token,
            "merchantId" => $this->nets_merchant_id,
            "request" => $QueryRequest
        );


####  START QUERY CALL  ####
        try {
            if (strpos($_SERVER["HTTP_HOST"], 'uapp') > 0) {
                // Creating new client having proxy
                $client = new SoapClient($this->wsdl, array('proxy_host' => "isa4", 'proxy_port' => 8080, 'trace' => true, 'exceptions' => true));
            } else {
                // Creating new client without proxy
                $client = new SoapClient($this->wsdl, array('trace' => true, 'exceptions' => true));
            }

            $OutputParametersOfQuery = $client->__call('Query', array("parameters" => $InputParametersOfQuery));

            return $QueryResult = $OutputParametersOfQuery->QueryResult;
        } // End try
        catch (SoapFault $fault) {

            return $fault;
        }
    }

    public function callingCapture($transactionId, $transactionAmount) {

        $description = "description of CAPTURE operation";
        $operation = "CAPTURE";

        $transactionReconRef = "";

        ####  PROCESS OBJECT  ####
        $ProcessRequest = new ProcessRequest(
                $description, $operation, $transactionAmount, $transactionId, $transactionReconRef
        );


        $InputParametersOfProcess = array
            (
            "token" => $this->nets_api_token,
            "merchantId" => $this->nets_merchant_id,
            "request" => $ProcessRequest
        );

        try {
            if (strpos($_SERVER["HTTP_HOST"], 'uapp') > 0) {
                // Creating new client having proxy
                $client = new SoapClient($this->wsdl, array('proxy_host' => "isa4", 'proxy_port' => 8080, 'trace' => true, 'exceptions' => true));
            } else {
                // Creating new client without proxy
                $client = new SoapClient($this->wsdl, array('trace' => true, 'exceptions' => true));
            }

            $OutputParametersOfProcess = $client->__call('Process', array("parameters" => $InputParametersOfProcess));

            return $ProcessResult = $OutputParametersOfProcess->ProcessResult;
        } // End try
        catch (SoapFault $fault) {
            return $fault;
        }
    }

}

?>
