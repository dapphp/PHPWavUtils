<?php

require_once 'WavFile.php';
require_once 'WavMaker.php';

//maryHad();
//noiseTest();
//sineTest();
squareTest();
//sineWave();
//mergeWavs();

function mergeWavs()
{
    $wav1 = new WavFile(dirname(__FILE__) . '/wavs/sine-1-8000-8.wav');
    $wav2 = new WavFile(dirname(__FILE__) . '/wavs/southpark2.wav');
    
    $wav1->mergeWav($wav2);
    
    $fp = fopen(dirname(__FILE__) . '/wavs/out.wav', 'w+b');
    fwrite($fp, $wav1->makeHeader());
    fwrite($fp, $wav1->getDataSubchunk());
    
    fclose($fp);
    
    die('Merge completed');
}

function maryHad()
{
    $wav = new WavMaker(1, 44100, 16);
    
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSineWav(391.995, 0.4); // g
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(493.883, 0.8); // b
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSilence(0.05);
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSilence(0.05);
    $wav->generateSineWav(440, 0.8);     // a
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(587.330, 0.4); // d
    $wav->generateSineWav(587.330);      // d
    $wav->generateSilence(2); // long pause
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSineWav(391.995, 0.4); // g
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(493.883, 0.8); // b
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSilence(0.05);
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSilence(0.05);
    $wav->generateSineWav(440, 0.8);     // a
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(587.330, 0.4); // d
    $wav->generateSineWav(587.330);      // d
    
    $wav->save(dirname(__FILE__) . '/wavs/mary.wav');
    
    die('Mary had a little lamb');
}

function sineTest()
{
    // generate 3 second sine waves in multiple bit and sample rates
    $sps = array(8000, 11025, 22050, 44100);
    $bps = array(8, 16, 24);
    
    foreach($sps as $samplesPerSec) {
        foreach($bps as $bitsPerSample) {
            $wav = new WavMaker(1, $samplesPerSec, $bitsPerSample);
            $wav->generateSineWav(329.628, 3);
            
            $wav->save(dirname(__FILE__) . '/wavs/sinetest-1-' . $samplesPerSec . '-' . $bitsPerSample . '.wav');
        }
    }
    
    die('Sine test completed');
}

function squareTest()
{
    $sps = array(8000, 11025, 22050, 44100);
    $bps = array(8, 16, 24);
    
    foreach($sps as $samplesPerSec) {
        foreach($bps as $bitsPerSample) {
            $wav = new WavMaker(1, $samplesPerSec, $bitsPerSample);
            $wav->generateSquareWave(130.813, 3);
            
            $wav->save(dirname(__FILE__) . '/wavs/squaretest-1-' . $samplesPerSec . '-' . $bitsPerSample . '.wav');
        }
    }
    
    die('Square test completed');
}

function noiseTest()
{
    $sps = array(8000, 44100);
    $bps = array(8, 16);
    
    foreach($sps as $samplesPerSec) {
        foreach($bps as $bitsPerSample) {
            $wav = new WavMaker(1, $samplesPerSec, $bitsPerSample);
            $wav->generateNoise(3);
            
            $wav->save(dirname(__FILE__) . '/wavs/noise-1-' . $samplesPerSec . '-' . $bitsPerSample . '.wav');
        }
    }
    
    die('Noise test completed');
}

function sineWave()
{
    $wav = new WavMaker(1, 44100, 16); // 2 channel, 44100 samples/sec, 16 bits/sample
    $wav->generateSineWav(659.255, 3); // E5 for 2 seconds
    
    
    $fp = fopen(dirname(__FILE__) . '/wavs/sine.wav', 'w+b');
    fwrite($fp, $wav->makeHeader());
    fwrite($fp, $wav->getDataSubchunk());
    
    fclose($fp);
    
    die('Sine wav completed');
}
