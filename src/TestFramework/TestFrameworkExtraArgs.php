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

namespace Infection\TestFramework;

use function base64_encode;
use const JSON_THROW_ON_ERROR;
use function Safe\base64_decode;
use function Safe\json_decode;
use function Safe\json_encode;
use function str_split;
use function str_starts_with;
use function strlen;
use function substr;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\StringInput;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class TestFrameworkExtraArgs
{
    public const string RAW_PREFIX = '@@infection-raw-test-framework-extra-args:';

    private const string PARSE_ERROR_PREFIX = 'Cannot parse `testFrameworkExtraArgs` / `--test-framework-extra-args`: ';

    /**
     * @param list<string> $argvTokens
     */
    private function __construct(
        public bool $isPresent,
        public bool $isLegacy,
        public string $value,
        public array $argvTokens,
    ) {
    }

    public static function legacy(?string $value, bool $isPresent): self
    {
        return new self($isPresent, true, trim($value ?? ''), []);
    }

    public static function raw(?string $value, bool $isPresent): self
    {
        $value = trim($value ?? '');

        if ($value === '') {
            return new self($isPresent, false, '', []);
        }

        try {
            self::assertNoUnclosedQuote($value);

            $tokens = (new StringInput($value))->getRawTokens();
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(self::PARSE_ERROR_PREFIX . $exception->getMessage(), 0, $exception);
        }

        return new self($isPresent, false, $value, $tokens);
    }

    /**
     * @param list<string> $tokens
     */
    public static function serializeRawTokens(array $tokens): string
    {
        return self::RAW_PREFIX . base64_encode(json_encode($tokens));
    }

    public static function isSerializedRaw(string $value): bool
    {
        return str_starts_with($value, self::RAW_PREFIX);
    }

    /**
     * @return list<string>
     */
    public static function unserializeRawTokens(string $value): array
    {
        Assert::true(self::isSerializedRaw($value));

        return self::assertRawTokens(json_decode(
            base64_decode(substr($value, strlen(self::RAW_PREFIX)), true),
            true,
            flags: JSON_THROW_ON_ERROR,
        ));
    }

    public function serializeForAdapter(): string
    {
        if (!$this->isPresent || $this->value === '') {
            return '';
        }

        return $this->isLegacy ? $this->value : self::serializeRawTokens($this->argvTokens);
    }

    /**
     * @return list<string>
     */
    private static function assertRawTokens(mixed $tokens): array
    {
        Assert::isList($tokens);
        Assert::allString($tokens);

        return $tokens;
    }

    private static function assertNoUnclosedQuote(string $value): void
    {
        $quote = null;
        $escaped = false;

        foreach (str_split($value) as $character) {
            if ($escaped) {
                $escaped = false;

                continue;
            }

            if ($character === '\\') {
                $escaped = true;

                continue;
            }

            if ($quote === null && ($character === '"' || $character === '\'')) {
                $quote = $character;

                continue;
            }

            if ($character === $quote) {
                $quote = null;
            }
        }

        if ($quote !== null) {
            throw new InvalidArgumentException('Unclosed quote.');
        }
    }
}
