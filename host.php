#!/usr/bin/php
<?php

/**
 * Class Host
 */
class Host
{
    /**
     * @String null
     */
    protected $path = null;

    /**
     * @String null
     */
    protected $host = null;

    /**
     * @String /etc/hosts
     */
    protected $hosts = "/etc/hosts";

    /**
     * @String /etc/apache2/sites-available/
     */
    protected $apacheSitesPath = "/etc/apache2/sites-available/";

    /**
     * @String 127.0.0.1
     */
    protected $hostPattern = "127.0.0.1       ";

    /**
     * @param $path
     * @param $host
     */
    public function __construct($path, $host)
    {
        $this->path = $path;
        $this->host = $host;
        $this->hostPattern .= $host;
    }

    /**
     * @throws Exception
     */
    public function addSiteAvailable()
    {
        $configPattert = file_get_contents("pattern.txt");
        if (!$configPattert)
            throw new Exception("Cannot open pattern.txt file!");
        $siteConfig = str_replace('%path%', $this->path, $configPattert);
        $siteConfig = str_replace('%host%', $this->host, $siteConfig);
        if (file_put_contents($this->apacheSitesPath . $this->host, $siteConfig) == 0)
            throw new Exception("Cannot access " . $this->apacheSitesPath, 2);
        if (!exec("a2ensite " . $this->host))
            throw new Exception("Cannot enable host!", 3);
        mkdir($this->path . "/" . $this->host, 0777);

        $hosts = file_get_contents($this->hosts);
        if (file_put_contents($this->hosts, $this->hostPattern . "\r\n" . $hosts) == 0)
            throw new Exception("Cannot write hosts!", 5);
        if (!exec("/etc/init.d/apache2 restart"))
            throw new Exception("Cannot restart apache!", 6);
        file_put_contents($this->path . "/" . $this->host . "/index.php", "<?php phpinfo(); ");
        exec("chmod -R 777 " . $this->path . "/" . $this->host);
        return true;
    }
}

try {
    if ($argc < 3 || !$argv[1] || !$argv[2]) {
        throw new Exception("Usage: php hosts.php %path to your projects dir (without end slash '/')% %name of new project%", 7);
    }
    $host = new Host($argv[1], $argv[2]);
    $host->addSiteAvailable();
} catch (Exception $ex) {
    echo $ex->getMessage() . "\r\n";
}
?>