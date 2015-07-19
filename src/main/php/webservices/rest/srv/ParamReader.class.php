<?php namespace webservices\rest\srv;

use webservices\rest\RestFormat;
use webservices\rest\RestDeserializer;
use lang\IllegalArgumentException;

/**
 * Reads request parameters
 *
 * @test  xp://webservices.rest.unittest.srv.ParamReaderTest
 */
abstract class ParamReader extends \lang\Enum {
  private static $sources= [];
  public static $COOKIE, $HEADER, $PARAM, $PATH, $BODY;

  static function __static() {
    self::$sources['cookie']= self::$COOKIE= newinstance(__CLASS__, [1, 'cookie'], '{
      static function __static() { }
      protected function get($name, $target, $request) {
        if (null === ($cookie= $request->getCookie($name, null))) return null;
        return $cookie->getValue();
      }
    }');
    self::$sources['header']= self::$HEADER= newinstance(__CLASS__, [2, 'header'], '{
      static function __static() { }
      protected function get($name, $target, $request) {
        return $request->getHeader($name, null);
      }
    }');
    self::$sources['param']= self::$PARAM= newinstance(__CLASS__, [3, 'param'], '{
      static function __static() { }
      protected function get($name, $target, $request) {
        return $request->getParam($name, null);
      }
    }');
    self::$sources['path']= self::$PATH= newinstance(__CLASS__, [4, 'path'], '{
      static function __static() { }
      protected function get($name, $target, $request) {
        return isset($target["segments"][$name]) ? rawurldecode($target["segments"][$name]) : null;
      }
    }');
    self::$sources['body']= self::$BODY= newinstance(__CLASS__, [5, 'body'], '{
      static function __static() { }
      protected function get($name, $target, $request) {
        return \webservices\rest\RestFormat::forMediaType($target["input"])->read($request->getInputStream(), \lang\Type::$VAR); 
      }
    }');
  }

  /**
   * Factory method
   *
   * @param  string name
   * @return self
   * @throws lang.IllegalArgumentException
   */
  public static function forName($name) {
    if (isset(self::$sources[$name])) {
      return self::$sources[$name];
    }
    throw new IllegalArgumentException('Invalid parameter source "'.$name.'"');
  }

  /**
   * Get a single parameter from the given request
   *
   * @param   string name
   * @param   [:var] target Routing target
   * @param   scriptlet.Request request
   */
  protected abstract function get($name, $target, $request);

  /**
   * Read this parameter from the given request
   *
   * @param   var spec
   * @param   [:var] target Routing target
   * @param   scriptlet.Request request
   */
  public function read($spec, $target, $request) {
    if (is_array($spec)) {
      $return= [];
      foreach ($spec as $name) {
        $return[$name]= $this->get($name, $target, $request);
      }
      return $return;
    } else {
      return $this->get($spec, $target, $request);
    }
  }
}
