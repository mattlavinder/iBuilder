<?php
namespace Mindful\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandCommand extends Command
{
    private $variables = [];

    const COMMAND_SCP = "scp {SOURCE} {SYSTEM_USE}@{SYSTEM_HOST}:{SYSTEM_DIRECTORY}";

    protected function configure()
    {
        $this
            ->setName('file')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_REQUIRED,
                "Commands to run",
                false
            )
            ->addOption(
                'target',
                't',
                InputOption::VALUE_REQUIRED,
                'Target directory',
                1
            )
            ->setDescription('Run commands.')
            ->setHelp('This command allows you run remote commands.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->variables['SYSTEM_HOST'] = $_SERVER['SYSTEM_HOST'];
        $this->variables['SYSTEM_USER'] = $_SERVER['SYSTEM_USER'];
        $this->variables['TARGET_DIR'] = $_SERVER['TARGET_DIR'];
        $this->variables['BIN_LIB'] = $_SERVER['BIN_LIB'];
    }
}