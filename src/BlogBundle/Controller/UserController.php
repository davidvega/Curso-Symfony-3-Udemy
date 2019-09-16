<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BlogBundle\Entity\User;
use BlogBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function loginAction(Request $request) {
        $authenticationUtils = $this->get("security.authentication_utils");
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        //validar form
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $user_repo = $em->getRepository("BlogBundle:User");
                $user = $user_repo->findOneBy(array("email" => $form->get("email")->getData()));
                if (count($user) == 0) {
                    $user = new User();
                    $user->setName($form->get("name")->getData());
                    $user->setSurname($form->get("surname")->getData());
                    $user->setEmail($form->get("email")->getData());
                    $user->setPassword($form->get("password")->getData());

                    $factory = $this->get("security.encoder_factory");
                    $encoder = $factory->getEncoder($user);
                    $password = $encoder->encodePassword($form->get("password")->getData(), $user->getSalt());

                    $user->setPassword($password);
                    $user->setRole("ROLE_USER");
                    $user->setImage(null);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $flush = $em->flush();
                    if ($flush == null) {
                        $error = "alert-success";
                        $status = "El usuario se ha creado correctamente";
                    } else {
                        $error = "alert-danger";
                        $status = "No te has registrado correctamente";
                    }
                } else {
                    $error = "alert-danger";
                    $status = "El usuario ya existe";
                }
            } else {
                $error = "alert-danger";
                $status = "No te has registrado correctamente";
            }
            $this->session->getFlashBag()->add($error, $status);

            //mensaje flash bag
        }
        return $this->render("BlogBundle:user:login.html.twig", array(
                    "error" => $error,
                    "last_username" => $lastUsername,
                    "form_register" => $form->createView()
        ));
    }

}
