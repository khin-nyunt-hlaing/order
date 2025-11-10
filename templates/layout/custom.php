<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->fetch('title') ?></title>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <style>
        html, body {
            margin: 0;
            height: 100%;
        }
        .header-box {
            height: 10%;
            width: 95%;
            background-Color:#ffffff;
            box-sizing: border-box;
            padding: 10px;
            margin: 0 auto; /* ← 中央寄せにする */
        }
        .content-box {
            height: 80%;
            width: 100%;
            overflow-y: auto;
            box-sizing: border-box;
            padding: 10px;
        }

    </style>
</head>
<body>
    <div class="header-box">
        <?= $this->fetch('header') ?>
    </div>
    <div class="content-box">
        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>
    </div>
</body>
</html>