<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\ConfigurationFactory;

use Infection\Configuration\Configuration;
use Infection\Configuration\Schema\SchemaConfiguration;

final class ConfigurationFactoryScenario
{
    public function __construct(
        public bool $ciDetected,
        public bool $githubActionsDetected,
        public ConfigurationFactoryInput $input,
        public Configuration $expected,
    )
    {}

    public static function create(
        bool $ciDetected,
        bool $githubActionsDetected,
        ConfigurationFactoryInput $input,
        Configuration $expected,
    ): self
    {
        return new self(
            $ciDetected,
            $githubActionsDetected,
            $input,
            $expected,
        );
    }

    public function withCiDetected(bool $ciDetected): self
    {
        $clone = clone $this;
        $clone->ciDetected = $ciDetected;

        return $clone;
    }

    public function withGithubActionsDetected(bool $githubActionsDetected): self
    {
        $clone = clone $this;
        $clone->githubActionsDetected = $githubActionsDetected;

        return $clone;
    }

    public function withInput(ConfigurationFactoryInput $input): self
    {
        $clone = clone $this;
        $clone->input = $input;

        return $clone;
    }

    public function withExpected(Configuration $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }
}
