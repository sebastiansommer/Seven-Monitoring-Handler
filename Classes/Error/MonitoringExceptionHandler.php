<?php
namespace Seven\Monitoring\Handler\Error;

use Throwable, Exception;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Error\ProductionExceptionHandler;
use Neos\Flow\ObjectManagement\DependencyInjection\DependencyProxy;
use Seven\Monitoring\Handler\MonitoringService;

class MonitoringExceptionHandler extends ProductionExceptionHandler
{
    /**
     * @var MonitoringService
     * @Flow\Inject
     */
    protected $monitoringService;

    /**
     * @param Throwable $exception
     * @throws Exception
     */
    public function echoExceptionCli(Throwable $exception)
    {
        $this->getMonitoringService()->logException($exception);
        parent::echoExceptionCli($exception);
    }

    /**
     * @param Throwable $exception
     * @throws Exception
     */
    public function echoExceptionWeb($exception)
    {
        $this->getMonitoringService()->logException($exception);
        parent::echoExceptionWeb($exception);
    }

    /**
     * @return MonitoringService
     */
    private function getMonitoringService() {
        if ($this->monitoringService instanceof MonitoringService) {
            return $this->monitoringService;
        } elseif ($this->monitoringService instanceof DependencyProxy) {
            return $this->monitoringService->_activateDependency();
        } else {
            return new MonitoringService();
        }
    }
}
