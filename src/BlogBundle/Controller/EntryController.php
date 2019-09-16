<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\BrowserKit\Response;
use BlogBundle\Entity\Tag;
use BlogBundle\Form\TagType;
use BlogBundle\Form\EntryType;
use BlogBundle\Entity\Entry;
use BlogBundle\Entity\Category;
use BlogBundle\Form\CategoryType;
use Symfony\Component\HttpFoundation\Session\Session;

class EntryController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request, $page) {
        //var_dump($request->getSession()->get("_locale"));
        //var_dump($this->get('translator')->trans('btn_edit'));
        $em = $this->getDoctrine()->getManager();
        $entry_repo = $em->getRepository("BlogBundle:Entry");
        $category_repo = $em->getRepository("BlogBundle:Category");

        $categories = $category_repo->findAll();
        $pageSize = 2;
        // num de resultados y la paginacion
        $entries = $entry_repo->getpaginateEntries($pageSize, $page);
        $totalItems = count($entries);
        $pagesCount = ceil($totalItems / $pageSize);

        return $this->render("BlogBundle:Entry:index.html.twig", array(
                    "entries" => $entries,
                    "categories" => $categories,
                    "totlaItems" => $totalItems,
                    "pagesCount" => $pagesCount,
                    "page" => $page,
                    "locale" => $request->getLocale()
        ));
    }

    public function addAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $category_repo = $em->getRepository("BlogBundle:Category");

        $categories = $category_repo->findAll();
        $entry = new Entry();
        $form = $this->createForm(EntryType::class, $entry);

        $form->handleRequest($request);


        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $entry_repo = $em->getRepository("BlogBundle:Entry");
                $category_repo = $em->getRepository("BlogBundle:Category");
                $entry = new Entry();
                $entry->setTitle($form->get("title")->getData());
                $entry->setContent($form->get("content")->getData());
                $entry->setStatus($form->get("status")->getData());


                $file = $form["image"]->getData();
                if ((!empty($file)) && ($file != null)) {
                    //para sar extension
                    $ext = $file->guessExtension();
                    $file_name = time() . "." . $ext;
                    $file->move("uploads", $file_name);

                    $entry->setImage($file_name);
                } else {
                    $entry->setImage(null);
                }
                $category = $category_repo->find($form->get("category")->getData());
                $entry->setCategory($category);

                $user = $this->getUser();
                $entry->setUser($user);
                if ($user == null) {
                    echo "necesita logearse para añadir entrada";
                } else {
                    $em->persist($entry);
                    $flush = $em->flush();

                    $entry_repo->saveEntryTags(
                            $form->get("tags")->getData(), $form->get("title")->getData(), $category, $user);

                    if ($flush == null) {
                        $status = "La entrada se ha creado correctamente";
                        $alert = "alert-success";
                    } else {
                        $status = "Error al añadir la entrada";
                        $alert = "alert-danger";
                    }
                }
            } else {
                $alert = "alert-danger";
                $status = "La entrada no se ha creado porque hay fallos de validación!!";
            }


            $this->session->getFlashBag()->add($alert, $status);
            return $this->redirectToRoute("blog_homepage");
        }
        return $this->render("BlogBundle:Entry:add.html.twig", array(
                    "form" => $form->createView(),
                    "categories" => $categories
        ));
    }

    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entry_repo = $em->getRepository("BlogBundle:Entry");
        $entry_tag_repo = $em->getRepository("BlogBundle:EntryTag");

        $entry = $entry_repo->find($id);

        $entry_tags = $entry_tag_repo->findBy(array("entry" => $entry));

        foreach ($entry_tags as $et) {
            if (is_object($et)) {
                $em->remove($et);
                $em->flush();
            }
        }

        if (is_object($entry)) {
            $em->remove($entry);
            $em->flush();
        }



        return $this->redirectToRoute("blog_homepage");
    }

    public function editAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entry_repo = $em->getRepository("BlogBundle:Entry");
        $category_repo = $em->getRepository("BlogBundle:Category");

        $categories = $category_repo->findAll();

        $entry = $entry_repo->find($id);
        $entry_image = $entry->getImage();
        $tags = "";
        foreach ($entry->getEntryTag() as $entryTag) {
            $tags .= $entryTag->getTag()->getName() . ", ";
        }

        $form = $this->createForm(EntryType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                /*
                  $entry->setTitle($form->get("title")->getData());
                  $entry->setContent($form->get("content")->getData());
                  $entry->setStatus($form->get("status")->getData());
                 */

                $file = $form["image"]->getData();
                if ((!empty($file)) && ($file != null)) {
                    //para sar extension
                    $ext = $file->guessExtension();
                    $file_name = time() . "." . $ext;
                    $file->move("uploads", $file_name);

                    $entry->setImage($file_name);
                } else {
                    $entry->setImage($entry_image);
                }

                $category = $category_repo->find($form->get("category")->getData());
                $entry->setCategory($category);

                $user = $this->getUser();
                $entry->setUser($user);
                if ($user == null) {
                    echo "necesita logearse para añadir entrada";
                } else {
                    $em->persist($entry);
                    $flush = $em->flush();



                    if ($flush == null) {
                        $status = "La entrada se ha editado correctamente";
                        $alert = "alert-success";
                    } else {
                        $status = "Error a editar la entrada";
                        $alert = "alert-danger";
                    }
                    $entry_tag_repo = $em->getRepository("BlogBundle:EntryTag");
                    $entry_tags = $entry_tag_repo->findBy(array("entry" => $entry));

                    foreach ($entry_tags as $et) {
                        if (is_object($et)) {
                            $em->remove($et);
                            $em->flush();
                        }
                    }
                    $entry_repo->saveEntryTags(
                            $form->get("tags")->getData(), $form->get("title")->getData(), $category, $user);
                }
            } else {
                $alert = "alert-danger";
                $status = "La entrada no se ha editado porque hay fallos de validación!!";
            }


            $this->session->getFlashBag()->add($alert, $status);
            return $this->redirectToRoute("blog_homepage");
        }
        return $this->render("BlogBundle:Entry:edit.html.twig", array(
                    "form" => $form->createView(),
                    "entry" => $entry,
                    "tags" => $tags,
                    "categories" => $categories
        ));
    }

}
