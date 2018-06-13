<?php
session_start();

include_once '../Eszkozok/Eszk.php';
include_once "../profil/Profil.php";

if (!isset($_SESSION['profilint_id']))
    Eszkozok\Eszk::RedirectUnderRoot('');

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx - Jelentkezés Műszakra</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">


    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="main.css">

    <link rel="stylesheet" href="modal.css">


</head>

<body>

<div id="osszhastablazat" class="tablaDiv" style="margin-top: 1.5%;">

    <table class="tabla">

        <colgroup>
            <col span="1" style="width: 10%;">
            <col span="1" style="width: 2%;">
            <col span="1" style="width: 8%;">
            <col span="1" style="width: 4%;">
            <col span="1" style="width: 56%;">
        </colgroup>

        <?php
        try
        {
            $conn = Eszkozok\Eszk::initMySqliObject();


            if (!$conn)
                throw new \Exception('SQL hiba: $conn is \'false\'');

            ///`fxmuszakok` (`kiirta`, `musznev`, `idokezd`, `idoveg`, `letszam`, `pont`)
            $stmt = $conn->prepare("SELECT * FROM `fxmuszakok` WHERE `idokezd` >= CURDATE() ORDER BY `idokezd` DESC;");
            if (!$stmt)
                throw new \Exception('SQL hiba: $stmt is \'false\'');


            if ($stmt->execute())
            {
                $result = $stmt->get_result();

                if ($result->num_rows > 0)
                {
                    while ($row = $result->fetch_assoc())
                    {
                        //var_dump($row);
                        $kiiroProfil = Eszkozok\Eszk::GetTaroltProfilAdat($row['kiirta']);

                        $idokezd = DateTime::createFromFormat("Y-m-d H:i:s", $row['idokezd']);
                        $idostringbuff = $idokezd->format('H:i');

                        $idostringbuff .= ' - ';

                        $idoveg = DateTime::createFromFormat("Y-m-d H:i:s", $row['idoveg']);
                        $idostringbuff .= $idoveg->format('H:i');

                        $jelentkezesaktiv = 1;

                        if(date("Y-m-d H:i:s") > $idokezd->format('Y-m-d H:i:s'))
                            $jelentkezesaktiv = 0;

                        ?>

                        <!--                        ShowModal(id,kiirta, musznev, idokezd, idoveg, letszam, pont, jelaktiv)-->

                        <tr class="tablaSor">
                            <td class="tablaCella oszlopNev">
                                <p><?php echo htmlspecialchars($row['musznev']); ?></p>
                            </td>
                            <td class="tablaCella oszlopReszletek">
                                <p onclick="ShowModal('<?php echo $row['ID'];?>','<?php echo $kiiroProfil->getNev();?>', '<?php echo $row['musznev'];?>', '<?php echo $idokezd->format('Y-m-d >>> H:i');?>', '<?php echo $idoveg->format('Y-m-d >>> H:i');?>', '<?php echo $row['letszam'];?>', '<?php echo $row['pont'];?>', '<?php echo $jelentkezesaktiv;?>');"><i
                                        class="fa fa-plus-square-o fa-2x"></i></p>
                            </td>
                            <td class="tablaCella oszlopIdo">
                                <p><?php echo htmlspecialchars($idostringbuff); ?></p>
                            </td>
                            <td class="tablaCella oszlopLetszam">
                                <p><?php echo htmlspecialchars($row['letszam']); ?> fő</p>
                            </td>
                            <td class="tablaCella oszlopVarolista">

                            </td>
                        </tr>
                        <?php

                    }


                }

            }
            else
            {
                throw new \Exception('Az SQL parancs végrehajtása nem sikerült.');
            }

        }
        catch (\Exception $e)
        {
            ob_clean();
            Eszkozok\Eszk::dieToErrorPage('3014: ' . $e->getMessage());
        }
        ?>

        <tr class="tablaSor">
            <td class="tablaCella oszlopNev">
                <p>Teszt Sor 1 bla bla bla bla bla</p>
            </td>
            <td class="tablaCella oszlopReszletek">
                <p onclick="ShowModal();"><i class="fa fa-plus-square-o fa-2x"></i></p>
            </td>
            <td class="tablaCella oszlopIdo">
                <p>19:30 - 21:00</p>
            </td>
            <td class="tablaCella oszlopLetszam">
                <p>2 fő</p>
            </td>
            <td class="tablaCella oszlopVarolista">
                <p class="varolistaElso">Végh Béla</p><p>,</p>

                <p class="varolistaElso">Recská Zoltán</p><p>,</p>

                <p>Tök Ödön</p><p>,</p>

                <p>Winch Eszter</p><p>,</p>

                <p>Senior Ególya</p>
            </td>
        </tr>

    </table>
</div>

<!-- The Modal -->
<div id="myModal" class="modal">

    <!-- Modal content -->
    <div class="modal-content">
        <div class="modal-header">
            <span class="close">&times;</span>

            <h2 id="modalheadertext">Jelentkezés</h2>
        </div>
        <div class="modal-body" id=modalbody">
            <p id="modalkiirta">Kiírta: </p>

            <p id="modalidokezd">Kezdet: </p>

            <p id="modalidoveg">Vég: </p>

            <p id="modalletszam">Maximális létszám: </p>

            <p id="modalpont">Közösségi pont: </p>
        </div>
        <div class="modal-footer">
            <div id="jelentkezgombdiv" style="text-align: center;">

                <form action="">
                    <input name="muszid" id="modalmuszakid" style="display: none">
                    <button type="submit" class="popupbutton">Viszem!</button>
                </form>
            </div>
            <br>
        </div>
    </div>
</div>

<script>
    // Get the modal
    var modal = document.getElementById('myModal');

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal

    function ShowModal(id, kiirta, musznev, idokezd, idoveg, letszam, pont, jelaktiv)
    {
        document.getElementById('modalheadertext').innerHTML = musznev + ' Jelentkezés';
        document.getElementById('modalkiirta').innerHTML = 'Kiírta: ' + kiirta;
        document.getElementById('modalidokezd').innerHTML = 'Kezdet: ' + idokezd;
        document.getElementById('modalidoveg').innerHTML = 'Vég: ' + idoveg;
        document.getElementById('modalletszam').innerHTML = 'Maximális létszám: ' + letszam;
        document.getElementById('modalpont').innerHTML = 'Közösségi pont: ' + pont;
        document.getElementById('modalmuszakid').value = id;

        if(jelaktiv == 0)
        {
            document.getElementById('jelentkezgombdiv').style.display = 'none';
        }
        else
        {
            document.getElementById('jelentkezgombdiv').style.display = 'block';
        }
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function ()
    {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event)
    {
        if (event.target == modal)
        {
            modal.style.display = "none";
        }
    }
</script>

</body>

</html>
