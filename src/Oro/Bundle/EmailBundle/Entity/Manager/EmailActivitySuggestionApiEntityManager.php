<?php

namespace Oro\Bundle\EmailBundle\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ActivityBundle\Entity\Manager\ActivitySearchApiEntityManager;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\SearchBundle\Engine\Indexer as SearchIndexer;
use Oro\Bundle\SearchBundle\Query\Result as SearchResult;
use Oro\Bundle\SearchBundle\Query\Result\Item as SearchResultItem;

class EmailActivitySuggestionApiEntityManager extends ActivitySearchApiEntityManager
{
    /**
     * @param string          $class
     * @param ObjectManager   $om
     * @param ActivityManager $activityManager
     * @param SearchIndexer   $searchIndexer
     */
    public function __construct(
        $class,
        ObjectManager $om,
        ActivityManager $activityManager,
        SearchIndexer $searchIndexer
    ) {
        parent::__construct($om, $activityManager, $searchIndexer);
        $this->setClass($class);
    }

    /**
     * Gets suggestion result
     *
     * @param int   $page
     * @param int   $limit
     * @param array $criteria
     *
     * @return array
     */
    public function getSuggestionResult(
        $page = 1,
        $limit = 10,
        $criteria = []
    ) {

        $searchQueryBuilder = $this->searchIndexer->getSimpleSearchQuery(
            false,
            0,
            0,
            $this->getSearchAliases([])
        );
        $searchResult = $this->searchIndexer->query($searchQueryBuilder);

        $queryBuilder = $this->activityManager->getActivityTargetsQueryBuilder(
            $this->class,
            $criteria
        );
        $queryResult = $queryBuilder->getQuery()->getArrayResult();

        $data = $this->mergeResults($searchResult, $queryResult);

        $slice = array_slice(
            $data,
            $this->getOffset($page, $limit),
            $limit
        );

        $result = [
            'result'     => $slice,
            'totalCount' =>
                function () use ($data) {
                    return count($data);
                }
        ];

        return $result;
    }


    /**
     * Merges results from the search(suggested to assign) and assigned entities to email.
     * Added assigned flag.
     *
     * @param SearchResult $searchResult
     * @param array        $queryResult
     *
     * @return array
     */
    protected function mergeResults(SearchResult $searchResult, array $queryResult = [])
    {
        $result = array_map(
            function ($res) {
                $res['assigned'] = true;

                return $res;
            },
            $queryResult
        );

        /** @var SearchResultItem $item */
        foreach ($searchResult->getElements() as $item) {
            $id        = $item->getRecordId();
            $className = $item->getEntityName();

            foreach ($queryResult as $res) {
                if ($res['id'] == $id && $res['entity'] == $className) {
                    continue 2;
                }
            }

            $result[] = [
                'id'       => $id,
                'entity'   => $className,
                'title'    => $item->getRecordTitle(),
                'assigned' => false
            ];
        }

        return $result;
    }
}
