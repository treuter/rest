<?php namespace webservices\rest;

use util\log\Traceable;
use peer\Header;
use peer\http\HttpConnection;
use peer\http\HttpRequest;
use lang\IllegalStateException;
use lang\IllegalArgumentException;
use lang\XPClass;

/**
 * REST client
 *
 * @test xp://net.xp_framework.unittest.webservices.rest.RestClientTest
 * @test xp://net.xp_framework.unittest.webservices.rest.RestClientSendTest
 * @test xp://net.xp_framework.unittest.webservices.rest.RestClientExecutionTest
 */
class RestClient extends \lang\Object implements Traceable {
  protected $connection= null;
  protected $cat= null;
  protected $serializers= [];
  protected $deserializers= [];
  protected $marshalling= null;

  /**
   * Creates a new Restconnection instance
   *
   * @param   var base default NULL
   */
  public function __construct($base= null) {
    if (null !== $base) $this->setBase($base);
    $this->marshalling= new RestMarshalling();
  }

  /**
   * Set trace
   *
   * @param   util.log.LogCategory cat
   */
  public function setTrace($cat) {
    $this->cat= $cat;
  }

  /**
   * Sets base
   *
   * @param   var base either a peer.URL or a string
   */
  public function setBase($base) {
    $this->setConnection(new HttpConnection($base));
  }

  /**
   * Sets base and returns this connection
   *
   * @param   var base either a peer.URL or a string
   * @return  self
   */
  public function withBase($base) {
    $this->setBase($base);
    return $this;
  }

  /**
   * Get base
   *
   * @return  peer.URL
   */
  public function getBase() {
    return $this->connection ? $this->connection->getURL() : null;
  }

  /**
   * Sets HTTP connection
   *
   * @param   peer.http.HttpConnection connection
   */
  public function setConnection(HttpConnection $connection) {
    $this->connection= $connection;
  }

  /**
   * Set connect timeout
   *
   * @param   float timeout
   * @throws  lang.IllegalStateException if no connection is set
   */
  public function setConnectTimeout($timeout) {
    if (null === $this->connection) {
      throw new IllegalStateException('No connection set');
    }

    $this->connection->setConnectTimeout($timeout);
  }

  /**
   * Retrieve connect timeout
   *
   * @return  float
   * @throws  lang.IllegalStateException if no connection is set
   */
  public function getConnectTimeout() {
    if (null === $this->connection) {
      throw new IllegalStateException('No connection set');
    }

    return $this->connection->getConnectTimeout();
  }

  /**
   * Set timeout
   *
   * @param   int timeout
   * @throws  lang.IllegalStateException if no connection is set
   */
  public function setTimeout($timeout) {
    if (null === $this->connection) {
      throw new IllegalStateException('No connection set');
    }

    $this->connection->setTimeout($timeout);
  }

  /**
   * Get timeout
   *
   * @return  int
   * @throws  lang.IllegalStateException if no connection is set
   */
  public function getTimeout() {
    if (null === $this->connection) {
      throw new IllegalStateException('No connection set');
    }

    return $this->connection->getTimeout();
  }

  /**
   * Sets deserializer
   *
   * @param   string mediaType e.g. "text/xml"
   * @param   webservices.rest.Deserializer deserializer
   */
  public function setDeserializer($mediaType, $deserializer) {
    $this->deserializers[$mediaType]= $deserializer;
  }

  /**
   * Returns a deserializer
   *
   * @param   string contentType
   * @return  webservices.rest.RestDeserializer
   */
  public function deserializerFor($contentType) {
    $mediaType= substr($contentType, 0, strcspn($contentType, ';'));
    if (isset($this->deserializers[$mediaType])) {
      return $this->deserializers[$mediaType];
    } else {
      return RestFormat::forMediaType($mediaType)->deserializer();
    }
  }

  /**
   * Sets deserializer
   *
   * @param   string mediaType e.g. "text/xml"
   * @param   webservices.rest.Serializer serializer
   */
  public function setSerializer($mediaType, $serializer) {
    $this->serializers[$mediaType]= $serializer;
  }

  /**
   * Returns a serializer
   *
   * @param   string contentType
   * @return  webservices.rest.RestSerializer
   */
  public function serializerFor($contentType) {
    $mediaType= substr($contentType, 0, strcspn($contentType, ';'));
    if (isset($this->serializers[$mediaType])) {
      return $this->serializers[$mediaType];
    } else {
      return RestFormat::forMediaType($mediaType)->serializer();
    }
  }

  /**
   * Execute a request
   *
   * ```php
   * $client->execute(new RestRequest('/', HttpConstants::GET));
   * ```
   *
   * @param   var t either a string or a lang.Type - response type, defaults to webservices.rest.RestResponse
   * @param   webservices.rest.RestRequest request
   * @return  webservices.rest.RestResponse
   * @throws  lang.IllegalStateException if no connection is set
   */
  public function execute($t, $request= null) {
    if (1 === func_num_args()) {      // Overloaded version with single argument
      $request= $t;
      $type= null;
    } else if (is_string($t)) {       // Overloaded version with string type
      $type= \lang\Type::forName($t);
    } else if ($t instanceof \lang\Type) {  // Overloaded version with Type instance
      $type= $t;
    } else {
      throw new IllegalArgumentException('Given type is neither a Type nor a string, '.\xp::typeOf($request).' given');
    }

    if (!$request instanceof RestRequest) {
      throw new IllegalArgumentException('Given request is not a RestRequest, '.\xp::typeOf($request).' given');
    }

    if (null === $this->connection) {
      throw new IllegalStateException('No connection set');
    }

    $send= $this->connection->create(new HttpRequest());
    $send->addHeaders($request->headerList());
    $send->setMethod($request->getMethod());
    $send->setTarget($request->getTarget($this->connection->getUrl()->getPath('/')));

    // Compose body
    // * Serialize payloads using the serializer for the given mimetype
    // * Use bodies as-is, e.g. file uploads
    // * If no body and no payload is set, use parameters
    if ($request->hasPayload()) {
      $send->setParameters(new RequestData(
        $this->marshalling->marshal($request->getPayload()),
        $this->serializerFor($request->getContentType())
      ));
    } else if ($request->hasBody()) {
      $send->setParameters($request->getBody());
    } else {
      $send->setParameters($request->getParameters());
    }
    
    try {
      $this->cat && $this->cat->debug('>>>', $send->getRequestString());
      $response= $this->connection->send($send);
    } catch (\io\IOException $e) {
      throw new RestException('Cannot send request', $e);
    }

    $reader= new ResponseReader($this->deserializerFor($response->header('Content-Type')[0]), $this->marshalling);
    if (null === $type) {
      $rr= new RestResponse($response, $reader);
    } else if ($type instanceof XPClass && $type->isSubclassOf('webservices.rest.RestResponse')) {
      $rr= $type->newInstance($response, $reader);
    } else {
      $rr= new RestResponse($response, $reader, $type);   // Deprecated!
    }

    $this->cat && $this->cat->debug('<<<', $response->toString(), $rr->contentCopy());
    return $rr;
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return nameof($this).'(->'.\xp::stringOf($this->connection).')';
  }
}
