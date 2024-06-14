<?php
return array(
  'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2', //Example https://appcenter.intuit.com/connect/oauth2',
  'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer', //Example https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
  'client_id' => 'ABlpY4eUAjGflIVRzoht7rZpN73xm3FanRBuO4wofFJsj2cueo', //Example 'Q0wDe6WVZMzyu1SnNPAdaAgeOAWNidnVRHWYEUyvXVbmZDRUfQ',
  'client_secret' => 'p6t3Pne3PdK4iHlV7ngtQVFPpGOSVEsz3y2sReBG', //Example 'R9IttrvneexLcUZbj3bqpmtsu5uD9p7UxNMorpGd',
  'oauth_scope' => 'com.intuit.quickbooks.accounting com.intuit.quickbooks.payment openid profile email phone address', //Example 'com.intuit.quickbooks.accounting',
  'openID_scope' => 'openid profile email', //Example 'openid profile email',
//  'oauth_redirect_uri' => 'https://9cd7-85-155-224-224.ngrok-free.app/doli15/htdocs/custom/quickbooks/inc/oauth/OAuth_2/OAuth2PHPExample.php', //Example https://d1eec721.ngrok.io/OAuth_2/OAuth2PHPExample.php',
  'oauth_redirect_uri' => 'http://localhost:3480/doli15/htdocs/custom/quickbooks/inc/oauth/OAuth_2/OAuth2PHPExample.php', //Example https://d1eec721.ngrok.io/OAuth_2/OAuth2PHPExample.php',
  'openID_redirect_uri' => 'https://9cd7-85-155-224-224.ngrok-free.app/doli15/htdocs/custom/quickbooks/inc/oauth/OAuth_2/OAuthOpenIDExample.php',//Example 'https://d1eec721.ngrok.io/OAuth_2/OAuthOpenIDExample.php',
  'mainPage' => 'https://9cd7-85-155-224-224.ngrok-free.app/doli15/htdocs/custom/quickbooks/inc/oauth/OAuth_2/index.php', //Example https://d1eec721.ngrok.io/OAuth_2/index.php',
  'refreshTokenPage' => 'https://9cd7-85-155-224-224.ngrok-free.app/doli15/htdocs/custom/quickbooks/inc/oauth/OAuth_2/RefreshToken.php', //Example https://d1eec721.ngrok.io/OAuth_2/RefreshToken.php'
)
?>
