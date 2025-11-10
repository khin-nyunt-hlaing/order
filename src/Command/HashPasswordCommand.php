<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Authentication\PasswordHasher\DefaultPasswordHasher; 

class HashPasswordCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $plain = $args->getArgument('password');

        if (!$plain) {
            $io->err('パスワードを指定してください。');
            return Command::FAILURE;
        }

        $hashed = (new DefaultPasswordHasher())->hash($plain);
        $io->out('ハッシュ値:');
        $io->out($hashed);

        return Command::SUCCESS;
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->addArgument('password', [
            'help' => 'ハッシュ化したいプレーンパスワード',
            'required' => true,
        ]);

        return $parser;
    }
}
