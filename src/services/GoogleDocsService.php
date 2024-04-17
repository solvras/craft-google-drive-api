<?php

namespace solvras\craftgoogledocsapi\services;

use Craft;
use craft\helpers\App;
use yii\base\Component;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use solvras\craftgoogledocsapi\records\GoogleDocs;

/**
 * Google Docs Service service
 */
class GoogleDocsService extends Component
{
    /**
     * @var GoogleClient
     */
    private GoogleClient $googleClient;

     /**
     * @var Drive
     */
    private Drive $driveService;

    public function __construct(GoogleClient $googleClient)
    {
        $this->googleClient = $googleClient;
        $this->initializeGoogleClient();
        $this->driveService = $this->getSharedDrive();
    }

    /**
     * Initialize Google Client
     * @return void
     */
    private function initializeGoogleClient(): void
    {
        $this->googleClient->setApplicationName('Google Docs API');
        $this->googleClient->setScopes(
            [
                Drive::DRIVE,
                Drive::DRIVE_FILE,
                Drive::DRIVE_METADATA,
            ]
        );
        
        $credentials = json_decode(App::env('GOOGLE_APPLICATION_CREDENTIALS'), true);
        $this->googleClient->setAuthConfig($credentials);
    }

    /**
     * Get shared drive
     * @return Drive
     */
    private function getSharedDrive(): Drive
    {
        $this->googleClient->fetchAccessTokenWithAssertion();
        return new Drive($this->googleClient);
    }

    /**
     * Get parent folder ID
     * @return string|null
     */
    public function getParentFolderId(): ?string {
        $parentFolderName = App::env('PARENT_FOLDER_NAME');
        $folderList = $this->folderExistsInDrive($parentFolderName);
        return !empty($folderList) ? $folderList[0]->getId() : null;
    }

    /**
     * Create site domain folder in Google Drive
     * @param string $parentFolderId
     * @param string $folderName
     * @return string|null
     */
    public function createSiteFolder(
        string $parentFolderId,
        string $folderName,
        ?string $subFolderName = null
    ): ?string
    {
        $mimeType = 'application/vnd.google-apps.folder';
        $folderId = $this->findOrCreateFolder($parentFolderId, $folderName, $mimeType);
        if($subFolderName) {
            $folderId = $this->findOrCreateFolder($folderId, $subFolderName, $mimeType);
        }
        
        return $folderId;
    }

    /**
     * Create file in Google Drive
     * @param string $folderId
     * @param string $fileName
     * @param string $htmlContent
     * @param string $entryId
     * @return array
     */
    public function updateOrCreateFile(
        string $folderId,
        string $fileName,
        string $htmlContent,
        string $entryId
    ): array
    {
        $existingFile = $this->fileExistsInDrive($fileName, $folderId);
        $metaData = new Drive\DriveFile([
            'name' => $fileName,
            'driveId' => App::env('GOOGLE_SHARED_DRIVE_ID'),
            'mimeType' => 'application/vnd.google-apps.document',
        ]);

        $params = [
            'data' => $htmlContent,
            'mimeType' => 'text/html',
            'uploadType' => 'media',
            'supportsAllDrives' => true
        ];

        $operation = empty($existingFile) ? 'create' : 'update';

        if ($operation === 'create') {
            $metaData->setParents([$folderId]);
        }

        $result = $operation === 'create' 
            ? $this->driveService->files->create($metaData, $params)
            : $this->driveService->files->update($existingFile[0]->getId(), $metaData, $params);

        if (!$result) {
            return [
                'googleDocId' => null,
                'message' => "Failed to $operation the file."
            ];
        }

        $googleDocId = $result->getId();
        $this->saveGoogleDocToDatabase($googleDocId, $entryId);

        return [
            'googleDocId' => $googleDocId,
            'message' => "File $operation was successful."
        ];
    }

    /**
     * Find or create folder
     * @param string $parentFolderId
     * @param string $folderName
     * @param string $mimeType
     * @return string|null
     */
    private function findOrCreateFolder(
        string $parentFolderId,
        string $folderName,
        string $mimeType
    ): ?string {
        $existingFolder = $this->folderExistsInDrive($folderName, $parentFolderId, $mimeType);
        if (!empty($existingFolder)) {
            return $existingFolder[0]->getId();
        }

        $metaData = new Drive\DriveFile([
            'name' => $folderName,
            'driveId' => App::env('GOOGLE_SHARED_DRIVE_ID'),
            'mimeType' => $mimeType,
            'parents' => [$parentFolderId]
        ]);

        try {
            $folder = $this->driveService->files->create($metaData, array(
                'supportsAllDrives' => true,
            ));
            return $folder->id;
        } catch (\Exception $e) {
            Craft::dd($e->getMessage());
        }
        return $folder->id;
    }

    /**
     * Save googleDocId to database
     * @param string $googleDocId
     * @return void
     */
    private function saveGoogleDocToDatabase(
        string $googleDocId,
        string $entryId
    ) : void {
        $existingRecord = GoogleDocs::findOne(['googleDocId' => $googleDocId]);
        if ($existingRecord !== null) {
            $existingRecord->googleDocId = $googleDocId;
            $existingRecord->entryId = $entryId;
            $existingRecord->update();
        } else {
            $googleDocRecord = new GoogleDocs();
            $googleDocRecord->googleDocId = $googleDocId;
            $googleDocRecord->entryId = $entryId;
            $googleDocRecord->save();
        }
    }
    
    /**
     * Check if file or folder exists in Google Drive
     * @param string $folderId
     * @param string $name
     * @param string|null $mimeType
     * @return array
     */
    private function folderExistsInDrive(
        string $name,
        ?string $folderId = null,
        ?string $mimeType = null
    ): array {
        $query = "mimeType='$mimeType' and name='$name' and '$folderId' in parents";
        $mixins = [
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
            'driveId' => App::env('GOOGLE_SHARED_DRIVE_ID'),
            'corpora' => 'drive'
        ];
        $params =  $folderId ? array_merge([
            'q' => $query,
            'fields' => 'files(id)',
        ], $mixins) : array_merge([
            'q' => "mimeType='application/vnd.google-apps.folder' and name='$name'",
        ], $mixins);
        $result = $this->driveService->files->listFiles($params);
        return $result->getFiles();
    }

    /**
     * Check if file or folder exists in Google Drive
     * @param string $name
     * @param string $folderId
     */
    private function fileExistsInDrive(
        string $name,
        string $folderId
    ): array {
        $params = [
            'q' => "'$folderId' in parents and name = '$name' and trashed = false",
            'fields' => 'files(id)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
            'driveId' => App::env('GOOGLE_SHARED_DRIVE_ID'),
            'corpora' => 'drive'
        ];
        $result = $this->driveService->files->listFiles($params);
        return $result->getFiles();
    }
}
