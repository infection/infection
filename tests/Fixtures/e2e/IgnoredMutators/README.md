# Disable certain mutators for certain lines

* https://github.com/infection/infection/issues/79

## Summary
Make sure we can certain mutators for specified namespaces/classes/methods

## Full Ticket

| Question    | Answer
| ------------| ---------------
| Infection version | 0.6.1
| Test Framework version | PHPUnit 6.4.4
| PHP version | 7.1.7
| Platform    | MacOS
| Github Repo | -

For WordPress, callback methods hooked into the actions or filters need to be `public`.

The `Function Signature` mutator naturally tries to change the callback from `public` to `protected`, and flags it as a "not covered mutant".

Is there any way (to reduce known false positives) to specify that a method really should stay `public`, either by adding a line comment (e.g. `// infection:disable FunctionSignature`) or by adding an exclusion (file and line number? class and method name?) to `infection.json.dist`?

