<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TDeliOrderTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TDeliOrderTable Test Case
 */
class TDeliOrderTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TDeliOrderTable
     */
    protected $TDeliOrder;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.TDeliOrder',
        'app.MTerm',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TDeliOrder') ? [] : ['className' => TDeliOrderTable::class];
        $this->TDeliOrder = $this->getTableLocator()->get('TDeliOrder', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TDeliOrder);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\TDeliOrderTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
