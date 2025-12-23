<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = '発注管理システム おたっしゃごぜん';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken') ?>">
    <title>
        <?= $cakeDescription ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake','common-ui']) ?>
    <?= $this->Html->script(['timekeeper', 'ui_actions','ui_modal']) ?> <!-- ← これを追加 -->

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <?= $this->fetch('script') ?>
</head>
<body class="<?= h($this->getRequest()->getParam('controller')) ?>-<?= h($this->getRequest()->getParam('action')) ?>">

    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>おたっしゃ</span>ごぜん</a>
        </div>
            <?php
            $controller = $this->getRequest()->getParam('controller');
            $action = $this->getRequest()->getParam('action');
            ?>

            <?php if ($controller === 'Mmenus' && $action === 'index'): ?>
                <div class="top-nav-links">
                    <?= $this->Html->link('パスワード再設定', ['controller' => 'MUser', 'action' => 'reset'], ['class' => 'top-button']) ?>
                    <?= $this->Html->link('ログアウト', ['controller' => 'MUser', 'action' => 'logout'], ['class' => 'top-button']) ?>
                </div>
            <?php endif; ?>
            </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
