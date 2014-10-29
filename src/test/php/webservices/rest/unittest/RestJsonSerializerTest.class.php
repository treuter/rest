<?php namespace webservices\rest\unittest;

use unittest\TestCase;
use webservices\rest\RestJsonSerializer;
use util\Date;
use util\TimeZone;

/**
 * TestCase
 *
 * @see   xp://webservices.rest.RestJsonSerializer
 */
class RestJsonSerializerTest extends TestCase {
  protected $fixture= null;

  /**
   * Sets up test case
   */
  public function setUp() {
    $this->fixture= new RestJsonSerializer();
  }

  #[@test]
  public function null() {
    $this->assertEquals('null', $this->fixture->serialize(null));
  }

  #[@test, @values(['', 'Test'])]
  public function strings($str) {
    $this->assertEquals('"'.$str.'"', $this->fixture->serialize($str));
  }

  #[@test, @values([-1, 0, 1, 4711])]
  public function integers($int) {
    $this->assertEquals(''.$int, $this->fixture->serialize($int));
  }

  #[@test, @values([-1.0, 0.0, 1.0, 47.11])]
  public function decimals($decimal) {
    $this->assertEquals(''.$decimal, $this->fixture->serialize($decimal));
  }

  #[@test]
  public function boolean_true() {
    $this->assertEquals('true', $this->fixture->serialize(true));
  }

  #[@test]
  public function boolean_false() {
    $this->assertEquals('false', $this->fixture->serialize(false));
  }

  #[@test]
  public function empty_array() {
    $this->assertEquals('[ ]', $this->fixture->serialize([]));
  }

  #[@test]
  public function int_array() {
    $this->assertEquals('[ 1 , 2 , 3 ]', $this->fixture->serialize([1, 2, 3]));
  }

  #[@test]
  public function string_array() {
    $this->assertEquals('[ "a" , "b" , "c" ]', $this->fixture->serialize(['a', 'b', 'c']));
  }

  #[@test]
  public function string_map() {
    $this->assertEquals(
      '{ "a" : "One" , "b" : "Two" , "c" : "Three" }',
      $this->fixture->serialize(['a' => 'One', 'b' => 'Two', 'c' => 'Three'])
    );
  }

  #[@test]
  public function traversable_array() {
    $this->assertEquals(
      '[ 1 , 2 , 3 ]',
      $this->fixture->serialize(new \ArrayIterator([1, 2, 3]))
    );
  }

  #[@test]
  public function traversable_map() {
    $this->assertEquals(
      '{ "color" : "green" , "price" : "$12.99" }',
      $this->fixture->serialize(new \ArrayIterator(['color' => 'green', 'price' => '$12.99']))
    );
  }
}
