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
						<h3 class="box-title"><?php echo anchor('admin/display/create', '<i class="fa fa-plus"></i> '.lang('display_create'), array('class' => 'btn btn-block btn-primary btn-flat')); ?></h3>
					</div>
					<div class="box-body">
						<table class="table table-striped table-hover">
							<thead>
								<tr>
									<th width="60">#</th>
									<th><?php echo lang('display_id_client'); ?></th>
									<th><?php echo lang('display_color_scheme'); ?></th>
									<th><?php echo lang('display_video_source'); ?></th>
									<th><?php echo lang('display_video_link'); ?></th>
									<th><?php echo lang('display_footer_mode'); ?></th>
									<th><?php echo lang('display_font_size'); ?></th>
									<th><?php echo lang('display_action'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($display_settings as $i => $row): ?>
								<tr>
									<td><?php echo $i + 1; ?></td>
									<td><?php echo html_escape($row['nama_client']); ?></td>
									<td>
										<span style="display:inline-block;width:16px;height:16px;border:1px solid #ddd;background:<?php echo html_escape($row['color_scheme']); ?>;"></span>
										<?php echo html_escape($row['color_scheme']); ?>
									</td>
									<td><?php echo html_escape($row['video_source']); ?></td>
									<td><?php echo html_escape($row['video_link']); ?></td>
									<td><?php echo html_escape($row['footer_mode']); ?></td>
									<td><?php echo (int) $row['font_size']; ?>px</td>
									<td>
										<?php echo anchor('admin/display/edit/'.$row['id'], '<i class="fa fa-pencil"></i> '.lang('actions_edit'), array('class' => 'btn btn-xs btn-warning btn-flat')); ?>
										<?php echo anchor('admin/display/delete/'.$row['id'], '<i class="fa fa-trash"></i> '.lang('actions_delete'), array('class' => 'btn btn-xs btn-danger btn-flat')); ?>
									</td>
								</tr>
								<?php endforeach; ?>
								<?php if (empty($display_settings)): ?>
								<tr>
									<td colspan="8" class="text-center text-muted"><?php echo lang('display_not_found'); ?></td>
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
