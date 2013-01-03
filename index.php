<?php
// get our required files
require_once 'config.php';
require_once 'lib/Google_Client.php';
// require_once 'lib/contrib/Google_DriveService.php'; it's _easier_ to just make rest calls
require_once 'lib/contrib/Google_Oauth2Service.php';

// add any helper functions
function get_the_class_name($path)
{
    $class = str_replace(array(
        'controller/',
        'model/',
        '_'
    ),
    array(
        '',
        '',
        ' '
    ), $path);
    $class = ucwords($class);
    $class = str_replace(' ', '_', $class);

    return $class;
}

// set up our routes
$routes = array(
    '\/admin\/?([^\?]+)(\?.*)?$' => 'controller/admin_controller', // assumes that our admin_controller contains a class called Admin_Controller
    '\/(.*)?$' => 'controller/page_controller'
);

// match our route against the current URL
$url = str_replace(URL_PREFIX, '', $_SERVER['REQUEST_URI']);
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
    // require our base conroller
    require_once 'controller/base_controller.php';

    // require it
    require_once $controller . '.php';

    // get the class name, remember Foo_Controller is the target
    $class = get_the_class_name($controller);

    // disregard the first part of our $matches array
    array_shift($matches);

    // init it and pass matches are the args
    new $class($matches);
}
else
{
    require_once '404.php';
}