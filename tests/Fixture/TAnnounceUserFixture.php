<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TAnnounceUserFixture
 */
class TAnnounceUserFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 't_announce_user';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'ANNOUNCE_ID' => 1,
                'USER_ID' => '997d2a28-8347-4c0e-b3c8-0e42dc4a5a5a',
                'CREATE_USER' => 'Lor',
                'CREATE_DATE' => '2025-06-20 15:17:58',
                'UPDATE_USER' => 'Lor',
                'UPDATE_DATE' => '2025-06-20 15:17:58',
            ],
        ];
        parent::init();
    }
}
