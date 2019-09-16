<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BlogBundle\Entity\Tag;
use BlogBundle\Form\TagType;
use BlogBundle\Entity\Category;
use BlogBundle\Form\CategoryType;
use Symfony\Component\HttpFoundation\Session\Session;

class CategoryController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $category_repo = $em->getRepository("BlogBundle:Category");
        $categories = $category_repo->findAll();

        return $this->render("BlogBundle:Category:index.html.twig", array(
                    "categories" => $categories
        ));
    }

    public function addAction(Request $request) {

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();

                $category = new Category();
                $category->setName($form->get("name")->getData());
                $category->setDescription($form->get("description")->getData());

                $em->persist($category);
                $flush = $em->flush();

                if ($flush == null) {
                    $status = "La cateogria se ha creado correctamente";
                    $alert = "alert-success";
                } else {
                    $status = "Error al añadir la categoria";
                    $alert = "alert-danger";
                }
            } else {
                $alert = "alert-danger";
                $status = "La categoria no se ha creado porque hay fallos de validación!!";
            }


            $this->session->getFlashBag()->add($alert, $status);
            return $this->redirectToRoute("blog_index_category");
        }
        return $this->render("BlogBundle:Category:add.html.twig", array(
                    "form" => $form->createView()
        ));
    }

    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $category_repo = $em->getRepository("BlogBundle:Category");
        $category = $category_repo->find($id);
       
        if (count($category->getEntries()) == 0) {

            $em->remove($category);

            $flush = $em->flush();
        }

        return $this->redirectToRoute("blog_index_category");
    }
    
 public function editAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $category_repo = $em->getRepository("BlogBundle:Category");
        $category = $category_repo->find($id);
       
        $form = $this->createForm(CategoryType::class, $category);
       $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $category->setName($form->get("name")->getData());
                $category->setDescription($form->get("description")->getData());

                $em->persist($category);
                $flush = $em->flush();

                if ($flush == null) {
                    $status = "La cateogria se ha editado correctamente";
                    $alert = "alert-success";
                } else {
                    $status = "Error a editar la categoria";
                    $alert = "alert-danger";
                }
            } else {
                $alert = "alert-danger";
                $status = "La categoria no se ha editado porque hay fallos de validación!!";
            }


            $this->session->getFlashBag()->add($alert, $status);
            return $this->redirectToRoute("blog_index_category");
        }
         return $this->render("BlogBundle:Category:edit.html.twig", array(
                    "form" => $form->createView()
        ));
    }
    
    public function categoryAction($id,$page=1){
        $em = $this->getDoctrine()->getManager();
        $category_repo = $em->getRepository("BlogBundle:Category");
        $category = $category_repo->find($id);
        
        $pageSize = 2;
        
        $entry_repo = $em->getRepository("BlogBundle:Entry");
        
        // num de resultados y la paginacion
        $entries = $entry_repo->getPaginateCategoryEntries($category,$pageSize, $page);
        $totalItems = count($entries);
        //la funcion redondea hacia arriba
        $pagesCount = ceil($totalItems / $pageSize);
        
        return $this->render("BlogBundle:Category:category.html.twig", array(
                    "category" => $category,
                    "categories" => $category_repo->findAll(),
                    "pagesCount" => $pagesCount,
                    "entries" => $entries,
                    "page" => $page
        ));
    }

}
