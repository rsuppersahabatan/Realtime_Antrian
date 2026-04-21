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
									<h3 class="box-title"><?php echo lang('layanan_create'); ?></h3>
								</div>
								<div class="box-body">
									<?php echo $message; ?>

									<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_layanan')); ?>
										<div class="form-group">
											<?php echo lang('layanan_kode_huruf', 'kode_huruf', array('class' => 'col-sm-3 control-label')); ?>
											<div class="col-sm-9">
												<?php echo form_input($kode_huruf); ?>
												<p class="help-block">Contoh: A, B, CS (maks 5 karakter, huruf kapital)</p>
											</div>
										</div>
										<div class="form-group">
											<?php echo lang('layanan_nama_layanan', 'nama_layanan', array('class' => 'col-sm-3 control-label')); ?>
											<div class="col-sm-9">
												<?php echo form_input($nama_layanan); ?>
											</div>
										</div>
										<div class="form-group">
											<?php echo lang('layanan_keterangan', 'keterangan', array('class' => 'col-sm-3 control-label')); ?>
											<div class="col-sm-9">
												<?php echo form_textarea($keterangan); ?>
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-offset-3 col-sm-9">
												<div class="btn-group">
													<?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => lang('actions_submit'))); ?>
													<?php echo form_button(array('type' => 'reset', 'class' => 'btn btn-warning btn-flat', 'content' => lang('actions_reset'))); ?>
													<?php echo anchor('admin/layanan', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
