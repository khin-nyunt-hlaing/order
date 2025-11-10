<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MDispUserFixture
 */
class MDispUserFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_disp_user';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'user_id' => 'c7bf3548-f0bd-42a1-91b0-44c9dbd2b09a',
                'disp_user_id' => 'f2cf0fc8-8154-4ea2-9165-ea746506dded',
                'create_user' => 'Lor',
                'create_date' => '2025-05-02 14:21:29',
                'update_user' => 'Lor',
                'update_date' => '2025-05-02 14:21:29',
            ],
        ];
        parent::init();
    }
}
