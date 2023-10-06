<?php

declare(strict_types=1);

namespace App\Application\WebAuthn;

use App\Domain\PublicKey\PublicKeyNotFoundException;
use App\Domain\User\UserNotFoundException;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class LoginCeremony extends AbstractWebAuthnCeremony
{
    public function getCredentialRequestOptions(
        PublicKeyCredentialUserEntity $userEntity,
        string $challenge
    ): PublicKeyCredentialRequestOptions {
        $allowCredentials = $this->getAllowCredentialsForUser($userEntity);
        return $this->doGetCredentialRequestOptionsFor(
            $challenge,
            $allowCredentials,
        );
    }

    protected function getAllowCredentialsForUser(PublicKeyCredentialUserEntity $userEntity): array
    {
        $registeredAuthenticators = $this->publicKeyRepository->findAllForUserEntity($userEntity);

        return \array_values(
            \array_map(
                static function (PublicKeyCredentialSource $credential): PublicKeyCredentialDescriptor {
                    return $credential->getPublicKeyCredentialDescriptor();
                },
                $registeredAuthenticators->toArray()
            )
        );
    }

    protected function doGetCredentialRequestOptionsFor(
        string $challenge,
        array $allowCredentials
    ): PublicKeyCredentialRequestOptions {
        return PublicKeyCredentialRequestOptions::create(
            challenge: $challenge,
            rpId: $this->getRpId(),
            allowCredentials: $allowCredentials
        );
    }

    /**
     * @throws \Throwable
     * @throws UserNotFoundException
     * @throws PublicKeyNotFoundException
     */
    public function assertion(
        string $challenge,
        string $userId,
        string $credentialRequestOptions,
        string $hostname
    ): PublicKeyCredentialUserEntity {
        $userEntity = $this->userRepository->findOneByUserId($userId);

        $publicKeyCredential = $this->loadCredentialByString($credentialRequestOptions);
        if (!$publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
            throw new \RuntimeException('Não foi possível validar a chave pública');
        }

        $publicKeyCredentialSource = $this->publicKeyRepository->findOneByCredentialId(
            Base64UrlSafe::encodeUnpadded($publicKeyCredential->rawId)
        );

        $allowCredentials = $this->getAllowCredentialsForUser($userEntity);

        $authenticatorAssertionResponse = $publicKeyCredential->response;
        $publicKeyCredentialSource = $this->getAssertionValidator()->check(
            $publicKeyCredentialSource,
            $authenticatorAssertionResponse,
            $this->doGetCredentialRequestOptionsFor(
                $challenge,
                $allowCredentials,
            ),
            $hostname,
            $userEntity->id,
            [$this->getRpId()]
        );

        $this->publicKeyRepository->save($publicKeyCredentialSource);

        return $userEntity;
    }

    protected function getAssertionValidator(): AuthenticatorAssertionResponseValidator
    {
        return AuthenticatorAssertionResponseValidator::create(
            null,                           //Deprecated Public Key Credential Source Repository. Please set null.
            null,                           //Deprecated Token Binding Handler. Please set null.
            $this->getExtensionOutputCheckerHandler(),
            $this->getAlgorithmManager(),
        );
    }

    protected function getAlgorithmManager(): Manager
    {
        return Manager::create()
            ->add(
                ES256::create(),
            );
    }
}
