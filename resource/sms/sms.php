<?php
error_reporting(0);
ini_set("display_errors", 0);
define("ENABLE_HTTP_PROXY", FALSE);
define("HTTP_PROXY_IP", "127.0.0.1");
define("HTTP_PROXY_PORT", "8888");
class HttpRequest
{
    public $timeoutMs;
    public $url;
    public $method;
    public $customHeaders;
    public $dataToPost;
    public $userData;
}
class HttpResponse
{
    public $curlErrorCode;
    public $curlErrorMessage;
    public $statusCode;
    public $headers;
    public $body;
}
class LibcurlWrapper
{
    private $curlMultiHandle;
    private $curlHandleInfo;
    private $idleCurlHandle;
    public function __construct()
    {
        $this->curlMultiHandle = curl_multi_init();
        $this->idleCurlHandle = array();
    }
    public function __destruct()
    {
        curl_multi_close($this->curlMultiHandle);
        foreach ($this->idleCurlHandle as $handle) {
            curl_close($handle);
        }
        $this->idleCurlHandle = array();
    }
    public function startSendingRequest($httpRequest, $done)
    {
        if (count($this->idleCurlHandle) !== 0) {
            $curlHandle = array_pop($this->idleCurlHandle);
        } else {
            $curlHandle = curl_init();
            if ($curlHandle === false) {
                return false;
            }
        }
        curl_setopt($curlHandle, CURLOPT_TIMEOUT_MS, $httpRequest->timeoutMs);
        curl_setopt($curlHandle, CURLOPT_URL, $httpRequest->url);
        curl_setopt($curlHandle, CURLOPT_HEADER, 1);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        $headers = $httpRequest->customHeaders;
        array_push($headers, "User-Agent:" . Conf::getUserAgent());
        if ($httpRequest->method === "POST") {
            if (defined("CURLOPT_SAFE_UPLOAD")) {
                curl_setopt($curlHandle, CURLOPT_SAFE_UPLOAD, true);
            }
            curl_setopt($curlHandle, CURLOPT_POST, true);
            $arr = buildCustomPostFields($httpRequest->dataToPost);
            array_push($headers, "Expect: 100-continue");
            array_push($headers, "Content-Type: multipart/form-data; boundary=" . $arr[0]);
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $arr[1]);
        }
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
        curl_multi_add_handle($this->curlMultiHandle, $curlHandle);
        $this->curlHandleInfo[$curlHandle]["done"] = $done;
        $this->curlHandleInfo[$curlHandle]["request"] = $httpRequest;
    }
    public function performSendingRequest()
    {
        $active = null;
        $mrc = curl_multi_exec($this->curlMultiHandle, $active);
        $info = curl_multi_info_read($this->curlMultiHandle);
        BmgO1:
        if ($info !== false) {
            $this->processResult($info);
        }
        if ($mrc == CURLM_CALL_MULTI_PERFORM) {
            $mrc = curl_multi_exec($this->curlMultiHandle, $active);
            $info = curl_multi_info_read($this->curlMultiHandle);
            goto BmgO1;
        }
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->curlMultiHandle) == -1) {
                usleep(1);
            }
            $mrc = curl_multi_exec($this->curlMultiHandle, $active);
            $info = curl_multi_info_read($this->curlMultiHandle);
            gnxjG:
            if ($info !== false) {
                $this->processResult($info);
            }
            if ($mrc == CURLM_CALL_MULTI_PERFORM) {
                $mrc = curl_multi_exec($this->curlMultiHandle, $active);
                $info = curl_multi_info_read($this->curlMultiHandle);
                goto gnxjG;
            }
        }
        if (!(count($this->curlHandleInfo) == 0)) {
            $active = null;
            $mrc = curl_multi_exec($this->curlMultiHandle, $active);
            $info = curl_multi_info_read($this->curlMultiHandle);
            goto BmgO1;
        }
    }
    private function processResult($info)
    {
        $result = $info["result"];
        $handle = $info["handle"];
        $request = $this->curlHandleInfo[$handle]["request"];
        $done = $this->curlHandleInfo[$handle]["done"];
        $response = new HttpResponse();
        if ($result !== CURLE_OK) {
            $response->curlErrorCode = $result;
            $response->curlErrorMessage = curl_error($handle);
            call_user_func($done, $request, $response);
        } else {
            $responseStr = curl_multi_getcontent($handle);
            $headerSize = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
            $headerStr = substr($responseStr, 0, $headerSize);
            $body = substr($responseStr, $headerSize);
            $response->curlErrorCode = curl_errno($handle);
            $response->curlErrorMessage = curl_error($handle);
            $response->statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $headLines = explode("\r\n", $headerStr);
            foreach ($headLines as $head) {
                $arr = explode(":", $head);
                if (count($arr) >= 2) {
                    $response->headers[trim($arr[0])] = trim($arr[1]);
                }
            }
            $response->body = $body;
            call_user_func($done, $request, $response);
        }
        unset($this->curlHandleInfo[$handle]);
        curl_multi_remove_handle($this->curlMultiHandle, $handle);
        array_push($this->idleCurlHandle, $handle);
    }
    private function resetCurl($handle)
    {
        if (function_exists("curl_reset")) {
            curl_reset($handle);
        } else {
            curl_setopt($handler, CURLOPT_URL, '');
            curl_setopt($handler, CURLOPT_HTTPHEADER, array());
            curl_setopt($handler, CURLOPT_POSTFIELDS, array());
            curl_setopt($handler, CURLOPT_TIMEOUT, 0);
            curl_setopt($handler, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($handler, CURLOPT_SSL_VERIFYHOST, 0);
        }
    }
}
class xcHttpResponse
{
    private $body;
    private $status;
    public function getBody()
    {
        return $this->body;
    }
    public function setBody($body)
    {
        $this->body = $body;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function setStatus($status)
    {
        $this->status = $status;
    }
    public function isSuccess()
    {
        if (200 <= $this->status && 300 > $this->status) {
            return true;
        }
        return false;
    }
}
class HttpHelper
{
    public static $connectTimeout = 30;
    public static $readTimeout = 80;
    public static function curl($url, $httpMethod = "GET", $postFields = null, $headers = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
        if (ENABLE_HTTP_PROXY) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXY, HTTP_PROXY_IP);
            curl_setopt($ch, CURLOPT_PROXYPORT, HTTP_PROXY_PORT);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($postFields) ? self::getPostHttpBody($postFields) : $postFields);
        if (self::$readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$readTimeout);
        }
        if (self::$connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
        }
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_array($headers) && 0 < count($headers)) {
            $httpHeaders = self::getHttpHearders($headers);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
        }
        $httpResponse = new xcHttpResponse();
        $httpResponse->setBody(curl_exec($ch));
        $httpResponse->setStatus(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        if (curl_errno($ch)) {
            throw new ClientException("Server unreachable: Errno: " . curl_errno($ch) . " " . curl_error($ch), "SDK.ServerUnreachable");
        }
        curl_close($ch);
        return $httpResponse;
    }
    static function getPostHttpBody($postFildes)
    {
        $content = '';
        foreach ($postFildes as $apiParamKey => $apiParamValue) {
            $content .= "{$apiParamKey}=" . urlencode($apiParamValue) . "&";
        }
        return substr($content, 0, -1);
    }
    static function getHttpHearders($headers)
    {
        $httpHeader = array();
        foreach ($headers as $key => $value) {
            array_push($httpHeader, $key . ":" . $value);
        }
        return $httpHeader;
    }
}
class ClientException extends \Exception
{
    function __construct($errorMessage, $errorCode)
    {
        parent::__construct($errorMessage);
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
        $this->setErrorType("Client");
    }
    private $errorCode;
    private $errorMessage;
    private $errorType;
    public function getErrorCode()
    {
        return $this->errorCode;
    }
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }
    public function getErrorType()
    {
        return $this->errorType;
    }
    public function setErrorType($errorType)
    {
        $this->errorType = $errorType;
    }
}
interface ISigner
{
    public function getSignatureMethod();
    public function getSignatureVersion();
    public function signString($source, $accessSecret);
}
class ShaHmac1Signer implements ISigner
{
    public function signString($source, $accessSecret)
    {
        return base64_encode(hash_hmac("sha1", $source, $accessSecret, true));
    }
    public function getSignatureMethod()
    {
        return "HMAC-SHA1";
    }
    public function getSignatureVersion()
    {
        return "1.0";
    }
}
interface IAcsClient
{
    public function doAction($requst);
}
class DefaultAcsClient implements IAcsClient
{
    public $iClientProfile;
    public $__urlTestFlag__;
    function __construct($iClientProfile)
    {
        $this->iClientProfile = $iClientProfile;
        $this->__urlTestFlag__ = false;
    }
    public function getAcsResponse($request, $iSigner = null, $credential = null, $autoRetry = true, $maxRetryNumber = 3)
    {
        $httpResponse = $this->doActionImpl($request, $iSigner, $credential, $autoRetry, $maxRetryNumber);
        $respObject = $this->parseAcsResponse($httpResponse->getBody(), $request->getAcceptFormat());
        if (false == $httpResponse->isSuccess()) {
            $this->buildApiException($respObject, $httpResponse->getStatus());
        }
        return $respObject;
    }
    private function doActionImpl($request, $iSigner = null, $credential = null, $autoRetry = true, $maxRetryNumber = 3)
    {
        if (null == $this->iClientProfile && (null == $iSigner || null == $credential || null == $request->getRegionId() || null == $request->getAcceptFormat())) {
            throw new ClientException("No active profile found.", "SDK.InvalidProfile");
        }
        if (null == $iSigner) {
            $iSigner = $this->iClientProfile->getSigner();
        }
        if (null == $credential) {
            $credential = $this->iClientProfile->getCredential();
        }
        $request = $this->prepareRequest($request);
        $domain = EndpointProvider::findProductDomain($request->getRegionId(), $request->getProduct());
        if (null == $domain) {
            throw new ClientException("Can not find endpoint to access.", "SDK.InvalidRegionId");
        }
        $requestUrl = $request->composeUrl($iSigner, $credential, $domain);
        if ($this->__urlTestFlag__) {
            throw new ClientException($requestUrl, "URLTestFlagIsSet");
        }
        if (count($request->getDomainParameter()) > 0) {
            $httpResponse = HttpHelper::curl($requestUrl, $request->getMethod(), $request->getDomainParameter(), $request->getHeaders());
        } else {
            $httpResponse = HttpHelper::curl($requestUrl, $request->getMethod(), $request->getContent(), $request->getHeaders());
        }
        $retryTimes = 1;
        while (500 <= $httpResponse->getStatus() && $autoRetry && $retryTimes < $maxRetryNumber) {
            $requestUrl = $request->composeUrl($iSigner, $credential, $domain);
            if (count($request->getDomainParameter()) > 0) {
                $httpResponse = HttpHelper::curl($requestUrl, $request->getDomainParameter(), $request->getHeaders());
            } else {
                $httpResponse = HttpHelper::curl($requestUrl, $request->getMethod(), $request->getContent(), $request->getHeaders());
            }
            $retryTimes++;
        }
        return $httpResponse;
    }
    public function doAction($request, $iSigner = null, $credential = null, $autoRetry = true, $maxRetryNumber = 3)
    {
        trigger_error("doAction() is deprecated. Please use getAcsResponse() instead.", E_USER_NOTICE);
        return $this->doActionImpl($request, $iSigner, $credential, $autoRetry, $maxRetryNumber);
    }
    private function prepareRequest($request)
    {
        if (null == $request->getRegionId()) {
            $request->setRegionId($this->iClientProfile->getRegionId());
        }
        if (null == $request->getAcceptFormat()) {
            $request->setAcceptFormat($this->iClientProfile->getFormat());
        }
        if (null == $request->getMethod()) {
            $request->setMethod("GET");
        }
        return $request;
    }
    private function buildApiException($respObject, $httpStatus)
    {
        throw new ServerException($respObject->Message, $respObject->Code, $httpStatus, $respObject->RequestId);
    }
    private function parseAcsResponse($body, $format)
    {
        if ("JSON" == $format) {
            $respObject = json_decode($body);
        } else {
            if ("XML" == $format) {
                $respObject = @simplexml_load_string($body);
            } else {
                if ("RAW" == $format) {
                    $respObject = $body;
                }
            }
        }
        return $respObject;
    }
}
class Endpoint
{
    private $name;
    private $regionIds;
    private $productDomains;
    function __construct($name, $regionIds, $productDomains)
    {
        $this->name = $name;
        $this->regionIds = $regionIds;
        $this->productDomains = $productDomains;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function getRegionIds()
    {
        return $this->regionIds;
    }
    public function setRegionIds($regionIds)
    {
        $this->regionIds = $regionIds;
    }
    public function getProductDomains()
    {
        return $this->productDomains;
    }
    public function setProductDomains($productDomains)
    {
        $this->productDomains = $productDomains;
    }
}
class ProductDomain
{
    private $productName;
    private $domainName;
    function __construct($product, $domain)
    {
        $this->productName = $product;
        $this->domainName = $domain;
    }
    public function getProductName()
    {
        return $this->productName;
    }
    public function setProductName($productName)
    {
        $this->productName = $productName;
    }
    public function getDomainName()
    {
        return $this->domainName;
    }
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }
}
class EndpointProvider
{
    private static $endpoints;
    public static function findProductDomain($regionId, $product)
    {
        if (null == $regionId || null == $product || null == self::$endpoints) {
            return null;
        }
        foreach (self::$endpoints as $key => $endpoint) {
            if (in_array($regionId, $endpoint->getRegionIds())) {
                return self::findProductDomainByProduct($endpoint->getProductDomains(), $product);
            }
        }
        return null;
    }
    private static function findProductDomainByProduct($productDomains, $product)
    {
        if (null == $productDomains) {
            return null;
        }
        foreach ($productDomains as $key => $productDomain) {
            if ($product == $productDomain->getProductName()) {
                return $productDomain->getDomainName();
            }
        }
        return null;
    }
    public static function getEndpoints()
    {
        return self::$endpoints;
    }
    public static function setEndpoints($endpoints)
    {
        self::$endpoints = $endpoints;
    }
}
class Credential
{
    private $dateTimeFormat = "Y-m-d\\TH:i:s\\Z";
    private $refreshDate;
    private $expiredDate;
    private $accessKeyId;
    private $accessSecret;
    private $securityToken;
    function __construct($accessKeyId, $accessSecret)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessSecret = $accessSecret;
        $this->refreshDate = date($this->dateTimeFormat);
    }
    public function isExpired()
    {
        if ($this->expiredDate == null) {
            return false;
        }
        if (strtotime($this->expiredDate) > date($this->dateTimeFormat)) {
            return false;
        }
        return true;
    }
    public function getRefreshDate()
    {
        return $this->refreshDate;
    }
    public function getExpiredDate()
    {
        return $this->expiredDate;
    }
    public function setExpiredDate($expiredHours)
    {
        if ($expiredHours > 0) {
            return $this->expiredDate = date($this->dateTimeFormat, strtotime("+" . $expiredHours . " hour"));
        }
    }
    public function getAccessKeyId()
    {
        return $this->accessKeyId;
    }
    public function setAccessKeyId($accessKeyId)
    {
        $this->accessKeyId = $accessKeyId;
    }
    public function getAccessSecret()
    {
        return $this->accessSecret;
    }
    public function setAccessSecret($accessSecret)
    {
        $this->accessSecret = $accessSecret;
    }
}
interface IClientProfile
{
    public function getSigner();
    public function getRegionId();
    public function getFormat();
    public function getCredential();
}
class DefaultProfile implements IClientProfile
{
    private static $profile;
    private static $endpoints;
    private static $credential;
    private static $regionId;
    private static $acceptFormat;
    private static $isigner;
    private static $iCredential;
    private function __construct($regionId, $credential)
    {
        self::$regionId = $regionId;
        self::$credential = $credential;
    }
    public static function getProfile($regionId, $accessKeyId, $accessSecret)
    {
        $credential = new Credential($accessKeyId, $accessSecret);
        self::$profile = new DefaultProfile($regionId, $credential);
        return self::$profile;
    }
    public function getSigner()
    {
        if (null == self::$isigner) {
            self::$isigner = new ShaHmac1Signer();
        }
        return self::$isigner;
    }
    public function getRegionId()
    {
        return self::$regionId;
    }
    public function getFormat()
    {
        return self::$acceptFormat;
    }
    public function getCredential()
    {
        if (null == self::$credential && null != self::$iCredential) {
            self::$credential = self::$iCredential;
        }
        return self::$credential;
    }
    public static function getEndpoints()
    {
        if (null == self::$endpoints) {
            self::$endpoints = EndpointProvider::getEndpoints();
        }
        return self::$endpoints;
    }
    public static function addEndpoint($endpointName, $regionId, $product, $domain)
    {
        if (null == self::$endpoints) {
            self::$endpoints = self::getEndpoints();
        }
        $endpoint = self::findEndpointByName($endpointName);
        if (null == $endpoint) {
            self::addEndpoint_($endpointName, $regionId, $product, $domain);
        } else {
            self::updateEndpoint($regionId, $product, $domain, $endpoint);
        }
    }
    public static function findEndpointByName($endpointName)
    {
        foreach (self::$endpoints as $key => $endpoint) {
            if ($endpoint->getName() == $endpointName) {
                return $endpoint;
            }
        }
    }
    private static function addEndpoint_($endpointName, $regionId, $product, $domain)
    {
        $regionIds = array($regionId);
        $productDomains = array(new ProductDomain($product, $domain));
        $endpoint = new Endpoint($endpointName, $regionIds, $productDomains);
        array_push(self::$endpoints, $endpoint);
    }
    private static function updateEndpoint($regionId, $product, $domain, $endpoint)
    {
        $regionIds = $endpoint->getRegionIds();
        if (!in_array($regionId, $regionIds)) {
            array_push($regionIds, $regionId);
            $endpoint->setRegionIds($regionIds);
        }
        $productDomains = $endpoint->getProductDomains();
        if (null == self::findProductDomain($productDomains, $product, $domain)) {
            array_push($productDomains, new ProductDomain($product, $domain));
        }
        $endpoint->setProductDomains($productDomains);
    }
    private static function findProductDomain($productDomains, $product, $domain)
    {
        foreach ($productDomains as $key => $productDomain) {
            if ($productDomain->getProductName() == $product && $productDomain->getDomainName() == $domain) {
                return $productDomain;
            }
        }
        return null;
    }
}
abstract class AcsRequest
{
    protected $version;
    protected $product;
    protected $actionName;
    protected $regionId;
    protected $acceptFormat;
    protected $method;
    protected $protocolType = "http";
    protected $content;
    protected $queryParameters = array();
    protected $headers = array();
    function __construct($product, $version, $actionName)
    {
        $this->headers["x-sdk-client"] = "php/2.0.0";
        $this->product = $product;
        $this->version = $version;
        $this->actionName = $actionName;
    }
    public abstract function composeUrl($iSigner, $credential, $domain)
    {
    }
    public function getVersion()
    {
        return $this->version;
    }
    public function setVersion($version)
    {
        $this->version = $version;
    }
    public function getProduct()
    {
        return $this->product;
    }
    public function setProduct($product)
    {
        $this->product = $product;
    }
    public function getActionName()
    {
        return $this->actionName;
    }
    public function setActionName($actionName)
    {
        $this->actionName = $actionName;
    }
    public function getAcceptFormat()
    {
        return $this->acceptFormat;
    }
    public function setAcceptFormat($acceptFormat)
    {
        $this->acceptFormat = $acceptFormat;
    }
    public function getQueryParameters()
    {
        return $this->queryParameters;
    }
    public function getHeaders()
    {
        return $this->headers;
    }
    public function getMethod()
    {
        return $this->method;
    }
    public function setMethod($method)
    {
        $this->method = $method;
    }
    public function getProtocol()
    {
        return $this->protocolType;
    }
    public function setProtocol($protocol)
    {
        $this->protocolType = $protocol;
    }
    public function getRegionId()
    {
        return $this->regionId;
    }
    public function setRegionId($region)
    {
        $this->regionId = $region;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function setContent($content)
    {
        $this->content = $content;
    }
    public function addHeader($headerKey, $headerValue)
    {
        $this->headers[$headerKey] = $headerValue;
    }
}
abstract class RpcAcsRequest extends AcsRequest
{
    private $dateTimeFormat = "Y-m-d\\TH:i:s\\Z";
    private $domainParameters = array();
    function __construct($product, $version, $actionName)
    {
        parent::__construct($product, $version, $actionName);
        $this->initialize();
    }
    private function initialize()
    {
        $this->setMethod("GET");
        $this->setAcceptFormat("JSON");
    }
    private function prepareValue($value)
    {
        if (is_bool($value)) {
            if ($value) {
                return "true";
            } else {
                return "false";
            }
        } else {
            return $value;
        }
    }
    public function composeUrl($iSigner, $credential, $domain)
    {
        $apiParams = parent::getQueryParameters();
        foreach ($apiParams as $key => $value) {
            $apiParams[$key] = $this->prepareValue($value);
        }
        $apiParams["RegionId"] = $this->getRegionId();
        $apiParams["AccessKeyId"] = $credential->getAccessKeyId();
        $apiParams["Format"] = $this->getAcceptFormat();
        $apiParams["SignatureMethod"] = $iSigner->getSignatureMethod();
        $apiParams["SignatureVersion"] = $iSigner->getSignatureVersion();
        $apiParams["SignatureNonce"] = uniqid(mt_rand(0, 0xffff), true);
        date_default_timezone_set("GMT");
        $apiParams["Timestamp"] = date($this->dateTimeFormat);
        $apiParams["Action"] = $this->getActionName();
        $apiParams["Version"] = $this->getVersion();
        $apiParams["Signature"] = $this->computeSignature($apiParams, $credential->getAccessSecret(), $iSigner);
        if (parent::getMethod() == "POST") {
            $requestUrl = $this->getProtocol() . "://" . $domain . "/";
            foreach ($apiParams as $apiParamKey => $apiParamValue) {
                $this->putDomainParameters($apiParamKey, $apiParamValue);
            }
            return $requestUrl;
        } else {
            $requestUrl = $this->getProtocol() . "://" . $domain . "/?";
            foreach ($apiParams as $apiParamKey => $apiParamValue) {
                $requestUrl .= "{$apiParamKey}=" . urlencode($apiParamValue) . "&";
            }
            return substr($requestUrl, 0, -1);
        }
    }
    private function computeSignature($parameters, $accessKeySecret, $iSigner)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= "&" . $this->percentEncode($key) . "=" . $this->percentEncode($value);
        }
        $stringToSign = parent::getMethod() . "&%2F&" . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = $iSigner->signString($stringToSign, $accessKeySecret . "&");
        return $signature;
    }
    protected function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\\+/", "%20", $res);
        $res = preg_replace("/\\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }
    public function getDomainParameter()
    {
        return $this->domainParameters;
    }
    public function putDomainParameters($name, $value)
    {
        $this->domainParameters[$name] = $value;
    }
}
class SendSmsRequest extends RpcAcsRequest
{
    public function __construct()
    {
        parent::__construct("Dysmsapi", "2017-05-25", "SendSms");
        $this->setMethod("POST");
    }
    private $templateCode;
    private $phoneNumbers;
    private $signName;
    private $resourceOwnerAccount;
    private $templateParam;
    private $resourceOwnerId;
    private $ownerId;
    private $outId;
    private $smsUpExtendCode;
    public function getTemplateCode()
    {
        return $this->templateCode;
    }
    public function setTemplateCode($templateCode)
    {
        $this->templateCode = $templateCode;
        $this->queryParameters["TemplateCode"] = $templateCode;
    }
    public function getPhoneNumbers()
    {
        return $this->phoneNumbers;
    }
    public function setPhoneNumbers($phoneNumbers)
    {
        $this->phoneNumbers = $phoneNumbers;
        $this->queryParameters["PhoneNumbers"] = $phoneNumbers;
    }
    public function getSignName()
    {
        return $this->signName;
    }
    public function setSignName($signName)
    {
        $this->signName = $signName;
        $this->queryParameters["SignName"] = $signName;
    }
    public function getResourceOwnerAccount()
    {
        return $this->resourceOwnerAccount;
    }
    public function setResourceOwnerAccount($resourceOwnerAccount)
    {
        $this->resourceOwnerAccount = $resourceOwnerAccount;
        $this->queryParameters["ResourceOwnerAccount"] = $resourceOwnerAccount;
    }
    public function getTemplateParam()
    {
        return $this->templateParam;
    }
    public function setTemplateParam($templateParam)
    {
        $this->templateParam = $templateParam;
        $this->queryParameters["TemplateParam"] = $templateParam;
    }
    public function getResourceOwnerId()
    {
        return $this->resourceOwnerId;
    }
    public function setResourceOwnerId($resourceOwnerId)
    {
        $this->resourceOwnerId = $resourceOwnerId;
        $this->queryParameters["ResourceOwnerId"] = $resourceOwnerId;
    }
    public function getOwnerId()
    {
        return $this->ownerId;
    }
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        $this->queryParameters["OwnerId"] = $ownerId;
    }
    public function getOutId()
    {
        return $this->outId;
    }
    public function setOutId($outId)
    {
        $this->outId = $outId;
        $this->queryParameters["OutId"] = $outId;
    }
    public function getSmsUpExtendCode()
    {
        return $this->smsUpExtendCode;
    }
    public function setSmsUpExtendCode($smsUpExtendCode)
    {
        $this->smsUpExtendCode = $smsUpExtendCode;
        $this->queryParameters["SmsUpExtendCode"] = $smsUpExtendCode;
    }
}
class xcap_SmsDemo
{
    static $acsClient = null;
    public static function getAcsClient($AccessKeyId, $AccessKeySecret)
    {
        $product = "Dysmsapi";
        $domain = "dysmsapi.aliyuncs.com";
        $accessKeyId = $AccessKeyId;
        $accessKeySecret = $AccessKeySecret;
        $region = "cn-hangzhou";
        $endPointName = "cn-hangzhou";
        if (static::$acsClient == null) {
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }
    public static function sendSms($AccessKeyId, $AccessKeySecret, $signName, $templateCode, $phoneNumbers, $templateParam = null, $outId = null, $smsUpExtendCode = null)
    {
        $request = new SendSmsRequest();
        $request->setPhoneNumbers($phoneNumbers);
        $request->setSignName($signName);
        $request->setTemplateCode($templateCode);
        if ($templateParam) {
            $request->setTemplateParam(json_encode($templateParam));
        }
        if ($outId) {
            $request->setOutId($outId);
        }
        if ($smsUpExtendCode) {
            $request->setSmsUpExtendCode($smsUpExtendCode);
        }
        $acsResponse = static::getAcsClient($AccessKeyId, $AccessKeySecret)->getAcsResponse($request);
        return $acsResponse;
    }
    public function queryDetails($phoneNumbers, $sendDate, $pageSize = 10, $currentPage = 1, $bizId = null)
    {
        $request = new QuerySendDetailsRequest();
        $request->setPhoneNumber($phoneNumbers);
        $request->setBizId($bizId);
        $request->setSendDate($sendDate);
        $request->setPageSize($pageSize);
        $request->setCurrentPage($currentPage);
        $acsResponse = static::getAcsClient()->getAcsResponse($request);
        return $acsResponse;
    }
}
set_time_limit(0);
header("Content-Type: text/plain; charset=utf-8");
$endpoint_filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . "endpoints.xml";
$xml = simplexml_load_string(file_get_contents($endpoint_filename));
$json = json_encode($xml);
$json_array = json_decode($json, TRUE);
$endpoints = array();
foreach ($json_array["Endpoint"] as $json_endpoint) {
    if (!array_key_exists("RegionId", $json_endpoint["RegionIds"])) {
        $region_ids = array();
    } else {
        $json_region_ids = $json_endpoint["RegionIds"]["RegionId"];
        if (!is_array($json_region_ids)) {
            $region_ids = array($json_region_ids);
        } else {
            $region_ids = $json_region_ids;
        }
    }
    if (!array_key_exists("Product", $json_endpoint["Products"])) {
        $products = array();
    } else {
        $json_products = $json_endpoint["Products"]["Product"];
        if (array() === $json_products or !is_array($json_products)) {
            $products = array();
        } else {
            if (array_keys($json_products) !== range(0, count($json_products) - 1)) {
                $products = array($json_products);
            } else {
                $products = $json_products;
            }
        }
    }
    $product_domains = array();
    foreach ($products as $product) {
        $product_domain = new ProductDomain($product["ProductName"], $product["DomainName"]);
        array_push($product_domains, $product_domain);
    }
    $endpoint = new Endpoint($region_ids[0], $region_ids, $product_domains);
    array_push($endpoints, $endpoint);
}
EndpointProvider::setEndpoints($endpoints);