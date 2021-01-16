<?php

/**
 * @snippet       Timezone Fix for MemberPress Coupons
 * @sourcecode    https://github.com/spiderjay/MemberPressCoupons-Timezone
 * @author        Jay Phillips
 * @compatible    Wordpress 5.6, MemberPress Plus 1.9.10
 */

/**
 * Display a notice when snippet is enabled
 */

function fix_membpress_snippet_notice() {
	if ( get_post_type() == 'memberpresscoupon' ) {
		$tz = wp_timezone_string();
?>
<div class="notice notice-info is-dismissible">
	<p><?php _e( 'The <strong>Timezone Fix for MemberPress Coupons Snippet</strong> is enabled. 
			Coupons will run to/from midnight in the following timezone: <strong>'.$tz.'</strong>', 'alwk-membpress-snippet' ); ?></p>
</div>
<?php
	}
}	
add_action( 'admin_notices', 'fix_membpress_snippet_notice', 10 );	

/**
 * Fix the timestamp when saving coupons
 */

function fix_membpress_coupon_timezone($coupon){
	
	// get/set the timezone setting from wordpress
	$tz = wp_timezone_string();
	date_default_timezone_set($tz);

	// create datetime object 
	$dt = new DateTime();
	
	// check for start date
	if ($coupon->rec->should_start) {
		// original start date in UTC
		$utc_start = $coupon->rec->starts_on;
		$dt->setTimestamp($utc_start);

		// create new start date using correct timezone 
		$new_start = mktime(23, 59, 59, $dt->format('m'), $dt->format('d'), $dt->format('Y'));
		
		$coupon->rec->starts_on = $new_start;
	}
	
	// check for expiry date
	if ($coupon->rec->should_expire) {
		// original expiry in UTC
		$utc_ends = $coupon->rec->expires_on;
	
		$dt->setTimestamp($utc_ends);
		
		// create new expiry date using correct timezone
		$new_end = mktime(23, 59, 59, $dt->format('m'), $dt->format('d'), $dt->format('Y'));
		
		$coupon->rec->expires_on = $new_end;
	}
	
	// save changes
	$coupon->store_meta();
}
add_action('mepr-coupon-save-meta', 'fix_membpress_coupon_timezone', 20, 1);

/**
 * Fix the date input fields when editing coupons
 */

function fix_membpress_coupon_javascript() {

	if ( get_post_type() == 'memberpresscoupon' ) {
				
		$c = new MeprCoupon(get_the_ID());
	
		// check for a start date
		$start = $c->rec->starts_on;
		
		// check for an expiry
		$expiry = $c->rec->expires_on;
		
		if ($start || $expiry) {
			
			// get/set the timezone setting from wordpress
			$tz = wp_timezone_string();
			date_default_timezone_set($tz);
		
			// create datetime object 
			$dt = new DateTime();
			
			if ($start) {

				$dt->setTimestamp($start);
				$dt->setTimezone(new DateTimeZone($tz));
				$st_dy = $dt->format('j');
				$st_mn = $dt->format('n');
				$st_yr = $dt->format('Y');
			} 
			if ($expiry) {
				$dt->setTimestamp($expiry);
				$dt->setTimezone(new DateTimeZone($tz));
				$ex_dy = $dt->format('j');
				$ex_mn = $dt->format('n');
				$ex_yr = $dt->format('Y');
			}
?>
    <script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function() {
			/* MemberPress Coupon Post Admin */
			if (/[?&]action=edit/.test(location.search)) {
				/* Editing existing coupon */
				
<?php 	if ($start) { ?>
				
				document.getElementsByName('mepr_coupons_start_day')[0].value = <?php echo $st_dy; ?>;
				document.getElementsByName('mepr_coupons_start_month')[0].value = <?php echo $st_mn; ?>;
				document.getElementsByName('mepr_coupons_start_year')[0].value = <?php echo $st_yr; ?>;
									
<?php 	} 
			
		if ($expiry) { ?>
						
				document.getElementsByName('mepr_coupons_ex_day')[0].value = <?php echo $ex_dy; ?>;
				document.getElementsByName('mepr_coupons_ex_month')[0].value = <?php echo $ex_mn; ?>;
				document.getElementsByName('mepr_coupons_ex_year')[0].value = <?php echo $ex_yr; ?>;
				document.querySelector('#mepr_expire_coupon_box td strong').textContent = 'Midnight <?php echo $tz; ?>';
	
<?php 	} ?>
					
			}
		});
	</script>
<?php
		}
	}
}
add_action( 'admin_footer', 'fix_membpress_coupon_javascript' );
