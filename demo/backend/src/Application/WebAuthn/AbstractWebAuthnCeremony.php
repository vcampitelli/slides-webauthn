<?php

declare(strict_types=1);

namespace App\Application\WebAuthn;

use App\Domain\PublicKey\PublicKeyRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRpEntity;

abstract class AbstractWebAuthnCeremony
{
    public function __construct(
        protected PublicKeyRepositoryInterface $publicKeyRepository,
        protected UserRepositoryInterface $userRepository,
    ) {
    }

    protected function getRpEntity(): PublicKeyCredentialRpEntity
    {
        return PublicKeyCredentialRpEntity::create(
            'WebAuthn by vcampitelli',
            $this->getRpId(),
        );
    }

    protected function getRpId(): string
    {
        return $_SERVER['SERVER_NAME'];
        return 'webauthn.local';
    }

    protected function getExtensionOutputCheckerHandler(): ExtensionOutputCheckerHandler
    {
        return ExtensionOutputCheckerHandler::create();
    }

    protected function getAttestationStatementSupportManager(): AttestationStatementSupportManager
    {
        // The manager will receive data to load and select the appropriate
        $attestationStatementSupportManager = AttestationStatementSupportManager::create();
        $attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
        return $attestationStatementSupportManager;
    }

    protected function loadCredentialByString(string $data): PublicKeyCredential
    {
        $attestationObjectLoader = AttestationObjectLoader::create(
            $this->getAttestationStatementSupportManager(),
        );

        $publicKeyCredentialLoader = PublicKeyCredentialLoader::create(
            $attestationObjectLoader
        );

        return $publicKeyCredentialLoader->load($data);
    }
}
