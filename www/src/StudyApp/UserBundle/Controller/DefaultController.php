<?php

namespace StudyApp\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use StudyApp\UserBundle\Form\Type\UserType;

class DefaultController extends Controller
{
    /**
     * @Route("/profile", name="profile_update", requirements = { "_method" = "PATCH" })
     * @Template("StudyAppUserBundle:Default:profile.html.twig")
    */
    public function changeProfileAction()
    {
        $user = $this->getUser();
        $form = $this->createForm(new UserType(), $user);
        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('profile');
        }
        return array('form' => $form->createView());
        
    }
    /**
     * @Route("/profile", name="profile", requirements = {"_method" = "GET"})
     * @Template("StudyAppUserBundle:Default:profile.html.twig")
     */
    public function profileAction()
    {
        $user = $this->getUser();
        $form = $this->createForm(new UserType(), $user);
        return array('form' => $form->createView());
    }
}
