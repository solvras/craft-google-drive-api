<?php

namespace solvras\craftgoogledocsapi\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;
use Google\Client as GoogleClient;
use solvras\craftgoogledocsapi\services\GoogleDocsService;

/**
 * Api controller
 */
class ApiController extends Controller
{
    public $defaultAction = 'index';
    // protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;
    protected $allowAnonymous = ['save-document'];

    /**
     * google-docs-api/api action
     */
    public function actionIndex(): Response
    {
        // ...
    }

    /**
     * google-docs-api/api/save-document action
     * @return Response
     */
    public function actionSaveDocument() : Response
    {
        $this->requirePostRequest();
        $siteBaseUrl = Craft::$app->getSites()->currentSite->getBaseUrl();
        $subFolder = parse_url($siteBaseUrl, PHP_URL_HOST);

        $postData = Craft::$app->getRequest()->getBodyParams();

        if (empty($postData)) {
            return $this->asJson([
                'error' => 'No data received'
            ]);
        }

        $subFolderName = $postData['section'] ?? null;
        $fileName = $postData['slug'] ?? null;
        $entryId = $postData['entryId'] ?? null;
        $url = $postData['url'] ?? null;
        $htmlContent = file_get_contents($url);

        if (!$fileName|| !$entryId) {
            return $this->asJson([
                'error' => 'Missing required data'
            ]);
        }

        $googleClient = new GoogleClient();
        $googleDocsService = new GoogleDocsService($googleClient);

        // Get parent folder ID
        $parentFolderId = $googleDocsService->getParentFolderId();

        // Create folder if it doesn't exists
        $folderId = $googleDocsService->createSiteFolder($parentFolderId, $subFolder, $subFolderName);

        // Check if the file already exists
        $response = $googleDocsService->updateOrCreateFile($folderId, $fileName, $htmlContent, $entryId);
        return $this->asJson($response);
    }
}
