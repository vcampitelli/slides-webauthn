<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\WebAuthn\LoginCeremony;
use App\Domain\User\UserRepositoryInterface;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

use function random_bytes;

class LoginAction extends Action
{
    const SESSION_CHALLENGE_KEY = 'login_challenge';

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoginCeremony $loginCeremony,
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

        $userEntity = $this->userRepository->findOneByUserName($data['username']);

        // Challenge
        $challenge = random_bytes(32);
        $_SESSION[self::SESSION_CHALLENGE_KEY] = Base64UrlSafe::encodeUnpadded($challenge);

        $publicKeyCredentialRequestOptions = $this->loginCeremony->getCredentialRequestOptions(
            $userEntity,
            $challenge
        );

        $data = $publicKeyCredentialRequestOptions->jsonSerialize();
        $data['user'] = [
            'id' => $userEntity->id,
        ];

        return $this->respondWithData($data);
    }
}
