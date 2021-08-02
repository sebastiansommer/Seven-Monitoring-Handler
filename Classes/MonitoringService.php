<?php
namespace Seven\Monitoring\Handler;

use Exception, Throwable;
use Neos\Flow\Exception as FlowException;
use Neos\Flow\Annotations as Flow;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class MonitoringService
{
    /**
     * @var array
     * @Flow\InjectConfiguration
     */
    protected $settings;

    /**
     * @var LoggerInterface
     * @Flow\Inject
     */
    protected $logger;

    /**
     * @param Exception||Throwable $exception
     */
    public function logException($exception)
    {
        $statusCode = null;

        if ($exception instanceof FlowException) {
            $statusCode = $exception->getStatusCode();
        }

        if (isset($this->settings['skipStatusCodes']) && in_array($statusCode, $this->settings['skipStatusCodes'])) {
            return;
        }

        $messageContext = [
            'exception' => $exception->getMessage(),
            'referenceCode' => $exception instanceof FlowException ? $exception->getReferenceCode() : null,
            'responseStatus' => $statusCode,
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'referer' => $this->settings['project']
        ];

        $this->logMessageToServer($messageContext);
    }

    /**
     * @param array $message
     */
    protected function logMessageToServer(array $message)
    {
        try {
            $client = new Client();

            $client->post($this->settings['apiUrl'] . '/api/create', [
                RequestOptions::JSON => ['log' => $message],
                RequestOptions::AUTH => [$this->settings['apiKey'], $this->settings['apiSecret']]
            ]);
        } catch (Exception $exception) {
            $this->logger->log(LogLevel::CRITICAL, 'Cant push exceptions to: ' . $this->settings['apiUrl']);
        }
    }
}
