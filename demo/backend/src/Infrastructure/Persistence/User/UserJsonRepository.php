<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Persistence\AbstractJsonRepository;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialUserEntity;

class UserJsonRepository extends AbstractJsonRepository implements UserRepositoryInterface
{
    public function save(PublicKeyCredentialUserEntity $userEntity): void
    {
        $data = $this->loadData();
        $data[$userEntity->id] = $userEntity;
        $this->writeData($data);
    }

    /**
     * @param string $id
     * @return PublicKeyCredentialUserEntity
     * @throws UserNotFoundException
     */
    public function findOneByUserId(string $id): PublicKeyCredentialUserEntity
    {
        $data = $this->loadData();
        /** @var PublicKeyCredentialUserEntity $user */
        foreach ($data as $user) {
            if (\hash_equals($user->id, $id)) {
                return $user;
            }
        }

        throw new UserNotFoundException("Usuário {$id} não encontrado");
    }

    /**
     * @param string $username
     * @return PublicKeyCredentialUserEntity
     * @throws UserNotFoundException
     */
    public function findOneByUserName(string $username): PublicKeyCredentialUserEntity
    {
        $data = $this->loadData();
        /** @var PublicKeyCredentialUserEntity $user */
        foreach ($data as $user) {
            if (\hash_equals($user->name, $username)) {
                return $user;
            }
        }

        throw new UserNotFoundException("Usuário {$username} não encontrado");
    }

    /**
     * @throws InvalidDataException
     */
    protected function jsonUnserialize(mixed $value): PublicKeyCredentialUserEntity
    {
        return PublicKeyCredentialUserEntity::createFromArray($value);
    }
}
