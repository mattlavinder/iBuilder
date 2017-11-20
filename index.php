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

    protected $isPASE = false;
    protected $variables = [];
    
    public function __construct() {
        if (PHP_OS == 'AIX') {
            $this->isPASE = true;
        }
        $this->variables['TARGET_DIR'] = $_SERVER['TARGET_DIR'];
        $this->variables['BIN_LIB'] = $_SERVER['BIN_LIB'];
        $this->variables['SYSTEM_HOST'] = $_SERVER['SYSTEM_HOST'];
        $this->variables['SYSTEM_USER'] = $_SERVER['SYSTEM_USER'];
    }
    
    static function run($argv) {
        $instance = new self();
        $instance->execute($argv);
    }

    function command($command) {
        echo "$command\n";
        exec($command, $output, $status);
        if ($status != 0) {
            $this->display($output);
            exit ($status);
        }
        return $output;
    }
    
    function remoteCommand($command) {
    
        $this->variables['COMMAND'] = str_replace('"', '\"', $command);
        $sshCommand = 'ssh {SYSTEM_USER}@{SYSTEM_HOST} "{COMMAND}"';
        $sshCommand = $this->parseCommand($sshCommand);
        return $this->command($sshCommand);
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
    
    function parseCommand($command) {
        foreach ($this->variables as $key => $value) {
            $command = str_replace('{'.$key.'}', $value, $command);
        }
        return $command;
    }
    
    function uploadSource() {
        echo "Uploading " . $this->variables['SOURCE'] . " to ".$this->variables['SYSTEM_HOST'] . "...\n";
        $scpCommand = $this->parseCommand("scp {SOURCE} {SYSTEM_USER}@{SYSTEM_HOST}:{TARGET_DIR}");
        $output = $this->command($scpCommand);
        $this->display($output);
        echo "Complete\n";
    }

    function execute($argv) {
    
        $this->variables['OBJECT'] = $argv[1];
        $this->variables['SOURCE'] = $argv[2];
        $command = $argv[3];
        
        
        $command = $this->parseCommand($command);

        if (!$this->isPASE) {
            $this->uploadSource();
            echo "Compiling ".$this->variables['SOURCE']." on ".$this->variables['SYSTEM_HOST'] ."...\n";
        }
        else {
            echo "Compiling ".$this->variables['SOURCE'] ."...\n";
        }
        
        $command = 'system "'. $command . '"';
        if ($this->isPASE) {
            $output = $this->command($command);
        }
        else {
            $output = $this->remoteCommand($command);
        }
        $this->displayListing($output);
    }
}
Builder::run($argv);
