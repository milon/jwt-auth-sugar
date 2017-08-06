<?php

namespace TonicCrm\Tools\JwtApiCredential;

use TonicCrm\Tools\JwtApiCredential\Processing\JwtProcessing;
use TonicCrm\Tools\JwtApiCredential\Storage\ApiCredentialsDatabase;
use TonicCrm\Tools\JwtApiCredential\Storage\ApiCredentialsRedis;
use Firebase\JWT\JWT;

/**
 * Class JwtApiCredential
 * @package TonicCrm\Tools\JwtApiCredential
 */
class JwtApiCredential implements JwtApiCredentialsInterface
{
    /** @var  ApiCredentialsRedis */
    private $redisStorage;
    /** @var  ApiCredentialsDatabase */
    private $databaseStorage;
    /** @var  JwtProcessing */
    private $jwtProcessing;
    /** @var  string */
    private $jwt;

    /**
     * JwtApiCredential constructor.
     * @param $jwt
     * @param $config
     * @throws \Exception
     */
    public function __construct($jwt, $config)
    {
        if (!isset($config["redis"]) || !isset($config["database"])) {
            throw new \Exception("Storage configuration missing");
        }
        $this->redisStorage = new ApiCredentialsRedis($config["redis"]);
        $this->databaseStorage = new ApiCredentialsDatabase($config["database"]);
        $this->jwtProcessing = new JwtProcessing($jwt);
        $this->jwt = $jwt;
    }

    /**
     * Checks if the JWT is a generic api-key:api-secret client payload token
     * @return bool
     */
    public function isApiCredential()
    {
        $payload = $this->jwtProcessing->getUnverifiedPayload();
        return ($payload !== null && isset($payload->apikey));
    }

    /**
     *    Does what the name suggest
     * @param $credentials
     * @param $service
     * @return bool
     */
    protected function isValidCredentialsForService($credentials, $service)
    {
        return (isset($credentials["service"]) && $credentials["service"] == $service);
    }

    /**
     *    Checks the JWT signature with our KITE logic
     *  LOGIC:
     *          api key grants permission for a particular service
     *          JWT needs to be signed with api-secret
     * @param $service
     * @param $credentials
     * @return mixed
     */
    protected function getValidJwtPayload($service, $credentials)
    {
        if (!$this->isValidCredentialsForService($credentials, $service)) {
            return null;
        }
        //This means the api key grants permission for the service
        //Now we just have to validate the token with the secret
        if (($verifiedPayload = $this->jwtProcessing->getVerifiedPayLoad($credentials["secret"])) === null) {
            return null;
        }
        return $verifiedPayload;
    }

    /**
     * Gets the verified payload of an JWT api-key:api-secret request
     * @param $service
     * @return mixed
     */
    public function getVerifiedPayload($service)
    {
        $jwtPayLoad = $this->jwtProcessing->getUnverifiedPayload();
        //Now we try to get the credentials from redis
        $credentials = $this->redisStorage->getCredentials($jwtPayLoad->apikey);
        if ($credentials !== null) {
            return $this->getValidJwtPayload($service, $credentials);
        }
        //Stuff is not on redis so we fallback to database
        if (($credentials = $this->databaseStorage->getCredentials($jwtPayLoad->apikey)) === null) {
            return null;
        }
        //Finally we validate the signature
        $verifiedPayload = $this->getValidJwtPayload($service, $credentials);
        if ($verifiedPayload !== null) {
            //Save the data ONLY once the signature is verified
            $this->redisStorage->setCredentials($jwtPayLoad->apikey, $credentials);
        }
        return $verifiedPayload;
    }

    /**
     *  Generates a jwt that follows our JWT authentication methods
     * @param array $credentials
     * @param array $additionalData
     * @return string
     */
    public function generateJwt($credentials, $additionalData = null)
    {
        $payload = [
            "iss" => "TONICCORE:JwtApiCredentialGenerator",
            "aud" => $credentials["service"],
            "apikey" => $credentials["apikey"],
            "iat" => time()
        ];
        if ($additionalData !== null) {
            $payload["data"] = $additionalData;
        }
        return JWT::encode($payload, $credentials["secret"]);
    }
}
