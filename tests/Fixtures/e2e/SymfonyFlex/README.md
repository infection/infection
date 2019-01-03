# Title

* https://github.com/infection/infection/issues/493

## Summary
Symfony flex should correctly detect phpunit executable

## Full Ticket
Symfony flex has a phpunit executable located under bin/phpunit.
Infection does not detect this yet, and will not be able to find
the executable.

This is a common enough use case that we should either detect this
during runtime, or help the user with a guesser during the initial
set up.