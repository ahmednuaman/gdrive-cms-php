<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0, minimum-scale=1.0" />
        <link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
        <title>Admin</title>
    </head>
    <body class="admin">
        <div id="container">
            <h1>Admin area</h1>
            <h3>Please select the folder you'd like to use for the site</h3>
            <div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <select name="folder">
                        <?php foreach ($folders as $id => $folder): ?>
                            <option value="<?php echo $id; ?>"><?php echo $folder; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Save</button>
                </form>
            </div>
        </div>
    </body>
</html>