<?php

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\TableNode;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;

/**
 * Rest context.
 */
class RestContext extends BehatContext
{
    protected $client;

    /**
     * @var Request
     */
    protected $lastRequest;

    /**
     * @var Response
     */
    protected $lastResponse;

    protected $custormHeaders =
        [
        ];

    protected $customHeadersEnabled = true;

    public function __construct($baseUrl)
    {
        $this->client = new Client(
            $baseUrl,
            [
                'ssl.certificate_authority'   => false,
                'curl.CURLOPT_SSL_VERIFYPEER' => false,
                'curl.CURLOPT_CERTINFO'       => false
            ]
        );
    }

    public function setCustomHeader($key, $value)
    {
        $this->custormHeaders[$key] = $value;
        return $this;
    }

    public function setCustomHeadersEnabled($customHeadersEnabled)
    {
        $this->customHeadersEnabled = $customHeadersEnabled;
        return $this;
    }

    /**
     * @When /^I send ([A-Z]+) request to "([^"]*)"$/
     * @When /^I send ([A-Z]+) request to "([^"]*)" with values:$/
     */
    public function iSendRequest($method, $url, TableNode $table = null)
    {
        $values = $table ? $table->getRowsHash() : [];

        $url = [$url, $this->getMainContext()->getPlaceholders()];

        $queryString = implode('&', $this->getMainContext()->getQueryString());
        $url[0] .= '?' . $queryString;

        $matches = [];
        preg_match_all('({[A-Z_0-9\ \.]+})', $url[0], $matches);

        foreach ($matches[0] as $match) {
            $url[0] = str_replace($match, $this->getMainContext()->getReplacements()[$match], $url[0]);
        }

        $headers =
            [
                'Accept' => 'application/hal+json',
                'Origin' => 'https://app.continuousphp.com'
            ];

        if ($this->customHeadersEnabled) {
            $headers = array_merge($headers, $this->custormHeaders);
        }

        $this->lastRequest  = $this->client->createRequest(
            $method,
            $url,
            $headers,
            $values,
            [
                'exceptions' => false
            ]
        );
        $this->lastRequest->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
        $this->lastRequest->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);

        $this->lastResponse = $this->lastRequest->send();
    }

    /**
     * @When /^I send ([A-Z]+) request to "([^"]*)" with payload from "([^"]*)"$/
     */
    public function iSendRequestWithPayloadFrom($method, $url, $fileName)
    {
        $url = [$url, $this->getMainContext()->getPlaceholders()];

        $queryString = implode('&', $this->getMainContext()->getQueryString());
        $url[0] .= '?' . $queryString;

        $matches = [];
        preg_match_all('({[A-Z_0-9\ \.]+})', $url[0], $matches);

        foreach ($matches[0] as $match) {
            $url[0] = str_replace($match, $this->getMainContext()->getReplacements()[$match], $url[0]);
        }

        $headers =
            [
                'Accept' => 'application/hal+json',
                'Origin' => 'https://app.continuousphp.com'
            ];

        if ($this->customHeadersEnabled) {
            $headers = array_merge($headers, $this->custormHeaders);
        }

        $this->lastRequest  = $this->client->createRequest(
            $method,
            $url,
            $headers,
            file_get_contents(__DIR__ . '/../_files/' . $fileName),
            [
                'exceptions' => false
            ]
        );
        $this->lastRequest->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, false);
        $this->lastRequest->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);

        $this->lastResponse = $this->lastRequest->send();
    }

    /**
     * @Then /^response should be in JSON$/
     */
    public function responseShouldBeInJson()
    {
        $contentType = $this->getLastResponse()->getContentType();
        if ('application/hal+json' !== $contentType) {
            throw new \Exception(sprintf('Expected json content type, but got %s.', $contentType));
        }

        $this->getLastResponseJsonData();
    }

    /**
     * @Then /^response should be an ApiProblem$/
     */
    public function responseShouldBeAnApiProblem()
    {
        $contentType = $this->getLastResponse()->getContentType();
        if ('application/problem+json' !== $contentType) {
            throw new \Exception(sprintf('Expected ApiProblem content type, but got %s.', $contentType));
        }
    }

    /**
     * @Given /^the response has a "([^"]*)" property$/
     */
    public function theResponseHasAProperty($propertyName)
    {
        $this->getLastResponseJsonProperty($propertyName);
    }

    /**
     * @Then /^the "([^"]*)" property equals "([^"]*)"$/
     */
    public function thePropertyEquals($propertyName, $expectedValue)
    {
        $actualValue = $this->getLastResponseJsonProperty($propertyName);

        if ($expectedValue !== $actualValue) {
            throw new \Exception(sprintf(
                'Property "%s" was expected to equal "%s", but got "%s".',
                $propertyName,
                $expectedValue,
                $actualValue
            ));
        }
    }

    /**
     * @Then /^response status code should be (\d+)$/
     */
    public function responseStatusCodeShouldBe($httpStatus)
    {
        if ((string)$this->getLastResponse()->getStatusCode() !== $httpStatus) {
            
            throw new \Exception('HTTP code does not match '.$httpStatus.
            ' (actual: '.$this->getLastResponse()->getStatusCode().')' . PHP_EOL
            . $this->getLastResponse()->getBody());
        }
    }

    /**
     * @Then dump last request
     */
    public function dumpLastRequest()
    {
        $this->printDebug($this->getLastRequest().PHP_EOL.$this->getLastResponse());
    }

    /**
     * Returns the last sent request
     *
     * @return Request
     */
    public function getLastRequest()
    {
        if (null === $this->lastRequest) {
            throw new \LogicException('No request sent yet.');
        }

        return $this->lastRequest;
    }

    /**
     * Returns the response of the last request
     *
     * @return Response
     */
    public function getLastResponse()
    {
        if (null === $this->lastResponse) {
            throw new \LogicException('No request sent yet.');
        }

        return $this->lastResponse;
    }

    public function setLastResponse($lastResponse)
    {
        $this->lastResponse = $lastResponse;
    }

    public function getLastResponseJsonData()
    {
        $responseBody = $this->getLastResponse()->getBody(true);

        $data = json_decode($responseBody);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(sprintf('Invalid json body: %s', $responseBody));
        }

        return $data;
    }

    /**
     * @Then /^echo last response$/
     */
    public function echoLastResponse()
    {
        $this->printDebug(
            $this->getLastResponse()
        );
    }

    /**
     * @Then /^response should contain CORS headers$/
     */
    public function responseShouldContainCORSHeaders()
    {
        $responseHeaders = array_keys($this->getLastResponse()->getHeaders()->getAll());

        $corsHeadersExpected = [
            'access-control-allow-origin',
            //'access-control-expose-headers'
        ];

        $corsHeadersFound = [];

        foreach ($responseHeaders as $responseHeader) {
            if (in_array($responseHeader, $corsHeadersExpected)) {
                $corsHeadersFound[] = $responseHeader;
            }
        }

        if ($corsHeadersFound != $corsHeadersExpected) {
            throw new \Behat\Behat\Exception\BehaviorException('Found the following CORS headers : ' . implode(', ', $corsHeadersFound));
        }
    }

    /**
     * @Then /^response value "([^"]*)" should be "([^"]*)"$/
     */
    public function responseValueShouldBe($property, $value)
    {
        $replacements = $this->getMainContext()->getReplacements();

        $matches = [];

        if (preg_match('/^\{(.*)\}$/', $value, $matches)) {
            $value = $replacements['{' . $matches[1] . '}'];
        }

        $data = $this->getLastResponseJsonProperty($property);
        if ($data !== $value) {
            throw new \Behat\Behat\Exception\BehaviorException(sprintf('Expected value for property %s was %s, got %s', $property, $value, $data));
        }
    }

    /**
     * @Then /^response value "([^"]*)" should contain the key "([^"]*)" with value "([^"]*)"$/
     */
    public function responseValueShouldContainTheKeyWithValue($level1Key, $level2key, $value)
    {
        $replacements = $this->getMainContext()->getReplacements();

        $matches = [];

        if (preg_match('/^\{(.*)\}$/', $value, $matches)) {
            $value = $replacements['{' . $matches[1] . '}'];
        }

        $data = $this->getLastResponseJsonProperty($level1Key);
        if ($data->$level2key != $value) {
            throw new \Behat\Behat\Exception\BehaviorException(sprintf('Expected value for property %s was %s, got %s', "$level1Key -> $level2key", $value, $data->$level2key));
        }
    }

    /**
     * @param $propertyName
     * @return string
     */
    public function getLastResponseJsonProperty($propertyName)
    {
        $data = $this->getLastResponseJsonData();
        if (!isset($data->$propertyName)) {
            throw new \UnexpectedValueException('Response does not contain property ' . $propertyName);
        }
        return $data->$propertyName;
    }
}