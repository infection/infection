<?php








class OCILob
{






public function load() {}








public function tell() {}












public function truncate($length = 0) {}









public function erase($offset = null, $length = null) {}



















public function flush($flag = null) {}











public function setbuffering($on_off) {}








public function getbuffering() {}







public function rewind() {}










public function read($length) {}








public function eof() {}





















public function seek($offset, $whence = OCI_SEEK_SET) {}















public function write($data, $length = null) {}










public function append(OCILob $lob_from) {}








public function size() {}










public function writetofile($filename, $start, $length) {}
















public function export($filename, $start = null, $length = null) {}










public function import($filename) {}
















public function writeTemporary($data, $lob_type = OCI_TEMP_CLOB) {}







public function close() {}













public function save($data, $offset = null) {}








public function savefile($filename) {}







public function free() {}
}






class OCICollection
{









public function append($value) {}












public function getelem($index) {}













public function assignelem($index, $value) {}










public function assign(OCICollection $from) {}







public function size() {}










public function max() {}










public function trim($num) {}







public function free() {}
}













function oci_set_call_timeout($connection, int $time_out) {}











function oci_set_db_operation($connection, string $dbop) {}
