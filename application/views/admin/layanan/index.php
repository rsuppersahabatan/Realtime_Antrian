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
						<div class="col-md-12">
							<?php if ($message = $this->session->flashdata('message')): ?>
							<div class="row">
								<div class="col-md-12">
									<?php echo $message; ?>
								</div>
							</div>
							<?php endif; ?>
							 <div class="box">
								<div class="box-header with-border">
									<h3 class="box-title"><?php echo anchor('admin/layanan/create', '<i class="fa fa-plus"></i> '. lang('layanan_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
								</div>
								<div class="box-body">
									<table class="table table-striped table-hover">
										<thead>
											<tr>
												<th width="60">#</th>
												<th><?php echo lang('layanan_kode_huruf'); ?></th>
												<th><?php echo lang('layanan_nama_layanan'); ?></th>
												<th><?php echo lang('layanan_keterangan'); ?></th>
												<th><?php echo lang('layanan_show_welcome'); ?></th>
												<th><?php echo lang('layanan_created_at'); ?></th>
												<th><?php echo lang('layanan_action'); ?></th>
											</tr>
										</thead>
										<tbody>
<?php foreach ($layanan as $i => $row): ?>
											<tr>
												<td><?php echo $i + 1; ?></td>
												<td><span class="label label-primary"><?php echo htmlspecialchars($row['kode_huruf'], ENT_QUOTES, 'UTF-8'); ?></span></td>
												<td><?php echo htmlspecialchars($row['nama_layanan'], ENT_QUOTES, 'UTF-8'); ?></td>
												<td><?php echo htmlspecialchars($row['keterangan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
												<td>
													<?php if ($row['show_welcome'] == 'ya'): ?>
														<span class="label label-success"><i class="fa fa-check"></i> <?php echo lang('layanan_ya'); ?></span>
													<?php else: ?>
														<span class="label label-default"><i class="fa fa-times"></i> <?php echo lang('layanan_tidak'); ?></span>
													<?php endif; ?>
												</td>
												<td><?php echo $row['created_at']; ?></td>
												<td>
													<?php echo anchor('admin/layanan/edit/'.$row['id'], lang('actions_edit'), array('class' => 'btn btn-xs btn-warning btn-flat')); ?>
													<?php echo anchor('admin/layanan/delete/'.$row['id'], lang('actions_delete'), array('class' => 'btn btn-xs btn-danger btn-flat')); ?>
												</td>
											</tr>
<?php endforeach; ?>
<?php if (empty($layanan)): ?>
											<tr>
												<td colspan="6" class="text-center text-muted"><?php echo lang('layanan_not_found'); ?></td>
											</tr>
<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>
						 </div>
					</div>
				</section>
			</div>
