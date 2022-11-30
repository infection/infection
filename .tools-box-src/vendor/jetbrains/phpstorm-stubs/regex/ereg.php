<?php


use JetBrains\PhpStorm\Deprecated;

/**
@removed





























*/
#[Deprecated(reason: "Use preg_match() instead", since: "5.3")]
function ereg($pattern, $string, ?array &$regs = null) {}

/**
@removed




















*/
#[Deprecated(reason: "Use preg_replace() instead", since: "5.3")]
function ereg_replace($pattern, $replacement, $string) {}

/**
@removed




























*/
#[Deprecated(reason: "Use preg_match() instead", since: "5.3")]
function eregi($pattern, $string, array &$regs = null) {}

/**
@removed




















*/
#[Deprecated(reason: "Use preg_replace() instead", since: "5.3")]
function eregi_replace($pattern, $replacement, $string) {}

/**
@removed





































*/
#[Deprecated(reason: "Use preg_split() instead", since: "5.3")]
function split($pattern, $string, $limit = -1) {}

/**
@removed





































*/
#[Deprecated(reason: "Use preg_split() instead", since: "5.3")]
function spliti($pattern, $string, $limit = -1) {}

/**
@removed










*/
#[Deprecated(since: '5.3')]
function sql_regcase($string) {}


