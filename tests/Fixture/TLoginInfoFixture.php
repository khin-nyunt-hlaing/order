<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TLoginInfoFixture
 */
class TLoginInfoFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 't_login_info';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'user_id' => 'c2f1c2f0-2e05-4ae7-b643-aecabdcd364d',
                'login_date' => '2025-08-21 15:34:46',
                'login_result' => '',
                'create_user' => 'Lorem ipsum dolor sit amet',
                'create_date' => '2025-08-21 15:34:46',
                'update_user' => 'Lorem ipsum dolor sit amet',
                'update_date' => '2025-08-21 15:34:46',
            ],
        ];
        parent::init();
    }
}
