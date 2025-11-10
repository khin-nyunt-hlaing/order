<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'username' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'secret_question' => 'Lorem ipsum dolor sit amet',
                'secret_answer' => 'Lorem ipsum dolor sit amet',
                'reset_token' => 'Lorem ipsum dolor sit amet',
                'reset_token_expire' => '2025-04-21 14:31:12',
                'created' => '2025-04-21 14:31:12',
                'modified' => '2025-04-21 14:31:12',
            ],
        ];
        parent::init();
    }
}
