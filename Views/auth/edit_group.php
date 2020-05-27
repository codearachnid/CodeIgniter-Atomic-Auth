<h1><?php echo lang('Auth.edit_role_heading');?></h1>
<p><?php echo lang('Auth.edit_role_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

<?php echo form_open(current_url());?>

      <p>
            <?php echo form_label(lang('Auth.edit_role_name_label'), 'role_name');?> <br />
            <?php echo form_input($role_name);?>
      </p>

      <p>
            <?php echo form_label(lang('Auth.edit_role_desc_label'), 'description');?> <br />
            <?php echo form_input($role_description);?>
      </p>

      <p><?php echo form_submit('submit', lang('Auth.edit_role_submit_btn'));?></p>

<?php echo form_close();?>