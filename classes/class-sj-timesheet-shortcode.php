<?php

if ( ! class_exists( 'SJ_Timesheet_Shortcode' ) ) {

	/**
	 * Responsible for setting up builder constants, classes and includes.
	 *
	 * @since 1.8
	 */
	final class SJ_Timesheet_Shortcode {

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
			add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
			add_shortcode( 'sj-timesheet',  array( $this, 'add_shortcode' ) );
		}

		function add_scripts() {
			wp_register_style( 'sj-timesheet-style', SJ_TIMESHEET_URL . 'assets/timesheet.css', array(), SJ_TIMESHEET_VER );
			wp_enqueue_style( 'sj-timesheet-style' );

			wp_register_script( 'sj-timesheet-script', SJ_TIMESHEET_URL . 'assets/timesheet.js', array( 'jquery' ), SJ_TIMESHEET_VER, true );
			wp_enqueue_script( 'sj-timesheet-script' );
		}

		/**
		 * Loads classes and includes.
		 *
		 * @since 1.0
		 * @return void
		 */
		function add_shortcode( $atts ) {
			
		    $args = shortcode_atts( array(
		        'form' 		=> 'no form',
		        'to_email'	=> 'sandeshj@bsf.io'
		    ), $atts );
		    
		    date_default_timezone_set('Asia/Kolkata');
			
			$option_name 	= 'sj_timesheet_'.date('m-d-Y');
		    $login_time 	= '08:30';
		    $logout_time 	= date('h:i');

			$saved_data 	= array(
			 	'login' => array(
		            'time' => $login_time,
		            'convention' => 'AM'
		        ),
			    'logout' => array(
			        'time' => $logout_time,
			        'convention' => 'PM'
			    ),
			    'breakfast' => 'Skip',
			    'lunch' 	=> array(
			            'hr' => '',
			            'min'=> '40'
			    ),
				'tea' => 'Skip',
			    'task' => array(
			        '0' => array(
				        	'project_name' 	=> '',
		                    'task_name' 	=> '',
		                    'task_time' => array(
	                            'hr' => '',
	                            'min' => ''
		                    ),
		                    'task_status' => 'Inprogress',
		                    'sub_task_name' => array(
	                            '0' => ''
		                    )
		                )
		        )
			);
			
			if ( isset( $_POST[ 'sj-timesheet-nonce' ] ) && wp_verify_nonce( $_POST[ 'sj-timesheet-nonce' ], 'sj-timesheet' ) ) {
				
				$to_save_data 	= $_POST;

				unset( $to_save_data[ 'sj-timesheet-nonce' ] );
				unset( $to_save_data[ '_wp_http_referer' ] );

				update_option( $option_name, $to_save_data );
				$saved_data 	= get_option( $option_name );
				
				$preview_data = '<div class="sj-timesheet-preview">';
					
					$preview_data .= $this->preview_timesheet( $saved_data );
				
					$preview_data .= '<div class="sj-edit-send-timesheet">';
						$preview_data .= '<form method="post" action="">';
							$preview_data .= '<a href="?edit" class="sj-edit-timesheet ast-button">Edit</a>';
						
							/* Submit Button */
							$preview_data .= wp_nonce_field( 'sj-timesheet-send', 'sj-timesheet-send-nonce', true, false );
							$preview_data .= '<button class="sj-send-timesheet ast-button">Confirm & Send</a>';
						$preview_data .= '</form>';
					$preview_data .= '</div>';
				
				$preview_data .= '</div>';
				
				return $preview_data;
			}

			if ( isset( $_GET['edit'] ) ) {
				$saved_data 	= get_option( $option_name );
			}

			if ( isset( $_POST[ 'sj-timesheet-send-nonce' ] ) && wp_verify_nonce( $_POST[ 'sj-timesheet-send-nonce' ], 'sj-timesheet-send' ) ) {
				
				$saved_data 	= get_option( $option_name );
				$timesheet 		= $this->preview_timesheet( $saved_data );

				$to 		= $args['to_email'];
				$subject 	= 'Timesheet - '.date('d F Y');
				$headers 	= array('Content-Type: text/html; charset=UTF-8');
				
				wp_mail( $to, $subject, $timesheet, $headers );

				return '<h3>Timesheet Sent Successfully...</h3>';
			}
			//vl( $saved_data );
		    

			//$timestamp = time();
			//$date_time = date("d-m-Y (D) H:i:s", $timestamp);
			//echo "Current date and local time on this server is $date_time";


		    $file_name = 'Timesheet_' . date('D-m-d-Y') . '.log';
		    $file_path = SJ_TIMESHEET_DIR . 'data/' . $file_name;
		    
		    if ( file_exists( $file_path ) ) {

		    	$file = $file_path; // here we are selecting text file by name. - remember same directory

				$f = fopen($file, 'r'); // now we open the same file with -r switch read.

				$login_time = trim( fgets($f) ); //fgets, gets us the first line and store it in $firstLine variable

				fclose($f); //now we close the file.
		    }
		    
		 	// vl( $login_time, 1 );
			// vl( $logout_time, 1 );
			$output = '';

			$output .= '<script type="text/template" id="sj-task-repeater-template">';
        		$output .= '<div class="sj-task-repeater sj-task-repeater-{{id}}" data-repeater="{{id}}">';
					$output .= '<div class="sj-task-fields">';
						$output .= '<div class="sj-field-wrapper">';
	    					$output .= '<label for="project_name">Project Name</label>';
	    					$output .= '<input type="text" id="project_name" name="task[{{id}}][project_name]" placeholder="Project Name" value="">';
	    				$output .= '</div>';

	    				$output .= '<div class="sj-field-wrapper">';
	    					$output .= '<label for="task_name">Task Name</label>';
	    					$output .= '<input type="text" id="task_name" name="task[{{id}}][task_name]" placeholder="Task Name" value="">';
	    				$output .= '</div>';

	    				$output .= '<div class="sj-field-wrapper">';
	    					$output .= '<label for="task_time">Task Time</label>';
	    					$output .= '<input type="text" id="task_time_hr" name="task[{{id}}][task_time][hr]" placeholder="Hour" value="">';
	    					$output .= '<input type="text" id="task_time_min" name="task[{{id}}][task_time][min]" placeholder="Minutes" value="">';
	    				$output .= '</div>';

	    				$output .= '<div class="sj-field-wrapper">';
	    					$output .= '<label for="task_status">Task Status</label>';
	    					$output .= '<select id="task_status" name="task[{{id}}][task_status]">';
							  $output .= '<option value="Inprogress">Inprogress</option>';
							  $output .= '<option value="Completed">Completed</option>';
							$output .= '</select>';
	    					//$output .= '<input type="text" id="task_status" name="task[{{id}}][task_status]" placeholder="Task Status" value="">';
	    				$output .= '</div>';
    				$output .= '</div>';

    				$output .= '<div class="sj-sub-task-wrapper">';
	    				$output .= '<div class="sj-sub-task-repeater">';
		    				$output .= '<div class="sj-field-wrapper sj-sub-task-field">';
		    					$output .= '<label for="sub_task_name">Sub-Task Name</label>';
		    					$output .= '<input type="text" id="sub_task_name" name="task[{{id}}][sub_task_name][]" placeholder="Sub-Task Name" value="">';
		    				$output .= '</div>';
		    			$output .= '</div>';
	    				
	    				$output .= '<div class="sj-add-new-sub-task" data-repeater="{{id}}">';
							$output .= '<span class="button">Add New Sub-Task</span>';
	    				$output .= '</div>';
	    			$output .= '</div>';
	    		$output .= '</div>';
	    	$output .= '</script>';

	    	$output .= '<script type="text/template" id="sj-sub-task-repeater-template">';
	    		$output .= '<div class="sj-sub-task-repeater">';
					$output .= '<div class="sj-field-wrapper sj-sub-task-field">';
						$output .= '<label for="sub_task_name">Sub-Task Name</label>';
						$output .= '<input type="text" id="sub_task_name" name="task[{{id}}][sub_task_name][]" placeholder="Sub-Task Name" value="">';
					$output .= '</div>';
				$output .= '</div>';
			$output .= '</script>';

		    $output .= '<div class="sj-timesheet-container">';
  			$output .= '<form method="post" action="">';

  				$output .= '<div class="sj-timesheet-time-wrapper">';
	  				/* Login Time */
	  				$output .= '<div class="sj-field-wrapper">';
	    				$output .= '<label for="login_time">Login Time</label>';
	    				$output .= '<input type="text" id="login_time" name="login[time]" placeholder="Login Time" value="' . $saved_data['login']['time'] . '">';
	    				$output .= '<select id="login_convention" name="login[convention]">';
	    					$output .= '<option '.selected( $saved_data['login']['convention'], 'AM', false ).' value="AM">AM</option>';
	    					$output .= '<option '.selected( $saved_data['login']['convention'], 'PM', false ).' value="PM">PM</option>';
	    				$output .= '</select>';
	    			$output .= '</div>';

	  				/* Logout Time */
	    			$output .= '<div class="sj-field-wrapper">';
	    				$output .= '<label for="logout_time">Logout Time</label>';
	    				$output .= '<input type="text" id="logout_time" name="logout[time]" placeholder="Logout Time" value="' . $saved_data['logout']['time'] . '">';
	    				$output .= '<select id="logout_convention" name="logout[convention]">';
	    					$output .= '<option '.selected( $saved_data['logout']['convention'], 'AM', false ).' value="AM">AM</option>';
	    					$output .= '<option '.selected( $saved_data['logout']['convention'], 'PM', false ).' value="PM">PM</option>';
	    				$output .= '</select>';
	    			$output .= '</div>';

	  				/* Breakfast */
	  				$output .= '<div class="sj-field-wrapper">';
	    				$output .= '<label for="breakfast">Breakfast</label>';
	    				$output .= '<input type="text" id="breakfast" name="breakfast" placeholder="Minutes" value="' . $saved_data['breakfast'] . '">';
	    			$output .= '</div>';

	  				/* Lunch */
	    			$output .= '<div class="sj-field-wrapper">';
	    				$output .= '<label for="lunch">Lunch</label>';
	    				$output .= '<input type="text" id="lunch_hr" name="lunch[hr]" placeholder="Hour" value="' . $saved_data['lunch']['hr'] . '">';
	    				$output .= '<input type="text" id="lunch_min" name="lunch[min]" placeholder="Minutes" value="' . $saved_data['lunch']['min'] . '">';
	    			$output .= '</div>';

	    			/* Tea */
	    			$output .= '<div class="sj-field-wrapper">';
	    				$output .= '<label for="tea">Tea</label>';
	    				$output .= '<input type="text" id="tea" name="tea" placeholder="Minutes" value="' . $saved_data['tea'] . '">';
	    			$output .= '</div>';
	    		
	    		$output .= '</div>';

    			/* Tasks */
				$output .= '<div class="sj-task-wrapper">';
					
	    			$i = 0;
					foreach ( $saved_data['task'] as $i => $task ) {
						
						$output .= '<div class="sj-task-repeater sj-task-repeater-'.$i.'" data-repeater="'.$i.'">';
							
							$output .= '<div class="sj-task-fields">';
			    				$output .= '<div class="sj-field-wrapper">';
			    					$output .= '<label for="project_name">Project Name</label>';
			    					$output .= '<input type="text" id="project_name" name="task['.$i.'][project_name]" placeholder="Project Name" value="' . $task['project_name'] . '">';
			    				$output .= '</div>';
			    				$output .= '<div class="sj-field-wrapper">';
			    					$output .= '<label for="task_name">Task Name</label>';
			    					$output .= '<input type="text" id="task_name" name="task['.$i.'][task_name]" placeholder="Task Name" value="' . $task['task_name'] . '">';
			    				$output .= '</div>';

			    				$output .= '<div class="sj-field-wrapper">';
			    					$output .= '<label for="task_time">Task Time</label>';
			    					$output .= '<input type="text" id="task_time_hr" name="task['.$i.'][task_time][hr]" placeholder="Hour" value="' . $task['task_time']['hr'] . '">';
		    						$output .= '<input type="text" id="task_time_min" name="task['.$i.'][task_time][min]" placeholder="Minutes" value="' . $task['task_time']['min'] . '">';
			    				$output .= '</div>';

			    				$output .= '<div class="sj-field-wrapper">';
			    					$output .= '<label for="task_status">Task Status</label>';
			    					$output .= '<select id="task_status" name="task['.$i.'][task_status]">';
										$output .= '<option ' . selected( $task['task_status'], 'Inprogress', false ) . ' value="Inprogress">Inprogress</option>';
										$output .= '<option ' . selected( $task['task_status'], 'Completed', false ) . ' value="Completed">Completed</option>';
									$output .= '</select>';
			    					//$output .= '<input type="text" id="task_status" name="task['.$i.'][task_status]" placeholder="Task Status" value="">';
			    				$output .= '</div>';
		    				$output .= '</div>';

		    				$output .= '<div class="sj-sub-task-wrapper">';
		    					
		    					foreach ( $task['sub_task_name'] as $sub_task ) {
		    						
				    				$output .= '<div class="sj-sub-task-repeater">';
					    				$output .= '<div class="sj-field-wrapper sj-sub-task-field">';
					    					$output .= '<label for="sub_task_name">Sub-Task Name</label>';
					    					$output .= '<input type="text" id="sub_task_name" name="task['.$i.'][sub_task_name][]" placeholder="Sub-Task Name" value="'.$sub_task.'">';
					    				$output .= '</div>';
					    			$output .= '</div>';
		    					}
			    				
			    				$output .= '<div class="sj-add-new-sub-task" data-repeater="'.$i.'">';
									$output .= '<span class="button">Add New Sub-Task</span>';
			    				$output .= '</div>';
			    			$output .= '</div>';
			    		$output .= '</div>';
					}
				
					$output .= '<div class="sj-add-new-task" data-repeater="'.$i.'">';
						$output .= '<span class="button">Add New Task</span>';
					$output .= '</div>';
    			$output .= '</div>';

				/* Submit Button */
				$output .= wp_nonce_field( 'sj-timesheet', 'sj-timesheet-nonce', true, false );
    			$output .= '<div class="sj-field-wrapper sj-field-submit">';
					$output .= '<input type="submit" value="Submit">';
				$output .= '</div>';
			$output .= '</form>';
			$output .= '</div>';
    

		    return $output;
		}
		function prepare_preview_data( $saved_data ) {
			
			$data = array();

			if ( is_array( $saved_data ) ) {
				
			}

			return $data;
		}
		function preview_timesheet( $saved_data ) {

			// vl( $saved_data );

			$login_time 	= esc_attr( $saved_data['login']['time'] .' '. $saved_data['login']['convention'] );
			$logout_time 	= esc_attr( $saved_data['logout']['time'] .' '. $saved_data['logout']['convention'] );

			$output = '';
			
			$output .= 'Timesheet - '.date('d F Y').'<br><br>';

			$output .= '<table border="1" cellspacing="0" cellpadding="10">';
				$output .= '<colgroup><col width="180" /><col width="120" /></colgroup>';
				$output .= '<tbody align="left">';
					
					$output .= '<tr>';
						$output .= '<td>Login Time</td>';
						$output .= '<td>'.$login_time.'</td>';
					$output .= '</tr>';

					$output .= '<tr>';
						$output .= '<td>Logout Time</td>';
						$output .= '<td>'.$logout_time.'</td>';
					$output .= '</tr>';

				$output .= '</tbody>';
			$output .= '</table>';
			$output .= '<br>';

			$output .= '<table border="1" cellspacing="0" cellpadding="10">';
			$output .= '<colgroup><col width="170" /><col width="305" /><col width="65" /><col width="65" /><col width="105" /></colgroup>';
				$output .= '<tbody align="center">';
					// Table Heading Start
					$output .= '<tr>';
						$output .= '<td rowspan="2">Project Name</td>';
						$output .= '<td rowspan="2">Task</td>';
						$output .= '<td colspan="2">Time</td>';
						$output .= '<td rowspan="2">Status</td>';
					$output .= '</tr>';
					$output .= '<tr>';
						$output .= '<td>Hours</td>';
						$output .= '<td>Minutes</td>';
					$output .= '</tr>';
					// Table Heading End

					if ( isset( $saved_data['task'] ) && is_array( $saved_data['task'] ) ) {
						
						foreach ( $saved_data['task'] as $task ) {
							// Task Start
							$output .= '<tr>';
								$output .= '<td>'.$task['project_name'].'</td>';
								$output .= '<td align="left">';

									$output .= '&nbsp;#'.$task['task_name'];

									if ( isset( $task['sub_task_name'] ) && is_array( $task['sub_task_name'] ) && count( $task['sub_task_name'] ) > 0 ) {
										$space = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
										foreach ( $task['sub_task_name'] as $sub_task ) {
											if ( '' != $sub_task ) {
												$output .= '<br>'.$space.'-'.$sub_task;
											}
										}
									}

								$output .= '</td>';


								$output .= '<td>'.$task['task_time']['hr'].'</td>';
								$output .= '<td>'.$task['task_time']['min'].'</td>';
								$output .= '<td>'.$task['task_status'].'</td>';
							$output .= '</tr>';
							// Task End
						}
					}

					// Breaks Start
					$output .= '<tr>';
						$output .= '<td>Break</td>';
						$output .= '<td align="left">';

							$output .= '&nbsp;#Breakfast<br><br>';
							$output .= '&nbsp;#Lunch<br><br>';
							$output .= '&nbsp;#Tea<br>';

						$output .= '</td>';


						$output .= '<td>';
							$output .= '<br><br>';
							$output .= $saved_data['lunch']['hr'].'<br><br>';
							$output .= '<br>';
						$output .= '</td>';

						$output .= '<td>';
							$output .= $saved_data['breakfast'].'<br><br>';
							$output .= $saved_data['lunch']['min'].'<br><br>';
							$output .= $saved_data['tea'].'<br>';
						$output .= '</td>';
						
						$output .= '<td></td>';

					$output .= '</tr>';
					// Breaks End

					//&nbsp;&nbsp;

					// $output .= '<tr>';
					// 	$output .= '<td>Break</td>';
					// 	$output .= '<td align="left">';
			  //           	$output .= '<br>#Breakfast';
			  //               $output .= '<br>';
			  //               $output .= '<br>#Lunch';
			  //               $output .= '<br>';
			  //               $output .= '<br>#Teabreak';
			  //          	$output .= '</td>';
					// 	$output .= '<td>0</td>';
					// 	$output .= '<td>20';
			  //           	$output .= '<br>';
			  //           	$output .= '<br>30';
			  //           	$output .= '<br>';
			  //           	$output .= '<br>15';
			  //           $output .= '</td>';
					// 	$output .= '<td></td>';
					// $output .= '</tr>';
				$output .= '</tbody>';
			$output .= '</table>';

			return $output;
		}
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

SJ_Timesheet_Shortcode::get_instance();