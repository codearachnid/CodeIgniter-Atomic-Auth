<?php if( isLoggedIn() ) : ?>
<div>
  <?php if (userCan('list_user')): ?><a href="/auth/list">List Users</a> | <?php endif; ?>
  <?php if (userCan('list_capability')): ?><a href="/auth/list">List Capabilities</a> | <?php endif; ?>
  <?php if (userCan('list_role')): ?><a href="/auth/list">List Roles</a> | <?php endif; ?>
  <a href="/auth/user">Profile</a> |
  <a href="/auth/logout">Logout</a>
</div>
<?php endif; ?>
