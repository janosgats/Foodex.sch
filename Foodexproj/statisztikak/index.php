<?php
session_start();

require_once __DIR__ . '/../Eszkozok/Eszk.php';
require_once __DIR__ . '/../Eszkozok/param.php';
require_once __DIR__ . '/../profil/Profil.php';
require_once __DIR__ . '/../Eszkozok/navbar.php';

\Eszkozok\Eszk::ValidateLogin();

$AktProfil = Eszkozok\Eszk::GetBejelentkezettProfilAdat();

if ($AktProfil->getUjMuszakJog() != 1)
    Eszkozok\Eszk::RedirectUnderRoot('');

$InternalIDk = array();
$ChartData = array();

//data:
// {
//    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
//	  datasets: [{
//        label: 'My First dataset',
//		  backgroundColor: window.chartColors.red,
//		  borderColor: window.chartColors.red,
//		  fill: false,
//        cubicInterpolationMode: 'monotone',
//		  data: [
//            randomScalingFactor(),
//            randomScalingFactor(),
//            randomScalingFactor(),
//            randomScalingFactor(),
//            randomScalingFactor(),
//            randomScalingFactor(),
//            randomScalingFactor()
//              ]
//		}]
//	}

try
{

    $conn = \Eszkozok\Eszk::initMySqliObject();

    $stmt = $conn->prepare("SELECT jelentkezo FROM fxjelentk
JOIN logs
ON logs.context = CONCAT('[',  fxjelentk.muszid, ']' )
GROUP BY fxjelentk.jelentkezo");

    if ($stmt->execute())
    {
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc())
        {
            //var_dump($row);
            $InternalIDk[] = $row['jelentkezo'];
        }
    }
    else
        throw new Exception('$stmt->execute() 01 is false!');

//    var_dump($InternalIDk);

    $datasetsDICT = array();

    foreach ($InternalIDk as $item)
    {
        $dataset = array();
        $dataset['label'] = $item;
        $dataset['backgroundColor'] = 'rgb(255, 99, 132)';
        $dataset['borderColor'] = 'rgb(255, 99, 132)';
        $dataset['fill'] = false;
        $dataset['cubicInterpolationMode'] = 'monotone';
        $dataset['data'] = array();

        $datasetsDICT[$item] = $dataset;
    }


//    var_dump($CDdatasets);


    $CDlabels = array();


    $stmt = $conn->prepare("SELECT jelentkezo, muszid,  TIMEDIFF(MinJelIdo, `datetime`) AS JelIdotartam, TIMESTAMPDIFF(SECOND, `datetime`, MinJelIdo) AS JelIdotartamSec FROM
  (
  SELECT jelentkezo, muszid, min(jelido) AS MinJelIdo FROM fxjelentk
  GROUP BY muszid, jelentkezo
  ) AS MinJel
JOIN logs
ON logs.context = CONCAT('[',  MinJel.muszid, ']' )
ORDER BY muszid ASC;");

    if ($stmt->execute())
    {
        $result = $stmt->get_result();

        $elozomuszid = null;
        $muszidLength = 0;
        while ($row = $result->fetch_assoc())
        {
            //var_dump($row);

            $aktmuszid = $row['muszid'];

            if ($elozomuszid == null)
            {
                $elozomuszid = $aktmuszid;
                $CDlabels[] = $aktmuszid;
                $muszidLength = 1;
            }

            if ($elozomuszid != $aktmuszid)
            {
                $CDlabels[] = $aktmuszid;
                //Üresek feltöltése
                foreach ($datasetsDICT as $itemkey => $itemvalue)
                {
                    if (count($itemvalue['data']) < $muszidLength)
                        $datasetsDICT[$itemkey]['data'][] = null;
                }
                ++$muszidLength;
            }


            $datasetsDICT[$row['jelentkezo']]['data'][] = $row['JelIdotartamSec'];


            $elozomuszid = $aktmuszid;
        }
    }
    else
        throw new Exception('$stmt->execute() 02 is false!');


    $CDdatasets = array();

    foreach ($datasetsDICT as $item)
    {
        $CDdatasets[] = $item;
    }

    $ChartData['labels'] = $CDlabels;
    $ChartData['datasets'] = $CDdatasets;

}
catch (Exception $e)
{
    \Eszkozok\Eszk::dieToErrorPage('56434: ' . $e->getMessage());
}


////TODO: megcsinálni a két SQL lekérdezést és belőlük még PHP-ban elkészíteni a data-t a graph számára.
/////======A charton megjelenítendő adat lekérdezése===========================
//SELECT jelentkezo, muszid,  TIMEDIFF(MinJelIdo, `datetime`) as JelIdotartam, TIMESTAMPDIFF(second, `datetime`, MinJelIdo) as JelIdotartamSec FROM
//(
//  select jelentkezo, muszid, min(jelido) as MinJelIdo from fxjelentk
//  group by muszid, jelentkezo
//  ) as MinJel
//JOIN logs
//ON logs.context = CONCAT('[',  MinJel.muszid, ']' )
//ORDER BY muszid ASC;
/////============================================================
//
/////=====A logolt időtartamokkal jelentkező internal_id-k lekérdezése=================
//SELECT jelentkezo FROM fxjelentk
//JOIN logs
//ON logs.context = CONCAT('[',  fxjelentk.muszid, ']' )
//group by fxjelentk.jelentkezo
/////============================================================


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Fx Profil</title>

    <link rel="icon" href="../res/kepek/favicon1_64p.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">


    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js'></script>

    <script src="../node_modules/chart.js/dist/Chart.min.js"></script>
    <script src="../js/chart.js/utils.js"></script>

    <style>
        canvas {
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
        }
    </style>

</head>

<body style="background-color: #de520d">

<div class="container">

    <?php
    NavBar::echonavbar($AktProfil, 'statisztikak');
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h4>Jelentkezési idők</h4>
        </div>
        <div class="panel-body">


            <div>
                <canvas id="canvas"></canvas>
            </div>

        </div>
    </div>
</div>
<br>
<br>
<button id="randomizeData">Randomize Data</button>
<button id="addDataset">Add Dataset</button>
<button id="removeDataset">Remove Dataset</button>
<button id="addData">Add Data</button>
<button id="removeData">Remove Data</button>
<script>
    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var config = {
        type: 'line',
        data: {},
        options: {
            spanGaps: true,
            responsive: true,
            tooltips: {
                mode: 'index',
                intersect: false,
            },
            hover: {
                mode: 'nearest',
                intersect: true
            },
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Muszak'
                    }
                }],
                yAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Jelentkezesi ido kiiras utan [sec]'
                    }
                }]
            }
        }
    };

    config['data'] = JSON.parse('<?php echo json_encode($ChartData); ?>');


    window.onload = function ()
    {
        var ctx = document.getElementById('canvas').getContext('2d');
        window.myLine = new Chart(ctx, config);
    };

    document.getElementById('randomizeData').addEventListener('click', function ()
    {
        config.data.datasets.forEach(function (dataset)
        {
            dataset.data = dataset.data.map(function ()
            {
                return randomScalingFactor();
            });

        });

        window.myLine.update();
    });

    var colorNames = Object.keys(window.chartColors);
    document.getElementById('addDataset').addEventListener('click', function ()
    {


        var colorName = colorNames[config.data.datasets.length % colorNames.length];
        var newColor = window.chartColors[colorName];
        var newDataset = {
            type: 'line',
            label: 'Dataset ' + config.data.datasets.length,
            backgroundColor: newColor,
            borderColor: newColor,
            data: [],
            fill: false,
            cubicInterpolationMode: 'monotone'
        };

        for (var index = 0; index < config.data.labels.length; ++index)
        {
            newDataset.data.push(randomScalingFactor());
        }

        config.data.datasets.push(newDataset);
        window.myLine.update();
    });

    document.getElementById('addData').addEventListener('click', function ()
    {
        if (config.data.datasets.length > 0)
        {
            var month = MONTHS[config.data.labels.length % MONTHS.length];
            config.data.labels.push(month);

            config.data.datasets.forEach(function (dataset)
            {
                dataset.data.push();
            });

            window.myLine.update();
        }
    });

    document.getElementById('removeDataset').addEventListener('click', function ()
    {
        config.data.datasets.splice(0, 1);
        window.myLine.update();
    });

    document.getElementById('removeData').addEventListener('click', function ()
    {
        config.data.labels.splice(-1, 1); // remove the label first

        config.data.datasets.forEach(function (dataset)
        {
            dataset.data.pop();
        });

        window.myLine.update();
    });
</script>
</body>
</html>