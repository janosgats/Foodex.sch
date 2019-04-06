<!DOCTYPE html>
<html lang="en">
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-137789203-1"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-137789203-1');
</script>

<head>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-137789203-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-137789203-1');
    </script>

    <meta charset="UTF-8">
    <title>Developer login</title>
</head>
<body>
<form action="devlogin.php" method="POST" style="width: 100%">
    <p>Login as:</p>
    <input type="text" name="int_id" id="int_id" placeholder="internal_id" style="width: 95%">

    <p>Password:</p>
    <input type="password" name="password" id="password" placeholder="********">
    <br>
    <button type="submit">Developer login</button>
</form>

<script>
    var IntIdField = document.getElementById('int_id');
</script>

<table>

    <?php
    require_once '../Eszkozok/Eszk.php';

    try
    {
    $conn = \Eszkozok\Eszk::initMySqliObject();
    if (!$conn)
        throw new \Exception('$conn is false!');

    $stmt = $conn->prepare("SELECT `nev`, `internal_id` FROM `fxaccok` ORDER BY `nev` ASC ;");

    if (!$stmt)
        throw new \Exception('$stmt is false!');

    if ($stmt->execute())
    {
    $result = $stmt->get_result();

    $row;
    do
    {
    ?>
    <tr>
        <?php
        $i = 0;
        while ($row = $result->fetch_assoc())
        {
            ?>
            <td style="cursor: pointer; color: #5bc0de" onclick="IntIdField.value = '<?php echo $row['internal_id'] ?>';"><?php echo $row['nev']; ?></td>
            <?php
            ++$i;

            if ($i >= 3)
                break;
        }
        ?>

    <tr>
        <?php
        }while ($row);

        }
        else
        {
            throw new \Exception('$stmt->execute() is false!');
        }

        }
        catch (\Exception $e)
        {
            ob_clean();
            die('Error: ' . $e->getMessage());
        }

        ?>

</table>

</body>
</html>