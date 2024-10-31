<?php
/*
Plugin Name: Roombeats Official for WordPress
Plugin URI: 
Description: Add Roombeats script to your WordPress blog. This plugin will contact the Roombeats servers and get your script. You need a <a href="http://roombeats.com" title="Roombeats">Roombeats</a> account to use it.
Version: 0.0.1
Author: Roombeats
License: GPL 2
*/

$rb_test_mode = false;

function rb_get_script($code) {
return '<script type="text/javascript" >'.
  '    (function() {'.
  '     var rbs = document.createElement("script");'.
  '     rbs.type = "text/javascript"; rbs.async = true; rbs.defer = true;'.
  '     rbs.src = "http://roombeats.com/script/'.$code.'";'.
  '     var s = document.getElementsByTagName("script")[0];'.
  '     s.parentNode.insertBefore(rbs, s);'.
  '    })();'.
  '</script>';
}
if ( ! class_exists( 'adminroombeats' )) {

	class adminroombeats {

		function addConfigPage() {

			global $wpdb;

			if ( function_exists('add_submenu_page')) {
				add_submenu_page('plugins.php', 'Roombeats Configuration', 'Roombeats', 1, basename(__FILE__), array('adminroombeats','configPage'));
			}
		}

		function configPage() {
			$rb_domain_not_found = false;
			$rb_proxy_error = false;
			$script = '';
			$save_reqd = false;
			$rb_admin = '0';
			$rb_exits = '0';

			if ( isset( $_POST['Save'] )) {
				if ( isset( $_POST['saScript'] ) && !empty( $_POST['saScript'] )) {
					$script = $_POST['saScript'];
					if ( $script[0] == '<' )
					{
						delete_option( 'rb_script' );
						add_option( 'rb_script', $script, '', 'yes' );
						$script = stripslashes( $script );
					}
				}
				delete_option( 'rb_admin' );
				if ( isset( $_POST['track_admin'] )) {
					$rb_admin = '1';
					add_option( 'rb_admin', '1', 'Roombeats code on admin pages', 'yes' );
				}
			} else if ( isset( $_POST['Clear'] )) {
				$script = '';
				delete_option( 'rb_script' );
				delete_option( 'rb_admin' );
			} else if ( isset( $_POST['Ask'] )) {
				$script = rb_get_script($_POST['rb_site_id']);
				delete_option( 'rb_admin' );
				delete_option( 'rb_exits' );
				if ( isset( $_POST['track_admin'] )) {
					$rb_admin = '1';
					add_option( 'rb_admin', '1', 'Roombeats code on admin pages', 'yes' );
				}
				$save_reqd = true;
			} else {
				$script = stripslashes( get_option( 'rb_script' ));
				$rb_admin = get_option( 'rb_admin' );
			}
			if (( true === $rb_proxy_error ) || ( true === $rb_domain_not_found )) {
				$message = $script;
			} else if (( '' !== $script ) && ( true === $save_reqd )) { 
				$message = 'Be sure to <b>save the changes!</b>';
			} else {
				$message = 'Configure Roombeats plugin by pasting the code from your dashboard, or get it filled in automaticaly by pasting your website\'s Roombeats ID!';

        if ($_POST['saScript'])
          $message = 'All settings saved. Ready to go!';
			}
			echo '<div class="wrap"><h2>Roombeats Configuration</h2>'.
        '   <p>You can get the code or the Roombeats Website ID from <a href="http://roombeats.com/dashboard">your dashboard.</a></p>'.
				'   <form style="width:100%;" action="" method="post" id="rb_script_form">'.
				'       <table width="100%" cellspacing="0" cellpadding="4" border="0" class="tborder">'.
				'           <thead>'.
				'               <tr class=""><td><b>Roombeats JavaScript</b></td></tr>'.
				'               <tr class=""><td>'.$message.'</td></tr>'.
				'           </thead>'.
				'           <tbody>'.
				'               <tr><td><input type="checkbox" id="track_admin" name="track_admin"'.($rb_admin=='1'?' checked="yes"/>':'/>').'&nbsp;<b>Roombeats code on admin pages</b></td></tr>'.
				'               <tr><td><textarea name="saScript" rows="15" cols="100">'.$script.'</textarea></td></tr>'.
				'           </tbody>'.
				'       </table>'.
				'       <p class="submit">';
				echo
        '           Your Roombeats Website ID:'.
        '           <input type="text" name="rb_site_id" value="" />';
        echo
				'           <input type="submit" name="Ask" value="Get Roombeats Script" />';
				echo
        '     </p><p class="submit">'.
				'           <input type="submit" name="Clear" value="Clear" />'.
				'           <input type="submit" name="Save" value="Save" />'.
				'       </p>'.
				'       </center>'.
				'   </form>'.
				'</div>';
		}
	} // End class adminroombeats
}

if ( !class_exists( 'filterroombeatscode' )) {

	class filterroombeatscode {

		function addroombeatscodeScript() {

			global $version;

			if (( $opt_script = stripslashes( stripslashes( get_option( 'rb_script' )))) == '' ) return;
			echo( "\n\n".'<!-- Roombeats plugin v'.$version.' for Wordpress 2.6 - 3.0 (http://www.godaddy.com/hosting/webroombeats-official.aspx?isc=WPSA1)(Begin) -->'."\n" );
			$page = $_SERVER['PHP_SELF'];
			$rb_admin = get_option( 'rb_admin' );
			if ( !strpos( $page, 'wp-admin' ) || ( $rb_admin == '1' ))
			{
				echo( '<script type="text/JavaScript">'."\n".'var rb_currentPage=\''.$_SERVER['PHP_SELF'].'\';'."\n".
				      'var rb_admin='.((get_option( 'rb_admin' ) == '1' )?'true':'false').";\n".
				      '</script>'."\n".$opt_script );
			}
			echo( "\n<!-- Roombeats plugin v".$version." for Wordpress 2.6 - 3.0 (End) -->\n\n");
		}
	} // End class filterroombeatscode
}

$version = "1.3";

$saf = new filterroombeatscode();

// add the menu item to the admin interface
add_action( 'admin_menu', array( 'adminroombeats', 'addConfigPage' ));

// add the footer so the javascript is loaded in admin and blog pages.
add_action( 'wp_footer', array( 'filterroombeatscode', 'addroombeatscodeScript' ));
add_action( 'admin_footer', array( 'filterroombeatscode', 'addroombeatscodeScript' ));
?>
