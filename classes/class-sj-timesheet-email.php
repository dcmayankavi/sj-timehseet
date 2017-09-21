<?php

if ( ! class_exists( 'SJ_Timesheet_Email' ) ) {

	/**
	 * Responsible for setting up builder constants, classes and includes.
	 *
	 * @since 1.8
	 */
	final class SJ_Timesheet_Email {

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
			add_action( 'init', array( $this, 'send_mail' ) );
		}

		/**
		 * Loads classes and includes.
		 *
		 * @since 1.0
		 * @return void
		 */
		function send_mail() {

			$body = '<table border="1" cellspacing="0" cellpadding="10">
				<tbody align="center">
					<tr>
						<td rowspan="2">Project Name</td>
						<td rowspan="2">Task</td>
						<td colspan="2">Time</td>
						<td rowspan="2">Status</td>
					</tr>
					<tr>
						<td>Hours</td>
						<td>Minutes</td>
					</tr>
					<tr>
						<td>Astra</td>
						<td align="left">#Blog Pro</td>
						<td>9</td>
						<td>10</td>
						<td>Inprogress</td>
					</tr>
					<tr>
						<td>Break</td>
						<td align="left">
			            	<br>#Breakfast
			                <br>
			                <br>#Lunch
			                <br>
			                <br>#Teabreak
			           	</td>
						<td>0</td>
						<td>20
			            	<br>
			            	<br>30
			            	<br>
			            	<br>15
			            </td>
						<td></td>
					</tr>
				</tbody>
			</table>'; 

			$to 		= 'sandeshj@bsf.io';
			$subject 	= 'Test Timesheet';
			$headers 	= array('Content-Type: text/html; charset=UTF-8');
			
		
			//wp_mail( $to, $subject, $body, $headers );
		}
	}
}

SJ_Timesheet_Email::get_instance();