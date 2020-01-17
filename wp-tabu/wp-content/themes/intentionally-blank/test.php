
<?php 
require("guzzle-master/src/functions_include.php");
require("MrShan0/PHPFirestore/src/FirestoreClient.php");
require("MrShan0/PHPFirestore/src/FirestoreDatabaseResource.php");
require("MrShan0/PHPFirestore/src/FirestoreDocument.php");

$firestoreClient = new MrShan0\PHPFirestore\FirestoreClient('teste-18089', 'e444e1c0e12f358d55c619231c07cc0aeca2ede9', [
    'database' => '(default)',
]);


echo "Heelo";
?>
