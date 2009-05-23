<?php

/**
 * @see Zend_Tool_Project_Context_Filesystem_File
 */
require_once 'Zend/Tool/Project/Context/Filesystem/File.php';

/**
 * @see Zend_CodeGenerator_Php_File
 */
require_once 'Zend/CodeGenerator/Php/File.php';

/**
 * @see Zend_Filter_Word_DashToCamelCase
 */
require_once 'Zend/Filter/Word/DashToCamelCase.php';

class Rei_ModelFileContext extends Zend_Tool_Project_Context_Filesystem_File
{
    protected $_modelName = 'model';

    /**
     * Initialize
     *
     * @return Zend_Tool_Project_Context_Zf_ControllerFile
     */
    public function init()
    {
        $this->_modelName      = $this->_resource->getAttribute('modelName');
        $this->_filesystemName = ucfirst($this->_modelName) . '.php';
        parent::init();
        return $this;
    }

    /**
     * The attributes assigned to any given resource within
     * a project. These aid in searching as well as distinguishing
     * one resource of 'ModelFile' from another.
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'modelName' => $this->_modelName
        );
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'ModelFile';
    }

    /**
     * getContents() will be called at creation time. This could be
     * as simple as you see below or could use Zend_Tool_CodeGenerator
     * for this task.
     *
     * @return string
     */
    public function getContents()
    {
        $filter    = new Zend_Filter_Word_DashToCamelCase();
        $className = $filter->filter($this->_modelName);

        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'fileName' => $this->getPath(),
            'classes'  => array(
                new Zend_CodeGenerator_Php_Class(array(
                    'name'          => $className,
                    'extendedClass' => 'Zend_Db_Table_Abstract',
                    'properties'    => array(array(
                        'name'         => '_name',
                        'visibility'   => 'protected',
                        'defaultValue' => $this->_modelName
                    ))
                ))
            )
        ));

        return $codeGenFile->generate();
    }
}