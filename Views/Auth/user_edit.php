<h1><?php echo lang('Auth.edit_user_heading');?></h1>
<p><?php echo lang('Auth.edit_user_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

<?php echo form_open(uri_string());?>
      <p>GUID:</p>
      <p>Username:</p>

      <p>
            <?php echo form_label(lang('Auth.edit_user_password_label'), 'password');?> <br />
            <?php echo form_input($password);?>
      </p>

      <p>
            <?php echo form_label(lang('Auth.edit_user_password_confirm_label'), 'password_confirm');?><br />
            <?php echo form_input($password_confirm);?>
      </p>

      <?php if ($atomicAuth->isAdmin()): ?>

          <h3><?php echo lang('Auth.edit_user_groups_heading');?></h3>
          <?php foreach ($groups as $group):?>
              <label class="checkbox">
              <?php echo form_checkbox('groups[]', $group->id, in_array($group->id, $userInGroups) ); ?>
              <?php echo htmlspecialchars($group->description, ENT_QUOTES, 'UTF-8');?>
              </label>
          <?php endforeach?>

      <?php else : ?>
        <h3><?php echo lang('Auth.edit_user_in_groups_heading');?></h3>
        <?php foreach ($user->groups as $group): ?>
            <label class="checkbox">
            <?php echo htmlspecialchars($group->description, ENT_QUOTES, 'UTF-8');?>
            </label>
        <?php endforeach?>
      <?php endif; ?>

      <?php echo form_hidden('id', $user->id);?>

      <p><?php echo form_submit('submit', lang('Auth.edit_user_submit_btn'));?></p>

<?php echo form_close();?>
