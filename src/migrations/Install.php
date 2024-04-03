<?php

namespace solvras\craftgoogledocsapi\migrations;

use Craft;
use craft\db\Migration;

/**
 * m240227_082554_create_google_docs_table migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        // Check if the table already exists
        if (!$this->db->tableExists('{{%google-docs-api_googledocs}}')) {
            $this->createTable('{{%google-docs-api_googledocs}}', [
                'id' => $this->primaryKey(),
                'googleDocId' => $this->string()->notNull(),
                'entryId' => $this->integer()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            //Add indexes to googleDocId
            $this->createIndex(
                null,
                '{{%google-docs-api_googledocs}}',
                ['googleDocId'],
                true
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): void
    {
        $this->dropTableIfExists('{{%google-docs-api_googledocs}}');
    }
}
