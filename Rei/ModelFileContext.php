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
 * @see Zend_Filter_Word_DashToUnderscore
 */
require_once 'Zend/Filter/Word/DashToUnderscore.php';

class Rei_ModelFileContext extends Zend_Tool_Project_Context_Filesystem_File
{
    protected $_modelName = 'model';

    protected $_filesystemName = 'model.php';

    /**
     * Initialize
     *
     * @return Zend_Tool_Project_Context_Zf_ControllerFile
     */
    public function init()
    {
        parent::init();
        $this->_initTableNames();
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
     * Parsing and setting file and model name
     *
     * @return null
     */
    protected function _initTableNames()
    {
        $filter    = new Zend_Filter_Word_DashToUnderscore();
        $modelName = $filter->filter($this->_resource->getAttribute('modelName'));

        $wordArray = explode('_', $modelName);

        for ($i=0; $i<count($wordArray); $i++) {
            $wordArray[$i] = ucwords($wordArray[$i]);
        }

        $this->_modelName = implode('_', $wordArray);

        if (count($wordArray) > 1) {
            $pathInfo = pathinfo($this->getPath());
            $dirname  = $pathInfo['dirname'];
            $basename = $pathInfo['basename'];

            for ($i=0; $i<(count($wordArray)-1); $i++) {
                $dirname .= '/' . $wordArray[$i];

                if (!file_exists($dirname)) {
                    mkdir($dirname);
                }
            }

            $this->setBaseDirectory($dirname);

            $fileName = $wordArray[count($wordArray)-1];
        } else {
            $fileName = $this->_modelName;
        }

        $this->setFilesystemName($fileName . '.php');
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
        $className = 'Model_DbTable_' . $this->_modelName;

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