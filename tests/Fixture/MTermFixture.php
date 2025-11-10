<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MTermFixture
 */
class MTermFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'm_term';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'term_id' => 1,
                'start_date' => '2025-06-30',
                'end_date' => '2025-06-30',
                'entry_start_date' => '2025-06-30',
                'add_deadline_date' => '2025-06-30',
                'upd_deadline_date' => '2025-06-30',
                'del_flg' => '',
                'create_user' => 'Lor',
                'create_date' => '2025-06-30 18:02:12',
                'update_user' => 'Lor',
                'update_date' => '2025-06-30 18:02:12',
            ],
        ];
        parent::init();
    }
}
