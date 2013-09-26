<?php

//page variables
$pageTitle = __("Brightcove Settings", 'bcPortal');
if( isset($_GET['display']) ){
	$page = $_GET['display'] ;
}else{
	$page = "accountsTable";
}

//includes
include_once('header.php');
include_once(BCPORTALPATH.'/resources/functions.php');

//declare database variables
global $wpdb;
$table_name = $wpdb->prefix . "brightcoveSettings";
$table_name_players = $wpdb->prefix . "brightcovePlayers";
$settingId;

//delete account
if( isset($_GET['deleteId']) ){
	$deleteId = $_GET['deleteId'];
	
	if (!is_numeric($deleteId)) {
        exit;
    } 	
		
	$players = $wpdb->get_results( 
	"
	SELECT *
	FROM $table_name_players
	WHERE settingId =  $deleteId
		
	"
	);
	
	if( empty($players) ){
		
		$wpdb->delete( $table_name, array( 'settingId' => $deleteId ), array( '%d' ) );
		
		if($wpdb->rows_affected > 0 ){
		?>
			<div class="alert alert-success">
			    <button type="button" class="close" data-dismiss="alert">&times;</button>
			     <?php _e('The account was deleted.','bcPortal') ; ?>
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
		
	}else{
		?>
			<div class="alert alert-error">
			    <button type="button" class="close" data-dismiss="alert">&times;</button>
			     <?php _e('You cannot delete an account if there is a player associated to this account.','bcPortal') ; ?>
			</div>
		<?php
	}
	
	
	
	
	
}

//get ID 
if( isset($_GET['settingId']) ){

	$settingId = $_GET['settingId'];
	
	if (!is_numeric($settingId)) {
        exit;
    } 
	
	$result = $wpdb->get_row( $wpdb->prepare( 
	"
		SELECT * FROM $table_name
		WHERE settingId = %d ;
		
	", 
        $settingId
	
    ) );
	
	$settingId   = $result->settingId;
	$bcName      = $result->bcName;
	$tokenRead   = $result->tokenRead ;
	$tokenWrite  = $result->tokenWrite ;
	$publisherId = $result->publisherId;
	
	
	if(empty($result)){
		?>
			     
			         <div class="alert alert-error">
					    <button type="button" class="close" data-dismiss="alert">&times;</button>
					     <?php _e('The account does not exits.','bcPortal') ; ?>
					 </div>
					 <a href="admin.php?page=wp-brightcove-portal-settings" class="btn" ><?php _e('Back to Settings','bcPortal') ?></a>
		<?php  
		exit;
	}
	
	
}



//Form 
if($page == 'settingsForm'){

	//add new account
	if( isset($_POST['saveSettings']) ){
	
		//check csrfToken
		if($_SESSION['csrfToken'] == $_POST['csrfToken']){
	
			$settingId = cleanHTMLVariable($_POST['settingId']);
			$bcName = cleanHTMLVariable($_POST['bcName']);
			$tokenRead = cleanHTMLVariable($_POST['tokenRead']);
			$tokenWrite = cleanHTMLVariable($_POST['tokenWrite']);
			$publisherId = cleanHTMLVariable($_POST['publisherId']);
			
			$wpdb->query( $wpdb->prepare( 
			"
				INSERT INTO $table_name
				( settingId, bcName, tokenRead, tokenWrite, publisherId )
				VALUES ( '%s', '%s', '%s', '%s', '%s' )
				ON DUPLICATE KEY UPDATE 
				bcName = '%s' ,
				tokenRead = '%s' ,
				tokenWrite = '%s' ,
				publisherId = '%s'
				
				;
			", 
				$settingId,
				$bcName,
		        $tokenRead, 
		        $tokenWrite, 
		        $publisherId,
		        $bcName,
		        $tokenRead, 
		        $tokenWrite, 
		        $publisherId 
		        
		    ) ); 
		    
		    
		    $affectedRows = $wpdb->rows_affected;
		    
		    if( $affectedRows > 0){
			 			     
			     
			?>
			     
			         <div class="alert alert-success">
					    <button type="button" class="close" data-dismiss="alert">&times;</button>
					    <?php _e('Account Saved','bcPortal') ; ?>
					 </div>
			     
			     
			<?php
			     $settingId = $wpdb->insert_id;
		    }else{
			     
			?>
			     
			         <div class="alert alert-block">
					    <button type="button" class="close" data-dismiss="alert">&times;</button>
					     <?php _e('You did not make any change to the account.','bcPortal') ; ?>
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



    <form id="settingsForm" name="settingsForm" method="post" action='admin.php?page=wp-brightcove-portal-settings&display=settingsForm'>
    
    	<fieldset>
	    	<legend><?php _e('Brightcove Form', 'bcPortal') ?></legend>
	    	
	    	<label><?php _e('Account Name','bcPortal') ?></label>
	    	<input type="text" id="bcName" name="bcName" value="<?php echo $bcName ?>" required data-validation-required-message="<?php _e('Account Name is Required','bcPortal') ?>"  >
	    	
	    	<label><?php _e('Token Read','bcPortal') ?></label>
	    	<input type="text" id="tokenRead" name="tokenRead" value="<?php echo $tokenRead ?>" required data-validation-required-message="<?php _e('Token Read is Required','bcPortal') ?>"  >
	    	
	    	<label><?php _e('Token Write','bcPortal') ?></label>
	    	<input type="text" id="tokenWrite" name="tokenWrite" value="<?php echo $tokenWrite ?>" required data-validation-required-message="<?php _e('Token Write is Required','bcPortal') ?>"  >
	    	
	    	<label><?php _e('Publisher ID','bcPortal') ?></label>
	    	<input type="text" id="publisherId" name="publisherId" value="<?php echo $publisherId ?>" required data-validation-required-message="<?php _e('Publisher Id is Required','bcPortal') ?>"  >
	    		    	
	
	    	<input type="hidden" id="settingId" name="settingId" value="<?php echo $settingId ?>">
	    	
	    	<input type="hidden" name="csrfToken" value="<?php echo $token ; ?>" />
	    
	    	<br />
	    	<button type="submit" name="saveSettings" class="btn"><?php _e('Save Account','bcPortal') ?></button>
	    	<a href="admin.php?page=wp-brightcove-portal-settings" class="btn" ><?php _e('Cancel','bcPortal') ?></a>
	    </fieldset>
    </form>

<?php

}// end if add/update FORM


//table
if($page == 'accountsTable'){


	$results = $wpdb->get_results( $wpdb->prepare( 
	"
		SELECT * FROM $table_name ;
	"
		
    ) );

    
    //print_r($results);   

?>

<script type="text/javascript">

	$(document).ready(function() {
    	$('#accounts').dataTable({"sPaginationType": "full_numbers"});
    } );
    
    
    function deleteAccount(settingId){
    	
	    bootbox.confirm("<?php _e('Are you sure to take this action?','bcPortal') ?>", function(result) {
	    	
	    	if(result){
		    	
		    	window.location = "admin.php?page=wp-brightcove-portal-settings&deleteId="+settingId ; 
		    	
	    	}
	    	
	    }); 
    }
    
    
    
    

</script>


<?php

if( empty($results) ){ 
	
?>	  		
	
			<div class="alert alert-block">
				    <button type="button" class="close" data-dismiss="alert">&times;</button>
				     <?php _e('There are no Brightcove Account, Please add a New Account.','bcPortal') ; ?>
			 </div>
	
<?php }else{ ?>

<h4><?php _e('Brightcove Accounts','bcPortal') ?></h4>

<table id="accounts" class="table table-striped" >
	<thead>
		
			<tr>
				<th><?php _e('Account Name','bcPortal') ?></th>
				<th><?php _e('Publisherd Id','bcPortal') ?></th>
				<th><?php _e('Actions','bcPortal') ?>Actions</th>
			</tr>
		
	</thead>
	<tbody>
		
		<?php foreach($results as $account){ ?>
		
			<tr>
				<td><?php echo $account->bcName ?></td>
				<td><?php echo $account->publisherId ?></td>
				<td>
					<a href="admin.php?page=wp-brightcove-portal-settings&display=settingsForm&settingId=<?php echo $account->settingId ; ?>" class="btn" ><i class="icon-edit"></i> <?php _e('View/Edit','bcPortal') ?></a>
					<a href="#" onclick="deleteAccount( <?php echo $account->settingId ?> );" class="btn" ><i class="icon-trash"></i> <?php _e('Delete','bcPortal') ?></a>
				</td>
			</tr>
		
		<?php } //end for each ?>
		
	</tbody>
</table>


<?php } ?>


<br />

<a href="admin.php?page=wp-brightcove-portal-settings&display=settingsForm" class="btn" ><i class="icon-plus-sign"></i> <?php _e('Add Account','bcPortal') ?></a>


<?php


} // end if accountsTable

include_once('footer.php');
?>