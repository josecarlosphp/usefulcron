<?php

namespace josecarlosphp\usefulcron;

/**
 * @author José Carlos PHP <info@josecarlosphp.com>
 */
class UsefulCron
{
    const MESSAGE_TYPE_INFO = 'info';
    const MESSAGE_TYPE_CHECK = 'check';
    const MESSAGE_TYPE_WARNING = 'warning';
    const MESSAGE_TYPE_ERROR = 'error';

    const EXTENSIONS_MODE_INCLUDE = 'include';
    const EXTENSIONS_MODE_EXCLUDE = 'exclude';

    /**
     * @var bool
     */
    private $initiated = false;
    /**
     * @var josecarlosphp\usefulcron\Config
     */
    private $config = null;

    public function __construct(Config $config = null)
    {
        $this->config = is_null($config) ? new Config() : clone $config;
    }

    public function __destruct()
    {
        if ($this->initiated) {
            $this->Msg('CRON FINISHED');
        }
    }
    /**
     * @param josecarlosphp\usefulcron\Config $config
     * @return josecarlosphp\usefulcron\Config
     */
    public function &config(Config $config = null)
    {
        if (!is_null($config)) {
            $this->config = clone $config;
        }

        return $this->config;
    }

    public function configVal($q, $val = null)
    {
        if (!property_exists('josecarlosphp\usefulcron\Config', $q)) {
            throw new \Exception('Property does not exist in Config class (' . $q . ')');
        }

        if (!is_null($val)) {
            $this->Msg(sprintf('%s = %s', $q, $val));
        }

        return $this->config->$q($val);
    }

    public function dirLog($val = null)
    {
        return $this->configVal(__FUNCTION__, $val);
    }

    public function debug($val = null)
    {
        return $this->configVal(__FUNCTION__, $val);
    }

    public function fake($val = null)
    {
        return $this->configVal(__FUNCTION__, $val);
    }

    public function Msg($message, $type = self::MESSAGE_TYPE_INFO, $log = true)
    {
        $this->init();

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        echo self::msg2line($message, $type);
        flush();

        if ($log) {
            $this->Log($message, $type);
        }
    }

    public function MsgDbg($message, $type = self::MESSAGE_TYPE_INFO)
    {
        if ($this->debug()) {
            $this->Msg($message, $type);
        }
    }

    static protected function msg2line($message, $type = self::MESSAGE_TYPE_INFO)
    {
        return sprintf("[%s] - %s\t- %s\n", date('Y-m-d H:i:s'), strtoupper($type), $message);
    }

    public function Log($message, $type = self::MESSAGE_TYPE_INFO)
    {
        $dir = $this->dirLog() . date('Y/m/');
        if (\josecarlosphp\utils\Files::makeDir($dir)) {
            if (($fp = fopen($dir . date('Y-m-d') . '.usefulcron.log', 'a'))) {
                $r = fwrite($fp, self::msg2line($message, $type));
                fclose($fp);

                return $r > 0;
            }
        }

        return false;
    }

    public function init()
    {
        if (!$this->initiated) {
            $this->initiated = true;
            header('Content-Type: text/plain; charset=UTF-8');
            $this->Msg('CRON STARTED');
        }
    }

    public function run($token = null, $codeOnNoAuth = 401)
    {
        if (!is_null($token)) {
            $this->checkAuth($token, $codeOnNoAuth);
        }

        foreach ($this->config->dirsToClean() as $dirToClean) {
            $this->cleanDir(
                $dirToClean->path(),
                $dirToClean->timeThreshold(),
                $dirToClean->extensions(),
                $dirToClean->extensionsMode()
            );
        }
    }

    public function checkAuth($token, $codeOnNoAuth = 401)
    {
        if (isset($_GET['token']) && $_GET['token'] === $token) {
            return true;
        }

        http_response_code($codeOnNoAuth);

        $codes = array(
            '100' => 'Continue',
            '101' => 'Switching Protocols',
            '200' => 'OK',
            '201' => 'Created',
            '202' => 'Accepted',
            '203' => 'Non-Authoritative Information',
            '204' => 'No Content',
            '205' => 'Reset Content',
            '206' => 'Partial Content',
            '300' => 'Multiple Choices',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '402' => 'Payment Required',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '406' => 'Not Acceptable',
            '407' => 'Proxy Authentication Required',
            '408' => 'Request Timeout',
            '409' => 'Conflict',
            '410' => 'Gone',
            '411' => 'Length Required',
            '412' => 'Precondition Failed',
            '413' => 'Request Entity Too Large',
            '414' => 'Request-URI Too Long',
            '415' => 'Unsupported Media Type',
            '416' => 'Requested Range Not Satisfiable',
            '417' => 'Expectation Failed',
            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavailable',
            '504' => 'Gateway Timeout',
            '505' => 'HTTP Version Not Supported'
        );
        if (array_key_exists((string)$codeOnNoAuth, $codes)) {
            echo $codes[(string)$codeOnNoAuth];
        }

        exit;
    }

    public function cleanDir($path, $timeThreshold, $extensions = array('php', 'htaccess'), $extensionsMode = self::EXTENSIONS_MODE_EXCLUDE)
    {
        $this->MsgFuncArgs(
            __FUNCTION__,
            array(
                'path' => $path,
                'timeThreshold' => $timeThreshold,
                'extensions' => $extensions,
                'extensionsMode' => $extensionsMode,
            ),
        );
        $stats = array(
            'total' => 0,
            'match' => 0,
            'deleted' => 0,
            'errors' => 0,
        );
        if ($path !== '' && is_dir($path)) {
            if (is_readable($path)) {
                $method = $extensionsMode == self::EXTENSIONS_MODE_EXCLUDE ? 'getFiles' : 'getFilesExt';
                $files = \josecarlosphp\utils\Files::$method($path, $extensions, true, false);
                foreach ($files as $file) {
                    $stats['total']++;
                    $this->MsgDbg($file);
                    if (($timeThreshold == 0 || time() - filemtime($file) > $timeThreshold)) {
                        $stats['match']++;
                        $this->MsgDbg('Delete');
                        if ($this->fake()) {
                            $stats['deleted']++;
                            $this->MsgDbg('Ok (fake)');
                        } elseif (unlink($file)) {
                            $stats['deleted']++;
                            $this->MsgDbg('Ok');
                        } else {
                            $stats['errors']++;
                            $this->MsgDbg('Error');
                        }
                    }
                }
            } else {
                $this->Msg('Not readable');
                $stats['errors']++;
            }
        } else {
            $this->Msg('Not dir');
            $stats['errors']++;
        }

        $this->Msg($stats, $stats['errors'] > 0 ? self::MESSAGE_TYPE_ERROR : self::MESSAGE_TYPE_CHECK);
    }

    protected function MsgFuncArgs($func, $args)
    {
        $str = $func . "(\n";
        foreach ($args as $key => $val) {
            $str .= sprintf("\t%s = %s,\n", $key, str_replace("\n", '', var_export($val, true)));
        }
        $str .= ')';

        $this->Msg($str);
    }

    static public function days2seconds($days)
    {
        return $days * 24 * 3600;
    }

    static public function hours2seconds($hours)
    {
        return $hours * 3600;
    }

    static public function minutes2seconds($minutes)
    {
        return $minutes * 60;
    }
}
