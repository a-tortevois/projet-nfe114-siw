<?php
function test_special_char() {
	$chaine = "&~#{}()[]|_\^@%*+-/=$<>!?,.;:"; // °€£§
	foreach( str_split($chaine) as $value )
	{
		echo htmlspecialchars($value, ENT_SUBSTITUTE) . ' : ';
		echo preg_match( '`[^ \w]+|[_]+`', $value ) ? 'true' : 'false';
		echo '<br />';
	}
}

test_special_char();
?>