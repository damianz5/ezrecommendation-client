<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClient\Generator;

use eZ\Publish\Core\REST\Common\Output\Generator;

class ContentListElementGenerator
{
    /**
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param array $contentList
     *
     * @return \eZ\Publish\Core\REST\Common\Output\Generator
     */
    public function generateElement(Generator $generator, array $contentList = []): Generator
    {
        $generator->startObjectElement('contentList');
        $generator->startList('content');

        foreach ($contentList as $content) {
            $generator->startObjectElement('content');

            $generator->startValueElement('contentId', $content['contentId']);
            $generator->endValueElement('contentId');

            $generator->startValueElement('contentTypeId', $content['contentTypeId']);
            $generator->endValueElement('contentTypeId');

            $generator->startValueElement('identifier', $content['identifier']);
            $generator->endValueElement('identifier');

            $generator->startValueElement('language', $content['language']);
            $generator->endValueElement('language');

            $generator->startValueElement('publishedDate', $content['publishedDate']);
            $generator->endValueElement('publishedDate');

            $generator->startValueElement('author', $content['author']);
            $generator->endValueElement('author');

            $generator->startValueElement('uri', $content['uri']);
            $generator->endValueElement('uri');

            $generator->startValueElement('categoryPath', $content['categoryPath']);
            $generator->endValueElement('categoryPath');

            $generator->startObjectElement('mainLocation');
            $generator->startAttribute('href', $content['mainLocation']['href']);
            $generator->endAttribute('href');
            $generator->endObjectElement('mainLocation');

            $generator->startObjectElement('locations');
            $generator->startAttribute('href', $content['locations']['href']);
            $generator->endAttribute('href');
            $generator->endObjectElement('locations');

            foreach ($content['fields'] as $name => $value) {
                $generator->startValueElement($name, $value);
                $generator->endValueElement($name);
            }

            $generator->endObjectElement('content');
        }

        $generator->endList('content');
        $generator->endObjectElement('contentList');

        return $generator;
    }
}
