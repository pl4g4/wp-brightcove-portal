<?php


function convertMilliseconds($ms){
	$milliseconds = $ms; // number of milliseconds
	$minutes = floor($milliseconds / (1000 * 60));
	$seconds = ceil($milliseconds % (1000 * 60) / 1000);
	echo $minutes . ':' . (($seconds < 10) ? '0' : '') . $seconds;
}


function cleanHTMLVariable($var){
	return strip_tags(trim($var));
}


if( isset($_POST['action']) && $_POST['action'] == 'getPlayers' ){
	
	require_once('../../../../wp-config.php'); 
	global $wpdb;
	$table_name = $wpdb->prefix . "brightcovePlayers";
	$bcAccount = cleanHTMLVariable($_POST['bcAccount']);	
	
	if( is_numeric($_POST['bcAccount']) ){				 
		$players = $wpdb->get_results("SELECT * FROM $table_name WHERE settingId = $bcAccount");		
		echo json_encode($players);		
	}
	
}

if( isset($_POST['action']) && $_POST['action'] == 'generateCode' ){
	
	require_once('../../../../wp-config.php'); 
	global $wpdb;
	$table_name_players = $wpdb->prefix . "brightcovePlayers";
	$table_name_settings = $wpdb->prefix . "brightcoveSettings";
	$playerId = cleanHTMLVariable($_POST['playerId']);
	$bcAccount = cleanHTMLVariable($_POST['bcAccount']);
	
	if(  is_numeric($_POST['bcAccount']) &&  is_numeric($_POST['playerId']) ){
		
		$player = $wpdb->get_row("SELECT * FROM $table_name_players WHERE playerId = $playerId");
		$account = $wpdb->get_row("SELECT * FROM $table_name_settings WHERE settingId = $bcAccount");
		
		echo ' [bcPortal width="'.$player->width.'" height="'.$player->height.'" videoid="'.$_POST['videoId'].'" publisherid="'.$account->publisherId.'" playerid="'.$player->brightcovePlayerId.'"] ';
		
	}

}


//check included files 
function checkbcMapi(){
	$included_files = get_included_files();
	
	$findFile = 'bc-mapi.php';
	
	foreach ( $included_files as $k => $v ){
	  	$pos = strpos($v, $findFile);
	  	if ($pos === false) {
		   //return false;
		} else {
		    return true;
		    break;
		}
	}	
		
		
}


?>