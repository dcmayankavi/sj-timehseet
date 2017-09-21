<?php

if ( ! class_exists( 'SJ_Timesheet_Loader' ) ) {

	/**
	 * Responsible for setting up builder constants, classes and includes.
	 *
	 * @since 1.8
	 */
	final class SJ_Timesheet_Loader {

		/**
		 * Member Variable
		 *
		 * @var object instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Load the builder if it's not already loaded, otherwise
		 * show an admin notice.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function __construct() {
			$this->define_constants();
			$this->load_files();
		}

		/**
		 * Define constants.
		 *
		 * @since 1.0
		 * @return void
		 */
		private function define_constants() {
			define('SJ_TIMESHEET_VER', '0.0.1');
			define('SJ_TIMESHEET_FILE', trailingslashit(dirname(dirname(__FILE__))) . 'timesheet.php');
			define('SJ_TIMESHEET_DIR', plugin_dir_path(SJ_TIMESHEET_FILE));
			define('SJ_TIMESHEET_URL', plugins_url('/', SJ_TIMESHEET_FILE));
		}

		/**
		 * Loads classes and includes.
		 *
		 * @since 1.0
		 * @return void
		 */
		private function load_files() {
			/* Classes */
			require_once SJ_TIMESHEET_DIR . 'classes/class-sj-timesheet-shortcode.php';
			require_once SJ_TIMESHEET_DIR . 'classes/class-sj-timesheet-email.php';
		}
	}
}

SJ_Timesheet_Loader::get_instance();