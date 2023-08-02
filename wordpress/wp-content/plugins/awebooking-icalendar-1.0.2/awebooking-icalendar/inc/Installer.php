<?php
namespace AweBooking\ICalendar;

class Installer {
	/**
	 * The cron hook identifier.
	 *
	 * @var string
	 */
	protected static $cron_hook_identifier = 'awebooking/cron_icalendar_synchronized';

	/**
	 * Active the addon.
	 *
	 * @return void
	 */
	public static function activate() {
		static::create_tables();

		if ( ! wp_next_scheduled( static::$cron_hook_identifier ) ) {
			wp_schedule_event( time(), 'hourly', static::$cron_hook_identifier );
		}
	}

	/**
	 * Deactivate the addon.
	 *
	 * @return void
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( static::$cron_hook_identifier );
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * @see https://codex.wordpress.org/Creating_Tables_with_Plugins
	 */
	public static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( static::get_schema() );
	}

	/**
	 * Get Table schema.
	 *
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "
CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}awebooking_ical_entities` (
	`eid`         VARCHAR(128)        NOT NULL     COMMENT 'Event Identifier',
	`unit_id`     BIGINT(10) UNSIGNED NOT NULL     COMMENT 'ID of the Unit',
	`entity_id`   BIGINT(10) UNSIGNED NOT NULL     COMMENT 'Entity ID',
	`entity_type` VARCHAR(128)        NOT NULL     COMMENT 'Entity type',
	`ical_type`   VARCHAR(128)        DEFAULT NULL COMMENT 'iCal type (e.g. airbnb, homeaway)',
	`start_date`  DATETIME            DEFAULT NULL COMMENT 'Event start date',
	`end_date`    DATETIME            DEFAULT NULL COMMENT 'Event end date',
	PRIMARY KEY (`eid`, `entity_type`),
	KEY `unit_id` (`unit_id`)
) $collate;
		";

		return $tables;
	}
}
