<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Common\UrlTest.
 */

namespace Drupal\system\Tests\Common;

use Drupal\simpletest\WebTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests for URL generation functions.
 *
 * url() calls Drupal::moduleHandler()->getImplementations(),
 * which may issue a db query, which requires
 * inheriting from a web test case rather than a unit test case.
 */
class UrlTest extends WebTestBase {

  public static $modules = array('common_test');

  public static function getInfo() {
    return array(
      'name' => 'URL generation tests',
      'description' => 'Confirm that url(), drupal_get_query_parameters(), drupal_http_build_query(), and l() work correctly with various input.',
      'group' => 'Common',
    );
  }

  /**
   * Confirms that invalid URLs are filtered in link generating functions.
   */
  function testLinkXSS() {
    // Test l().
    $text = $this->randomName();
    $path = "<SCRIPT>alert('XSS')</SCRIPT>";
    $link = l($text, $path);
    $sanitized_path = check_url(url($path));
    $this->assertTrue(strpos($link, $sanitized_path) !== FALSE, format_string('XSS attack @path was filtered by l().', array('@path' => $path)));

    // Test #type 'link'.
    $link_array =  array(
      '#type' => 'link',
      '#title' => $this->randomName(),
      '#href' => $path,
    );
    $type_link = drupal_render($link_array);
    $sanitized_path = check_url(url($path));
    $this->assertTrue(strpos($type_link, $sanitized_path) !== FALSE, format_string('XSS attack @path was filtered by #theme', array('@path' => $path)));
  }

  /**
   * Tests for active class in links produced by l() and #type 'link'.
   */
  function testLinkActiveClass() {
    $options_no_query = array();
    $options_query = array(
      'query' => array(
        'foo' => 'bar',
        'one' => 'two',
      ),
    );
    $options_query_reverse = array(
      'query' => array(
        'one' => 'two',
        'foo' => 'bar',
      ),
    );

    // Test #type link.
    $path = 'common-test/type-link-active-class';

    $this->drupalGet($path, $options_no_query);
    $links = $this->xpath('//a[@href = :href and contains(@class, :class)]', array(':href' => url($path, $options_no_query), ':class' => 'active'));
    $this->assertTrue(isset($links[0]), 'A link generated by l() to the current page is marked active.');

    $links = $this->xpath('//a[@href = :href and not(contains(@class, :class))]', array(':href' => url($path, $options_query), ':class' => 'active'));
    $this->assertTrue(isset($links[0]), 'A link generated by l() to the current page with a query string when the current page has no query string is not marked active.');

    $this->drupalGet($path, $options_query);
    $links = $this->xpath('//a[@href = :href and contains(@class, :class)]', array(':href' => url($path, $options_query), ':class' => 'active'));
    $this->assertTrue(isset($links[0]), 'A link generated by l() to the current page with a query string that matches the current query string is marked active.');

    $links = $this->xpath('//a[@href = :href and contains(@class, :class)]', array(':href' => url($path, $options_query_reverse), ':class' => 'active'));
    $this->assertTrue(isset($links[0]), 'A link generated by l() to the current page with a query string that has matching parameters to the current query string but in a different order is marked active.');

    $links = $this->xpath('//a[@href = :href and not(contains(@class, :class))]', array(':href' => url($path, $options_no_query), ':class' => 'active'));
    $this->assertTrue(isset($links[0]), 'A link generated by l() to the current page without a query string when the current page has a query string is not marked active.');
  }

  /**
   * Tests for custom class in links produced by l() and #type 'link'.
   */
  function testLinkCustomClass() {
    // Test l().
    $class_l = $this->randomName();
    $link_l = l($this->randomName(), current_path(), array('attributes' => array('class' => array($class_l))));
    $this->assertTrue($this->hasClass($link_l, $class_l), format_string('Custom class @class is present on link when requested by l()', array('@class' => $class_l)));

    // Test #type.
    $class_theme = $this->randomName();
    $type_link = array(
      '#type' => 'link',
      '#title' => $this->randomName(),
      '#href' => current_path(),
      '#options' => array(
        'attributes' => array(
          'class' => array($class_theme),
        ),
      ),
    );
    $link_theme = drupal_render($type_link);
    $this->assertTrue($this->hasClass($link_theme, $class_theme), format_string('Custom class @class is present on link when requested by #type', array('@class' => $class_theme)));
  }

  /**
   * Tests that link functions support render arrays as 'text'.
   */
  function testLinkRenderArrayText() {
    // Build a link with l() for reference.
    $l = l('foo', 'http://drupal.org');

    // Test a renderable array passed to l().
    $renderable_text = array('#markup' => 'foo');
    $l_renderable_text = l($renderable_text, 'http://drupal.org');
    $this->assertEqual($l_renderable_text, $l);

    // Test a themed link with plain text 'text'.
    $type_link_plain_array = array(
      '#type' => 'link',
      '#title' => 'foo',
      '#href' => 'http://drupal.org',
    );
    $type_link_plain = drupal_render($type_link_plain_array);
    $this->assertEqual($type_link_plain, $l);

    // Build a themed link with renderable 'text'.
    $type_link_nested_array = array(
      '#type' => 'link',
      '#title' => array('#markup' => 'foo'),
      '#href' => 'http://drupal.org',
    );
    $type_link_nested = drupal_render($type_link_nested_array);
    $this->assertEqual($type_link_nested, $l);
  }

  /**
   * Checks for class existence in link.
   *
   * @param $link
   *   URL to search.
   * @param $class
   *   Element class to search for.
   *
   * @return bool
   *   TRUE if the class is found, FALSE otherwise.
   */
  private function hasClass($link, $class) {
    return preg_match('|class="([^\"\s]+\s+)*' . $class . '|', $link);
  }

  /**
   * Tests drupal_get_query_parameters().
   */
  function testDrupalGetQueryParameters() {
    $original = array(
      'a' => 1,
      'b' => array(
        'd' => 4,
        'e' => array(
          'f' => 5,
        ),
      ),
      'c' => 3,
    );

    // First-level exclusion.
    $result = $original;
    unset($result['b']);
    $this->assertEqual(drupal_get_query_parameters($original, array('b')), $result, "'b' was removed.");

    // Second-level exclusion.
    $result = $original;
    unset($result['b']['d']);
    $this->assertEqual(drupal_get_query_parameters($original, array('b[d]')), $result, "'b[d]' was removed.");

    // Third-level exclusion.
    $result = $original;
    unset($result['b']['e']['f']);
    $this->assertEqual(drupal_get_query_parameters($original, array('b[e][f]')), $result, "'b[e][f]' was removed.");

    // Multiple exclusions.
    $result = $original;
    unset($result['a'], $result['b']['e'], $result['c']);
    $this->assertEqual(drupal_get_query_parameters($original, array('a', 'b[e]', 'c')), $result, "'a', 'b[e]', 'c' were removed.");
  }

  /**
   * Tests drupal_parse_url().
   */
  function testDrupalParseUrl() {
    // Relative, absolute, and external URLs, without/with explicit script path,
    // without/with Drupal path.
    foreach (array('', '/', 'http://drupal.org/') as $absolute) {
      foreach (array('', 'index.php/') as $script) {
        foreach (array('', 'foo/bar') as $path) {
          $url = $absolute . $script . $path . '?foo=bar&bar=baz&baz#foo';
          $expected = array(
            'path' => $absolute . $script . $path,
            'query' => array('foo' => 'bar', 'bar' => 'baz', 'baz' => ''),
            'fragment' => 'foo',
          );
          $this->assertEqual(drupal_parse_url($url), $expected, 'URL parsed correctly.');
        }
      }
    }

    // Relative URL that is known to confuse parse_url().
    $url = 'foo/bar:1';
    $result = array(
      'path' => 'foo/bar:1',
      'query' => array(),
      'fragment' => '',
    );
    $this->assertEqual(drupal_parse_url($url), $result, 'Relative URL parsed correctly.');

    // Test that drupal can recognize an absolute URL. Used to prevent attack vectors.
    $url = 'http://drupal.org/foo/bar?foo=bar&bar=baz&baz#foo';
    $this->assertTrue(url_is_external($url), 'Correctly identified an external URL.');

    // Test that drupal_parse_url() does not allow spoofing a URL to force a malicious redirect.
    $parts = drupal_parse_url('forged:http://cwe.mitre.org/data/definitions/601.html');
    $this->assertFalse(valid_url($parts['path'], TRUE), 'drupal_parse_url() correctly parsed a forged URL.');
  }

  /**
   * Tests external URL handling.
   */
  function testExternalUrls() {
    $test_url = 'http://drupal.org/';

    // Verify external URL can contain a fragment.
    $url = $test_url . '#drupal';
    $result = url($url);
    $this->assertEqual($url, $result, 'External URL with fragment works without a fragment in $options.');

    // Verify fragment can be overidden in an external URL.
    $url = $test_url . '#drupal';
    $fragment = $this->randomName(10);
    $result = url($url, array('fragment' => $fragment));
    $this->assertEqual($test_url . '#' . $fragment, $result, 'External URL fragment is overidden with a custom fragment in $options.');

    // Verify external URL can contain a query string.
    $url = $test_url . '?drupal=awesome';
    $result = url($url);
    $this->assertEqual($url, $result, 'External URL with query string works without a query string in $options.');

    // Verify external URL can be extended with a query string.
    $url = $test_url;
    $query = array($this->randomName(5) => $this->randomName(5));
    $result = url($url, array('query' => $query));
    $this->assertEqual($url . '?' . http_build_query($query, '', '&'), $result, 'External URL can be extended with a query string in $options.');

    // Verify query string can be extended in an external URL.
    $url = $test_url . '?drupal=awesome';
    $query = array($this->randomName(5) => $this->randomName(5));
    $result = url($url, array('query' => $query));
    $this->assertEqual($url . '&' . http_build_query($query, '', '&'), $result, 'External URL query string can be extended with a custom query string in $options.');
  }
}
