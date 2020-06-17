<html>
<body>
	<h1><?php echo sprintf(lang('AtomicAuth.emailActivate_heading'), $identity);?></h1>
	<p>
		<?php
		echo sprintf(lang('AtomicAuth.emailActivate_subheading'),
						  anchor('auth/activate/' . $id . '/' . $activation, lang('AtomicAuth.emailActivate_link')));
		?>
	</p>
</body>
</html>