<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\WebAuthn\AbstractWebAuthnCeremony;
use App\Application\WebAuthn\LoginCeremony;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class LoginAssertionAction extends Action
{
    public function __construct(
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
        if (empty($_SESSION[LoginAction::SESSION_CHALLENGE_KEY])) {
            return $this->respondWithData([
                'error' => 'Processo de registro não foi iniciado',
            ], StatusCodeInterface::STATUS_UNAUTHORIZED);
        }
        $challenge = $_SESSION[LoginAction::SESSION_CHALLENGE_KEY];
        unset($_SESSION[LoginAction::SESSION_CHALLENGE_KEY]);

        $formData = $this->getFormData();
        $data = (string) $this->request->getBody();
        if ((empty($data)) || (empty($formData['user'])) || (empty($formData['user']['id']))) {
            return $this->respondWithData([
                'error' => 'Requisição inválida',
            ], StatusCodeInterface::STATUS_BAD_REQUEST);
        }

        try {
            $userEntity = $this->loginCeremony->assertion(
                challenge: $challenge,
                userId: $formData['user']['id'],
                credentialRequestOptions: $data,
                hostname: $this->request->getUri()->getHost(),
            );
        } catch (\Throwable $t) {
            throw new HttpBadRequestException($this->request, $t->getMessage(), $t);
        }

        return $this->respondWithData([
            'status' => true,
            'user' => $userEntity,
        ]);
    }
}
