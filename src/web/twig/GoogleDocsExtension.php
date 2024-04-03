<?php

namespace solvras\craftgoogledocsapi\web\twig;

use Craft;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use solvras\craftgoogledocsapi\records\GoogleDocs;

/**
 * Twig extension
 */
class GoogleDocsExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('getEntriesWithGoogleDocs', [$this, 'getEntriesWithGoogleDocs']),
        ];
    }


    /**
     * Get entries with Google Docs
     */
    public function getEntriesWithGoogleDocs()
    {
        $result = [];
        $googleDocs = GoogleDocs::find()->where(['not', ['googleDocId' => null]])->all();

        foreach ($googleDocs as $googleDoc) {
            $result[$googleDoc->entryId] = $googleDoc->googleDocId;
        }

        return $result;
    }
}
