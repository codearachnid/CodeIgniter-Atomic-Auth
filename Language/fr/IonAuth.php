<?php

/**
* Name:  Atomic Auth Lang - French
*
* Author:     Stan
* 		      tfspir@gmail.com
*
* Updated by: Gwenaël Gallon
* 			  github@dev-ggallon
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.23.2010
* Updated:  06.16.2017
*
* Description:  French language file for Atomic Auth messages and errors
*
* @package Codeigniter-Ion-Auth
*/

return [
	// Account Creation
	'account_creation_successful'            => 'Compte créé avec succès',
	'account_creation_unsuccessful'          => 'Impossible de créer le compte',
	'account_creation_duplicate_email'       => 'Email déjà utilisé ou invalide',
	'account_creation_duplicate_identity'    => 'Nom d\'utilisateur déjà utilisé ou invalide',
	'account_creation_missing_defaultRole' => 'Le rolee par défaut n\'est pas configuré',
	'account_creation_invalid_defaultRole' => 'Le nom du rolee par défaut n\'est pas valide',

	// Password
	'password_change_successful'   => 'Le mot de passe a été changé avec succès',
	'password_change_unsuccessful' => 'Impossible de changer le mot de passe',
	'forgot_password_successful'   => 'Mail de réinitialisation du mot de passe envoyé',
	'forgot_password_unsuccessful' => 'Impossible de réinitialiser le mot de passe',

	// Activation
	'activate_successful'           => 'Compte activé',
	'activate_unsuccessful'         => 'Impossible d\'activer le compte',
	'deactivate_successful'         => 'Compte désactivé',
	'deactivate_unsuccessful'       => 'Impossible de désactiver le compte',
	'activation_email_successful'   => 'Email d\'activation envoyé avec succès',
	'activation_email_unsuccessful' => 'Impossible d\'envoyer l\'email d\'activation',

	// Login / Logout
	'login_successful'              => 'Connecté avec succès',
	'login_unsuccessful'            => 'Erreur lors de la connexion',
	'login_unsuccessful_not_active' => 'Ce compte est inactif',
	'login_timeout'                 => 'Compte temporairement bloqué suite à de trop nombreuses tentatives.  Veuillez réessayer plus tard.',
	'logout_successful'             => 'Déconnexion effectuée avec succès',

	// Account Changes
	'update_successful'   => 'Compte utilisateur mis à jour avec succès',
	'update_unsuccessful' => 'Impossible de mettre à jour le compte utilisateur',
	'delete_successful'   => 'Utilisateur supprimé',
	'delete_unsuccessful' => 'Impossible de supprimer l\'utilisateur',
	'deactivate_current_user_unsuccessful'=> 'Vous ne pouvez pas vous désactiver vous-même.',

	// Roles
	'role_creation_successful' => 'Groupe créé avec succès',
	'role_already_exists'      => 'Nom du rolee déjà pris',
	'role_update_successful'   => 'Informations sur le rolee mis à jour',
	'role_delete_successful'   => 'Groupe supprimé',
	'role_delete_unsuccessful' => 'Impossible de supprimer le rolee',
	'role_delete_notallowed'    => 'Le rolee Administrateur ne peut pas être supprimé',
	'role_name_required'       => 'Le nom du rolee est un champ obligatoire',
	'role_name_admin_not_alter' => 'Le nom du rolee Admin ne peut pas être modifié',

	// Activation Email
	'emailActivation_subject'  => 'Activation du compte',
	'emailActivate_heading'    => 'Activer le compte pour %s',
	'emailActivate_subheading' => 'Veuillez cliquer sur le lien pour %s',
	'emailActivate_link'       => 'Activez votre compte',

	// Forgot Password Email
	'email_forgotten_password_subject' => 'Mot de Passe Oublié - Vérification',
	'emailForgotPassword_heading'    => 'Réinitialiser le mot de passe pour %s',
	'emailForgotPassword_subheading' => 'Veuillez cliquer sur ce lien pour %s.',
	'emailForgotPassword_link'       => 'Réinitialiser votre mot de passe',
];
