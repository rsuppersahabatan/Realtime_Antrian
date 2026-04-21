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
									<h3 class="box-title"><?php echo lang('layanan_delete'); ?></h3>
								</div>
								<div class="box-body">
									<p><?php echo sprintf(lang('layanan_delete_confirm'), '<strong>'.htmlspecialchars($layanan['kode_huruf'].' - '.$layanan['nama_layanan'], ENT_QUOTES, 'UTF-8').'</strong>'); ?></p>
									<div class="callout callout-warning">
										<p><i class="fa fa-exclamation-triangle"></i> Menghapus layanan akan menghapus semua data antrian terkait (CASCADE).</p>
									</div>

									<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-delete_layanan')); ?>
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
													<?php echo anchor('admin/layanan', '<i class="fa fa-times"></i> '.lang('actions_cancel'), array('class' => 'btn btn-default btn-flat')); ?>
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
