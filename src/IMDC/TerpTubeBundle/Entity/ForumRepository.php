<?php

namespace IMDC\TerpTubeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * ForumRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ForumRepository extends EntityRepository
{
	private function getViewableToUserQB($user, $excludeGroups = false, $groupsOnly = false)
	{
		$qb = $this->createQueryBuilder ( 'f' );
		
		if ($excludeGroups)
		{
			$qb->where ( $qb->expr ()->isNull ( 'f.group' ) );
		}
		else
		{
			$qb->leftJoin ( 'f.group', 'g' )->leftJoin ( 'g.members', 'm' )->where ( $qb->expr ()->isNotNull ( 'f.group' ) )->andWhere ( $qb->expr ()->in ( 'm.id', array (
					$user->getId () 
			) ) );
			
			if (! $groupsOnly)
				$qb->orWhere ( $qb->expr ()->isNull ( 'f.group' ) );
		}
		
		return $qb;
	}
	private function filterViewableToUser($securityContext, array $items)
	{
		$viewable = array ();
		foreach ( $items as $item )
		{
			if ($securityContext->isGranted ( 'VIEW', $item ) === true)
			{
				$viewable [] = $item;
			}
		}
		
		return $viewable;
	}
	public function getViewableToUser($securityContext, $user, $excludeGroups = false, $groupsOnly = false)
	{
		$forums = $this->getViewableToUserQB ( $user, $excludeGroups, $groupsOnly )->getQuery ()->getResult ();
		
		return $this->filterViewableToUser ( $securityContext, $forums );
	}
	public function getRecent($securityContext, $user, $excludeGroups = false, $groupsOnly = false, $limit = 4)
	{
		$forums = $this->getViewableToUserQB ( $user, $excludeGroups, $groupsOnly )->orderBy ( 'f.lastActivity', 'DESC' )->getQuery ()->getResult ();
		
		$filtered = $this->filterViewableToUser ( $securityContext, $forums );
		
		return array_slice ( $filtered, 0, $limit );
	}
	public function getRecentlyCreatedForums($limit = 30)
	{
		$query = $this->getEntityManager ()->createQuery ( 'SELECT f
                     FROM IMDCTerpTubeBundle:Forum f
                     ORDER BY f.creationDate DESC' );
		$query->setMaxResults ( $limit );
		
		return $query->getResult ();
	}
	
	/**
	 * Does this belong here, or in the Thread Repository?
	 *
	 * @param unknown $fid        	
	 */
	public function getTopLevelThreadsForForum($fid)
	{
		$dql = "SELECT t
	            FROM IMDCTerpTubeBundle:Thread t
	            WHERE t.parentForum IS NOT NULL
	            AND t.parentForum = :fid
	            ORDER BY t.lastPostAt DESC";
		
		$query = $this->getEntityManager ()->createQuery ( $dql )->setParameter ( 'fid', $fid );
		return $query->getResult ();
	}
	
	/**
	 * Currently not used as Forums don't have permissions yet
	 * Also the Forum->userHasAccess method does not exist yet
	 *
	 * @param IMDCTerpTubeBundle:User $user        	
	 * @return multitype:Ambigous <multitype:, \Doctrine\ORM\mixed, \Doctrine\ORM\Internal\Hydration\mixed, \Doctrine\DBAL\Driver\Statement, \Doctrine\Common\Cache\mixed>
	 */
	public function findForumsThatUserHasAccessTo($user)
	{
		$query = $this->getEntityManager ()->createQuery ( '
            SELECT f FROM IMDCTerpTubeBundle:Forum f
        ' );
		$allForums = $query->getResult ();
		$results = array ();
		foreach ( $allForums as $aForum )
		{
			if ($aForum->userHasAccess ( $user ))
			{
				$results [] = $aForum;
			}
		}
		
		return $results;
	}
	
	/**
	 * Used to get all the forums that use the media as a title
	 * @param Media $media
	 */
	public function getForumsForMedia(Media $media)
	{
		$dql = "SELECT f
	            FROM IMDCTerpTubeBundle:Forum f
	            JOIN f.titleMedia m
    			WHERE :mid = m.id";
		$query = $this->getEntityManager ()->createQuery ( $dql )->setParameter ( 'mid', $media->getId () );
		return $query->getResult ();
	}
}
