<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
include('../config.php');

use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;

$dataService = DataService::Configure(array(
       'auth_mode' => 'oauth2',
         'ClientID' => "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
         'ClientSecret' => "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
         'accessTokenKey' =>  "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX..XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
         'refreshTokenKey' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
         'QBORealmID' => "xxxxxxxxxxxxxxxxxxx",
         'baseUrl' => "development"
));

$dataService->setLogLocation("/Users/bsivalingam/Desktop/newFolderForLog");


$resultingCustomerObj = $dataService->getEntitlementsResponse();
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
} else {
    var_dump($resultingCustomerObj);
}
