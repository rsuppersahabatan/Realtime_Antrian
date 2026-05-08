<div class="form-group">
	<?php echo lang('display_id_client', 'id_client', array('class' => 'col-sm-3 control-label')); ?>
	<div class="col-sm-9">
		<select class="form-control" id="id_client" name="id_client">
			<option value="0"><?php echo lang('display_select_client'); ?></option>
			<?php foreach ($clients as $client): ?>
			<option value="<?php echo (int) $client['id']; ?>" <?php echo ((int) $selected_client_id === (int) $client['id']) ? 'selected' : ''; ?>>
				<?php echo html_escape($client['nama_client']); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<p class="help-block"><?php echo lang('display_client_help'); ?></p>
	</div>
</div>

<div class="form-group">
	<?php echo lang('display_color_scheme', 'color_scheme', array('class' => 'col-sm-3 control-label')); ?>
	<div class="col-sm-9">
		<?php $palette = array('#4e73df', '#1e88e5', '#008000', '#808080', '#800080', '#ff0000', '#ff9900'); ?>
		<div class="display-color-palette">
			<?php foreach ($palette as $color): ?>
			<label class="display-color-box" style="background-color: <?php echo $color; ?>;">
				<input type="radio" name="color_scheme" value="<?php echo $color; ?>" <?php echo ($color_scheme === $color) ? 'checked' : ''; ?>>
				<span class="display-color-check"><i class="fa fa-check"></i></span>
			</label>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<div class="form-group">
	<?php echo lang('display_video_link', 'video_link', array('class' => 'col-sm-3 control-label')); ?>
	<div class="col-sm-3">
		<select class="form-control" name="video_source" id="video_source">
			<option value="youtube" <?php echo ($video_source === 'youtube') ? 'selected' : ''; ?>>Youtube</option>
			<option value="local" <?php echo ($video_source === 'local') ? 'selected' : ''; ?>>Local File</option>
		</select>
	</div>
	<div class="col-sm-6">
		<input type="text" class="form-control" id="video_link" name="video_link" value="<?php echo html_escape($video_link); ?>" placeholder="<?php echo lang('display_video_link_placeholder'); ?>">
		<p class="help-block"><?php echo lang('display_video_help'); ?></p>
	</div>
</div>

<div class="form-group">
	<?php echo lang('display_footer_text', 'footer_text', array('class' => 'col-sm-3 control-label')); ?>
	<div class="col-sm-9">
		<textarea class="form-control" id="footer_text" name="footer_text" rows="4"><?php echo html_escape($footer_text); ?></textarea>
	</div>
</div>

<div class="form-group">
	<?php echo lang('display_footer_mode', 'footer_mode', array('class' => 'col-sm-3 control-label')); ?>
	<div class="col-sm-3">
		<select class="form-control" id="footer_mode" name="footer_mode">
			<option value="statis" <?php echo ($footer_mode === 'statis') ? 'selected' : ''; ?>><?php echo lang('display_footer_mode_statis'); ?></option>
			<option value="running" <?php echo ($footer_mode === 'running') ? 'selected' : ''; ?>><?php echo lang('display_footer_mode_running'); ?></option>
		</select>
	</div>
</div>

<div class="form-group">
	<?php echo lang('display_font_family', 'font_family', array('class' => 'col-sm-3 control-label')); ?>
	<div class="col-sm-3">
		<select class="form-control" id="font_family" name="font_family">
			<?php $fonts = array('Poppins', 'Inter', 'Arial', 'Roboto', 'Open Sans', 'Tahoma'); ?>
			<?php foreach ($fonts as $font): ?>
			<option value="<?php echo $font; ?>" <?php echo ($font_family === $font) ? 'selected' : ''; ?>><?php echo $font; ?></option>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<div class="form-group">
	<?php echo lang('display_font_size', 'font_size', array('class' => 'col-sm-3 control-label')); ?>
	<div class="col-sm-6">
		<input type="range" class="form-control" id="font_size" name="font_size" min="60" max="160" value="<?php echo (int) $font_size; ?>">
		<p class="help-block"><span id="font_size_value"><?php echo (int) $font_size; ?></span>px</p>
	</div>
</div>

<style>
	.display-color-palette {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}
	.display-color-box {
		width: 40px;
		height: 40px;
		border-radius: 2px;
		border: 1px solid rgba(0, 0, 0, 0.2);
		margin-bottom: 0;
		position: relative;
		cursor: pointer;
	}
	.display-color-box input[type="radio"] {
		display: none;
	}
	.display-color-check {
		display: none;
		position: absolute;
		top: 9px;
		left: 12px;
		color: #fff;
		font-size: 16px;
		text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
	}
	.display-color-box input[type="radio"]:checked + .display-color-check {
		display: inline-block;
	}
</style>

<script>
	(function() {
		var fontSize = document.getElementById('font_size');
		var output = document.getElementById('font_size_value');
		if (fontSize && output) {
			fontSize.addEventListener('input', function() {
				output.textContent = fontSize.value;
			});
		}
	})();
</script>
