<?php


use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Internal\PhpStormStubsElementAvailable;
use JetBrains\PhpStorm\Pure;







class LibXMLError
{









public int $level;







public int $code;












public int $column;







public string $message;





public string $file;







public int $line;
}










function libxml_set_streams_context($context): void {}










function libxml_use_internal_errors(
#[PhpStormStubsElementAvailable(from: '5.3', to: '7.4')] bool $use_errors = false,
#[PhpStormStubsElementAvailable(from: '8.0')] ?bool $use_errors = null
): bool {}







#[Pure(true)]
function libxml_get_last_error(): LibXMLError|false {}






function libxml_clear_errors(): void {}







#[Pure(true)]
function libxml_get_errors(): array {}












#[Deprecated(since: "8.0")]
function libxml_disable_entity_loader(bool $disable = true): bool {}













function libxml_set_external_entity_loader(?callable $resolver_function): bool {}





define('LIBXML_VERSION', 20901);





define('LIBXML_DOTTED_VERSION', "2.9.1");
define('LIBXML_LOADED_VERSION', 20901);





define('LIBXML_NOENT', 2);





define('LIBXML_DTDLOAD', 4);





define('LIBXML_DTDATTR', 8);





define('LIBXML_DTDVALID', 16);





define('LIBXML_NOERROR', 32);





define('LIBXML_NOWARNING', 64);





define('LIBXML_NOBLANKS', 256);





define('LIBXML_XINCLUDE', 1024);





define('LIBXML_NSCLEAN', 8192);





define('LIBXML_NOCDATA', 16384);





define('LIBXML_NONET', 2048);






define('LIBXML_PEDANTIC', 128);









define('LIBXML_COMPACT', 65536);








define('LIBXML_BIGLINES', 65535);








define('LIBXML_NOXMLDECL', 2);










define('LIBXML_PARSEHUGE', 524288);











define('LIBXML_NOEMPTYTAG', 4);








define('LIBXML_SCHEMA_CREATE', 1);









define('LIBXML_HTML_NOIMPLIED', 8192);









define('LIBXML_HTML_NODEFDTD', 4);





define('LIBXML_ERR_NONE', 0);





define('LIBXML_ERR_WARNING', 1);





define('LIBXML_ERR_ERROR', 2);





define('LIBXML_ERR_FATAL', 3);


