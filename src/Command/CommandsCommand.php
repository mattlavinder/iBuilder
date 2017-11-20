<?php
namespace Mindful\Command;

use Mindful\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandCommand extends AbstractCommand
{
    private $variables = [];

    const DIAGNOSTIC_START = "A d d i t i o n a l   D i a g n o s t i c   M e s s a g e s";
    const DIAGNOSTIC_END = "* * * * *   E N D   O F   A D D I T I O N A L   D I A G N O S T I C   M E S S A G E S   * * * * *";
    const SUMMARY = "M e s s a g e   S u m m a r y";
    const COMMAND_SCP = "scp {SOURCE} {SYSTEM_USE}@{SYSTEM_HOST}:{SYSTEM_DIRECTORY}";
    const COMMAND_SYSTEM = "ssh {SYSTEM_USE}@{SYSTEM_HOST} system \"{SYSTEM_COMMAND}\"";

    protected function configure()
    {
        $this
            ->setName('commands')
            ->addOption(
                'commands',
                'c',
                InputOption::VALUE_IS_ARRAY,
                "Commands to run",
                false
            )
            ->setDescription('Run commands.')
            ->setHelp('This command allows you run remote commands.');

        $this->globalConfigure();

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->variables['SYSTEM_HOST'] = $_SERVER['SYSTEM_HOST'];
        $this->variables['SYSTEM_USER'] = $_SERVER['SYSTEM_USER'];
        $this->variables['TARGET_DIR'] = $_SERVER['TARGET_DIR'];
        $this->variables['BIN_LIB'] = $_SERVER['BIN_LIB'];

        if ($input->hasOption('var')) {
            $this->extractVariables($input);
        }
        if ($this->getOption("compile")) {
            echo "Uploading " . $this->variables['SOURCE'] . " to " . $this->variables['SYSTEM_HOST']. "...\n";
            $command = $this->parseCommand(self::COMMAND_SCP);
            $output = $this->command($command);
            $this->display($output);
            echo "Complete\n";
        }

        $systemCommand = $this->getArgument("command");
        $systemCommand = $this->parseCommand($command);
        $this->variables['SYSTEM_COMMAND'] = $systemCommand;

        $command = self::COMMAND_SYSTEM;
        $command = $this->parseCommand($command);
        
        echo "Compiling " . $this->variables['SOURCE'] . "  on " . $this->variables['SYSTEM_HOST']. "...\n";
        $output = $this->command();
        $this->displayListing($output);
    }

    static function run($argv) {
        $instance = new self();
        $instance->execute($argv);
    }

    

    

    function displayListing($output) {
        $printIt = false;
        foreach ($output as $line) {
            if (trim($line) == self::DIAGNOSTIC_START) {
                $printIt = true;
            }
            elseif (trim($line) == self::SUMMARY) {
                $printIt = true;
            }

            if ($printIt) {
                echo "$line\n";
            }
            
            if (trim($line) == self::DIAGNOSTIC_END) {
                $printIt = false;
            }
        }
    }
}