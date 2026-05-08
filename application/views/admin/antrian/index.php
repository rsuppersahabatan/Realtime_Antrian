<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/* Helper: badge warna status */
function antrian_status_badge($status) {
	$map = [
		'menunggu'  => ['label' => 'warning', 'icon' => 'clock-o'],
		'dipanggil' => ['label' => 'info',    'icon' => 'bell'],
		'selesai'   => ['label' => 'success', 'icon' => 'check'],
		'batal'     => ['label' => 'danger',  'icon' => 'times'],
	];
	$cfg = isset($map[$status]) ? $map[$status] : ['label' => 'default', 'icon' => 'question'];
	return '<span class="label label-'.$cfg['label'].'"><i class="fa fa-'.$cfg['icon'].'"></i> '.ucfirst($status).'</span>';
}
?>

			<div class="content-wrapper">
				<section class="content-header">
					<?php echo $pagetitle; ?>
					<?php echo $breadcrumb; ?>
				</section>

				<section class="content">

					<?php if ($message = $this->session->flashdata('message')): ?>
					<div class="row">
						<div class="col-md-12"><?php echo $message; ?></div>
					</div>
					<?php endif; ?>

					<!-- Rekap Box -->
					<div class="row">
						<div class="col-md-3 col-sm-6">
							<div class="info-box bg-orange">
								<span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
								<div class="info-box-content">
									<span class="info-box-text"><?php echo lang('antrian_status_menunggu'); ?></span>
									<span class="info-box-number"><?php echo $rekap['menunggu']; ?></span>
								</div>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="info-box bg-aqua">
								<span class="info-box-icon"><i class="fa fa-bell"></i></span>
								<div class="info-box-content">
									<span class="info-box-text"><?php echo lang('antrian_status_dipanggil'); ?></span>
									<span class="info-box-number"><?php echo $rekap['dipanggil']; ?></span>
								</div>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="info-box bg-green">
								<span class="info-box-icon"><i class="fa fa-check"></i></span>
								<div class="info-box-content">
									<span class="info-box-text"><?php echo lang('antrian_status_selesai'); ?></span>
									<span class="info-box-number"><?php echo $rekap['selesai']; ?></span>
								</div>
							</div>
						</div>
						<div class="col-md-3 col-sm-6">
							<div class="info-box bg-red">
								<span class="info-box-icon"><i class="fa fa-times"></i></span>
								<div class="info-box-content">
									<span class="info-box-text"><?php echo lang('antrian_status_batal'); ?></span>
									<span class="info-box-number"><?php echo $rekap['batal']; ?></span>
								</div>
							</div>
						</div>
					</div>

					<!-- Tabel Antrian -->
					<div class="row">
						<div class="col-md-12">
							<div class="box">
								<div class="box-header with-border">
									<h3 class="box-title">
										<i class="fa fa-ticket"></i>
										<?php echo lang('menu_antrian'); ?> &mdash;
										<span class="text-primary"><?php echo date('d/m/Y', strtotime($tanggal)); ?></span>
										<small>(<?php echo array_sum($rekap); ?> total)</small>
									</h3>
									<div class="box-tools pull-right">
										<!-- Filter Tanggal -->
										<form class="form-inline" method="get" action="<?php echo site_url('admin/antrian'); ?>" style="display:inline-block;">
											<div class="input-group input-group-sm">
												<input type="date" name="tanggal" class="form-control" value="<?php echo $tanggal; ?>">
												<span class="input-group-btn">
													<button type="submit" class="btn btn-default btn-flat"><i class="fa fa-search"></i></button>
												</span>
											</div>
										</form>
										<?php echo anchor('admin/antrian/create', '<i class="fa fa-plus"></i> '.lang('antrian_create'), array('class' => 'btn btn-sm btn-primary btn-flat')); ?>
										<?php echo anchor('admin/antrian/reset', '<i class="fa fa-refresh"></i> '.lang('antrian_reset_hari_ini'), array('class' => 'btn btn-sm btn-danger btn-flat')); ?>
									</div>
								</div>
								<div class="box-body table-responsive">
									<table class="table table-striped table-hover table-bordered" id="tabel-antrian">
										<thead>
											<tr>
												<th width="50">#</th>
												<th><?php echo lang('antrian_nomor'); ?></th>
												<th><?php echo lang('antrian_nama'); ?></th>
												<th><?php echo lang('antrian_layanan'); ?></th>
												<th><?php echo lang('antrian_loket'); ?></th>
												<th><?php echo lang('antrian_status'); ?></th>
												<th><?php echo lang('antrian_waktu_ambil'); ?></th>
												<th><?php echo lang('antrian_waktu_panggil'); ?></th>
												<th><?php echo lang('antrian_waktu_selesai'); ?></th>
												<th><?php echo lang('antrian_action'); ?></th>
											</tr>
										</thead>
										<tbody>
<?php if (empty($antrian)): ?>
											<tr>
												<td colspan="9" class="text-center text-muted"><?php echo lang('antrian_kosong'); ?></td>
											</tr>
<?php else: ?>
<?php foreach ($antrian as $i => $row): ?>
											<tr class="<?php
												if ($row['status'] == 'dipanggil') echo 'active';
												elseif ($row['status'] == 'selesai') echo 'success';
												elseif ($row['status'] == 'batal')  echo 'danger';
											?>">
												<td><?php echo $i + 1; ?></td>
												<td><strong><?php echo htmlspecialchars($row['nomor_antrian'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
												<td><?php echo htmlspecialchars($row['keterangan'], ENT_QUOTES, 'UTF-8'); ?></td>
												<td>
													<span class="label label-primary"><?php echo htmlspecialchars($row['kode_huruf'], ENT_QUOTES, 'UTF-8'); ?></span>
													<?php echo htmlspecialchars($row['nama_layanan'], ENT_QUOTES, 'UTF-8'); ?>
												</td>
												<td><?php echo $row['nama_loket'] ? htmlspecialchars($row['nama_loket'], ENT_QUOTES, 'UTF-8') : '<span class="text-muted">-</span>'; ?></td>
												<td><?php echo antrian_status_badge($row['status']); ?></td>
												<td><small><?php echo $row['waktu_ambil']; ?></small></td>
												<td><small><?php echo $row['waktu_panggil'] ?: '-'; ?></small></td>
												<td><small><?php echo $row['waktu_selesai'] ?: '-'; ?></small></td>
												<td>
<?php if ($row['status'] == 'menunggu' OR $row['status'] == 'dipanggil'): ?>
													<?php echo anchor(
														'admin/antrian/update_status/'.$row['id'].'/selesai?tanggal='.$tanggal,
														'<i class="fa fa-check"></i> '.lang('antrian_btn_selesai'),
														array('class' => 'btn btn-xs btn-success btn-flat')
													); ?>
													<?php echo anchor(
														'admin/antrian/update_status/'.$row['id'].'/batal?tanggal='.$tanggal,
														'<i class="fa fa-times"></i> '.lang('antrian_btn_batal'),
														array('class' => 'btn btn-xs btn-warning btn-flat')
													); ?>
<?php endif; ?>
													<?php echo anchor(
														'admin/antrian/delete/'.$row['id'],
														'<i class="fa fa-trash"></i>',
														array('class' => 'btn btn-xs btn-danger btn-flat', 'title' => lang('actions_delete'))
													); ?>
												</td>
											</tr>
<?php endforeach; ?>
<?php endif; ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>

				</section>
			</div>
