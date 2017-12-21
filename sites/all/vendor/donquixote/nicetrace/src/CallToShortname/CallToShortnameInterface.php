<?php

namespace Donquixote\Nicetrace\CallToShortname;

interface CallToShortnameInterface {

  /**
   * @param array $call
   *
   * @return string
   */
  function callGetShortname(array $call);

}
