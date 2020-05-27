<?php
/**
* Name:  Atomic Auth Lang - Norwegian
*
* Author: Tomas E. Sandven
* 		  tomas191191@gmail.com
*         @codemonkey1991
*
* Author: Yngve Høiseth
* 		  yngve.hoiseth@gmail.com
*         @yhoiseth
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  01.01.2012
* Last-Edit: 16.11.2014
*
* Description:  Norwegian language file for Atomic Auth messages and errors
*
*/

return [
	// Account Creation
	'account_creation_successful'			=> 'Konto opprettet',
	'account_creation_unsuccessful'			=> 'Klarte ikke å opprette konto',
	'account_creation_duplicate_email'		=> 'E-posten er allerede i bruk eller ugyldig',
	'account_creation_duplicate_identity'	=> 'Brukernavnet er allerede i bruk eller ugyldig',
	'account_creation_missing_defaultRole' => 'Standardgruppe er ikke valgt',
	'account_creation_invalid_defaultRole' => 'Ugyldig gruppenavn',


	// Password
	'password_change_successful'	  => 'Passordet har blitt endret',
	'password_change_unsuccessful' => 'Klarte ikke å endre passord',
	'forgot_password_successful'	  => 'E-post for tilbakestilling av passord har blitt sendt',
	'forgot_password_unsuccessful' => 'Klarte ikke å tilbakestille passord',

	// Activation
	'activate_successful'		   => 'Kontoen har blitt aktivert',
	'activate_unsuccessful'		   => 'Klarte ikke å aktivere konto',
	'deactivate_successful'		   => 'Kontoen har blitt deaktivert',
	'deactivate_unsuccessful'	   => 'Klarte ikke å deaktivere konto',
	'activation_email_successful'   => 'E-post for aktivering av konto har blitt sendt',
	'activation_email_unsuccessful' => 'Klarte ikke å sende e-post for aktivering av konto',
	'deactivate_current_user_unsuccessful'=> 'You cannot De-Activate your self.',

	// Login / Logout
	'login_successful'			   => 'Logget inn',
	'login_unsuccessful'			   => 'Feil e-post/brukernavn eller passord',
	'login_unsuccessful_not_active' => 'Kontoen er inaktiv',
	'login_timeout'				   => 'Midlertidig sperret. Logg inn senere.',
	'logout_successful'			   => 'Logget ut',

	// Account Changes
	'update_successful' 	 => 'Kontoinformasjon oppdatert',
	'update_unsuccessful' => 'Klarte ikke å oppdatere kontoinformasjon',
	'delete_successful'	 => 'Konto slettet',
	'delete_unsuccessful' => 'Klarte ikke å slette konto',

	// Roles
	'role_creation_successful' => 'Gruppe opprettet',
	'role_already_exists'	   => 'Gruppenavnet finnes allerede',
	'role_update_successful'   => 'Gruppeinformasjon oppdatert',
	'role_delete_successful'   => 'Gruppe slettet',
	'role_delete_unsuccessful' => 'Klarte ikke å slette gruppe',
	'role_delete_notallowed'    => 'Can\'t delete the administrators\' role',
	'role_name_required'	   => 'Gruppenavn må fylles inn',
	'role_name_admin_not_alter' => 'Admingruppenavnet kan ikke endres',

	// Activation Email
	'emailActivation_subject'  => 'Aktivering av konto',
	'emailActivate_heading'	   => 'Aktivér konto for %s',
	'emailActivate_subheading' => 'Klikk på denne linken for å %s.',
	'emailActivate_link'	   => 'Aktivér konto',

	// Forgot Password Email
	'email_forgotten_password_subject' => 'Glemt passord: bekreftelse',
	'emailForgotPassword_heading'    => 'Tilbakestill passord for %s',
	'emailForgotPassword_subheading' => 'Klikk på denne linken for å %s.',
	'emailForgotPassword_link'       => 'Tilbakestill passord',
];
