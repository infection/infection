# How to contribute

Contributions are always welcome. Here are a few guidelines to be aware of:

- Include tests for new behaviours introduced by PRs.
- Fixed bugs MUST be covered by test(s) to avoid regression.
- If you are on Unix-like system, you may run `./setup_environment.sh` to set up `pre-push` git
  hook.
- All code must follow the project coding style standards which can be achieved by running `make cs`
- Before implementing a new big feature, consider creating a new issue on Github. It will save your
  time when the core team is not going to accept it or has good recommendations about how to
  proceed.


## Tests

To run the tests locally, you can run `make test`. It however requires [Docker][docker]. For more
granular tests, you can run `make` to see the available commands.


<br />
<hr />

« [Go back to the readme](README.md) »


[docker]: https://www.docker.com/get-docker
