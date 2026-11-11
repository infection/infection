#!/usr/bin/env bash

set -euo pipefail

. /etc/os-release

# Check the workload baseline.
test "$ID" = debian
test "$VERSION_ID" = 13
test "$(id -u)" != 0

# Check the PHP runtime and configuration.
php --version | grep -E '^PHP 8\.4\.[0-9]+ \(cli\) '

php -i | grep -E '^xdebug\.mode => coverage => coverage$'

php -r 'exit(extension_loaded("pcov") && extension_loaded("curl") && extension_loaded("mbstring") && extension_loaded("dom") && extension_loaded("zip") ? 0 : 1);'

php -r 'exit(ini_get("memory_limit") === "-1" ? 0 : 1);'

# Check the system timezone database, including a named zone beyond UTC.
test -r /usr/share/zoneinfo/Etc/UTC
test -r /usr/share/zoneinfo/Europe/Paris

test "$(php -r 'echo (new DateTimeImmutable("2026-01-01T00:00:00", new DateTimeZone("UTC")))->setTimezone(new DateTimeZone("Europe/Paris"))->format("P");')" = +01:00

# Check the PHP package tools.
composer --version

pie --version

# PIE must be able to compile extensions locally.
for tool in gcc make autoconf bison re2c pkg-config libtoolize phpize; do
    command -v "$tool"
done

wget --version
