<?php


namespace Yosimitso\WorkingForumBundle\Repository;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

use Yosimitso\WorkingForumBundle\Entity\{Forum, PostReport, PostReportReview, Subforum, Thread, UserInterface};

class PostRepository extends EntityRepository
{
    public function getCountByUser(UserInterface $user)
    {
        $query = new QueryBuilder($this->getEntityManager()
            ->getConnection());

        $result = $query->select('COUNT(*) AS `post_count`')
            ->from('`workingforum_post`', 'P')
            ->where('P.`user_id` = :userId')
            ->setParameter(':userId', $user->getId())
            ->setMaxResults(1)
            ->execute()->fetchColumn(0);

        return intval($result);
    }

    public function getCountByForum(Forum $forum)
    {
        $query = new QueryBuilder($this->getEntityManager()
            ->getConnection());

        $result = $query->select('COUNT(*) AS `post_count`')
            ->from('`workingforum_post`', 'P')
            ->innerJoin('P', '`workingforum_thread`', 'T', 'P.`thread_id` = T.`id`')
            ->innerJoin('T', 'workingforum_subforum', 'S', 'T.subforum_id = S.id')
            ->where('S.`forum_id` = :forumId')
            ->setParameter(':forumId', $forum->getId())
            ->setMaxResults(1)
            ->execute()->fetchColumn(0);

        return intval($result);
    }

    public function getCountBySubforum(Subforum $subforum)
    {
        $query = new QueryBuilder($this->getEntityManager()
            ->getConnection());

        $result = $query->select('COUNT(*) AS `post_count`')
            ->from('`workingforum_post`', 'P')
            ->innerJoin('P', '`workingforum_thread`', 'T', 'P.`thread_id` = T.`id`')
            ->where('T.`subforum_id` = :subforumId')
            ->setParameter(':subforumId', $subforum->getId())
            ->setMaxResults(1)
            ->execute()->fetchColumn(0);

        return intval($result);
    }



    public function getLastBySubforum(Subforum $subforum)
    {
        return $this->createNativeNamedQuery('last_post_by_subforum')
            ->setParameter(':subforumId', $subforum->getId())
            ->getSingleResult();
    }

    public function getLastByThread(Thread $thread)
    {
        return $this->createNativeNamedQuery('last_post_by_thread')
            ->setParameter(':threadId', $thread->getId())
            ->getSingleResult();
    }

    public function getPostWithVoteCountByThread(Thread $thread)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($em);

        $rsm->addRootEntityFromClassMetadata($this->getEntityName(), 'p');
        $rsm->addJoinedEntityFromClassMetadata(PostReport::class, 'pr', 'p', 'postReports', [
            'id' => 'post_report_id', 'create_date' => 'post_report_create_date', 'user_id' => 'report_user_id'
        ]);
        $rsm->addJoinedEntityFromClassMetadata(PostReportReview::class, 'prr', 'pr', 'review', [
            'id' => 'post_report_review_id', 'create_date' => 'post_report_review_create_date'
        ]);

        $rsm->addScalarResult('vote_count', 'voteCount', 'integer');

        $rsm->entityMappings['p'] = 'post';

        $query = [
            'SELECT post_id, COUNT(*) AS vote_count',
            'FROM workingforum_post_vote',
            'WHERE vote_type = :voteType',
            'GROUP BY post_id'
        ];

        $query = [
            'SELECT ' . $rsm->generateSelectClause(['p' => 'P', 'pr' => 'PR', 'prr' => 'PRR']) . ', IFNULL(PV.vote_count, 0) AS vote_count',
            'FROM workingforum_post P',
            'LEFT JOIN (' . implode(' ', $query) . ') PV on P.id = PV.post_id',
            'LEFT JOIN workingforum_post_report PR ON P.id = PR.post_id',
            'LEFT JOIN workingforum_post_report_review PRR ON PR.id = PRR.report_id',
            'WHERE P.thread_id = :threadId'
        ];

        return $em->createNativeQuery(implode(' ', $query), $rsm)
            ->setParameters([
                ':threadId' => $thread->getId(),
                ':voteType' => 1])
            ->getResult();
    }

    public function getLastPostsOfThreadsBySubforum(Subforum $subforum)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($em);

        $rsm->addRootEntityFromClassMetadata($this->getEntityName(), 'p', [
            'id' => 'post_id',   'create_date' => 'post_create_date']);

        $rsm->addJoinedEntityFromClassMetadata(Thread::class, 't', 'p', 'thread', [
            'id' => 'thread_id', 'create_date' => 'thread_create_date']);

        $rsm->addScalarResult('post_count', 'postCount', 'integer');

        $rsm->entityMappings['p'] = 'post';

        $query = [
            'SELECT thread_id, MAX(id) AS last_post_id, COUNT(id) AS post_count ',
            'FROM workingforum_post',
            'GROUP BY thread_id'];

        $query = [
            'SELECT ' . $rsm->generateSelectClause(['t' => 'WFT', 'p' => 'WFP']) . ', LPI.post_count',
            'FROM workingforum_thread WFT',
            'INNER JOIN (' . implode(' ', $query) . ') LPI on WFT.id = LPI.thread_id',
            'INNER JOIN workingforum_post WFP on WFP.id = LPI.last_post_id where WFT.subforum_id = :subforumId',
            'ORDER BY WFT.pin DESC, WFP.create_date DESC'];

        return $em->createNativeQuery(implode(' ', $query), $rsm)
            ->setParameter(':subforumId', $subforum->getId())
            ->getResult();
    }

    public function getLastPostOfUser(UserInterface $user)
    {
        return $this->findOneBy(['user' => $user], ['createDate' => 'DESC']);
    }

    /**
     * @param string  $keywords
     * @param integer $start
     * @param integer $limit
     * @param array  $subforums
     *
     * @return Post[]
     */
    public function search($keywords, $start = 0, $limit = 100, array $subforums)
    {
        if (empty($subforums)) {
            return [];
        }

        $keywords = array_filter(array_map('trim', explode(' ', $keywords)));

        if (empty($keywords)) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder('b');

        $queryBuilder
            ->select('b')
            ->join('YosimitsoWorkingForumBundle:Thread', 'a', 'WITH', 'a.id = b.thread')
            ->join('YosimitsoWorkingForumBundle:Subforum','c','WITH','a.subforum = c.id');

        $queryBuilder->where('c.id IN (:subforums)');

        $queryBuilder->setParameter(':subforums', $subforums, Connection::PARAM_STR_ARRAY);

        foreach ($keywords as $index => $word) {
            $queryBuilder->andWhere('(' . implode(' OR ', [
                    'a.label LIKE :keyword_' . $index,
                    'a.subLabel LIKE :keyword_' . $index,
                    'b.content LIKE :keyword_' . $index]) . ')');

            $queryBuilder->setParameter(':keyword_' . $index, '%' . $word . '%');
        }

        return $queryBuilder->setMaxResults($limit)
            ->getQuery()->getResult();
    }

}

