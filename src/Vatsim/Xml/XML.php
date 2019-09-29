<?php

namespace Vatsim\Xml;

use SimpleXMLElement;

class XML
{
    private $_urls = [];

    /**
     * Initiate the Module.
     *
     * @param [type] $urls [description]
     */
    public function __construct($urls)
    {
        $this->_urls = $urls;
    }

    /**
     * Grab data for a VID from the datastream and return the resulting response.
     *
     * @param [type] $cid [description]
     * @param string $url [description]
     *
     * @return [type] [description]
     */
    public function getData($cid, $url = 'idstatus')
    {
        if (!array_key_exists($url, $this->_urls)) {
            return [];
        }

        // let's load the URL!
        $context = stream_context_create(
            ['ssl' => [
                    'verify_peer' => true,
                    'allow_self_signed' => true,
                    'CN_match' => 'cert.vatsim.net',
                ],
            ]
        );
        $url = sprintf($this->_urls[$url], $cid);
        $xml = new SimpleXMLElement(file_get_contents($url, false, $context));

        if (!isset($xml->user) or !isset($xml->user[0])) {
            throw new VatsimXMLExpception('Invalid response received: '.$url);
        }

        $values = json_decode(json_encode($xml->user), false);
        $values->cid = $values->{'@attributes'}->cid;
        unset($values->{'@attributes'});

        return $values;
    }
}
