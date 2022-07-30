# Summary

* Related to https://github.com/infection/infection/issues/1677

Previously, Infection didn't prefixed `Composer\*` namespaces and we had a bug where not prefixed `Composer\XdebugHandler` inside the PHAR of version 2.* could conflict with app's `Composer\XdebugHandler` of verions 1.*.

This test ensures Infection PHAR with prefixed `Composer\XdebugHandler` can work with the app's old `Composer\XdebugHandler`
