<?php

declare(strict_types=1);

use App\Domain\PublicKey\PublicKeyRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Persistence\PublicKey\PublicKeyJsonRepository;
use App\Infrastructure\Persistence\User\UserJsonRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        PublicKeyRepositoryInterface::class => \DI\autowire(PublicKeyJsonRepository::class),
        UserRepositoryInterface::class => \DI\autowire(UserJsonRepository::class),
    ]);
};
