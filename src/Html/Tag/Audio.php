<?php

namespace tourze\Html\Tag;

use tourze\Html\Element\InlineElement;
use tourze\Html\Tag;

/**
 * audio标签
 *
 * @property mixed autoPlay  如果出现该属性，则音频在就绪后马上播放
 * @property mixed controls  如果出现该属性，则向用户显示控件，比如播放按钮
 * @property mixed loop      如果出现该属性，则每当音频结束时重新开始播放
 * @property mixed muted     规定视频输出应该被静音
 * @property mixed preLoad   如果出现该属性，则音频在页面加载时进行加载，并预备播放，如果使用 "autoplay"，则忽略该属性。
 * @property mixed src       要播放的音频的URL
 */
class Audio extends Tag implements InlineElement
{

    protected $_tagName = 'audio';

    /**
     * @return null|string|array
     */
    public function getAutoPlay()
    {
        return $this->getAttribute('autoPlay');
    }

    /**
     * @param $autoPlay
     */
    public function setAutoPlay($autoPlay)
    {
        $this->setAttribute('autoPlay', $autoPlay);
    }

    /**
     * @return null|string|array
     */
    public function getControls()
    {
        return $this->getAttribute('controls');
    }

    /**
     * @param $controls
     */
    public function setControls($controls)
    {
        $this->setAttribute('controls', $controls);
    }

    /**
     * @return null|string|array
     */
    public function getLoop()
    {
        return $this->getAttribute('loop');
    }

    /**
     * @param $loop
     */
    public function setLoop($loop)
    {
        $this->setAttribute('loop', $loop);
    }

    /**
     * @return null|string|array
     */
    public function getMuted()
    {
        return $this->getAttribute('muted');
    }

    /**
     * @param $muted
     */
    public function setMuted($muted)
    {
        $this->setAttribute('muted', $muted);
    }

    /**
     * @return null|string|array
     */
    public function getPreLoad()
    {
        return $this->getAttribute('preLoad');
    }

    /**
     * @param $preLoad
     */
    public function setPreLoad($preLoad)
    {
        $this->setAttribute('preLoad', $preLoad);
    }

    /**
     * @return null|string|array
     */
    public function getSrc()
    {
        return $this->getAttribute('src');
    }

    /**
     * @param $src
     */
    public function setSrc($src)
    {
        $this->setAttribute('src', $src);
    }

}
