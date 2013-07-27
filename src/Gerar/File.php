<?php

namespace Gerar;

class File {
    function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    public static function named($fileName)
    {
        return new File($fileName);
    }

    public function read()
    {
        return file_get_contents($this->fileName);
    }

    public function exists()
    {
        return file_exists($this->fileName);
    }

    public function write($content)
    {
        file_put_contents($this->fileName, $content);
        return $this;
    }

    public function whenChanges($callable)
    {
        $cacheFile = Gerar::getCacheFile("md5-" . md5($this->fileName));
        $md5 = md5_file($this->fileName);

        if((!$cacheFile->exists()) || ($cacheFile->read() != $md5)) {
            call_user_func($callable);
            $md5 = md5_file($this->fileName);
            $cacheFile->write($md5);
        }

        return $this;
    }

    public function replaceIfPresent($needle, $newNeedle)
    {
        if($this->contains($needle)) {
            $content = $this->read();
            if($needle instanceof RegExp) {
                $newContent = preg_replace($needle->regexp, $newNeedle, $content);
            } else {
                $newContent = str_replace($needle, $newNeedle, $content);
            }
            $this->write($newContent);
        }
        return $this;
    }

    public function contains($needle)
    {
        if($needle instanceof RegExp) {
            return preg_match($needle->regexp, $this->read());
        } else {
            return strstr($this->read(), $needle);
        }
    }

    public function append($newContent)
    {
        $this->write($this->read() . $newContent);
    }

    public function chmod($string)
    {
        chmod($this->fileName, $string);
    }

    public function findString($needle)
    {
        if($needle instanceof RegExp) {
            preg_match_all($needle->regexp, $this->read(), $m);
            if(isset($m[1])) {
                return $m[1];
            }
        } else {
            if(strstr($this->read(), $needle)) {
                return $needle;
            };
        }
        return null;
    }

    public function shouldHaveLine($string)
    {
        if(!$this->findString($string)) {
            $this->append($string);
        }
    }

}