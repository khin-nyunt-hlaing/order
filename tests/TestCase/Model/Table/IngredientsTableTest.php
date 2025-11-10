<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IngredientsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\IngredientsTable Test Case
 */
class IngredientsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\IngredientsTable
     */
    protected $Ingredients;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Ingredients',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Ingredients') ? [] : ['className' => IngredientsTable::class];
        $this->Ingredients = $this->getTableLocator()->get('Ingredients', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Ingredients);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\IngredientsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
