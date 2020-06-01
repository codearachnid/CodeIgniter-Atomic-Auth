<?= $this->extend('AtomicAuth\Views\layout') ?>
<?php

$usersColCount = 2;
// TODO automatically pass filter options in
$filters = ['all','active','inactive'];

if ($filterUserStatus == 'all') {
    $usersColCount++;
}

?>
<?= $this->section('app') ?>
  <h1><?php echo lang('Auth.user_list_heading');?></h1>
  <p><?php echo lang('Auth.user_list_subheading');?></p>


<div class="filters list">
  <?php // TODO translate label value ?>
  <label>Filter by status:</label>
  <?php foreach( $filters as $filter ) : ?>
    <a href="/auth/list/<?= $filter ?>" class="filter"><?= humanize($filter) ?></a>
  <?php endforeach; ?>
</div>

  <table class="data list">
    <tr>
      <th>Action</th>
      <th><?php echo humanize($identity); ?></th>
      <?php if ($filterUserStatus == 'all') : ?><th>Status</th><?php endif; ?>
    </tr>
  <?php if ($users) : foreach ($users as $user) : ?>
    <tr>
      <td><a href="/auth/edit/<?php echo $user->guid; ?>" class="col action edit">Edit</a></td>
      <td><?php echo getUserIdentity($user); ?></td>
      <?php if ($filterUserStatus == 'all') : ?><td><?php echo humanize($user->status); ?></td><?php endif; ?>
    </tr>
  <?php endforeach; else : ?>
    <tr>
      <td colspan="<?php echo $usersColCount; ?>">No users to show</td>
    </tr>
  <?php endif; ?>
  </table>
<?= $this->endSection() ?>
