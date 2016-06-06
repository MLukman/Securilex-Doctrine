<?php

namespace Securilex\Doctrine;

use Securilex\Authentication\User\MutableUserInterface;
use Securilex\Authentication\User\MutableUserProviderInterface;

class DoctrineMutableUserProvider extends DoctrineUserProvider implements MutableUserProviderInterface
{

    public function saveUser(MutableUserInterface $user)
    {
        $this->em->persist($user);
        $this->em->flush($user);
    }

    public function removeUser(MutableUserInterface $user)
    {
        $this->em->remove($user);
        $this->em->flush($user);
    }
}