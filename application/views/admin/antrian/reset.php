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
							<div class="box box-warning">
								<div class="box-header with-border">
									<h3 class="box-title"><i class="fa fa-refresh"></i> <?php echo lang('antrian_reset_hari_ini'); ?></h3>
								</div>
								<div class="box-body">
									<div class="callout callout-danger">
										<p><?php echo sprintf(lang('antrian_reset_confirm'), $count); ?></p>
									</div>
									<p><strong><?php echo lang('antrian_tanggal'); ?>:</strong> <?php echo date('d/m/Y', strtotime($tanggal)); ?></p>

									<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-reset_antrian')); ?>
										<?php echo form_hidden($csrf); ?>
										<div class="form-group">
											<div class="col-sm-12">
												<div class="btn-group">
													<?php echo form_button(array(
														'type'    => 'submit',
														'name'    => 'confirm',
														'value'   => 'yes',
														'class'   => 'btn btn-danger btn-flat',
														'content' => '<i class="fa fa-refresh"></i> '.lang('actions_yes').', Reset Sekarang',
													)); ?>
													<?php echo anchor('admin/antrian', '<i class="fa fa-times"></i> '.lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
