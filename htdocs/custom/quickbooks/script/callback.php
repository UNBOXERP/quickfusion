<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/quickbooks/inc/vendor/autoload.php';
use QuickBooksOnline\API\DataService\DataService;

session_start();

function processCode()
{
   global $conf;
    $dataService = DataService::Configure(array(
		'auth_mode' => 'oauth2',
		'ClientID' => $conf->global->QUICKBOOKS_CLIENTID,
		'ClientSecret' => $conf->global->QUICKBOOKS_CLIENTSECRET,
		'RedirectURI' => $conf->global->QUICKBOOKS_OAUTHREDIRECT,
		'scope' => $conf->global->QUICKBOOKS_OAUTHSCOPE,
		'baseUrl' => "development"
    ));

    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $parseUrl = parseAuthRedirectUrl(htmlspecialchars_decode($_SERVER['QUERY_STRING']));

    /*
     * Update the OAuth2Token
     */
    $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
    $dataService->updateOAuth2Token($accessToken);

    /*
     * Setting the accessToken for session variable
     */
    $_SESSION['sessionAccessToken'] = $accessToken;
}

function parseAuthRedirectUrl($url)
{
    parse_str($url,$qsArray);
    return array(
        'code' => $qsArray['code'],
        'realmId' => $qsArray['realmId']
    );
}

$result = processCode();

?>
