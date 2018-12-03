<?php

namespace Tests;

use Exception;
use Codeception\Specify;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GuzzleTestCase
 * @package Tests
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
abstract class GuzzleTestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, Specify;

    const SUCCESS = 200;

    protected $uri;
    
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * Set up the test
     */
    protected function setUp()
    {
        parent::setUp();

        // create our http client (Guzzle)
        $this->client = new GuzzleClient([
            // Base URI is used with relative requests
            'base_uri' => env('BASE_URI')
        ]);

        // Additional authentication and authorization setUp may be required
        // Exemple below is for applications using CAS Authentication
        // \Config::set('cas', ['cas_pretend_user' => 'test']);
    }

    /**
     * Get JSON Array from paginated GET request
     *
     * @param int $page
     * @param int $size
     * @param string $uriSuffix
     *
     * @throws
     *
     * @return array|null
     */
    protected function getJsonArray($page, $size, $uriSuffix = "")
    {
        try {
            $responseInterface = $this->client->request('GET', $this->uri . $uriSuffix, ['query' => ['page' => $page, 'size' => $size]]);
            $content = $responseInterface->getBody()->getContents();
            return json_decode($content)->data;

        } catch (Exception $e) {
            $this->assertEquals("ERROR", "TestCase::getJsonArray Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get JSON Array from paginated GET request using Filters array
     *
     * @param int $page
     * @param int $size
     * @param array $filters
     * @param string $uriSuffix
     * @param string $jsonParam
     *
     * @throws
     *
     * @return array|mixed
     */
    protected function getFilteredJsonArray($page, $size, $filters, $uriSuffix = "", $jsonParam = "data")
    {
        try {
            $data = array();
            $data['page']  = $page ;
            $data['size']  = $size ;
            foreach ($filters as $name => $value) {
                $data['filter[' . $name . ']']  = $value ;
            }
            $responseInterface = $this->client->request('GET', $this->uri . $uriSuffix, ['query' => $data]);
            $content = $responseInterface->getBody()->getContents();

            if(!empty($jsonParam)) {
                return json_decode($content)->$jsonParam;
            } else {
                return json_decode($content, true);
            }


        } catch (Exception $e) {
            $this->assertEquals("ERROR", "TestCase::getFilteredJsonArray Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get JSON Data from response
     *
     * @param string $method
     * @param array options
     * @param string $uriSuffix
     *
     * @throws
     *
     * @return array
     */
    protected function getJsonData($method, $options, $uriSuffix = "")
    {
        try {
            $responseInterface = $this->client->request($method, $this->uri . $uriSuffix, $options);
            $content = $responseInterface->getBody()->getContents();
            return json_decode($content)->data;

        } catch (Exception $e) {
            $this->assertEquals("ERROR", "TestCase::getJsonData Exception: " . $e->getMessage());
        }

        return Array();
    }

    /**
     * Get JSON Data from ResponseInterface Object
     *
     * @param ResponseInterface $responseInterface
     *
     * @throws
     *
     * @return mixed
     */
    protected function getJsonDataFromResponse(ResponseInterface $responseInterface)
    {
        try {
            $content = $responseInterface->getBody()->getContents();
            return json_decode($content)->data;

        } catch (Exception $e) {
            $this->assertEquals("ERROR", "TestCase::getJsonDataFromResponse Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get JSON Object from ResponseInterface Object
     *
     * @param ResponseInterface $responseInterface
     *
     * @throws
     *
     * @return mixed
     */
    protected function getJsonObjectFromResponse(ResponseInterface $responseInterface)
    {
        try {
            $content = $responseInterface->getBody()->getContents();
            return json_decode($content, true);

        } catch (Exception $e) {
            $this->assertEquals("ERROR", "TestCase::getJsonObjectFromResponse Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Delete object from Database using id from ResponseInterface Object
     *
     * @param ResponseInterface $responseInterface
     *
     * @throws
     *
     * @return ResponseInterface|null
     */
    protected function deleteGeneratedId(ResponseInterface $responseInterface)
    {
        try {
            $responseObject = $this->getJsonObjectFromResponse($responseInterface);
            $id = $responseObject['data']["id"];
            return $this->client->request('DELETE', $this->uri . "/$id");

        } catch (Exception $e) {
            $this->assertEquals("ERROR", "TestCase::deleteGeneratedId Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get Psr ResponseInterface from request
     *
     * @param string $method
     * @param array options
     * @param string $uriSuffix
     *
     * @throws
     *
     * @return ResponseInterface|null
     */
    protected function getResponse($method, $options, $uriSuffix = "")
    {
        try {
            return $this->client->request($method, $this->uri . $uriSuffix, $options);

        } catch (Exception $e) {
            $this->assertEquals("ERROR", "TestCase::getResponse Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get Psr Body Contents from response
     *
     * @param string $method
     * @param array options
     * @param string $uriSuffix
     *
     * @throws
     *
     * @return string|null
     */
    protected function getContents($method, $options, $uriSuffix = "")
    {
        try {
            $responseInterface = $this->client->request($method, $this->uri . $uriSuffix, $options);
            return $responseInterface->getBody()->getContents();

        } catch (Exception $e) {
            $this->assertEquals("ERROR", "TestCase::getContents Exception: " . $e->getMessage());
        }

        return null;
    }

}


