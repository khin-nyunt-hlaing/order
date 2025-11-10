<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MAnnounceUserFixture
 */
class MAnnounceUserFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_announce_user';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'USER_ID' => 'e6051aee-9d1c-4e2f-92ff-997295d001b1',
                'ANNOUNCE_ID' => 1,
                'CREATE_USER' => 'Lor',
                'CREATE_DATE' => '2025-06-19 14:10:24',
                'UPDATE_USER' => 'Lor',
                'UPDATE_DATE' => '2025-06-19 14:10:24',
            ],
        ];
        parent::init();
    }
}
