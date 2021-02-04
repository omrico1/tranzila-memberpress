<?php


class MeprTranzilaGateway extends MeprBaseRealGateway
{

    /** Used in the view to identify the gateway */
    public function __construct() {
        $this->name = __("Tranzila Payment Page", 'memberpress');
        $this->has_spc_form = true;
        $this->set_defaults();

        // Setup the notification actions for this gateway
        $this->notifiers = array( 'notifier' => 'listener',
            'success' => 'success_handler',
            'failure' => 'failure_handler' );
        $this->message_pages = array('cancel' => 'cancel_message');
    }

    public function load($settings) {
        $this->settings = (object)$settings;
        $this->set_defaults();
    }

    protected function set_defaults() {
        if(!isset($this->settings))
            $this->settings = array();

        $this->settings =
            (object)array_merge(
                array(
                    'gateway' =>  get_class($this),
                    'id' => $this->generate_id(),
                    'label' => '',
                    'use_label' => true,
                    'icon' => MEPR_IMAGES_URL . '/checkout/cards.png',
                    'use_icon' => true,
                    'desc' => __('Credit Card Payment', 'memberpress'),
                    'use_desc' => true,
                    'failure_page_id' => '',
                    'api_username' => '',
                    'api_password' => '',
                    'test_api_username' => '',
                    'test_api_password' => '',
                    'terminal_name' => "",
                    'signature' => '',
                    'tranzila_pw' => "",
                    'test_mode' => false,
                    'debug' => false,
                    'tranzila_page_url' => "https://direct.tranzila.com/",
                    'tranzila_handshake_url' => "https://secure5.tranzila.com/cgi-bin/tranzila71dt.cgi"
                ),
                (array)$this->settings
            );

        $this->id = $this->settings->id;
        $this->label = $this->settings->label;
        $this->use_label = $this->settings->use_label;
        $this->icon = $this->settings->icon;
        $this->use_icon = $this->settings->use_icon;
        $this->desc = $this->settings->desc;
        $this->use_desc = $this->settings->use_desc;

//        if($this->is_test_mode()) {
//            $this->settings->url     = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
//            $this->settings->api_url = 'https://api-3t.sandbox.paypal.com/nvp';
//        }
//        else {
//            $this->settings->url = 'https://www.paypal.com/cgi-bin/webscr';
//            $this->settings->api_url = 'https://api-3t.paypal.com/nvp';
//        }

//        $this->settings->api_version = 69;

        $this->capabilities = array(
            'process-payments',
            'create-subscriptions'
//                'update-subscriptions'
//                'subscription-trial-payment' //The trial payment doesn't have to be processed as a separate one-off like Authorize.net & Stripe
        );


    }

    /**
     * @inheritDoc
     */
    public function process_payment($transaction)
    {
        // TODO: Implement process_payment() method.
    }

    /**
     * @inheritDoc
     */
    public function record_payment()
    {
        // TODO: Implement record_payment() method.
    }

    /**
     * @inheritDoc
     */
    public function process_refund(MeprTransaction $txn)
    {
        // TODO: Implement process_refund() method.
    }

    /**
     * @inheritDoc
     */
    public function record_refund()
    {
        // TODO: Implement record_refund() method.
    }

    /**
     * @inheritDoc
     */
    public function record_subscription_payment()
    {
        // TODO: Implement record_subscription_payment() method.
    }

    /**
     * @inheritDoc
     */
    public function record_payment_failure()
    {
        // TODO: Implement record_payment_failure() method.
    }

    /**
     * @inheritDoc
     */
    public function process_trial_payment($transaction)
    {
        // TODO: Implement process_trial_payment() method.
    }

    /**
     * @inheritDoc
     */
    public function record_trial_payment($transaction)
    {
        // TODO: Implement record_trial_payment() method.
    }

    /**
     * @inheritDoc
     */
    public function process_create_subscription($transaction)
    {
        // TODO: Implement process_create_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function record_create_subscription()
    {
        // TODO: Implement record_create_subscription() method.
    }

    public function process_update_subscription($subscription_id)
    {
        // TODO: Implement process_update_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function record_update_subscription()
    {
        // TODO: Implement record_update_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function process_suspend_subscription($subscription_id)
    {
        // TODO: Implement process_suspend_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function record_suspend_subscription()
    {
        // TODO: Implement record_suspend_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function process_resume_subscription($subscription_id)
    {
        // TODO: Implement process_resume_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function record_resume_subscription()
    {
        // TODO: Implement record_resume_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function process_cancel_subscription($subscription_id)
    {
        // TODO: Implement process_cancel_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function record_cancel_subscription()
    {
        // TODO: Implement record_cancel_subscription() method.
    }

    /**
     * @inheritDoc
     */
    public function process_signup_form($txn)
    {
        // TODO: Implement process_signup_form() method.
    }
    private function send_handshake_request($args, $method = 'post', $blocking = true) {
        $mepr_options = MeprOptions::fetch();
        $args = array_merge(
            array(
                'TranzilaPW'    => $this->settings->tranzila_pw,
                'supplier'      => $this->settings->terminal_name,
                'op'            => '1'
            ),
            $args
        );

        $args = MeprHooks::apply_filters('mepr_tranzila_handshake_request_args', $args);

        $arg_array = MeprHooks::apply_filters('mepr_tranzila_handshake_request', array(
            'method'    => strtoupper($method),
            'body'      => $args,
            'timeout'   => 15,
            'httpversion' => '1.1', //PayPal is now requiring this
            'blocking'  => $blocking,
            'sslverify' => $mepr_options->sslverify,
            'headers'   => array()
        ));

        $resp = wp_remote_request($this->settings->tranzila_handshake_url, $arg_array);

        // If we're not blocking then the response is irrelevant
        // So we'll just return true.
        if($blocking == false)
            return true;

        if(is_wp_error($resp))
            throw new MeprHttpException(sprintf(__( 'You had an HTTP error connecting to %s' , 'memberpress'), $this->name));
        else
            return wp_parse_args($resp['body']);

        return false;
    }


    /** This gets called on the 'init' hook when the signup form is processed ...
     * this is in place so that payment solutions like tranzila page can redirect
     * before any content is rendered.
     */
    public function display_payment_page($txn) {
        $mepr_options = MeprOptions::fetch();

        if(isset($txn) && ($txn instanceof MeprTransaction)) {
            $prd = $txn->product();
        }
        else {
            throw new Exception(__('Sorry, we couldn\'t complete the transaction. Try back later.', 'memberpress'));
        }

        if($txn->amount <= 0.00) {
            MeprTransaction::create_free_transaction($txn);
            return;
        }

        if($txn->gateway == $this->id) {
            $payment_vars = $this->get_gateway_payment_args($txn);

            //handshake process w Tranzila
            $handshake_args = array( 'sum' => $txn->amount );
            $handshake_result = $this->send_handshake_request($handshake_args);

            $payment_vars = array_merge($payment_vars, $handshake_result);
            $_REQUEST['tranzila_payment_args'] = $payment_vars;
            return; //Uh yeah - don't forget this or we'll trigger the exception below
        }

        throw new Exception(__('Sorry, we couldn\'t complete the transaction. Try back later.', 'memberpress'));
    }
    /**
     * @inheritDoc
     */
    public function enqueue_payment_form_scripts()
    {
        // TODO: Implement enqueue_payment_form_scripts() method.
    }

    /**
     * This gets called on the_content and just renders the payment form
     * For Tranzila payment page we're loading up a hidden form and submitting it with JS (like in paypal)
     */
    public function display_payment_form($amount, $user, $product_id, $transaction_id) {




        $payment_vars = isset($_REQUEST['tranzila_payment_args'])?$_REQUEST['tranzila_payment_args']:array();

        if(empty($payment_vars)) {
            echo '<p id="tranzila_oops_message">';
            _ex('Woops, someting went wrong. Please try your purchase again.', 'ui', 'memberpress');
            echo '</p>';
        }

        //Show a message?
        ?>
        <p id="tranzila_redirecting_message"><img src="<?php echo includes_url('js/thickbox/loadingAnimation.gif'); ?>" width="250" />
            <br/>
            <?php _ex('You are being redirected to Tranzila.. please wait', 'ui', 'memberpress'); ?></p>
        <?php
        $page_url = $this->settings->tranzila_page_url.$this->settings->terminal_name."/";
        //Output the form YO
        echo '<form id="mepr_tranzila_form" action="'.$page_url.'" method="post">';
        foreach($payment_vars as $key => $val) {

            ?>

            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo esc_attr($val); ?>" />
            <?php

        }
        echo '</form>';

        //Javascript to force the form to submit
        ?>
        <script type="text/javascript">
            setTimeout(function() {
                document.getElementById("mepr_tranzila_form").submit();
            }, 1000); //Let's wait one second to let some stuff load up
        </script>
        <?php
    }

    /**
     * @inheritDoc
     */
    public function validate_payment_form($errors)
    {
        // TODO: Implement validate_payment_form() method.
    }




    /**
     * @inheritDoc
     */
    /** Displays the form for the given payment gateway on the MemberPress Options page */
    public function display_options_form() {

        $mepr_options = MeprOptions::fetch();

        $api_username       = trim($this->settings->api_username);
        $api_password       = trim($this->settings->api_password);
        $terminal_name      = trim($this->settings->terminal_name);
        $failure_page_id    = trim($this->settings->failure_page_id);
        $tranzila_pw        = trim($this->settings->tranzila_pw);
        $test_mode          = ($this->settings->test_mode == 'on' or $this->settings->test_mode == true);
        $debug              = ($this->settings->debug == 'on' or $this->settings->debug == true);

//        $this->notify_url('success'),
//$this->notify_url('failure'),
//$this->notify_url('notifier')

        ?>

        <table>

          <tr>
                <td><?php _e('API Username:', 'memberpress'); ?></td>
                <td><input type="text" class="mepr-auto-trim" name="<?php echo $mepr_options->integrations_str; ?>[<?php echo $this->id;?>][api_username]" value="<?php echo $api_username; ?>" /></td>
            </tr>
            <tr >
                <td><?php _e('API Password:', 'memberpress'); ?></em></td>
                <td><input type="text" class="mepr-auto-trim" name="<?php echo $mepr_options->integrations_str; ?>[<?php echo $this->id;?>][api_password]" value="<?php echo $api_password; ?>" /></td>
            </tr>

            <tr >
                <td><?php _e('Terminal Name:', 'memberpress'); ?></td>
                <td><input type="text" class="mepr-auto-trim" name="<?php echo $mepr_options->integrations_str; ?>[<?php echo $this->id;?>][terminal_name]" value="<?php echo $terminal_name; ?>" /></td>
            </tr>

            <tr >
                <td><?php _e('TranzilaPW (for handshake):', 'memberpress'); ?></td>
                <td><input type="text" class="mepr-auto-trim" name="<?php echo $mepr_options->integrations_str; ?>[<?php echo $this->id;?>][tranzila_pw]" value="<?php echo $tranzila_pw; ?>" /></td>
            </tr>

            <tr>
                <td><?php _e('Failure Page:', 'memberpress'); ?>*:</td>
                <td><?php MeprOptionsHelper::wp_pages_dropdown($mepr_options->integrations_str."[".$this->id."]"."[failure_page_id]", $failure_page_id, __('failure_page', 'memberpress')); ?></td>
            </tr>

            <tr>
                <td><?php _e('Tranzila Notify URL:', 'memberpress'); ?></td>
                <td><?php MeprAppHelper::clipboard_input($this->notify_url('notifier')); ?></td>
            </tr>
            <!-- THIS IS NOT ACTUALLY USED ANY LONGER - BUT IT IS REQUIRED FOR THE RETURN DATA TO BE SENT SO LEAVING IT IN PLACE FOR NOW -->
            <tr>
                <td><?php _e('Tranzila Success URL:', 'memberpress'); ?></td>
                <td><?php MeprAppHelper::clipboard_input($this->notify_url('success')); ?></td>
            </tr>

            <tr>
                <td><?php _e('Tranzila Failure URL:', 'memberpress'); ?></td>
                <td><?php MeprAppHelper::clipboard_input($this->notify_url('failure')); ?></td>
            </tr>

        </table>
        <?php
    }

    /** Validates the form for the given payment gateway on the MemberPress Options page */
    public function validate_options_form($errors) {
        $mepr_options = MeprOptions::fetch();

        if( !isset($_POST[$mepr_options->integrations_str][$this->id]['api_username']) or
            empty($_POST[$mepr_options->integrations_str][$this->id]['api_username']) ) {
            $errors[] = __("API username field can't be blank.", 'memberpress');
        }
        if( !isset($_POST[$mepr_options->integrations_str][$this->id]['api_password']) or
            empty($_POST[$mepr_options->integrations_str][$this->id]['api_password']) ) {
            $errors[] = __("API password field can't be blank.", 'memberpress');
        }

        return $errors;
    }



    /**
     * @inheritDoc
     */
    public function display_update_account_form($subscription_id, $errors = array(), $message = "")
    {
        // TODO: Implement display_update_account_form() method.
    }

    /**
     * @inheritDoc
     */
    public function validate_update_account_form($errors = array())
    {
        // TODO: Implement validate_update_account_form() method.
    }

    /**
     * @inheritDoc
     */
    public function process_update_account_form($subscription_id)
    {
        // TODO: Implement process_update_account_form() method.
    }

    /**
     * @inheritDoc
     */
    public function is_test_mode()
    {
        // TODO: Implement is_test_mode() method.
    }

    /**
     * @inheritDoc
     */
    public function force_ssl()
    {
        // TODO: Implement force_ssl() method.
    }

    private function get_gateway_payment_args($txn) {
        $mepr_options = MeprOptions::fetch();
        $prd = $txn->product();

        $payment_vars = array(
            'cred'                  => "1",
            'lang'                  => "il",
            'currency'              => "1",
            'pdesc'                 => $prd->post_title,
            'mempr_origin_url'      => $prd->url(),
            'sum'                   => $txn->total,
            'mempr_txn_id'          => $txn->id,
            'mempr_product_id'      => $txn->product_id,
            'success_url_address'   => $this->notify_url('success'),
            'fail_url_address'      => $this->notify_url('failure'),
            'notify_url_address'    => $this->notify_url('notifier'),
            'XDEBUG_SESSION_START'  => "PHPSTORM"

        );

        return $payment_vars;
    }

    public function success_handler() {
        $this->email_status("Paypal Return \$_REQUEST:\n".MeprUtils::object_to_string($_REQUEST, true)."\n", $this->settings->debug);

        $mepr_options = MeprOptions::fetch();

        //If PayPal gives us an item_number let's setup this txn now
        if(isset($_POST['mempr_txn_id']) && is_numeric($_POST['mempr_txn_id'])) {
            $txn      = new MeprTransaction((int)$_POST['mempr_txn_id']);
            $sub      = $txn->subscription();
            $product  = new MeprProduct($txn->product_id);

            //Did the IPN already beat us here?
            if(strpos($txn->trans_num, 'mp-txn') === false) {
                $sanitized_title = sanitize_title($product->post_title);
                $query_params = array('membership' => $sanitized_title, 'trans_num' => $txn->trans_num, 'membership_id' => $product->ID);
                if($txn->subscription_id > 0) {
                    $sub = $txn->subscription();
                    $query_params = array_merge($query_params, array('subscr_id' => $sub->subscr_id));
                }
                MeprUtils::wp_redirect($mepr_options->thankyou_page_url(build_query($query_params)));
            }

            //If $sub let's set this up as a confirmation txn until the IPN comes in later so the user can have access now
            if($sub) {
                $sub->status      = MeprSubscription::$active_str;
                $sub->created_at  = $txn->created_at; //Set the created at too
                $sub->store();

                if(!$mepr_options->disable_grace_init_days && $mepr_options->grace_init_days > 0) {
                    $expires_at = MeprUtils::ts_to_mysql_date(time() + MeprUtils::days($mepr_options->grace_init_days), 'Y-m-d 23:59:59');
                } else {
                    $expires_at = $txn->created_at; // Expire immediately
                }

                $txn->trans_num   = uniqid();
                $txn->txn_type    = MeprTransaction::$subscription_confirmation_str;
                $txn->status      = MeprTransaction::$confirmed_str;
                $txn->expires_at  = $expires_at;
                $txn->store(true);
            }
            else {
                //The amount can be fudged in the URL with PayPal Standard - so let's make sure no fudgyness is goin' on
                if(isset($_GET['amt']) && (float)$_GET['amt'] < (float)$txn->total) {
                    $txn->status    = MeprTransaction::$pending_str;
                    $txn->txn_type  = MeprTransaction::$payment_str;
                    $txn->store();
                    wp_die(_x('Your payment amount was lower than expected. Please contact us for assistance if necessary.', 'ui', 'memberpress') . ' <br/><a href="'.$mepr_options->account_page_url('action=subscriptions').'">View my Subscriptions</a>');
                }

                //Don't set a trans_num here - it will get updated when the IPN comes in
                $txn->txn_type    = MeprTransaction::$payment_str;
                $txn->status      = MeprTransaction::$complete_str;
                $txn->store();
            }

            $this->email_status("Paypal Transaction \$txn:\n".MeprUtils::object_to_string($txn, true)."\n", $this->settings->debug);

            $sanitized_title = sanitize_title($product->post_title);
            $query_params = array('membership' => $sanitized_title, 'trans_num' => $txn->trans_num, 'membership_id' => $product->ID);
            if($txn->subscription_id > 0) {
                $sub = $txn->subscription();
                $query_params = array_merge($query_params, array('subscr_id' => $sub->subscr_id));
            }
            MeprUtils::wp_redirect($mepr_options->thankyou_page_url(build_query($query_params)));
        }

        //Handle free trial periods here YO
        if(isset($_GET['free_trial_txn_id']) and is_numeric($_GET['free_trial_txn_id'])) {
            $free_trial_txn = new MeprTransaction((int)$_GET['free_trial_txn_id']);
            $fsub           = $free_trial_txn->subscription();
            $product        = new MeprProduct($free_trial_txn->product_id);

            //Did the IPN already beat us here?
            if(strpos($free_trial_txn->trans_num, 'mp-txn') === false) {
                $sanitized_title = sanitize_title($product->post_title);
                $query_params = array('membership' => $sanitized_title, 'trans_num' => $free_trial_txn->trans_num, 'membership_id' => $product->ID);
                if($free_trial_txn->subscription_id > 0) {
                    $sub = $free_trial_txn->subscription();
                    $query_params = array_merge($query_params, array('subscr_id' => $sub->subscr_id));
                }
                MeprUtils::wp_redirect($mepr_options->thankyou_page_url(build_query($query_params)));
            }

            //confirmation txn so the user can have access right away, instead of waiting for the IPN
            $free_trial_txn->set_subtotal(0.00);
            $free_trial_txn->txn_type   = MeprTransaction::$subscription_confirmation_str;
            $free_trial_txn->trans_num  = uniqid();
            $free_trial_txn->status     = MeprTransaction::$confirmed_str;
            $free_trial_txn->expires_at = MeprUtils::ts_to_mysql_date(time() + MeprUtils::days(1), 'Y-m-d 23:59:59');
            $free_trial_txn->store();

            $fsub->status     = MeprSubscription::$active_str;
            $fsub->created_at = $free_trial_txn->created_at; //Set the created at too
            $fsub->store();

            $this->email_status("Paypal Transaction \$free_trial_txn:\n".MeprUtils::object_to_string($free_trial_txn, true)."\n", $this->settings->debug);

            $sanitized_title = sanitize_title($product->post_title);
            $query_params = array('membership' => $sanitized_title, 'trans_num' => $free_trial_txn->trans_num, 'membership_id' => $product->ID);
            if($free_trial_txn->subscription_id > 0) {
                $sub = $free_trial_txn->subscription();
                $query_params = array_merge($query_params, array('subscr_id' => $sub->subscr_id));
            }
            MeprUtils::wp_redirect($mepr_options->thankyou_page_url(build_query($query_params)));
        }

        //If all else fails, just send them to their account page
        MeprUtils::wp_redirect($mepr_options->account_page_url('action=subscriptions'));
    }

    public function failure_handler()
    {
        $txn      = new MeprTransaction((int)$_POST['mempr_txn_id']);
        $txn->txn_type    = MeprTransaction::$payment_str;
        $txn->status      = MeprTransaction::$failed_str;
        $txn->store();

        MeprUtils::wp_redirect(MeprUtils::get_permalink($this->settings->failure_page_id));

    }
}

