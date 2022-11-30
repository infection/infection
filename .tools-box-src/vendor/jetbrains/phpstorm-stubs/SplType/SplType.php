<?php






abstract class SplType
{




public const __default = null;








public function __construct($initial_value = self::__default, $strict = true) {}
}






class SplInt extends SplType
{



public const __default = 0;
}






class SplFloat extends SplType
{
public const __default = 0;
}






class SplEnum extends SplType
{



public const __default = null;








public function getConstList($include_default = false) {}
}






class SplBool extends SplEnum
{



public const __default = false;




public const false = false;




public const true = true;
}






class SplString extends SplType
{



public const __default = 0;
}
