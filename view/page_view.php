<?php
function build_menu($items)
{
    ?>
    <ul>
    <?php
    foreach ($items as $item)
    {
        ?>
            <li>
                <a
                    <?php if (!$item->is_folder): ?>
                        href="<?php echo URL_PREFIX; ?>/<?php echo $item->name; ?>"
                    <?php endif; ?>
                    title="<?php echo $item->title; ?>"
                    class="<?php echo $item->is_home ? 'homepage' : ''; ?> <?php echo $item->is_selected ? 'selected' : ''; ?>">
                        <?php echo $item->title; ?>
                </a>
                <?php
                if (property_exists($item, 'children'))
                {
                    echo build_menu($item->children);
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
        <link rel="stylesheet" type="text/css" href="assets/css/vendor/normalize.css" />
        <link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
        <title><?php echo $page->title; ?></title>
    </head>
    <body class="page page-<?php echo $page->name; ?>">
        <div id="container">
            <div id="menu">
                <?php echo build_menu($menu); ?>
            </div>
            <div id="content">
                <h1><?php echo $page->title; ?></h1>
                <?php echo $page->body; ?>
            </div>
        </div>
    </body>
</html>