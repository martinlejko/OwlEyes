<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PingMonitor extends Monitor
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $host;

    #[ORM\Column(type: 'integer')]
    private int $port;

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function check(): MonitorStatus
    {
        $startTime = microtime(true);
        $status = new MonitorStatus();
        $status->setMonitor($this);
        $status->setStartTime(new \DateTime());
        
        try {
            $errno = 0;
            $errstr = '';
            
            // Try to open a TCP connection to the host
            $socket = @fsockopen($this->host, $this->port, $errno, $errstr, 10);
            
            // Calculate response time
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $status->setResponseTime($responseTime);
            
            if ($socket) {
                fclose($socket);
                $status->setStatus(true);
            } else {
                $status->setStatus(false);
            }
        } catch (\Exception $e) {
            $status->setStatus(false);
            $status->setResponseTime(0);
        }
        
        return $status;
    }
} 