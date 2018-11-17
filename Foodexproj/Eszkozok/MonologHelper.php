<?php
/**This file makes it easier to include Monolog.

USAGE:
 * 1: Include this file!
 * 2: $logger = MonologHelper('name');
 * 3: $logger->warning('log');
 * **/

require_once __DIR__ . '/Eszk.php';
require_once __DIR__ . '/../vendor/monolog/monolog/src/Monolog/Logger.php';

class MonologHelper extends Monolog\Logger
{

    public function __construct($name)
    {
        parent::__construct($name);
        $this->pushHandler(new FxDBLogHandler());
    }
}

class FxDBLogHandler extends  Monolog\Handler\AbstractProcessingHandler
{
    private $conn;

    public function __construct()
    {
        parent::__construct();

        $this->conn = \Eszkozok\Eszk::initMySqliObject();
        $a = 3;
        $b = $a + 2;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        try
        {
            $stmt = $this->conn->prepare('INSERT INTO `logs` (`datetime`, `channel`, `message`, `context`, `level`, `level_name`, `extra`) VALUES (?,?,?,?,?,?,?);');

            $refbuff = $record['datetime']->format('Y-m-d H:i:s');
            $refbuff2 = json_encode($record['context']);
            $refbuff3 = json_encode($record['extra']);
            $stmt->bind_param('ssssiss', $refbuff, $record['channel'], $record['message'], $refbuff2, $record['level'], $record['level_name'], $refbuff3);

            if ($stmt->execute())
            {
            }
            else
            {
                throw new \Exception('$stmt->execute() is false.');
            }
        }
        catch(\Exception $e)
        {

        }
    }
}