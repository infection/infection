<?php

namespace newSrc\TestFramework\Coverage\Locator;

interface ReportLocator
{
    /**
     * @throws NoReportFound
     */
    public function locate(): string;
}
