<?php
 /**
  * Order Class.
  *
  * @link       https://powerfulwp.com
  * @since      1.0.0
  *
  * @package    Pdfclw
  * @subpackage Pdfclw/includes
  */

class Pdfclw_Order {

	/**
	 * Order pickup phone.
	 *
	 * @param object $order order obkect.
	 * @return void
	 */

	/**
	 * Order pickup phone
	 *
	 * @param string $pickup_phone pickup phone.
	 * @param object $order order object.
	 * @return string
	 */
	public function pickup_phone( $pickup_phone, $order ) {
		if ( ! empty( $order ) ) {
			$order_id  = $order->get_id();
			$address_1 = get_post_meta( $order_id, '_pdfclw_pickup_address_1', true );
			$city      = get_post_meta( $order_id, '_pdfclw_pickup_city', true );
			if ( '' !== $address_1 && '' !== $city ) {
				$pickup_phone = get_post_meta( $order_id, '_billing_phone', true );
			}
		}
		return $pickup_phone;
	}


	/**
	 * Order pickup type.
	 *
	 * @param string $pickup_type pickup_type.
	 * @param object $order order object.
	 * @return string
	 */
	public function pickup_type( $pickup_type, $order ) {
		if ( ! empty( $order ) ) {
			$order_id  = $order->get_id();
			$address_1 = get_post_meta( $order_id, '_pdfclw_pickup_address_1', true );
			$city      = get_post_meta( $order_id, '_pdfclw_pickup_city', true );
			if ( '' !== $address_1 && '' !== $city ) {
				$pickup_type = 'customer';
			}
		}
		return $pickup_type;
	}


	/**
	 * Order Pickup location.
	 *
	 * @param string $format address format.
	 * @param object $order order object.
	 * @param int    $seller_id sellet id.
	 * @param string $address address.
	 * @return string
	 */
	public function order_pickup_loction( $format, $order, $seller_id, $address ) {
		if ( ! empty( $order ) ) {

			$order_id  = $order->get_id();
			$address_1 = get_post_meta( $order_id, '_pdfclw_pickup_address_1', true );
			$city      = get_post_meta( $order_id, '_pdfclw_pickup_city', true );

			if ( '' !== $address_1 && '' !== $city ) {
				// Customer pickup location.
				$first_name = get_post_meta( $order_id, '_pdfclw_pickup_first_name', true );
				$last_name  = get_post_meta( $order_id, '_pdfclw_pickup_last_name', true );
				$company    = get_post_meta( $order_id, '_pdfclw_pickup_company', true );
				$address_2  = get_post_meta( $order_id, '_pdfclw_pickup_address_2', true );
				$postcode   = get_post_meta( $order_id, '_pdfclw_pickup_postcode', true );
				$country    = get_post_meta( $order_id, '_pdfclw_pickup_country', true );
				$state      = get_post_meta( $order_id, '_pdfclw_pickup_state', true );

				if ( '' !== $country ) {
					$state = pdfclw_states( $country, $state );
				}

				$array   = array(
					'first_name' => $first_name,
					'last_name'  => $last_name,
					'company'    => $company,
					'street_1'   => $address_1,
					'street_2'   => $address_2,
					'city'       => $city,
					'zip'        => $postcode,
					'country'    => '' !== $country ? WC()->countries->countries[ $country ] : '',
					'state'      => $state,
				);
				$address = pdfclw_format_address( $format, $array );
			}
		}
		return $address;
	}

	/**
	 * Function that return order seller id.
	 *
	 * @param object $order order.
	 * @return string
	 */
	public function order_seller( $order ) {
		$result = '';

		global $wpdb;
		$order_id = $order->get_id();
		switch ( PDFCLW_MULTIVENDOR ) {
			case 'dokan':
				$result = $order->get_meta( '_dokan_vendor_id' );
				break;
			case 'wcmp':
				$result = $order->get_meta( '_vendor_id' );
				break;
			case 'wcfm':
				$query = $wpdb->get_results(
					$wpdb->prepare(
						'select vendor_id from ' . $wpdb->prefix . 'wcfm_marketplace_orders where order_id=%s',
						array( $order_id )
					)
				);
				if ( ! empty( $query ) ) {
					$result = $query[0]->vendor_id;
				}
				break;
			default:
				$result = '';
				break;
		}
		return $result;
	}
}


