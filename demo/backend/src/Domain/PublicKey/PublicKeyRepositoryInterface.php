<?php

declare(strict_types=1);

namespace App\Domain\PublicKey;

use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

interface PublicKeyRepositoryInterface
{
    public function findAllForUserEntity(
        PublicKeyCredentialUserEntity $userEntity
    ): PublicKeyCredentialSourceCollection;

    /**
     * @param string $credentialId
     * @return PublicKeyCredentialSource
     * @throws PublicKeyNotFoundException
     */
    public function findOneByCredentialId(string $credentialId): PublicKeyCredentialSource;

    public function save(PublicKeyCredentialSource $credential): void;
}
