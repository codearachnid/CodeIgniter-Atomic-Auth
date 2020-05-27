<h1><?php echo lang('Auth.edit_user_heading');?></h1>
<p><?php echo lang('Auth.edit_user_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

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

      <?php if ($atomicAuth->userCan('promote_user')): ?>

          <h3><?php echo lang('Auth.edit_user_roles_heading');?></h3>
          <?php d($roles); foreach ($roles as $role):?>
              <label class="checkbox">
              <?php echo form_checkbox('roles[]', $role->id, in_array($role->id, $userInRoles) ); ?>
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

      <?php echo form_hidden('id', $user->id);?>

      <p><?php echo form_submit('submit', lang('Auth.edit_user_submit_btn'));?></p>

<?php echo form_close();?>
