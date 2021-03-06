<?php
/**
 * BNFW AJAX Helper functions.
 *
 * @class   BNFW_AJAX
 * @package bnfw
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BNFW_AJAX', false ) ) {
	/**
	 * BNFW_AJAX class.
	 */
	class BNFW_AJAX {
		/**
		 * Hook in ajax handlers.
		 */
		public static function init() {
			add_action( 'wp_ajax_bnfw_search_users', array( __CLASS__, 'bnfw_search_users' ) );
		}
		/**
		 * BNFW Search User AJAX Handler.
		 */
		public static function bnfw_search_users() {
			check_ajax_referer( 'bnfw_users_search_ajax_nonce', 'bnfw_security' );
			if ( ! current_user_can( 'bnfw' ) ) {
				wp_die( -1 );
			}
			global $wp_roles;
			$roles_data = array();
			$user_count = count_users();
			$roles      = $wp_roles->get_names();
			/**
			 * GM - ms
			 * Membership Plans
			 */
			$mem_plans_data = array();
			$mem_plans = wc_memberships_get_membership_plans();
			foreach ( $mem_plans as $plan ) {
				$mem_plans_data[] = array(
					'id'   => 'wc_mp_' . $plan->get_id(),
					'text' => esc_html( $plan->get_name() ) . ' (#' . $plan->get_id() . ')',
				);
			}
			$data = array(
				array(
					'id'       => 1,
					'text'     => esc_html__( 'Membership Plans', 'bnfw' ),
					'children' => $mem_plans_data,
				),
			);
			/** End of Membership Plans */
			foreach ( $roles as $role_slug => $role_name ) {
				$count = 0;
				if ( isset( $user_count['avail_roles'][ $role_slug ] ) ) {
					$count = $user_count['avail_roles'][ $role_slug ];
				}
				$roles_data[] = array(
					'id'   => 'role-' . $role_slug,
					'text' => $role_name . ' (' . $count . ' Users)',
				);
			}
			$data[] = array(
				'id'       => 2,
				'text'     => esc_html__( 'User Roles', 'bnfw' ),
				'children' => $roles_data,
			);
			$query     = isset( $_GET['query'] ) ? sanitize_text_field( wp_unslash( $_GET['query'] ) ) : '';
			$users     = get_users(
				array(
					'order_by' => 'email',
					'search'   => "$query*",
					'number'   => 100,
					'fields'   => array( 'ID', 'user_login' ),
				)
			);
			$user_data = array();
			foreach ( $users as $user ) {
				$user_data[] = array(
					'id'   => $user->ID,
					'text' => $user->user_login,
				);
			}
			$data[] = array(
				'id'       => 3,
				'text'     => esc_html__( 'Users', 'bnfw' ),
				'children' => $user_data,
			);
			echo wp_json_encode( $data );
			wp_die();
		}
	}
	BNFW_AJAX::init();
}
