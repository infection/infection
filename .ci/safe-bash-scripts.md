# Safe bash script configuration instructions

Source: [Vaneyckt: Safer bash scripts with 'set -euxo pipefail'][vaneyckt safer bash scripts]

Shortcut: `set -Eeuox pipefail`

## Set `-e`

Makes the script exit immediately when a command fails.

Before:

```
#!/bin/bash

# 'foo' is a non-existing command
foo
echo "bar"

# output
# ------
# line 4: foo: command not found
# bar
#
# Note how the script didn't exit when the foo command could not be found.
# Instead it continued on and echoed 'bar'.
```

After:

```
#!/bin/bash
set -e

# 'foo' is a non-existing command
foo
echo "bar"

# output
# ------
# line 5: foo: command not found
#
# This time around the script exited immediately when the foo command wasn't
# found. Such behavior is much more in line with that of higher-level languages.
```

## Set `-o pipefail`

Makes the script exit immediately when a command in a pipeline fails instead of
looking only at the exit code of the pipeline.

Before: 

```
#!/bin/bash
set -e

# 'foo' is a non-existing command
foo | echo "a"
echo "bar"

# output
# ------
# a
# line 5: foo: command not found
# bar
#
# Note how the non-existing foo command does not cause an immediate exit, as
# it's non-zero exit code is ignored by piping it with '| echo "a"'.
```

After:

```
#!/bin/bash
set -o pipefail

# 'foo' is a non-existing command
foo | echo "a"
echo "bar"

# output
# ------
# a
# line 5: foo: command not found
#
# This time around the non-existing foo command causes an immediate exit, as
# '-o pipefail' will prevent piping from causing non-zero exit codes to be 
# ignored.
```

## Set `-u`

Treat unset variables as an error and exit immediately.

Before:

```
#!/bin/bash

echo "$a
echo "bar"

# output
# ------
#
# bar
#
# The default behavior will not cause unset variables to trigger an immediate 
# exit. In this particular example, echoing the non-existing $a variable will 
# just cause an empty line to be printed.
```

After:

```
#!/bin/bash
set -u

echo "$a"
echo "bar"

# output
# ------
# line 5: a: unbound variable
#
# Notice how 'bar' no longer gets printed. We can clearly see that '-u' did 
# indeed cause an immediate exit upon encountering an unset variable.
```

For cases where you want to deal with unset variables, check the 
[${a:-b}][unset variable assignment]


## Set `-u`

Makes the bash script print each command before executing it.

```
#!/bin/bash
set -x

a=5
echo $a
echo "bar"

# output
# ------
# + a=5
# + echo 5
# 5
# + echo bar
# bar
```

## Set `-E`

[Traps][traps] are fired when a bash script catches certain signals (e.g. `SIGINT` or `SIGTERM`). 
However using `-e` without `-E` will cause an `ERR` trap to not fire in certain conditions:  

Before:

```
#!/bin/bash
set -e

trap "echo ERR trap fired!" ERR

myfunc()
{
  # 'foo' is a non-existing command
  foo
}

myfunc
echo "bar"

# output
# ------
# line 9: foo: command not found
#
# Notice that while '-e' did indeed cause an immediate exit upon trying to execute the non-existing 
# foo command, it did not case the ERR trap to be fired.
```

After:

```
#!/bin/bash
set -Ee

trap "echo ERR trap fired!" ERR

myfunc()
{
  # 'foo' is a non-existing command
  foo
}

myfunc
echo "bar"

# output
# ------
# line 9: foo: command not found
# ERR trap fired!
#
# Not only do we still have an immediate exit, we can also clearly see that the
# ERR trap was actually fired now.
```

<br />
<hr />
<br />


[vaneyckt safer bash scripts]: https://vaneyckt.io/posts/safer_bash_scripts_with_set_euxo_pipefail/
[unset variable assignment]: https://unix.stackexchange.com/questions/122845/using-a-b-for-variable-assignment-in-scripts/122878
[traps]: http://tldp.org/LDP/Bash-Beginners-Guide/html/sect_12_02.html
