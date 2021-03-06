<?= $this->extend('AtomicAuth\Views\layout') ?>
<?= $this->section('app') ?>
  <h1><?php echo lang('Auth.login_heading');?></h1>
  <p><?php echo lang('Auth.login_subheading');?></p>

  <?php echo form_open('auth/login');?>

    <p>
      <?php echo form_label(lang('Auth.login_identity_label'), 'identity');?>
      <?php echo form_input($identity);?>
    </p>

    <p>
      <?php echo form_label(lang('Auth.login_password_label'), 'password');?>
      <?php echo form_input($password);?>
    </p>

    <p>
      <?php echo form_label(lang('Auth.login_remember_label'), 'remember');?>
      <?php echo form_checkbox('remember', '1', false, 'id="remember"');?>
    </p>


    <p><?php echo form_submit('submit', lang('Auth.login_submit_btn'));?></p>

  <?php echo form_close();?>

  <?php if( userCan('create_user') ) : ?>
  <p><a href="create"><?php echo lang('Auth.login_user_create');?></a></p>
  <?php endif; ?>
  <p><a href="forgot"><?php echo lang('Auth.login_forgot_password');?></a></p>
<?= $this->endSection() ?>
