<?php

namespace TonicCrm\Tools\JwtApiCredential\Storage;

use Predis\Client as RedisClient;
use Predis\Connection\ConnectionException;

/**
 * Class ApiCredentialsRedis
 * @package TonicCrm\Tools\JwtApiCredential\Storage
 */
class ApiCredentialsRedis extends ApiCredentialsStorage
{
    /** @var RedisClient */
    private $redisClient;
    const API_KEY_PREFIX = "api_key_";
    // default TTL for 10 days
    const API_KEY_TTL = 864000;

    /**
     * ApiCredentialsRedis constructor.
     * @param $storageConfig
     */
    public function __construct($storageConfig)
    {
        parent::__construct($storageConfig);
        $this->redisClient = new RedisClient($this->storageConfig, ["prefix" => self::API_KEY_PREFIX]);
    }

    /**
     *  Gets the credentials IF present on redis
     *  Returns the credentials or NULL on not present
     * @param $apiKey
     * @return mixed
     */
    public function getCredentials($apiKey)
    {
        try {
            if (($result = $this->redisClient->get($apiKey)) == null) {
                return null;
            }
            if (($credentials = json_decode($result, true)) === null) {
                return null;
            }
            return $credentials;
        } catch (ConnectionException $error) {
            return null;
        }
    }

    /**
     *  Save the credentials in redis to avoid going to a second storage system
     * @param $apiKey
     * @param $credentials
     * @return bool
     */
    public function setCredentials($apiKey, $credentials)
    {
        try {
            $redisStr = json_encode($credentials);
            $ttl = empty($this->storageConfig['ttl']['api_jwt_ttl']) ? self::API_KEY_TTL : $this->storageConfig['ttl']['api_jwt_ttl'];
            $this->redisClient->setex($apiKey, $ttl, $redisStr);
        } catch (ConnectionException $error) {
            return false;
        }
    }
}
