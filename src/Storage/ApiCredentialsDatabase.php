<?php

namespace TonicCrm\Tools\JwtApiCredential\Storage;

use Doctrine\DBAL\Configuration as DBALConfiguration;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\DriverException;

/**
 * Class ApiCredentialsDatabase
 * @package TonicCrm\Tools\JwtApiCredential\Storage
 */
class ApiCredentialsDatabase extends ApiCredentialsStorage
{

    /**
     * @return DBALConnection
     * @throws DBALException
     */
    private function getDatabaseConnection()
    {
        $config = new DBALConfiguration();
        return DriverManager::getConnection($this->storageConfig, $config);
    }

    /**
     * @param string $sqlQuery
     * @param mixed $sqlData
     * @param int $fetchType
     * @return array|null
     * @throws DBALException
     */
    private function runQuery($sqlQuery, $sqlData = null, $fetchType = \PDO::FETCH_ASSOC)
    {
        $dbalConnection = $this->getDatabaseConnection();
        try {
            $stmt = null;
            if ($sqlData === null) {
                $stmt = $dbalConnection->query($sqlQuery);
            } elseif (is_array($sqlData)) {
                $stmt = $dbalConnection->executeQuery($sqlQuery, $sqlData);
            } else {
                return array();
            }
            $stmt->setFetchMode($fetchType);
            return $stmt->fetchAll($fetchType);
        } catch (DriverException $sqlError) {
            return null;
        }
    }

    /**
     *   Calls the database to get the credentials
     * RETURNS the credentials or NULL on failure
     * @param $apiKey
     * @return mixed
     */
    public function getCredentials($apiKey)
    {
        $sqlQuery = "SELECT * FROM api_credentials where api_key = ?;";
        if (($data = $this->runQuery($sqlQuery, [$apiKey])) === null) {
            return null;
        }
        $credentials = [
            "apikey" => $data[0]["api_key"],
            "secret" => $data[0]["api_secret"],
            "service" => $data[0]["service"],
            "acl" => $data[0]["acl"]
        ];
        return $credentials;
    }

    /**
     * @param $apiKey
     * @param $credentials
     * @return mixed
     */
    public function setCredentials($apiKey, $credentials)
    {
        //TODO: for the moment database storage is only to read and not to write
        return null;
    }
}