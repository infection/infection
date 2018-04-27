# Title

Fixes https://github.com/infection/infection/issues/320 issue

## Summary
This test ensures Infection uses bootstrap file from project's phpunit.xml.

It's essential because bootstrap file can autoload, require additional classes or just have needed logic.