<?php if( !empty( $messages ) ) : dd($messages); ?>
<div id="infoMessage">
		<?php foreach ($messages as $message) : ?>
			<aside class="alert alert-info" role="alert"><?= esc($message) ?></aside>
		<?php endforeach ?>
</div>
<?php endif; ?>
