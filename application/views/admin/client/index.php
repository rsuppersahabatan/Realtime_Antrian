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
									<h3 class="box-title"><?php echo anchor('admin/client/create', '<i class="fa fa-plus"></i> '. lang('client_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
								</div>
								<div class="box-body">
									<table class="table table-striped table-hover">
										<thead>
											<tr>
												<th width="60">#</th>
												<th><?php echo lang('client_nama_client'); ?></th>
												<th><?php echo lang('client_lokets'); ?></th>
												<th><?php echo lang('client_is_active'); ?></th>
												<th><?php echo lang('client_created_at'); ?></th>
												<th><?php echo lang('client_action'); ?></th>
											</tr>
										</thead>
										<tbody>
<?php foreach ($client as $i => $row): ?>
											<tr>
												<td><?php echo $i + 1; ?></td>
												<td><?php echo htmlspecialchars($row['nama_client'], ENT_QUOTES, 'UTF-8'); ?></td>
												<td>
<?php if (empty($row['lokets'])): ?>
													<span class="text-muted">&mdash;</span>
<?php else: ?>
<?php foreach ($row['lokets'] as $l): ?>
													<span class="label label-info" style="margin-right:2px;display:inline-block;margin-bottom:2px;"><?php echo htmlspecialchars($l['nama_loket'], ENT_QUOTES, 'UTF-8'); ?></span>
<?php endforeach; ?>
<?php endif; ?>
												</td>
												<td>
													<?php if ($row['is_active'] == 'ya'): ?>
														<span class="label label-success"><i class="fa fa-check"></i> <?php echo lang('client_ya'); ?></span>
													<?php else: ?>
														<span class="label label-default"><i class="fa fa-times"></i> <?php echo lang('client_tidak'); ?></span>
													<?php endif; ?>
												</td>
												<td><?php echo $row['created_at']; ?></td>
												<td>
													<?php echo anchor('client/'.$row['id'], '<i class="fa fa-tv"></i> '.lang('actions_display'), array('class' => 'btn btn-xs btn-warning btn-flat')); ?>
													<?php echo anchor('welcome/'.$row['id'], '<i class="fa fa-tv"></i> '.lang('actions_display_welcome'), array('class' => 'btn btn-xs btn-warning btn-flat')); ?>

													<?php echo anchor('admin/client/toggle_status/'.$row['id'],
														($row['is_active'] == 'ya' ? '<i class="fa fa-toggle-on"></i> Matikan Status' : '<i class="fa fa-toggle-off"></i> Jadikan Aktif'),
														array('class' => 'btn btn-xs btn-'.($row['is_active'] == 'ya' ? 'success' : 'default').' btn-flat')
													); ?>
													<?php echo anchor('admin/client/edit/'.$row['id'], '<i class="fa fa-pencil"></i> '.lang('actions_edit'), array('class' => 'btn btn-xs btn-warning btn-flat')); ?>
													<?php echo anchor('admin/client/delete/'.$row['id'], '<i class="fa fa-trash"></i> '.lang('actions_delete'), array('class' => 'btn btn-xs btn-danger btn-flat')); ?>
												</td>
											</tr>
<?php endforeach; ?>
<?php if (empty($client)): ?>
											<tr>
												<td colspan="6" class="text-center text-muted"><?php echo lang('client_not_found'); ?></td>
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
