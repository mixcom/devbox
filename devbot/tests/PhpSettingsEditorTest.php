<?php
use Devbot\Core\Settings\PhpSettingsEditor;
use Devbot\Core\Settings\PhpExpression;

class PhpSettingsEditorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider readSettingsDataProvider
     */
    public function testReadsConstants(
        $data, 
        array $constants, 
        array $variables
    ) {
        $editor = new PhpSettingsEditor($data);
        $this->assertEquals($constants, $editor->getOriginalConstants());
    }
    
    /**
     * @dataProvider readSettingsDataProvider
     */
    public function testReadsVariables(
        $data, 
        array $constants, 
        array $variables
    ) {
        $editor = new PhpSettingsEditor($data);
        $this->assertEquals($variables, $editor->getOriginalVariables());
    }
    
    /**
     * @dataProvider changeSettingsDataProvider
     */
    public function testChangesConstantsData(
        $data, 
        array $constantModifications,
        array $variableModifications,
        array $modifiedConstants, 
        array $modifiedVariables
    ) {
        $editor = new PhpSettingsEditor($data);
        $editor->setModifiedConstants($constantModifications);
        
        $this->assertEquals($modifiedConstants, $editor->getResultConstants());
    }
    
    /**
     * @dataProvider changeSettingsDataProvider
     */
    public function testChangesVariablesData(
        $data, 
        array $constantModifications,
        array $variableModifications,
        array $modifiedConstants, 
        array $modifiedVariables
    ) {
        $editor = new PhpSettingsEditor($data);
        $editor->setModifiedVariables($variableModifications);
        
        $this->assertEquals($modifiedVariables, $editor->getResultVariables());
    }
    
    /**
     * @dataProvider changeSettingsDataProvider
     */
    public function testChangesConstants(
        $data, 
        array $constantModifications,
        array $variableModifications,
        array $modifiedConstants, 
        array $modifiedVariables
    ) {
        $editor = new PhpSettingsEditor($data);
        $editor->setModifiedConstants($constantModifications);
        
        $modified = $editor->getModifiedScript();
        
        $editor = new PhpSettingsEditor($modified);
        $this->assertEquals($modifiedConstants, $editor->getOriginalConstants());
    }
    
    /**
     * @dataProvider changeSettingsDataProvider
     */
    public function testChangesVariables(
        $data, 
        array $constantModifications,
        array $variableModifications,
        array $modifiedConstants, 
        array $modifiedVariables
    ) {
        $editor = new PhpSettingsEditor($data);
        $editor->setModifiedVariables($variableModifications);
        
        $modified = $editor->getModifiedScript();
        
        $editor = new PhpSettingsEditor($modified);
        $this->assertEquals($modifiedVariables, $editor->getOriginalVariables());
    }
    
    /**
     * @dataProvider changeSettingsDataProvider
     */
    public function testChangesConstantsTwice(
        $data, 
        array $constantModifications,
        array $variableModifications,
        array $modifiedConstants, 
        array $modifiedVariables
    ) {
        $testModifications = [
            'TWICE_NEW_CONSTANT' => 123,
        ];
        
        // make our own modifications
        $editor = new PhpSettingsEditor($data);
        $editor->setModifiedConstants($testModifications);
        
        $modified = $editor->getModifiedScript();
        
        // then make some more modifications
        $editor = new PhpSettingsEditor($modified);
        $editor->setModifiedConstants($constantModifications);
        
        $modifiedTwice = $editor->getModifiedScript();
        
        // assert that both sets of modifications were applied
        $editor = new PhpSettingsEditor($modifiedTwice);
        $this->assertEquals(
            array_merge($testModifications, $modifiedConstants),
            $editor->getOriginalConstants()
        );
    }
    
    /**
     * @dataProvider changeSettingsDataProvider
     */
    public function testChangesVariablesTwice(
        $data, 
        array $constantModifications,
        array $variableModifications,
        array $modifiedConstants, 
        array $modifiedVariables
    ) {
        $testModifications = [
            'twiceVariable' => 123,
        ];
        
        // make our own modifications
        $editor = new PhpSettingsEditor($data);
        $editor->setModifiedVariables($testModifications);
        
        $modified = $editor->getModifiedScript();
        
        // then make some more modifications
        $editor = new PhpSettingsEditor($modified);
        $editor->setModifiedVariables($modifiedVariables);
        
        $modifiedTwice = $editor->getModifiedScript();
        
        // assert that both sets of modifications were applied
        $editor = new PhpSettingsEditor($modifiedTwice);
        $this->assertEquals(
            array_merge($testModifications, $modifiedVariables),
            $editor->getOriginalVariables()
        );
    }
    
    public function testReadsEmptyEditor() {
        $editor = new PhpSettingsEditor();
        
        $this->assertEquals([], $editor->getOriginalConstants());
        $this->assertEquals([], $editor->getOriginalVariables());
    }
    
    /**
     * @dataProvider changeSettingsDataProvider
     */
    public function testChangesEmptyEditorConstants(
        $data,
        array $constants,
        array $variables
    ) {
        $editor = new PhpSettingsEditor();
        $editor->setModifiedConstants($constants);
        
        $modified = $editor->getModifiedScript();
        
        $editor = new PhpSettingsEditor($modified);
        
        $this->assertEquals($constants, $editor->getOriginalConstants());
    }
    
    /**
     * @dataProvider changeSettingsDataProvider
     */
    public function testChangesEmptyEditorVariables(
        $data,
        array $constants,
        array $variables
    ) {
        $editor = new PhpSettingsEditor();
        $editor->setModifiedVariables($variables);
        
        $modified = $editor->getModifiedScript();
        
        $editor = new PhpSettingsEditor($modified);
        
        $this->assertEquals($variables, $editor->getOriginalVariables());
    }
    
    public function readSettingsDataProvider()
    {
        $testData = [];
        $testData[] = [
            $this->readSettingsFile('test1'),
            [
                'SITE_ROOT' => 'aap',
                'SCHAAP' => 'test',
            ],
            [
                'blaat' => [
                    'a' => 'aa',
                    'b' => 'bb',
                    'c' => [
                        'c', 'cc', 'ccc'
                    ]
                ],
                'base_url' => 'http://test',
            ]
        ];
        $testData[] = [
            $this->readSettingsFile('test2'),
            [
                'testConstant1' => 'constant1',
                'testConstant2' => 'constant2',
            ],
            [
                'testVariable1' => 1,
                'testVariable2' => 2,
                'testVariable3' => 3,
            ]
        ];
        return $testData;
    }
    
    public function changeSettingsDataProvider()
    {
        $testData = [];
        
        $modifications = [];
        $modifications[] = [
            [
                'NEW_CONSTANT' => true,
                'SCHAAP' => 'modified',
            ],
            [
                '__newVar' => [1, 2, 3],
                'base_url' => 'changed',
            ],
        ];
        
        foreach ($this->readSettingsDataProvider() as $testDataItem) {
            foreach ($modifications as $modification) {
                list ($constantMods, $variableMods) = $modification;
                
                $testData[] = [
                    $testDataItem[0],
                    $constantMods,
                    $variableMods,
                    array_merge($testDataItem[1], $constantMods),
                    array_merge($testDataItem[2], $variableMods),
                ];
            }
        }
        
        return $testData;
    }
    
    public function createSettingsDataProvider()
    {
        $testData = [];
        
        $modifications = [];
        $modifications[] = [
            [
                'NEW_CONSTANT' => true,
                'SCHAAP' => 'modified',
            ],
            [
                '__newVar' => [1, 2, 3],
                'base_url' => 'changed',
            ],
        ];
        
        foreach ($this->readSettingsDataProvider() as $testDataItem) {
            foreach ($modifications as $modification) {
                list ($constantMods, $variableMods) = $modification;
                
                $testData[] = [
                    $testDataItem[0],
                    $constantMods,
                    $variableMods,
                    array_merge($testDataItem[1], $constantMods),
                    array_merge($testDataItem[2], $variableMods),
                ];
            }
        }
        
        return $testData;
    }
    
    protected function readSettingsFile($name)
    {
        $path = __DIR__ . '/resources/PhpSettingsEditorTest/' . $name . '.php';
        return file_get_contents($path);
    }
}