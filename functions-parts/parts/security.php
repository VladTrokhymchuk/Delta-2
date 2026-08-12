<?php
# 8. Безопасность
	# 8.1 Полное Удаление версии WP
	add_filter('the_generator', '__return_empty_string'); // из фидов и URL

	# 8.2 Удаление параметра ver в добавляемых скриптах и стилях
	function rem_wp_ver_css_js( $src ) {
		if ( strpos( $src, 'ver=' ) )
			$src = remove_query_arg( 'ver', $src );
		return $src;
	}
	add_filter( 'style_loader_src', 'rem_wp_ver_css_js', 9999 );
	add_filter( 'script_loader_src', 'rem_wp_ver_css_js', 9999 );

	# 8.3 Авто удаление файлов license.txt и readme.html
	if( is_admin() && ! defined('DOING_AJAX') ){
		add_action( 'init', 'remove_license_txt_readme_html' );
		function remove_license_txt_readme_html(){
			$license_file = ABSPATH .'/license.txt';
			$readme_file  = ABSPATH .'/readme.html';

			if( file_exists($license_file) && current_user_can('manage_options') ){

				$deleted = unlink($license_file) && unlink($readme_file);

				if( ! $deleted  )
					$GLOBALS['readmedel'] = 'Не удалось удалить файлы: license.txt и readme.html из папки `'. ABSPATH .'`. Удалите их вручную!';
				else
					$GLOBALS['readmedel'] = 'Файлы: license.txt и readme.html удалены из из папки `'. ABSPATH .'`.';

				add_action( 'admin_notices', function(){
					echo '<div class="error is-dismissible"><p>'. $GLOBALS['readmedel'] .'</p></div>';
				} );
			}
		}
	}

	# 8.4 Зашифровать логин и пароль во время передачи их серверу
	define('FORCE_SSL_LOGIN', true);

	# 8.5 SSL в админской части сайта
	// define('FORCE_SSL_ADMIN', true);

	# 8.6 Отключить вывод ошибок на странице авторизации
	add_filter('login_errors', 'login_obscure_func');
	function login_obscure_func(){
		return 'Помилка: Ви ввели неправильний логін або пароль.';
	}

	# 8.7 Отключить возможность редактировать файлы в админке для тем, плагинов
	define('DISALLOW_FILE_EDIT', true);