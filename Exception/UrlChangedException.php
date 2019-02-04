<?php


namespace Yosimitso\WorkingForumBundle\Exception;


class UrlChangedException extends \Exception
{
    protected $actualUrl;

    /**
     * @return mixed
     */
    public function getActualUrl()
    {
        return $this->actualUrl;
    }

    /**
     * @param mixed $actualUrl
     */
    public function setActualUrl($actualUrl)
    {
        $this->actualUrl = $actualUrl;

        return $this;
    }


}

