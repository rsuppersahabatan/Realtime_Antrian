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
						<div class="col-md-6 col-md-offset-3">
							<div class="box box-danger">
								<div class="box-header with-border">
									<h3 class="box-title"><?php echo lang('loket_delete'); ?></h3>
								</div>
								<div class="box-body">
									<p><?php echo sprintf(lang('loket_delete_confirm'), htmlspecialchars($loket['nama_loket'], ENT_QUOTES, 'UTF-8')); ?></p>
									<div class="callout callout-info">
										<p>
											<strong><?php echo lang('loket_layanan'); ?>:</strong>
											<span class="label label-primary"><?php echo htmlspecialchars($loket['kode_huruf'], ENT_QUOTES, 'UTF-8'); ?></span>
											<?php echo htmlspecialchars($loket['nama_layanan'], ENT_QUOTES, 'UTF-8'); ?>
										</p>
										<p>
											<strong><?php echo lang('loket_status_buka'); ?>:</strong>
											<?php echo ($loket['status_buka'] == 'buka')
												? '<span class="label label-success">'.lang('loket_buka').'</span>'
												: '<span class="label label-default">'.lang('loket_tutup').'</span>'; ?>
										</p>
									</div>

									<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-delete_loket')); ?>
										<?php echo form_hidden('id', $id); ?>
										<?php echo form_hidden($csrf); ?>
										<div class="form-group">
											<div class="col-sm-12">
												<div class="btn-group">
													<?php echo form_button(array(
														'type'    => 'submit',
														'name'    => 'confirm',
														'value'   => 'yes',
														'class'   => 'btn btn-danger btn-flat',
														'content' => '<i class="fa fa-trash"></i> '.lang('actions_yes').', '.lang('actions_delete'),
													)); ?>
													<?php echo anchor('admin/loket', '<i class="fa fa-times"></i> '.lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
