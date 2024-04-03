<?php

namespace solvras\craftgoogledocsapi\records;

use Craft;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

/**
 * Google Docs record
 */
class GoogleDocs extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%google-docs-api_googledocs}}';
    }
}
