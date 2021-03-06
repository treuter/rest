<?php namespace webservices\rest;

use peer\http\HttpConstants;
use peer\http\Header;

/**
 * A REST request
 *
 * @test    xp://net.xp_framework.unittest.webservices.rest.RestRequestTest
 */
class RestRequest extends \lang\Object {
  protected $resource= '/';
  protected $method= '';
  protected $contentType= null;
  protected $parameters= [];
  protected $segments= [];
  protected $headers= [];
  protected $accept= [];
  protected $payload= null;
  protected $body= null;

  /**
   * Creates a new RestRequest instance
   *
   * @param   string resource default NULL
   * @param   string method default HttpConstants::GET
   */
  public function __construct($resource= null, $method= HttpConstants::GET) {
    if (null !== $resource) $this->setResource($resource);
    $this->method= $method;
  }
  
  /**
   * Sets resource
   *
   * @param   string resource
   */
  public function setResource($resource) {
    $this->resource= $resource;
  }

  /**
   * Sets resource
   *
   * @param   string resource
   * @return  self
   */
  public function withResource($resource) {
    $this->resource= $resource;
    return $this;
  }

  /**
   * Gets resource
   *
   * @return  string resource
   */
  public function getResource() {
    return $this->resource;
  }

  /**
   * Sets method
   *
   * @param   string method
   */
  public function setMethod($method) {
    $this->method= $method;
  }

  /**
   * Sets method
   *
   * @param   string method
   * @return  self
   */
  public function withMethod($method) {
    $this->method= $method;
    return $this;
  }

  /**
   * Gets method
   *
   * @return  string method
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * Sets body
   *
   * @param   peer.http.RequestData body
   */
  public function setBody(\peer\http\RequestData $body) {
    $this->body= $body;
  }

  /**
   * Sets body
   *
   * @param   peer.http.RequestData body
   * @return  self
   */
  public function withBody(\peer\http\RequestData $body) {
    $this->body= $body;
    return $this;
  }

  /**
   * Adds an expected mime type
   *
   * @param   string range
   * @param   string q
   */
  public function addAccept($type, $q= null) {
    $range= $type;
    null === $q || $range.= ';q='.$q;
    $this->accept[]= $range;
  }

  /**
   * Adds an expected mime type
   *
   * @param   string range
   * @param   string q
   * @return  self
   */
  public function withAccept($type, $q= null) {
    $this->addAccept($type, $q);
    return $this;
  }

  /**
   * Adds a cookie
   *
   * @param   string $name cookie name
   * @param   string $value default ''
   * @return  void
   */
  public function addCookie($name, $value= '') {
    $this->headers[]= new Header('Cookie', $name.'='.$value);
  }

  /**
   * Adds a cookie
   *
   * @param   string $name cookie name
   * @param   string $value default ''
   * @return  self
   */
  public function withCookie($name, $value= '') {
    $this->addCookie($name, $value);
    return $this;
  }

  /**
   * Sets payload
   *
   * @param   var payload
   * @param   var format either a string, a RestFormat or a RestSerializer instance
   */
  public function setPayload($payload, $format) {
    $this->payload= $payload;
    if ($format instanceof \webservices\rest\RestFormat) {
      $this->contentType= $format->serializer()->contentType();
    } else if ($format instanceof RestSerializer) {
      $this->contentType= $format->contentType();
    } else {
      $this->contentType= $format;
    }
  }

  /**
   * Sets payload
   *
   * @param   var payload
   * @param   var format
   * @return  self
   */
  public function withPayload($payload, $format) {
    $this->setPayload($payload, $format);
    return $this;
  }

  /**
   * Gets payload
   *
   * @return  var
   */
  public function hasPayload() {
    return null !== $this->payload;
  }

  /**
   * Gets payload
   *
   * @return  var
   */
  public function getPayload() {
    return $this->payload;
  }

  /**
   * Gets content type
   *
   * @return  string
   */
  public function getContentType() {
    return $this->contentType;
  }

  /**
   * Gets body
   *
   * @return  peer.http.RequestData body
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * Gets whether a body is set
   *
   * @return  bool
   */
  public function hasBody() {
    return null !== $this->body;
  }

  /**
   * Adds a parameter
   *
   * @param   string name
   * @param   string value
   */
  public function addParameter($name, $value) {
    $this->parameters[$name]= $value;
  }

  /**
   * Adds a parameter
   *
   * @param   string name
   * @param   string value
   * @return  self
   */
  public function withParameter($name, $value) {
    $this->parameters[$name]= $value;
    return $this;
  }

  /**
   * Adds a segment
   *
   * @param   string name
   * @param   string value
   */
  public function addSegment($name, $value) {
    $this->segments[$name]= $value;
  }

  /**
   * Adds a segment
   *
   * @param   string name
   * @param   string value
   * @return  self
   */
  public function withSegment($name, $value) {
    $this->segments[$name]= $value;
    return $this;
  }

  /**
   * Adds a header
   *
   * @param   var arg
   * @param   string value
   * @return  peer.Header
   */
  public function addHeader($arg, $value= null) {
    if ($arg instanceof \peer\Header) {
      $h= $arg;
    } else {
      $h= new Header($arg, $value);
    }
    $this->headers[]= $h;
    return $h;
  }

  /**
   * Adds a header
   *
   * @param   var arg
   * @param   string value
   * @return  self
   */
  public function withHeader($arg, $value= null) {
    $this->addHeader($arg, $value);
    return $this;
  }

  /**
   * Returns a parameter specified by its name
   *
   * @param   string name
   * @return  string value
   * @throws  lang.ElementNotFoundException
   */
  public function getParameter($name) {
    if (!isset($this->parameters[$name])) {
      raise('lang.ElementNotFoundException', 'No such parameter "'.$name.'"');
    }
    return $this->parameters[$name];
  }

  /**
   * Returns all parameters
   *
   * @return  [:string]
   */
  public function getParameters() {
    return $this->parameters;
  }

  /**
   * Returns a segment specified by its name
   *
   * @param   string name
   * @return  string value
   * @throws  lang.ElementNotFoundException
   */
  public function getSegment($name) {
    if (!isset($this->segments[$name])) {
      raise('lang.ElementNotFoundException', 'No such segment "'.$name.'"');
    }
    return $this->segments[$name];
  }

  /**
   * Returns all segments
   *
   * @return  [:string]
   */
  public function getSegments() {
    return $this->segments;
  }

  /**
   * Returns a header specified by its name
   *
   * @param   string name
   * @return  string value
   * @throws  lang.ElementNotFoundException
   */
  public function getHeader($name) {
    if ('Content-Type' === $name) {
      return $this->contentType;
    } else if ('Accept' === $name) {
      return $this->accept;
    } else foreach ($this->headers as $header) {
      if ($name === $header->name()) return $header->value();
    }
    raise('lang.ElementNotFoundException', 'No such header "'.$name.'"');
  }

  /**
   * Returns all headers
   *
   * @return  [:string]
   */
  public function getHeaders() {
    $headers= [];
    foreach ($this->headers as $header) {
      $headers[$header->name()]= $header->value();
    }
    $this->contentType && $headers['Content-Type']= $this->contentType;
    $this->accept && $headers['Accept']= implode(', ', $this->accept);
    return $headers;
  }

  /**
   * Returns all headers
   *
   * @return  peer.Header[]
   */
  public function headerList() {
    return array_merge(
      $this->headers,
      $this->contentType ? [new Header('Content-Type', $this->contentType)] : [],
      $this->accept ? [new Header('Accept', implode(', ', $this->accept))] : []
    );
  }

  /**
   * Gets query
   *
   * @param   string base
   * @return  string query
   */
  public function getTarget($base= '/') {
    $resource= rtrim($base, '/').'/'.ltrim($this->resource, '/');
    $l= strlen($resource);
    $target= '';
    $offset= 0;
    do {
      $b= strcspn($resource, '{', $offset);
      $target.= substr($resource, $offset, $b);
      $offset+= $b;
      if ($offset >= $l) break;
      $e= strcspn($resource, '}', $offset);
      $target.= urlencode($this->getSegment(substr($resource, $offset+ 1, $e- 1)));
      $offset+= $e+ 1;
    } while ($offset < $l);
    return $target;
  }


  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    $headers= "\n";
    foreach ($this->headers as $header) {
      $headers.= '  '.$header->name().': '.\xp::stringOf($header->value())."\n";
    }
    if ($this->accept) {
      $headers.='  Accept: '.implode(', ', $this->accept)."\n";
    }

    return nameof($this).'('.$this->method.' '.$this->resource.')@['.$headers.']';
  }
}
