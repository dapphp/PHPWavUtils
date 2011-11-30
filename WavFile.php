<?php

// error_reporting(E_ALL); ini_set('display_errors', 1); // uncomment this line for debugging

/**
* Project: PHPWavUtils: Classes for creating, reading, and manipulating WAV files in PHP<br />
* File: WavFile.php<br />
*
* Copyright (c) 2011, Drew Phillips
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without modification,
* are permitted provided that the following conditions are met:
*
* - Redistributions of source code must retain the above copyright notice,
* this list of conditions and the following disclaimer.
* - Redistributions in binary form must reproduce the above copyright notice,
* this list of conditions and the following disclaimer in the documentation
* and/or other materials provided with the distribution.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
* AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
* IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
* ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
* LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
* CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
* SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
* INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
* CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
* ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
* POSSIBILITY OF SUCH DAMAGE.
*
* Any modifications to the library should be indicated clearly in the source code
* to inform users that the changes are not a part of the original software.<br /><br />
*
* @copyright 2011 Drew Phillips
* @author Drew Phillips <drew@drew-phillips.com>
* @version 0.1-alpha (December 2011)
* @package PHPWavUtils
*
*/

class WavFile
{
    const CHANNEL_FL = 1;
    const CHANNEL_FR = 2;
    const CHANNEL_FC = 3;
    const CHANNEL_LC = 4;
    const CHANNEL_BL = 5;
    const CHANNEL_BR = 6;
    const MAX_CHANNEL = 6;
    
    protected $_size;
    protected $_actualSize;
    protected $_format;
    protected $_subChunk1Size;
    protected $_numChannels;
    protected $_sampleRate;
    protected $_byteRate;
    protected $_blockAlign;
    protected $_bitsPerSample;
    protected $_dataSize;
    protected $_dataOffset;
    protected $_samples;
    protected $_fp;
    
    /**
     * WavFile Constructor
     * 
     * @param array|WavFile|int $param  A WavFile, array of property values, or the number of channels to set
     * @param int $sampleRate           The samples per second (i.e. 44100, 22050)
     * @param int $bitsPerSample        Bits per sample - 8, 16 or 32
     */
    public function __construct($params = null)
    {
        if ($params instanceof WavFile) {
            foreach ($params as $prop => $val) {
                $this->$prop = $val;
            }
        } else if (is_string($params)) {
            if (is_readable($params)) {
                try {
                    $this->openWav($params);
                    
                } catch(WavFormatException $wex) {
                    throw $wex;
                } catch(Exception $ex) {
                    throw $ex;
                }
            } else {
                throw new InvalidArgumentException("Cannot construct WavFile.  '" . htmlspecialchars($params) . "' is not readable.");
            }
        } else if (is_array($params)) {
            foreach ($params as $key => $val) {
                $this->$prop = $val;
            }
        } else if (is_int($params) && sizeof(func_num_args() == 3)) {
            $args = func_get_args();
            
            $numChannels   = $args[0];
            $sampleRate    = $args[1];
            $bitsPerSample = $args[2];
            
            $this->setNumChannels($numChannels)
                 ->setSampleRate($sampleRate)
                 ->setBitsPerSample($bitsPerSample);
        }
    }
   
    public function getSize() {
        return $this->_size;
    }

    protected function setSize($size) {
        $this->_size = $size;
        return $this;
    }

    public function getActualSize() {
        return $this->_actualSize;
    }

    public function setActualSize($actualSize) {
        $this->_actualSize = $actualSize;
        return $this;
    }

    public function getFormat() {
        return $this->_format;
    }

    public function setFormat($format) {
        $this->_format = $format;
        return $this;
    }
    
    public function getSubChunk1Size() {
        return $this->_subChunk1Size;
    }
    
    public function setSubChunk1Size($size) {
        $this->_subChunk1Size = $size;
        return $this;
    }

    public function getNumChannels() {
        return $this->_numChannels;
    }

    public function setNumChannels($numChannels) {
        $this->_numChannels = $numChannels;
        return $this;
    }

    public function getSampleRate() {
        return $this->_sampleRate;
    }
    
    public function getByteRate() {
        return $this->_byteRate;
    }

    public function setByteRate($_byteRate) {
        $this->_byteRate = $_byteRate;
        return $this;
    }

    public function getBlockAlign() {
        return $this->_blockAlign;
    }

    public function setBlockAlign($_blockAlign) {
        $this->_blockAlign = $_blockAlign;
        return $this;
    }

    public function setSampleRate($sampleRate) {
        $this->_sampleRate = $sampleRate;
        return $this;
    }

    public function getBitsPerSample() {
        return $this->_bitsPerSample;
    }

    public function setBitsPerSample($bitsPerSample) {
        $this->_bitsPerSample = $bitsPerSample;
        return $this;
    }
    
    public function getDataSize() {
        return $this->_dataSize;
    }

    public function setDataSize($_dataSize) {
        $this->_dataSize = $_dataSize;
        return $this;
    }
    
    public function getDataOffset() {
        return $this->_dataOffset;
    }

    public function setDataOffset($_dataOffset) {
        $this->_dataOffset = $_dataOffset;
        return $this;
    }
    
    public function getSamples() {
        return $this->_samples;
    }

    public function setSamples($_samples) {
        $this->_samples = $_samples;
        return $this;
    }
    
    public function getAmplitude()
    {
        if($this->getBitsPerSample() == 8) {
            return 255;
        } else {
            return (pow(2, $this->getBitsPerSample()) / 2) - 1;
        }
    }
    
    public function getMinAmplitude()
    {
        if ($this->getBitsPerSample() == 8) {
            return 0;
        } else {
            return -$this->getAmplitude() - 1;
        }
    }
    public function makeHeader()
    {
        // RIFF header
        $header = pack('N', 0x52494646);
        
        $subchunk1size = 16;  // 16 byte subchunk1, PCM format
        $subchunk2size = sizeof($this->_samples) * $this->getNumChannels() * $this->getBitsPerSample() / 8;
        
        $header .= pack('V', 4 + (8 + $subchunk1size) + (8 +  $subchunk2size));
        $header .= pack('N', 0x57415645); // WAVE marker
        
        // fmt subchunk
        $header .= pack('N', 0x666d7420); // "fmt "
        $header .= pack('V', $subchunk1size);
        $header .= pack('v', 1);
        $header .= pack('v', $this->getNumChannels());
        $header .= pack('V', $this->getSampleRate());
        $header .= pack('V', $this->getSampleRate() * $this->getNumChannels() * $this->getBitsPerSample() / 8);
        $header .= pack('v', $this->getNumChannels() * $this->getBitsPerSample() / 8);
        $header .= pack('v', $this->getBitsPerSample());
        
        return $header;
    }
    
    public function getDataSubchunk()
    {
        return pack('N', 0x64617461) . // "data" chunk
               pack('V', sizeof($this->_samples) * $this->getNumChannels() * $this->getBitsPerSample() / 8) .
               implode('', $this->_samples);
    }
    
    public function openWav($filename)
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException("Failed to open $filename - no such file or directory");
        } else if (!is_readable($filename)) {
            throw new InvalidArgumentException("Failed to open $filename, access denied");
        }
        
        $this->_fp = @fopen($filename, 'rb');
        
        if (!$this->_fp) {
            $e = error_get_last();
            throw new Exception($e['message']);
        }
        
        try {
            $this->readWav();
        } catch (WavFormatException $wex) {
            throw $wex;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    public function setWavData(&$data, $free = true)
    {
        $this->_fp = @fopen('php://memory', 'w+b');
        
        if (!$this->_fp) {
            throw new Exception('Failed to open memory stream to write wav data.  Use openWav() instead');
        }
        
        fputs($this->_fp, $data);
        
        if ($free) {
            $data = null;
        }
        
        rewind($this->_fp);
        
        try {
            $this->readWav();
        } catch (WavFormatException $wex) {
            throw $wex;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    public function getSample($sampleNum)
    {
        if (sizeof($this->_samples) <= $sampleNum) {
            return null;
        } else {
            return $this->_samples[$sampleNum];
        }
    }
    
    public function appendWav(WavFile $wav) {
        // basic checks
        if ($wav->getSampleRate() != $this->getSampleRate()) {
            throw new Exception("Sample rate for wav files do not match");
        } else if ($wav->getBitsPerSample() != $this->getBitsPerSample()) {
            throw new Exception("Bits per sample for wav files do not match");
        } else if ($wav->getNumChannels() != $this->getNumChannels()) {
            throw new Exception("Number of channels for wav files do not match");
        }
        
        foreach ($wav->getSamples() as $sample) {
        	$this->_samples[] = $sample;
        }
        
        return $this;
    }
    
    public function mergeWav(WavFile $wav) {
        if ($wav->getSampleRate() != $this->getSampleRate()) {
            throw new Exception("Sample rate for wav files do not match");
        } else if ($wav->getNumChannels() != $this->getNumChannels()) {
            throw new Exception("Number of channels for wav files does not match");
        }
        
        $numSamples = sizeof($this->_samples);
        $numChannels = $this->getNumChannels();
        $packChr     = $this->getPackFormatString();
        
        for ($s = 0; $s < $numSamples; ++$s) {
            $sample1 = $this->getSample($s);
            $sample2 = $wav->getSample($s);
            
            // TODO: option to extend/rewind buffer to extend to for the longest wav
            if ($sample1 == null || $sample2 == null) break;
            
            $sample = '';
            
            for ($c = 1; $c <= $numChannels; ++$c) {
                $smpl = (int)(($this->getChannelData($sample1, $c) + $this->getChannelData($sample2, $c)) / 2);
                
                $sample .= $this->packSample($smpl);
            }
            
            $this->_samples[$s] = $sample;
        }
        
        return $this;
    }
    
    public function save($filename)
    {
        $fp = @fopen($filename, 'w+b');
        
        if (!$fp) {
            throw new Exception("Failed to open " . htmlspecialchars($filename) . " for writing");
        }
        
        fwrite($fp, $this->makeHeader());
        fwrite($fp, $this->getDataSubchunk());
        fclose($fp);
        
        return $this;
    }
    
    
    protected function getPackFormatString()
    {
        switch($this->getBitsPerSample()) {
            case 8:
                return 'C'; // unsigned char
                
            case 16:
                return 'v'; // signed short - little endian
                
            case 24:
                return 'C3';
        }
        
        throw new Exception("Invalid bits per sample");
    }
    
    protected function packSample($value)
    {
        switch ($this->getBitsPerSample()) {
            case 8:
            case 16:
                return pack($this->getPackFormatString(), $value);
                
            case 24:
                // 3 byte packed integer, little endian
                return pack('C3', ($value & 0xff),
                                  ($value >>  8) & 0xff,
                                  ($value >> 16) & 0xff);
        }
    }
    
    protected function readWav()
    {
        try {
            $this->getWavInfo();
        } catch (WavFormatException $wex) {
            fclose($this->_fp);
            throw $wex;
        } catch (Exception $ex) {
            fclose($this->_fp);
            throw $ex;
        }
        
        $this->readWavData();
        
        // TODO: read any extra data chunks
    }
    
    protected function getWavInfo()
    {
        if (!$this->_fp) {
            throw new Exception("No wav file open");
        }

        $wavHeaderSize = 36; // size of the wav header
        
        $header = fread($this->_fp, $wavHeaderSize);
        
        if (strlen($header) < $wavHeaderSize) {
            throw new WavFormatException('Not wav format, header too short', 1);
        }
        
        $RIFF = unpack('NChunkID/VChunkSize/NFormat', $header);
        
        if ($RIFF['ChunkID'] != 0x52494646) {
            throw new WavFormatException('Not wav format, RIFF signature missing', 2);
        }
        
        $stat       = fstat($this->_fp);
        $actualSize = $stat['size'];
        
        if ($actualSize - 8 != $RIFF['ChunkSize']) {
            //echo "$actualSize {$RIFF['ChunkSize']}\n";
            trigger_error("Bad chunk size, does not match actual file size ($actualSize {$RIFF['ChunkSize']})", E_USER_NOTICE);
            //throw new WavFormatException('Bad chunk size, does not match actual file size', 4);
        }

        if ($RIFF['Format'] != 0x57415645) {
            throw new WavFormatException('Not wav format, RIFF format not WAVE', 5);
        }
        
        $this->setSize($RIFF['ChunkSize'])
             ->setActualSize($actualSize);

        $fmt = unpack('NSubChunk1ID/VSubChunk1Size/vAudioFormat/vNumChannels/'
                     .'VSampleRate/VByteRate/vBlockAlign/vBitsPerSample',
                     substr($header, 12, 26));
                     
        if ($fmt['SubChunk1ID'] != 0x666d7420) {
            throw new WavFormatException('Bad wav header, expected fmt, found ' . $fmt['SubChunk1ID'], 6);
        }
        
        $this->setSubChunk1Size($fmt['SubChunk1Size']);
        
        if ($fmt['AudioFormat'] != 1) {
            throw new WavFormatException('Not PCM audio, non PCM is not supported', 7);
        }
        
        $this->setFormat('PCM')
             ->setNumChannels($fmt['NumChannels'])
             ->setSampleRate($fmt['SampleRate'])
             ->setBitsPerSample($fmt['BitsPerSample'])
             ->setByteRate($fmt['ByteRate'])
             ->setSampleRate($fmt['SampleRate'])
             ->setBlockAlign($fmt['BlockAlign']);
                
        if ($this->getSubChunk1Size() > 16) {
            $epSize          = fread($this->_fp, 2);
            $extraParamsSize = unpack('vSize', $epSize);
            if ($extraParamsSize['Size'] > 0) {
            	$extraParams     = fread($this->_fp, $extraParamsSize['Size']);
            }
            
            $wavHeaderSize  += ($extraParamsSize['Size'] - 16);
        }

        $dataHeader = fread($this->_fp, 8);
        $data       = unpack('NSubchunk2ID/VSubchunk2Size', $dataHeader);
        
        if ($data['Subchunk2ID'] != 0x64617461) {
            throw new WavFormatException('Data chunk expected, found ' . $data['Subchunk2ID'], 8);
        }
        
        $this->setDataSize($data['Subchunk2Size'])
             ->setDataOffset($wavHeaderSize + 8);
        
        return $this;
    }
    
    protected function readWavData()
    {
        $numSamples = $this->getDataSize() / $this->getBlockAlign();
        
        for ($i = 0; $i < $numSamples; ++$i) {
            $this->readSample();
        }
    }
    
    protected function readSample()
    {
        $sample = fread($this->_fp, $this->getBlockAlign());
        
        $this->_samples[] = $sample;
        
        return $sample;
    }
    
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            throw new Exception("No such property '$name' exists");
        }
    }
    
    public function __get($name) {
        $method = 'get' . $name;
        
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            throw new Exception("No such property '$name' exists");
        }
    }
    
    protected function getChannelData(&$sample, $channel = 0)
    {
        $channels = array();
        
        $csize = $this->getBitsPerSample()/8;
        $numChannels = $this->getNumChannels();
        $packChr = $this->getPackFormatString();
        
        $ch = $channel;
        
        for ($i = 1; $i <= self::MAX_CHANNEL; ++$i) {
            if ($i - 1 > $numChannels) break;
            
            if ($ch == 0 || $i == $ch) {
                $cdata = substr($sample, ($i - 1) * $csize, $csize);
                $data  = unpack($packChr . 'sample', $cdata);
                $channels[$i] = (int)$data['sample'];
            }
        }
        
        if (sizeof($channels) == 1) {
            return array_shift($channels);
        } else {
            return $channels;
        }
    }
    
    public function __toString()
    {
        return $this->makeHeader() .
               $this->getDataSubchunk();
    }
    
    public function displayInfo()
    {
        $s = "File Size: %u\n"
            ."Audio Format: %s\n"
            ."Sub Chunk 1 Size: %u\n"
            ."Channels: %u\n"
            ."Byte Rate: %u\n"
            ."Sample Size: %u\n"
            ."Sample Rate: %u\n"
            ."Bits Per Sample: %u\n";
            
        $s = sprintf($s, $this->getActualSize(),
                         $this->getFormat(),
                         $this->getSubChunk1Size(),
                         $this->getNumChannels(),
                         $this->getByteRate(),
                         $this->getBlockAlign(),
                         $this->getSampleRate(),
                         $this->getBitsPerSample());
                         
        if (php_sapi_name() == 'cli') {
            return $s;
        } else {
            return nl2br($s);
        }
    }
}

class WavFormatException extends Exception
{
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

