<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\TimeSheet\TimeSheetRepository;
use App\Infrastructure\Persistence\User\UserRepository;
use Cake\Database\Connection;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

/**
 * Needed to give connection to the repositories
 *
 * @param ContainerBuilder $containerBuilder
 */
return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions(
        [
            UserRepository::class => function (ContainerInterface $container) {
                $pdo = $container->get(Connection::class);
                return new UserRepository($pdo);
            },
            TimeSheetRepository::class => function (ContainerInterface $container) {
                $pdo = $container->get(Connection::class);
                return new TimeSheetRepository($pdo);
            },

        ]
    );
};