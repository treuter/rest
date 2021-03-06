<?php namespace webservices\rest;

use io\streams\Streams;
use io\streams\MemoryInputStream;
use peer\http\HttpResponse;


/**
 * A REST response
 *
 * @test    xp://net.xp_framework.unittest.webservices.rest.RestResponseTest
 */
class RestResponse extends \lang\Object {
  protected $response= null;
  protected $reader= null;
  protected $input= null;
  protected $type= null;

  /**
   * Creates a new response
   *
   * @param   peer.http.HttpResponse response
   * @param   webservices.rest.ResponseReader reader
   * @param   var type (Deprecated)
   */
  public function __construct(HttpResponse $response, ResponseReader $reader= null, $type= null) {
    $this->response= $response;
    $this->reader= $reader;
    $this->type= $type;
    $this->input= $response->getInputStream();
  }

  /**
   * Get status code
   *
   * @return  int
   */
  public function status() {
    return $this->response->statusCode();
  }

  /**
   * Get status message
   *
   * @return  string
   */

  public function message() {
    return $this->response->message();
  }

  /**
   * Get data
   *
   * @return  string
   */
  public function content() {
    return Streams::readAll($this->input);
  }

  /**
   * Get data as stream
   *
   * @return  io.streams.InputStream
   */
  public function stream() {
    return $this->input;
  }

  /**
   * Get headers
   *
   * @return  [:var]
   */
  public function headers() {
    $r= [];
    foreach ($this->response->headers() as $key => $values) {
      $r[$key]= sizeof($values) > 1 ? $values : $values[0];
    }
    return $r;
  }

  /**
   * Get headers
   *
   * @return  [:var]
   */
  public function cookies() {
    if (null === ($header= $this->response->header('Set-Cookie'))) return [];

    $r= [];
    foreach ($header as $cookie) {
      sscanf($cookie, "%[^=]=%[^\r]", $name, $content);
      $r[$name]= $content;
    }
    return $r;
  }

  /**
   * Get header by a specified name
   *
   * @param   string name
   * @return  var
   */
  public function header($name) {
    if (null === ($values= $this->response->header($name))) return null;  // Not found
    return sizeof($values) > 1 ? $values : $values[0];
  }

  /**
   * Copy data
   *
   * @return  string
   */
  public function contentCopy() {
    $data= $this->content();

    // Reassign input, so code relying on the stream delivering bytes
    // can still read them.
    $this->input= new MemoryInputStream($data);
    return $data;
  }

  /**
   * Handle status code. Throws an exception in this default implementation
   * if the numeric value is larger than 399. Overwrite in subclasses to 
   * change this behaviour.
   *
   * @param   int code
   * @throws  webservices.rest.RestException
   */
  protected function handleStatus($code) {
    if ($code > 399) {
      throw new RestException($code.': '.$this->response->message());
    }
  }

  /**
   * Handle payload deserialization. Uses the deserializer passed to the
   * constructor to deserialize the input stream and coerces it to the 
   * passed target type. Overwrite in subclasses to change this behaviour.
   *
   * @param   lang.Type target
   * @return  var
   */
  protected function handlePayloadOf($target) {
    return $this->reader->read($target, $this->input);
  }

  /**
   * Get data
   *
   * @param   var type target type of deserialization, either a lang.Type or a string
   * @return  var
   * @throws  webservices.rest.RestException if the status code is > 399
   */
  public function data($type= null) {
    $this->handleStatus($this->response->statusCode());
 
    if (null === $type) {
      $target= $this->type ?: \lang\Type::$VAR;  // BC
    } else if ($type instanceof \lang\Type) {
      $target= $type;
    } else {
      $target= \lang\Type::forName($type);
    }

    if (null === $this->reader) {
      throw new \lang\IllegalArgumentException('Unknown content type "'.$this->headers['Content-Type'][0].'"');
    }

    return $this->handlePayloadOf($target);
  }

  /**
   * Creates a string representation
   *
   * @return string
   */
  public function toString() {
    return nameof($this).'<'.$this->response->message().'>@(->'.$this->response->toString().')';
  }
}
