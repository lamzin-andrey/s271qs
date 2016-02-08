<?php

namespace StudyApp\StudyAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use StudyApp\UserBundle\Entity\User;
use StudyApp\CommentBundle\Entity\Comment;
use StudyApp\CommentBundle\Form\Type\CommentType;

class DefaultController extends Controller
{
     /**
     * @Route("/", name="study_app_home")
     * @Template()
    */
    public function indexAction()
    {
        $fb_friends = ($this->getRequest()->getSession()->has('fb_friends') ? $this->getRequest()->getSession()->get('fb_friends') : array());
    	
        $result = array(
    		'is_auth' => $is_auth = is_object($this->get('security.context')->getToken()->getUser()),
    		'error' => null,
    		'has_errors' => null,
    		'host' => $this->getRequest()->getHost(),
    		'username' => ($is_auth ? $this->get('security.context')->getToken()->getUser()->getFirstName() : null),
            'login_form' => $this->createForm(new \StudyApp\UserBundle\Form\Type\UserRegistrationType(), new User())->createView()
    	);
        return $result;
    }
}
