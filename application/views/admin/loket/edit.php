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
									<h3 class="box-title"><?php echo lang('loket_edit'); ?> &mdash; <?php echo htmlspecialchars($loket['nama_loket'], ENT_QUOTES, 'UTF-8'); ?></h3>
								</div>
								<div class="box-body">
									<?php echo $message; ?>

									<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-edit_loket')); ?>
										<div class="form-group">
											<?php echo lang('loket_layanan', 'id_layanan', array('class' => 'col-sm-3 control-label')); ?>
											<div class="col-sm-9">
												<?php echo form_dropdown('id_layanan', $layanan_options, $selected_layanan, 'id="id_layanan" class="form-control"'); ?>
											</div>
										</div>
										<div class="form-group">
											<?php echo lang('loket_nama_loket', 'nama_loket', array('class' => 'col-sm-3 control-label')); ?>
											<div class="col-sm-9">
												<?php echo form_input($nama_loket); ?>
											</div>
										</div>
										<div class="form-group">
											<?php echo lang('loket_status_buka', 'status_buka', array('class' => 'col-sm-3 control-label')); ?>
											<div class="col-sm-9">
												<?php echo form_dropdown('status_buka',
													array('buka' => lang('loket_buka'), 'tutup' => lang('loket_tutup')),
													$selected_status,
													'id="status_buka" class="form-control"'
												); ?>
											</div>
										</div>
										<div class="form-group">
											<label class="col-sm-3 control-label"><?php echo lang('loket_users'); ?></label>
											<div class="col-sm-9">
<?php if (empty($users_list)): ?>
												<p class="form-control-static text-muted"><?php echo lang('loket_users_empty'); ?></p>
<?php else: ?>
												<div style="max-height:240px;overflow-y:auto;border:1px solid #ddd;padding:8px;border-radius:3px;">
<?php foreach ($users_list as $u): ?>
<?php
	$uid     = (int) $u['id'];
	$checked = in_array($uid, $selected_users, TRUE) ? ' checked="checked"' : '';
	$label   = $u['username'];
	$full    = trim($u['first_name'].' '.$u['last_name']);
	if ($full !== '') { $label .= ' ('.$full.')'; }
?>
													<div class="checkbox">
														<label>
															<input type="checkbox" name="id_users[]" value="<?php echo $uid; ?>"<?php echo $checked; ?>>
															<?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
														</label>
													</div>
<?php endforeach; ?>
												</div>
												<p class="help-block"><?php echo lang('loket_users_help'); ?></p>
<?php endif; ?>
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-offset-3 col-sm-9">
												<div class="btn-group">
													<?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
													<?php echo form_button(array('type' => 'reset',  'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
													<?php echo anchor('admin/loket', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
