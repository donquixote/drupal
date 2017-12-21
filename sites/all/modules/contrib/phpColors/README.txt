INTRODUCTION
------------

This module provides library and xautoload integration for the phpColors
library. The phpColors library provides an easy-to-use PHP class for dealing
with color manipulation.

REQUIREMENTS
------------
libraries https://drupal.org/project/libraries
xautoload https://drupal.org/project/xautoload
phpColors http://mexitek.github.io/phpColors

INSTALLATION
------------

 1. Install as usual, see http://drupal.org/node/70151 for further information.

 2. Download the phpColors library from http://mexitek.github.io/phpColors/ and
    install into sites/all/libraries/phpcolors.

    git clone git://github.com/mexitek/phpColors.git sites/all/libraries/phpcolors

USAGE
-----

<?php

using phpColors\Color;

// Initialize my color.
$my_blue = new Color("#336699");

echo $my_blue->darken();
// 1a334d
?>

See http://mexitek.github.io/phpColors/ for more in depth documentation.

CONTACT
-------

Current maintainers:
* Steven Wichers (steven.wichers) - http://drupal.org/user/1774136

This project has been sponsored by:
* McMurry/TMG
  McMurry/TMG is a world-leading, results-focused content marketing firm. We
  leverage the power of world-class content — in the form of the broad
  categories of video, websites, print and mobile — to keep our clients’ brands
  top of mind with their customers.  Visit http://www.mcmurry.com for more
  information.
