<?php if( !empty( $messages ) ) : ?>
<div id="infoMessage">
		<?php foreach ($messages as $msg) : ?>
			<aside class="alert alert-info" role="alert"><?= esc($msg['message']) ?></aside>
		<?php endforeach ?>
</div>
<?php endif; ?>
