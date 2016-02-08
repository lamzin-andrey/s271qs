<?php
namespace StudyApp\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserRegistrationType extends AbstractType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('username')
			->add('email')
			->add('password')
			->add('first_name')
			->add('last_name');
	}
	
	public function getName() {
		return 'user_registration_type';
	}
}
