<?php
namespace Devbot\Core\Settings;

use Symfony\Component\Process\PhpProcess;

class PhpSettingsEditor
{
    protected $originalScriptData;
    
    protected $originalConstants;
    protected $modifiedConstants;
    protected $originalVariables;
    protected $modifiedVariables;
    
    public function __construct($scriptData)
    {
        $this->modifiedConstants = [];
        $this->modifiedVariables = [];
        $this->importScriptData($scriptData);
    }
    
    protected function importScriptData($data)
    {
        $this->originalScriptData = $data;
        
        $normalizedScriptData = $this->normalizeScriptData(
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
    
    protected function normalizeScriptData($data)
    {
        // remove closing PHP tag
        $data = preg_replace('(\?>\s*$)s', '', $data);
        
        // remove trailing whitespace
        $data = preg_replace('(\s*$)s', '', $data);
        
        // add two nice line endings
        $data .= "\n\n";
        
        return $data;
    }
    
    protected function removeOpeningPHPTag($script)
    {
        return preg_replace('(^\s*<\?(php)?\s*)s', '', $script);
    }
    
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
    
    protected function getResourceData($name)
    {
        return file_get_contents($this->getResourcePath($name));
    }
    
    public function getModifiedScript()
    {
        $scriptData = $this->normalizeScriptData(
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
    
    protected function generateDefineStatement($name, $value)
    {
        return 'define(' . var_export($name, true) 
            . ', ' . var_export($value, true) . ');';
    }
    
    protected function generateVariableStatement($name, $value)
    {
        return '$' . $name . ' = ' . var_export($value, true) . ';';
    }
    
    public function getOriginalConstants()
    {
        return $this->originalConstants;
    }
    
    public function getModifiedConstants()
    {
        return $this->modifiedConstants;
    }
    
    public function setModifiedConstants(array $constants)
    {
        $this->modifiedConstants = $constants;
        return $this;
    }
    
    public function addModifiedConstants(array $constants)
    {
        return $this->setModifiedConstants(
            array_merge($this->modifiedConstants, $constants)
        );
    }
    
    public function getResultConstants()
    {
        return array_merge($this->originalConstants, $this->modifiedConstants);
    }
    
    public function getOriginalVariables()
    {
        return $this->originalVariables;
    }
    
    public function getModifiedVariables()
    {
        return $this->modifiedVariables;
    }
    
    public function setModifiedVariables(array $variables)
    {
        $this->modifiedVariables = $variables;
        return $this;
    }
    
    public function addModifiedVariables(array $variables)
    {
        return $this->setModifiedVariables(
            array_merge($this->modifiedVariables, $variables)
        );
    }
    
    public function getResultVariables()
    {
        return array_merge($this->originalVariables, $this->modifiedVariables);
    }
}