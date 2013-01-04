<?php
function build_menu($pages)
{
    ?>
    <ul>
    <?php
    foreach ($pages as $page)
    {
        ?>
            <li>
                <a href="<?php echo URL_PREFIX; ?>" title="<?php echo $page->title; ?>"><?php echo $page->title; ?></a>
                <?php
                if (property_exists($page, 'children'))
                {
                    echo build_menu($page->children);
                }
                ?>
            </li>
        <?php
    }
    ?>
    </ul>
    <?php
}
?>
<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0" />
        <link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
        <title><?php echo $title; ?></title>
    </head>
    <body class="page page-<?php echo $name; ?>">
        <div id="container">
            <div id="menu">
                <?php echo build_menu($pages); ?>
            </div>
            <div id="content">
                <h1><?php echo $title; ?></h1>
                <?php echo $body; ?>
            </div>
        </div>
    </body>
</html>