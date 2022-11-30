<?php















function rrd_error() {}













function rrd_create($filename, $options) {}













function rrd_fetch($file, $options) {}













function rrd_first($file, $raaindex = 0) {}













function rrd_graph($file, $options) {}










function rrd_info($file) {}










function rrd_last($file) {}










function rrd_lastupdate($file) {}
















function rrd_restore($xml_file, $rrd_file, $options = []) {}













function rrd_tune($file, $options) {}













function rrd_update($file, $options) {}







function rrd_version() {}










function rrd_xport($options) {}









function rrd_disconnect() {}







function rrdc_disconnect() {}






class RRDCreator
{












public function addArchive($description) {}












public function addDataSource($description) {}















public function __construct($path, $startTime = '', $step = 0) {}








public function save() {}
}






class RRDGraph
{








public function __construct($path) {}







public function save() {}









public function saveVerbose() {}










public function setOptions($options) {}
}






class RRDUpdater
{









public function __construct($path) {}














public function update($values, $time = '') {}
}


