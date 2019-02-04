<?php

namespace Yosimitso\WorkingForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Yosimitso\WorkingForumBundle\Entity\PostReportReview;

class PostReportRepository extends EntityRepository
{
    public function getNonReviewed()
    {
        return $this->createNativeNamedQuery('post_report_non_reviewed')
            ->getResult();
    }

    public function getReviewed()
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($em);

        $rsm->addRootEntityFromClassMetadata($this->getEntityName(), 'pr', [
            'id' => 'post_report_id', 'create_date' => 'report_create_date'
        ]);

        $rsm->addJoinedEntityFromClassMetadata(PostReportReview::class, 'prr', 'pr', 'review', [
            'id' => 'post_report_review_id', 'create_date' => 'review_create_date'
        ]);

        $query = [
            'SELECT ' . $rsm->generateSelectClause(['pr' => 'WPR', 'prr' => 'WPRR']),
            'FROM workingforum_post_report WPR',
            'INNER JOIN workingforum_post_report_review WPRR ON WPR.id = WPRR.report_id'];

        return $em->createNativeQuery(implode(' ', $query), $rsm)->getResult();
    }
}