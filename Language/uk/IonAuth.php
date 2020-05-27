<?php
/**
* Name:  Atomic Auth Lang - Ukraine (UTF-8)
*
* Author: Ben Edmunds
* 		  ben.edmunds@gmail.com
*         @benedmunds
* Translation:  Petrosyan R.
*             for@petrosyan.rv.ua
*
* Location: http://github.com/benedmunds/ion_auth/
*
* Created:  03.27.2010
*
* Description:  Ukraine language file for Atomic Auth messages and errors
*
*/

return [
	// Account Creation
	'account_creation_successful'		=> 'Обліковий запис успішно створено',
	'account_creation_unsuccessful'		=> 'Неможливо створити обліковий запис',
	'account_creation_duplicate_email'	=> 'Електронна пошта використовується або некоректна',
	'account_creation_duplicate_identity'    => 'Ім`я користувача існує або некоректне',

	// TODO Please Translate
	'account_creation_missing_defaultRole' => 'Група за умовчанням не встановлена',
	'account_creation_invalid_defaultRole' => 'Група за умовчанням задана некоректно',

	// Password
	'password_change_successful'		=> 'Пароль успішно змінено',
	'password_change_unsuccessful'		=> 'Пароль неможливо змінити',
	'forgot_password_successful'		=> 'Пароль скинутий. На електронну пошту відправлено повідомлення',
	'forgot_password_unsuccessful'		=> 'Неможливе скидання пароля',

	// Activation
	'activate_successful'                  => 'Обліковий запис активовано',
	'activate_unsuccessful'                => 'Не вдалося активувати обліковий запис',
	'deactivate_successful'                => 'Обліковий запис деактивовано',
	'deactivate_unsuccessful'              => 'Неможливо деактивувати обліковий запис',
	'activation_email_successful'          => 'Повідомлення про активацію відправлено',
	'activation_email_unsuccessful'        => 'Повідомлення про активацію неможливо відправити',
	'deactivate_current_user_unsuccessful' => 'Ви не можете самі деактивувати свій обліковий запис',

	// Login / Logout
	'login_successful'		=> 'Авторизація пройшла успішно',
	'login_unsuccessful'		=> 'Логін невірний',
	'login_unsuccessful_not_active' 	=> 'Обліковий запис не активований',
	'login_timeout'			=> 'В цілях безпеки можливість входу тимчасово заблокована. Спробуйте зайти пізніше.',
	'logout_successful'		=> 'Вихід успішний',

	// Account Changes
	'update_successful'		=> 'Обліковий запис успішно оновлено',
	'update_unsuccessful'		=> 'Неможливо оновити обліковий запис',
	'delete_successful'		=> 'Обліковий запис видалено',
	'delete_unsuccessful'		=> 'Неможливо видалити обліковий запис',

	// Roles
	'role_creation_successful'  => 'Група створена успішно',
	'role_already_exists'       => 'Група з таким ім\'ям вже існує',
	'role_update_successful'    => 'Дані групи оновлені успішно',
	'role_delete_successful'    => 'Група видалена',
	'role_delete_unsuccessful'  => 'Не вдалося видалити групу',
	'role_delete_notallowed'    => 'Не можна видалити групу адміністраторів',
	'role_name_required' 	    => 'Ім\'я групи обов\'язкове до заповнення',
	'role_name_admin_not_alter' => 'Admin role name can not be changed',

	// Activation Email
	'emailActivation_subject'  => 'Активація облікового запису',
	'emailActivate_heading'    => 'Активувати акаунт з ім\'ям  %s',
	'emailActivate_subheading' => 'Натисніть на посилання %s.',
	'emailActivate_link'       => 'Активувати ваш акаунт',
	// Forgot Password Email
	'email_forgotten_password_subject'	=> 'Перевірка забутого пароля',
	'emailForgotPassword_heading'		=> 'Скидання пароля для користувача %s',
	'emailForgotPassword_subheading'	=> 'Натисніть на посилання для %s.',
	'emailForgotPassword_link'		=> 'Відновлення пароля',
];
