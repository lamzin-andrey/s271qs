<?php

namespace StudyApp\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use StudyApp\UserBundle\Entity\User;
use StudyApp\UserBundle\Entity\StudyAppUserProvider;
use StudyApp\UserBundle\Form\Type\UserRegistrationType;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;


use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultController extends Controller
{
    
    /**
     * @Route("/login", name="study_app_login")
     * @Template("StudyAppSecurityBundle:Default:login.html.twig")
    */
    public function loginAction()
    {
		$request = $this->getRequest();
		$error = '';
		if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
		}
		if (!$error) {
			$error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
			$request->getSession()->remove(SecurityContext::AUTHENTICATION_ERROR);
		}
		$last_username = $request->getSession()->get(SecurityContext::LAST_USERNAME);
		$variables = array(
			'error' => $error,
			'username' => $last_username,
			'is_auth' => is_object( $this->getUser() ),
            'host' => $this->getRequest()->getHost()
		);
        return $variables;
    }
    
    /**
     * @Route("/registration", name="study_app_signup")
     * @Template("StudyAppSecurityBundle:Default:add.html.twig")
    */
    public function signupAction()
    {
		$request = $this->getRequest();
		$session = $request->getSession();
		$req = $request->request;
		$error = '';
		$form = $this->createForm( new UserRegistrationType(), new User() );
		$variables = array(
			'error' => $error,
			'form' => $form->createView()
		);
        return $variables;
    }
    /**
     * @Route("/user/create", name="study_app_user_create", requirements={ "_method" : "POST"})
     * @Template("StudyAppSecurityBundle:Default:add.html.twig")
    */
    public function createAction()
    {
		$form = $this->createForm( new UserRegistrationType(), new User() );
		$request = $this->getRequest();
		if ($request->getMethod() == 'POST') {
			$form->handleRequest($request);
			if ($form->isValid()) {
				$user = $form->getData();
				//encode password
				$encoder = $this->get('security.encoder_factory')->getEncoder($user);
				$password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
				$user->setPassword($password);
				$user->setRole(0);
				$user->setEmailIsVerify(false);
				$user->setActivationCode( $this->_activationCode($user) );
				//save
				$em = $this->getDoctrine()->getEntityManager();
				$em->persist($user);
				$em->flush();
                //send email
                $message = \Swift_Message::newInstance();
                $html = $this->renderView('StudyAppSecurityBundle:Mailer:registration.html.twig', array(
                    'name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'host' =>$request->getHost(),
                    'route' => $this->generateUrl('study_app_confirm_email', array('code' => $user->getActivationCode()))
                ));
                $admin_email = $this->container->getParameter('admin')['email'];
                $message->setBody($html, 'text/html', 'UTF-8')
                        ->setFrom($admin_email)
                        ->setTo($user->getEmail())
                        ->setSubject('Регистрация на ' . $request->getHost());
                $this->get("mailer")->send($message);
                
				return $this->redirectToRoute('study_app_email_sended');
			}
		}
		return array(
			'form' => $form->createView(),
			'error' => true
		);
	}
    /**
     * @Route("/emailsended", name="study_app_email_sended")
     * @Template("StudyAppSecurityBundle:Default:message.html.twig")
    */
    public function emailSendedAction()
    {
        return array(
            'message' => $this->get("translator")->trans('You will be sent an email with a link to activate your account. Click on the link in the email to complete your registration.'),
            'type' => 'success',
            'route' => null
        );
    }
    /**
     * @Route("/confirmemail/{code}", name="study_app_confirm_email")
     * @Template("StudyAppSecurityBundle:Default:message.html.twig")
    */
    public function emailConfirmAction($code)
    {
        $type = 'danger';
        $link = 'study_app_signup';
        $link_text = 'Sign Up';
        $message = 'Confirmation failure';
        $user = $this->getDoctrine()->getRepository(get_class( new User() ))->findOneBy( array('activation_code' => $code ) );
        if ($user) {
            $type = 'success';
            $link = 'login';
            $link_text = 'Sign In';
            $message = 'Confirmation success';
            $user->setActivationCode( $this->_activationCode($user) );
            $user->setEmailIsVerify(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }
        return array(
            'message'   => $this->get("translator")->trans($message),
            'type'      => $type,
            'route'     => $link,
            'link_text' => $this->get("translator")->trans($link_text)
        );
    }
    
    private function _activationCode($user) {
        return md5( uniqid( date('YmdHis') . $user->getEmail() ) );
    }
}
