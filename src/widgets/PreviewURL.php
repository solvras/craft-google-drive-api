<?php

namespace solvras\craftgoogledocsapi\widgets;

use Craft;
use craft\base\Widget;

class PreviewURL extends Widget
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Preview');
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {
        $view = Craft::$app->getView();
 
        return $view->renderTemplate('google-docs-api/_components/widgets/PreviewURL/body',[], Craft::$app->view::TEMPLATE_MODE_CP);
    } 
}
