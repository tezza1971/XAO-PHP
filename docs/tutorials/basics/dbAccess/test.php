<html>
    <head>
    	<title>dbSim</title>
    </head>
    <body>
        <h1>Random DB results (dbSim)</h1>
        <pre><?php
            include_once "dbSim.php";
            $objDb = new dbSim;
            var_dump($objDb->arrGetResults());
        ?></pre>
    </body>
</html>
