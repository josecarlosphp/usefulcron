<?php

namespace josecarlosphp\usefulcron;

/**
 * @author José Carlos PHP <info@josecarlosphp.com>
 */
class Config
{
    private $dirLog = null;
    private $debug = false;
    private $fake = false;
    /**
     * @var DirToClean[]
     */
    private $dirsToClean = array();

    public function __construct()
    {
        $this->dirLog = getcwd() . '/log/';
    }

    private function prop($prop, $val = null)
    {
        if (!is_null($val)) {
            switch ($prop) {
                case 'dirLog':
                    $len = mb_strlen($val);
                    if ($len == 0 || mb_substr($val, $len-1) != '/') {
                        $val .= '/';
                    }
                    break;
            }
            $this->$prop = $val;
        }

        return $this->$prop;
    }

    public function dirLog($val = null)
    {
        return $this->prop(__FUNCTION__, $val);
    }

    public function debug($val = null)
    {
        return $this->prop(__FUNCTION__, $val);
    }

    public function fake($val = null)
    {
        return $this->prop(__FUNCTION__, $val);
    }

    public function addDirToClean($path, $timeThreshold, $extensions = array('php', 'htaccess'), $extensionsMode = UsefulCron::EXTENSIONS_MODE_EXCLUDE)
    {
        $this->dirsToClean[] = new DirToClean($path, $timeThreshold, $extensions, $extensionsMode);
    }
    /**
     * @return DirToClean[]
     */
    public function dirsToClean()
    {
        return $this->dirsToClean;
    }
}
