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
							<div class="box">
								<div class="box-header with-border">
									<h3 class="box-title"><i class="fa fa-plus"></i> <?php echo lang('antrian_create'); ?></h3>
								</div>
								<div class="box-body">
									<?php echo $message; ?>

									<div class="callout callout-info">
										<p><i class="fa fa-info-circle"></i> Nomor antrian akan digenerate otomatis berdasarkan kode huruf layanan dan nomor urut terakhir hari ini.</p>
									</div>

									<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-create_antrian')); ?>
										<div class="form-group">
											<?php echo lang('antrian_layanan', 'id_layanan', array('class' => 'col-sm-4 control-label')); ?>
											<div class="col-sm-8">
												<?php echo form_dropdown('id_layanan', $layanan_options, $selected_layanan, 'id="id_layanan" class="form-control"'); ?>
											</div>
										</div>
										<div class="form-group">
											<?php echo lang('antrian_nik', 'nik', array('class' => 'col-sm-4 control-label')); ?>
											<div class="col-sm-8">
												<?php echo form_input(array(
													'name'        => 'nik',
													'id'          => 'nik',
													'value'       => $selected_nik,
													'class'       => 'form-control',
													'maxlength'   => 16,
													'pattern'     => '\d{16}',
													'inputmode'   => 'numeric',
													'placeholder' => '16 digit (opsional)',
												)); ?>
											</div>
										</div>
										<div class="form-group">
											<?php echo lang('antrian_keterangan', 'keterangan', array('class' => 'col-sm-4 control-label')); ?>
											<div class="col-sm-8">
												<?php echo form_textarea(array(
													'name'        => 'keterangan',
													'id'          => 'keterangan',
													'value'       => $selected_keterangan,
													'class'       => 'form-control',
													'rows'        => 3,
													'placeholder' => 'Keterangan tambahan (opsional)',
												)); ?>
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-offset-4 col-sm-8">
												<div class="btn-group">
													<?php echo form_button(array('type' => 'submit', 'class' => 'btn btn-primary btn-flat', 'content' => '<i class="fa fa-ticket"></i> '.lang('actions_submit'))); ?>
													<?php echo anchor('admin/antrian', lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
