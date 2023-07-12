<header>
    <button id="logout-button" class="logout-button" onclick="window.location.href='../../Logout.php'">ログアウト</button>
    <div class="header-wrapper">
        <h1>
            <a href="../Home/StudentHome.php">図書館システム</a>
        </h1>
        <nav class="pc-nav">
            <!--<p class="name">
            <?php echo $user->getUserName() ?>
        </p>
        <p class="kind">
            <?php echo $user->getUserTypeName() ?>・
            <?php echo $user->getAffiliationName() ?>
        </p>-->
            <li>ログイン者：<?php echo $user->getUserName() ?></li>
            <li>区分：<?php echo $user->getUserTypeName() ?></li>
            <li>学科：<?php echo $user->getAffiliationName() ?></li>
        </nav>
    </div>
</header>

<style>
    body {
        margin: 0 auto;
        max-width: 1920px;
    }

    /*ヘッダー*/
    header {
        padding: 1px;
        background-color: #FADF6A;
        margin: 0;
        height: 120px;
    }

    .header-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    h1 a {
        color: #000000;
        text-decoration: none;
        font-size: 50px;
    }

    li {
        list-style-type: none;
        padding: 0;
    }

    .pc-nav {
        position: relative;
        top: 5px;
    }

    /*ログアウトボタン*/
    .logout-button {
        font-size: 20px;
        width: 150px;
        height: 100px;
        position: relative;
        top: 3px;
        float: right;
        margin: 10px;
        background: #FADF6A;
        color: #000000;
        border-radius: 20px;
    }
</style>