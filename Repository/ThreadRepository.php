<?php

namespace Yosimitso\WorkingForumBundle\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NativeQuery;

use Yosimitso\WorkingForumBundle\Entity\{Forum,Subforum,Post,Thread};

/**
 * Class ThreadRepository
 *
 * @package Yosimitso\WorkingForumBundle\Repository
 */
class ThreadRepository extends EntityRepository
{
    /**
     * @param integer $start
     * @param integer $limit
     *
     * @return array
     */
    public function getThread($start = 0, $limit = 10)
    {

        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder();
        $query = $queryBuilder
            ->select('a')
            ->addSelect('b')
            ->from($this->_entityName, 'a')
            ->join('YosimitsoWorkingForumBundle:Post', 'b', 'WITH', 'a.id = b.thread')
            ->orderBy('a.note', 'desc')
            ->setMaxResults($limit)
            ->getQuery();

        return $query->getScalarResult();
    }

    /**
     * @param Subforum $subforum
     * @return integer
     */
    public function getCountBySubforum(Subforum $subforum)
    {
        $query = new QueryBuilder($this->getEntityManager()
            ->getConnection());

        $result = $query->select('COUNT(*) AS `thread_count`')
            ->from('`workingforum_thread`')
            ->where('`subforum_id` = :subforumId')
            ->setParameter(':subforumId', $subforum->getId())
            ->setMaxResults(1)
            ->execute()->fetchColumn(0);

        return intval($result);
    }

    public function getCountByForum(Forum $forum)
    {
        $query = new QueryBuilder($this->getEntityManager()
            ->getConnection());

        $result = $query->select('COUNT(*) AS `thread_count`')
            ->from('`workingforum_thread`', 'WFT')
            ->innerJoin('WFT', 'workingforum_subforum', 'WFS', 'WFT.subforum_id = WFS.id')
            ->where('WFS.forum_id = :forumId')
            ->setParameter(':forumId', $forum->getId())
            ->setMaxResults(1)
            ->execute()->fetchColumn(0);

        return intval($result);
    }


}
