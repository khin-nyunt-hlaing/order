<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TAnnounceFixture
 */
class TAnnounceFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 't_announce';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'announce_id' => 1,
                'announce_div' => '',
                'announce_start_date' => '2025-08-04',
                'announce_end_date' => '2025-08-04',
                'announce_title' => 'Lorem ipsum dolor sit amet',
                'announce_text' => 'Lorem ipsum dolor sit amet',
                'temp_filename1' => 'Lorem ipsum dolor sit amet',
                'temp_filename2' => 'Lorem ipsum dolor sit amet',
                'temp_filename3' => 'Lorem ipsum dolor sit amet',
                'temp_filename4' => 'Lorem ipsum dolor sit amet',
                'temp_filename5' => 'Lorem ipsum dolor sit amet',
                'visibility' => '',
                'del_flg' => '',
                'create_user' => 'Lorem ipsum dolor sit amet',
                'create_date' => '2025-08-04 19:31:02',
                'update_user' => 'Lorem ipsum dolor sit amet',
                'update_date' => '2025-08-04 19:31:02',
            ],
        ];
        parent::init();
    }
}
