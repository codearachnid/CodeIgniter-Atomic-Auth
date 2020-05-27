<?php

/**
 * Name:  Atomic Auth Lang - English
 *
 * Author: Ben Edmunds
 *         ben.edmunds@gmail.com
 *         @benedmunds
 *
 * Location: http://github.com/benedmunds/ion_auth/
 *
 * Created:  03.14.2010
 *
 * Description:  English language file for Atomic Auth messages and errors
 *
 * @package Codeigniter-Ion-Auth
 */

return [
	// Account Creation
	'account_creation_successful'            => 'Account Successfully Created',
	'account_creation_unsuccessful'          => 'Unable to Create Account',
	'account_creation_duplicate_email'       => 'Email Already Used or Invalid',
	'account_creation_duplicate_identity'    => 'Identity Already Used or Invalid',
	'account_creation_missing_defaultRole' => 'Default role is not set',
	'account_creation_invalid_defaultRole' => 'Invalid default role name set',

	// Password
	'password_change_successful'          => 'Password Successfully Changed',
	'password_change_unsuccessful'        => 'Unable to Change Password',
	'forgot_password_successful'          => 'Password Reset Email Sent',
	'forgot_password_unsuccessful'        => 'Unable to email the Reset Password link',

	// Activation
	'activate_successful'                 => 'Account Activated',
	'activate_unsuccessful'               => 'Unable to Activate Account',
	'deactivate_successful'               => 'Account De-Activated',
	'deactivate_unsuccessful'             => 'Unable to De-Activate Account',
	'activation_email_successful'         => 'Activation Email Sent. Please check your inbox or spam',
	'activation_email_unsuccessful'       => 'Unable to Send Activation Email',
	'deactivate_current_user_unsuccessful'=> 'You cannot De-Activate your self.',

	// Login / Logout
	'login_successful'                    => 'Logged In Successfully',
	'login_unsuccessful'                  => 'Incorrect Login',
	'login_unsuccessful_not_active'       => 'Account is inactive',
	'login_timeout'                       => 'Temporarily Locked Out.  Try again later.',
	'logout_successful'                   => 'Logged Out Successfully',

	// Account Changes
	'update_successful'                   => 'Account Information Successfully Updated',
	'update_unsuccessful'                 => 'Unable to Update Account Information',
	'delete_successful'                   => 'User Deleted',
	'delete_unsuccessful'                 => 'Unable to Delete User',

	// Roles
	'role_creation_successful'           => 'Group created Successfully',
	'role_already_exists'                => 'Group name already taken',
	'role_update_successful'             => 'Group details updated',
	'role_delete_successful'             => 'Group deleted',
	'role_delete_unsuccessful'           => 'Unable to delete role',
	'role_delete_notallowed'             => 'Can\'t delete the administrators\' role',
	'role_name_required'                 => 'Group name is a required field',
	'role_name_admin_not_alter'          => 'Admin role name can not be changed',

	// Activation Email
	'emailActivation_subject'            => 'Account Activation',
	'emailActivate_heading'              => 'Activate account for %s',
	'emailActivate_subheading'           => 'Please click this link to %s.',
	'emailActivate_link'                 => 'Activate Your Account',

	// Forgot Password Email
	'email_forgotten_password_subject'    => 'Forgotten Password Verification',
	'emailForgotPassword_heading'       => 'Reset Password for %s',
	'emailForgotPassword_subheading'    => 'Please click this link to %s.',
	'emailForgotPassword_link'          => 'Reset Your Password',

];
