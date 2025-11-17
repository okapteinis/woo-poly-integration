<?php
/**
 * This file is part of the hyyan/woo-poly-integration plugin.
 * (c) Hyyan Abo Fakher <hyyanaf@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Hyyan\WPI
 */

namespace Hyyan\WPI\Security;

/**
 * Nonce.
 *
 * Security helper class for CSRF protection using WordPress nonces
 *
 * @author Claude Code AI
 * @since 1.7.0
 */
class Nonce {

	/**
	 * Generate a nonce field for forms.
	 *
	 * @since 1.7.0
	 * @param string $action Action name for nonce verification.
	 * @param string $name   Optional. Nonce field name. Default '_wpinonce'.
	 * @param bool   $referer Optional. Whether to also add referer field. Default true.
	 * @param bool   $output   Optional. Whether to echo or return. Default true.
	 * @return string Nonce field HTML.
	 */
	public static function field( $action, $name = '_wpinonce', $referer = true, $output = true ) {
		return wp_nonce_field( $action, $name, $referer, $output );
	}

	/**
	 * Verify a nonce.
	 *
	 * @since 1.7.0
	 * @param string $nonce  Nonce value to verify.
	 * @param string $action Action name used when creating nonce.
	 * @return bool|int False if nonce invalid, 1 if valid and generated 0-12 hours ago,
	 *                  2 if valid and generated 12-24 hours ago.
	 */
	public static function verify( $nonce, $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Verify nonce from request and die if invalid.
	 *
	 * @since 1.7.0
	 * @param string $action Action name used when creating nonce.
	 * @param string $name   Optional. Nonce field name. Default '_wpinonce'.
	 * @param string $method Optional. Request method (POST, GET, REQUEST). Default 'POST'.
	 * @return void Dies with error message if nonce invalid.
	 */
	public static function verify_or_die( $action, $name = '_wpinonce', $method = 'POST' ) {
		$request = null;

		switch ( strtoupper( $method ) ) {
			case 'POST':
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This function itself verifies nonces.
				$request = $_POST;
				break;
			case 'GET':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This function itself verifies nonces.
				$request = $_GET;
				break;
			case 'REQUEST':
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- This function itself verifies nonces.
				$request = $_REQUEST;
				break;
			default:
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- This function itself verifies nonces.
				$request = $_POST;
		}

		if ( ! isset( $request[ $name ] ) || ! self::verify( $request[ $name ], $action ) ) {
			wp_die(
				esc_html__( 'Security check failed. Please try again.', 'woo-poly-integration' ),
				esc_html__( 'Security Error', 'woo-poly-integration' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Verify AJAX nonce and die if invalid.
	 *
	 * @since 1.7.0
	 * @param string $action Action name used when creating nonce.
	 * @param string $name   Optional. Nonce field name. Default '_wpinonce'.
	 * @param bool   $terminate    Optional. Whether to die on failure. Default true.
	 * @return bool|int False if nonce invalid and $terminate is false,
	 *                  1 if valid and generated 0-12 hours ago,
	 *                  2 if valid and generated 12-24 hours ago.
	 */
	public static function verify_ajax( $action, $name = '_wpinonce', $terminate = true ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- This function itself verifies nonces, and nonce values should not be sanitized.
		$nonce = isset( $_REQUEST[ $name ] ) ? wp_unslash( $_REQUEST[ $name ] ) : '';

		if ( $terminate ) {
			check_ajax_referer( $action, $name );
			return true;
		}

		return self::verify( $nonce, $action );
	}

	/**
	 * Create a nonce.
	 *
	 * @since 1.7.0
	 * @param string $action Action name.
	 * @return string The nonce token.
	 */
	public static function create( $action ) {
		return wp_create_nonce( $action );
	}

	/**
	 * Get nonce URL.
	 *
	 * @since 1.7.0
	 * @param string $url    URL to add nonce to.
	 * @param string $action Action name.
	 * @param string $name   Optional. Nonce parameter name. Default '_wpnonce'.
	 * @return string URL with nonce added.
	 */
	public static function url( $url, $action, $name = '_wpnonce' ) {
		return wp_nonce_url( $url, $action, $name );
	}
}
