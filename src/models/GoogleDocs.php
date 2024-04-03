<?php

namespace solvras\craftgoogledocsapi\models;

use Craft;
use craft\base\Model;

/**
 * Google Docs model
 */
class GoogleDocs extends Model
{
    /**
     * @var string
     */
    public string $googleDocId;
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules() : array
    {
        return [
            [['googleDocId'], 'required'],
        ];
    }
}
