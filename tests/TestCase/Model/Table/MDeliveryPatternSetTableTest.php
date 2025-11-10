<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MDeliveryPatternSetTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MDeliveryPatternSetTable Test Case
 */
class MDeliveryPatternSetTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MDeliveryPatternSetTable
     */
    protected $MDeliveryPatternSet;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MDeliveryPatternSet',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MDeliveryPatternSet') ? [] : ['className' => MDeliveryPatternSetTable::class];
        $this->MDeliveryPatternSet = $this->getTableLocator()->get('MDeliveryPatternSet', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MDeliveryPatternSet);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MDeliveryPatternSetTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
