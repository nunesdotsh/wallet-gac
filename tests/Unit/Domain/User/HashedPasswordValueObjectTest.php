<?php

declare(strict_types=1);

use App\Domain\User\ValueObjects\HashedPassword;

describe('HashedPassword', function () {

    it('cria hash a partir de senha em texto plano', function () {
        $vo = HashedPassword::fromPlain('minha-senha');

        expect($vo->value())->toBeString()->not->toBeEmpty();
        expect($vo->value())->not->toBe('minha-senha');
    });

    it('gera hashes diferentes para a mesma senha (salt aleatório)', function () {
        $a = HashedPassword::fromPlain('mesma-senha');
        $b = HashedPassword::fromPlain('mesma-senha');

        expect($a->value())->not->toBe($b->value());
    });

    it('verifica senha correta com sucesso', function () {
        $vo = HashedPassword::fromPlain('senha-correta');

        expect($vo->verify('senha-correta'))->toBeTrue();
    });

    it('rejeita senha incorreta', function () {
        $vo = HashedPassword::fromPlain('senha-correta');

        expect($vo->verify('senha-errada'))->toBeFalse();
    });

    it('reconstrói a partir de hash armazenado', function () {
        $original = HashedPassword::fromPlain('minha-senha');
        $restored = HashedPassword::fromHash($original->value());

        expect($restored->verify('minha-senha'))->toBeTrue();
        expect($restored->value())->toBe($original->value());
    });

    it('hash segue o algoritmo bcrypt', function () {
        $vo = HashedPassword::fromPlain('qualquer-senha');

        expect($vo->value())->toStartWith('$2y$');
    });

    it('equals retorna true para hashes idênticos', function () {
        $hash = password_hash('senha', PASSWORD_BCRYPT);
        $a = HashedPassword::fromHash($hash);
        $b = HashedPassword::fromHash($hash);

        expect($a->equals($b))->toBeTrue();
    });

    it('equals retorna false para hashes diferentes', function () {
        $a = HashedPassword::fromPlain('senha');
        $b = HashedPassword::fromPlain('senha');

        expect($a->equals($b))->toBeFalse();
    });
});
