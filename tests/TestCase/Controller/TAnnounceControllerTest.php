<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\TAnnounceController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\TAnnounceController Test Case
 *
 * @uses \App\Controller\TAnnounceController
 */
class TAnnounceControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.TAnnounce',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\TAnnounceController::index()
     */
    protected function setUp(): void
{
    parent::setUp();

    // 認証済みユーザーのセッションデータをセット（IDと必要な情報に変更してOK）
    $this->session([
        'Auth' => [
            'User' => [
                'user_id' => 'master',
                'username' => 'master',
            ],
        ],
    ]);
}
    public function testIndex(): void
{
    $this->get('/t-announce/index');
    $this->assertResponseOk();
}

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\TAnnounceController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\TAnnounceController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\TAnnounceController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\TAnnounceController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
