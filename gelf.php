<?php

class GELFMessage {

    const MAX_CHUNK_SIZE = 8192;

    const GELF_ID = 'gf';

    private $graylogHostname;
    private $graylogPort;
    
    private $data;

    public function  __construct($graylogHostname, $graylogPort)
    {
        if (!is_numeric($graylogPort)) {
            throw new Exception("Port must be numeric");
        }

        $this->graylogHostname = $graylogHostname;
        $this->graylogPort = $graylogPort;
    }

    private function dataParamSet($dataType) {
        if (isset($this->data[$dataType]) && strlen($this->data[$dataType]) > 0) {
            return true;
        }

        return false;
    }

    public function send()
    {
        // Check if all required parameters are set.
        if (!$this->dataParamSet("short_message") || !$this->dataParamSet("host")) {
            throw new Exception('Missing required data parameter: "short_message" and "host" are required.');
        }

echo json_encode($this->data) . "\n";

        // Convert data array to JSON and GZIP.
        $gzippedJsonData = gzcompress(json_encode($this->data));
	
        $sock = stream_socket_client('udp://' . gethostbyname($this->graylogHostname) .':' . $this->graylogPort);

	// Maximum size is 8192 byte. Split to chunks. (GELFv2 supports chunking)
/*	if (strlen($gzippedJsonData) > self::MAX_CHUNK_SIZE) {
            // Too big for one datagram. Send in chunks.
            $msgId = microtime(true) . rand(0,10000);

            // TODO: $parts = str_split($gzippedJsonData, self::MAX_CHUNK_SIZE);
            $parts = str_split($gzippedJsonData, 100);
            $i = 0;
            foreach($parts as $part) {
                fwrite($sock, $this->prependChunkData($part, $msgId, $i, count($parts)));
                $i++;
            }
*/
        //} else {
            // Send in one datagram.
            fwrite($sock, $gzippedJsonData);
	//}
    }

    private function prependChunkData($data, $msgId, $seqNum, $seqCnt)
    {
        if (!is_string($data) || $data === '') {
            throw new Exception('Data must be a string and not be empty');
        }

        if (!is_integer($seqNum) || !is_integer($seqCnt) || $seqCnt <= 0) {
            throw new Exception('Sequence number and count must be integer. Sequence count must be bigger than 0.');
        }

        if ($seqNum > $seqCnt) {
            throw new Exception('Sequence number must be bigger than sequence count');
        }

	echo pack('c', self::GELF_ID);

	return self::GELF_ID . sha1($msgId) . '<' . $seqNum . '><' . $seqCnt . '>' . $data;
    }

    // Setters / Getters.

    public function setShortMessage($message)
    {
        $this->data["short_message"] = $message;
    }

    public function setFullMessage($message)
    {
        $this->data["full_message"] = $message;
    }

    public function setHost($host)
    {
        $this->data["host"] = $host;
    }

    public function setLevel($level)
    {
        $this->data["level"] = $level;
    }

    public function setType($type)
    {
        $this->data["type"] = $type;
    }

    public function setFile($file)
    {
        $this->data["file"] = $file;
    }

    public function setLine($line)
    {
        $this->data["line"] = $line;
    }

    public function getShortMessage()
    {
        return isset($this->data["short_message"]) ? $this->data["short_message"] : null;
    }

    public function getFullMessage()
    {
        return isset($this->data["full_message"]) ? $this->data["full_message"] : null;
    }

    public function getHost()
    {
        return isset($this->data["host"]) ? $this->data["host"] : null;
    }

    public function getLevel()
    {
        return isset($this->data["level"]) ? $this->data["level"] : null;
    }

    public function getType()
    {
        return isset($this->data["type"]) ? $this->data["type"] : null;
    }

    public function getFile()
    {
        return isset($this->data["file"]) ? $this->data["file"] : null;
    }

    public function getLine()
    {
        return isset($this->data["line"]) ? $this->data["line"] : null;
    }
    
}