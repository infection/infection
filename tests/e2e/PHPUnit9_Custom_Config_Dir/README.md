# Title

https://github.com/infection/infection/issues/199

## Full Ticket

| Question    | Answer
| ------------| ---------------
| Infection version | 0.8.0
| Test Framework version | PHPUnit 7.0.2
| PHP version | 7.2.1
| Platform    | e.g. Ubuntu on Windows

Relative path to PHPUnit bootstrap file being incorrectly built with version 0.8.0. Version 0.7.1 works exactly as expected. PR #165 seems like the likely cause, but with no clear solution.