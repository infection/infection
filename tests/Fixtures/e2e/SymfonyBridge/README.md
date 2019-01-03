# Title

https://github.com/infection/infection/issues/588

## Summary
symfony/phpunit-bridge isn't supported

## Full Ticket
A lot of symfony projects use the symfony/phpunit-bridge package instead
of phpunit directly. This exposes simple-phpunit instead of phpunit as
an executable in vendor/bin. When adding infection to a project it
doesn't detect the correct executable during the config generation step.

This is a common enough use case that we should either detect this
during runtime, or help the user with a guesser during the initial
set up.
