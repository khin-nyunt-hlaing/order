<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MANNOUNCEAUTHTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MANNOUNCEAUTHTable Test Case
 */
class MANNOUNCEAUTHTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MANNOUNCEAUTHTable
     */
    protected $MANNOUNCEAUTH;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MANNOUNCEAUTH',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MANNOUNCEAUTH') ? [] : ['className' => MANNOUNCEAUTHTable::class];
        $this->MANNOUNCEAUTH = $this->getTableLocator()->get('MANNOUNCEAUTH', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MANNOUNCEAUTH);

        parent::tearDown();
    }
}
