<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MfoodTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MfoodTable Test Case
 */
class MfoodTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MfoodTable
     */
    protected $Mfood;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Mfood',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Mfood') ? [] : ['className' => MfoodTable::class];
        $this->Mfood = $this->getTableLocator()->get('Mfood', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Mfood);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MfoodTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
