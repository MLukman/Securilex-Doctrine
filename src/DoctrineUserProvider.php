<?php

namespace Securilex\Doctrine;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DoctrineUserProvider implements UserProviderInterface
{
    const NOT_FOUND = 'Username "%s" does not exist.';

    /**
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * The User entity full class name
     * @var string
     */
    protected $userClass;

    /**
     * The column name for the username in the database table
     * @var string
     */
    protected $usernameColumn;

    /**
     * Additional criteria when querying for User
     * @var array
     */
    protected $additionalCriteria;

    public function __construct(EntityManager $em, $userClass,
                                $usernameColumn = 'username',
                                array $additionalCriteria = array())
    {
        $this->em                 = $em;
        $this->userClass          = $userClass;
        $this->usernameColumn     = $usernameColumn;
        $this->additionalCriteria = $additionalCriteria;
    }

    /**
     * Load User by username
     * @param string $username
     * @return UserInterface
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository($this->userClass)->findOneBy(
            array_merge(
                $this->additionalCriteria,
                array($this->usernameColumn => $username)
            )
        );
        if (!$user) {
            $ex = new UsernameNotFoundException(
                sprintf(self::NOT_FOUND, $username));
            $ex->setUsername($username);
            throw $ex;
        }
        return $user;
    }

    /**
     * Refresh the User object
     * @param UserInterface $user
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        try {
            return $this->loadUserByUsername($user->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new UnsupportedUserException(sprintf('User "%s" did not come from this provider (%s).',
                $user->getUsername(), get_called_class()));
        }
    }

    /**
     * Check if the passed class name is supported or not
     * @param string $class The class name
     * @return boolean Whether the passed class is supported
     */
    public function supportsClass($class)
    {
        if ($class == $this->userClass) {
            return true;
        }
        $c = new \ReflectionClass($class);
        return $c->isSubclassOf($this->userClass);
    }
}