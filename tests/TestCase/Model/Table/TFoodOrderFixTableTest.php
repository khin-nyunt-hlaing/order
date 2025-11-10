<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TFoodOrderFixTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TFoodOrderFixTable Test Case
 */
class TFoodOrderFixTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TFoodOrderFixTable
     */
    protected $TFoodOrderFix;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.TFoodOrderFix',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TFoodOrderFix') ? [] : ['className' => TFoodOrderFixTable::class];
        $this->TFoodOrderFix = $this->getTableLocator()->get('TFoodOrderFix', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TFoodOrderFix);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\TFoodOrderFixTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
