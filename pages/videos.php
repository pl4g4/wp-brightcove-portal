<?php

//page variables
$pageTitle = __("Brightcove Accounts", 'bcPortal');
if( isset($_GET['display']) ){
	$page = $_GET['display'] ;
}else{
	$page = "videos";
}


//includes
include_once('header.php');

include_once(BCPORTALPATH.'/resources/functions.php');
$bcMapi = checkbcMapi();
if($bcMapi){
	//do not include bc-mapi
}else{
	include_once(BCPORTALPATH.'/resources/bc-mapi.php');
}


//db variables
global $wpdb;
$table_name = $wpdb->prefix . "brightcoveSettings";

//delete video
if( isset($_GET['deleteId']) && isset($_GET['bcAccount']) && is_numeric($_GET['deleteId']) && is_numeric($_GET['bcAccount']) ){


	$videoIdDelete = $_GET['deleteId'];	
	
	
	$settingId = $_GET['bcAccount'] ; 
	
	$result = $wpdb->get_row( $wpdb->prepare( 
		"
			SELECT * FROM $table_name
			WHERE settingId = '%d';
		", 
	        $settingId
	) );
	
	$tokenRead = $result->tokenRead ;	
	$tokenWrite = $result->tokenWrite ;
		
	$bc = new BCMAPI($tokenRead, $tokenWrite);
	
	try {
		$bc->delete('video', $videoIdDelete, NULL);
	?>
		<div class="alert alert-success">
		    <button type="button" class="close" data-dismiss="alert">&times;</button>
		     <?php _e('The video was deleted. Please give Brightcove and the WP Brightcove Portal some time to update the information. It can take up to 15 minutes','bcPortal') ; ?>
		</div>
	<?php
	} catch(Exception $error) {
	?>
		<div class="alert alert-error">
		    <button type="button" class="close" data-dismiss="alert">&times;</button>
		     <?php _e('An error has occurred.','bcPortal') ; ?>
		</div>
	<?php
	}
	
}


if($page == 'videos'){
	
	//getting tokens from account
	if( (isset($_POST['bcAccount']) && is_numeric($_POST['bcAccount'])) || ( isset($_GET['bcAccount']) && is_numeric($_GET['bcAccount']))  ){	
	?>
		<script type="text/javascript">
			$(document).ready(function() {
				
				$('#tableDiv').css('display','block');
				
				$('#videosTable').dataTable({"sPaginationType": "full_numbers"});
				
			} );
		</script>
	
	<?php
	
		if(isset($_GET['bcAccount'])){
			$settingId = $_GET['bcAccount'] ;
		}
		
		if(isset($_POST['bcAccount'])){
			$settingId = $_POST['bcAccount'] ; 
		}	

		$result = $wpdb->get_row( $wpdb->prepare( 
			"
				SELECT * FROM $table_name
				WHERE settingId = '%d';
			", 
		        $settingId
		) );	

		$tokenRead = $result->tokenRead ;	
		$tokenWrite = $result->tokenWrite ;
		$bc = new BCMAPI($tokenRead, $tokenWrite);
		
		 /*   future use for search specific videos
		// Define our parameters
		$params = array(
		    'video_fields' => 'id,name,shortDescription'
		);
		// Set our search terms
		$terms = array(
		    'all' => 'display_name:'.$_POST['searchVid']	   
		);
		$videos = $bc->search('video', $terms, $params);
		*/
		
		$pageNumber = $_POST['pageNumber'];
		
		$videoCount = 'http://api.brightcove.com/services/library?command=find_all_videos&video_fields=id&output=json&media_delivery=http&page_size=100&page_number='.$pageNumber.'&get_item_count=true&sort_by=CREATION_DATE&token='.$tokenRead;
		
		$ch = curl_init($videoCount);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$videoCount = json_decode(curl_exec($ch));
		curl_close($ch);
		
		//print_r($videoCount);
		
		if($tokenRead != ''){
					
			$url = 'http://api.brightcove.com/services/library?command=find_all_videos&output=rss&media_delivery=http&video_fields=id,name,shortDescription,thumbnailURL,videoStillURL&page_size=100&page_number='.$pageNumber.'&get_item_count=true&sort_by=CREATION_DATE&token='.$tokenRead;
			
			$xml= @simplexml_load_file($url);
								
		} //end if empty tokenread
		
		//print_r($xml);
	
			
	}//end form submit
	
	
	
	
	
	?>

<script type="text/javascript">
    
    function selectedAccount(){
    	$('form#selectAccount').submit();	 		    	    
    }
    
    
    function deleteAccount(videoId, bcAccount){
    	
	    bootbox.confirm("<?php _e('Are you sure to take this action?','bcPortal') ?>", function(result) {
	    	
	    	if(result){
		    	
		    	window.location = "admin.php?page=wp-brightcove-portal&deleteId="+videoId+"&bcAccount="+bcAccount ; 
		    	
	    	}
	    	
	    }); 
    }

</script>

<?php 

$table_name_settings = $wpdb->prefix . "brightcoveSettings";
$accounts = $wpdb->get_results( 
	"
	SELECT * 
	FROM $table_name_settings ;
	"
	
);


if( empty($accounts) ){ 
	
	
?>	  		
	
			<div class="alert alert-block">
				    <button type="button" class="close" data-dismiss="alert">&times;</button>
				     <?php _e('There are no accounts, Please add a <a href="/wp-admin/admin.php?page=wp-brightcove-portal-settings" >Brightcove Account</a>.','bcPortal') ; ?>
			 </div>
	
<?php }else{ ?>

<form id="selectAccount" name="selectAccount" method="get"  >
    
    	<fieldset>
	    	
	    	<!-- <legend><?php _e('Brightcove Accounts', 'bcPortal') ?></legend>-->
	    	
	    	<label><?php _e('Accounts','bcPortal') ?></label>
	    	<select id="bcAccount" name="bcAccount" onchange="selectedAccount();" >
	    	
	    	<option value="0" ><?php _e('Select the account','bcPortal') ?></option>
	    	
	    	<?php
			
			foreach ( $accounts as $account ){
			
			
			?>
				
				<option value="<?php echo $account->settingId; ?>" <?php  
				
					if($settingId == $account->settingId){
						echo 'selected="selected"';
					}
				
				?>><?php echo $account->bcName; ?></option>
			
			<?php } ?>
	    	</select>

	    	<input type="hidden" name="page" value="wp-brightcove-portal" >


	    </fieldset>
</form>


<?php } ?>


<div id="tableDiv" style="display:none;" >
	
	
	<?php if(empty($xml) || $xml == ''){ ?>	  		
	
			<div class="alert alert-block">
				    <button type="button" class="close" data-dismiss="alert">&times;</button>
				     <?php _e('There are no videos under this account.','bcPortal') ; ?>
			 </div>
	
	<?php }else{ ?>
		
		<h4><?php _e('Brightcove Videos','bcPortal') ?></h4>
	
		<table id="videosTable" class="table table-striped" >
			<thead>
				
					<tr>
						<th><?php _e('Video ID','bcPortal') ?></th>
						<th><?php _e('Video Name','bcPortal') ?></th>
						<th><?php _e('Actions','bcPortal') ?>Actions</th>
					</tr>
				
			</thead>
			<tbody>
			
				
				
				<?php foreach($xml->channel->item as $value){ ?>
				
					<?php 
					
					$videosId =  str_replace("video", "", $value->guid) ;
						
					if($videoIdDelete == $videosId){
						continue;
					}	
						
					?>
					<tr>
						<td><?php echo $videosId ; ?></td>
						<td><?php echo $value->title ?></td>
						<td>
							<a href="admin.php?page=wp-brightcove-portal&display=videoForm&videoId=<?php echo $videosId ; ?>&bcAccount=<?php echo $settingId ?>"class="btn" ><i class="icon-edit"></i> <?php _e('View/Edit','bcPortal') ?></a>
							<a onclick="deleteAccount( <?php echo $videosId ?>, <?php echo $settingId ?> );" href="#" class="btn" ><i class="icon-trash"></i> <?php _e('Delete','bcPortal') ?></a>
						</td>
					</tr>
				
				<?php } //end for each ?>
				
			</tbody>
		</table>
		
		
		<br />
		
		<?php $pageNumber = $videoCount->page_number + 1 ?>
		
		<?php if($videoCount->total_count > 100){ ?>
		<form method="post">
			<input type="hidden" name="totalVideos" value="<?php echo $videoCount->total_count; ?>" >
			<input type="hidden" name="pageNumber"value="<?php echo $pageNumber ?>"  >
			<input type="hidden" name="bcAccount"value="<?php echo $settingId ?>"  >			 
			<button type="submit" class="btn">Load More Videos</button>
		</form>
		<?php } ?>
	
	<?php } ?>
	
	

	
	
	
	
</div><!-- end tableDiv -->

<?php } // end videos if 
	

	
if($page == 'videoForm'){


	if( ( isset($_GET['videoId']) && is_numeric($_GET['videoId']) && is_numeric($_GET['bcAccount']) && isset($_GET['bcAccount']) ) ){
		
		
		$videoId = $_GET['videoId'];	
		$settingId = $_GET['bcAccount'] ; 
		
		$result = $wpdb->get_row( $wpdb->prepare( 
			"
				SELECT * FROM $table_name
				WHERE settingId = '%d';
			", 
		        $settingId
		) );
		
		$tokenRead = $result->tokenRead ;	
		$tokenWrite = $result->tokenWrite ;
		$bc = new BCMAPI($tokenRead, $tokenWrite);
		
		
		$video = $bc->find('find_video_by_id', $videoId);
		
		$videoName = $video->name ;
		$videoDescription = $video->shortDescription ;	
		$videoTags = implode(",", $video->tags );	
	
		$creationDate = $video->creationDate/1000;
		$publishedDate = $video->publishedDate/1000;
		$lastModifiedDate = $video->lastModifiedDate/1000;
		$creationDate = date("M j, Y", $creationDate);
		$publishedDate = date("M j, Y", $publishedDate);
		$lastModifiedDate = date("M j, Y", $lastModifiedDate);
		
		
				
		if( isset($_POST['saveVideo']) ){
				
			$videoName = cleanHTMLVariable($_POST['videoName']) ;
			$videoDescription = cleanHTMLVariable($_POST['videoDescription']) ;
			$tags = explode(",", cleanHTMLVariable($_POST['tags']) )  ;
			$videoTags = implode(",", $tags );
			
			$metaData = array(
			    'id' => $videoId,
			    'name' => substr($videoName, 0, 250),
			    'shortDescription' => substr($videoDescription, 0, 250),
			    'tags' => $tags
			);
			
			try {
				$bc->update('video', $metaData);				
			?>
			
				 <div class="alert alert-success">
			     	<button type="button" class="close" data-dismiss="alert">&times;</button>
			     	<?php _e('Video Saved, Please give Brightcove and the WP Brightcove Portal some time to update the information. It can take up to 15 minutes','bcPortal') ; ?> 
			     </div>
				
			<?php	
			}catch(Exception $error) {		   
			    echo $error;
			?>
			   
			   	<div class="alert alert-error">
			    	<button type="button" class="close" data-dismiss="alert">&times;</button>
			    	<?php _e('There was an error when saving the information, please try again.','bcPortal') ; ?> 
			    </div>
	
			   
			<?php		   
	    	}
			
		}
					
	?>
		
		<script type="text/javascript">
			$(function () { $("input,select,textarea").not("[type=submit]").jqBootstrapValidation({
				sniffHtml: false
			}); } );
		</script>
		
		<div class="row-fluid">
		
		
			 <div class="span4">
		
				<form method="post" id="videoForm" name="videoForm" >
				
					<fieldset>
					    	<legend><?php _e('Brightcove Form', 'bcPortal') ?></legend>
					
							<label><?php _e('Name','bcPortal') ?></label>
							<input class="textVideoForm" type="text" name="videoName" value="<?php echo $videoName ?>" required  data-validation-required-message="<?php _e('Name is required','bcPortal') ?>" >
							
							<label><?php _e('Description','bcPortal') ?></label>
							<textarea class="textAreaShortDesc" name="videoDescription" data-validation-maxlength-message="<?php _e('Maximun Chars reached','bcPortal') ?>" data-validation-required-message="<?php _e('Description is required','bcPortal') ?>" required maxlength="250" ><?php echo $videoDescription ?></textarea>
							<span class="help-block"><?php _e('Maximum of 250 chars','bcPortal') ?></span>
							
							<label><?php _e('Tags','bcPortal') ?></label>
							<input class="textVideoForm" type="text" name="tags" value="<?php echo $videoTags ?>" required  data-validation-required-message="<?php _e('Tags are required','bcPortal') ?>">
							<span class="help-block"><?php _e('Separate tags with commas','bcPortal') ?></span>
							
							<input type="hidden" name="videoId" value="<?php echo $videoId ?>" >	
							<input type="hidden" name="bcAccount" value="<?php echo $settingId ?>" >
								
							<br />		 
							<button type="submit" name="saveVideo" class="btn">Save Video</button>
							<a href="admin.php?page=wp-brightcove-portal&bcAccount=<?php echo $settingId ?>" class="btn" ><?php _e('Back to Portal','bcPortal') ?></a>
							
					</fieldset>
					
				</form>
		</div>
	
		<div class="span8">
		
			<h4>Advanced Video Information</h4>  	                                
		                                
			<table id="advanceInfoTable" class="table">
			  <tr>
			    <td><?php _e('Thumbnail','bcPortal') ?></td>		   
			    <td><img src="<?php echo $video->thumbnailURL ; ?>" title="Thumbnail" /></td>
			  </tr>
			  <tr>
			    <td><?php _e('Creation Date','bcPortal') ?></td>
			    <td><?php echo $creationDate ; ?></td>
			  </tr>
			  <tr>
			     <td><?php _e('Published Date','bcPortal') ?></td>
			    <td><?php echo $publishedDate ; ?></td>
			  </tr>
			  <tr>
			     <td><?php _e('Last Modified Date','bcPortal') ?></td>
			    <td><?php echo $lastModifiedDate ; ?></td>
			  </tr>
			  <tr>
			     <td><?php _e('Lenght','bcPortal') ?></td>
			    <td><?php convertMilliseconds($video->length) ; ?></td>
			  </tr>
			  <tr>
			  	 <td><?php _e('Times this Video has been played since it was created','bcPortal') ?></td>
			    <td><?php echo $video->playsTotal ; ?></td>
			  </tr>
			  <tr>
			  	<td><?php _e('Times this Video has been played within the past seven days','bcPortal') ?></td>
			    <td><?php echo $video->playsTrailingWeek ; ?></td>
			  </tr>
			</table>
	
		</div>
		
		
		</div>
	
		
	<?php }else{ ?>
	
	
		<div class="alert alert-error">
				    <button type="button" class="close" data-dismiss="alert">&times;</button>
				     <?php _e('The Video does not exits.','bcPortal') ; ?>
		</div>
		<a href="admin.php?page=wp-brightcove-portal" class="btn" ><?php _e('Back to Portal','bcPortal') ?></a>
	
	<?php
	
	} // end isset $_GET video ID

} //end video form ?>





<?php
include_once('footer.php');
?>