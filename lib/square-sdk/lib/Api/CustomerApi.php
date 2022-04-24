<?php
/**
 * CustomerApi
 * PHP version 5
 *
 * @category Class
 * @package  SquareConnect
 * @author   http://github.com/swagger-api/swagger-codegen
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link     https://github.com/swagger-api/swagger-codegen
 */
/**
 *  Copyright 2016 Square, Inc.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program. 
 * https://github.com/swagger-api/swagger-codegen 
 * Do not edit the class manually.
 */

namespace SquareConnect\Api;

use \SquareConnect\Configuration;
use \SquareConnect\ApiClient;
use \SquareConnect\ApiException;
use \SquareConnect\ObjectSerializer;

/**
 * CustomerApi Class Doc Comment
 *
 * @category Class
 * @package  SquareConnect
 * @author   http://github.com/swagger-api/swagger-codegen
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class CustomerApi
{

    /**
     * API Client
     * @var \SquareConnect\ApiClient instance of the ApiClient
     */
    protected $apiClient;
  
    /**
     * Constructor
     * @param \SquareConnect\ApiClient|null $apiClient The api client to use
     */
    function __construct($apiClient = null)
    {
        if ($apiClient == null) {
            $apiClient = new ApiClient();
            $apiClient->getConfig()->setHost('https://connect.squareup.com');
        }
  
        $this->apiClient = $apiClient;
    }
  
    /**
     * Get API client
     * @return \SquareConnect\ApiClient get the API client
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }
  
    /**
     * Set the API client
     * @param \SquareConnect\ApiClient $apiClient set the API client
     * @return CustomerApi
     */
    public function setApiClient(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
        return $this;
    }
  
    /**
     * createCustomer
     *
     * CreateCustomer
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param \SquareConnect\Model\CreateCustomerRequest $body An object containing the fields to POST for the request.  See the corresponding object definition for field details. (required)
     * @return \SquareConnect\Model\CreateCustomerResponse
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    public function createCustomer($authorization, $body)
    {
        list($response, $statusCode, $httpHeader) = $this->createCustomerWithHttpInfo ($authorization, $body);
        return $response; 
    }


    /**
     * createCustomerWithHttpInfo
     *
     * CreateCustomer
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param \SquareConnect\Model\CreateCustomerRequest $body An object containing the fields to POST for the request.  See the corresponding object definition for field details. (required)
     * @return Array of \SquareConnect\Model\CreateCustomerResponse, HTTP status code, HTTP response headers (array of strings)
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    public function createCustomerWithHttpInfo($authorization, $body)
    {
        
        // verify the required parameter 'authorization' is set
        if ($authorization === null) {
            throw new \InvalidArgumentException('Missing the required parameter $authorization when calling createCustomer');
        }
        // verify the required parameter 'body' is set
        if ($body === null) {
            throw new \InvalidArgumentException('Missing the required parameter $body when calling createCustomer');
        }
  
        // parse inputs
        $resourcePath = "/v2/customers";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array('application/json'));
  
        
        // header params
        if ($authorization !== null) {
            $headerParams['Authorization'] = $this->apiClient->getSerializer()->toHeaderValue($authorization);
        }
        
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // body params
        $_tempBody = null;
        if (isset($body)) {
            $_tempBody = $body;
        }
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
                // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, 'POST',
                $queryParams, $httpBody,
                $headerParams, '\SquareConnect\Model\CreateCustomerResponse'
            );
            if (!$response) {
                return array(null, $statusCode, $httpHeader);
            }

            return array(\SquareConnect\ObjectSerializer::deserialize($response, '\SquareConnect\Model\CreateCustomerResponse', $httpHeader), $statusCode, $httpHeader);
                    } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = \SquareConnect\ObjectSerializer::deserialize($e->getResponseBody(), '\SquareConnect\Model\CreateCustomerResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
    }
    /**
     * deleteCustomer
     *
     * DeleteCustomer
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param string $customer_id The ID of the customer to delete. (required)
     * @return \SquareConnect\Model\DeleteCustomerResponse
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    public function deleteCustomer($authorization, $customer_id)
    {
        list($response, $statusCode, $httpHeader) = $this->deleteCustomerWithHttpInfo ($authorization, $customer_id);
        return $response; 
    }


    /**
     * deleteCustomerWithHttpInfo
     *
     * DeleteCustomer
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param string $customer_id The ID of the customer to delete. (required)
     * @return Array of \SquareConnect\Model\DeleteCustomerResponse, HTTP status code, HTTP response headers (array of strings)
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    public function deleteCustomerWithHttpInfo($authorization, $customer_id)
    {
        
        // verify the required parameter 'authorization' is set
        if ($authorization === null) {
            throw new \InvalidArgumentException('Missing the required parameter $authorization when calling deleteCustomer');
        }
        // verify the required parameter 'customer_id' is set
        if ($customer_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $customer_id when calling deleteCustomer');
        }
  
        // parse inputs
        $resourcePath = "/v2/customers/{customer_id}";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array('application/json'));
  
        
        // header params
        if ($authorization !== null) {
            $headerParams['Authorization'] = $this->apiClient->getSerializer()->toHeaderValue($authorization);
        }
        // path params
        if ($customer_id !== null) {
            $resourcePath = str_replace(
                "{" . "customer_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($customer_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
                // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, 'DELETE',
                $queryParams, $httpBody,
                $headerParams, '\SquareConnect\Model\DeleteCustomerResponse'
            );
            if (!$response) {
                return array(null, $statusCode, $httpHeader);
            }

            return array(\SquareConnect\ObjectSerializer::deserialize($response, '\SquareConnect\Model\DeleteCustomerResponse', $httpHeader), $statusCode, $httpHeader);
                    } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = \SquareConnect\ObjectSerializer::deserialize($e->getResponseBody(), '\SquareConnect\Model\DeleteCustomerResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
    }
    /**
     * listCustomers
     *
     * ListCustomers
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param string $cursor A pagination cursor returned by a previous call to this endpoint. Provide this to retrieve the next set of results for your original query.  See [Paginating results](#paginatingresults) for more information. (optional)
     * @return \SquareConnect\Model\ListCustomersResponse
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    public function listCustomers($authorization, $cursor = null)
    {
        list($response, $statusCode, $httpHeader) = $this->listCustomersWithHttpInfo ($authorization, $cursor);
        return $response; 
    }


    /**
     * listCustomersWithHttpInfo
     *
     * ListCustomers
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param string $cursor A pagination cursor returned by a previous call to this endpoint. Provide this to retrieve the next set of results for your original query.  See [Paginating results](#paginatingresults) for more information. (optional)
     * @return Array of \SquareConnect\Model\ListCustomersResponse, HTTP status code, HTTP response headers (array of strings)
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    public function listCustomersWithHttpInfo($authorization, $cursor = null)
    {
        
        // verify the required parameter 'authorization' is set
        if ($authorization === null) {
            throw new \InvalidArgumentException('Missing the required parameter $authorization when calling listCustomers');
        }
  
        // parse inputs
        $resourcePath = "/v2/customers";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array('application/json'));
  
        // query params
        if ($cursor !== null) {
            $queryParams['cursor'] = $this->apiClient->getSerializer()->toQueryValue($cursor);
        }
        // header params
        if ($authorization !== null) {
            $headerParams['Authorization'] = $this->apiClient->getSerializer()->toHeaderValue($authorization);
        }
        
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
                // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, 'GET',
                $queryParams, $httpBody,
                $headerParams, '\SquareConnect\Model\ListCustomersResponse'
            );
            if (!$response) {
                return array(null, $statusCode, $httpHeader);
            }

            return array(\SquareConnect\ObjectSerializer::deserialize($response, '\SquareConnect\Model\ListCustomersResponse', $httpHeader), $statusCode, $httpHeader);
                    } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = \SquareConnect\ObjectSerializer::deserialize($e->getResponseBody(), '\SquareConnect\Model\ListCustomersResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
    }
    /**
     * retrieveCustomer
     *
     * RetrieveCustomer
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param string $customer_id The ID of the customer to retrieve. (required)
     * @return \SquareConnect\Model\RetrieveCustomerResponse
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    /*public function retrieveCustomer($authorization, $customer_id)
    {
        list($response, $statusCode, $httpHeader) = $this->retrieveCustomerWithHttpInfo ($authorization, $customer_id);
        return $response; 
    }*/
    public function retrieveCustomer($customer_id)
    {
        list($response, $statusCode, $httpHeader) = $this->retrieveCustomerWithHttpInfo ($customer_id);
        return $response; 
    }


    /**
     * retrieveCustomerWithHttpInfo
     *
     * RetrieveCustomer
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param string $customer_id The ID of the customer to retrieve. (required)
     * @return Array of \SquareConnect\Model\RetrieveCustomerResponse, HTTP status code, HTTP response headers (array of strings)
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    /*public function retrieveCustomerWithHttpInfo($authorization, $customer_id)
    {
        
        // verify the required parameter 'authorization' is set
        if ($authorization === null) {
            throw new \InvalidArgumentException('Missing the required parameter $authorization when calling retrieveCustomer');
        }
        // verify the required parameter 'customer_id' is set
        if ($customer_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $customer_id when calling retrieveCustomer');
        }
  
        // parse inputs
        $resourcePath = "/v2/customers/{customer_id}";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array('application/json'));
  
        
        // header params
        if ($authorization !== null) {
            $headerParams['Authorization'] = $this->apiClient->getSerializer()->toHeaderValue($authorization);
        }
        // path params
        if ($customer_id !== null) {
            $resourcePath = str_replace(
                "{" . "customer_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($customer_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
                // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, 'GET',
                $queryParams, $httpBody,
                $headerParams, '\SquareConnect\Model\RetrieveCustomerResponse'
            );
            if (!$response) {
                return array(null, $statusCode, $httpHeader);
            }

            return array(\SquareConnect\ObjectSerializer::deserialize($response, '\SquareConnect\Model\RetrieveCustomerResponse', $httpHeader), $statusCode, $httpHeader);
                    } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = \SquareConnect\ObjectSerializer::deserialize($e->getResponseBody(), '\SquareConnect\Model\RetrieveCustomerResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
    }*/
    public function retrieveCustomerWithHttpInfo($customer_id)
    {
        
        // verify the required parameter 'customer_id' is set
        if ($customer_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $customer_id when calling retrieveCustomer');
        }
  
        // parse inputs
        $resourcePath = "/v2/customers/{customer_id}";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array('application/json'));
        $headerParams['Square-Version'] = "2020-02-26";

        
        
        // path params
        if ($customer_id !== null) {
            $resourcePath = str_replace(
                "{" . "customer_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($customer_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
        
        // this endpoint requires OAuth (access token)
        if (strlen($this->apiClient->getConfig()->getAccessToken()) !== 0) {
            $headerParams['Authorization'] = 'Bearer ' . $this->apiClient->getConfig()->getAccessToken();
        }
        // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, 'GET',
                $queryParams, $httpBody,
                $headerParams, '\SquareConnect\Model\RetrieveCustomerResponse'
            );
            if (!$response) {
                return array(null, $statusCode, $httpHeader);
            }

            return array(\SquareConnect\ObjectSerializer::deserialize($response, '\SquareConnect\Model\RetrieveCustomerResponse', $httpHeader), $statusCode, $httpHeader);
                    } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = \SquareConnect\ObjectSerializer::deserialize($e->getResponseBody(), '\SquareConnect\Model\RetrieveCustomerResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
    }
    /**
     * updateCustomer
     *
     * UpdateCustomer
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param string $customer_id The ID of the customer to update. (required)
     * @param \SquareConnect\Model\UpdateCustomerRequest $body An object containing the fields to POST for the request.  See the corresponding object definition for field details. (required)
     * @return \SquareConnect\Model\UpdateCustomerResponse
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    public function updateCustomer($authorization, $customer_id, $body)
    {
        list($response, $statusCode, $httpHeader) = $this->updateCustomerWithHttpInfo ($authorization, $customer_id, $body);
        return $response; 
    }


    /**
     * updateCustomerWithHttpInfo
     *
     * UpdateCustomer
     *
     * @param string $authorization The value to provide in the Authorization header of your request. This value should follow the format &#x60;Bearer YOUR_ACCESS_TOKEN_HERE&#x60;. (required)
     * @param string $customer_id The ID of the customer to update. (required)
     * @param \SquareConnect\Model\UpdateCustomerRequest $body An object containing the fields to POST for the request.  See the corresponding object definition for field details. (required)
     * @return Array of \SquareConnect\Model\UpdateCustomerResponse, HTTP status code, HTTP response headers (array of strings)
     * @throws \SquareConnect\ApiException on non-2xx response
     */
    public function updateCustomerWithHttpInfo($authorization, $customer_id, $body)
    {
        
        // verify the required parameter 'authorization' is set
        if ($authorization === null) {
            throw new \InvalidArgumentException('Missing the required parameter $authorization when calling updateCustomer');
        }
        // verify the required parameter 'customer_id' is set
        if ($customer_id === null) {
            throw new \InvalidArgumentException('Missing the required parameter $customer_id when calling updateCustomer');
        }
        // verify the required parameter 'body' is set
        if ($body === null) {
            throw new \InvalidArgumentException('Missing the required parameter $body when calling updateCustomer');
        }
  
        // parse inputs
        $resourcePath = "/v2/customers/{customer_id}";
        $httpBody = '';
        $queryParams = array();
        $headerParams = array();
        $formParams = array();
        $_header_accept = ApiClient::selectHeaderAccept(array('application/json'));
        if (!is_null($_header_accept)) {
            $headerParams['Accept'] = $_header_accept;
        }
        $headerParams['Content-Type'] = ApiClient::selectHeaderContentType(array('application/json'));
  
        
        // header params
        if ($authorization !== null) {
            $headerParams['Authorization'] = $this->apiClient->getSerializer()->toHeaderValue($authorization);
        }
        // path params
        if ($customer_id !== null) {
            $resourcePath = str_replace(
                "{" . "customer_id" . "}",
                $this->apiClient->getSerializer()->toPathValue($customer_id),
                $resourcePath
            );
        }
        // default format to json
        $resourcePath = str_replace("{format}", "json", $resourcePath);

        
        // body params
        $_tempBody = null;
        if (isset($body)) {
            $_tempBody = $body;
        }
  
        // for model (json/xml)
        if (isset($_tempBody)) {
            $httpBody = $_tempBody; // $_tempBody is the method argument, if present
        } elseif (count($formParams) > 0) {
            $httpBody = $formParams; // for HTTP post (form)
        }
                // make the API Call
        try {
            list($response, $statusCode, $httpHeader) = $this->apiClient->callApi(
                $resourcePath, 'PUT',
                $queryParams, $httpBody,
                $headerParams, '\SquareConnect\Model\UpdateCustomerResponse'
            );
            if (!$response) {
                return array(null, $statusCode, $httpHeader);
            }

            return array(\SquareConnect\ObjectSerializer::deserialize($response, '\SquareConnect\Model\UpdateCustomerResponse', $httpHeader), $statusCode, $httpHeader);
                    } catch (ApiException $e) {
            switch ($e->getCode()) { 
            case 200:
                $data = \SquareConnect\ObjectSerializer::deserialize($e->getResponseBody(), '\SquareConnect\Model\UpdateCustomerResponse', $e->getResponseHeaders());
                $e->setResponseObject($data);
                break;
            }
  
            throw $e;
        }
    }
}