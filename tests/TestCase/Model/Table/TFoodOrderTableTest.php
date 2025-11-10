<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TFoodOrderTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TFoodOrderTable Test Case
 */
class TFoodOrderTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TFoodOrderTable
     */
    protected $TFoodOrder;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.TFoodOrder',
        'app.MFoods',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TFoodOrder') ? [] : ['className' => TFoodOrderTable::class];
        $this->TFoodOrder = $this->getTableLocator()->get('TFoodOrder', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TFoodOrder);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\TFoodOrderTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
