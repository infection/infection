<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger\Html;

use _HumbugBox9658796bb9f0\Infection\Logger\LineMutationTestingResultsLogger;
use function _HumbugBox9658796bb9f0\Safe\json_encode;
final class HtmlFileLogger implements LineMutationTestingResultsLogger
{
    public function __construct(private StrykerHtmlReportBuilder $strykerHtmlReportBuilder)
    {
    }
    public function getLogLines() : array
    {
        return [<<<HTML
<!DOCTYPE html>
<html>
    <body>
        <a href="/">Back</a>
        <mutation-test-report-app title-postfix="Infection"></mutation-test-report-app>
        <script defer src="https://www.unpkg.com/mutation-testing-elements"></script>
        <script>
            const app = document.getElementsByTagName('mutation-test-report-app').item(0);
            function updateTheme() {
                document.body.style.backgroundColor = app.themeBackgroundColor;
            }
            app.addEventListener('theme-changed', updateTheme);
            updateTheme();

            document.getElementsByTagName('mutation-test-report-app').item(0).report = {$this->getMutationTestingReport()}
            ;
        </script>
    </body>
</html>
HTML
];
    }
    private function getMutationTestingReport() : string
    {
        return json_encode($this->strykerHtmlReportBuilder->build());
    }
}
