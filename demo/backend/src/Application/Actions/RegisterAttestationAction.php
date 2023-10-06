<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\WebAuthn\RegisterCeremony;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class RegisterAttestationAction extends Action
{
    public function __construct(
        private readonly RegisterCeremony $registerCeremony,
        LoggerInterface $logger
    ) {
        parent::__construct($logger);
    }

    /**
     * @throws \Exception
     */
    protected function action(): Response
    {
        if (empty($_SESSION[RegisterAction::SESSION_CHALLENGE_KEY])) {
            throw new HttpUnauthorizedException($this->request, 'Processo de registro não foi iniciado');
        }

        $challenge = $_SESSION[RegisterAction::SESSION_CHALLENGE_KEY];
        unset($_SESSION[RegisterAction::SESSION_CHALLENGE_KEY]);

        $formData = $this->getFormData();
        $data = (string) $this->request->getBody();
        if ((empty($data)) || (empty($formData['user'])) || (empty($formData['user']['id']))) {
            throw new HttpBadRequestException(
                $this->request,
                'Requisição inválida. Por favor, inicie o processo novamente.'
            );
        }

        try {
            $userEntity = $this->registerCeremony->attestation(
                challenge: $challenge,
                userId: Base64UrlSafe::decode($formData['user']['id']),
                credentialCreationOptions: $data,
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
