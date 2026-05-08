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
									<h3 class="box-title"><?php echo lang('client_delete'); ?></h3>
								</div>
								<div class="box-body">
									<?php echo form_open(current_url(), array('class' => 'form-horizontal', 'id' => 'form-delete_client')); ?>
										<div class="form-group">
											<div class="col-sm-10 col-sm-offset-1">
												<div class="alert alert-warning">
													<?php echo sprintf(lang('client_delete_confirm'), $client['nama_client']); ?>
												</div>
											</div>
										</div>
										<div class="form-group">
											<div class="col-sm-offset-1 col-sm-10">
												<div class="btn-group">
													<?php echo form_hidden('id', $id); ?>
													<?php echo form_hidden($csrf); ?>
													<button type="submit" name="confirm" value="yes" class="btn btn-danger btn-flat"><i class="fa fa-check"></i> <?php echo lang('actions_yes'); ?></button>
													<button type="submit" name="confirm" value="no" class="btn btn-default btn-flat"><i class="fa fa-times"></i> <?php echo lang('actions_no'); ?></button>
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
