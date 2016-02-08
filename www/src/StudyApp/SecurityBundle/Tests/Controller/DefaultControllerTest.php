<?php
namespace StudyApp\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use StudyApp\UserBundle\Entity\User;


class DefaultControllerTest extends WebTestCase
{
    public function testActivateAction()
    {
        $client = static::createClient();
        $t = $client->getContainer()->get('translator');
        $this->_getEnvParam($client, $em, $r, 'StudyAppUserBundle:User');
        try {
        $arr  = $em->createQuery("SELECT u.id FROM StudyAppUserBundle:User AS u WHERE u.activation_code != '' AND u.activation_code IS NOT NULL")->
                                    setMaxResults(1)-> getSingleResult();
        } catch (Exception $E) {
            $arr = array();
        }
        $need_clear = false;
        if (isset($arr['id']) && intval($arr['id'])) {
            $test_user = $r->find($test_user_id = $arr['id']);
        } else {
            $test_user = $this->_createTestUser($em, $r);
            $test_user_id = $test_user->getId();
            $need_clear = true;
        }
        $code = $test_user->getActivationCode();
        $crawler = $client->request('GET', "/confirmemail/{$code}");
        $this->assertTrue($crawler->filter('html:contains("Регистрация успешна, вы можете войти используя ваши логин и пароль")')->count() > 0);
        $crawler = $client->request('GET', "/user/activate/{$code}");
        $this->assertFalse($crawler->filter('html:contains("Confirmation success!")')->count() > 0);
        if ($need_clear) {
            $em->createQuery("DELETE FROM StudyAppUserBundle:User AS u WHERE u.id = {$test_user_id}")->getSingleResult();
        }
    }
    public function testAddAction()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/user/add');
        $this->assertTrue($crawler->filter('html:contains("Welcome!")')->count() > 0);
    }
    public function testChangeProfileAction()
    {
        $client = static::createClient();
        $t = $client->getContainer()->get("translator");
        $user = $this->_createTestUserEx('123456', $client, $em, $r);
        
        $crawler = $client->request('GET', '/');
        $msg = $t->trans('Login');
        $this->assertTrue($crawler->filter('html:contains("'. $msg .'")')->count() > 0);
        $form = $crawler->selectButton($t->trans('Login'))->form(array(
            '_username' => $user->getUsername(),
            '_password' => '123456'
        ));
        $client->submit($form);
        
        $crawler = $client->request('GET', '/profile');
        
        $form = $crawler->selectButton($t->trans('Save'))->form(array(
            'user_type[first_name]' => 'Andrey',
            'user_type[last_name]' => 'Lamzin'
        ));
        $client->submit($form);
        $crawler = $client->request('GET', '/profile');
        $s = $crawler->filter('input[type=text]')->first()->attr('value');
        $this->assertTrue($s == 'Andrey');
        $user = $this->_deleteTestUser($user, $em);
    }
    public function testLoginAction()
    {
        $client = static::createClient();
        $t = $client->getContainer()->get("translator");
        $msg = $t->trans('Login');
        $this->_getEnvParam($client, $em, $r, 'StudyAppUserBundle:User');
        $user = $this->_createTestUser($em, $r, $uname = 'tstuser' . date('His'), $pwd = '123456', $client);
        $crawler = $client->request('GET', '/');
        $this->assertTrue($crawler->filter('html:contains("'. $msg .'")')->count() > 0);
        $form = $crawler->selectButton($t->trans('Login'))->form(array(
            '_username' => $uname,
            '_password' => $pwd
        ));
        $client->submit($form);
        $crawler = $client->request('GET', '/');
        $this->assertTrue($crawler->filter('html:contains("Это пример приложения")')->count() > 0);
        $this->_deleteTestUser($user, $em);
    }
    private function _createTestUserEx($password, $client, &$em, &$r)
    {
        $this->_getEnvParam($client, $em, $r, 'StudyAppUserBundle:User');
        return $this->_createTestUser($em, $r, $uname = 'tstuser' . date('His'), $password, $client);
        
    }
    private function _createTestUser($em, $r, $username = null, $password = null, $client = null)
    {
        $user = new User();
        $user->setEmailIsVerify(1);
        $code = '1';
        $user->setFirstName( md5( uniqid(date('YmdHis') . $code) ) );
        $user->setEmail( 'test' . md5( uniqid(date('YmdHis') . $code) ) . '@test.test' );
        $user->setLastName( md5( uniqid(date('YmdHis') . $code) ) );
        $user->setRole(1);
        $username = $username ? $username : md5( uniqid(date('YmdHis') . $code) );
        if (!$password && !$client) {
            $password = $username;
        } elseif($client){
            $encoder = $client->getContainer()->get('security.encoder_factory')->getEncoder($user);
            $password = $encoder->encodePassword($password, $user->getSalt());
        }
        $user->setUsername( $username );
        $user->setPassword( $password );
        $user->setActivationCode( md5( uniqid(date('YmdHis') . $code) ) );
        $em->persist($user);
        $em->flush();
        return $user;
    }
    
    private function _getEnvParam($client, &$em, &$r, $scope)
    {
        $doctrine = $client->getContainer()->get("doctrine");
        $em = $doctrine->getEntityManager();
        $r = $doctrine->getRepository($scope);
    }
    private function _deleteTestUser($user, $em)
    {
        $em->createQuery("DELETE FROM StudyAppUserBundle:User AS u WHERE u.id = {$user->getId()}")->getSingleResult();
    }
}
