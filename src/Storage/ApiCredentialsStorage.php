<?php

namespace TonicCrm\Tools\JwtApiCredential\Storage;

/**
 * Class ApiCredentialsStorage
 * @package TonicCrm\Tools\JwtApiCredential\Storage
 */
abstract class ApiCredentialsStorage
{
    /** @var  mixed */
    protected $storageConfig;

    /**
     * ApiCredentialsStorage constructor.
     * @param $storageConfig
     */
    public function __construct($storageConfig)
    {
        $this->storageConfig = $storageConfig;
    }

    abstract function getCredentials($apiKey);

    abstract function setCredentials($apiKey, $credentials);
}
