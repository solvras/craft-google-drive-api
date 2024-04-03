<?php

namespace solvras\craftgoogledocsapi;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use solvras\craftgoogledocsapi\models\Settings;
use solvras\craftgoogledocsapi\services\GoogleDocsService;
use solvras\craftgoogledocsapi\migrations\m240227_082554_create_google_docs_table as GoogleDriveApiTable;
use solvras\craftgoogledocsapi\web\twig\GoogleDocsExtension;

/**
 * Google Docs Api  plugin
 *
 * @method static GoogleDocs getInstance()
 * @author Solvr AS <utviklere@solvr.no>
 * @copyright Solvr AS
 * @license MIT
 * @property-read GoogleDocsService $googleDocsService
 */
class GoogleDocs extends Plugin
{
    public $schemaVersion = '1.0.1';
    public $hasCpSettings = false;

    public static function config(): array
    {
        return [
            'components' => ['googleDocsService' => GoogleDocsService::class],
        ];
    }

    public function init(): void
    {
        parent::init();
        Craft::$app->view->registerTwigExtension(new GoogleDocsExtension());
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
    }
}
