# Output streams

## Discussion

When running in a CI context it's useful to display the log file contents, to avoid the time wasted running it again locally to access infection.log

The simple option of just dumping `infection.log` to the console after the tests complete is not that simple, because most CI will stop once the infection command exits with non-zero status.

So to avoid complex and error prone scripts in CI, this test ensures that the text log can be configured to be written to a PHP stream.
