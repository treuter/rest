<?php namespace webservices\rest;

use io\streams\InputStream;
use io\streams\OutputStream;

/**
 * Rest format
 *
 * @test  xp://net.xp_framework.unittest.webservices.rest.RestFormatTest
 */
class RestFormat extends \lang\Enum implements Format {
  public static $JSON;
  public static $XML;
  public static $FORM;

  private $serializer, $deserializer;

  static function __static() {
    self::$JSON= new self(1, 'JSON', new RestJsonSerializer(), new RestJsonDeserializer());
    self::$XML= new self(2, 'XML', new RestXmlSerializer(), new RestXmlDeserializer());
    self::$FORM= new self(3, 'FORM', new RestFormSerializer(), new RestFormDeserializer());
  }

  /**
   * Constructor
   *
   * @param  int ordinal
   * @param  string name
   * @param  webservices.rest.RestSerializer serializer
   * @param  webservices.rest.RestDeserializer deserializer
   */
  public function __construct($ordinal, $name, $serializer, $deserializer) {
    parent::__construct($ordinal, $name);
    $this->serializer= $serializer;
    $this->deserializer= $deserializer;
  }

  /** @return bool */
  public function isHandled() { return true; }

  /** @return webservices.rest.RestSerializer */
  public function serializer() { return $this->serializer; }

  /** @return webservices.rest.RestDeserializer */
  public function deserializer() { return $this->deserializer; }

  /**
   * Deserialize from input
   *
   * @param  io.streams.InputStream in
   * @return var
   */
  public function read(InputStream $in) {
    return $this->deserializer->deserialize($in);
  }

  /**
   * Serialize and write to output
   *
   * @param  io.streams.OutputStream out
   * @param  webservices.rest.Payload value
   */
  public function write(OutputStream $out, Payload $value= null) {
    $this->serializer->serialize($value, $out);
  }

  /**
   * Get format for a given mediatype
   *
   * @param  string mediatype
   * @return self
   */
  public static function forMediaType($mediatype) {
    if ('application/x-www-form-urlencoded' === $mediatype) {
      return self::$FORM;
    } else if ('text/x-json' === $mediatype || 'text/javascript' === $mediatype || preg_match('#[/\+]json$#', $mediatype)) {
      return self::$JSON;
    } else if (preg_match('#[/\+]xml$#', $mediatype)) {
      return self::$XML;
    } else {
      return new UnknownFormat($mediatype ?: 'without content type');
    }
  }
}
