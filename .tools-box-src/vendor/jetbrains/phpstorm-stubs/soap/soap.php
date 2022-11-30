<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Internal\TentativeType;
use JetBrains\PhpStorm\Pure;






class SoapClient
{
/**
@removed


























































































































*/
public function SoapClient($wsdl, array $options = null) {}





























































































































public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $wsdl,
array $options = null
) {}








#[Deprecated]
#[TentativeType]
public function __call(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
array $args
): mixed {}










































#[TentativeType]
public function __soapCall(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
array $args,
#[LanguageLevelTypeAware(['8.0' => 'array|null'], default: '')] $options = null,
$inputHeaders = null,
&$outputHeaders = null
): mixed {}







#[TentativeType]
public function __getLastRequest(): ?string {}







#[TentativeType]
public function __getLastResponse(): ?string {}







#[TentativeType]
public function __getLastRequestHeaders(): ?string {}







#[TentativeType]
public function __getLastResponseHeaders(): ?string {}








#[TentativeType]
public function __getFunctions(): ?array {}







#[TentativeType]
public function __getTypes(): ?array {}







#[TentativeType]
public function __getCookies(): array {}























#[TentativeType]
public function __doRequest(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $request,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $location,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $action,
#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $version,
#[LanguageLevelTypeAware(["8.0" => 'bool'], default: 'int')] $oneWay = false
): ?string {}













#[TentativeType]
public function __setCookie(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(["8.0" => "string|null"], default: "string")] $value
): void {}










#[TentativeType]
public function __setLocation(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $location = ''): ?string {}












#[TentativeType]
public function __setSoapHeaders(
#[PhpStormStubsElementAvailable(from: '5.3', to: '5.6')] $headers,
#[PhpStormStubsElementAvailable(from: '7.0')] $headers = null
): bool {}
}





class SoapVar
{




public int $enc_type;





public mixed $enc_value;





public string|null $enc_stype;





public string|null $enc_ns;





public string|null $enc_name;





public string|null $enc_namens;
























public function __construct(
#[LanguageLevelTypeAware(["8.0" => 'mixed'], default: '')] $data,
#[LanguageLevelTypeAware(["7.1" => "int|null"], default: "int")] $encoding,
#[LanguageLevelTypeAware(["8.0" => "string|null"], default: "string")] $typeName,
#[LanguageLevelTypeAware(["8.0" => 'string|null'], default: '')] $typeNamespace = '',
#[LanguageLevelTypeAware(["8.0" => 'string|null'], default: '')] $nodeName = '',
#[LanguageLevelTypeAware(["8.0" => 'string|null'], default: '')] $nodeNamespace = ''
) {}

/**
@removed





















*/
public function SoapVar($data, $encoding, $type_name = '', $type_namespace = '', $node_name = '', $node_namespace = '') {}
}





class SoapServer
{








































public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $wsdl,
array $options = null
) {}

/**
@removed






































*/
public function SoapServer($wsdl, array $options = null) {}





















#[TentativeType]
public function setPersistence(#[LanguageLevelTypeAware(['8.0' => 'int'], default: '')] $mode): void {}











#[TentativeType]
public function setClass(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $class,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] ...$args
): void {}









#[TentativeType]
public function setObject(object $object): void {}























#[TentativeType]
public function addFunction($functions): void {}







#[TentativeType]
public function getFunctions(): array {}











#[TentativeType]
public function handle(#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $request = null): void {}






















#[TentativeType]
public function fault(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $code,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $string,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $actor = null,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $details = null,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name = null
): void {}










#[TentativeType]
public function addSoapHeader(SoapHeader $header): void {}
}





class SoapFault extends Exception
{



#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $faultcode;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $faultstring;




#[LanguageLevelTypeAware(['8.1' => 'string|null'], default: '')]
public $faultactor;




#[LanguageLevelTypeAware(['8.1' => 'mixed'], default: '')]
public $detail;




#[LanguageLevelTypeAware(['8.1' => 'string'], default: '')]
public $faultname;




#[LanguageLevelTypeAware(['8.1' => 'mixed'], default: '')]
public $headerfault;





public string|null $faultcodens;





public string|null $_name;

























#[Pure]
public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'array|string|null'], default: '')] $code,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $string,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $actor = null,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $details = null,
#[LanguageLevelTypeAware(['8.0' => 'string|null'], default: '')] $name = null,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $headerFault = null
) {}

/**
@removed






















*/
public function SoapFault($faultcode, $faultstring, $faultactor = null, $detail = null, $faultname = null, $headerfault = null) {}







#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')]
public function __toString() {}
}





class SoapParam
{




public string $param_name;





public mixed $param_data;














public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name
) {}

/**
@removed











*/
public function SoapParam($data, $name) {}
}





class SoapHeader
{




public string $namespace;





public string $name;





public mixed $data;





public bool $mustUnderstand;





public string|int|null $actor;





















public function __construct(
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $namespace,
#[LanguageLevelTypeAware(['8.0' => 'string'], default: '')] $name,
#[LanguageLevelTypeAware(['8.0' => 'mixed'], default: '')] $data = null,
#[LanguageLevelTypeAware(['8.0' => 'bool'], default: '')] $mustUnderstand = false,
#[LanguageLevelTypeAware(['8.0' => 'string|int|null'], default: '')] $actor = null
) {}

/**
@removed


















*/
public function SoapHeader($namespace, $name, $data = null, $mustunderstand = false, $actor = null) {}
}









function use_soap_error_handler(bool $enable = true): bool {}









function is_soap_fault(mixed $object): bool {}

define('SOAP_1_1', 1);
define('SOAP_1_2', 2);
define('SOAP_PERSISTENCE_SESSION', 1);
define('SOAP_PERSISTENCE_REQUEST', 2);
define('SOAP_FUNCTIONS_ALL', 999);
define('SOAP_ENCODED', 1);
define('SOAP_LITERAL', 2);
define('SOAP_RPC', 1);
define('SOAP_DOCUMENT', 2);
define('SOAP_ACTOR_NEXT', 1);
define('SOAP_ACTOR_NONE', 2);
define('SOAP_ACTOR_UNLIMATERECEIVER', 3);
define('SOAP_COMPRESSION_ACCEPT', 32);
define('SOAP_COMPRESSION_GZIP', 0);
define('SOAP_COMPRESSION_DEFLATE', 16);
define('SOAP_AUTHENTICATION_BASIC', 0);
define('SOAP_AUTHENTICATION_DIGEST', 1);
define('UNKNOWN_TYPE', 999998);
define('XSD_STRING', 101);
define('XSD_BOOLEAN', 102);
define('XSD_DECIMAL', 103);
define('XSD_FLOAT', 104);
define('XSD_DOUBLE', 105);
define('XSD_DURATION', 106);
define('XSD_DATETIME', 107);
define('XSD_TIME', 108);
define('XSD_DATE', 109);
define('XSD_GYEARMONTH', 110);
define('XSD_GYEAR', 111);
define('XSD_GMONTHDAY', 112);
define('XSD_GDAY', 113);
define('XSD_GMONTH', 114);
define('XSD_HEXBINARY', 115);
define('XSD_BASE64BINARY', 116);
define('XSD_ANYURI', 117);
define('XSD_QNAME', 118);
define('XSD_NOTATION', 119);
define('XSD_NORMALIZEDSTRING', 120);
define('XSD_TOKEN', 121);
define('XSD_LANGUAGE', 122);
define('XSD_NMTOKEN', 123);
define('XSD_NAME', 124);
define('XSD_NCNAME', 125);
define('XSD_ID', 126);
define('XSD_IDREF', 127);
define('XSD_IDREFS', 128);
define('XSD_ENTITY', 129);
define('XSD_ENTITIES', 130);
define('XSD_INTEGER', 131);
define('XSD_NONPOSITIVEINTEGER', 132);
define('XSD_NEGATIVEINTEGER', 133);
define('XSD_LONG', 134);
define('XSD_INT', 135);
define('XSD_SHORT', 136);
define('XSD_BYTE', 137);
define('XSD_NONNEGATIVEINTEGER', 138);
define('XSD_UNSIGNEDLONG', 139);
define('XSD_UNSIGNEDINT', 140);
define('XSD_UNSIGNEDSHORT', 141);
define('XSD_UNSIGNEDBYTE', 142);
define('XSD_POSITIVEINTEGER', 143);
define('XSD_NMTOKENS', 144);
define('XSD_ANYTYPE', 145);
define('XSD_ANYXML', 147);
define('APACHE_MAP', 200);
define('SOAP_ENC_OBJECT', 301);
define('SOAP_ENC_ARRAY', 300);
define('XSD_1999_TIMEINSTANT', 401);
define('XSD_NAMESPACE', "http://www.w3.org/2001/XMLSchema");
define('XSD_1999_NAMESPACE', "http://www.w3.org/1999/XMLSchema");
define('SOAP_SINGLE_ELEMENT_ARRAYS', 1);
define('SOAP_WAIT_ONE_WAY_CALLS', 2);
define('SOAP_USE_XSI_ARRAY_TYPE', 4);
define('WSDL_CACHE_NONE', 0);
define('WSDL_CACHE_DISK', 1);
define('WSDL_CACHE_MEMORY', 2);
define('WSDL_CACHE_BOTH', 3);





define('SOAP_SSL_METHOD_TLS', 0);





define('SOAP_SSL_METHOD_SSLv2', 1);





define('SOAP_SSL_METHOD_SSLv3', 2);





define('SOAP_SSL_METHOD_SSLv23', 3);


