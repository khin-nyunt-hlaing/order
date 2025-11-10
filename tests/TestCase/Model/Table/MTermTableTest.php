<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MTermTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MTermTable Test Case
 */
class MTermTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MTermTable
     */
    protected $MTerm;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
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
        $config = $this->getTableLocator()->exists('MTerm') ? [] : ['className' => MTermTable::class];
        $this->MTerm = $this->getTableLocator()->get('MTerm', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MTerm);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MTermTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
