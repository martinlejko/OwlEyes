<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

#[ORM\Entity]
class WebsiteMonitor extends Monitor
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $url;

    #[ORM\Column(type: 'boolean')]
    private bool $checkStatus;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $keywords = [];

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function isCheckStatus(): bool
    {
        return $this->checkStatus;
    }

    public function setCheckStatus(bool $checkStatus): self
    {
        $this->checkStatus = $checkStatus;
        return $this;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): self
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function check(): MonitorStatus
    {
        $startTime = microtime(true);
        $status = new MonitorStatus();
        $status->setMonitor($this);
        $status->setStartTime(new \DateTime());
        
        $client = new Client([
            'timeout' => 10,
            'verify' => false,
        ]);
        
        try {
            $response = $client->get($this->url);
            $responseTime = (int) ((microtime(true) - $startTime) * 1000);
            $status->setResponseTime($responseTime);
            
            // Check if status code is in range (200, 300]
            $statusCode = $response->getStatusCode();
            $validStatus = !$this->checkStatus || ($statusCode > 200 && $statusCode <= 300);
            
            // Check keywords if needed
            $validKeywords = true;
            if (!empty($this->keywords)) {
                $body = (string) $response->getBody();
                foreach ($this->keywords as $keyword) {
                    if (strpos($body, $keyword) === false) {
                        $validKeywords = false;
                        break;
                    }
                }
            }
            
            $status->setStatus($validStatus && $validKeywords);
        } catch (GuzzleException $e) {
            $status->setStatus(false);
            $status->setResponseTime(0);
        }
        
        return $status;
    }
} 