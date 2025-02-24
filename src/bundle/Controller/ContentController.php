<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzRecommendationClientBundle\Controller;

use eZ\Publish\API\Repository\SearchService as SearchServiceInterface;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\REST\Server\Controller as BaseController;
use eZ\Publish\Core\REST\Server\Exceptions\AuthenticationFailedException;
use EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface;
use EzSystems\EzRecommendationClient\Content\Content;
use EzSystems\EzRecommendationClient\Value\ContentData;
use EzSystems\EzRecommendationClient\Value\IdList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends BaseController
{
    /** @var \eZ\Publish\Core\Repository\SearchService */
    private $searchService;

    /** @var \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface */
    private $authenticator;

    /** @var \EzSystems\EzRecommendationClient\Content\Content */
    private $content;

    /**
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \EzSystems\EzRecommendationClient\Authentication\AuthenticatorInterface $authenticator
     * @param \EzSystems\EzRecommendationClient\Content\Content $content
     */
    public function __construct(
        SearchServiceInterface $searchService,
        AuthenticatorInterface $authenticator,
        Content $content
    ) {
        $this->searchService = $searchService;
        $this->authenticator = $authenticator;
        $this->content = $content;
    }

    /**
     * Prepares content for ContentData class.
     *
     * @param \EzSystems\EzRecommendationClient\Value\IdList $idList
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @ParamConverter("list_converter")
     *
     * @return \EzSystems\EzRecommendationClient\Value\ContentData
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getContentAction(IdList $idList, Request $request): ContentData
    {
        if (!$this->authenticator->authenticate()) {
            throw new AuthenticationFailedException('Access denied: wrong credentials', Response::HTTP_UNAUTHORIZED);
        }

        $requestQuery = $request->query;
        $lang = $requestQuery->get('lang');

        $contentItems = $this->searchService->findContent(
            $this->getQuery($requestQuery, $idList),
            (!empty($lang) ? ['languages' => [$lang]] : [])
        )->searchHits;

        $contentData = $this->content->prepareContent([$contentItems], $requestQuery);

        return new ContentData($contentData);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\ParameterBag ParameterBag $parameterBag
     * @param \EzSystems\EzRecommendationClient\Value\IdList $idList
     *
     * @return Query
     */
    private function getQuery(ParameterBag $parameterBag, IdList $idList): Query
    {
        $criteria = [new Criterion\ContentId($idList->list)];

        if (!$parameterBag->get('hidden')) {
            $criteria[] = new Criterion\Visibility(Criterion\Visibility::VISIBLE);
        }

        if ($parameterBag->has('lang')) {
            $criteria[] = new Criterion\LanguageCode($parameterBag->get('lang'));
        }

        $query = new Query();
        $query->query = new Criterion\LogicalAnd($criteria);

        return $query;
    }
}
