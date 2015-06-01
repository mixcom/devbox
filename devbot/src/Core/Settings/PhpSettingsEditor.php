<?php
namespace Devbot\Core\Settings;

use Symfony\Component\Process\PhpProcess;

/**
 * A class that allows you to read and edit PHP settings files.
 * 
 * Many CMSes and applications have files where variables and constants are set.
 * This class allows you to read those values from the file without including it.
 * It can also change the settings files by altering constant defines and setting
 * new variable values.
 * 
 * Example:
 * ``` php
 * $settingsPath = '/path/to/settings.php';
 * $drupalSettingsData = file_get_contents($settingsPath);
 * 
 * // read the settings file
 * $drupalSettings = new PhpSettingsEditor($drupalSettingsData);
 * 
 * // make some changes
 * $variables = $drupalSettings->getOriginalVariables();
 * $conf = $variables['conf'];
 * $conf['proxy_server'] = '123.123.123.123';
 * $drupalSettings->addModifiedVariables([
 *     'conf' => $conf,
 *     'update_free_access' => false,
 * ]);
 * 
 * // write back the changes
 * $modifiedSettingsData = $drupalSettings->getModifiedScript();
 * file_put_contents($settingsPath, $modifiedSettingsData);
 * ```
 */
class PhpSettingsEditor
{
    /**
     * The unaltered original script
     * @var string
     */
    protected $originalScriptData;
    
    /**
     * The constants that are defined in the script
     * @var array
     */
    protected $originalConstants;
    
    /**
     * The constants we want to add or modify
     * @var array
     */
    protected $modifiedConstants;
    
    /**
     * The variables that are set in the script
     * @var array
     */
    protected $originalVariables;
    
    /**
     * The variables we want to add or modify
     * @var array
     */
    protected $modifiedVariables;
    
    /**
     * Create a settings editor for the specified script
     * 
     * @param string|null $scriptData Source code of the script, including <?php tag,
     *                                or null for an empty script
     */
    public function __construct($scriptData = null)
    {
        $this->modifiedConstants = [];
        $this->modifiedVariables = [];
        
        if ($scriptData !== null) {
            $this->importScriptData($scriptData);
        } else {
            $this->originalScriptData = "<?php\n\n";
            $this->originalConstants = [];
            $this->originalVariables = [];
        }
    }
    
    /**
     * Import script data and read its constants and variables
     * @param string $data Source code of the script, including <?php tag
     */
    protected function importScriptData($data)
    {
        $this->originalScriptData = $data;
        
        $normalizedScriptData = $this->normalizeScriptClosing(
            $this->originalScriptData
        );
        
        $outputScript = $this->processScriptDataForOutput($normalizedScriptData);
        
        $process = new PhpProcess($outputScript);
        $process->run();
        
        $jsonOutput = $process->getOutput();
        $originalData = json_decode($jsonOutput, true);
        
        if ($originalData === null) {
            $originalData = [
                'constants' => [],
                'variables' => [],
            ];
        }
        
        $this->originalConstants = $originalData['constants'];
        $this->originalVariables = $originalData['variables'];
    }
    
    /**
     * Process a script so it outputs a JSON hash of its constants and variables
     * @param string $data Source code of the script, including <?php tag
     * @return string Modified script
     */
    protected function processScriptDataForOutput($data)
    {
        $preScript  = $this->getResourceData('ScriptOutputPre.php');
        $postScript = $this->getResourceData('ScriptOutputPost.php');
        
        $postScript = $this->removeOpeningPHPTag($postScript);
        $data       = $this->removeOpeningPHPTag($data);
        
        return implode(
            PHP_EOL,
            [$preScript, $data, $postScript]
        );
    }
    
    /**
     * Normalize the way the script is closed: remove ?> tags and add line breaks
     * @param string $data Source code of the script
     * @return string Modified script
     */
    protected function normalizeScriptClosing($data)
    {
        // remove closing PHP tag
        $data = preg_replace('(\?>\s*$)s', '', $data);
        
        // remove trailing whitespace
        $data = preg_replace('(\s*$)s', '', $data);
        
        // add two nice line endings
        $data .= "\n\n";
        
        return $data;
    }
    
    /**
     * Remove the opening <?php or <? tag from a script
     * @param string $data Source code of the script
     * @return string Modified script
     */
    protected function removeOpeningPHPTag($data)
    {
        return preg_replace('(^\s*<\?(php)?\s*)s', '', $data);
    }
    
    /**
     * Get the path to a local resource
     * @param string $name
     * @return string Full file path
     */
    protected function getResourcePath($name)
    {
        $path = realpath(implode(
            DIRECTORY_SEPARATOR,
            [
                __DIR__,
                'Resources',
                $name,
            ]
        ));
        return $path;
    }
    
    /**
     * Get the raw content of a local resource
     * @param string $name
     * @return string Raw data
     */
    protected function getResourceData($name)
    {
        return file_get_contents($this->getResourcePath($name));
    }
    
    /**
     * Get the modified script, where the new variables and constants are set
     * 
     * @return string Source code
     */
    public function getModifiedScript()
    {
        $scriptData = $this->normalizeScriptClosing(
            $this->originalScriptData
        );
        
        // remove any defines
        $scriptData = preg_replace(
            '((?<![_a-zA-Z0-9])define\s*\(.*?\)\s*;\s*)s',
            '',
            $scriptData
        );
        
        foreach ($this->getResultConstants() as $name => $value) {
            $scriptData .= $this->generateDefineStatement($name, $value) . PHP_EOL;
        }
        foreach ($this->getModifiedVariables() as $name => $value) {
            $scriptData .= $this->generateVariableStatement($name, $value) . PHP_EOL;
        }
        
        return $scriptData;
    }
    
    /**
     * Generate a PHP define() statement for a constant
     * 
     * @param string $name Name of the constant
     * @param string $value Value of the constant
     * @return string PHP statement
     */
    protected function generateDefineStatement($name, $value)
    {
        return 'define(' . var_export($name, true) 
            . ', ' . var_export($value, true) . ');';
    }
    
    /**
     * Generate a PHP variable assignmenet statement
     * 
     * @param string $name Name of the variable
     * @param string $value Value of the variable
     * @return string PHP statement
     */
    protected function generateVariableStatement($name, $value)
    {
        return '$' . $name . ' = ' . var_export($value, true) . ';';
    }
    
    /**
     * Get an array of the constants that the file originally defines
     * @return array
     */
    public function getOriginalConstants()
    {
        return $this->originalConstants;
    }
    
    /**
     * Get an array of the constants that we add or modify
     * @return array
     */
    public function getModifiedConstants()
    {
        return $this->modifiedConstants;
    }
    
    /**
     * Set constants to modify, overwriting current setting
     * @param array $constants
     * @return PhpSettingsEditor
     */
    public function setModifiedConstants(array $constants)
    {
        $this->modifiedConstants = $constants;
        return $this;
    }
    
    /**
     * Add constants to modify, merging with current setting
     * @param array $constants
     * @return PhpSettingsEditor
     */
    public function addModifiedConstants(array $constants)
    {
        return $this->setModifiedConstants(
            array_merge($this->modifiedConstants, $constants)
        );
    }
    
    /**
     * Get an array of the constants that the modified script defines
     * @return array
     */
    public function getResultConstants()
    {
        return array_merge($this->originalConstants, $this->modifiedConstants);
    }
    
    /**
     * Get an array of the variables that the file originally defines
     * @return array
     */
    public function getOriginalVariables()
    {
        return $this->originalVariables;
    }
    
    /**
     * Get an array of the variables that we add or modify
     * @return array
     */
    public function getModifiedVariables()
    {
        return $this->modifiedVariables;
    }
    
    /**
     * Set variables to modify, overwriting current setting
     * @param array $variables
     * @return PhpSettingsEditor
     */
    public function setModifiedVariables(array $variables)
    {
        $this->modifiedVariables = $variables;
        return $this;
    }
    
    /**
     * Add variables to modify, merging with current setting
     * @param array $variables
     * @return PhpSettingsEditor
     */
    public function addModifiedVariables(array $variables)
    {
        return $this->setModifiedVariables(
            array_merge($this->modifiedVariables, $variables)
        );
    }
    
    /**
     * Get an array of the variables that the modified script defines
     * @return array
     */
    public function getResultVariables()
    {
        return array_merge($this->originalVariables, $this->modifiedVariables);
    }
}