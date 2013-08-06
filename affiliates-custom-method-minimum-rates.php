<?php
/**
 * Plugin Name: Affiliates Custom Method Minimums with Rates
 * Description: Implements an example method with referral amounts based on transaction minimums for use with Affiliates Pro.
 * Version: 1.0.0
 * Author: itthinx
 * Author URI: http://www.itthinx.com
 */
class ACMMinRates {

  /**
	 * Commission rates based on minimum transaction amounts.
	 * Here, a minimum order of 1000$ rewards 10%, 2000$ rewards 20% and
	 * 3000$ rewards 30% (the currency is that of the order).
	 * 
	 * The LAST matching rate is applied.
	 */
	public static $rates = array(
		'1000'  => 0.10,
		'2000'  => 0.20,
		'3000'  => 0.30
	);

	/**
	 * Registers a custom referral amount method.
	 */
	public static function init() {
		add_filter( 'affiliates_record_referral', array( __CLASS__, 'affiliates_record_referral' ), 10, 2 );
		Affiliates_Referral::register_referral_amount_method( array( __CLASS__, 'custom_method' ) );
	}

	/**
	 * Don't record 0 referrals (when below limit).
	 * 
	 * @param boolean $record_referral
	 * @param array $referral_data
	 */
	public static function affiliates_record_referral( $record_referral, $referral_data ) {
		if ( $record_referral ) {
			if ( isset( $referral_data['amount'] ) ) {
				if ( bccomp( $referral_data['amount'], '0') <= 0 ) {
					$record_referral = false;
				}
			}
		}
		return $record_referral;
	}

	/**
	 * Custom referral amount method implementation.
	 * @param int $affiliate_id
	 * @param array $parameters
	 */
	public static function custom_method( $affiliate_id = null, $parameters = null ) {
		$result = '0';
		if ( isset( $parameters['base_amount'] ) ) {
			$result = self::get_referral_amount( $parameters['base_amount'] );
		}
		return $result;
	}

	/**
	 * Returns the referral amount for a given base amount.
	 * @param string $base_amount
	 * @return string referral amount
	 */
	public static function get_referral_amount( $base_amount ) {
		$result = '0';
		// iterate over all, the last match gives the result
		foreach ( self::$rates as $limit => $rate ) {
			if ( bccomp( $base_amount, $limit ) >= 0 ) {
				$result = bcmul( $base_amount, $rate, 2 );
			}
		}
		return $result;
	}
}
add_action( 'init', array( 'ACMMinRates', 'init' ) );
