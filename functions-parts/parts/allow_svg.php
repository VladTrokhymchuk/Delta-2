<?php
// Підтримка SVG в media
function allow_svg($mimes){
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'allow_svg');
define('ALLOW_UNFILTERED_UPLOADS', true);