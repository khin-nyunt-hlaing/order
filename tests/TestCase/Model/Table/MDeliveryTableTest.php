<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MDeliveryTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MDeliveryTable Test Case
 */
class MDeliveryTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MDeliveryTable
     */
    protected $MDelivery;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MDelivery',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MDelivery') ? [] : ['className' => MDeliveryTable::class];
        $this->MDelivery = $this->getTableLocator()->get('MDelivery', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MDelivery);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MDeliveryTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
