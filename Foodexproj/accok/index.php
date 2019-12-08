<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/LoginValidator.php';
require_once __DIR__ . '/../Eszkozok/entitas/Kor.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\LoginValidator::AdminJog_DiesToErrorrPage();

ob_start();

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx - Accountok</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">


    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="collapsible.css">
    <link rel="stylesheet" href="toggleSwitch.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
</head>

<body style="background-color: #de520d">

<div class="container">

    <?php
    NavBar::echonavbar('accok');
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">

            <label for="exampleInputEmail1">Keresés:</label>
            <input onkeyup="keresesFgv();" type="text" class="form-control" id="keresesInput" aria-describedby="emailHelp" placeholder="Pl. Végh Béla" autofocus>

            <br>

            <span>Mutasd az archivált tagokat is!</span>
            <label class="switch">
                <input type="checkbox" id="checkboxMutasdAzArchivaltTagokat" onchange="keresesFgv()">
                <span class="slider round"></span>
            </label>
        </div>

        <div class="panel-body">
            <table class="table table-hover" id="accoktable">

            </table>
        </div>
    </div>

    <!-- Modal -->
    <div style="max-width: 100vw;" class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size: larger" id="exampleModalLongTitle">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <button class="collapsible">Kattints ide az értékelési jogok leírásáért!</button>
                    <div class="collapsiblecontent">
                        <p style="color: #999999; text-align: justify;">Az egyes körökhöz írt értékelések később bármikor módosíthatók az értékelő által. Akkor is, ha az értékelőnek aktuálisan nincs joga az adott körhöz új értékelést írni.<br>Amennyiben
                            viszont az értékelőnek aktuálisan egyetlen körhöz sincs értékelési joga, úgy egyeltalán nem módosíthatja a korábbi értékeléseit semmilyen körhöz.</p>
                    </div>
                    <script>
                        var coll = document.getElementsByClassName("collapsible");
                        var i;

                        for (i = 0; i < coll.length; i++)
                        {
                            coll[i].addEventListener("click", function ()
                            {
                                this.classList.toggle("collapsibleactive");
                                var content = this.nextElementSibling;
                                if (content.style.maxHeight)
                                {
                                    content.style.maxHeight = null;
                                } else
                                {
                                    content.style.maxHeight = content.scrollHeight + "px";
                                }
                            });
                        }
                    </script>
                    <form>
                        <?php
                        try
                        {
                            $conn = \Eszkozok\Eszk::initMySqliObject();
                            $stmt = $conn->prepare("SELECT id, nev FROM korok ORDER BY nev;");

                            if (!$stmt->execute())
                                throw new \Exception('$stmt->execute() is false! Próbáld frissíteni az oldalt!');

                            $res = $stmt->get_result();
                            if ($res->num_rows > 0)
                            {
                                while ($row = $res->fetch_assoc())
                                {

                                    ?>
                                    <div class="checkbox" style="font-size: large">
                                        <label><input class="modal_kor_checkbox" type="checkbox" value="<?= $row['id']; ?>"><?= $row['nev']; ?></label>
                                    </div>
                                    <?php
                                }
                            }
                            else
                            {
                                ?>
                                <p>Úgy tűnik, még egy kört sem vettél fel. <a href="<?= \Eszkozok\Eszk::GetRootURL() . 'korok'; ?>">Kattints ide, hogy ezt bepótold!</a></p>

                                <?php
                            }
                        }
                        catch (\Exception $e)
                        {
                            \Eszkozok\Eszk::dieToErrorPage('45634: ' . $e->getMessage(), 'accok');
                        }
                        ?>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Mégsem</button>
                    <button type="button" onclick="PrepareSubmitKorertekelesJogok()" class="btn btn-success">Mentés</button>
                </div>
            </div>
        </div>
    </div>

    <script>

        var AktualisanSzerkesztett_KorertekelesekAccanak_InternalIDje = '';


        function PrepareSubmitKorertekelesJogok()
        {
            try
            {
                let korertjogok = {};
                $(".modal_kor_checkbox").each(function ()
                {
                    korertjogok[$(this).attr('value').toString()] = $(this).is(":checked") ? 1 : 0;
                });

                submitSetKorErtekelesJogok(AktualisanSzerkesztett_KorertekelesekAccanak_InternalIDje, JSON.stringify(korertjogok));
            }
            catch (e)
            {
                alert('Hiba történt a küldés előtt. Próbáld meg újra!');
            }
        }

        function ShowKorErtekelSzerkModal(domclicker)
        {
            var modal = $('#exampleModal');
            var datacontainer = domclicker.parentElement.parentElement;

            AktualisanSzerkesztett_KorertekelesekAccanak_InternalIDje = datacontainer.getAttribute('data-acc_internalid');

            var ertekelkorok = JSON.parse(datacontainer.getAttribute('data-grouped_korertekelesek'));


            $(".modal_kor_checkbox").each(function ()
            {
                $(this).prop('checked', false);

                if (ertekelkorok != null)
                {
                    if ((ertekelkorok).includes($(this).attr('value')))
                        $(this).prop('checked', true);
                }
            });


            modal.find('.modal-title').html('Állítsd be, hogy <b>' + escapeHtml(datacontainer.getAttribute('data-acc_nev')) + '</b> mely körök nevében értékelhet!');
            modal.modal('show');
        }
    </script>


</div>
<script>


    function escapeHtml(unsafe)
    {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


    function HandleAJAXkorertekelesekPHPPageData(ret)
    {
        try
        {
//            console.log(ret);
            var fullres = JSON.parse(ret);

            if (fullres.status == 'siker2345')
            {
                $('#exampleModal').modal('toggle');

                var LegalabbEgyKornelErtekelesiJogaVan = false;
                var korertekelesekArrayFromObject = [];
                $.each(fullres.korertekelesek, function (index, value)
                {
                    if (value == 1)
                    {
                        LegalabbEgyKornelErtekelesiJogaVan = true;

                        korertekelesekArrayFromObject.push(index);
                    }
                });

                $("#td_toggle_jogosultsag" + fullres.internal_id).attr("data-grouped_korertekelesek", JSON.stringify(korertekelesekArrayFromObject));

                if (LegalabbEgyKornelErtekelesiJogaVan)
                {
                    $("#i_open_ertekelesjog_editor" + fullres.internal_id).addClass('acc-jogosultsag-korertekelesek-true').removeClass('acc-jogosultsag-korertekelesek-false');
                }
                else
                {
                    $("#i_open_ertekelesjog_editor" + fullres.internal_id).addClass('acc-jogosultsag-korertekelesek-false').removeClass('acc-jogosultsag-korertekelesek-true');
                }
            }
            else
            {
                if (ret.error != null)
                    throw {message: fullres.error};
            }
        }
        catch (e)
        {
            alert('Hiba történt a értékelési jogok beállítása során. Próbáld meg újra! (' + e.message + ')');
        }
    }


    function callPHPPageSetKorErtekelesJogok(postdata)
    {
        $.post('AJAXkorertekelesek.php', postdata, HandleAJAXkorertekelesekPHPPageData).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }

    function submitSetKorErtekelesJogok(internal_id, ertekelesjogokJSON)
    {
        callPHPPageSetKorErtekelesJogok({
            int_id: internal_id,
            ertekelesjogokJSON: ertekelesjogokJSON
        });
    }


    function getArchivFromNode(node)
    {
        return (node.parentElement.parentElement.getAttribute("archiv") == '1') ? 1 : 0;
    }
    function getBelephetFromNode(node)
    {
        return (node.parentElement.parentElement.getAttribute("belephet") == '1') ? 1 : 0;
    }
    function getAdminJogFromNode(node)
    {
        return (node.parentElement.parentElement.getAttribute("adminjog") == '1') ? 1 : 0;
    }
    function getMuszjelJogFromNode(node)
    {
        return (node.parentElement.parentElement.getAttribute("muszjeljog") == '1') ? 1 : 0;
    }
    function getPontLatJogFromNode(node)
    {
        return (node.parentElement.parentElement.getAttribute("pontlatjog") == '1') ? 1 : 0;
    }

    var accoktable = document.getElementById('accoktable');
    function HandlePHPPageData(ret)
    {
//        console.log(ret);
        var fullres = JSON.parse(ret);

        var thead = jQuery.parseHTML('<thead><tr><th>Név</th><th>Fx tag</th><th>Jogosultságok</th><th>Archív</th></tr></thead>')[0];
        var tbody = jQuery.parseHTML('<tbody></tbody>')[0];

        fullres.forEach(function (row)
        {
            if (row.grouped_korertekelesek != null)
            {
                row.grouped_korertekelesek = row.grouped_korertekelesek.split(',');
            }

//            console.log(row);

            var a_user_nev = jQuery.parseHTML('<a href="../profil/?mprof=' + row['internal_id'] + '"><div style="width: 100%">' + escapeHtml(row['nev']) + '</div></a>')[0];

            var i_fx_tag;

            if (row['fxtag'] == 1)
                i_fx_tag = jQuery.parseHTML('<i class="fas fa-2x fa-check" style="color: limegreen"></i>')[0];
            else
                i_fx_tag = jQuery.parseHTML('<i class="fas fa-2x fa-times" style="color: red"></i>')[0];

            i_fx_tag.setAttribute('data-toggle', 'tooltip');
            i_fx_tag.setAttribute('data-container', 'body');
            i_fx_tag.setAttribute('data-placement', 'right');
            i_fx_tag.setAttribute('title', 'PéK szerinti Foodex tagság');

            var i_toggle_belephet = jQuery.parseHTML('<i id="i_toggle_belephet' + row['internal_id'] + '" data-toggle="tooltip" data-container="body" data-placement="right"  title="Beléphet az oldalra"  class="acc-jogosultsag ' + ((row['belephet'] == 1) ? 'acc-jogosultsag-belephet-true' : 'acc-jogosultsag-belephet-false') + ' fas fa-2x fa-power-off"></i>')[0];
            i_toggle_belephet.onclick = function ()
            {
                var is_adminjog = getAdminJogFromNode(this);
                var is_muszjeljog = getMuszjelJogFromNode(this);
                var is_pontlatjog = getPontLatJogFromNode(this);
                var is_belephet = getBelephetFromNode(this);

                var r;
                if (is_belephet)
                {
                    r = confirm("Biztosan elveszed " + row['nev'] + " belépési jogát?");
                    if (r == true)
                        submitSetJogosultsagok(row['internal_id'], !is_belephet, is_adminjog, is_muszjeljog, is_pontlatjog)
                }
                else
                {
                    r = confirm("Biztosan engedélyezed neki az oldalra való belépést: " + row['nev'] + "?");
                    if (r == true)
                        submitSetJogosultsagok(row['internal_id'], !is_belephet, is_adminjog, is_muszjeljog, is_pontlatjog)
                }
            };

            var i_toggle_adminjog = jQuery.parseHTML('<i id="i_toggle_adminjog' + row['internal_id'] + '" data-toggle="tooltip" data-container="body" data-placement="right"  title="Admin jog"  class="acc-jogosultsag ' + ((row['adminjog'] == 1) ? 'acc-jogosultsag-admin-true' : 'acc-jogosultsag-admin-false') + ' fas fa-2x fa-user-astronaut"></i>')[0];
            i_toggle_adminjog.onclick = function ()
            {
                var is_adminjog = getAdminJogFromNode(this);
                var is_muszjeljog = getMuszjelJogFromNode(this);
                var is_pontlatjog = getPontLatJogFromNode(this);
                var is_belephet = getBelephetFromNode(this);

                var r;
                if (is_adminjog)
                {
                    r = confirm("Biztosan elveszed " + row['nev'] + " admin jogát?");
                    if (r == true)
                        submitSetJogosultsagok(row['internal_id'], is_belephet, !is_adminjog, is_muszjeljog, is_pontlatjog)
                }
                else
                {
                    r = confirm("Biztosan adminná teszed őt: " + row['nev'] + "?");
                    if (r == true)
                        submitSetJogosultsagok(row['internal_id'], is_belephet, !is_adminjog, is_muszjeljog, is_pontlatjog)
                }
            };


            var i_toggle_muszjeljog = jQuery.parseHTML('<i id="i_toggle_muszjeljog' + row['internal_id'] + '" data-toggle="tooltip" data-container="body" data-placement="right"  title="Műszakfelvételi jog"  class="acc-jogosultsag ' + ((row['muszjeljog'] == 1) ? 'acc-jogosultsag-muszjel-true' : 'acc-jogosultsag-muszjel-false') + ' fas fa-2x fa-handshake"></i>')[0];
            i_toggle_muszjeljog.onclick = function ()
            {
                var is_adminjog = getAdminJogFromNode(this);
                var is_muszjeljog = getMuszjelJogFromNode(this);
                var is_pontlatjog = getPontLatJogFromNode(this);
                var is_belephet = getBelephetFromNode(this);

                var r;
                if (is_muszjeljog)
                {
                    r = confirm("Biztosan elveszed " + row['nev'] + " műszakfelvételi jogát?");
                    if (r == true)
                        submitSetJogosultsagok(row['internal_id'], is_belephet, is_adminjog, !is_muszjeljog, is_pontlatjog)
                }
                else
                {
                    r = confirm("Biztosan engedélyezed a műszakfelvételt neki: " + row['nev'] + "?");
                    if (r == true)
                        submitSetJogosultsagok(row['internal_id'], is_belephet, is_adminjog, !is_muszjeljog, is_pontlatjog)
                }
            };
            var i_toggle_pontlatjog = jQuery.parseHTML('<i id="i_toggle_pontlatjog' + row['internal_id'] + '" data-toggle="tooltip" data-container="body" data-placement="right"  title="Láthatja mások pontszámát"  class="acc-jogosultsag ' + ((row['pontlatjog'] == 1) ? 'acc-jogosultsag-pontlat-true' : 'acc-jogosultsag-pontlat-false') + ' fas fa-2x fa-low-vision"></i>')[0];
            i_toggle_pontlatjog.onclick = function ()
            {
                var is_adminjog = getAdminJogFromNode(this);
                var is_muszjeljog = getMuszjelJogFromNode(this);
                var is_pontlatjog = getPontLatJogFromNode(this);
                var is_belephet = getBelephetFromNode(this);

                var r;
                if (is_pontlatjog)
                {
                    r = confirm("Biztosan elveszed " + row['nev'] + " jogát mások pontszámának megtekintésére?");
                    if (r == true)
                        submitSetJogosultsagok(row['internal_id'], is_belephet, is_adminjog, is_muszjeljog, !is_pontlatjog)
                }
                else
                {
                    r = confirm("Biztosan engedélyezed mások pontszámának megtekintését neki: " + row['nev'] + "?");
                    if (r == true)
                        submitSetJogosultsagok(row['internal_id'], is_belephet, is_adminjog, is_muszjeljog, !is_pontlatjog)
                }
            };

            var i_toggle_archiv = jQuery.parseHTML('<i id="i_toggle_archiv' + row['internal_id'] + '" data-toggle="tooltip" data-container="body" data-placement="right"  title="Archiválva van-e? (Azaz nem léphet be és el van rejtve a listában.)"  class="acc-jogosultsag ' + ((row['archiv'] == 1) ? 'acc-archiv-true' : 'acc-archiv-false') + ' far fa-2x fa-trash-alt"></i>')[0];
            i_toggle_archiv.onclick = function ()
            {
                var is_archiv = getArchivFromNode(this);

                var r;
                if (is_archiv)
                {
                    r = confirm("Biztosan visszavonod az archiválását: " + row['nev'] + "?");
                    if (r == true)
                        submitArchivalas(row['internal_id'], 'visszavon')
                }
                else
                {
                    r = confirm("Biztosan archiválod őt: " + row['nev'] + "? Ezzel az oldalra való belépési jogosultságát is elveszed.");
                    if (r == true)
                        submitArchivalas(row['internal_id'], 'archival')
                }
            };
            var i_open_ertekelesjog_editor = jQuery.parseHTML('<i id="i_open_ertekelesjog_editor' + row['internal_id'] + '" onclick="ShowKorErtekelSzerkModal(this);" data-toggle="tooltip" data-container="body" data-placement="right"  title="Értékelési jogok szerkesztése"  class="acc-jogosultsag ' + ((row['grouped_korertekelesek'] != null) ? 'acc-jogosultsag-korertekelesek-true' : 'acc-jogosultsag-korertekelesek-false') + ' fas fa-2x fa-star-half-alt"></i>')[0];

            var td_user_nev = jQuery.parseHTML('<td></td>')[0];
            var td_fx_tag = jQuery.parseHTML('<td></td>')[0];
            var td_toggle_jogosultsag = jQuery.parseHTML('<td></td>')[0];
            var td_toggle_archiv = jQuery.parseHTML('<td></td>')[0];

            td_user_nev.appendChild(a_user_nev);

            td_fx_tag.appendChild(i_fx_tag);

            td_toggle_archiv.id = "td_toggle_archiv" + row['internal_id'];
            td_toggle_archiv.setAttribute("archiv", row['archiv'].toString());

            td_toggle_jogosultsag.id = "td_toggle_jogosultsag" + row['internal_id'];

            td_toggle_jogosultsag.setAttribute("belephet", row['belephet'].toString());
            td_toggle_jogosultsag.setAttribute("adminjog", row['adminjog'].toString());
            td_toggle_jogosultsag.setAttribute("muszjeljog", row['muszjeljog'].toString());
            td_toggle_jogosultsag.setAttribute("pontlatjog", row['pontlatjog'].toString());
            td_toggle_jogosultsag.setAttribute("data-grouped_korertekelesek", JSON.stringify(row.grouped_korertekelesek));
            td_toggle_jogosultsag.setAttribute("data-acc_nev", escapeHtml(row['nev'].toString()));
            td_toggle_jogosultsag.setAttribute("data-acc_internalid", escapeHtml(row['internal_id'].toString()));

            var div0 = jQuery.parseHTML('<div></div>')[0];
            var div1 = jQuery.parseHTML('<div></div>')[0];
            var div2 = jQuery.parseHTML('<div></div>')[0];
            div0.style.display = 'inline-block';
            div1.style.display = 'inline-block';
            div2.style.display = 'inline-block';
            div0.style.marginBottom = '10px';
            div1.style.marginBottom = '10px';

            div0.appendChild(i_toggle_belephet);
            div0.appendChild(jQuery.parseHTML(' &nbsp; ')[0]);

            div1.appendChild(i_toggle_adminjog);
            div1.appendChild(jQuery.parseHTML(' &nbsp; ')[0]);
            div1.appendChild(i_toggle_muszjeljog);
            div1.appendChild(jQuery.parseHTML(' &nbsp; ')[0]);

            div2.appendChild(i_toggle_pontlatjog);
            div2.appendChild(jQuery.parseHTML(' &nbsp; ')[0]);
            div2.appendChild(i_open_ertekelesjog_editor);
            div2.appendChild(jQuery.parseHTML(' &nbsp; ')[0]);

            td_toggle_jogosultsag.appendChild(div0);
            td_toggle_jogosultsag.appendChild(div1);
            td_toggle_jogosultsag.appendChild(div2);

            var divX = jQuery.parseHTML('<div></div>')[0];

            divX.appendChild(i_toggle_archiv);
            td_toggle_archiv.appendChild(divX);

            var tr_account = jQuery.parseHTML('<tr></tr>')[0];
            tr_account.id = "tr_account" + row['internal_id'];
            tr_account.appendChild(td_user_nev);
            tr_account.appendChild(td_fx_tag);
            tr_account.appendChild(td_toggle_jogosultsag);
            tr_account.appendChild(td_toggle_archiv);

            tbody.appendChild(tr_account);
        });
        accoktable.innerHTML = '';

        accoktable.appendChild(thead);
        accoktable.appendChild(tbody);

        $('[data-toggle="tooltip"]').tooltip();
    }

    function callPHPPage(postdata)
    {
        $.post('tabllekero.php', postdata, HandlePHPPageData).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }
    function keresesFgv()
    {
        let keresesText = $('#keresesInput').val();
        let showArchived = 0;

        if($("#checkboxMutasdAzArchivaltTagokat").is(':checked'))
            showArchived = 1;
        console.log(keresesText);
        console.log(showArchived);
        callPHPPage({
            keresett: keresesText,
            show_archived: showArchived
        });
    }

    function HandleAJAXjogosultsagokPHPPageData(ret)
    {

        try
        {
//            console.log(ret);
            var fullres = JSON.parse(ret);

            if (fullres.status != 'siker3456')
                alert('Hiba: ' + fullres.error);
            else
            {
                if (fullres.belephet == 1)
                {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("belephet", '1');
                    $("#i_toggle_belephet" + fullres.internal_id).addClass('acc-jogosultsag-belephet-true').removeClass('acc-jogosultsag-belephet-false');
                }
                else
                {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("belephet", '0');
                    $("#i_toggle_belephet" + fullres.internal_id).addClass('acc-jogosultsag-belephet-false').removeClass('acc-jogosultsag-belephet-true');
                }
                if (fullres.adminjog == 1)
                {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("adminjog", '1');
                    $("#i_toggle_adminjog" + fullres.internal_id).addClass('acc-jogosultsag-admin-true').removeClass('acc-jogosultsag-admin-false');
                }
                else
                {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("adminjog", '0');
                    $("#i_toggle_adminjog" + fullres.internal_id).addClass('acc-jogosultsag-admin-false').removeClass('acc-jogosultsag-admin-true');
                }
                if (fullres.muszjeljog == 1)
                {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("muszjeljog", '1');
                    $("#i_toggle_muszjeljog" + fullres.internal_id).addClass('acc-jogosultsag-muszjel-true').removeClass('acc-jogosultsag-muszjel-false');
                }
                else
                {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("muszjeljog", '0');
                    $("#i_toggle_muszjeljog" + fullres.internal_id).addClass('acc-jogosultsag-muszjel-false').removeClass('acc-jogosultsag-muszjel-true');
                }
                if (fullres.pontlatjog == 1)
                {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("pontlatjog", '1');
                    $("#i_toggle_pontlatjog" + fullres.internal_id).addClass('acc-jogosultsag-pontlat-true').removeClass('acc-jogosultsag-pontlat-false');
                }
                else
                {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("pontlatjog", '0');
                    $("#i_toggle_pontlatjog" + fullres.internal_id).addClass('acc-jogosultsag-pontlat-false').removeClass('acc-jogosultsag-pontlat-true');
                }
            }
        }
        catch (e)
        {
            alert('Hiba: ' + e.message)
        }
    }

    function HandleAJAXarchivalasPHPPageData(ret)
    {
        try
        {
            console.log(ret);
            var fullres = JSON.parse(ret);

            if (fullres.status != 'siker2345')
                alert('Hiba: ' + fullres.error);
            else
            {
                    $("#td_toggle_jogosultsag" + fullres.internal_id).attr("belephet", '0');
                    $("#i_toggle_belephet" + fullres.internal_id).addClass('acc-jogosultsag-belephet-false').removeClass('acc-jogosultsag-belephet-true');

                    if (fullres.action == 'archival'){
                        $("#td_toggle_archiv" + fullres.internal_id).attr("archiv", '1');
                        $("#i_toggle_archiv" + fullres.internal_id).addClass('acc-archiv-true').removeClass('acc-archiv-false');

                        if(!$("#checkboxMutasdAzArchivaltTagokat").is(':checked')){
                            $("#tr_account" + fullres.internal_id).css("display", 'none');
                        }
                    }
                    else if (fullres.action == 'visszavon'){
                        $("#td_toggle_archiv" + fullres.internal_id).attr("archiv", '0');
                        $("#i_toggle_archiv" + fullres.internal_id).addClass('acc-archiv-false').removeClass('acc-archiv-true');
                    }
            }
        }
        catch (e)
        {
            alert('Hiba: ' + e.message)
        }
    }
    function callPHPPageSetJogosultsagok(postdata)
    {
        $.post('AJAXjogosultsagok.php', postdata, HandleAJAXjogosultsagokPHPPageData).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }
    function submitSetJogosultsagok(internal_id, belephet, adminjog, muszjeljog, pontlatjog)
    {
        callPHPPageSetJogosultsagok({
            int_id: internal_id,
            belephet: belephet ? 1 : 0,
            adminjog: adminjog ? 1 : 0,
            muszjeljog: muszjeljog ? 1 : 0,
            pontlatjog: pontlatjog ? 1 : 0
        });
    }

    function callPHPPageArchivalas(postdata)
    {console.log(postdata);
        $.post('AJAXarchivalas.php', postdata, HandleAJAXarchivalasPHPPageData).fail(
            function ()
            {
                alert("Error at AJAX call!");
            });
    }
    function submitArchivalas(internal_id, action)
    {
        callPHPPageArchivalas({
            int_id: internal_id,
            action: action
        });
    }

    function uresKereses()
    {
        callPHPPage({});
    }
    window.onload = function ()
    {
        uresKereses();
    };
</script>


</body>
</html>


