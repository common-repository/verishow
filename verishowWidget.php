<?php
/*
Plugin Name: VeriShow
Plugin URI: http://wordpress.org/extend/plugins/verishow/
Description: <a href="http://www.verishow.com/?from=wordpress" target="_blank">VeriShow</a> is a multimedia live chat plugin that enables you to interact with site visitors in real time using chat, voice and video, and access a host of real-time content-sharing tools. You can share product documents, images and videos and annotate them in real-time with your visitors. To complete the installation process or to configure the plugin visit the <a href="options-general.php?page=verishow-control">settings page</a> (ensure that the plugin is activated first).
Version: 1.4.5
Author: HBRLabs
Author URI: http://www.verishow.com/?from=wordpress
*/

// this is the function that outputs the background as a style tag in the <head> 
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

global $aioseop_options;
$aioseop_options = get_option('aioseop_options');

function no_magic_quotes($query) {
        $data = explode("\\",$query);
        $cleaned = implode("",$data);
        return $cleaned;
}

function aioseop_activation_notice()
{

	global $aioseop_options;
	//echo 'text:1';
	//	echo $aioseop_options['activated'];
	if ($aioseop_options['activated']!='activated'){
		
		
		echo '<div class="error fade" style="background-color:#FFFFE0;"><p><strong>Your VeriShow plugin installation is incomplete. Please complete it on the <a style="color:#21759B;" href="' . admin_url( 'options-general.php?page=verishow-control' ) . '" >settings page</a>.</strong><br /></p></div>';
	}
	
}


if( ($aioseop_options['activated']!='activated') || ($_POST['activated']!='activated')){
	add_action( 'admin_notices', 'aioseop_activation_notice');
	}


function do_post_request($url, $data, $optional_headers = null)
{
  $params = array('http' => array(
              'method' => 'POST',
              'content' => $data
            ));
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
    throw new Exception("Problem with $url, $php_errormsg");
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
    throw new Exception("Problem reading data from $url, $php_errormsg");
  }
  return $response;
}



function verishow_control_header() {

$options = get_option('verishow_control_configuration');


if(isset($options)) {
		if (no_magic_quotes($options['integration'])!="")
		{
			echo "<script type='text/javascript' src='https://platform.verishow.com/client?get=chat&aac=";
			echo no_magic_quotes($options['integration']);
			echo "&call_button=true&img_url=https://platform.verishow.com/resources/embed_images/tab-right-blue-live-expert.png&img_alt=Live Support | Live Expert&alignment=right&isDynamic=false'></script>";
		}
	}

}
// This is the function that outputs our configuration page	

function verishow_control_conf() {

		$options = get_option('verishow_control_configuration');
		if ( !is_array($options) )
			$options = array( 'bg_file'=>null);
			
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));

		if (!empty($_POST['verishow-control-submit-li'])) {

			$int_number = do_post_request("http://platform.verishow.com/plugin?action=getIntegrationKey&email=".$_POST['lemail']."&password=".$_POST['lpassword'],"");
			
			if ($int_number!="error")
			{
				$options['user']=$_POST['lemail'];
				$options['integration']=$int_number;		
				//header("Location: settings.php?int_number=".$int_number);
			}
			else
			{
				$error_msg = "Wrong password, please try again.";
			}
		}

		else if (!empty($_POST['verishow-control-submit-su'])) {
			if( !empty($_POST['sname']) && !empty($_POST['semail']) && !empty($_POST['semail']) )
			{
				$int_number = do_post_request("https://platform.verishow.com/plugin?action=newUser&full_name=".$_POST['sname']."&phone=".$_POST['sphone']."&email=".$_POST['semail']."&password=".$_POST['spassword'],"");

				if ($int_number!="error")
				{
					
					$options['user']=$_POST['semail'];
					$options['integration']=$int_number;		
					//header("Location: settings.php?int_number=".$int_number);
				}
				else
				{
					$error_msg = "Could not create a user, user already exists or not all fields are filled.";
				}
			}
			else
			{
				$error_msg = "Please fill all the fields.";
			}
		}

		
		if ($options['integration']) {


			// Remember to sanitize and format use input appropriately.
				
			$options['button_enabled']=$_POST['button_enabled'];		
				

			$aioseop_options = get_option('aioseop_options');
			if ($options['integration']!="")
			{

				if ($aioseop_options['activated']!="activated")
				{
					?><script type="text/javascript">
					window.location.replace(window.location)
					</script><?php
				}
				$aioseop_options['activated'] = "activated";
				
			}
			else
			{
				if ($aioseop_options['activated']=="activated")
				{
					?><script type="text/javascript">
					window.location.replace(window.location)
					</script><?php
				}
				$aioseop_options['activated'] = "0";
			}
			update_option('aioseop_options',$aioseop_options);
		
			
			update_option('verishow_control_configuration', $options);
			
		}

	?>
		<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Verishow Settings</h2>
<?php

	if ($options['integration']!="")
	{
		$options['activated'] = "activated";
		$aioseop_options['activated'] = "activated";
		echo "<p><font color=green><strong>The VeriShow plugin is succesfully setup with the following email address: ".$options['user']."</strong></font></p>";
		?>
			<p>To use a different VeriShow account with your plugin, please click <a href="#" onclick="document.getElementById('login_div').style.visibility='visible'">here</a>.</p>To log onto the VeriShow platform, use the VeriShow <a href="https://platform.verishow.com/login.jsf" target="_blank">login page</a>.
		<?php
		
	}
	else
	{
		?>
			<br><label>Please <a href="https://platform.verishow.com/registration.jsf?type=free" target="_blank">Sign-up</a> for an account, and then log in below.</label>
		<?php
		$options['activated'] = "0";
		$aioseop_options['activated'] = "0";
	}

	if ($options['button_enabled']=="") $options['button_enabled']="enabled";
	

?>

            <div id="login_div" >
			<label for="error_text" id="error_text" ></label></br>
                <label>Log-in to VeriShow to complete the setup.</label></br>
				
				<form name="log-in-form" id="log-in-form" method="post" action="">
					<TABLE BORDER="0" style='font-family: Verdana;font-size: 13px;'>
					  <TR>
						<TD>Email: </TD>
						<TD>
						  <input type="text" name="lemail" id="lemail" /></br>
						</TD>
					  </TR>
						<TR>
						<TD>Password: </TD>
						<TD>
						  <input type="password" name="lpassword" /></br>
						</TD>
					  </TR>
					</table>
				
					<input type="submit" name="verishow-control-submit-li" class="button-primary" value="Log-in" />
				</br>
				</br>
				<?php
					if(isset($error_msg)) {
						echo "<font color=red><strong>".$error_msg."</strong></font><br />";
					}
				?>
				
				</br></br>Further instructions on how to use VeriShow will be sent to you by email.</br>
				<p>For any assistance needed contact <a href="mailto:support@verishow.com">support@verishow.com</a></p>
            </div>

		
	</div>
	<?php
	if (isset($options['integration']))
	{
		?>
			<script>
				document.getElementById("login_div").style.visibility='hidden';
			</script>
		
		<?php
	}
	
}

function verishow_control_init() {
	add_action('admin_menu', 'verishow_control_config_page');
}

// This is the function that adds a configuration page to settings menu group
function verishow_control_config_page() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('options-general.php', __('Verishow Configuration'), __('Verishow'), 'manage_options', 'verishow-control', 'verishow_control_conf');

}

add_action('init', 'verishow_control_init');

// Put out the styling inside head tag.
add_action('wp_head', 'verishow_control_header');

?>