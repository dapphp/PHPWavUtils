<?php

require_once 'WavFile.php';
require_once 'WavMaker.php';

maryHad();
noiseTest();
sineTest();
squareTest();
sineWave();
mergeWavs();
appendWavs();


function mergeWavs()
{
    $wav1 = new WavFile(dirname(__FILE__) . '/wavs/spin.wav');
    $wav2 = new WavFile(dirname(__FILE__) . '/wavs/sinetest-2-44100-8.wav');

    $wav1->mergeWav($wav2);

    $wav1->save(dirname(__FILE__) . '/wavs/merged.wav');

    echo "mergeWavs() completed.\n";
}

function maryHad()
{
    $wav = new WavMaker(1, 11025, 16);

    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSineWav(391.995, 0.4); // g
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(493.883, 0.8); // b
    $wav->generateSineWav(440, 0.4);     // a
    $wav->insertSilence(0.05);
    $wav->generateSineWav(440, 0.4);     // a
    $wav->insertSilence(0.05);
    $wav->generateSineWav(440, 0.8);     // a
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(587.330, 0.4); // d
    $wav->generateSineWav(587.330);      // d
    $wav->insertSilence(2); // long pause
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSineWav(391.995, 0.4); // g
    $wav->generateSineWav(440, 0.4);     // a
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(493.883, 0.8); // b
    $wav->generateSineWav(440, 0.4);     // a
    $wav->insertSilence(0.05);
    $wav->generateSineWav(440, 0.4);     // a
    $wav->insertSilence(0.05);
    $wav->generateSineWav(440, 0.8);     // a
    $wav->generateSineWav(493.883, 0.4); // b
    $wav->generateSineWav(587.330, 0.4); // d
    $wav->generateSineWav(587.330);      // d

    $wav->save(dirname(__FILE__) . '/wavs/mary.wav');

    echo "maryHad() completed.\n";
}

function sineTest()
{
    // generate 3 second sine waves in multiple bit and sample rates
    $sps = array(8000, 11025, 22050, 44100);
    $bps = array(8, 16, 24);

    foreach($sps as $samplesPerSec) {
        foreach($bps as $bitsPerSample) {
            $wav = new WavMaker(2, $samplesPerSec, $bitsPerSample);
            $wav->generateSineWav(329.628, 3);

            $wav->save(dirname(__FILE__) . '/wavs/sinetest-2-' . $samplesPerSec . '-' . $bitsPerSample . '.wav');
        }
    }

    echo "sineTest() completed.\n";
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

    echo "squareTest completed.\n";
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

    echo "noiseTest() completed.\n";
}

function sineWave()
{
    $wav = new WavMaker(1, 44100, 16); // 2 channel, 44100 samples/sec, 16 bits/sample
    $wav->generateSineWav(659.255, 3); // E5 for 2 seconds

    $wav->save(dirname(__FILE__) . '/wavs/sine.wav');

    echo "sineWave() completed\n";
}

function appendWavs()
{
    $numChannels = 1;
    $sampleRate  = 11025;
    $bpSample    = 24;

    $wav      = new WavFile($numChannels, $sampleRate, $bpSample);
    $sineWave = new WavMaker($numChannels, $sampleRate, $bpSample);
    $sqreWave = new WavMaker($numChannels, $sampleRate, $bpSample);

    $sineWave->generateSineWav(880, 2.5);
    $sqreWave->generateSquareWave(880, 2.5);

    $wav->appendWav($sineWave);
    $wav->insertSilence();
    $wav->appendWav($sqreWave);

    $wav->save(dirname(__FILE__) . '/wavs/appended.wav');

    echo "appendWavs() completed.\n";
}
