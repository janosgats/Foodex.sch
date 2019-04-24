
<form method="post" action="test2.php">
.. form elements

<div>
    <?php
        require_once '3rdparty/securimage/securimage.php';
        echo Securimage::getCaptchaHtml();
    ?>
    <button type="submit">btn sbmt</button>test.php
</div>
</form>