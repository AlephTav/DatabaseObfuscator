<?php
/*

Сложность алгоритма O(n) = n

Упарываться по микрооптимизациям я не стал, поэтому в цикле присутствует 
обращение к хэш таблице, поиск в массиве (тем не менее обе эти операции 
выполняются за константное время) и просмотр строки с начала.

*/
 
function checkBraces(string $str): int
{
	$brackets = [
		')' => '(', 
		'}' => '{',
		']' => '[',
		'>' => '<'
	];
	
	$stack = [];
	for ($len = strlen($str), $i = 0; $i < $len; ++$i) {
		$char = $str[$i];
		
		if (in_array($char, $brackets, true)) {
			$stack[] = $char;
		} else if (isset($brackets[$char])) {
			$previousBracket = array_pop($stack);
			if ($previousBracket !== $brackets[$char]) {
				return 1;
			}
		}
	}
	
	return 0;
}

function assertBraces(string $str, int $expected)
{
	$result = checkBraces($str);
	if ($result !== $expected) {
		throw new LogicException("Expected {$expected}, but we've got {$result} for {$str}");
	}
}

assertBraces("---(++++)----", 0);
assertBraces("", 0);
assertBraces("before ( middle []) after ", 0);
assertBraces(") (", 1);
assertBraces("} {", 1);
assertBraces("<(   >)", 1);
assertBraces("(  [  <>  ()  ]  <>  )", 0);
assertBraces("   (      [)", 1);

echo 'All tests passed.';