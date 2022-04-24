<?php 
    /**
     * Delete Card Confirmation Window
     */
?>
<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"> -->
<link rel="stylesheet" href="<?php echo SQGF_PLUGIN_URL . 'assets/style/bootstrap-v4.5.css'; ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<!-- Modal HTML -->
<div id="gfsqs-confirmation" class="modal fade">
	<div class="modal-dialog modal-confirm">
		<div class="modal-content">
			<div class="modal-header flex-column">
				<div class="icon-box">
					<i class="material-icons">&#xE5CD;</i>
				</div>						
				<h4 class="modal-title w-100"><?php echo __('Are you sure?', 'gravity-forms-square'); ?></h4>	
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<p><?php echo __('Do you really want to delete card?', 'gravity-forms-square'); ?></p>
				<p><?php echo __('This process cannot be undone.', 'gravity-forms-square'); ?></p>
				<p class="in-process" style="display:none" ><?php echo __('deleting...', 'gravity-forms-square'); ?></p>
				<p class="alert alert-success" style="display:none"><?php echo __('Card has been deleted successfully.', 'gravity-forms-square'); ?></p>
				<p class="alert alert-danger" style="display:none"></p>
			</div>
			<div class="modal-footer justify-content-center">
				<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo __('Cancel', 'gravity-forms-square'); ?></button>
				<button type="button" data-form-id="" data-card-id="" class="btn btn-danger credit-card-delete"><?php echo __('Delete', 'gravity-forms-square'); ?></button>
			</div>
		</div>
	</div>
</div>