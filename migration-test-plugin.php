<?php
/**
 * Plugin headers
 * Plugin Name: Content Control Migration Tester
 * Plugin URI:
 * Description: Test the migration from Content Control v1 to v2
 * Version: 1.0.0
 * Author: Code Atlantic
 * Author URI: https://code-atlantic.com/
 * Text Domain: content-control-migration-tester
 * Domain Path: /languages
 * License: GPLv2 or later
 */

namespace ContentControlMigrationTester;

defined( 'ABSPATH' ) || exit;

define( 'CONTENT_CONTROL_LOGGING', true );

define( 'TRUSTEDLOGIN_DISABLE_LOCAL_NOTICE', true );

// Add admin bar menu item and sub items to reset v1 data, clear v2 data etc.
\add_action( 'admin_bar_menu', __NAMESPACE__ . '\add_admin_bar_menu_item', 999 );

/**
 * Add admin bar menu item and sub items to reset v1 data, clear v2 data etc.
 *
 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function add_admin_bar_menu_item( $wp_admin_bar ) {
	$nonce = \wp_create_nonce( 'content-control-migration-tester' );

	$wp_admin_bar->add_menu(
		[
			'id'    => 'content-control-migration-tester',
			'title' => 'Content Control Tester',
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-plugins-v1',
			'title'  => 'Activate v1',
			'parent' => 'content-control-migration-tester',
			'href'   => \add_query_arg(
				[
					'cc_action' => 'activate_v1',
					'cc_nonce'  => $nonce,
				]
			),
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-plugins-v2',
			'title'  => 'Activate v2',
			'parent' => 'content-control-migration-tester',
			'href'   => \add_query_arg(
				[
					'cc_action' => 'activate_v2',
					'cc_nonce'  => $nonce,
				]
			),
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-data',
			'title'  => 'Load/Save/Reset Data',
			'parent' => 'content-control-migration-tester',
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-clean_install',
			'title'  => 'Clean Install',
			'parent' => 'content-control-migration-tester-data',
			'href'   => \add_query_arg(
				[
					'cc_action' => 'clean_install',
					'cc_nonce'  => $nonce,
				]
			),
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-clear_completed_upgrades',
			'title'  => 'Clear Completed Upgrades',
			'parent' => 'content-control-migration-tester-data',
			'href'   => \add_query_arg(
				[
					'cc_action' => 'clear_completed_upgrades',
					'cc_nonce'  => $nonce,
				]
			),
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-v1-separator',
			'title'  => '---- V1 Data ----',
			'parent' => 'content-control-migration-tester-data',
			'href'   => '#',
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-load_v1_data',
			'title'  => 'Load v1 Data',
			'parent' => 'content-control-migration-tester-data',
			'href'   => \add_query_arg(
				[
					'cc_action' => 'load_v1_data',
					'cc_nonce'  => $nonce,
				]
			),
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-save_v1_data',
			'title'  => 'Save v1 Data',
			'parent' => 'content-control-migration-tester-data',
			'href'   => \add_query_arg(
				[
					'cc_action' => 'save_v1_data',
					'cc_nonce'  => $nonce,
				]
			),
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-delete_v1_data',
			'title'  => 'Delete v1 Data',
			'parent' => 'content-control-migration-tester-data',
			'href'   => \add_query_arg(
				[
					'cc_action' => 'delete_v1_data',
					'cc_nonce'  => $nonce,
				]
			),
		]
	);

	// Add Separator
	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-v2',
			'title'  => '---- V2 Data ----',
			'parent' => 'content-control-migration-tester-data',
			'href'   => '#',
		]
	);

	$wp_admin_bar->add_menu(
		[
			'id'     => 'content-control-migration-tester-delete_v2_data',
			'title'  => 'Delete v2 Data',
			'parent' => 'content-control-migration-tester-data',
			'href'   => \add_query_arg(
				[
					'cc_action' => 'delete_v2_data',
					'cc_nonce'  => $nonce,
				]
			),
		]
	);
}

// Listen for the admin bar menu item clicks.
\add_action( 'plugins_loaded', __NAMESPACE__ . '\listen_for_admin_bar_menu_item_clicks', -100 );

/**
 * Listen for admin bar menu clicks.
 *
 * @return void
 */
function listen_for_admin_bar_menu_item_clicks() {
	if ( ! isset( $_GET['cc_action'], $_GET['cc_nonce'] ) ) {
		return;
	}

	if ( ! \wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['cc_nonce'] ) ), 'content-control-migration-tester' ) ) {
		return;
	}

	if ( ! function_exists( 'activate_plugin' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	switch ( $_GET['cc_action'] ) {
		case 'activate_v1':
			deactivate_plugins( 'content-control/content-control.php', true );
			activate_plugin( 'content-control-old/content-control-old.php', admin_url( 'options-general.php?page=jp-cc-settings' ), false, true );
			return;
		case 'activate_v2':
			deactivate_plugins( 'content-control-old/content-control-old.php', true );
			activate_plugin( 'content-control/content-control.php', admin_url( 'options-general.php?page=content-control-settings' ), false, true );
			return;
		case 'save_v1_data':
			save_v1_data();
			break;
		case 'delete_v1_data':
			delete_v1_data();
			set_data_versioning( 2 );
			break;
		case 'load_v1_data':
			clean_install();
			load_v1_data();
			break;
		case 'delete_v2_data':
			delete_v2_data();
			break;
		case 'clean_install':
			clean_install();
			break;
		case 'clear_completed_upgrades':
			clear_completed_upgrades();
			break;
	}

	\wp_safe_redirect( \remove_query_arg( [ 'cc_action', 'cc_nonce' ] ) );
}

/**
 * Delete all plugin data.
 *
 * @return void
 */
function clean_install() {
	delete_v1_data();
	delete_v2_data();
}

/**
 * Delete all v1 data.
 *
 * @return void
 */
function delete_v1_data() {
	$option_keys = [
		'jp_cc_settings',
		'jp_cc_reviews_installed_on',
	];

	foreach ( $option_keys as $option_key ) {
		\delete_option( $option_key );
	}

	$user_meta_keys = [
		'_jp_cc_reviews_dismissed_triggers',
		'_jp_cc_reviews_last_dismissed',
		'_jp_cc_reviews_already_did',
	];

	foreach ( $user_meta_keys as $user_meta_key ) {
		\delete_user_meta( 1, $user_meta_key );
	}

	$transient_keys = [
		'content_control_installed',
	];

	foreach ( $transient_keys as $transient ) {
		\delete_transient( $transient );
	}
}

/**
 * Save v1 data to a json file.
 *
 * @return void
 */
function save_v1_data() {
	$data = [];

	$data['settings'] = \get_option( 'jp_cc_settings', [] );

	$data['user_meta'] = [
		'_jp_cc_reviews_dismissed_triggers' => \get_user_meta( 1, '_jp_cc_reviews_dismissed_triggers', true ),
		'_jp_cc_reviews_last_dismissed'     => \get_user_meta( 1, '_jp_cc_reviews_last_dismissed', true ),
		'_jp_cc_reviews_already_did'        => \get_user_meta( 1, '_jp_cc_reviews_already_did', true ),
	];

	$data['plugin_meta'] = [
		'jp_cc_reviews_installed_on' => \get_option( 'jp_cc_reviews_installed_on' ),
	];

	// Save to file in the same folder as v1_data.json.
	\file_put_contents( __DIR__ . '/v1_data.json', \wp_json_encode( $data ) );
}

/**
 * Load v1 data from a json file.
 *
 * @return void
 */
function load_v1_data() {
	$data = \json_decode( \file_get_contents( __DIR__ . '/v1_data.json' ), true );

	\update_option( 'jp_cc_settings', $data['settings'] );

	foreach ( $data['user_meta'] as $meta_key => $meta_value ) {
		if ( ! empty( $meta_value ) ) {
			\update_user_meta( 1, $meta_key, $meta_value );
		}
	}

	foreach ( $data['plugin_meta'] as $meta_key => $meta_value ) {
		\update_option( $meta_key, $meta_value );
	}
}

/**
 * Delete all v2 data.
 *
 * @return void
 */
function delete_v2_data() {
	// Delete all cc_restriction post type items.
	$restriction_post_ids = \get_posts(
		[
			'post_type'      => 'cc_restriction',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => 'any',
		]
	);

	foreach ( $restriction_post_ids as $post_id ) {
		\wp_delete_post( $post_id, true );
	}

	$transient_keys = [
		'content_control_installed',
	];

	foreach ( $transient_keys as $transient ) {
		\delete_transient( $transient );
	}

	// Delete the log file if it exists.
	if ( function_exists( '\ContentControl\plugin' ) ) {
		\ContentControl\plugin( 'logging' )->delete_logs();
	}

	$option_keys = [
		'content_control_settings',
		'content_control_version',
		'content_control_activated',
		'content_control_data_versioning',
		'content_control_debug_log_token', // delete log first.
		'content_control_installed_on',
		'content_control_known_blockTypes',
		'content_control_pro_version',
		'content_control_completed_upgrades',
		'content_control_license',
		'content_control_pro_activation_date',
		'content_control_connect_token',
	];

	foreach ( $option_keys as $option ) {
		\delete_option( $option );
	}

	$site_option = [
		'content_control_activated',
	];

	foreach ( $site_option as $option ) {
		\delete_site_option( $option );
	}
}

function set_data_versioning( $v = 1 ) {
	\update_option(
		'content_control_data_versioning', [
			'settings'     => $v,
			'restrictions' => $v,
			'user_meta'    => $v,
			'plugin_meta'  => $v,
		]
	);
}

function clear_completed_upgrades() {
	\delete_option( 'content_control_completed_upgrades' );
}
