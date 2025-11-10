<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TAnnounceTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TAnnounceTable Test Case
 */
class TAnnounceTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TAnnounceTable
     */
    protected $TAnnounce;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.TAnnounce',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TAnnounce') ? [] : ['className' => TAnnounceTable::class];
        $this->TAnnounce = $this->getTableLocator()->get('TAnnounce', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TAnnounce);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\TAnnounceTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
