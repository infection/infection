<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\TestFramework\Common\CommandLineArguments;

use function addcslashes;
use function array_diff_key;
use function array_flip;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function explode;
use function implode;
use function sprintf;
use function str_starts_with;
use function strcspn;
use function strlen;
use Symfony\Component\Console\Input\InputOption;
use Webmozart\Assert\Assert;

/**
 * Ordered command-line arguments. Unknown tokens are preserved;
 * only explicitly supplied option definitions are interpreted. Definitions can
 * be retained across transformations with withOptionDefinitions(). In particular,
 * an unknown option's separate value cannot be distinguished from an option.
 *
 * @internal
 */
final readonly class CommandLineArguments
{
    /**
     * @param list<string> $tokens
     * @param list<InputOption> $optionDefinitions
     */
    private function __construct(
        private array $tokens,
        private array $optionDefinitions = [],
    ) {
    }

    /**
     * @param list<string> $tokens
     */
    public static function fromTokens(array $tokens): self
    {
        return new self($tokens);
    }

    public static function fromString(string $input): self
    {
        return new self(TestFrameworkExtraArgs::parseRawTokens($input));
    }

    public function withOptionDefinitions(InputOption ...$options): self
    {
        return new self($this->tokens, [...$this->optionDefinitions, ...array_values($options)]);
    }

    public function withoutOptions(InputOption ...$options): self
    {
        return new self(
            array_values(array_diff_key(
                $this->tokens,
                array_flip($this->findOptionIndexes($options)),
            )),
            $this->optionDefinitions,
        );
    }

    public function hasOption(InputOption $option): bool
    {
        return $this->findOptionIndexes([$option]) !== [];
    }

    /**
     * Adds generated options before the terminator. Definitions distinguish known
     * option values equal to "--" from the terminator; unknown syntax stays opaque.
     *
     * @param list<string> $tokens
     */
    public function append(array $tokens): self
    {
        $optionIndexes = array_flip($this->findOptionIndexes($this->optionDefinitions));
        $offset = count($this->tokens);

        foreach ($this->tokens as $index => $token) {
            if ($token === '--' && !isset($optionIndexes[$index])) {
                $offset = $index;

                break;
            }
        }

        return new self(
            [
                ...array_slice($this->tokens, 0, $offset),
                ...$tokens,
                ...array_slice($this->tokens, $offset),
            ],
            $this->optionDefinitions,
        );
    }

    /**
     * @param list<string> $tokens
     */
    public function prepend(array $tokens): self
    {
        return new self([...$tokens, ...$this->tokens], $this->optionDefinitions);
    }

    /**
     * @return list<string>
     */
    public function toTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Serializes for legacy adapters that still accept raw argument strings.
     * Double quotes and escapes follow the tokenizer's grammar, not shell syntax.
     */
    public function toString(): string
    {
        return implode(' ', array_map(
            static fn (string $token): string => $token !== '' && strcspn($token, " \t\r\n\v\f\\\"'") === strlen($token)
                ? $token
                : '"' . addcslashes($token, '\\"') . '"',
            $this->tokens,
        ));
    }

    /**
     * @param array<array-key, InputOption> $options
     *
     * @return list<int>
     */
    private function findOptionIndexes(array $options): array
    {
        $definitions = [];
        $targetNames = array_flip(array_map(static fn (InputOption $option): string => $option->getName(), $options));

        foreach ([...$this->optionDefinitions, ...$options] as $option) {
            Assert::false(
                $option->isValueOptional() || $option->isNegatable(),
                'Argument transformations require required-value or value-less option definitions.',
            );

            $definitions['--' . $option->getName()] = $option;

            $shortcuts = $option->getShortcut();

            if ($shortcuts !== null) {
                foreach (explode('|', $shortcuts) as $shortcut) {
                    $definitions['-' . $shortcut] = $option;
                }
            }
        }

        $indexes = [];

        for ($index = 0, $count = count($this->tokens); $index < $count; ++$index) {
            $token = $this->tokens[$index];

            if ($token === '--') {
                break;
            }

            foreach ($definitions as $name => $option) {
                $attachedValue = $option->acceptValue()
                    && str_starts_with($token, $name . '=');

                if ($token !== $name && !$attachedValue) {
                    continue;
                }

                $targeted = isset($targetNames[$option->getName()]);

                if ($targeted) {
                    $indexes[] = $index;
                }

                if ($token === $name && $option->acceptValue()) {
                    ++$index;
                    Assert::keyExists($this->tokens, $index, sprintf('The option "%s" requires a value.', $name));

                    if ($targeted) {
                        $indexes[] = $index;
                    }
                }

                break;
            }
        }

        return $indexes;
    }
}
