<?php

namespace Yosimitso\WorkingForumBundle\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NativeQuery;

use http\Exception\InvalidArgumentException;
use Yosimitso\WorkingForumBundle\Entity\{Forum,Subforum,Post,Thread};

/**
 * Class ThreadRepository
 *
 * @package Yosimitso\WorkingForumBundle\Repository
 */
class ThreadRepository extends EntityRepository
{

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

    /**
     * @param string  $keywords
     * @param array  $subforums
     *
     * @return QueryBuilder
     */
    public function search(array $keywords, array $subforums)
    {
        if (empty($keywords)) {
            throw new InvalidArgumentException('no keyword to search');
        }

        if (empty($subforums)) {
            throw new InvalidArgumentException('subforum not exist');
        }

        $queryBuilder = $this->createQueryBuilder('t')
            ->where('t.subforum IN (:subforums)')
            ->setParameter(':subforums', $subforums, Connection::PARAM_STR_ARRAY);


        foreach ($keywords as $index => $word) {

            $queryBuilder->andWhere('(' . implode(' OR ', [
                    't.label LIKE :keyword_' . $index,
                    't.subLabel LIKE :keyword_' . $index]) . ')');

            $queryBuilder->setParameter(':keyword_' . $index, '%' . $word . '%');
        }

        return $queryBuilder;
    }
}
