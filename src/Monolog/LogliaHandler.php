<?php

namespace Loglia\LaravelClient\Monolog;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class LogliaHandler extends AbstractProcessingHandler
{
    /**
     * @var TransportInterface
     */
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        parent::__construct(Logger::DEBUG, true);

        $this->transport = $transport;
    }

    public function handleBatch(array $records)
    {
        foreach ($records as $index => $record) {
            if (!$this->isHandling($record)) {
                continue;
            }

            $record = $this->processRecord($record);

            // Replace each log with its formatted version before sending.
            $records[$index] = $this->getFormatter()->format($record);
        }

        $this->transport->send($records);
    }

    public function write(array $record)
    {
        // TODO: this method is not needed, perhaps we don't need to extend AbstractHandler?
    }
}
