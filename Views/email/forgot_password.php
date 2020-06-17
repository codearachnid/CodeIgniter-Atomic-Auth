<html>
<body>
	<h1><?=sprintf(lang('AtomicAuth.emailForgotPassword_heading'), $identity)?></h1>
	<p>
		<?=sprintf(lang('AtomicAuth.emailForgotPassword_subheading'), anchor('auth/reset/' . $reset_hash, lang('AtomicAuth.emailForgotPassword_link')))?>
	</p>
</body>
</html>
