<?php
// get our required files
require_once 'config.php';
require_once 'lib/Google_Client.php';

// set up our routes
$routes = array(
    '\/admin\/?([^\?]+)(\?.*)?$' => 'controller/admin_controller', // assumes that our admin_controller contains a class called Admin_Controller
    '\/(.*)?$' => 'controller/page_controller'
);

// currently my demo sits in a directory (called gdrive-cms-php) so let's strip this route prefix from our url
$url_prefix = '/gdrive-cms-php';

// match our route against the current URL
$url = str_replace($url_prefix, '', $_SERVER['REQUEST_URI']);
$controller = null;
$matches = null;

// loop through our routes
foreach ($routes as $route => $controller)
{
    if (preg_match('/' . $route . '/', $url, $matches))
    {
        break;
    }
}

// can we haz controller?
if ($controller !== null && $matches !== null)
{
    // require it
    require_once $controller . '.php';

    // get the class name, remember Foo_Controller is the target
    $class = str_replace(array(
        'controller/',
        '_'
    ),
    array(
        '',
        ' '
    ), $controller);
    $class = ucwords($class);
    $class = str_replace(' ', '_', $class);

    // disregard the first part of our $matches array
    array_shift($matches);

    // init it and pass matches are the args
    new $class($matches);
}
else
{
    require_once '404.php';
}