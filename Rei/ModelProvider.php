<?php

/**
 * @see Zend_Tool_Project_Provider_Abstract
 */
require_once 'Zend/Tool/Project/Provider/Abstract.php';

/**
 * @see Rei_ModelFileContext
 */
require_once 'Rei/ModelFileContext.php';

class Rei_ModelProvider extends Zend_Tool_Project_Provider_Abstract
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return 'Model';
    }

    /**
     * Create Resource
     *
     * @param  Zend_Tool_Project_Profile $profile
     * @param  string                    $modelName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $modelName)
    {
        if (!is_string($modelName)) {
            require_once 'Zend/Tool/Project/Provider/Exception.php';
            throw new Zend_Tool_Project_Provider_Exception(
                'Rei_ModelProvider::createResource() expects \"modelName\" is the name of a model resource to create.'
            );
        }

        // check to see if a model already exists
        $existingModelFile = $profile->search(array(
            'ModelsDirectory',
            'DbTableDirectory', 
            'modelFile' => array('modelName' => $modelName)
        ));

        if ($existingModelFile !== false) {
            require_once 'Zend/Tool/Project/Provider/Exception.php';
            throw new Zend_Tool_Project_Provider_Exception(
                'A model file named ' . $modelName . ' already exists within the models directory.'
            );
        }

        $modelsDirectoryResource = $profile->search('ModelsDirectory');

        if (($dbTableDirectory = $modelsDirectoryResource->search('DbTableDirectory')) === false) {
            $dbTableDirectory = $modelsDirectoryResource->createResource('DbTableDirectory');
        }

        $newModel = $dbTableDirectory->createResource(
            'ModelFile',                        // what to create
            array('modelName' => $modelName)    // attrs to initiate with
        );

        return $newModel;
    }

    /**
     * This method returns the classes to load as contexts.  Since this provider 
     * is creating 'ModelFile', it will need this context
     *
     * @return array
     */
    public function getContextClasses()
    {
        return array('Rei_ModelFileContext');
    }

    /**
     * This it the method exposed to the Zend_Tool_Framework client.  Once
     * a request is parsed, this method is executed.  As you can see, this
     * method allows for pretendability.
     *
     * @param string $name The name of the model
     */
    public function create($name)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $modelFile = self::createResource($this->_loadedProfile, $name);

        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse()->appendContent(
                'Would create model at ' . $modelFile->getPath()
            );
        } else {
            $this->_registry->getResponse()->appendContent(
                'Creating model at ' . $modelFile->getPath()
            );

            $modelFile->create();
            $this->_storeProfile();
        }
    }
}