<?php

//page variables
$pageTitle = __("Brightcove Players", 'bcPortal');
if( isset($_GET['display']) ){
	$page = $_GET['display'] ;
}else{
	$page = "playersTable";
}

//includes
include_once('header.php');
include_once(BCPORTALPATH.'/resources/functions.php');

//declare database variables
global $wpdb;
$table_name = $wpdb->prefix . "brightcovePlayers";
$playerId;

//delete account
if( isset($_GET['deleteId']) ){
	$deleteId = $_GET['deleteId'];
	
	if (!is_numeric($deleteId)) {
        exit;
    } 
	
	$wpdb->delete( $table_name, array( 'playerId' => $deleteId ), array( '%d' ) );
	
	
	if($wpdb->rows_affected > 0 ){
	?>
		<div class="alert alert-success">
		    <button type="button" class="close" data-dismiss="alert">&times;</button>
		     <?php _e('The player was deleted.','bcPortal') ; ?>
		</div>
	<?php
	}else{
	?>
		<div class="alert alert-error">
		    <button type="button" class="close" data-dismiss="alert">&times;</button>
		     <?php _e('An error has occurred.','bcPortal') ; ?>
		</div>
	<?php
	}
}

//get ID 
if( isset($_GET['playerId']) ){

	$playerId = $_GET['playerId'];
	
	if (!is_numeric($playerId)) {
        exit;
    } 
	
	$result = $wpdb->get_row( $wpdb->prepare( 
	"
		SELECT * FROM $table_name
		WHERE playerId = %d ;
		
	", 
        $playerId
	
    ) );
	
	$playerId  = $result->playerId;
	$brightcovePlayerId      = $result->brightcovePlayerId;
	$playerName  = $result->playerName ;
	$width  = $result->width ;
	$height = $result->height;
	$settingId = $result->settingId;
	
	
	if(empty($result)){
		?>		     
	         <div class="alert alert-error">
			    <button type="button" class="close" data-dismiss="alert">&times;</button>
			     <?php _e('The player does not exits.','bcPortal') ; ?>
			 </div>
			 <a href="admin.php?page=wp-brightcove-portal-players" class="btn" ><?php _e('Back to Players','bcPortal') ?></a>
		<?php  
		exit;
	}
	
	
}



//Form 
if($page == 'playersForm'){


	//add new account
	if( isset($_POST['savePlayer']) ){
	
		//check csrfToken
		if($_SESSION['csrfToken'] == $_POST['csrfToken']){
	
			$playerId = cleanHTMLVariable($_POST['playerId']);
			$brightcovePlayerId = cleanHTMLVariable($_POST['brightcovePlayerId']);
			$playerName = cleanHTMLVariable($_POST['playerName']);
			$width =cleanHTMLVariable( $_POST['width']);
			$height = cleanHTMLVariable($_POST['height']);
			$settingId = cleanHTMLVariable($_POST['bcAccount']);
			
			$wpdb->query( $wpdb->prepare( 
			"
				INSERT INTO $table_name
				( playerId, brightcovePlayerId, playerName, width, height, settingId )
				VALUES ( '%s', '%s', '%s', '%s', '%s', '%s' )
				ON DUPLICATE KEY UPDATE 
				brightcovePlayerId = '%s' ,
				playerName = '%s' ,
				width = '%s' ,
				height = '%s',
				settingId = '%s'
				
				;
			", 
				$playerId,
				$brightcovePlayerId,
		        $playerName, 
		        $width, 
		        $height,
		        $settingId,
		        $brightcovePlayerId, 
		        $playerName, 
		        $width, 
		        $height,
		        $settingId
		        
		    ) ); 
		    
		    
		    $affectedRows = $wpdb->rows_affected;
		    
		    if( $affectedRows > 0){
			    
			     
			     
			?>
			     
			         <div class="alert alert-success">
					    <button type="button" class="close" data-dismiss="alert">&times;</button>
					    <?php _e('Player Saved','bcPortal') ; ?> 
					 </div>
			     
			     
			<?php
			     $playerId = $wpdb->insert_id;
		    }else{
			     
			?>
			     
			         <div class="alert alert-block">
					    <button type="button" class="close" data-dismiss="alert">&times;</button>
					     <?php _e('You did not make any change to the player.','bcPortal') ; ?>
					 </div>
			     
			     
			<?php     
		    }
		    
		    //echo $wpdb->insert_id ; 
		    //echo $wpdb->num_rows;
		    //echo $wpdb->rows_affected;
	    
	    }else{
		    _e('Invalid Token','bcPortal');
		    exit;
	    }
	   	
	}
	
	
	//CSRF
	//generate the token
	$token = md5(uniqid());
	$_SESSION['csrfToken'] = $token;

?>

	
	<script type="text/javascript">
		$(function () { $("input,select,textarea").not("[type=submit]").jqBootstrapValidation({
			sniffHtml: false
		}); } );
	</script>



    <form id="settingsForm" name="settingsForm" method="post" action='admin.php?page=wp-brightcove-portal-players&display=playersForm'>
    
    	<fieldset>
	    	<legend><?php _e('Brightcove Player', 'bcPortal') ?></legend>
	    	
	    	<label><?php _e('Player Name','bcPortal') ?></label>
	    	<input type="text" id="playerName" name="playerName" value="<?php echo $playerName ?>" required data-validation-required-message="<?php _e('Player Name is Required','bcPortal') ?>"  >
	    	
	    	<label><?php _e('Brightcove Player ID','bcPortal') ?></label>
	    	<input type="text" id="brightcovePlayerId" name="brightcovePlayerId" value="<?php echo $brightcovePlayerId ?>" required data-validation-required-message="<?php _e('Brightcove Id is Required','bcPortal') ?>"  >
	    	
	    	<label><?php _e('Player Width','bcPortal') ?></label>
	    	<input type="text" id="width" name="width" value="<?php echo $width ?>" required data-validation-required-message="<?php _e('Player Width is Required','bcPortal') ?>"  >
	    	
	    	<label><?php _e('Player Height','bcPortal') ?></label>
	    	<input type="text" id="height" name="height" value="<?php echo $height ?>" required data-validation-required-message="<?php _e('Player height is Required','bcPortal') ?>"  >
	    	
	    	<label><?php _e('Brightcove Account','bcPortal') ?></label>
	    	<select id="bcAccount" name="bcAccount" >
	    	
	    	<option value="0">Select Account</option>
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
				
				<option value="<?php echo $account->settingId; ?>" 
				
				<?php
					
					if($account->settingId == $settingId){
						echo 'selected="selected"';
					}
					
				?>
				
				><?php echo $account->bcName; ?></option>
			
			<?php } ?>
	    	</select>

	    		    	
	
	    	<input type="hidden" id="playerId" name="playerId" value="<?php echo $playerId ?>">
	    	
	    	<input type="hidden" name="csrfToken" value="<?php echo $token ; ?>" />
	    
	    	<br />
	    	<button type="submit" name="savePlayer" class="btn"><?php _e('Save Player','bcPortal') ?></button>
	    	<a href="admin.php?page=wp-brightcove-portal-players" class="btn" ><?php _e('Cancel','bcPortal') ?></a>
	    </fieldset>
    </form>

<?php

}// end if add/update FORM


//table
if($page == 'playersTable'){


	$results = $wpdb->get_results( $wpdb->prepare( 
	"
		SELECT * FROM $table_name ;
	"
		
    ) );
 

?>

<script type="text/javascript">

	$(document).ready(function() {
    	$('#accounts').dataTable({"sPaginationType": "full_numbers"});
    } );
    
    
    function deleteAccount(playerId){
    	
	    bootbox.confirm("<?php _e('Are you sure to take this action?','bcPortal') ?>", function(result) {
	    	
	    	if(result){
		    	
		    	window.location = "admin.php?page=wp-brightcove-portal-players&deleteId="+playerId ; 
		    	
	    	}
	    	
	    }); 
    }
    
</script>

<?php

if( empty($results) ){ 
	
?>	  		
	
			<div class="alert alert-block">
				    <button type="button" class="close" data-dismiss="alert">&times;</button>
				     <?php _e('There are no players, Please add a New Player.','bcPortal') ; ?>
			 </div>
	
<?php }else{ ?>


	<h4><?php _e('Brightcove Players','bcPortal') ?></h4>
	
	<table id="players" class="table table-striped" >
		<thead>
			
				<tr>
					<th><?php _e('Player Name','bcPortal') ?></th>
					<th><?php _e('Brightcove Id','bcPortal') ?></th>
					<th><?php _e('Actions','bcPortal') ?>Actions</th>
				</tr>
			
		</thead>
		<tbody>
			
			<?php foreach($results as $account){ ?>
			
				<tr>
					<td><?php echo $account->playerName ?></td>
					<td><?php echo $account->brightcovePlayerId ?></td>
					<td>
						<a href="admin.php?page=wp-brightcove-portal-players&display=playersForm&playerId=<?php echo $account->playerId ; ?>" class="btn" ><i class="icon-edit"></i> <?php _e('View/Edit','bcPortal') ?></a>
						<a href="#" onclick="deleteAccount( <?php echo $account->playerId ?> );" class="btn" ><i class="icon-trash"></i> <?php _e('Delete','bcPortal') ?></a>
					</td>
				</tr>
			
			<?php } //end for each ?>
			
		</tbody>
	</table>
	
	<?php } ?>

<br />

<a href="admin.php?page=wp-brightcove-portal-players&display=playersForm" class="btn" ><i class="icon-plus-sign"></i> <?php _e('Add Player','bcPortal') ?></a>


<?php


} // end if accountsTable

include_once('footer.php');
?>