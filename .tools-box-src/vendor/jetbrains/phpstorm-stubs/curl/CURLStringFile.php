<?php




class CURLStringFile
{
public string $data;
public string $mime;
public string $postname;

public function __construct(string $data, string $postname, string $mime = 'application/octet-stream') {}
}
