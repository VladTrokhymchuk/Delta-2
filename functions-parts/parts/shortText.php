<?php
function shortText($text, $chars_limit) { // Function name ShortenText
	$chars_text = strlen($text);
	$text = $text." ";
	$text = substr($text,0,$chars_limit);
	$text = substr($text,0,strrpos($text,' '));
	
	if ($chars_text > $chars_limit) { 
		$text = $text."..."; 
	} // Ellipsis
	return $text;
}

function prefix_wcount(){
	ob_start();
	the_content();
	$content = ob_get_clean();
	return sizeof(explode(" ", $content));
}

function custom_excerpt_length( $length ) {
	return 75;
}
add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

// function custom_excerpt($limit) {
// 	return wp_trim_words(get_the_excerpt(), $limit);
// }

function custom_excerpt_more() {
	return ' ...';
}
add_filter('excerpt_more', 'custom_excerpt_more');