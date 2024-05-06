<?php

namespace solvras\craftgoogledocsapi;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Dashboard;
use yii\base\Event;
use solvras\craftgoogledocsapi\models\Settings;
use solvras\craftgoogledocsapi\services\GoogleDocsService;
use solvras\craftgoogledocsapi\migrations\m240227_082554_create_google_docs_table as GoogleDriveApiTable;
use solvras\craftgoogledocsapi\web\twig\GoogleDocsExtension;
use solvras\craftgoogledocsapi\widgets\PreviewURL as PreviewURLWidget;

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

        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = PreviewURLWidget::class;
            }
        );
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
    }
}
