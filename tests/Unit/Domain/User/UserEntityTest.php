<?php

declare(strict_types=1);

use App\Domain\User\Entities\User;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserId;

describe('User Entity', function () {

    it('cria usuário com método de fábrica', function () {
        $email    = new Email('john@example.com');
        $password = HashedPassword::fromPlain('secret123');
        $user     = User::create('John Doe', $email, $password);

        expect($user->name())->toBe('John Doe');
        expect($user->email()->value())->toBe('john@example.com');
        expect($user->password())->toBeInstanceOf(HashedPassword::class);
        expect($user->password()->verify('secret123'))->toBeTrue();
        expect($user->id())->toBeInstanceOf(UserId::class);
    });

    it('cria com id explícito via construtor', function () {
        $id       = UserId::generate();
        $email    = new Email('jane@example.com');
        $password = HashedPassword::fromHash(password_hash('pass', PASSWORD_BCRYPT));
        $user     = new User($id, 'Jane Doe', $email, $password);

        expect($user->id()->equals($id))->toBeTrue();
    });

    it('altera nome corretamente', function () {
        $user = User::create('Old Name', new Email('user@example.com'), HashedPassword::fromPlain('pass'));

        $user->changeName('New Name');

        expect($user->name())->toBe('New Name');
    });

    it('gera ids únicos para usuários diferentes', function () {
        $user1 = User::create('User 1', new Email('user1@example.com'), HashedPassword::fromPlain('pass'));
        $user2 = User::create('User 2', new Email('user2@example.com'), HashedPassword::fromPlain('pass'));

        expect($user1->id()->equals($user2->id()))->toBeFalse();
    });

    it('novo usuário está ativo por padrão', function () {
        $user = User::create('Test', new Email('test@example.com'), HashedPassword::fromPlain('pass'));

        expect($user->isActive())->toBeTrue();
        expect($user->deactivatedAt())->toBeNull();
    });

    it('desativa o usuário e registra o momento', function () {
        $user = User::create('Test', new Email('test@example.com'), HashedPassword::fromPlain('pass'));

        $user->deactivate();

        expect($user->isActive())->toBeFalse();
        expect($user->deactivatedAt())->not->toBeNull();
        expect($user->deactivatedAt())->toBeInstanceOf(DateTimeImmutable::class);
    });

    it('reconstitui usuário desativado com deactivatedAt informado', function () {
        $id       = UserId::generate();
        $email    = new Email('deactivated@example.com');
        $password = HashedPassword::fromHash(password_hash('pass', PASSWORD_BCRYPT));
        $at       = new DateTimeImmutable('2024-01-01 00:00:00');

        $user = new User($id, 'Inactive', $email, $password, $at);

        expect($user->isActive())->toBeFalse();
        expect($user->deactivatedAt())->toBe($at);
    });
});
