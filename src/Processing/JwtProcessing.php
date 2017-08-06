<?php

namespace TonicCrm\Tools\JwtApiCredential\Processing;

use Firebase\JWT\JWT;

/**
 * Class JwtProcessing
 * @package Ekhanei\Tools\JwtApiCredential\Processing
 */
class JwtProcessing
{
    /** @var  string */
    protected $jwt;

    /**
     * JwtProcessing constructor.
     * @param $jwt
     */
    public function __construct($jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     *  Gets the unverified payload of the the JWT if the value is actually a JWT
     * @return mixed
     */
    public function getUnverifiedPayload()
    {
        if (strstr($this->jwt, ".") === false) {
            return null;
        }

        $base64Parts = explode(".", $this->jwt);
        if (count($base64Parts) != 3 && empty($base64Parts[1])) {
            return null;
        }

        try {
            $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($base64Parts[1]));
        } catch (\Exception $error) {
            return null;
        }

        if ($payload === null) {
            return null;
        }
        return $payload;
    }

    /**
     * Get a verified by using the secret
     * @param     $secretKey
     * @param int $leewaySeconds
     * @return array
     */
    public function getVerifiedPayLoad($secretKey, $leewaySeconds = 60000000)
    {
        try {
            JWT::$leeway = $leewaySeconds; // $leeway in seconds
            $payload = JWT::decode($this->jwt, $secretKey, array('HS256'));
            JWT::$leeway = 60;
            return $payload;
        } catch (\UnexpectedValueException $e) {
            return null;
        }
    }
}
