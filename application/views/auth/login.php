<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?>
            <div class="login-logo">
                <a href="<?php echo site_url('/'); ?>"><b>Admin</b><?php echo $title_lg; ?></a>
            </div>

            <div class="login-box-body">
                <p class="login-box-msg"><?php echo lang('auth_sign_session'); ?></p>

                <?php if ( ! empty($message)): ?>
                    <div class="callout callout-info">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php echo form_open('auth/login');?>
                    <div class="form-group has-feedback">
                        <?php echo form_input($identity);?>
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <?php echo form_input($password);?>
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="row" style="display:flex;align-items:center;">
                        <div class="col-xs-8">
                            <div class="checkbox icheck" style="margin:0;padding-left:5px;">
                                <label style="display:inline-flex;align-items:center;gap:6px;padding-left:0;">
                                    <?php echo form_checkbox('remember', '1', FALSE, 'id="remember" style="margin:0 6px 0 0;vertical-align:middle;position:relative;top:0;"'); ?><span><?php echo lang('auth_remember_me'); ?></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <?php echo form_submit('submit', lang('auth_login'), array('class' => 'btn btn-primary btn-block btn-flat'));?>
                        </div>
                    </div>
                <?php echo form_close();?>

<?php if ($auth_social_network == TRUE): ?>
                <div class="social-auth-links text-center">
                    <p>- <?php echo lang('auth_or'); ?> -</p>
                    <?php echo anchor('#', '<i class="fa fa-facebook"></i> ' . lang('auth_sign_facebook'), array('class' => 'btn btn-block btn-social btn-facebook btn-flat')); ?>
                    <?php echo anchor('#', '<i class="fa fa-google-plus"></i> ' . lang('auth_sign_google'), array('class' => 'btn btn-block btn-social btn-google btn-flat')); ?>
                </div>
<?php endif; ?>
<?php if ($forgot_password == TRUE): ?>
                <?php echo anchor('#', lang('auth_forgot_password')); ?><br />
<?php endif; ?>
<?php if ($new_membership == TRUE): ?>
                <?php echo anchor('#', lang('auth_new_member')); ?>
<?php endif; ?>
            </div>

            <div class="login-footer text-center" style="margin-top:15px;color:#777;font-size:12px;">
                &copy; 2026-<?php echo date('Y'); ?> <a href="https://antrian.rspersahabatan.co.id" target="_blank">RS Persahabatan</a>
            </div>
