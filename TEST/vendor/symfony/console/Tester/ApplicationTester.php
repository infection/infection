<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Tester;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Application;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\ArrayInput;
class ApplicationTester
{
    use TesterTrait;
    private $application;
    public function __construct(Application $application)
    {
        $this->application = $application;
    }
    public function run(array $input, array $options = [])
    {
        $this->input = new ArrayInput($input);
        if (isset($options['interactive'])) {
            $this->input->setInteractive($options['interactive']);
        }
        if ($this->inputs) {
            $this->input->setStream(self::createStream($this->inputs));
        }
        $this->initOutput($options);
        return $this->statusCode = $this->application->run($this->input, $this->output);
    }
}
