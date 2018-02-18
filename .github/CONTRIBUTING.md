# How to contribute

Contributions are always welcome. Here are a few guidelines to be aware of:
 
 - Include unit or e2e tests for new behaviours introduced by PRs.
 - Fixed bugs MUST be covered by test(s) to avoid regression.
 - If you are on Unix-like system, run `./setup_environment.sh` to set up `pre-push` git hook.
 - All code must follow the `PSR-2` coding standard. Please see [PSR-2](http://www.php-fig.org/psr/psr-2/) for more details. To make this as easy as possible, we use PHPCSFixer running two simple composer scripts: `composer cs:check` and `composer cs:fix`.
 - Before implementing a new big feature, consider creating a new Issue on Github with RFC label. It will save your time when the core team is not going to accept it or has good recommendations about how to proceed.
