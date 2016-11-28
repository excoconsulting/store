<?php

require_once 'const.php';
require_once 'helper.php';

//Login functions
require_once 'login/load.php';

//Dashboard functions
require_once 'dashboard/load.php';

//Widgets
require_once 'widgets/load.php';

//Shortcodes
require_once 'lib/shortcode-interface.php';
require_once 'shortcodes/login/login.php';
require_once 'shortcodes/register/register.php';
require_once 'shortcodes/reset-password/reset-password.php';

require_once 'lib/shortcode-loader.php';