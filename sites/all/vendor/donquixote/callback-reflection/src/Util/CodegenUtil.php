<?php

namespace Donquixote\CallbackReflection\Util;

/**
 * @todo This should probably be in a separate library.
 * But for now it is good enough to have it here.
 */
final class CodegenUtil extends UtilBase {

  /**
   * @param string[] $argsPhp
   *
   * @return string
   */
  public static function argsPhpGetArglistPhp(array $argsPhp) {

    if (array() === $argsPhp) {
      return '';
    }

    $oneline = implode(', ', $argsPhp);
    if (FALSE === strpos($oneline, "\n") && strlen($oneline) < 30) {
      return $oneline;
    }

    return "\n" . implode(",\n", $argsPhp);
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
   * Replaces long class names with aliases.
   *
   * @param string $php
   *   PHP code without the leading <?php.
   *
   * @return mixed[]
   *   Format: $[$class] = $alias|true
   */
  public static function aliasify(&$php) {
    $tokens = token_get_all('<?php' . "\n" . $php . "\n");
    $tokens[] = '#';

    $map = [];
    foreach ($tokens as $i => $token) {

      if (T_NEW === $token[0] && T_WHITESPACE === $tokens[$i + 1][0]) {

        $name = '';
        for ($j = $i + 2; TRUE; ++$j) {

          switch ($tokens[$j][0]) {

            case T_NS_SEPARATOR:
            case T_STRING:
              $name .= $tokens[$j][1];
              break;

            case T_WHITESPACE:
              break;

            default:
              break 2;
          }
        }

        if ('' !== $name && FALSE === strpos($name . '\\', '\\\\')) {
          if ('\\' === $name[0]) {
            $name = substr($name, 1);
          }
          $map[$name][] = [$i + 2, $j - 1];
        }
      }
      elseif (T_DOUBLE_COLON === $token[0]) {

        $name = '';
        for ($j = $i - 1; TRUE; --$j) {

          switch ($tokens[$j][0]) {

            case T_NS_SEPARATOR:
            case T_STRING:
              $name = $tokens[$j][1] . $name;
              break;

            case T_WHITESPACE:
              break;

            default:
              break 2;
          }
        }

        if ('' !== $name && FALSE === strpos($name . '\\', '\\\\')) {
          if ('\\' === $name[0]) {
            $name = substr($name, 1);
          }
          $map[$name][] = [$j + 1, $i - 1];
        }
      }
    }

    $mm = [];
    $abs = [];
    foreach ($map as $class => $positions) {
      if (FALSE !== $pos = strrpos($class, '\\')) {
        $shortname = substr($class, $pos + 1);
        $mm[$shortname][$class] = $positions;
      }
      else {
        $abs['\\' . $class] = $positions;
      }
    }

    $alias_map = [];
    foreach ($mm as $alias_base => $classes) {
      $alias = $alias_base;
      $i_alias_variation = 0;
      foreach ($classes as $class => $positions) {
        $alias_map[$class] = (0 === $i_alias_variation) ? TRUE : $alias;
        foreach ($positions as $i01) {
          list($i0, $i1) = $i01;
          $tokens[$i1] = $alias;
          for ($i = $i0; $i < $i1; ++$i) {
            if (T_WHITESPACE !== $tokens[$i][0]) {
              $tokens[$i] = '';
            }
          }
        }
        ++$i_alias_variation;
        $alias = $alias_base . '_' . $i_alias_variation;
      }
    }

    ksort($alias_map);

    foreach ($abs as $fqcn => $positions) {
      foreach ($positions as $i01) {
        list($i0, $i1) = $i01;
        $tokens[$i1] = $fqcn;
        for ($i = $i0; $i < $i1; ++$i) {
          if (T_WHITESPACE !== $tokens[$i][0]) {
            $tokens[$i] = '';
          }
        }
      }
    }

    array_shift($tokens);
    array_pop($tokens);
    array_pop($tokens);

    $php = '';
    foreach ($tokens as $token) {
      if (is_string($token)) {
        $php .= $token;
      }
      else {
        $php .= $token[1];
      }
    }

    return $alias_map;
  }

  /**
   * @param string $php
   * @param string $indent_level
   * @param string $indent_base
   *
   * @return string
   */
  public static function autoIndent($php, $indent_level, $indent_base = '') {
    $tokens = token_get_all('<?php' . "\n" . $php);
    $tokens[] = [T_WHITESPACE, "\n"];
    $tokens[] = '#';
    $tokens = self::prepareTokens($tokens);

    $i = 1;
    $out = [''];
    self::doAutoIndent($out, $tokens, $i, $indent_base, $indent_level);

    array_pop($out);

    return implode('', $out);
  }

  /**
   * @param array $tokens_original
   *
   * @return array
   */
  private static function prepareTokens(array $tokens_original) {

    $tokens_prepared = [];
    for ($i = 0; TRUE; ++$i) {
      $token = $tokens_original[$i];
      if (T_COMMENT === $token[0]) {
        if ("\n" === substr($token[1], -1)) {
          $tokens_prepared[] = [T_COMMENT, substr($token[1], 0, -1)];
          if (T_WHITESPACE === $tokens_original[$i + 1][0]) {
            $tokens_prepared[] = [T_WHITESPACE, "\n" . $tokens_original[$i + 1][1]];
            ++$i;
          }
          else {
            $tokens_prepared[] = [T_WHITESPACE, "\n"];
          }
          continue;
        }
      }

      $tokens_prepared[] = $token;

      if ('#' === $token) {
        break;
      }
    }

    return $tokens_prepared;
  }

  /**
   * @param string[] $out
   * @param array $tokens
   * @param int $i
   * @param string $indent_base
   * @param string $indent_level
   */
  private static function doAutoIndent(array &$out, array $tokens, &$i, $indent_base, $indent_level) {

    $indent_deeper = $indent_base . $indent_level;

    while (TRUE) {
      $token = $tokens[$i];

      if (is_string($token)) {

        switch ($token) {

          case '{':
          case '(':
          case '[':
            $out[] = $token;
            ++$i;
            self::doAutoIndent($out, $tokens, $i, $indent_deeper, $indent_level);
            if ('#' === $tokens[$i]) {
              return;
            }
            if (T_WHITESPACE === $tokens[$i - 1][0]) {
              $out[$i - 1] = str_replace($indent_deeper, $indent_base, $out[$i - 1]);
            }
            break;

          case '}':
          case ')':
          case ']':
            $out[] = $token;
            return;

          case '#':
            return;

          default:
            $out[] = $token;
            break;
        }
      }
      else {
        switch ($token[0]) {

          case T_WHITESPACE:
            $n_linebreaks = substr_count($token[1], "\n");
            if (0 === $n_linebreaks) {
              $out[] = $token[1];
              ++$i;
              continue 2;
            }
            $out[] = str_repeat("\n", $n_linebreaks) . $indent_base;
            break;

          case T_COMMENT:
          case T_DOC_COMMENT:
            # $out[] = $token[1];
            $out[] = preg_replace("@ *\\n *\\*@", "\n" . $indent_base . ' *', $token[1]);
            break;

          default:
            $out[] = $token[1];
            break;
        }
      }

      ++$i;
    }
  }

  /**
   * @param array $tokens
   *
   * @return string
   */
  public static function showTokens(array $tokens) {

    $show = [];
    foreach ($tokens as $i => $token) {
      if (is_string($token)) {
        $show[] = var_export($token, TRUE);
      }
      else {
        $show[] = var_export($token[1], TRUE) . ' ==== ' . token_name($token[0]);
      }
    }

    return implode("\n", $show);
  }
}
