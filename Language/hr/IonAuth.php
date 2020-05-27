<?php
/**
* Name:  Atomic Auth Lang - Croatian
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
*
* Translation: primjeri
*		info@primjeri.com
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.14.2010
*
* Description:  Croatian language file for Atomic Auth messages and errors
*
*/

return [
	// Account Creation
	'account_creation_successful' 	  	 => 'Račun je uspješno kreiran',
	'account_creation_unsuccessful' 	 	 => 'Račun nije kreiran',
	'account_creation_duplicate_email' 	 => 'Email je već iskorišten ili krivi',
	'account_creation_duplicate_identity' => 'Korisničko ime je već iskorišteno ili krivo',

	// TODO Please Translate
	'account_creation_missing_defaultRole' => 'Default role is not set',
	'account_creation_invalid_defaultRole' => 'Invalid default role name set',

	// Password
	'password_change_successful' 	 	 => 'Lozinka uspješno promjenjena',
	'password_change_unsuccessful' 	  	 => 'Lozinka nije promjenjena',
	'forgot_password_successful' 	 	 => 'Email za poništenje lozinke je poslan',
	'forgot_password_unsuccessful' 	 	 => 'lozinka nije poništena',

	// Activation
	'activate_successful' 		  	     => 'Račun je aktiviran',
	'activate_unsuccessful' 		 	     => 'Aktiviranje računa nije uspjelo',
	'deactivate_successful' 		  	     => 'Račun je deaktiviran',
	'deactivate_unsuccessful' 	  	     => 'De-aktivacija računa noje uspjela',
	'activation_email_successful' 	  	 => 'Email za aktivaciju je poslan',
	'activation_email_unsuccessful'   	 => 'Slanje mail za aktivaciju nije uspjelo',
	'deactivate_current_user_unsuccessful'=> 'You cannot De-Activate your self.',

	// Login / Logout
	'login_successful' 		  	         => 'Uspješno prijavljeni',
	'login_unsuccessful' 		  	     => 'Prijava nije uspjela',
	'login_unsuccessful_not_active' 		 => 'Račun nije aktivan',
	'login_timeout'                       => 'Temporarily Locked Out. Try again later.',
	'logout_successful' 		 	         => 'Uspješno ste odjavljeni',

	// Account Changes
	'update_successful' 		 	         => 'Podaci o računu uspješno su a≈æurirani',
	'update_unsuccessful' 		 	     => 'Podaci o računu nisu ažurirani',
	'delete_successful' 		 	         => 'Korisnik je obrisan',
	'delete_unsuccessful' 		 	     => 'Brisanje korisnika nije uspjelo',

	// Roles
	'role_creation_successful'  => 'Group created Successfully',
	'role_already_exists'       => 'Group name already taken',
	'role_update_successful'    => 'Group details updated',
	'role_delete_successful'    => 'Group deleted',
	'role_delete_unsuccessful' 	=> 'Unable to delete role',
	'role_delete_notallowed'    => 'Can\'t delete the administrators\' role',
	'role_name_required' 		=> 'Group name is a required field',
	'role_name_admin_not_alter' => 'Admin role name can not be changed',

	// Activation Email
	'emailActivation_subject'            => 'Aktivacija računa',
	'emailActivate_heading'    => 'Activate account for %s',
	'emailActivate_subheading' => 'Kliknite sljedeći link %s.',
	'emailActivate_link'       => 'Aktivirajte Vaš račun',
	// Forgot Password Email
	'email_forgotten_password_subject'    => 'Potvrda o zaboravljenoj lozinci',
	'emailForgotPassword_heading'    => 'Reset Password for %s',
	'emailForgotPassword_subheading' => 'Please click this link to %s.',
	'emailForgotPassword_link'       => 'Reset Your Password',
];
