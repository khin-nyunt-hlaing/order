<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MAnnounceUserTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MAnnounceUserTable Test Case
 */
class MAnnounceUserTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MAnnounceUserTable
     */
    protected $MAnnounceUser;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.MAnnounceUser',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MAnnounceUser') ? [] : ['className' => MAnnounceUserTable::class];
        $this->MAnnounceUser = $this->getTableLocator()->get('MAnnounceUser', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MAnnounceUser);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MAnnounceUserTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
