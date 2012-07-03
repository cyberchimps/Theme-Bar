<?php
/*
Plugin Name: Header Bar
Description: This plugin adds custom header bar.
Author: Cyberchimps
Author URI: http://www.cyberchimps.com/

*/

if ( !class_exists( 'header_bar' ) ) {

	// Start of class
	class header_bar {
	
		// Constructor
		function header_bar() {
		
			// Initialise plugin
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'wp_head', array( &$this, 'header_content' ) );
	
			// Action to incude css
			add_action( 'wp_enqueue_scripts', array(&$this, 'add_header_bar_stylesheet') );
			
			// Action to incude JS
			add_action( 'wp_enqueue_scripts', array(&$this, 'add_header_bar_js') );
		}

		//Enqueue plugin style file
		function add_header_bar_stylesheet() {
			wp_register_style( 'header_bar_style', plugins_url('css/header_bar_style.css', __FILE__) );
			wp_enqueue_style( 'header_bar_style' );
		}

		//Enqueue plugin jS files
		function add_header_bar_js() {
		
		    if(!wp_script_is( 'jquery' )) {
				wp_enqueue_script('jquery');
			}
		
			wp_register_script( 'header_bar_script', plugins_url('js/header_bar_js.js', __FILE__), array('jquery') );
			wp_enqueue_script( 'header_bar_script' );
		}
	
		// Initialise plugin - admin part
		function admin_init() {
			
			// Registers setting
			register_setting( 'header_bar_setting', 'button_text', 'trim' );
			register_setting( 'header_bar_setting', 'button_link', 'trim' );
			
			// Includes JS to validate admin setting form
			wp_register_script( 'validator', plugins_url('js/jquery.validate.js', __FILE__), array('jquery') );
			wp_register_script( 'validate_header', plugins_url('js/validate_header_info.js', __FILE__), array('jquery') );
			wp_enqueue_script( 'validator' );
			wp_enqueue_script( 'validate_header' );
		}
		
		// Add admin menu option
		function admin_menu() {
			add_submenu_page( 'options-general.php', 'Header bar option', 
				'Header bar option', 'manage_options', __FILE__, array( &$this, 'options_panel' ) );
		}
		
		// Display header bar
		function header_content() {
			$text = get_option( 'button_text', '' );  // gets text to be displayed on button
			$link = get_option( 'button_link', '' );  // gets link to where it should be redirected when button is clicked
		
			$plugins_url = plugins_url(); // gets url of plugin directory
			
			// if text and link option is defined display the header bar
			if ( $text != '' && $link != '') {
				
				// Getting current theme name
				$current_theme_name = get_current_theme();
				
				// Initializing array with theme name and link
				$theme_name_link = array(
									"iFeature Pro" => "http://cyberchimps.com/ifeatureprodemo/",
									"Eclipse Pro" => "http://cyberchimps.com/eclipseprodemo/",
									"Business Pro" => "http://cyberchimps.com/businessprodemo/",
									"Response Pro" => "http://cyberchimps.com/responseprodemo/",
									"Neuro Pro" => "http://cyberchimps.com/neuroprodemo/"
									);
				
				// Defining content of the header bar
				echo "<div class='header_bar'>
						<table width='100%' height='65px' class='header_bar'>
							<tr>
								<td width='40%'>
									<a href='http://cyberchimps.com/'>
										<img height='45' width='40%' class='logo'
										src='".$plugins_url ."/header_bar/image/logo.png'>
									</a>
								</td>
								<td width='20%' class='theme_change'>
									<select class='theme_selector'>";
										
										foreach( $theme_name_link as $name => $theme_link) {
											echo "<option value='" . $theme_link . "'" . 
											($name == $current_theme_name? "selected='true'" : "" ). ">". $name . "</option>";
										}
										
							  echo "</select>
								</td>
								<td width='27%' valign='middle' class='buy_button'>
									<a href='". $link ."' class='header_button'>". esc_html($text) . "</a>
								</td>
								<td width='13%' class='cross'>
									<img height='15' width='15' class='close_button'
									src='".$plugins_url ."/header_bar/image/cross.png'>
								</td?
							</tr>
						</table>
					  </div>";
			}
			
		}

		// Handle options panel
		function options_panel() {
?>
			<div class="wrap">
				<?php screen_icon(); // gets icon of the current setting page ?>
				<h2><?php echo "Header bar options" ?></h2>

				<form id="header_bar_options" name="header_bar_options" action="options.php" method="post">
					<?php settings_fields( 'header_bar_setting' ); ?>
					<table class="form-table">
					
						<!-- Input field for button text -->
						<tr>
							<th scope="row" class="button_text">
								<label for="button_text"><?php echo "Button text:"; ?></label>
							</th>
							<td>
								<input type="text" id="button_text" name="button_text" size="25"
								value="<?php echo esc_html( get_option( 'button_text' ) ); ?>">
							</td>
						</tr>
						
						<!-- Input field for button link -->
						<tr>
							<th scope="row" class="button_link">
								<label for="button_link"><?php echo "Button link:"; ?></label>
							</th>
							<td>
								<input type="text" id="button_link" name="button_link" size="25"
								value="<?php echo esc_html( get_option( 'button_link' ) ); ?>">
							</td>
						</tr>
					</table>

					<p class="submit">
						<input type="submit" name="Submit" value="<?php echo "Save settings"; ?>" />
					</p>

				</form>
			</div>
	<?php
		}  // End of options_panel
	} 	// End of class

	add_option( 'button_text', '' );  // Adding option for button text
	add_option( 'button_link', '' );  // Adding option for button link

	$wp_header_bar = new header_bar();	// Creating object of header_bar class

}
?>