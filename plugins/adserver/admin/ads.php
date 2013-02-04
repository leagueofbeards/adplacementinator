<?php namespace Habari; ?>
<?php 
$theme = new StacheAdmin();
$theme->show('header');
$rows = Ads::get( array('nolimit' => true) );
?>
<div id="title_bar">
	<h3>Ads</h3>
	<div class="add_button_holder"><a href="<?php Site::out_url('admin'); ?>/add_vendor">Add New Vendor &raquo;</a> &nbsp; <a href="<?php Site::out_url('admin'); ?>/add_ad">Add New Advertisement &raquo;</a></div>	
</div>
<div id="content_holder">
<div class="innerpad">   
	<table width="100%" border="0" cellspacing="0" id="data_sort">
    	<thead>
    	<tr>
	        <th width="2">&nbsp;</th>
        	<th width="2">Active?</th>
        	<th>Vendor</th>
        	<th>Size</th>
        	<th>URL</th>
        	<th>Views</th>
        	<th>Clicks</th>
	        <th width="1">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach( $rows as $row ) { ?>
	        <tr id="<?php echo $rows->id; ?>">
	        <td><a href="<?php Site::out_url('admin'); ?>/add_ad?id=<?php echo $row->id; ?>"><img src="<?php $theme->image('edit.png'); ?>" alt="Edit"></a></td>
	        <?php if( $row->active == 1 ) { ?>
	        	<td align="center"><img src="<?php $theme->image('checked.png'); ?>"></td>
        	<?php } else { ?>
	        	<td>&nbsp;</td>
        	<?php } ?>
        	<td><?php echo Vendor::get( array('id' => $row->vendor_id) )->title; ?>
        	<td><?php echo $row->size; ?></td>
        	<td><?php echo $row->link; ?></td>
        	<td><?php echo $row->views; ?></td>
        	<td><?php echo $row->clicks; ?></td>
        	<td><a href=""><img src="<?php $theme->image('delete.png'); ?>" alt="Delete"></a></td>
		</tr>
		<?php } ?>
		</tbody>
		</table>
</div>
<?php $theme->show('footer'); ?>