<?php

namespace Donquixote\CallbackReflection\Util;

final class CodegenUtil extends UtilBase {

  /**
   * @param string[] $argsPhp
   * @param string $indentation
   *   E.g. '  '.
   *
   * @return string
   */
  public static function argsPhpGetArglistPhp(array $argsPhp, $indentation) {
    if (array() === $argsPhp) {
      return '';
    }
    else {
      return "\n  " . self::indent(implode(",\n", $argsPhp), $indentation);
    }
  }

  /**
   * @param string $php
   * @param string $indentation
   *
   * @return mixed
   */
  public static function indent($php, $indentation) {
    $tokens = token_get_all('<?php' . "\n" . $php);
    array_shift($tokens);
    $out = '';
    foreach ($tokens as $token) {
      if (is_string($token)) {
        $out .= $token;
      }
      elseif ($token[0] !== T_WHITESPACE && $token[0] !== T_DOC_COMMENT && $token[0] !== T_COMMENT) {
        $out .= $token[1];
      }
      else {
        $out .= str_replace("\n", "\n" . $indentation, $token[1]);
      }
    }
    return $out;
  }

  /**
   * @param string $php
   * @param string $indent_level
   *
   * @return string
   */
  public static function autoIndent($php, $indent_level, $indent_base = '') {
    $tokens = token_get_all('<?php' . "\n" . $php);
    array_shift($tokens);
    $tokens[] = '#';

    $i = 0;
    return self::doAutoIndent($tokens, $i, $indent_base, $indent_level);
  }

  /**
   * @param array $tokens
   * @param int $i
   * @param string $indent_base
   * @param string $indent_level
   *
   * @return string
   */
  private static function doAutoIndent(array $tokens, &$i, $indent_base, $indent_level) {

    $out = '';
    while (TRUE) {
      $token = $tokens[$i];

      if (is_string($token)) {

        switch ($token) {

          case '{':
          case '(':
          case '[':
            $out .= $token;
            $out .= self::doAutoIndent($tokens, ++$i, $indent_base . $indent_level, $indent_level);
            break;

          case '}':
          case ')':
          case ']':
            $out .= $token;
            ++$i;
            return $out;

          case '#':
            return $out;
        }
      }
      else {
        switch ($token[0]) {

          case T_WHITESPACE:
            $snippet = preg_replace("@\n *@", "\n" . $indent_base, $token[1]);
            break;

          case T_DOC_COMMENT:
          case T_COMMENT:
            $snippet = preg_replace("@\n *\\*@", "\n" . $indent_base . ' *', $token[1]);
            break;

          default:
            $out .= $token[1];
            continue 2;
        }

        // Remove trailing whitespace.
        $snippet = preg_replace("@ *\n@", '', $snippet);

        $out .= $snippet;
      }

      ++$i;
    }

    return $out;
  }
}
