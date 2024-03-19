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

namespace Infection\Tests\Logger\Html;

use Infection\Logger\Html\HtmlFileLogger;
use Infection\Logger\Html\StrykerHtmlReportBuilder;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use PHPUnit\Framework\TestCase;

final class HtmlFileLoggerTest extends TestCase
{
    public function test_it_builds_html(): void
    {
        $htmlLogger = new HtmlFileLogger(new StrykerHtmlReportBuilder(new MetricsCalculator(2), new ResultsCollector()));

        $logLines = $htmlLogger->getLogLines();

        $this->assertSame(
            <<<'HTML'
            <!DOCTYPE html>
            <html>
                <body>
                    <a href="/">Back</a>
                    <mutation-test-report-app title-postfix="Infection"></mutation-test-report-app>
                    <script defer src="https://cdn.jsdelivr.net/npm/mutation-testing-elements/dist/mutation-test-elements.js"></script>
                    <script>
                        const app = document.getElementsByTagName('mutation-test-report-app').item(0);
                        function updateTheme() {
                            document.body.style.backgroundColor = app.themeBackgroundColor;
                        }
                        app.addEventListener('theme-changed', updateTheme);
                        updateTheme();

                        document.getElementsByTagName('mutation-test-report-app').item(0).report = {"schemaVersion":"1","thresholds":{"high":90,"low":50},"files":{},"testFiles":{},"framework":{"name":"Infection","branding":{"homepageUrl":"https:\/\/infection.github.io\/","imageUrl":"https:\/\/infection.github.io\/images\/logo.png"}}}
                        ;
                    </script>
                </body>
            </html>
            HTML,
            $logLines[0],
        );
    }
}
