<?php
 
namespace CloudPod\ClassroomBundle\Entity;
 
use Doctrine\ORM\EntityRepository;

class ClassroomRepository extends EntityRepository
{
    public function getClassroomID($id)
    {
            $query = $this->getEntityManager()
            ->createQuery('SELECT c FROM CloudPodClassroomBundle:Classroom c WHERE c.classID = :id')
            ->setParameter('id', $id);

        try {
        return $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
        
        return $e;}
        
    }

    public function UpdateClassInfo($id, $classname, $classdesc)
    {
            $query = $this->getEntityManager()
            ->createQuery('UPDATE CloudPodClassroomBundle:Classroom c SET c.className = :classname, c.classDesc = :classdesc WHERE c.classID = :id')
           ->setParameters(array('id' => $id, 'classname' => $classname, 'classdesc' => $classdesc));

        try {
        $query->execute();;
        } catch (\Doctrine\ORM\NoResultException $e) {
        
        return $e;}
        
    }

    public function UpdateClassKey($id,$newkey)
    {
            $query = $this->getEntityManager()
            ->createQuery('UPDATE CloudPodClassroomBundle:Classroom c SET c.classKey = :newkey WHERE c.classID = :id')
           ->setParameters(array('id' => $id, 'newkey' => $newkey));

        try {
        $query->execute();;
        } catch (\Doctrine\ORM\NoResultException $e) {
        
        return $e;}
        
    }

    public function DeleteClassRoom($id)
    {
            $query = $this->getEntityManager()
            ->createQuery('DELETE FROM CloudPodClassroomBundle:Classroom c WHERE c.classID = :id')
           ->setParameter('id', $id);

        try {
        $query->execute();;
        } catch (\Doctrine\ORM\NoResultException $e) {
        
        return $e;}
        
    }




}