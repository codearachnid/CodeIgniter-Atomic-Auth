<h1><?php echo lang('Auth.create_user_heading');?></h1>
<p><?php echo lang('Auth.create_user_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

<?php echo form_open('auth/create');?>


      <?php
      if ($identity_column !== 'email') {
          echo '<p>';
          echo form_label(lang('Auth.create_user_identity_label'), 'identity');
          echo '<br />';
          echo form_error('identity');
          echo form_input($identity);
          echo '</p>';
      }
      ?>

      <p>
            <?php echo form_label(lang('Auth.create_user_email_label'), 'email');?> <br />
            <?php echo form_input($email);?>
      </p>


      <p>
            <?php echo form_label(lang('Auth.create_user_password_label'), 'password');?> <br />
            <?php echo form_input($password);?>
      </p>

      <p>
            <?php echo form_label(lang('Auth.create_user_password_confirm_label'), 'password_confirm');?> <br />
            <?php echo form_input($password_confirm);?>
      </p>


      <p><?php echo form_submit('submit', lang('Auth.create_user_submit_btn'));?></p>

<?php echo form_close();?>
