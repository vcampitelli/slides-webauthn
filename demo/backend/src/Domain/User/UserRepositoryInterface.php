<?php

namespace App\Domain\User;

use Webauthn\PublicKeyCredentialUserEntity;

interface UserRepositoryInterface
{
    public function save(PublicKeyCredentialUserEntity $userEntity): void;

    /**
     * @param string $id
     * @return PublicKeyCredentialUserEntity
     * @throws UserNotFoundException
     */
    public function findOneByUserId(string $id): PublicKeyCredentialUserEntity;

    /**
     * @param string $username
     * @return PublicKeyCredentialUserEntity
     * @throws UserNotFoundException
     */
    public function findOneByUserName(string $username): PublicKeyCredentialUserEntity;
}
