<?php

/**
* Project: PHPWavUtils: Classes for creating, reading, and manipulating WAV files in PHP<br />
* File: WavMaker.php<br />
*
* Copyright (c) 2012, Drew Phillips
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
* @version 0.2-alpha (January 2012)
* @package PHPWavUtils
*
*/

require_once 'WavFile.php';

class WavMaker extends WavFile
{
    public function generateSineWav($frequency = 440, $duration = 1.0) {
        $numChannels = $this->getNumChannels();
        $numSamples  = $this->getSampleRate() * $duration;
        $amplitude   = $this->getMaxAmplitude();
        $t           = (M_PI * 2 * $frequency) / $this->getSampleRate();

        for ($i = 0; $i < $numSamples - 1; ++$i) {
            $sample = '';
            for ($channel = 0; $channel < $numChannels; ++$channel) {
                $sample .= self::packSample($amplitude * sin($t * $i), $this->getBitsPerSample());
            }

            $this->_samples .= $sample;
        }

        $this->_dataSize_valid = false;
    }

    public function generateSquareWave($frequency = 440, $duration = 1.0)
    {
        $numChannels = $this->getNumChannels();
        $numSamples  = $this->getSampleRate() * $duration;
        $amplitude   = $this->getMaxAmplitude();
        $t           = (M_PI * 2 * $frequency) / $this->getSampleRate();

        for ($i = 0; $i < $numSamples - 1; ++$i) {
            $sample = '';
            for ($channel = 0; $channel < $numChannels; ++$channel) {
                $sample .= self::packSample($amplitude * $this->sgn(sin($t * $i)), $this->getBitsPerSample());
            }

            $this->_samples .= $sample;
        }

        $this->_dataSize_valid = false;
    }

    public function sgn($value)
    {
        if ($value > 0) return  1;
        if ($value < 0) return -1;

        return 0;
    }
}
