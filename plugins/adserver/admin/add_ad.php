<?php namespace Habari; ?>
<?php $theme = new StacheAdmin(); ?>
<?php $theme->show( 'header' ); ?>
<?php
	if (isset($_GET['id'])) {
		$ad = Ad::get( array('id' => $_GET['id']) );
	} else {
		$ad = '';
	}
	
	$vendors = Vendors::get( array('nolimit' => true) );
?>

	<div id="title_bar">
		<?php if( is_object($ad) ) { ?>
		<h3><?php echo $ad->title; ?></h3>
		<?php } else { ?>
		<h3>Add Ad</h3>
		<?php } ?>
		<div class="add_button_holder"><a href="<?php Site::out_url('admin'); ?>/ads"> &laquo; Back to Ads</a></div>	
	</div>
	
	<div class="messagepass"></div>
	<div class="messagefail"></div>
		
	<div id="content_holder">
	<div id="inner_left_column">

	<?php if( is_object($ad) ) { ?>
		<form id="photo_item" action="<?php Site::out_url('habari'); ?>/auth_ajax/update_advert" method="post" enctype="multipart/form-data">
			<input type="hidden" value="<?php echo $ad->id; ?>" name="ad_id" id="ad_id">
	<?php } else { ?>
		<form id="photo_item" action="<?php Site::out_url('habari'); ?>/auth_ajax/add_advert" method="post" enctype="multipart/form-data">
	<?php } ?>
	
	<div class="grey_contain">
		<ul>
			<li>
			<?php if ( is_object($ad) && $ad->active == 1 ) { $active_checked = "checked"; } else { $active_checked = ''; } ?>
				<input type="checkbox" name="active" value="yes" <?php echo $active_checked ?> />&nbsp;&nbsp;Active?&nbsp;&nbsp;
			</li>
       	</ul>    
	</div>
	
	<div class="form50">
		<label for="title">Vendor:</label>
		<select name="vendor" id="vendor" class="chzn-select" data-placeholder="Choose a Vendor">
			<option></option>
			<?php foreach( $vendors as $vendor ) { ?>
				<?php if( $vendor->id == $ad->vendor_id ) { $selected = 'selected'; } else { $selected = ''; } ?>
				<option value="<?php echo $vendor->id; ?>" <?php echo $selected; ?>><?php echo $vendor->title; ?></option>
			<?php } ?>
		</select>
	</div>

	<div class="form50">
		<label for="title">Ad Type:</label>
		<select name="size" id="size" class="chzn-select" data-placeholder="Choose an Ad Type">
			<option></option>
			<?php foreach( AdPlacementInator::sizes() as $key => $value ) { ?>
				<?php if( $value == $ad->size ) { $selected = 'selected'; } else { $selected = ''; } ?>
				<option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $key; ?></option>
			<?php } ?>
		</select>
	</div>
	
	<div class="form50">
		<label for="link">Link:</label>
		<input type="text" name="link" id="link" value="<?php echo $ad ? $ad->link : ''; ?>" class="field100" tabindex="1">
	</div>
	
	<div class="form100">
		Ad:<br />
		<input name="uploaded" type="file" value="<?php $target1 ?>" tabindex="3" />
	</div>
	
	<?php if( is_object($ad) && $ad->image_url != NULL ) { ?>
		<div class="form_row">Ad:<br /><img src="<?php Site::out_url('habari'); ?>/<?php echo $ad->image_url; ?>" width="100%"></div>
    <?php } ?>		
	
	<div class="form_button_row">
	<?php if( is_object($ad) ) { ?>
		<input type="submit" name="submit" value="Update Ad" class="form_button" />
	<?php } else { ?>
		<input type="submit" name="submit" value="Add Ad" class="form_button" />
	<?php } ?>
	</div>
	
	</form>
	
	</div>
	</div>
	    		
<?php $theme->show( 'footer' ); ?>