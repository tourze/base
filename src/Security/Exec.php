<?php

namespace tourze\Security;

use tourze\Base\Exception\InvalidArgumentException;

/**
 * 安全地执行外部程序
 *
 * @package phpSec
 */
class Exec
{

    const STDIN  = 0;
    const STDOUT = 1;
    const STDERR = 2;

    private $descSpecs = [
        self::STDIN  => ["pipe", "r"],
        self::STDOUT => ["pipe", "w"],
        self::STDERR => ["pipe", "w"],
    ];

    /**
     * @var array 附加环境变量
     */
    public $_env = [];

    /**
     * @var string 当前工作目录，默认是脚本路径
     */
    public $_cwd = null;

    /**
     * 执行外部应用，允许使用类似PDO的参数绑定
     *
     * @param string $cmd   要执行的命令，如："ls -lsa %path".
     * @param array  $args  参数，数组形式，如：['%path' => '/home']
     * @param string $stdin 输入重定向，完成一些需要交互才能搞定的功能
     * @return array  返回执行结果
     */
    public function run($cmd, $args = [], $stdin = null)
    {

        $cmd = $this->buildCommand($cmd, $args);

        $process = proc_open($cmd, $this->descSpecs, $pipes, $this->_cwd, $this->_env);

        if (is_resource($process))
        {

            /* Write stuff to STDIN, and close it. */
            fwrite($pipes[self::STDIN], $stdin);
            fclose($pipes[self::STDIN]);

            // 读STDOUT和STDERR
            $out['STDOUT'] = stream_get_contents($pipes[self::STDOUT]);
            $out['STDERR'] = stream_get_contents($pipes[self::STDERR]);

            // 关闭STDOUT和STDERR，防止出现可能的死锁
            fclose($pipes[self::STDOUT]);
            fclose($pipes[self::STDERR]);

            /* Close process and get return value. */
            $out['return'] = proc_close($process);

            return $out;
        }
        return false;
    }

    /**
     * 生成最终执行的命令
     *
     * @param string $cmd 要执行的命令
     * @param array  $args 命令中要替换的参数
     * @return string 执行结果
     * @throws \tourze\Base\Exception\InvalidArgumentException
     */
    private function buildCommand($cmd, $args = [])
    {
        while (list($name, $data) = each($args))
        {
            $filterType = mb_substr($name, 0, 1);
            switch ($filterType)
            {
                case '%':
                    $safeData = escapeshellarg($data);
                    break;
                case '!':
                    $safeData = escapeshellcmd($data);
                    break;
                default:
                    throw new InvalidArgumentException('Unknown variable type');
                    break;
            }
            if ($safeData !== false)
            {
                $cmd = str_replace($name, $safeData, $cmd);
            }
        }
        return $cmd;
    }
}
