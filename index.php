<?php
#!/usr/bin/env  php
include 'vendor/autoload.php';

// realpath functions seems neccessary in Phar.
$dotenv = new Dotenv\Dotenv(realpath("."));
$dotenv->load();

class Builder 
{
    const DIAGNOSTIC_START = "A d d i t i o n a l   D i a g n o s t i c   M e s s a g e s";
    const DIAGNOSTIC_END = "* * * * *   E N D   O F   A D D I T I O N A L   D I A G N O S T I C   M E S S A G E S   * * * * *";
    const SUMMARY = "M e s s a g e   S u m m a r y";

    static function run($argv) {
        $instance = new self();
        $instance->execute($argv);
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

    function execute($argv) {
        
        $object = $argv[1];
        $source = $argv[2];
        $command = $argv[3];

        $host = $_SERVER['SYSTEM_HOST'];
        $user = $_SERVER['SYSTEM_USER'];
        $directory = $_SERVER['TARGET_DIR'];
        $library = $_SERVER['BIN_LIB'];

        $command = str_replace('{OBJECT}', $object, $command);
        $command = str_replace('{SOURCE}', $source, $command);
        $command = str_replace('{BIN_LIB}', $library, $command);
        $command = str_replace('{TARGET_DIR}', $directory, $command);

        echo "Uploading $source to $host...\n";
        $output = $this->command("scp $source $user@$host:$directory");
        $this->display($output);
        echo "Complete\n";

        echo "Compiling $source on $host...\n";
        $output = $this->command('ssh '.$user.'@'. $host . ' "system \"'. $command . '\""');
        $this->displayListing($output);
    }
}
Builder::run($argv);
