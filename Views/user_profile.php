<?= $this->extend('AtomicAuth\Views\layout') ?>
<?= $this->section('app') ?>
  <h1><?php echo lang('Auth.user_profile_heading');?></h1>
  <p><?php echo lang('Auth.user_profile_subheading');?></p>

  <p><?php echo form_label(lang('Auth.edit_user_guid_label'), 'GUID');?> <br />
     <?php echo $user->guid; ?></p>

  <p><?php echo form_label(lang('Auth.edit_user_identiy_label'), 'Email');?> <br />
     <?php echo $user->email; ?></p>

  <h3><?php echo lang('Auth.edit_user_in_roles_heading');?></h3>

  <div>
  <?php foreach ($user->roles as $role): ?>
    <label class="checkbox">
      <?php echo htmlspecialchars($role->description, ENT_QUOTES, 'UTF-8');?>
    </label>
  <?php endforeach?>
  </div>
  <hr />
  <a href="/auth/edit">Edit</a>
<?= $this->endSection() ?>
