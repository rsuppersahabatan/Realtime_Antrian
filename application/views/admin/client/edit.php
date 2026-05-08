<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

			<div class="content-wrapper">
				<section class="content-header">
					<?php echo $pagetitle; ?>
					<?php echo $breadcrumb; ?>
				</section>

				<section class="content">
					<div class="row">
						<div class="col-md-8 col-md-offset-2">
							 <div class="box">
								<div class="box-header with-border">
									<h3 class="box-title"><?php echo lang('client_edit'); ?></h3>
								</div>
								<div class="box-body">
									<?php echo $message; ?>

									<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_client')); ?>
										<div class="form-group">
											<?php echo lang('client_nama_client', 'nama_client', array('class' => 'col-sm-3 control-label')); ?>
											<div class="col-sm-9">
												<?php echo form_input($nama_client); ?>
												<p class="help-block">Contoh: Display Poli Umum (maks 50 karakter)</p>
											</div>
										</div>
										<div class="form-group">
											<?php echo lang('client_is_active', 'is_active', array('class' => 'col-sm-3 control-label')); ?>
											<div class="col-sm-9">
												<?php echo form_dropdown('is_active',
													array('ya' => lang('client_ya'), 'tidak' => lang('client_tidak')),
													$selected_status,
													'id="is_active" class="form-control"'
												); ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-3 control-label"><?php echo lang('client_lokets'); ?></label>
											<div class="col-sm-9">
<?php if (empty($loket_list)): ?>
												<p class="form-control-static text-muted"><?php echo lang('client_lokets_empty'); ?></p>
<?php else: ?>
												<div style="max-height:240px;overflow-y:auto;border:1px solid #ddd;padding:8px;border-radius:3px;">
<?php foreach ($loket_list as $l): ?>
<?php
	$lid     = (int) $l['id'];
	$checked = in_array($lid, $selected_lokets, TRUE) ? ' checked="checked"' : '';
	$label   = $l['nama_loket'] . ' (' . $l['kode_huruf'] . ' - ' . $l['nama_layanan'] . ')';
?>
													<div class="checkbox">
														<label>
															<input type="checkbox" name="id_lokets[]" value="<?php echo $lid; ?>"<?php echo $checked; ?>>
															<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
														</label>
													</div>
<?php endforeach; ?>
												</div>
												<p class="help-block"><?php echo lang('client_lokets_help'); ?></p>
<?php endif; ?>
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-offset-3 col-sm-9">
												<div class="btn-group">
													<?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
													<?php echo form_button(array('type' => 'reset',  'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
													<?php echo anchor('admin/client', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
												</div>
											</div>
										</div>
									<?php echo form_close(); ?>
								</div>
							</div>
						 </div>
					</div>
				</section>
			</div>
