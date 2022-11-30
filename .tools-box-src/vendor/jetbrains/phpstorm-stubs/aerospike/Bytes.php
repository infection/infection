<?php























namespace Aerospike;














class Bytes implements \Serializable
{




public $s;






public function __construct($bin_str) {
$this->s = $bin_str;
}







public function serialize() {
return $this->s;
}







public function unserialize($bin_str) {
return $this->s = $bin_str;
}






public function __toString() {
return $this->s;
}







public static function unwrap(Bytes $bytes_wrap) {
return $bytes_wrap->s;
}
}
