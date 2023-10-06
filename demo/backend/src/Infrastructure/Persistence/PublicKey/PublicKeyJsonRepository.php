<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\PublicKey;

use App\Domain\PublicKey\PublicKeyCredentialSourceCollection;
use App\Domain\PublicKey\PublicKeyNotFoundException;
use App\Domain\PublicKey\PublicKeyRepositoryInterface;
use App\Infrastructure\Persistence\AbstractJsonRepository;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class PublicKeyJsonRepository extends AbstractJsonRepository implements PublicKeyRepositoryInterface
{
    public function findOneByCredentialId(string $credentialId): PublicKeyCredentialSource
    {
        $data = $this->loadData();
        /**
         * @var string $key
         * @var PublicKeyCredentialSource $credential
         */
        foreach ($data as $key => $credential) {
            if (\hash_equals($key, $credentialId)) {
                return $credential;
            }
        }

        throw new PublicKeyNotFoundException("Credencial {$credentialId} não encontrada");
    }

    public function save(PublicKeyCredentialSource $credential): void
    {
        $data = $this->loadData();
        $data[Base64UrlSafe::encodeUnpadded($credential->publicKeyCredentialId)] = $credential;
        $this->writeData($data);
    }

    public function findAllForUserEntity(PublicKeyCredentialUserEntity $userEntity): PublicKeyCredentialSourceCollection
    {
        error_log(json_encode($this->loadData()));
        error_log(json_encode(\array_filter(
            $this->loadData(),
            fn(PublicKeyCredentialSource $credential) => $credential->userHandle === $userEntity->id,
        )));
        return new PublicKeyCredentialSourceCollection(
            \array_filter(
                $this->loadData(),
                fn(PublicKeyCredentialSource $credential) => $credential->userHandle === $userEntity->id,
            )
        );
    }

    /**
     * @throws InvalidDataException
     */
    protected function jsonUnserialize(mixed $value): PublicKeyCredentialSource
    {
        return PublicKeyCredentialSource::createFromArray($value);
    }
}
