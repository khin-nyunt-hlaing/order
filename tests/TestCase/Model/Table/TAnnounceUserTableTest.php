<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TAnnounceUserTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TAnnounceUserTable Test Case
 */
class TAnnounceUserTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TAnnounceUserTable
     */
    protected $TAnnounceUser;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.TAnnounceUser',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TAnnounceUser') ? [] : ['className' => TAnnounceUserTable::class];
        $this->TAnnounceUser = $this->getTableLocator()->get('TAnnounceUser', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TAnnounceUser);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\TAnnounceUserTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
