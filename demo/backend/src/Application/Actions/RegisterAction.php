<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Uuid;
use App\Application\WebAuthn\AbstractWebAuthnCeremony;
use App\Application\WebAuthn\RegisterCeremony;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\UserRepositoryInterface;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Webauthn\PublicKeyCredentialUserEntity;

use function random_bytes;

class RegisterAction extends Action
{
    public const SESSION_CHALLENGE_KEY = 'register_challenge';

    public function __construct(
        private readonly RegisterCeremony $registerCeremony,
        private readonly UserRepositoryInterface $userRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws \Exception
     */
    protected function action(): Response
    {
        $data = $this->getFormData();
        $data['username'] = \trim($data['username'] ?? '');
        if (empty($data['username'])) {
            throw new HttpBadRequestException($this->request, 'Por favor, preencha seu nome de usuário.');
        }

        try {
            $userEntity = $this->userRepository->findOneByUserName($data['username']);
        } catch (UserNotFoundException) {
            $userEntity = PublicKeyCredentialUserEntity::create(
                $data['username'],
                Uuid::generate(),
                $data['username'],
            );
        }

        // Challenge
        $challenge = random_bytes(32);
        $_SESSION[self::SESSION_CHALLENGE_KEY] = Base64UrlSafe::encodeUnpadded($challenge);

        $options = $this->registerCeremony->getCredentialCreationOptions(
            $userEntity,
            $challenge
        );

        $this->userRepository->save($userEntity);

        return $this->respondWithData($options);
    }
}
