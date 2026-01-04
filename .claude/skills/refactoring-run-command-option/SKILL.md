---
name: refactor-cmd-option
description: Extracts a Console command option from RunCommand into a dedicated option class for re-usability.
---

## Input Required

You need the user to specify:

- The option constant name (e.g., `OPTION_LOGGER_GITLAB`)
- OR the option name which is the value of the constant (e.g., `logger-gitlab`)

The available options can be found in `/src/Command/RunCommand.php`.


## Execution Steps

### Phase 1: Discovery and Analysis

1. **Read example option classes** to understand patterns:
   - `/src/Command/Option/ConfigurationOption.php`
   - `/src/Command/Option/MapSourceClassToTestOption.php`
   - `/src/Command/Git/Option/BaseOption.php`

2. **Locate the option in RunCommand.php**:
   - Read `/src/Command/RunCommand.php`
   - Find the constant definition (e.g., `public const OPTION_FOO_BAR = 'foo-bar'`)
   - Note the option name from the constant value
   - Find the option definition in the `configure()` method
   - Find where the option value is retrieved (usually in `createContainer()`)

3. **Check RunCommandHelper.php**:
   - Read `/src/Command/RunCommandHelper.php`
   - Search for a getter method (e.g., `getFooBar()`)
   - If found, extract the complete method body - you'll need this logic

4. **Determine the pattern** by analyzing:
   - Option type: `VALUE_REQUIRED`, `VALUE_OPTIONAL`, or `VALUE_NONE`
   - Default value if any
   - Return type from helper method or usage context
   - Parsing/validation logic

### Phase 2: Create Todo List

Use the TodoWrite tool to create a task list:

```
- Analyze option and determine pattern
- Create new option class
- Update RunCommand.php imports
- Replace option definition in configure()
- Replace option value retrieval
- Remove constant from RunCommand.php
- Update RunCommandHelper.php (if applicable)
- Update tests
- Verify changes
```

Update the todo list as you progress through each task.

### Phase 3: Create the Option Class

1. **Determine class name and location**:
   - Convert option name to PascalCase + "Option" suffix
   - Example: `logger-gitlab` → `LoggerGitlabOption`
   - Use `/src/Command/Option/` for general options
   - Use `/src/Command/Git/Option/` for git-related options (if option name contains "git")

2. **Create the option class file** at the determined path with:

```php
<?php

declare(strict_types=1);

namespace Infection\Command\Option;

use Infection\CannotBeInstantiated;
use Infection\Console\IO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
// Add required imports based on the parsing logic

/**
 * @internal
 */
final class [ClassName]
{
    use CannotBeInstantiated;

    public const NAME = '[option-name]';

    /**
     * @template T of Command
     * @param T $command
     *
     * @return T
     */
    public static function addOption(Command $command): Command
    {
        return $command->addOption(
            self::NAME,
            [short-name-or-null],
            InputOption::[VALUE_TYPE],
            '[description]',
            [default-value-or-omit],
        );
    }

    /**
     * @return [return-type]
     */
    public static function get(IO $io): [return-type]
    {
        // Copy parsing logic from RunCommandHelper method or RunCommand
        // Replace $this->input->getOption() with $io->getInput()->getOption(self::NAME)
        // Replace RunCommand::OPTION_FOO_BAR with self::NAME
    }
}
```

**Fill in the template**:
   - Copy the exact addOption() parameters from RunCommand's configure() method
   - Copy parsing logic from helper method (if exists) or from createContainer()
   - Adjust variable references: `$this->input` → `$io->getInput()`
   - Adjust constant references: `RunCommand::OPTION_FOO_BAR` → `self::NAME`
   - Add necessary imports for the parsing logic

### Phase 4: Update RunCommand.php

1. **Add the import** after reading the existing imports section:
   - Place it alphabetically among other `Infection\Command\Option\*` imports
   - Format: `use Infection\Command\Option\[ClassName];`

2. **Find and replace option definition** in `configure()` method:
   - Locate the `->addOption(self::OPTION_FOO_BAR, ...)` block
   - Note if it's in a chain (has `->addOption` before and/or after)
   - Replace the entire addOption block with: `[ClassName]::addOption($this)`
   - If part of a chain, ensure proper formatting:
     - If the previous line ends with `;`, start a new statement
     - If chaining, maintain the `->` pattern

3. **Replace value retrieval**:
   - Find where the option is accessed (search for the constant name)
   - Replace `$commandHelper->getFooBar()` with `[ClassName]::get($io)`
   - OR replace `$input->getOption(self::OPTION_FOO_BAR)` with `[ClassName]::get($io)`

4. **Remove the constant**:
   - Delete the line: `public const OPTION_FOO_BAR = '...';`
   - Keep any doc comment on the same line if it's related to adjacent constants

### Phase 5: Update RunCommandHelper.php (If Applicable)

1. **Check if getter method exists** using the Grep tool:
   ```
   pattern: "function get[MethodName]"
   path: src/Command/RunCommandHelper.php
   ```

2. **If method exists**:
   - Delete the entire method
   - Note any imports that were used only in this method
   - Delete unused imports (use Grep to verify they're not used elsewhere)

### Phase 6: Update Tests

1. **Read the test file**: `/tests/phpunit/Command/RunCommandHelperTest.php`

2. **Search for test methods** related to the option:
   - Look for `test_it_[something]_[option_name]` or similar
   - Look for corresponding `provides[OptionName]` data provider

3. **Remove test method and data provider**:
   - Delete the test method that tests the removed helper method
   - Delete the data provider method
   - Look for and remove any imports used only in the deleted test

### Phase 7: Verification

1. **Check PHP syntax** using Bash tool:
   ```bash
   make cs
   ```

2. **Search for orphaned references** using Grep:
   ```
   pattern: OPTION_FOO_BAR
   output_mode: content
   ```
   - Should only find references in comments or unrelated code
   - If found in code, investigate and update

3. **Run tests** using Bash tool:
   ```bash
   vendor/bin/phpunit tests/phpunit/Command/RunCommandHelperTest.php
   ```

4. **Report results** to user:
   - List all files created/modified
   - Confirm tests pass
   - Note any warnings or issues

### Phase 8: Complete Todo List

Mark all todos as completed and summarize the changes made.

## Pattern Recognition Guide

When analyzing the option, identify which pattern it follows:

### Pattern 1: Simple String Option
- **Indicators**: Direct string value, minimal processing
- **Template**:
```php
public static function get(IO $io): ?string
{
    $value = trim((string) $io->getInput()->getOption(self::NAME));
    return $value === '' ? null : $value;
}
```

### Pattern 2: Optional Boolean Flag
- **Indicators**: `VALUE_OPTIONAL` with `false` default, checks for false/null/string
- **Template**:
```php
public static function get(IO $io): ?string
{
    $inputValue = $io->getInput()->getOption(self::NAME);

    if ($inputValue === false) {
        return null; // Not provided
    }

    if ($inputValue === null) {
        return 'default-enabled-value'; // Provided without value
    }

    // Validate the provided value
    if (!in_array($inputValue, ['allowed', 'values'], true)) {
        throw new InvalidArgumentException(sprintf(
            'Cannot pass "%s" to "--%s": only ... supported',
            $inputValue,
            self::NAME,
        ));
    }

    return $inputValue;
}
```

### Pattern 3: Required with Validation
- **Indicators**: Asserts, validation checks, required value
- **Template**:
```php
public static function get(IO $io): ?string
{
    $value = $io->getInput()->getOption(self::NAME);

    if ($value === null) {
        return null;
    }

    $trimmedValue = trim((string) $value);

    Assert::stringNotEmpty(
        $trimmedValue,
        sprintf('Expected a non-blank value for the option "--%s".', self::NAME),
    );

    return $trimmedValue;
}
```

### Pattern 4: Enum/Choice Option
- **Indicators**: Limited set of allowed values, validation against list
- **Template**:
```php
public static function get(IO $io): string
{
    $value = trim((string) $io->getInput()->getOption(self::NAME));

    $allowedValues = ['value1', 'value2', 'value3'];

    if (!in_array($value, $allowedValues, true)) {
        throw new InvalidArgumentException(sprintf(
            'Option "--%s" must be one of: %s. Got: "%s"',
            self::NAME,
            implode(', ', $allowedValues),
            $value,
        ));
    }

    return $value;
}
```

### Pattern 5: Integer/Numeric Option
- **Indicators**: Numeric check, special keywords like 'max'
- **Template**:
```php
public static function get(IO $io): ?int
{
    $value = $io->getInput()->getOption(self::NAME);

    if ($value === null) {
        return null;
    }

    if (is_numeric($value)) {
        return (int) $value;
    }

    if ($value === 'max') {
        return PHP_INT_MAX; // or calculated value
    }

    throw new InvalidArgumentException(sprintf(
        'Option "--%s" must be an integer or "max". Got: "%s"',
        self::NAME,
        $value,
    ));
}
```

## Important Rules

1. **Always use TodoWrite** to track progress
2. **Always maintain the fluent interface** - `addOption()` must return `$command`
3. **Always copy the exact BSD license header** from the template above
4. **Never skip verification steps** - syntax checking and tests are mandatory
5. **Always use the exact formatting** from existing option classes
6. **Preserve the chaining pattern** in configure() method
7. **Use Edit tool, never Write** when modifying existing files
8. **Read files before editing** to understand the context
9. **Match the return type** to what the container expects
10. **Include all necessary imports** for validation and parsing logic

## Error Handling

If you encounter issues:

1. **Constant not found**: Ask user to provide the correct constant name or option name
2. **Multiple patterns match**: Ask user which pattern to use or make a decision based on the option type
3. **Helper method has complex logic**: Copy it exactly and ask user to review
4. **Tests fail**: Report the failure and ask user how to proceed
5. **Grep finds multiple references**: List them and ask user if they should be updated

## Example Invocations

User might say:
- "Extract OPTION_LOGGER_GITLAB"
- "Refactor the logger-gitlab option"
- "Move OPTION_MIN_MSI to its own class"

## Success Criteria

The refactoring is complete when:
- ✅ New option class file created with proper structure
- ✅ RunCommand.php updated (import, configure(), createContainer())
- ✅ Constant removed from RunCommand.php
- ✅ RunCommandHelper.php updated (method removed if existed)
- ✅ Tests updated (test methods removed if existed)
- ✅ All PHP syntax checks pass
- ✅ All tests pass
- ✅ No orphaned references to old constant
- ✅ Todo list completed
