<?php

declare(strict_types=1);
namespace StraschekIo\Hyphenator\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TermRepository
{
    private $queryBuilder = null;

    public function fetchAll(): ?array
    {
        $queryBuilder = $this->getQueryBuilder();
        $records = $queryBuilder
            ->select('from', 'to')
            ->from('tx_hyphenator_term')
            ->execute()
            ->fetchAll();

        if (!empty($records)) {
            return $records;
        }

        return null;
    }

    private function getQueryBuilder()
    {
        if (!$this->queryBuilder instanceof QueryBuilder) {
            $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_hyphenator_term');
        }
        return $this->queryBuilder;
    }
}
