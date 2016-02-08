<?php

namespace StudyApp\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use StudyApp\UserBundle\Entity\StudyAppUserProvider;
/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity
 * @UniqueEntity(fields="username", message="Username already used")
 * @UniqueEntity(fields="email", message="Email already used")
 */
class User implements UserInterface, EquatableInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=64, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=60)
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=32)
     * @Assert\NotBlank()
     */
    private $first_name;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=32)
     * @Assert\NotBlank()
     */
    private $last_name;

    /**
     * @var integer
     *
     * @ORM\Column(name="role", type="integer")
     */
    private $role;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=64, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var boolean
     *
     * @ORM\Column(name="email_is_verify", type="boolean")
     */
    private $email_is_verify;

	/**
     * @var string
     *
     * @ORM\Column(name="activation_code", type="string", length=32)
     */
    private $activation_code;
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set role
     *
     * @param integer $role
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return integer 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email_is_verify
     *
     * @param boolean $emailIsVerify
     * @return User
     */
    public function setEmailIsVerify($emailIsVerify)
    {
        $this->email_is_verify = $emailIsVerify;

        return $this;
    }

    /**
     * Get email_is_verify
     *
     * @return boolean 
     */
    public function getEmailIsVerify()
    {
        return $this->email_is_verify;
    }
   /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
		return array('ROLE_USER');
	}

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt() 
    {
		return null;
	}

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
	}
	
    public function isEqualTo(UserInterface $user)
    {
    	if (!$user instanceof User) {
    		return false;
    	}
    
    	if ($this->password !== $user->getPassword()) {
    		return false;
    	}
    
    	if ($this->username !== $user->getUsername()) {
    		return false;
    	}
    
    	return true;
    }
    /**
     * Set activation code
     *
     * @param string $code
     * @return User
    */
    public function setActivationCode($code)
    {
        $this->activation_code = $code;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getActivationCode()
    {
        return $this->activation_code;
    }
    
}
