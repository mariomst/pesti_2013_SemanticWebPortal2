<?php
header('Content-Type: text/html; charset=utf-8');
?>

<html>
    <head>
        <link href="/assets/images/rdf.ico" rel="shortcut icon" type="image/x-icon" />
        <title>
            <?php echo $title ?>
        </title>
    </head>
    <body>
        <h1><?php echo $title ?></h1>
        <?php echo $this->unit->report(); ?>
    </body>
</html>

