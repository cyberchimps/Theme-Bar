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
	
		public static $uploaded_image_url;
		
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
			register_setting( 'header_bar_setting', 'custom_logo_url', 'trim' );
			
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
			$custom_logo_url = get_option( 'custom_logo_url', '' );
		
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
				echo "<div class='header_bar_container'>
						<div class='header_bar'>
							<table>
								<tr>
									<td>
										<div class='logo_container'>
											<a href='http://cyberchimps.com/'>
												<img class='logo' src='". $custom_logo_url. "'>
											</a>
										</div>
										<div class='dropdown_container'>
											<select class='theme_selector'>";
												foreach( $theme_name_link as $name => $theme_link) {
													echo "<option value='" . $theme_link . "'" . 
													($name == $current_theme_name? "selected='true'" : "" ). ">". $name . "</option>";
												}	
											echo "</select>
										</div>
										<div class='button_container'>
											<a href='". $link ."' class='header_button'>". esc_html($text) . "</a>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<img height='15' width='15' class='close_button' src='".$plugins_url ."/theme-bar/image/cross.png'>
					</div>";
			}
		}

		// Handle options panel
		function options_panel() {
		
			$upload_dir = wp_upload_dir(); // Getting url of upload directory
			$custom_logo_url = get_option( 'custom_logo_url', '' );
			
			// Uploading file to server
			if(isset($_FILES["custom_logo"]))
			{
				if ((($_FILES["custom_logo"]["type"] == "image/png") || ($_FILES["custom_logo"]["type"] == "image/gif")
				|| ($_FILES["custom_logo"]["type"] == "image/jpeg") || ($_FILES["custom_logo"]["type"] == "image/pjpeg")) 
				&& ($_FILES["custom_logo"]["size"] < 100000))
				{
					if ($_FILES["custom_logo"]["error"] > 0)
					{
						echo "<div class='error'><p><strong>Return Code: " . $_FILES["custom_logo"]["error"] .
						"</strong></p></div>";
					}
					else
					{
						move_uploaded_file($_FILES["custom_logo"]["tmp_name"], $upload_dir['path'] . "/" . $_FILES["custom_logo"]["name"]);
						header_bar::$uploaded_image_url = $upload_dir['url'] . "/" . $_FILES["custom_logo"]["name"];
						update_option( 'custom_logo_url', header_bar::$uploaded_image_url);
						$custom_logo_url = get_option( 'custom_logo_url', '' );
						echo "<div class='updated'><p><strong>Image uploaded succesfully</strong></p></div>";
					}
				  }
				else
					echo "<div class='error'><p><strong>Please enter an image of jpg, png or gif of size less than 100kb
					</strong></p></div>";
			}
			
			$theme_list = get_option( 'theme_links', '');
			if( isset($_POST["theme_url"]) && isset($_POST["theme_name"]) )
			{
				$new_theme_list = array("theme_name"=>$_POST["theme_name"], "theme_url"=>$_POST["theme_url"]);

				$theme_list[] = $new_theme_list;
	
				update_option( 'theme_links', $theme_list);
			}
?>
			<div class="wrap">
				<?php screen_icon(); // gets icon of the current setting page ?>
				<h2><?php echo "Header bar options" ?></h2>

				<!-- Form to upload image for custom logo -->
				<form id="upload_logo" name="upload_logo" action="" method="post" enctype="multipart/form-data">
					<table class="form-table">
						<tr>
							<td><h3>Logo Option:<h3></td>
						</tr>
					
						<!-- Input field to upload custom logo -->
						<tr>
							<th scope="row" class="custom_logo">
								<label for="custom_logo">Custom Logo:</label>
							</th>
							<td>
								<input type="file" id="custom_logo" name="custom_logo" size="22">
							</td>
						</tr>
					</table>
					<img src="<?php echo $custom_logo_url ?>" height="60" width="230">
					<p class="submit">
						<input type="submit" name="Submit" value="<?php echo "Upload"; ?>" />
					</p>
				</form>
				
				<!-- Form to update theme list -->
				<form id="theme_list" name="theme_list" action="" method="post">
					<table class="form-table">
						<tr>
							<td><h3>Theme List:<h3></td>
						</tr>
						<tr>
							<th scope="row">
								<label for="theme_name">Theme Name:</label>
							</th>
							<td>
								<input type="text" id="theme_name" name="theme_name" size="25">
							</td>
							<th scope="row">
								<label for="theme_url">Theme URL:</label>
							</th>
							<td>
								<input type="text" id="theme_url" name="theme_url" size="25">
							</td>
						</tr>
					</table>	
					<p class="submit">
						<input type="submit" name="Submit" value="Update theme list" />
					</p>
				</form>
				
				<!-- Form to add button options -->
				<form id="header_bar_options" name="header_bar_options" action="options.php" method="post">
					<?php settings_fields( 'header_bar_setting' ); ?>
					<table class="form-table">
						<tr>
							<td><h3>Button Option:<h3></td>
						</tr>
					
						<!-- Input field for button text -->
						<tr>
							<th scope="row" class="button_text">
								<label for="button_text">Button text:</label>
							</th>
							<td>
								<input type="text" id="button_text" name="button_text" size="25"
								value="<?php echo esc_html( get_option( 'button_text' ) ); ?>">
							</td>
						</tr>
						
						<!-- Input field for button link -->
						<tr>
							<th scope="row" class="button_link">
								<label for="button_link">Button link:</label>
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

	add_option( 'custom_logo_url', '');  // Adding option for custom logo url
	add_option( 'button_text', '' );  // Adding option for button text
	add_option( 'button_link', '' );  // Adding option for button link
	add_option( 'theme_links', '');  // Adding option for theme list

	$wp_header_bar = new header_bar();	// Creating object of header_bar class

}
?>