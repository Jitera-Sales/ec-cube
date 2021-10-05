<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Repository;

use Doctrine\Persistence\ManagerRegistry as RegistryInterface;
use Eccube\Doctrine\Query\Queries;
use Eccube\Entity\LoginHistory;
use Eccube\Util\StringUtil;

/**
 * LoginHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LoginHistoryRepository extends AbstractRepository
{
    /**
     * @var Queries
     */
    private $queries;

    /**
     * LoginHistoryRepository constructor.
     */
    public function __construct(
        RegistryInterface $registry,
        Queries $queries
    ) {
        parent::__construct($registry, LoginHistory::class);
        $this->queries = $queries;
    }

    /**
     * @param $searchData
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderBySearchDataForAdmin($searchData)
    {
        $qb = $this->createQueryBuilder('lh')
            ->select('lh');

        if (isset($searchData['multi']) && StringUtil::isNotBlank($searchData['multi'])) {
            //スペース除去
            $clean_key_multi = preg_replace('/\s+|[　]+/u', '', $searchData['multi']);
            $qb
                ->andWhere('lh.user_name LIKE :multi_key OR lh.client_ip LIKE :multi_key')
                ->setParameter('multi_key', '%'.$clean_key_multi.'%')
                ->setParameter('multi_key', '%'.$clean_key_multi.'%');
        }

        if (isset($searchData['client_ip']) && StringUtil::isNotBlank($searchData['client_ip'])) {
            $qb
                ->andWhere('lh.client_ip LIKE :client_ip')
                ->setParameter('client_ip', '%'.$searchData['client_ip'].'%');
        }

        if (isset($searchData['user_name']) && StringUtil::isNotBlank($searchData['user_name'])) {
            $qb
                ->andWhere('lh.user_name LIKE :user_name')
                ->setParameter('user_name', '%'.$searchData['user_name'].'%');
        }

        // create_datetime
        if (!empty($searchData['create_datetime_start']) && $searchData['create_datetime_start']) {
            $qb
                ->andWhere('lh.create_date >= :create_datetime_start')
                ->setParameter('create_datetime_start', $searchData['create_datetime_start']);
        }

        if (!empty($searchData['create_datetime_end']) && $searchData['create_datetime_end']) {
            $date = clone $searchData['create_datetime_end'];
            $qb
                ->andWhere('lh.create_date < :create_datetime_end')
                ->setParameter('create_datetime_end', $date);
        }

        // create_date 時間までは要らない人のために残しておきたい
        if (!empty($searchData['create_date_start']) && $searchData['create_date_start']) {
            $qb
                ->andWhere('lh.create_date >= :create_date_start')
                ->setParameter('create_date_start', $searchData['create_date_start']);
        }

        if (!empty($searchData['create_date_end']) && $searchData['create_date_end']) {
            $date = clone $searchData['create_date_end'];
            $date->modify('+1 days');
            $qb
                ->andWhere('lh.create_date < :create_date_end')
                ->setParameter('create_date_end', $date);
        }

        // status
        if (!empty($searchData['Status']) && count($searchData['Status'])) {
            $qb
                ->andWhere($qb->expr()->in('lh.Status', ':Status'))
                ->setParameter('Status', $searchData['Status']);
        }

        // Order By
        $qb
            ->addOrderBy('lh.create_date', 'DESC')
            ->addOrderBy('lh.id', 'DESC');

        return $this->queries->customize(QueryKey::LOGIN_HISTORY_SEARCH_ADMIN, $qb, $searchData);
    }
}
