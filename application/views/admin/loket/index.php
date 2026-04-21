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
									<h3 class="box-title"><?php echo anchor('admin/loket/create', '<i class="fa fa-plus"></i> '. lang('loket_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
								</div>
								<div class="box-body">
									<table class="table table-striped table-hover">
										<thead>
											<tr>
												<th width="60">#</th>
												<th><?php echo lang('loket_nama_loket'); ?></th>
												<th><?php echo lang('loket_layanan'); ?></th>
												<th><?php echo lang('loket_users'); ?></th>
												<th><?php echo lang('loket_status_buka'); ?></th>
												<th><?php echo lang('loket_created_at'); ?></th>
												<th><?php echo lang('loket_action'); ?></th>
											</tr>
										</thead>
										<tbody>
<?php foreach ($loket as $i => $row): ?>
											<tr>
												<td><?php echo $i + 1; ?></td>
												<td><?php echo htmlspecialchars($row['nama_loket'], ENT_QUOTES, 'UTF-8'); ?></td>
												<td>
													<span class="label label-primary"><?php echo htmlspecialchars($row['kode_huruf'], ENT_QUOTES, 'UTF-8'); ?></span>
													<?php echo htmlspecialchars($row['nama_layanan'], ENT_QUOTES, 'UTF-8'); ?>
												</td>
												<td>
<?php if (empty($row['users'])): ?>
													<span class="text-muted">&mdash;</span>
<?php else: ?>
<?php foreach ($row['users'] as $u): ?>
													<span class="label label-info" style="margin-right:2px;display:inline-block;margin-bottom:2px;"><?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endforeach; ?>
<?php endif; ?>
												</td>
												<td>
													<?php if ($row['status_buka'] == 'buka'): ?>
														<span class="label label-success"><i class="fa fa-check"></i> <?php echo lang('loket_buka'); ?></span>
													<?php else: ?>
														<span class="label label-default"><i class="fa fa-times"></i> <?php echo lang('loket_tutup'); ?></span>
													<?php endif; ?>
												</td>
												<td><?php echo $row['created_at']; ?></td>
												<td>
													<?php echo anchor('admin/loket/toggle_status/'.$row['id'],
														($row['status_buka'] == 'buka' ? '<i class="fa fa-toggle-on"></i> Tutup' : '<i class="fa fa-toggle-off"></i> Buka'),
														array('class' => 'btn btn-xs btn-'.($row['status_buka'] == 'buka' ? 'success' : 'default').' btn-flat')
													); ?>
													<?php echo anchor('admin/loket/edit/'.$row['id'], '<i class="fa fa-pencil"></i> '.lang('actions_edit'), array('class' => 'btn btn-xs btn-warning btn-flat')); ?>
													<?php echo anchor('admin/loket/delete/'.$row['id'], '<i class="fa fa-trash"></i> '.lang('actions_delete'), array('class' => 'btn btn-xs btn-danger btn-flat')); ?>
												</td>
											</tr>
<?php endforeach; ?>
<?php if (empty($loket)): ?>
											<tr>
												<td colspan="7" class="text-center text-muted"><?php echo lang('loket_not_found'); ?></td>
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
