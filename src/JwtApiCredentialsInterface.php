<?php

namespace TonicCrm\Tools\JwtApiCredential;

/**
 * Interface JwtApiCredentialsInterface
 * @package TonicCrm\Tools\JwtApiCredential
 */
interface JwtApiCredentialsInterface {
  /**
   * @return boolean
   */
  public function isApiCredential();

  /**
   * @param $service
   * @return mixed
   */
  public function getVerifiedPayload($service);

  /**
   * @param $credentials
   * @param $additionalData
   * @return mixed
   */
  public function generateJwt($credentials, $additionalData = null);

}
