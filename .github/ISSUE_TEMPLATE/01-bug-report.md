---
name: "\U0001F41B Bug report"
about: "If something isn't working as expected \U0001F914."
title: 'bug: '
labels: Bug, Not Confirmed
assignees: ''

---

| Question    | Answer
| ------------| ---------------
| Infection version | x.y.z (`infection.phar --version`)
| Test Framework version | PHPUnit/PhpSpec x.y.z
| CI | yes/no (local only)/either
| PHP version | x.y.z (`php -v`)
| Platform    | e.g. Ubuntu/Windows/MacOS
| Github Repo | -


<!--
- Replace this comment with your issue description.
- Please complete the above table with a correct information.
- Please include steps to reproduce your issue, or use Infection Playground https://infection-php.dev/.
- Please include any options you use when running Infection
- For general support, please use the Twitter @infection_php or Discord `#infection` channel https://discord.gg/ZUmyHTJ
-->

<!-- Please past your phpunit.xml[.dist] if no Github link to the repo provided -->
<details>
 <summary>infection.json5</summary>

 ```json5
  %infection.json5 content%
 ```
</details>

<details>
 <summary>phpunit.xml</summary>

 ```xml
  %phpunit.xml content%
 ```
</details>

<!-- Remove this section if not needed -->
 ```shell
# The command executed
 ```

<!-- Remove this section if not needed -->
<details>
 <summary>Output with issue</summary>

 ```
 The long infection output (probably with stacktrace)
 ```
</details>
