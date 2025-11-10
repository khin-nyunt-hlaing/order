<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MDeliveryPatternTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MDeliveryPatternTable Test Case
 */
class MDeliveryPatternTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MDeliveryPatternTable
     */
    protected $MDeliveryPattern;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MDeliveryPattern',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MDeliveryPattern') ? [] : ['className' => MDeliveryPatternTable::class];
        $this->MDeliveryPattern = $this->getTableLocator()->get('MDeliveryPattern', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MDeliveryPattern);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MDeliveryPatternTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
