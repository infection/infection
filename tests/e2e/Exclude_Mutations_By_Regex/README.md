## Summary

* Support test for the feature that fixes https://github.com/infection/infection/issues/697

Allows to avoid mutating such lines of code as:

```diff
- Assert::notNull($variable);
```

or

```diff
- $this->logger->error($message, ['user' => $user]);
+ $this->logger->error($message, []);
```

and so on.

Example of new config setting usage:

```json
{
    "mutators": {
        "MethodCallRemoval": {
            "ignoreSourceCodeByRegex": [
                "Assert::.*",
                "\\$this->logger.*"
            ]
        }
    }
}
```

