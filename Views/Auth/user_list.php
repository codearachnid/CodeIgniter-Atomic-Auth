<h1><?php echo lang('Auth.user_list_heading');?></h1>
<p><?php echo lang('Auth.user_list_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

<a href="/auth/list/all">All</a> | <a href="/auth/list/active">Active</a> | <a href="/auth/list/inactive">inactive</a> |

<?php foreach( $users as $user ) : ?>
  <?php d($user); ?>
  <label><?php echo $user->email; ?></label>
  <a href="/auth/edit/<?php echo $user->guid; ?>">Edit User</a>
<?php endforeach; ?>


<p>
  <?php if( userCan('create_user') ) : ?><a href="/auth/create">Create User</a> | <?php endif; ?>
  <a href="/auth/user">View Profile</a> |
  <a href="/auth/logout">Logout</a>
</p>
