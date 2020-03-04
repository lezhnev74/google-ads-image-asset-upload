<?php
declare(strict_types=1);


require 'vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->load();

$clientId = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$developerToken = getenv('GOOGLE_ADWORDS_DEV_TOKEN');
$refreshToken = getenv('GOOGLE_ACCOUNT_REFRESH_TOKEN');
$customerId = getenv('GOOGLE_ADS_CUSTOMER_ID');


// Prepare Google Client
$log = new \Monolog\Logger('google');
$log->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG));


$credentials = (new \Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder())
    ->withClientId($clientId)
    ->withClientSecret($clientSecret)
    ->withRefreshToken($refreshToken)
    ->build();

$client = (new \Google\Ads\GoogleAds\Lib\V3\GoogleAdsClientBuilder())
    ->withDeveloperToken($developerToken)
    ->withOAuth2Credential($credentials)
    ->withLogger($log)
    ->withLogLevel('debug')
    ->build();

// Prepare image
$imageBinary = file_get_contents('image.jpg');
$imageBase64 = base64_encode($imageBinary);

// Upload an image
$asset = new \Google\Ads\GoogleAds\V3\Resources\Asset([
    'type' => \Google\Ads\GoogleAds\V3\Enums\AssetTypeEnum\AssetType::IMAGE,
    'image_asset' => new \Google\Ads\GoogleAds\V3\Common\ImageAsset([
        'data' => new \Google\Protobuf\BytesValue(['value' => $imageBase64]),
    ]),
]);
$assetOperation = new \Google\Ads\GoogleAds\V3\Services\AssetOperation();
$assetOperation->setCreate($asset);

// Issues a mutate request to add the asset.
$assetServiceClient = $client->getAssetServiceClient();
$response = $assetServiceClient->mutateAssets($customerId, [$assetOperation]);
