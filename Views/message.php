<?php if( !empty( $messages ) ) : ?>
<div id="infoMessage">
		<?php if(is_array($messages)) : foreach ($messages as $msg) : $message = isset($msg['message']) ? $msg['message'] : $msg; ?>
			<aside class="alert alert-info" role="alert"><?= esc($message) ?></aside>
		<?php endforeach; else : ?>
<?= esc( $messages ) ?>
			<?php endif; ?>
</div>
<?php endif; ?>
