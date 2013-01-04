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
            <div>
                <?php if ($success === true): ?>
                    <div class="alert alert-success">
                        Successfully updated site!
                    </div>
                <?php elseif ($success): ?>
                    <div class="alert alert-error">
                        There's been an error: <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                    <p>
                        <label>
                            Please select the folder you'd like to use for the site
                            <select name="folder">
                                <?php foreach ($folders as $folder): ?>
                                    <option value="<?php echo $folder->id; ?>"><?php echo $folder->title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </p>
                    <?php if ($files): ?>
                        <p>
                            <label>
                                Please select the file you'd like to use as the home page
                                <select name="file">
                                    <?php foreach ($files as $file): ?>
                                        <option value="<?php echo $file->id; ?>"><?php echo $file->title; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </p>
                    <?php endif; ?>
                    <button type="submit">
                        <?php echo $files ? 'Update' : 'Continue'; ?>
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>