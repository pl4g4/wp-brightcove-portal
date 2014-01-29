<?php 
/* 
Plugin Name: WP Brightcove Portal
Plugin URI: 
Description:Brightcove Portal will help you to add your brightcove videos to wordress from different brightcove accounts. Also you will be able to edit/delete videos from your brightcove account. 
Version: 1.1 
Author: Pl4g4
Author URI: 
*/

//define plugin path
define( 'BCPORTALPATH', plugin_dir_path(__FILE__) );

//check for updates
/*require 'resources/update/plugin-update-checker.php';
$MyUpdateChecker = new PluginUpdateChecker(
    '',
    __FILE__,
    'wp-brightcove-portal'
);*/


// Hook for adding admin menus
add_action('admin_menu', 'wpBrightcovePortalMenu');

// action function for above hook
function wpBrightcovePortalMenu() {
  
   add_menu_page('WP Brightcove Portal', 'Brightcove Portal', 'read', 'wp-brightcove-portal', 'wpBrightcovePortalvideos', get_option('siteurl').'/wp-content/plugins/wp-brightcove-portal/img/menuIcon.png '  );
   
   
    add_submenu_page('wp-brightcove-portal',  __('Brightcove Players','bcPortal'),  __('Brightcove Players','bcPortal'), 'read', 'wp-brightcove-portal-players', 'wpBrightcovePortalPlayers');    
    
    add_submenu_page('wp-brightcove-portal', __('Brightcove Settings','bcPortal'),  __('Brightcove Settings','bcPortal'), 'read', 'wp-brightcove-portal-settings', 'wpBrightcovePortalSettings');
    
                       
}


function wpBrightcovePortalvideos(){
	require_once('pages/videos.php');
}

function wpBrightcovePortalsettings(){
	require_once('pages/settings.php');
}

function wpBrightcovePortalPlayers(){
	require_once('pages/players.php');
}



global $bcPortal_db_version;
$bcPortal_db_version = '1.1' ;


//creating the table
function createBCPortalPlugin(){
	global $wpdb;
	global $bcPortal_db_version;

	$table_name_videos = $wpdb->prefix . "brightcoveVideos"; 
	$table_name_settings = $wpdb->prefix . "brightcoveSettings";
	$table_name_players = $wpdb->prefix . "brightcovePlayers";
	
	$sqlVideos = "
	
	
	CREATE TABLE $table_name_videos (
	
	
  		videoId int(64) NOT NULL AUTO_INCREMENT,
  		brightcoveId varchar(64) ,
  		postId		 varchar(64),
  		
  		
  		  	
  		PRIMARY KEY (videoId)
  		
  		
	)";
	
	
	$sqlSettings = "
	
	
	CREATE TABLE $table_name_settings (
	
		settingId   int(64) NOT NULL AUTO_INCREMENT ,
		bcName      varchar(64),
		tokenRead   varchar(128),
		tokenWrite  varchar(128),
		publisherId varchar(64),
  		
  		PRIMARY KEY (settingId)
  		
	)"; 
	
	
	
	$sqlPlayers = "
	
	
	CREATE TABLE $table_name_players (
	
	
  		playerId int(64) NOT NULL AUTO_INCREMENT,
  		brightcovePlayerId varchar(64),
  		settingId varchar(64),
  		playerName varchar(64),
  		width varchar(64),
  		height varchar(64),
  		  	
  		PRIMARY KEY (playerId)
  		
  		
	)"; 


	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sqlVideos);
	dbDelta($sqlSettings);
	dbDelta($sqlPlayers);
	
	
	
	update_option( "bcPortal_db_version", $bcPortal_db_version );
				
} //end function createBCPortalPlugin



//checking for DB update
function createBCPortalPlugin_update_db_check() {
    global $bcPortal_db_version;
    if (get_site_option( 'bcPortal_db_version' ) != $bcPortal_db_version) {
        createBCPortalPlugin();
    }
}
add_action( 'plugins_loaded', 'createBCPortalPlugin_update_db_check' );



//creating table
register_activation_hook(__FILE__,'createBCPortalPlugin');




//Sessions
add_action('init', 'myStartSession', 1);
add_action('wp_logout', 'myEndSession');
add_action('wp_login', 'myEndSession');

function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}

function myEndSession() {
    session_destroy ();
}


//metaBox form 
add_action( 'add_meta_boxes', 'add_bc_portal_metabox' );  
function add_bc_portal_metabox()  {  
    add_meta_box( 'bc_portal_metabox', 'Brightcove Portal', 'bc_portal_form_metabox', 'post', 'normal', 'high' );  
}  

function bc_portal_form_metabox()  {  

	global $wpdb;
	
?>   


	<script type="text/javascript">
	
		function generateShortCode(){
			
			
			var accountId = jQuery('#bcAccount').val();
			var playerdId = jQuery('#bcplayers').val();
			var videoId = jQuery('#videoId').val();
			
			
			
			jQuery.ajax({
				type: "POST",
				url: "/wp-content/plugins/wp-brightcove-portal/resources/functions.php",
				data: { playerId : playerdId , bcAccount: accountId , videoId: videoId, action: "generateCode" }
			}).done(function( data ) {

				jQuery('#shortCodeDiv').html(data);
				jQuery('#shortCodeDiv').css('display','block');
				
				parent.tinyMCE.activeEditor.setContent(parent.tinyMCE.activeEditor.getContent() + data);
				
			});
			
			
			
			
		}
	
		function selectPlayersAccount(){
			var accountId = jQuery('#bcAccount').val();
			
			/*alert(accountId);*/
			
			jQuery.ajax({
				type: "POST",
				url: "/wp-content/plugins/wp-brightcove-portal/resources/functions.php",
				data: { action: "getPlayers", bcAccount: accountId }
			}).done(function( data ) {
				
				jQuery('#bcplayers').empty();
				
				players = JSON.parse(data);
				
				var length = players.length;
				
				for (var i = 0; i < length; i++) {			
					var options = jQuery("#bcplayers");
					options.append(jQuery("<option />").val( players[i]['playerId'] ).text( players[i]['playerName'] ) );				
				}
				
				
				
				jQuery('#playerDiv').css('display','block');
				jQuery('#videoIdDiv').css('display','block');
				
			});
			

		}
	
	</script>
	
	
    <label><?php _e('Brightcove Account','bcPortal') ?></label>
    <select id="bcAccount" name="bcAccount" onchange="selectPlayersAccount();" >
    	<option value="0">Select an account</option>
	<?php
	$table_name_settings = $wpdb->prefix . "brightcoveSettings";
	$accounts = $wpdb->get_results( 
		"
		SELECT * 
		FROM $table_name_settings ;
		"
		
	);
	
	foreach ( $accounts as $account ){
	
	?>
		
		<option value="<?php echo $account->settingId; ?>" ><?php echo $account->bcName; ?></option>
	
	<?php } ?>
	</select>
	
	<div id="playerDiv" style="display:none;" >
		<label><?php _e('Players','bcPortal') ?></label>
		<select id="bcplayers" name="bcplayers"  ></select>
	</div>
	
	<div id="videoIdDiv" style="display:none;" >
		<label><?php _e('Video Id','bcPortal') ?></label>
		<input type="text" name="videoId" id="videoId" >		
	</div>
	
	<div id="shortCodeDiv" style="display:none; margin-top: 15px;" ></div>
	
	<br />
	<input type="button" onclick="generateShortCode();" value="Generate ShortCode" />
	
   <?php
     
}  


//adding shortcode
function brightcovePortal($atts) {

   extract(shortcode_atts(array(
      'videoid' => 0,
      'publisherid' => 0,
      'width' => 0,
      'height' => 0,
      'playerid' => 0,
   ), $atts));

return '

<div id="playerDiv">

	<div id="player">
		<!-- Start of Brightcove Player -->

		<div style="display:none">

		</div>

		<!--
		By use of this code snippet, I agree to the Brightcove Publisher T and C 
		found at http://corp.brightcove.com/legal/terms_publisher.cfm. 
		-->

		<script language="JavaScript" type="text/javascript" src="http://admin.brightcove.com/js/BrightcoveExperiences.js"></script>	
			<script src="http://admin.brightcove.com/js/APIModules_all.js" type="text/javascript"> </script>

		<object id="myExperience'.$videoid.'" class="BrightcoveExperience">
		  <param name="bgcolor" value="#FFFFFF" />
		  <param name="width" value="'.$width.'" />
		  <param name="height" value="'.$height.'" />
		  <param name="playerID" value="'.$playerid.'" />
		  <param name="publisherID" value="'.$publisherid.'"/>
		  <param name="isVid" value="true" />
		  <param name="isUI" value="true" />
		  <param name="wmode" value="transparent" />

		  <param name="@videoPlayer" value="'.$videoid.'" />
		</object>
	</div>

</div>
';

}

function register_bcPortalShortCode(){
	add_shortcode('bcPortal', 'brightcovePortal');
}

add_action( 'init', 'register_bcPortalShortCode');



?>
