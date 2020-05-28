<h1><?php echo lang('Auth.user_list_heading');?></h1>
<p><?php echo lang('Auth.user_list_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

Filter by status: <a href="/auth/list/all">All</a> | <a href="/auth/list/active">Active</a> | <a href="/auth/list/inactive">inactive</a>
<?php

$usersColCount = 2;

if ($filterUserStatus == 'all') {
    $usersColCount++;
}

?>
<table>
  <tr>
    <th>Action</th>
    <th><?php echo humanize($identity); ?></th>
    <?php if ($filterUserStatus == 'all') : ?><th>Status</th><?php endif; ?>
  </tr>
<?php if ($users) : foreach ($users as $user) : ?>
  <tr>
    <td><a href="/auth/edit/<?php echo $user->guid; ?>">Edit User</a></td>
    <td><?php echo getUserIdentity($user); ?></td>
    <?php if ($filterUserStatus == 'all') : ?><td><?php echo humanize($user->status); ?></td><?php endif; ?>
  </tr>
<?php endforeach; else : ?>
  <tr>
    <td colspan="<?php echo $usersColCount; ?>">No users to show</td>
  </tr>
<?php endif; ?>
</table>

<p>
  <?php if (userCan('create_user')) : ?><a href="/auth/create">Create User</a> | <?php endif; ?>
  <a href="/auth/user">View Profile</a> |
  <a href="/auth/logout">Logout</a>
</p>
