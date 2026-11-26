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

namespace Infection\Environment;

use function array_key_exists;

/**
 * @internal
 * @final
 */
class AgentDetector
{
    /**
     * @see https://github.com/shipfastlabs/agent-detector
     * @see https://github.com/phpstan/phpstan-src/blob/2.1.x/src/Internal/AgentDetector.php
     */
    private const array AGENT_MARKERS = [
        'AI_AGENT',
        'AMP_CURRENT_THREAD_ID',
        'AUGMENT_AGENT',
        'CLAUDECODE',
        'CLAUDE_CODE',
        'CODEX_SANDBOX',
        'CODEX_THREAD_ID',
        'CURSOR_AGENT',
        'CURSOR_TRACE_ID',
        'GEMINI_CLI',
        'OPENCODE',
        'OPENCODE_CLIENT',
        'REPL_ID',
    ];

    private const string NOT_AI_AGENT = 'NOT_AI_AGENT';

    /**
     * @param array<string, string> $environment
     */
    public function isRunningInAgent(array $environment): bool
    {
        if (array_key_exists(self::NOT_AI_AGENT, $environment)) {
            return false;
        }

        foreach (self::AGENT_MARKERS as $name) {
            if (array_key_exists($name, $environment)) {
                return true;
            }
        }

        return false;
    }
}
