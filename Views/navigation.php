<?php if( isLoggedIn() ) : ?>
<div>
  <?php if (userCan('list_user')): ?><a href="/auth/list">List Users</a> | <?php endif; ?>
  <a href="/auth/user">Profile</a> |
  <a href="/auth/logout">Logout</a>
</div>
<?php endif; ?>
