<?php
/*
Plugin Name: Header Bar
Description: This plugin adds custom header bar.
Author: Cyberchimps
Author URI: http://www.cyberchimps.com/

*/

	// Checking existance of class
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
			add_action( 'admin_init', array(&$this, 'add_header_bar_admin_stylesheet') );
			
			// Action to incude JS
			add_action( 'wp_enqueue_scripts', array(&$this, 'add_header_bar_js') );
		}

		//Enqueue plugin style file for fornt end
		function add_header_bar_stylesheet() {
			wp_register_style( 'header_bar_style', plugins_url('css/header_bar_style.css', __FILE__) );
			wp_enqueue_style( 'header_bar_style' );
		}
		
		//Enqueue plugin style file for admin end
		function add_header_bar_admin_stylesheet() {
			wp_register_style( 'header_bar_admin', plugins_url('css/header_bar_admin.css', __FILE__) );
			wp_enqueue_style( 'header_bar_admin' );
		}

		//Enqueue plugin jS files
		function add_header_bar_js() {
			
			// Check whether jquery is included or not, if not then include
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
		
		// Function to define mark up for header bar 
		function header_content() {
			$text = get_option( 'button_text', '' );  // gets text to be displayed on button
			$link = get_option( 'button_link', '' );  // gets link to where it should be redirected when button is clicked
			$custom_logo_url = get_option( 'custom_logo_url', '' ); // gets the url of the custom logo
			$plugins_url = plugins_url(); // gets url of plugin directory
			
			// if text and link option is defined display the header bar
			if ( $text != '' && $link != '') {
				
				// Getting current theme name
				$current_theme_name = get_current_theme();
				
				// Initializing array with theme name and link
				$theme_name_link = get_option( 'theme_links', '');
				
				// content of the header bar starts
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
												foreach( $theme_name_link as $theme_info) {
													echo "<option value='" . $theme_info['theme_url'] . "'" . 
													($theme_info['theme_name'] == $current_theme_name? "selected='true'" : "" ). ">". $theme_info['theme_name'] . "</option>";
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
				// content of the header bar ends
			}
		}

		// Handle options panel
		function options_panel() {
		
			$upload_dir = wp_upload_dir(); // Getting url of upload directory
			$custom_logo_url = get_option( 'custom_logo_url', '' ); // gets url of the custom logo
			$admin_url = get_admin_url(); // gets admin url
			$plugins_url = plugins_url(); // gets url of plugin directory
			
			// Uploading file to server
			if(isset($_FILES["custom_logo"]))
			{
				if ((($_FILES["custom_logo"]["type"] == "image/png") || ($_FILES["custom_logo"]["type"] == "image/gif")
				|| ($_FILES["custom_logo"]["type"] == "image/jpeg") || ($_FILES["custom_logo"]["type"] == "image/pjpeg")) 
				&& ($_FILES["custom_logo"]["size"] < 100000))
				{
					// Display error code if there is any error
					if ($_FILES["custom_logo"]["error"] > 0)
					{
						echo "<div class='error'><p><strong>Return Code: " . $_FILES["custom_logo"]["error"] .
						"</strong></p></div>";
					}
					else
					{	
						// If no error upload the file to uploads folder
						move_uploaded_file($_FILES["custom_logo"]["tmp_name"], $upload_dir['path'] . "/" . $_FILES["custom_logo"]["name"]);
						
						// save url of the recently uploaded image
						header_bar::$uploaded_image_url = $upload_dir['url'] . "/" . $_FILES["custom_logo"]["name"];
						
						// update the uploaded image url to the custom_logo_url option
						update_option( 'custom_logo_url', header_bar::$uploaded_image_url);
						
						// Get the updated value of the custom_logo_url option
						$custom_logo_url = get_option( 'custom_logo_url', '' );
						
						// Display success mesasge
						echo "<div class='updated'><p><strong>Image uploaded succesfully</strong></p></div>";
					}
				  }
				else
				{
					// Display error message describing acceptable condition for the image
					echo "<div class='error'><p><strong>Please enter an image of jpg, png or gif of size less than 100kb
					</strong></p></div>";
				}
			}
			
			// Add, update and delete operation of theme list
			$theme_list = get_option( 'theme_links', '');
			if(isset($_GET['action']))
				$action = $_GET['action'];  // get value of action to be performed
			if(isset($_GET['id']))
				$theme_id = $_GET['id'];   // get value of theme_id on which action to be performed
	
			// Perform add or edit action only if theme_name and theme_url form fields are defined
			if( isset($_POST["theme_url"]) && isset($_POST["theme_name"]) )
			{
				// store posted theme_name and theme_url to an array
				$new_theme_list = array("theme_name"=>$_POST["theme_name"], "theme_url"=>$_POST["theme_url"]);

				// get the action to be done
				$form_action = $_POST["action"];
				
				// get the theme_id if it is posted on which action to be done(to be used incase of edit and delete action)
				if(isset($_POST["theme_id"]))
					$form_theme_id = $_POST["theme_id"];
					
				// Permorm add action	
				if($form_action == "add")
				{
					$theme_list[] = $new_theme_list; // add new theme to the list
					$action = "";   // reset action
					echo "<div class='updated'>New theme is added to list</div>"; // show success message
				}
				
				// perform edit action
				else if($form_action == "edit")
				{
					$theme_list[$form_theme_id] = $new_theme_list; // replace the updated data in the array 
					$action = "";  // reset action
					echo "<div class='updated'>Theme list updated</div>"; // show success message
				}
			}
			
			// perform delete action
			if($action == "delete")
			{
				unset($theme_list[$theme_id]);  // unset the array at the specisfied position
				$theme_list = array_values($theme_list);  // rearrange the array
				echo "<div class='updated'>One theme is deleted from list</div>"; // show success message
			}
			
			// update the option theme_links with the updates array
			update_option( 'theme_links', $theme_list);
			
			// get a fresh copy of the updated option theme_links
			$theme_list = get_option( 'theme_links', '');	
?>
			<!-- Mark up for option panel -->
			<div class="wrap">
				<?php screen_icon(); // gets icon of the current setting page ?>
				<h2><?php echo "Header bar options"; ?></h2>  <!-- show title of the page -->

				<!-- Form to upload image for custom logo -->
				<form id="upload_logo" name="upload_logo" action="" method="post" enctype="multipart/form-data">
					<table class="form-table">
						<tr>
							<td><h3>Logo Option:<h3></td>  <!-- subsection header -->
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
					
					<!-- show preview of the logo -->
					<img src="<?php echo $custom_logo_url ?>" height="60" width="230"> 
					
					<p class="submit">
						<input type="submit" name="Submit" value="<?php echo "Upload"; ?>" />
					</p>
				</form>

				<!-- Showing list of theme -->
				<table width="50%">
					<tr>
						<td><h3>Theme List:<h3></td> <!-- subsection header -->
					</tr>
					<tr class="theme_list_alt2">
						<td width="40%"><h4>Theme Name</h4></td>
						<td width="40%"><h4>Theme URL</h4></td>
						<td width="20%"><h4>Action</h4></td>
					</tr>
					<?php	
					$id = 0; // initialize counter to track theme_id
					
					// display one row for each record on theme list
					foreach( $theme_list as $theme_details) { 
					?>
						<tr class="<?php echo $id%2 == 0? "theme_list_alt1" : "theme_list_alt2"; ?>" >
							<td> <?php echo $theme_details['theme_name']; ?> </td>   <!-- Display theme name -->
							<td> <?php echo $theme_details['theme_url']; ?> </td>	 <!-- Display theme url -->
							<td>
								<!-- Display edit link -->
								<a href="<?php echo $admin_url. "options-general.php?page=Theme-Bar/header_bar.php&action=edit&id=". $id; ?>" >
								<img title="edit" src="<?php echo $plugins_url . "/theme-bar/image/edit.png" ?>" /></a>
								
								<!-- Display delete link -->
								<a href="<?php echo $admin_url. "options-general.php?page=Theme-Bar/header_bar.php&action=delete&id=". $id; ?>" 
								onclick="return confirm('Are you sure to delete?');">
								<img title="delete" src="<?php echo $plugins_url . "/theme-bar/image/delete.png" ?>" /></a>
							</td>
						</tr>
					<?php 
					$id++;  // increament the counter 
					} ?>	
					
					<!-- Display link to add new theme details -->
					<tr>
						<td>  
							<a class="add_theme_link" href="<?php echo $admin_url. "options-general.php?page=Theme-Bar/header_bar.php&action=add"; ?>">
							Add New Theme<img class="add_theme_img" src="<?php echo $plugins_url . "/theme-bar/image/add.png" ?>"></a>
						</td>
					</tr>
				</table>	
						
				<?php 
				       // Display the edit/add form if the defined action is edit or add
				if(isset($action) && ($action == "edit" || $action == "add"))
				{ 
					// if action is edit get previous theme name and url otherwise set them to ""
					if($action == "edit")
					{
						$pre_theme_name = $theme_list[$theme_id]["theme_name"];
						$pre_theme_url = $theme_list[$theme_id]["theme_url"];
					}
					else
					{
						$pre_theme_name = "";
						$pre_theme_url = "";
					}
				?>
				<!-- Form to update theme list -->
					<form id="theme_list" name="theme_list" action="" method="post">
						<table class="form-table">
							<tr>
								<!-- For theme name -->
								<th scope="row">
									<label for="theme_name">Theme Name:</label>
								</th>
								<td>
									<input type="text" id="theme_name" name="theme_name" size="25" 
									value="<?php echo $pre_theme_name; ?>">
								</td>
								
								<!-- For theme url -->
								<th scope="row">
									<label for="theme_url">Theme URL:</label>
								</th>
								<td>
									<input type="text" id="theme_url" name="theme_url" size="25"
									value="<?php echo $pre_theme_url; ?>">
									
									<!-- hidden field for action -->
									<input type="hidden" name="action" id="action" value="<?php echo $action; ?>" >
									<?php
									// hidden field for theme_id if action is edit
									if($action == "edit")
									{?>
										<input type="hidden" name="theme_id" id="theme_id" value="<?php echo $theme_id; ?>" >
									<?php
									} ?>
								</td>
							</tr>
						</table>	
						<p class="submit">
							<input type="submit" name="Submit" value="Update theme list" />
						</p>
					</form>
				<?php 
				}?>	
				
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

	add_option( 'button_text', '' );  // Adding option for button text
	add_option( 'button_link', '' );  // Adding option for button link
	add_option( 'theme_links', '');  // Adding option for theme list
	add_option( 'custom_logo_url', '');  // Adding option for custom logo url

	$wp_header_bar = new header_bar();	// Creating object of header_bar class

}
?>