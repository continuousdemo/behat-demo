<?php

namespace Application\Mapper;

class User extends MapperDoctrineAbstract
{
    /**
     * Get the user with the given username.
     *
     * @param $username The username.
     * @return \Application\Entity\User
     */
    public function findByUsername($username)
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * Get the user with the given email address.
     *
     * @param $email The email address.
     * @return \Application\Entity\User
     */
    public function findByEmail($email)
    {
        return $this->findOneBy(['email' => $email]);
    }
}