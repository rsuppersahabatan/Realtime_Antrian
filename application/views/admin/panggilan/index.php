<?php
defined('BASEPATH') OR exit('No direct script access allowed');
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

					<div class="row">
						<div class="col-md-12">
							<div class="box">
								<div class="box-header with-border">
									<h3 class="box-title">
										<i class="fa fa-bullhorn"></i>
										<?php echo lang('panggilan_title'); ?>
										<small>&mdash; <?php echo lang('panggilan_subtitle'); ?></small>
									</h3>
									<div class="box-tools pull-right">
										<span id="socket-status" class="label label-default">
											<i class="fa fa-plug"></i> <?php echo lang('panggilan_disconnected'); ?>
										</span>
									</div>
								</div>
								<div class="box-body">
<?php if (empty($loket)): ?>
									<div class="callout callout-warning">
										<p><?php echo sprintf(lang('panggilan_no_loket'), anchor('admin/loket', lang('menu_loket'))); ?></p>
									</div>
<?php else: ?>
									<div class="row">
<?php foreach ($loket as $row): ?>
										<div class="col-md-4 col-sm-6">
											<div class="box box-primary box-solid loket-card" data-id="<?php echo (int) $row['id']; ?>">
												<div class="box-header with-border">
													<h3 class="box-title">
														<i class="fa fa-desktop"></i>
														<?php echo htmlspecialchars($row['nama_loket'], ENT_QUOTES, 'UTF-8'); ?>
													</h3>
												</div>
												<div class="box-body text-center bg-white">
													<p class="text-muted">
														<span class="label label-info"><?php echo htmlspecialchars($row['kode_huruf'], ENT_QUOTES, 'UTF-8'); ?></span>
														<?php echo htmlspecialchars($row['nama_layanan'], ENT_QUOTES, 'UTF-8'); ?>
													</p>
													<h1 class="loket-nomor text-primary" style="font-size:72px;margin:10px 0;line-height:1;">&mdash;</h1>
													<div class="btn-group btn-group-justified" role="group">
														<div class="btn-group" role="group">
															<a href="#" class="btn btn-primary btn-flat btn-call" data-id="<?php echo (int) $row['id']; ?>">
																<i class="fa fa-bullhorn"></i> <?php echo lang('panggilan_btn_panggil'); ?>
															</a>
														</div>
														<div class="btn-group" role="group">
															<a href="#" class="btn btn-default btn-flat btn-recall" data-id="<?php echo (int) $row['id']; ?>">
																<i class="fa fa-repeat"></i> <?php echo lang('panggilan_btn_ulangi'); ?>
															</a>
														</div>
													</div>
												</div>
											</div>
										</div>
<?php endforeach; ?>
									</div>
<?php endif; ?>
								</div>
							</div>
						</div>
					</div>

				</section>
			</div>

<script type="text/javascript">
/* Pemutaran audio dilakukan di display via TTS (server.js + tts.js).
   Admin panel cukup mem-broadcast event panggilan via Redis — tidak memutar
   suara lokal supaya tidak terdengar dobel kalau operator & display berada
   di mesin/speaker yang sama. */

document.addEventListener('DOMContentLoaded', function () {
	var callUrl   = '<?php echo site_url('admin/panggilan/call'); ?>';
	var recallUrl = '<?php echo site_url('admin/panggilan/recall'); ?>';
	var sockUrl   = <?php $__s = $this->config->item('socket_url'); echo $__s ? json_encode($__s) : "window.location.protocol + '//' + window.location.host"; ?>;

	var LABEL = {
		error       : '<?php echo lang('panggilan_error'); ?>',
		connected   : '<?php echo lang('panggilan_connected'); ?>',
		disconnected: '<?php echo lang('panggilan_disconnected'); ?>',
		reconnecting: '<?php echo lang('panggilan_reconnecting'); ?>'
	};

	/* ---------- AJAX ---------- */
	function toggleBusy($card, busy) {
		$card.find('.btn-call, .btn-recall').toggleClass('disabled', busy);
	}

	function callLoket(idLoket) {
		var $card = $('.loket-card[data-id="' + idLoket + '"]');
		toggleBusy($card, true);

		$.ajax({
			url     : callUrl + '/' + idLoket,
			type    : 'POST',
			dataType: 'json'
		}).done(function (res) {
			if (res.status === 'ok') {
				$card.find('.loket-nomor').text(res.nomor);
			} else {
				alert(res.message || LABEL.error);
			}
		}).fail(function () {
			alert(LABEL.error);
		}).always(function () {
			toggleBusy($card, false);
		});
	}

	function recallLoket(idLoket) {
		var $card = $('.loket-card[data-id="' + idLoket + '"]');
		var nomor = $.trim($card.find('.loket-nomor').text());
		if ( ! nomor || nomor === '—') return;

		toggleBusy($card, true);

		$.ajax({
			url     : recallUrl + '/' + idLoket + '/' + encodeURIComponent(nomor),
			type    : 'POST',
			dataType: 'json'
		}).done(function (res) {
			if (res.status !== 'ok') {
				alert(res.message || LABEL.error);
			}
		}).fail(function () {
			alert(LABEL.error);
		}).always(function () {
			toggleBusy($card, false);
		});
	}

	$(document).on('click', '.btn-call', function (e) {
		e.preventDefault();
		if ($(this).hasClass('disabled')) return;
		callLoket($(this).data('id'));
	});

	$(document).on('click', '.btn-recall', function (e) {
		e.preventDefault();
		if ($(this).hasClass('disabled')) return;
		recallLoket($(this).data('id'));
	});

	/* ---------- Socket status indicator ---------- */
	function setStatus(kind) {
		var $s = $('#socket-status');
		$s.removeClass('label-default label-success label-warning label-danger');

		if (kind === 'connected') {
			$s.addClass('label-success').html('<i class="fa fa-plug"></i> ' + LABEL.connected);
		} else if (kind === 'reconnecting') {
			$s.addClass('label-warning').html('<i class="fa fa-refresh fa-spin"></i> ' + LABEL.reconnecting);
		} else {
			$s.addClass('label-danger').html('<i class="fa fa-plug"></i> ' + LABEL.disconnected);
		}
	}

	$.getScript(sockUrl + '/socket.io/socket.io.js').done(function () {
		var socket = io.connect(sockUrl);
		socket.on('connect',      function () { setStatus('connected'); });
		socket.on('disconnect',   function () { setStatus('disconnected'); });
		socket.on('reconnecting', function () { setStatus('reconnecting'); });
	}).fail(function () {
		setStatus('disconnected');
	});
});
</script>
