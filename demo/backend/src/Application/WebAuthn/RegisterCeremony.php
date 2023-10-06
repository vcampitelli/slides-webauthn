<?php

declare(strict_types=1);

namespace App\Application\WebAuthn;

use App\Domain\User\UserNotFoundException;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialUserEntity;

class RegisterCeremony extends AbstractWebAuthnCeremony
{
    public function getCredentialCreationOptions(
        PublicKeyCredentialUserEntity $userEntity,
        string $challenge,
    ): PublicKeyCredentialCreationOptions {
        return PublicKeyCredentialCreationOptions::create(
            $this->getRpEntity(),
            $userEntity,
            $challenge,
            [
                PublicKeyCredentialParameters::createPk(ES256::ID)
            ],
        );
    }

    /**
     * @throws \Throwable
     * @throws UserNotFoundException
     */
    public function attestation(
        string $challenge,
        string $userId,
        string $credentialCreationOptions,
        string $hostname
    ): PublicKeyCredentialUserEntity {
        $userEntity = $this->userRepository->findOneByUserId($userId);

        $publicKeyCredential = $this->loadCredentialByString($credentialCreationOptions);
        if (!$publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new \RuntimeException(
                'Não foi possível validar a chave pública'
            );
        }

        $authenticatorAttestationResponse = $publicKeyCredential->response;
        $publicKeyCredentialSource = $this->getAttestationValidator()->check(
            authenticatorAttestationResponse: $authenticatorAttestationResponse,
            publicKeyCredentialCreationOptions: $this->getCredentialCreationOptions(
                $userEntity,
                $challenge,
            ),
            request: $hostname,
            securedRelyingPartyId: [$this->getRpId()],
        );

        $this->publicKeyRepository->save($publicKeyCredentialSource);

        return $userEntity;
    }

    protected function getAttestationValidator(): AuthenticatorAttestationResponseValidator
    {
        return AuthenticatorAttestationResponseValidator::create(
            $this->getAttestationStatementSupportManager(),
            null, //Deprecated Public Key Credential Source Repository. Please set null.
            null, //Deprecated Token Binding Handler. Please set null.
            $this->getExtensionOutputCheckerHandler(),
        );
    }
}
