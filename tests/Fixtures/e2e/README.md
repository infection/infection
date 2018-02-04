#How-to

Every sub-folder should contain an e2e test case for `infection`. For each of these folders `standard_script.bash` is ran.
This runs infection, and checks the difference between `expected-output.txt` and `infection-log.txt`.

If your test-case needs more sophisticated checks you can provide a `run_test.bash` file in the sub-folder which should
contain all needed checks.

All tests exiting with a non 0 exit code are considered failures, and will be logged in `error.log`. The error log contains
all information normally printed to the console of failed tests.
