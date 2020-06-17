<?= $this->extend('AtomicAuth\Views\layout') ?>
<?= $this->section('app') ?>
  <h1><?php echo lang('Auth.edit_user_heading');?></h1>
  <p><?php echo lang('Auth.edit_user_subheading');?></p>

  <?php echo form_open(uri_string());?>
        <p><?php echo form_label(lang('Auth.edit_user_guid_label'), 'password');?> <br />
           <?php echo $user->guid; ?></p>

        <p><?php echo form_label(lang('Auth.edit_user_identiy_label'), 'password');?> <br />
            <?php echo $user->email; ?></p>

        <p>
              <?php echo form_label(lang('Auth.edit_user_password_label'), 'password');?> <br />
              <?php echo form_input($password);?>
        </p>

        <p>
              <?php echo form_label(lang('Auth.edit_user_password_confirm_label'), 'password_confirm');?><br />
              <?php echo form_input($password_confirm);?>
        </p>

        <?php if ($atomicAuth->userCan('edit_user_status')): ?>

            <p>
              <?php echo form_label(lang('Auth.edit_user_status_heading'), 'status');?> <br />
              <?php echo form_dropdown('status', ['pending'=>'Pending','active'=>'Active','inactive'=>'Inactive','suspended'=>'Suspended','banned'=>'Banned',], $user->status); ?>
            </p>

        <?php else : ?>
          <p><?php echo form_label(lang('Auth.edit_user_status_label'), 'status');?> <br />
              <?php echo $user->status; ?></p>
        <?php endif; ?>

        <?php if ($atomicAuth->userCan('promote_user')): ?>

            <h3><?php echo lang('Auth.edit_user_roles_heading');?></h3>
            <?php foreach ($roles as $role):?>
                <label class="checkbox">
                <?php echo form_checkbox('roles[]', $role->id, in_array($role->id, $userInRoles)); ?>
                <?php echo htmlspecialchars($role->description, ENT_QUOTES, 'UTF-8');?>
                </label>
            <?php endforeach?>

        <?php else : ?>
          <h3><?php echo lang('Auth.edit_user_in_roles_heading');?></h3>
          <?php foreach ($user->roles as $role): ?>
              <label class="checkbox">
              <?php echo htmlspecialchars($role->description, ENT_QUOTES, 'UTF-8');?>
              </label>
          <?php endforeach?>
        <?php endif; ?>

        <?php if ($atomicAuth->userCan('list_capability')): ?>
          <h3><?php echo lang('Auth.edit_user_capabilities_heading');?></h3>
          <?php foreach( $capabilities as $capability ) : ?>
            <label class="checkbox">
            <?php echo form_checkbox('capabilities[]', $capability->id, in_array($capability->id, $userInCapabilities)); ?>
            <?php echo htmlspecialchars($capability->description, ENT_QUOTES, 'UTF-8');?>
            </label>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php echo form_hidden('attomic_auth_user_id', $user->id);?>

        <p><?php echo form_submit('submit', lang('Auth.edit_user_submit_btn'));?></p>

  <?php echo form_close();?>
<?= $this->endSection() ?>
