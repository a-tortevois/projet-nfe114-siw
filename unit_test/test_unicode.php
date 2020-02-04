<html
<head>
<meta charset="utf-8">
</head>
<body>
<?php
// https://stackoverflow.com/questions/1805802/php-convert-unicode-codepoint-to-utf-8

// function utf8($num)
// {
    // if($num<=0x7F)       return chr($num);
    // if($num<=0x7FF)      return chr(($num>>6)+192).chr(($num&63)+128);
    // if($num<=0xFFFF)     return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
    // if($num<=0x1FFFFF)   return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
    // return '';
// }

function codepoint_decode($char)
{
	return json_decode(sprintf('"%s"', $char));
}

$unicode_categories = array ( 
						// '\d',
						// '\w', // 
						// '\p{Lu}',
						// '\p{Pc}',
						// '\p{Sm}',						
						// '\p{Ps}',						
						// '\p{Pe}',
						// '\pM',	
						'0-9a-zA-Z_&~#{}()[\]|^@%*+\-\/=$<>!?,.;:\\\\', // &~#{}()[]|\^@%*+-/=$<>!?,.;:
					);

foreach( $unicode_categories as $pattern ) {
	$result = array();
	for( $i = 0 ; $i <= hexdec("FFFF") ; $i++ )
	{
		$unicode_char = substr("000".dechex($i), -4);
		// if( preg_match('/['.$pattern.']/u', utf8("0x".$unicode_char)) )
		if( preg_match('/['.$pattern.']/u', codepoint_decode("\u".$unicode_char) ) )
		{
			$result[] = "&#x".$unicode_char;
		}
	}
	echo $pattern . ' : ' . '<br/>';
	print_d( $result );
}
?>
</body>
</html>