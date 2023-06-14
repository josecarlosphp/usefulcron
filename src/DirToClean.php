<?php

namespace josecarlosphp\usefulcron;

/**
 * @author José Carlos PHP <info@josecarlosphp.com>
 */
class DirToClean
{
    private $path = '';
    private $timeThreshold = 100 * 365 * 24 * 3600; //100 years
    private $extensions = array('php', 'htaccess');
    private $extensionsMode = UsefulCron::EXTENSIONS_MODE_EXCLUDE;

    public function __construct($path, $timeThreshold, $extensions = array('php', 'htaccess'), $extensionsMode = UsefulCron::EXTENSIONS_MODE_EXCLUDE)
    {
        $this->path($path);
        $this->timeThreshold($timeThreshold);
        $this->extensions($extensions);
        $this->extensionsMode($extensionsMode);
    }

    private function prop($prop, $val = null)
    {
        if (!is_null($val)) {
            switch ($prop) {
                case 'extensions':
                    if (!is_array($val)) {
                        $arr = explode(',', $val);
                        foreach ($arr as &$item) {
                            $item = trim($item);
                        }
                        $val = $arr;
                    }
                    break;
            }
            $this->$prop = $val;
        }

        return $this->$prop;
    }

    public function path($val = null)
    {
        return $this->prop(__FUNCTION__, $val);
    }

    public function timeThreshold($val = null)
    {
        return $this->prop(__FUNCTION__, $val);
    }

    public function extensions($val = null)
    {
        return $this->prop(__FUNCTION__, $val);
    }

    public function extensionsMode($val = null)
    {
        return $this->prop(__FUNCTION__, $val);
    }
}
