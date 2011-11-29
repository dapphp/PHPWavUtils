<?php

require_once 'WavFile.php';
require_once 'WavMaker.php';

//sineWave();
//exit;
mergeWavs();

function mergeWavs()
{
	$wav1 = new WavFile();
	
	$wav1->openWav('spin.wav');
	
	
	$wav2 = new WavFile(dirname(__FILE__) . '/wavs/test.wav');
	
	$wav1->mergeWav($wav2);
	
	$fp = fopen(dirname(__FILE__) . '/wavs/out.wav', 'w+b');
	fwrite($fp, $wav1->makeHeader());
	fwrite($fp, $wav1->getDataSubchunk());
	
	fclose($fp);
}

function sineWave()
{
	$wav = new WavMaker(2, 44100, 16); // 2 channel, 44100 samples/sec, 16 bits/sample
	$wav->generateSineWav(659.255, 2); // E5 for 2 seconds
	
	
	$fp = fopen(dirname(__FILE__) . '/wavs/sine.wav', 'w+b');
	fwrite($fp, $wav->makeHeader());
	fwrite($fp, $wav->getDataSubchunk());
	
	fclose($fp);
}
