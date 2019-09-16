<?php

namespace BlogBundle\Repository;

use BlogBundle\Entity\Tag;
use BlogBundle\Entity\EntryTag;
use Doctrine\ORM\Tools\Pagination\Paginator;
class EntryRepository extends \Doctrine\ORM\EntityRepository {

    public function saveEntryTags($tags = null, $title = null, $category = null, $user = null, $entry = null) {
        $em = $this->getEntityManager();

        $tag_repo = $em->getRepository("BlogBundle:Tag");

        if ($entry == null) {
            $entry = $this->findOneBy(array(
                "title" => $title,
                "category" => $category,
                "user" => $user));
        } else {
            
        }

        $tags .= ",";
        $tags = explode(",", $tags);

        foreach ($tags as $tag) {
            if (!empty(trim($tag))) {
                $deleteSpaceTags = trim($tag);
                
                $isset_tag = $tag_repo->findOneBy(array("name" => $deleteSpaceTags));
                
                if (count($isset_tag) == 0) {
                    
                    $tag_obj = new Tag();
                    $tag_obj->setName($deleteSpaceTags);
                    $tag_obj->setDescription($deleteSpaceTags);
                    $em->persist($tag_obj);
                    $em->flush();
                }


                $tag = $tag_repo->findOneBy(array("name" => trim($deleteSpaceTags)));
               
                $entryTag = new EntryTag();
                $entryTag->setEntry($entry);
                $entryTag->setTag($tag);
                
                //var_dump($entryTag);
                
                $em->persist($entryTag);
                
            }
        }
        

        $flush = $em->flush();
     
        return $flush;
    }
    
    public function getPaginateEntries($pageSize=5,$currentPage=1){
        $em = $this->getEntityManager();
        
        $dql = "SELECT e FROM BlogBundle\Entity\Entry e ORDER BY e.id DESC";
        
        //calcular el numero de paginas y limitar el numero de resutlados
        $query = $em->createQuery($dql)
                //primer resultado a mostrar
                ->setFirstResult($pageSize*($currentPage-1))
                ->setMaxResults($pageSize);
        
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        return $paginator;
    }
    
    public function getPaginateCategoryEntries($category,$pageSize=5,$currentPage=1){
        $em = $this->getEntityManager();
        
        $dql = "SELECT e FROM BlogBundle\Entity\Entry e Where e.category =:category ORDER BY e.id DESC ";
        
        //calcular el numero de paginas y limitar el numero de resutlados
        $query = $em->createQuery($dql)->setParameter('category', $category)
                //primer resultado a mostrar
                ->setFirstResult($pageSize*($currentPage-1))
                ->setMaxResults($pageSize);
        
        $paginator = new Paginator($query, $fetchJoinCollection = true);
        
        return $paginator;
    }

}
