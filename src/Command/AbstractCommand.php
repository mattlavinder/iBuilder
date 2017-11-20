<?php
namespace Mindful\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    protected $variables = [];

    protected function globalConfigure() {
        $this
            ->addOption(
                'var',
                'v',
                InputOption::VALUE_IS_ARRAY,
                'Extra variables for commands',
                1
            );
    }

    function parseCommand($command) {
        foreach ($this->variables as $key => $value) {
            $command = str_replace("{$key}", $value, $commnad);
        }
        return $command;
    }

    function extractVariables(InputInterface $input) {
        $variables = $input->getOption("var");
        foreach ($variables as $var) {
            $parts = explode(':', $var);
            $this->variables[$part[0]] = $part[1];
        }
    }

    function command($command) {
        exec($command, $output, $status);
        if ($status != 0) {
            $this->display($output);
            exit ($status);
        }
        return $output;
    }

    function display($output) {
        foreach ($output as $line) {
            echo $line . "\n";
        }
    }
}