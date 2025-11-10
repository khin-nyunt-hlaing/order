<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MfoodsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MfoodsTable Test Case
 */
class MfoodsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MfoodsTable
     */
    protected $Mfoods;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Mfoods',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Mfoods') ? [] : ['className' => MfoodsTable::class];
        $this->Mfoods = $this->getTableLocator()->get('Mfoods', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Mfoods);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MfoodsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
