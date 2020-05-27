<h1><?php echo lang('Auth.index_heading');?></h1>
<p><?php echo lang('Auth.index_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

<?php if ( $atomicAuth->userCan('list_user') ) : ?>
<table cellpadding=0 cellspacing=10>
	<tr>
		<th><?php echo lang('Auth.index_email_th');?></th>
		<th><?php echo lang('Auth.index_roles_th');?></th>
		<th><?php echo lang('Auth.index_status_th');?></th>
		<th><?php echo lang('Auth.index_action_th');?></th>
	</tr>
	<?php foreach ($users as $user):?>
		<tr>
            <td><?php echo htmlspecialchars($user->email,ENT_QUOTES,'UTF-8');?></td>
			<td>
				<?php foreach ($user->roles as $role):?>
					<?php echo anchor('auth/edit_role/' . $role->id, htmlspecialchars($role->name, ENT_QUOTES, 'UTF-8')); ?><br />
				<?php endforeach ?>
			</td>
			<td><?php /*echo ($user->active) ? anchor('auth/deactivate/' . $user->id, lang('Auth.index_active_link')) : anchor("auth/activate/". $user->id, lang('Auth.index_inactive_link')); */?></td>
			<td><?php echo anchor('auth/edit/' . $user->guid, lang('Auth.index_edit_link')) ;?></td>
		</tr>
	<?php endforeach;?>
</table>
<?php endif; ?>

<?php if ( $atomicAuth->userCan('create_user') ) : ?>
	<p><?php echo anchor('auth/create', lang('Auth.index_create_user_link'))?> | <?php echo anchor('auth/edit', lang('Auth.index_create_user_link'))?> | <?php echo anchor('auth/create_role', lang('Auth.index_create_role_link'))?></p>
<?php else : ?>
	<p><?php echo anchor('auth/login', lang('Auth.index_login_link'))?> | <?php echo anchor('auth/create', lang('Auth.index_create_user_link'))?></p>
<?php endif; ?>
