<?php
/**
 * Plugin Name: SmartCoin (payment Gateway)
 * Plugin URI: https://www.smartcoin.com.br
 * Description: Provides payment method for credit card through SmartCoin to WooCommerce.
 * Version: 0.1
 * Author: Arthur Granado
 * Author URI: https://www.smartcoin.com.br
 * License: MIT
*/

function smartcoin_init_your_gateway() {
  if(class_exists('WC_Payment_Gateway')) {
    include_once('smartcoin_gateway.php');
  }
}

add_action('plugins_loaded', 'smartcoin_init_your_gateway',0);

wp_enqueue_script( 'sift_science', sift_js(), array( 'jquery' ));

function sift_js() {
	create_user_session();
	$smartcoin_sys_id = 'bf65ad0591';

	echo "
	<script>
		var _user_id = '';
	  var _session_id = '" . $_COOKIE['smartcoin_sys'] . "';

	  var _sift = _sift || [];
	  _sift.push(['_setAccount', '" . $smartcoin_sys_id . "']);
	  _sift.push(['_setUserId', _user_id]);
	  _sift.push(['_setSessionId', _session_id]);
	  _sift.push(['_trackPageview']);
	  (function() {
	    function ls() {
	      var e = document.createElement('script');
	      e.type = 'text/javascript';
	      e.async = true;
	      e.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.siftscience.com/s.js';
	      var s = document.getElementsByTagName('script')[0];
	      s.parentNode.insertBefore(e, s);
	    }
	    if (window.attachEvent) {
	      window.attachEvent('onload', ls);
	    } else {
	      window.addEventListener('load', ls, false);
	    }
	  })();
	</script>";
}

function create_user_session() {
  error_log("before set cookie" . $_COOKIE['smartcoin_sys']);
  if (!isset($_COOKIE['smartcoin_sys'])) {
    error_log('it will set new cookie');
    setcookie('smartcoin_sys', uniqid('sc_'), 0, COOKIEPATH, COOKIE_DOMAIN, false);
  }
}
