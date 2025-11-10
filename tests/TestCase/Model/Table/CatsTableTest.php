<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CatsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CatsTable Test Case
 */
class CatsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CatsTable
     */
    protected $Cats;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Cats',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Cats') ? [] : ['className' => CatsTable::class];
        $this->Cats = $this->getTableLocator()->get('Cats', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Cats);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\CatsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
