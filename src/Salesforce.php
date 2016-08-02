<?php

namespace anewmanjones\laravelSalesforceREST;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use anewmanjones\laravelSalesforceREST\Exceptions\SalesforceException;

class Salesforce
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $url;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $instanceUrl;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $issuedAt;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var array
     */
    private $version = [
        'label'   => 'Summer 16',
        'url'     => '/services/data/v37.0',
        'version' => '37.0'
    ];

    /**
     * Salesforce constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->client = new Client();

        $this->login();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return mixed
     */
    private function sendRequest($method, $url, array $options = [])
    {
        $defaultOptions = [
            'headers' => [
                'Authorization'   => 'Bearer ' . $this->accessToken,
                'X-PrettyPrint'   => '1',
                'Accept'          => 'application/json',
                'Accept-Encoding' => 'gzip'
            ]
        ];

        $requestOptions = array_merge($defaultOptions, $options);

        $response = $this->client->request($method, $url, $requestOptions);

        $this->checkResponse($response);

        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }

    /**
     * @param ResponseInterface $response
     */
    private function checkResponse(ResponseInterface $response)
    {
        $limit = $response->getHeader('Sforce-Limit-Info');

        \GuzzleHttp\json_decode($response->getBody()->getContents());
    }

    /**
     *
     */
    public function login()
    {
        $body = [
            'grant_type'    => 'password',
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'username'      => $this->config['username'],
            'password'      => $this->config['password']
        ];

        $response = $this->client->post('https://login.salesforce.com/services/oauth2/token', [
            'form_params' => $body
        ])->getBody()->getContents();

        $responseObject = \GuzzleHttp\json_decode($response);

        $this->id = $responseObject->id;
        $this->issuedAt = $responseObject->issued_at;
        $this->signature = $responseObject->signature;
        $this->accessToken = $responseObject->access_token;
        $this->instanceUrl = $responseObject->instance_url;
        $this->url = $responseObject->instance_url . $this->version['url'];
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->sendRequest('GET', $this->instanceUrl . '/services/data');
    }

    /**
     * @return mixed
     */
    public function listOrganisationLimits()
    {
        return $this->sendRequest('GET', $this->instanceUrl . $this->version['url'] . '/limits');
    }

    /**
     * @return mixed
     */
    public function listAvailableResources()
    {
        return $this->sendRequest('GET', $this->url);
    }

    /**
     * @return mixed
     */
    public function listObjects()
    {
        return $this->sendRequest('GET', $this->url . '/sobjects');
    }

    /**
     * @param string $type
     * @param string $id
     * @param array $fields
     * @return miSalesforceExceptionxed
     * @throws
     */
    public function getRecord($type, $id, array $fields = [])
    {
        $response = $this->sendRequest('GET', $this->url . "/sobjects/$type/$id", ['query' => $fields]);

        if($response->success === false) {
            throw new SalesforceException($response->errors);
        }

        return $response;
    }

    /**
     * @param string $type
     * @param array $data
     * @return mixed
     * @throws SalesforceException
     */
    public function createRecord($type, array $data)
    {
        $response = $this->sendRequest('POST', $this->url . "/sobjects/$type", [
            'json' => $data
        ]);

        if($response->success === false) {
            throw new SalesforceException($response->errors);
        }

        return $response;
    }

    /**
     * @param string $type
     * @param string $id
     * @return mixed
     * @throws SalesforceException
     */
    public function updateRecord($type, $id)
    {
        $response = $this->sendRequest('PATCH', $this->url . "/sobjects/$type/$id");

        if($response->success === false) {
            throw new SalesforceException($response->errors);
        }

        return $response;
    }

    /**
     * @param string $type
     * @param string $id
     * @return mixed
     * @throws SalesforceException
     */
    public function deleteRecord($type, $id)
    {
        $response = $this->sendRequest('DELETE', $this->url . "/sobjects/$type/$id");

        if($response->success === false) {
            throw new SalesforceException($response->errors);
        }

        return $response;
    }
}