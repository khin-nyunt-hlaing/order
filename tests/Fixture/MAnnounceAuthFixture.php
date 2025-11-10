<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MANNOUNCEAUTHFixture
 */
class MANNOUNCEAUTHFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_announce_auth';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'USER_ID' => '5cd63c35-8168-4ae2-924d-049aebfcf994',
                'ANNOUNCE_ID' => 1,
                'SPEC_TYPE' => '',
                'CREATE_USER' => 'Lor',
                'CREATE_DATE' => '2025-06-11 14:03:57',
                'UPDATE_USER' => 'Lor',
                'UPDATE_DATE' => '2025-06-11 14:03:57',
            ],
        ];
        parent::init();
    }
}
